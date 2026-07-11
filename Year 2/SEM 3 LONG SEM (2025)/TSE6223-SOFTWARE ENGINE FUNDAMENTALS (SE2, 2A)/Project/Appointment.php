<?php
/**
 * Appointment Model
 * 
 * Handles appointment-related operations
 */
class Appointment {
    private $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        require_once __DIR__ . '/Database.php';
        $this->db = Database::getInstance();
    }
    
    /**
     * Get appointment by ID
     * 
     * @param int $id Appointment ID
     * @return array|false Appointment data
     */
    public function getById($id) {
        return $this->db->fetchOne(
            "SELECT a.*, r.type as room_type, r.room_number, u.name as student_name, u.email as student_email 
             FROM appointments a
             JOIN rooms r ON a.room_id = r.id
             JOIN users u ON a.student_id = u.id
             WHERE a.id = ?", 
            [$id]
        );
    }
    
    /**
     * Get appointments by student ID
     * 
     * @param int $studentId Student ID
     * @param string $status Filter by status (optional)
     * @return array Appointments
     */
    public function getByStudentId($studentId, $status = null) {
        $sql = "SELECT a.*, r.type as room_type, r.room_number
                FROM appointments a
                JOIN rooms r ON a.room_id = r.id
                WHERE a.student_id = ?";
        $params = [$studentId];
        
        if ($status) {
            $sql .= " AND a.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY a.date DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get all appointments
     * 
     * @param array $filters Optional filters
     * @return array Appointments
     */
    public function getAll($filters = []) {
        $sql = "SELECT a.*, r.type as room_type, r.room_number, u.name as student_name, u.email as student_email
                FROM appointments a
                JOIN rooms r ON a.room_id = r.id
                JOIN users u ON a.student_id = u.id";
        $params = [];
        $where = [];
        
        // Apply filters
        if (!empty($filters['status'])) {
            $where[] = "a.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['room_id'])) {
            $where[] = "a.room_id = ?";
            $params[] = $filters['room_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $where[] = "DATE(a.date) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "DATE(a.date) <= ?";
            $params[] = $filters['date_to'];
        }
        
        // Add WHERE clause if filters exist
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        // Add ordering
        $sql .= " ORDER BY a.date DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Create a new appointment
     * 
     * @param array $data Appointment data
     * @return int|bool Appointment ID or false on failure
     */
    public function create($data) {
        try {
            $this->db->beginTransaction();
            
            // Generate appointment number
            $data['appointment_number'] = 'AP' . date('Ymd') . rand(1000, 9999);
            
            // Insert appointment
            $appointmentId = $this->db->insert('appointments', $data);
            
            // Create notification for student
            $this->db->insert('notifications', [
                'user_id' => $data['student_id'],
                'title' => 'Appointment Created',
                'message' => 'Your appointment #' . $data['appointment_number'] . ' has been created and is pending approval.',
                'type' => 'info'
            ]);
            
            // Create notification for admin
            $admins = $this->db->fetchAll(
                "SELECT id FROM users WHERE role = 'admin'"
            );
            
            foreach ($admins as $admin) {
                $this->db->insert('notifications', [
                    'user_id' => $admin['id'],
                    'title' => 'New Appointment',
                    'message' => 'A new appointment #' . $data['appointment_number'] . ' has been created and requires approval.',
                    'type' => 'info'
                ]);
            }
            
            $this->db->commit();
            return $appointmentId;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log('Create appointment error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update appointment status
     * 
     * @param int $id Appointment ID
     * @param string $status New status
     * @param string $notes Optional notes
     * @return bool Success status
     */
    public function updateStatus($id, $status, $notes = null) {
        try {
            $this->db->beginTransaction();
            
            // Get appointment details
            $appointment = $this->getById($id);
            if (!$appointment) {
                return false;
            }
            
            // Update appointment
            $updateData = ['status' => $status];
            if ($notes !== null) {
                $updateData['notes'] = $notes;
            }
            
            $this->db->update('appointments', $updateData, 'id = ?', [$id]);
            
            // Create notification for student
            $this->db->insert('notifications', [
                'user_id' => $appointment['student_id'],
                'title' => 'Appointment ' . ucfirst($status),
                'message' => 'Your appointment #' . $appointment['appointment_number'] . ' has been ' . $status . '.',
                'type' => $status === 'approved' ? 'success' : ($status === 'rejected' ? 'error' : 'info')
            ]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log('Update appointment status error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cancel appointment
     * 
     * @param int $id Appointment ID
     * @param int $studentId Student ID (for security)
     * @param string $reason Cancellation reason
     * @return bool Success status
     */
    public function cancel($id, $studentId, $reason = null) {
        try {
            $this->db->beginTransaction();
            
            // Get appointment details
            $appointment = $this->db->fetchOne(
                "SELECT * FROM appointments WHERE id = ? AND student_id = ?", 
                [$id, $studentId]
            );
            
            if (!$appointment) {
                return false;
            }
            
            // Check if appointment can be cancelled
            if ($appointment['status'] !== 'pending' && $appointment['status'] !== 'approved') {
                return false;
            }
            
            // Update appointment
            $updateData = [
                'status' => 'cancelled',
                'notes' => $reason ? 'Cancelled by student. Reason: ' . $reason : 'Cancelled by student.'
            ];
            
            $this->db->update('appointments', $updateData, 'id = ?', [$id]);
            
            // Create notification for admin
            $admins = $this->db->fetchAll(
                "SELECT id FROM users WHERE role = 'admin'"
            );
            
            foreach ($admins as $admin) {
                $this->db->insert('notifications', [
                    'user_id' => $admin['id'],
                    'title' => 'Appointment Cancelled',
                    'message' => 'Appointment #' . $appointment['appointment_number'] . ' has been cancelled by the student.',
                    'type' => 'warning'
                ]);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log('Cancel appointment error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if time slot is available
     * 
     * @param int $roomId Room ID
     * @param string $date Appointment date and time
     * @param int $excludeId Exclude this appointment ID (for updates)
     * @return bool True if available
     */
    public function isTimeSlotAvailable($roomId, $date, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count 
                FROM appointments 
                WHERE room_id = ? 
                AND status IN ('pending', 'approved') 
                AND DATE(date) = DATE(?) 
                AND ABS(TIME_TO_SEC(TIMEDIFF(date, ?))) < 3600"; // 1 hour buffer
        $params = [$roomId, $date, $date];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['count'] == 0;
    }
}
