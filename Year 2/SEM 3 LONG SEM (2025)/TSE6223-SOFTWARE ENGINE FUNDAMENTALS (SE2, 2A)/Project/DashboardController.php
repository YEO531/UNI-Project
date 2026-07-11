<?php
/**
 * Dashboard Controller
 * 
 * Handles dashboard-related operations
 */
class DashboardController extends Controller {
    private $bookingModel;
    private $roomModel;
    private $userModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        require_once __DIR__ . '/../models/Booking.php';
        require_once __DIR__ . '/../models/Room.php';
        require_once __DIR__ . '/../models/User.php';
        $this->bookingModel = new Booking();
        $this->roomModel = new Room();
        $this->userModel = new User();
    }
    
    /**
     * Display student dashboard
     */
    public function studentDashboard() {
        $this->requireRole('student');
        
        $userId = $_SESSION['user']['id'];
        
        // Get recent bookings
        $bookings = $this->bookingModel->getByStudentId($userId);
        $recentBookings = array_slice($bookings, 0, 5);
        
        // Get available rooms
        $availableRooms = $this->roomModel->getAvailableRooms(
            date('Y-m-d'), 
            date('Y-m-d', strtotime('+1 day'))
        );
        $featuredRooms = array_slice($availableRooms, 0, 3);
        
        // Get notifications
        $notifications = $this->userModel->getNotifications($userId, true);
        
        $this->render('dashboard/student', [
            'recentBookings' => $recentBookings,
            'featuredRooms' => $featuredRooms,
            'notifications' => $notifications
        ]);
    }
    
    /**
     * Display admin dashboard
     */
    public function adminDashboard() {
        $this->requireRole('admin');
        
        // Get pending bookings
        $pendingBookings = $this->bookingModel->getAll(['status' => 'pending']);
        
        // Get booking statistics
        $bookingStats = $this->bookingModel->getStatistics('monthly');
        
        // Get room statistics
        $rooms = $this->roomModel->getAll();
        $roomStats = [
            'total' => count($rooms),
            'available' => 0,
            'booked' => 0,
            'occupied' => 0,
            'maintenance' => 0
        ];
        
        foreach ($rooms as $room) {
            $roomStats[$room['status']]++;
        }
        
        // Get user notifications
        $userId = $_SESSION['user']['id'];
        $notifications = $this->userModel->getNotifications($userId, true);
        
        $this->render('dashboard/admin', [
            'pendingBookings' => $pendingBookings,
            'bookingStats' => $bookingStats,
            'roomStats' => $roomStats,
            'notifications' => $notifications
        ]);
    }
    
    /**
     * Display staff dashboard
     */
    public function staffDashboard() {
        $this->requireRole('staff');
        
        // Get today's check-ins and check-outs
        $today = date('Y-m-d');
        $checkIns = $this->bookingModel->getAll(['date_from' => $today, 'date_to' => $today, 'status' => 'approved']);
        $checkOuts = $this->bookingModel->getAll(['date_from' => $today, 'date_to' => $today, 'status' => 'completed']);
        
        // Get room maintenance requests
        // (This would require a maintenance request model, which we haven't implemented yet)
        
        // Get user notifications
        $userId = $_SESSION['user']['id'];
        $notifications = $this->userModel->getNotifications($userId, true);
        
        $this->render('dashboard/staff', [
            'checkIns' => $checkIns,
            'checkOuts' => $checkOuts,
            'notifications' => $notifications
        ]);
    }
    
    /**
     * Display dashboard based on user role
     */
    public function index() {
        $this->requireAuth();
        
        if ($this->hasRole('admin')) {
            $this->adminDashboard();
        } else if ($this->hasRole('staff')) {
            $this->staffDashboard();
        } else {
            $this->studentDashboard();
        }
    }
    
    /**
     * Mark notification as read
     */
    public function markNotificationRead($id) {
        $this->requireAuth();
        
        $userId = $_SESSION['user']['id'];
        $result = $this->userModel->markNotificationAsRead($id, $userId);
        
        // If AJAX request
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            if ($result) {
                $this->jsonResponse(['success' => true]);
            } else {
                $this->jsonError('Failed to mark notification as read');
            }
        } else {
            if ($result) {
                Util::setFlash('success', 'Notification marked as read');
            } else {
                Util::setFlash('error', 'Failed to mark notification as read');
            }
            
            Util::redirect('/dashboard');
        }
    }
}
