<?php
/**
 * Booking Controller
 * 
 * Handles booking-related operations
 */
class BookingController extends Controller {
    private $bookingModel;
    private $roomModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        require_once __DIR__ . '/../models/Booking.php';
        require_once __DIR__ . '/../models/Room.php';
        $this->bookingModel = new Booking();
        $this->roomModel = new Room();
    }
    
    /**
     * Display user's bookings
     */
    public function index() {
        $this->requireAuth();
        
        $userId = $_SESSION['user']['id'];
        $bookings = $this->bookingModel->getByStudentId($userId);
        
        $this->render('bookings/index', [
            'bookings' => $bookings
        ]);
    }
    
    /**
     * Display booking details
     */
    public function view($id) {
        $this->requireAuth();
        
        // Get booking details
        $booking = $this->bookingModel->getById($id);
        
        if (!$booking) {
            Util::setFlash('error', 'Booking not found');
            Util::redirect('/bookings');
            return;
        }
        
        // Check if user has permission to view this booking
        if (!$this->hasRole('admin') && $booking['student_id'] != $_SESSION['user']['id']) {
            Util::setFlash('error', 'You do not have permission to view this booking');
            Util::redirect('/bookings');
            return;
        }
        
        $this->render('bookings/view', [
            'booking' => $booking
        ]);
    }
    
    /**
     * Display booking form
     */
    public function book($roomId) {
        $this->requireAuth();
        
        // Get room details
        $room = $this->roomModel->getById($roomId);
        
        if (!$room) {
            Util::setFlash('error', 'Room not found');
            Util::redirect('/rooms/available');
            return;
        }
        
        // Check if room is available
        if ($room['status'] !== 'available') {
            Util::setFlash('error', 'Room is not available for booking');
            Util::redirect('/rooms/available');
            return;
        }
        
        $this->render('bookings/book', [
            'room' => $room,
            'check_in' => $_GET['check_in'] ?? date('Y-m-d'),
            'check_out' => $_GET['check_out'] ?? date('Y-m-d', strtotime('+1 day'))
        ]);
    }
    
    /**
     * Process booking form
     */
    public function processBooking() {
        $this->requireAuth();
        $this->requireCsrfToken('/rooms/available');
        
        $roomId = $_POST['room_id'] ?? '';
        $checkIn = $_POST['check_in'] ?? '';
        $checkOut = $_POST['check_out'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        // Validate input
        $errors = $this->validate(
            [
                'room_id' => $roomId,
                'check_in' => $checkIn,
                'check_out' => $checkOut
            ],
            [
                'room_id' => 'required|numeric',
                'check_in' => 'required|date',
                'check_out' => 'required|date'
            ]
        );
        
        // Check if check-in date is in the future
        if (strtotime($checkIn) < strtotime(date('Y-m-d'))) {
            $errors['check_in'] = 'Check-in date must be in the future';
        }
        
        // Check if check-out date is after check-in date
        if (strtotime($checkOut) <= strtotime($checkIn)) {
            $errors['check_out'] = 'Check-out date must be after check-in date';
        }
        
        if (!empty($errors)) {
            Util::setFlash('error', 'Please correct the errors in the form');
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = [
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'notes' => $notes
            ];
            Util::redirect('/bookings/book/' . $roomId);
            return;
        }
        
        // Get room details
        $room = $this->roomModel->getById($roomId);
        
        if (!$room) {
            Util::setFlash('error', 'Room not found');
            Util::redirect('/rooms/available');
            return;
        }
        
        // Check if room is available
        if ($room['status'] !== 'available') {
            Util::setFlash('error', 'Room is not available for booking');
            Util::redirect('/rooms/available');
            return;
        }
        
        // Check if room is available for the selected dates
        $availableRooms = $this->roomModel->getAvailableRooms($checkIn, $checkOut);
        $isAvailable = false;
        
        foreach ($availableRooms as $availableRoom) {
            if ($availableRoom['id'] == $roomId) {
                $isAvailable = true;
                break;
            }
        }
        
        if (!$isAvailable) {
            Util::setFlash('error', 'Room is not available for the selected dates');
            $_SESSION['form_data'] = [
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'notes' => $notes
            ];
            Util::redirect('/bookings/book/' . $roomId);
            return;
        }
        
        // Create booking
        $bookingData = [
            'student_id' => $_SESSION['user']['id'],
            'room_id' => $roomId,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'notes' => $notes,
            'status' => 'pending',
            'payment_status' => 'unpaid'
        ];
        
        $bookingId = $this->bookingModel->create($bookingData);
        
        if (!$bookingId) {
            Util::setFlash('error', 'Failed to create booking');
            $_SESSION['form_data'] = [
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'notes' => $notes
            ];
            Util::redirect('/bookings/book/' . $roomId);
            return;
        }
        
        // Log activity
        $this->logActivity('booking_created', 'booking', $bookingId);
        
        Util::setFlash('success', 'Booking created successfully and is pending approval');
        Util::redirect('/bookings');
    }
    
    /**
     * Cancel booking
     */
    public function cancel($id) {
        $this->requireAuth();
        $this->requireCsrfToken('/bookings');
        
        $reason = $_POST['reason'] ?? '';
        
        // Cancel booking
        $result = $this->bookingModel->cancel($id, $_SESSION['user']['id'], $reason);
        
        if (!$result) {
            Util::setFlash('error', 'Failed to cancel booking');
            Util::redirect('/bookings');
            return;
        }
        
        // Log activity
        $this->logActivity('booking_cancelled', 'booking', $id);
        
        Util::setFlash('success', 'Booking cancelled successfully');
        Util::redirect('/bookings');
    }
    
    /**
     * Display manage bookings page (admin only)
     */
    public function manage() {
        $this->requireRole('admin');
        
        // Get filter parameters
        $filters = [
            'status' => $_GET['status'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
            'payment_status' => $_GET['payment_status'] ?? null
        ];
        
        // Get bookings
        $bookings = $this->bookingModel->getAll($filters);
        
        $this->render('admin/bookings/manage', [
            'bookings' => $bookings,
            'filters' => $filters
        ]);
    }
    
    /**
     * Update booking status (admin only)
     */
    public function updateStatus($id) {
        $this->requireRole('admin');
        $this->requireCsrfToken('/admin/bookings/manage');
        
        $status = $_POST['status'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        if (empty($status)) {
            Util::setFlash('error', 'Status is required');
            Util::redirect('/admin/bookings/manage');
            return;
        }
        
        // Update booking status
        $result = $this->bookingModel->updateStatus($id, $status, $notes);
        
        if (!$result) {
            Util::setFlash('error', 'Failed to update booking status');
            Util::redirect('/admin/bookings/manage');
            return;
        }
        
        // Log activity
        $this->logActivity('booking_status_updated', 'booking', $id);
        
        Util::setFlash('success', 'Booking status updated successfully');
        Util::redirect('/admin/bookings/manage');
    }
    
    /**
     * Display booking statistics (admin only)
     */
    public function statistics() {
        $this->requireRole('admin');
        
        $period = $_GET['period'] ?? 'monthly';
        $stats = $this->bookingModel->getStatistics($period);
        
        $this->render('admin/bookings/statistics', [
            'stats' => $stats,
            'period' => $period
        ]);
    }
}
