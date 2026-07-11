<?php
/**
 * API Controller
 * 
 * Handles API endpoints for mobile and frontend integration
 */
class ApiController extends Controller {
    private $roomModel;
    private $bookingModel;
    private $userModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        require_once __DIR__ . '/../models/Room.php';
        require_once __DIR__ . '/../models/Booking.php';
        require_once __DIR__ . '/../models/User.php';
        $this->roomModel = new Room();
        $this->bookingModel = new Booking();
        $this->userModel = new User();
        
        // Set content type for all API responses
        header('Content-Type: application/json');
    }
    
    /**
     * API authentication check
     */
    private function requireApiAuth() {
        // Check for Authorization header
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';
        
        // Extract token
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
            
            // Verify token (simplified for demo)
            if ($this->verifyApiToken($token)) {
                return true;
            }
        }
        
        // Authentication failed
        $this->jsonError('Unauthorized', 401);
        return false;
    }
    
    /**
     * Verify API token
     * 
     * @param string $token API token
     * @return bool
     */
    private function verifyApiToken($token) {
        // In a real application, this would verify the token against a database
        // For demo purposes, we'll accept a hardcoded token
        return $token === 'demo_api_token';
    }
    
    /**
     * Get rooms API endpoint
     */
    public function getRooms() {
        $this->requireApiAuth();
        
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
        
        // Return response
        $this->jsonResponse([
            'success' => true,
            'data' => $rooms
        ]);
    }
    
    /**
     * Get available rooms API endpoint
     */
    public function getAvailableRooms() {
        $this->requireApiAuth();
        
        // Get filter parameters
        $checkIn = $_GET['check_in'] ?? date('Y-m-d');
        $checkOut = $_GET['check_out'] ?? date('Y-m-d', strtotime('+1 day'));
        
        $filters = [
            'category_id' => $_GET['category_id'] ?? null,
            'type' => $_GET['type'] ?? null,
            'capacity_min' => $_GET['capacity_min'] ?? null
        ];
        
        // Validate dates
        if (!strtotime($checkIn) || !strtotime($checkOut)) {
            $this->jsonError('Invalid date format');
            return;
        }
        
        // Get available rooms
        $rooms = $this->roomModel->getAvailableRooms($checkIn, $checkOut, $filters);
        
        // Return response
        $this->jsonResponse([
            'success' => true,
            'data' => $rooms
        ]);
    }
    
    /**
     * Get room details API endpoint
     */
    public function getRoom($id) {
        $this->requireApiAuth();
        
        // Get room details
        $room = $this->roomModel->getById($id);
        
        if (!$room) {
            $this->jsonError('Room not found', 404);
            return;
        }
        
        // Get room amenities
        $amenities = $this->roomModel->getRoomAmenities($id);
        
        // Return response
        $this->jsonResponse([
            'success' => true,
            'data' => [
                'room' => $room,
                'amenities' => $amenities
            ]
        ]);
    }
    
    /**
     * Get bookings API endpoint
     */
    public function getBookings() {
        $this->requireApiAuth();
        
        // Get user ID from token (in a real app)
        // For demo, we'll use a hardcoded ID
        $userId = 1;
        
        // Get bookings
        $bookings = $this->bookingModel->getByStudentId($userId);
        
        // Return response
        $this->jsonResponse([
            'success' => true,
            'data' => $bookings
        ]);
    }
    
    /**
     * Get booking details API endpoint
     */
    public function getBooking($id) {
        $this->requireApiAuth();
        
        // Get user ID from token (in a real app)
        // For demo, we'll use a hardcoded ID
        $userId = 1;
        
        // Get booking details
        $booking = $this->bookingModel->getById($id);
        
        if (!$booking) {
            $this->jsonError('Booking not found', 404);
            return;
        }
        
        // Check if user has permission to view this booking
        if ($booking['student_id'] != $userId) {
            $this->jsonError('You do not have permission to view this booking', 403);
            return;
        }
        
        // Return response
        $this->jsonResponse([
            'success' => true,
            'data' => $booking
        ]);
    }
    
    /**
     * Create booking API endpoint
     */
    public function createBooking() {
        $this->requireApiAuth();
        
        // Get user ID from token (in a real app)
        // For demo, we'll use a hardcoded ID
        $userId = 1;
        
        // Get request body
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            $this->jsonError('Invalid request data');
            return;
        }
        
        $roomId = $data['room_id'] ?? '';
        $checkIn = $data['check_in'] ?? '';
        $checkOut = $data['check_out'] ?? '';
        $notes = $data['notes'] ?? '';
        
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
            $this->jsonError('Validation failed', 400, $errors);
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
            $this->jsonError('Room is not available for the selected dates', 400);
            return;
        }
        
        // Create booking
        $bookingData = [
            'student_id' => $userId,
            'room_id' => $roomId,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'notes' => $notes,
            'status' => 'pending',
            'payment_status' => 'unpaid'
        ];
        
        $bookingId = $this->bookingModel->create($bookingData);
        
        if (!$bookingId) {
            $this->jsonError('Failed to create booking', 500);
            return;
        }
        
        // Get created booking
        $booking = $this->bookingModel->getById($bookingId);
        
        // Return response
        $this->jsonResponse([
            'success' => true,
            'message' => 'Booking created successfully',
            'data' => $booking
        ], 201);
    }
    
    /**
     * Cancel booking API endpoint
     */
    public function cancelBooking($id) {
        $this->requireApiAuth();
        
        // Get user ID from token (in a real app)
        // For demo, we'll use a hardcoded ID
        $userId = 1;
        
        // Get request body
        $data = json_decode(file_get_contents('php://input'), true);
        $reason = $data['reason'] ?? '';
        
        // Cancel booking
        $result = $this->bookingModel->cancel($id, $userId, $reason);
        
        if (!$result) {
            $this->jsonError('Failed to cancel booking', 400);
            return;
        }
        
        // Return response
        $this->jsonResponse([
            'success' => true,
            'message' => 'Booking cancelled successfully'
        ]);
    }
    
    /**
     * Get user profile API endpoint
     */
    public function getUserProfile() {
        $this->requireApiAuth();
        
        // Get user ID from token (in a real app)
        // For demo, we'll use a hardcoded ID
        $userId = 1;
        
        // Get user details
        $user = $this->userModel->getById($userId);
        
        if (!$user) {
            $this->jsonError('User not found', 404);
            return;
        }
        
        // Remove sensitive data
        unset($user['password_hash']);
        unset($user['reset_token']);
        unset($user['reset_token_expires']);
        
        // Return response
        $this->jsonResponse([
            'success' => true,
            'data' => $user
        ]);
    }
    
    /**
     * Update user profile API endpoint
     */
    public function updateUserProfile() {
        $this->requireApiAuth();
        
        // Get user ID from token (in a real app)
        // For demo, we'll use a hardcoded ID
        $userId = 1;
        
        // Get request body
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            $this->jsonError('Invalid request data');
            return;
        }
        
        // Validate input
        $errors = $this->validate(
            [
                'name' => $data['name'] ?? '',
                'email' => $data['email'] ?? '',
                'phone' => $data['phone'] ?? ''
            ],
            [
                'name' => 'required|max:180',
                'email' => 'required|email|max:180',
                'phone' => 'max:14'
            ]
        );
        
        if (!empty($errors)) {
            $this->jsonError('Validation failed', 400, $errors);
            return;
        }
        
        // Update user profile
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone']
        ];
        
        $result = $this->userModel->updateProfile($userId, $userData);
        
        if (!$result) {
            $this->jsonError('Failed to update profile', 500);
            return;
        }
        
        // Get updated user
        $user = $this->userModel->getById($userId);
        
        // Remove sensitive data
        unset($user['password_hash']);
        unset($user['reset_token']);
        unset($user['reset_token_expires']);
        
        // Return response
        $this->jsonResponse([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $user
        ]);
    }
}
