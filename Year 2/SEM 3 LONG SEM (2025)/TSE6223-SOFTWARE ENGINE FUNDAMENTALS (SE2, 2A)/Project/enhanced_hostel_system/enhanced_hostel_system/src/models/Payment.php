<?php
/**
 * Payment Model
 * 
 * Handles payment-related operations
 */
class Payment {
    private $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        require_once __DIR__ . '/Database.php';
        $this->db = Database::getInstance();
    }
    
    /**
     * Get payment by ID
     * 
     * @param int $id Payment ID
     * @return array|false Payment data
     */
    public function getById($id) {
        return $this->db->fetchOne(
            "SELECT p.*, b.booking_number, u.name as student_name, u.email as student_email 
             FROM payments p
             JOIN bookings b ON p.booking_id = b.id
             JOIN users u ON p.student_id = u.id
             WHERE p.id = ?", 
            [$id]
        );
    }
    
    /**
     * Get payments by booking ID
     * 
     * @param int $bookingId Booking ID
     * @return array Payments
     */
    public function getByBookingId($bookingId) {
        return $this->db->fetchAll(
            "SELECT * FROM payments WHERE booking_id = ? ORDER BY payment_date DESC", 
            [$bookingId]
        );
    }
    
    /**
     * Get payments by student ID
     * 
     * @param int $studentId Student ID
     * @param string $status Filter by status (optional)
     * @return array Payments
     */
    public function getByStudentId($studentId, $status = null) {
        $sql = "SELECT p.*, b.booking_number 
                FROM payments p
                JOIN bookings b ON p.booking_id = b.id
                WHERE p.student_id = ?";
        $params = [$studentId];
        
        if ($status) {
            $sql .= " AND p.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY p.payment_date DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get all payments
     * 
     * @param array $filters Optional filters
     * @return array Payments
     */
    public function getAll($filters = []) {
        $sql = "SELECT p.*, b.booking_number, u.name as student_name, u.email as student_email
                FROM payments p
                JOIN bookings b ON p.booking_id = b.id
                JOIN users u ON p.student_id = u.id";
        $params = [];
        $where = [];
        
        // Apply filters
        if (!empty($filters['status'])) {
            $where[] = "p.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['method'])) {
            $where[] = "p.method = ?";
            $params[] = $filters['method'];
        }
        
        if (!empty($filters['date_from'])) {
            $where[] = "DATE(p.payment_date) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "DATE(p.payment_date) <= ?";
            $params[] = $filters['date_to'];
        }
        
        // Add WHERE clause if filters exist
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        // Add ordering
        $sql .= " ORDER BY p.payment_date DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Create a new payment
     * 
     * @param array $data Payment data
     * @return int|bool Payment ID or false on failure
     */
    public function create($data) {
        try {
            $this->db->beginTransaction();
            
            // Generate payment number
            $data['payment_number'] = 'PAY' . date('Ymd') . rand(1000, 9999);
            
            // Insert payment
            $paymentId = $this->db->insert('payments', $data);
            
            // Update booking payment status
            $this->updateBookingPaymentStatus($data['booking_id']);
            
            // Create notification for student
            $this->db->insert('notifications', [
                'user_id' => $data['student_id'],
                'title' => 'Payment Recorded',
                'message' => 'Your payment of $' . number_format($data['amount'], 2) . ' has been recorded and is ' . $data['status'] . '.',
                'type' => 'info'
            ]);
            
            $this->db->commit();
            return $paymentId;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log('Create payment error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update payment status
     * 
     * @param int $id Payment ID
     * @param string $status New status
     * @param string $notes Optional notes
     * @return bool Success status
     */
    public function updateStatus($id, $status, $notes = null) {
        try {
            $this->db->beginTransaction();
            
            // Get payment details
            $payment = $this->getById($id);
            if (!$payment) {
                return false;
            }
            
            // Update payment
            $updateData = ['status' => $status];
            if ($notes !== null) {
                $updateData['notes'] = $notes;
            }
            
            $this->db->update('payments', $updateData, 'id = ?', [$id]);
            
            // Update booking payment status
            $this->updateBookingPaymentStatus($payment['booking_id']);
            
            // Create notification for student
            $this->db->insert('notifications', [
                'user_id' => $payment['student_id'],
                'title' => 'Payment ' . ucfirst($status),
                'message' => 'Your payment of $' . number_format($payment['amount'], 2) . ' is now ' . $status . '.',
                'type' => $status === 'completed' ? 'success' : ($status === 'failed' ? 'error' : 'info')
            ]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log('Update payment status error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update booking payment status based on payments
     * 
     * @param int $bookingId Booking ID
     * @return bool Success status
     */
    private function updateBookingPaymentStatus($bookingId) {
        // Get booking total price
        $booking = $this->db->fetchOne(
            "SELECT total_price FROM bookings WHERE id = ?", 
            [$bookingId]
        );
        
        if (!$booking) {
            return false;
        }
        
        // Get total paid amount
        $paid = $this->db->fetchOne(
            "SELECT SUM(amount) as total FROM payments 
             WHERE booking_id = ? AND status = 'completed'", 
            [$bookingId]
        );
        
        $totalPaid = $paid['total'] ?? 0;
        
        // Determine payment status
        $paymentStatus = 'unpaid';
        if ($totalPaid >= $booking['total_price']) {
            $paymentStatus = 'paid';
        } else if ($totalPaid > 0) {
            $paymentStatus = 'partially_paid';
        }
        
        // Update booking
        $this->db->update('bookings', 
            ['payment_status' => $paymentStatus],
            'id = ?', 
            [$bookingId]
        );
        
        return true;
    }
    
    /**
     * Get payment statistics
     * 
     * @param string $period Period (daily, weekly, monthly, yearly)
     * @return array Statistics
     */
    public function getStatistics($period = 'monthly') {
        $sql = "";
        
        switch ($period) {
            case 'daily':
                $sql = "SELECT DATE(payment_date) as date, 
                        COUNT(*) as count, 
                        SUM(amount) as total,
                        SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as completed,
                        SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending,
                        SUM(CASE WHEN status = 'failed' THEN amount ELSE 0 END) as failed
                        FROM payments
                        WHERE payment_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                        GROUP BY DATE(payment_date)
                        ORDER BY DATE(payment_date)";
                break;
                
            case 'weekly':
                $sql = "SELECT YEAR(payment_date) as year, WEEK(payment_date) as week, 
                        COUNT(*) as count, 
                        SUM(amount) as total,
                        SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as completed,
                        SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending,
                        SUM(CASE WHEN status = 'failed' THEN amount ELSE 0 END) as failed
                        FROM payments
                        WHERE payment_date >= DATE_SUB(CURDATE(), INTERVAL 12 WEEK)
                        GROUP BY YEAR(payment_date), WEEK(payment_date)
                        ORDER BY YEAR(payment_date), WEEK(payment_date)";
                break;
                
            case 'yearly':
                $sql = "SELECT YEAR(payment_date) as year, 
                        COUNT(*) as count, 
                        SUM(amount) as total,
                        SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as completed,
                        SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending,
                        SUM(CASE WHEN status = 'failed' THEN amount ELSE 0 END) as failed
                        FROM payments
                        GROUP BY YEAR(payment_date)
                        ORDER BY YEAR(payment_date)";
                break;
                
            case 'monthly':
            default:
                $sql = "SELECT YEAR(payment_date) as year, MONTH(payment_date) as month, 
                        COUNT(*) as count, 
                        SUM(amount) as total,
                        SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as completed,
                        SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending,
                        SUM(CASE WHEN status = 'failed' THEN amount ELSE 0 END) as failed
                        FROM payments
                        WHERE payment_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                        GROUP BY YEAR(payment_date), MONTH(payment_date)
                        ORDER BY YEAR(payment_date), MONTH(payment_date)";
                break;
        }
        
        return $this->db->fetchAll($sql);
    }
}
