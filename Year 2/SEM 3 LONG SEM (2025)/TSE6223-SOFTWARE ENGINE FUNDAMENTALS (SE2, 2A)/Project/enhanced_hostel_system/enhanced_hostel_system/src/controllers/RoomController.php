<?php
/**
 * Room Controller
 * 
 * Handles room-related operations
 */
class RoomController extends Controller {
    private $roomModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        require_once __DIR__ . '/../models/Room.php';
        $this->roomModel = new Room();
    }
    
    /**
     * Display all rooms
     */
    public function index() {
        $this->requireAuth();
        
        // Get filter parameters
        $filters = [
            'category_id' => $_GET['category_id'] ?? null,
            'status' => $_GET['status'] ?? null,
            'type' => $_GET['type'] ?? null,
            'capacity_min' => $_GET['capacity_min'] ?? null,
            'capacity_max' => $_GET['capacity_max'] ?? null
        ];
        
        // Get rooms
        $rooms = $this->roomModel->getAll($filters);
        
        // Get room categories for filter
        $categories = $this->roomModel->getCategories();
        
        $this->render('rooms/index', [
            'rooms' => $rooms,
            'categories' => $categories,
            'filters' => $filters
        ]);
    }
    
    /**
     * Display room details
     */
    public function view($id) {
        $this->requireAuth();
        
        // Get room details
        $room = $this->roomModel->getById($id);
        
        if (!$room) {
            Util::setFlash('error', 'Room not found');
            Util::redirect('/rooms');
            return;
        }
        
        // Get room amenities
        $amenities = $this->roomModel->getRoomAmenities($id);
        
        $this->render('rooms/view', [
            'room' => $room,
            'amenities' => $amenities
        ]);
    }
    
    /**
     * Display room management page (admin only)
     */
    public function manage() {
        $this->requireRole('admin');
        
        // Get all rooms
        $rooms = $this->roomModel->getAll();
        
        // Get room categories
        $categories = $this->roomModel->getCategories();
        
        $this->render('admin/rooms/manage', [
            'rooms' => $rooms,
            'categories' => $categories
        ]);
    }
    
    /**
     * Display add room form (admin only)
     */
    public function add() {
        $this->requireRole('admin');
        
        // Get room categories
        $categories = $this->roomModel->getCategories();
        
        $this->render('admin/rooms/add', [
            'categories' => $categories
        ]);
    }
    
    /**
     * Process add room form (admin only)
     */
    public function processAdd() {
        $this->requireRole('admin');
        $this->requireCsrfToken('/admin/rooms/add');
        
        // Get form data
        $roomData = [
            'category_id' => $_POST['category_id'] ?? null,
            'room_number' => $_POST['room_number'] ?? '',
            'type' => $_POST['type'] ?? '',
            'capacity' => $_POST['capacity'] ?? 0,
            'price_per_day' => $_POST['price_per_day'] ?? 0,
            'description' => $_POST['description'] ?? '',
            'features' => $_POST['features'] ?? '',
            'status' => 'available'
        ];
        
        // Validate input
        $errors = $this->validate($roomData, [
            'category_id' => 'required|numeric',
            'room_number' => 'required|max:20',
            'type' => 'required|max:180',
            'capacity' => 'required|numeric',
            'price_per_day' => 'required|numeric'
        ]);
        
        if (!empty($errors)) {
            Util::setFlash('error', 'Please correct the errors in the form');
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $roomData;
            Util::redirect('/admin/rooms/add');
            return;
        }
        
        // Add room
        $roomId = $this->roomModel->add($roomData);
        
        if (!$roomId) {
            Util::setFlash('error', 'Failed to add room');
            $_SESSION['form_data'] = $roomData;
            Util::redirect('/admin/rooms/add');
            return;
        }
        
        // Handle room image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $result = $this->roomModel->uploadImage($roomId, $_FILES['image']);
            
            if (is_string($result)) {
                Util::setFlash('warning', 'Room added but image upload failed: ' . $result);
                Util::redirect('/admin/rooms/manage');
                return;
            }
        }
        
        // Log activity
        $this->logActivity('room_added', 'room', $roomId);
        
        Util::setFlash('success', 'Room added successfully');
        Util::redirect('/admin/rooms/manage');
    }
    
    /**
     * Display edit room form (admin only)
     */
    public function edit($id) {
        $this->requireRole('admin');
        
        // Get room details
        $room = $this->roomModel->getById($id);
        
        if (!$room) {
            Util::setFlash('error', 'Room not found');
            Util::redirect('/admin/rooms/manage');
            return;
        }
        
        // Get room categories
        $categories = $this->roomModel->getCategories();
        
        $this->render('admin/rooms/edit', [
            'room' => $room,
            'categories' => $categories
        ]);
    }
    
    /**
     * Process edit room form (admin only)
     */
    public function processEdit($id) {
        $this->requireRole('admin');
        $this->requireCsrfToken('/admin/rooms/manage');
        
        // Get room details
        $room = $this->roomModel->getById($id);
        
        if (!$room) {
            Util::setFlash('error', 'Room not found');
            Util::redirect('/admin/rooms/manage');
            return;
        }
        
        // Get form data
        $roomData = [
            'category_id' => $_POST['category_id'] ?? $room['category_id'],
            'room_number' => $_POST['room_number'] ?? $room['room_number'],
            'type' => $_POST['type'] ?? $room['type'],
            'capacity' => $_POST['capacity'] ?? $room['capacity'],
            'price_per_day' => $_POST['price_per_day'] ?? $room['price_per_day'],
            'description' => $_POST['description'] ?? $room['description'],
            'features' => $_POST['features'] ?? $room['features'],
            'status' => $_POST['status'] ?? $room['status']
        ];
        
        // Validate input
        $errors = $this->validate($roomData, [
            'category_id' => 'required|numeric',
            'room_number' => 'required|max:20',
            'type' => 'required|max:180',
            'capacity' => 'required|numeric',
            'price_per_day' => 'required|numeric'
        ]);
        
        if (!empty($errors)) {
            Util::setFlash('error', 'Please correct the errors in the form');
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $roomData;
            Util::redirect('/admin/rooms/edit/' . $id);
            return;
        }
        
        // Update room
        $result = $this->roomModel->update($id, $roomData);
        
        if (!$result) {
            Util::setFlash('error', 'Failed to update room');
            $_SESSION['form_data'] = $roomData;
            Util::redirect('/admin/rooms/edit/' . $id);
            return;
        }
        
        // Handle room image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $result = $this->roomModel->uploadImage($id, $_FILES['image']);
            
            if (is_string($result)) {
                Util::setFlash('warning', 'Room updated but image upload failed: ' . $result);
                Util::redirect('/admin/rooms/manage');
                return;
            }
        }
        
        // Log activity
        $this->logActivity('room_updated', 'room', $id);
        
        Util::setFlash('success', 'Room updated successfully');
        Util::redirect('/admin/rooms/manage');
    }
    
    /**
     * Update room status (admin only)
     */
    public function updateStatus($id) {
        $this->requireRole('admin');
        $this->requireCsrfToken('/admin/rooms/manage');
        
        $status = $_POST['status'] ?? '';
        
        if (empty($status)) {
            Util::setFlash('error', 'Status is required');
            Util::redirect('/admin/rooms/manage');
            return;
        }
        
        // Update room status
        $result = $this->roomModel->updateStatus($id, $status);
        
        if (!$result) {
            Util::setFlash('error', 'Failed to update room status');
            Util::redirect('/admin/rooms/manage');
            return;
        }
        
        // Log activity
        $this->logActivity('room_status_updated', 'room', $id);
        
        Util::setFlash('success', 'Room status updated successfully');
        Util::redirect('/admin/rooms/manage');
    }
    
    /**
     * Delete room (admin only)
     */
    public function delete($id) {
        $this->requireRole('admin');
        $this->requireCsrfToken('/admin/rooms/manage');
        
        // Delete room
        $result = $this->roomModel->delete($id);
        
        if (!$result) {
            Util::setFlash('error', 'Failed to delete room. It may have associated bookings.');
            Util::redirect('/admin/rooms/manage');
            return;
        }
        
        // Log activity
        $this->logActivity('room_deleted', 'room', $id);
        
        Util::setFlash('success', 'Room deleted successfully');
        Util::redirect('/admin/rooms/manage');
    }
    
    /**
     * Display available rooms for booking
     */
    public function available() {
        $this->requireAuth();
        
        // Get filter parameters
        $checkIn = $_GET['check_in'] ?? date('Y-m-d');
        $checkOut = $_GET['check_out'] ?? date('Y-m-d', strtotime('+1 day'));
        
        $filters = [
            'category_id' => $_GET['category_id'] ?? null,
            'type' => $_GET['type'] ?? null,
            'capacity_min' => $_GET['capacity_min'] ?? null
        ];
        
        // Get available rooms
        $rooms = $this->roomModel->getAvailableRooms($checkIn, $checkOut, $filters);
        
        // Get room categories for filter
        $categories = $this->roomModel->getCategories();
        
        $this->render('rooms/available', [
            'rooms' => $rooms,
            'categories' => $categories,
            'filters' => $filters,
            'check_in' => $checkIn,
            'check_out' => $checkOut
        ]);
    }
}
