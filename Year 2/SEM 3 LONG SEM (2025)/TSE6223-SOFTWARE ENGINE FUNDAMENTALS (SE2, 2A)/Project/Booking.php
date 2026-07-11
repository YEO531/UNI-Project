<?php
/**
 * Booking Model
 * 
 * Handles booking-related operations
 */
class Booking {
    private $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        require_once __DIR__ . '/Database.php';
        $this->db = Database::getInstance();
    }
    
    /**
     * Get booking by ID
     * 
     * @param int $id Booking ID
     * @return array|false Booking data
     */
    public function getById($id) {
        return $this->db->fetchOne(
            "SELECT b.*, r.type as room_type, r.room_number, u.name as student_name, u.email as student_email 
             FROM bookings b
             JOIN rooms r ON b.room_id = r.id
             JOIN users u ON b.student_id = u.id
             WHERE b.id = ?", 
            [$id]
        );
    }
    
    /**
     * Get bookings by student ID
     * 
     * @param int $studentId Student ID
     * @param string $status Filter by status (optional)
     * @return array Bookings
     */
    public function getByStudentId($studentId, $status = null) {
        $sql = "SELECT b.*, r.type as room_type, r.room_number, r.image as room_image
                FROM bookings b
                JOIN rooms r ON b.room_id = r.id
                WHERE b.student_id = ?";
        $params = [$studentId];
        
        if ($status) {
            $sql .= " AND b.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY b.check_in_date DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get all bookings
     * 
     * @param array $filters Optional filters
     * @return array Bookings
     */
    public function getAll($filters = []) {
        $sql = "SELECT b.*, r.type as room_type, r.room_number, u.name as student_name, u.email as student_email
                FROM bookings b
                JOIN rooms r ON b.room_id = r.id
                JOIN users u ON b.student_id = u.id";
        $params = [];
        $where = [];
        
        // Apply filters
        if (!empty($filters['status'])) {
            $where[] = "b.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['room_id'])) {
            $where[] = "b.room_id = ?";
            $params[] = $filters['room_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $where[] = "b.check_in_date >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "b.check_in_date <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['payment_status'])) {
            $where[] = "b.payment_status = ?";
            $params[] = $filters['payment_status'];
        }
        
        // Add WHERE clause if filters exist
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        // Add ordering
        $sql .= " ORDER BY b.check_in_date DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Create a new booking
     * 
     * @param array $data Booking data
     * @return int|bool Booking ID or false on failure
     */
    public function create($data) {
        try {
            $this->db->beginTransaction();
            
            // Generate booking number
            $data['booking_number'] = 'BK' . date('Ymd') . rand(1000, 9999);
            
            // Calculate total price
            $room = $this->db->fetchOne(
                "SELECT price_per_day FROM rooms WHERE id = ?", 
                [$data['room_id']]
            );
            
            $checkIn = new DateTime($data['check_in_date']);
            $checkOut = new DateTime($data['check_out_date']);
            $days = $checkIn->diff($checkOut)->days;
            
            $data['total_price'] = $room['price_per_day'] * $days;
            
            // Insert booking
            $bookingId = $this->db->insert('bookings', $data);
            
            // Update room status
            $this->db->update('rooms', 
                ['status' => 'booked'],
                'id = ?', 
                [$data['room_id']]
            );
            
            // Create notification for student
            $this->db->insert('notifications', [
                'user_id' => $data['student_id'],
                'title' => 'Booking Created',
                'message' => 'Your booking #' . $data['booking_number'] . ' has been created and is pending approval.',
                'type' => 'info'
            ]);
            
            // Create notification for admin
            $admins = $this->db->fetchAll(
                "SELECT id FROM users WHERE role = 'admin'"
            );
            
            foreach ($admins as $admin) {
                $this->db->insert('notifications', [
                    'user_id' => $admin['id'],
                    'title' => 'New Booking',
                    'message' => 'A new booking #' . $data['booking_number'] . ' has been created and requires approval.',
                    'type' => 'info'
                ]);
            }
            
            $this->db->commit();
            return $bookingId;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log('Create booking error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update booking status
     * 
     * @param int $id Booking ID
     * @param string $status New status
     * @param string $notes Optional notes
     * @return bool Success status
     */
    public function updateStatus($id, $status, $notes = null) {
        try {
            $this->db->beginTransaction();
            
            // Get booking details
            $booking = $this->getById($id);
            if (!$booking) {
                return false;
            }
            
            // Update booking
            $updateData = ['status' => $status];
            if ($notes !== null) {
                $updateData['notes'] = $notes;
            }
            
            $this->db->update('bookings', $updateData, 'id = ?', [$id]);
            
            // Update room status based on booking status
            if ($status === 'approved') {
                $this->db->update('rooms', 
                    ['status' => 'booked'],
                    'id = ?', 
                    [$booking['room_id']]
                );
            } else if ($status === 'rejected' || $status === 'cancelled') {
                $this->db->update('rooms', 
                    ['status' => 'available'],
                    'id = ?', 
                    [$booking['room_id']]
                );
            } else if ($status === 'completed') {
                $this->db->update('rooms', 
                    ['status' => 'available'],
                    'id = ?', 
                    [$booking['room_id']]
                );
            }
            
            // Create notification for student
            $this->db->insert('notifications', [
                'user_id' => $booking['student_id'],
                'title' => 'Booking ' . ucfirst($status),
                'message' => 'Your booking #' . $booking['booking_number'] . ' has been ' . $status . '.',
                'type' => $status === 'approved' ? 'success' : ($status === 'rejected' ? 'error' : 'info')
            ]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log('Update booking status error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update payment status
     * 
     * @param int $id Booking ID
     * @param string $paymentStatus New payment status
     * @return bool Success status
     */
    public function updatePaymentStatus($id, $paymentStatus) {
        try {
            $this->db->update('bookings', 
                ['payment_status' => $paymentStatus],
                'id = ?', 
                [$id]
            );
            return true;
        } catch (Exception $e) {
            error_log('Update payment status error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cancel booking
     * 
     * @param int $id Booking ID
     * @param int $studentId Student ID (for security)
     * @param string $reason Cancellation reason
     * @return bool Success status
     */
    public function cancel($id, $studentId, $reason = null) {
        try {
            $this->db->beginTransaction();
            
            // Get booking details
            $booking = $this->db->fetchOne(
                "SELECT * FROM bookings WHERE id = ? AND student_id = ?", 
                [$id, $studentId]
            );
            
            if (!$booking) {
                return false;
            }
            
            // Check if booking can be cancelled
            if ($booking['status'] !== 'pending' && $booking['status'] !== 'approved') {
                return false;
            }
            
            // Update booking
            $updateData = [
                'status' => 'cancelled',
                'notes' => $reason ? 'Cancelled by student. Reason: ' . $reason : 'Cancelled by student.'
            ];
            
            $this->db->update('bookings', $updateData, 'id = ?', [$id]);
            
            // Update room status
            $this->db->update('rooms', 
                ['status' => 'available'],
                'id = ?', 
                [$booking['room_id']]
            );
            
            // Create notification for admin
            $admins = $this->db->fetchAll(
                "SELECT id FROM users WHERE role = 'admin'"
            );
            
            foreach ($admins as $admin) {
                $this->db->insert('notifications', [
                    'user_id' => $admin['id'],
                    'title' => 'Booking Cancelled',
                    'message' => 'Booking #' . $booking['booking_number'] . ' has been cancelled by the student.',
                    'type' => 'warning'
                ]);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log('Cancel booking error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get booking statistics
     * 
     * @param string $period Period (daily, weekly, monthly, yearly)
     * @return array Statistics
     */
    public function getStatistics($period = 'monthly') {
        $sql = "";
        
        switch ($period) {
            case 'daily':
                $sql = "SELECT DATE(created_at) as date, COUNT(*) as count, 
                        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
                        FROM bookings
                        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                        GROUP BY DATE(created_at)
                        ORDER BY DATE(created_at)";
                break;
                
            case 'weekly':
                $sql = "SELECT YEAR(created_at) as year, WEEK(created_at) as week, COUNT(*) as count,
                        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
                        FROM bookings
                        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 WEEK)
                        GROUP BY YEAR(created_at), WEEK(created_at)
                        ORDER BY YEAR(created_at), WEEK(created_at)";
                break;
                
            case 'yearly':
                $sql = "SELECT YEAR(created_at) as year, COUNT(*) as count,
                        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
                        FROM bookings
                        GROUP BY YEAR(created_at)
                        ORDER BY YEAR(created_at)";
                break;
                
            case 'monthly':
            default:
                $sql = "SELECT YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count,
                        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
                        FROM bookings
                        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                        GROUP BY YEAR(created_at), MONTH(created_at)
                        ORDER BY YEAR(created_at), MONTH(created_at)";
                break;
        }
        
        return $this->db->fetchAll($sql);
    }
}
