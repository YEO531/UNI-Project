<?php
class Database {
    private $pdo;
    private static $instance = null;

    private function __construct() {
        require_once __DIR__ . '/../includes/config.php';
        $this->pdo = $pdo;
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }

    // User related methods
    public function getUserByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function getUserById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function createUser($data) {
        $sql = "INSERT INTO users (employee_id, first_name, last_name, email, password, department, position, role) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['employee_id'],
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['password'],
            $data['department'],
            $data['position'],
            $data['role'] ?? 'employee'
        ]);
    }

    public function updateUser($id, $data) {
        // Check if email is being changed and if it already exists
        if (isset($data['email'])) {
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$data['email'], $id]);
            if ($stmt->fetch()) {
                throw new Exception("Email already exists");
            }
        }

        // Build update query
        $updates = [];
        $params = [];

        foreach ($data as $key => $value) {
            if ($key !== 'id') {
                $updates[] = "`$key` = ?";
                $params[] = $value;
            }
        }

        if (empty($updates)) {
            throw new Exception("No fields to update");
        }

        $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
        $params[] = $id;

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    // Department related methods
    public function getAllDepartments() {
        $stmt = $this->pdo->query("SELECT * FROM departments ORDER BY name");
        return $stmt->fetchAll();
    }

    public function getDepartmentById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM departments WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Position related methods
    public function getPositionsByDepartment($departmentId) {
        $stmt = $this->pdo->prepare("SELECT * FROM positions WHERE department_id = ? ORDER BY title");
        $stmt->execute([$departmentId]);
        return $stmt->fetchAll();
    }

    // Leave type related methods
    public function getAllLeaveTypes() {
        $stmt = $this->pdo->query("SELECT * FROM leave_types ORDER BY name");
        return $stmt->fetchAll();
    }

    public function getLeaveTypeById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM leave_types WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Leave application related methods
    public function createLeaveApplication($data) {
        $sql = "INSERT INTO leave_applications (user_id, leave_type_id, start_date, end_date, total_days, reason) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['user_id'],
            $data['leave_type_id'],
            $data['start_date'],
            $data['end_date'],
            $data['total_days'],
            $data['reason']
        ]);
    }

    public function getLeaveApplicationsByUser($userId) {
        $stmt = $this->pdo->prepare("
            SELECT la.*, lt.name as leave_type_name, u.first_name, u.last_name 
            FROM leave_applications la
            JOIN leave_types lt ON la.leave_type_id = lt.id
            JOIN users u ON la.user_id = u.id
            WHERE la.user_id = ?
            ORDER BY la.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function updateLeaveApplicationStatus($id, $status, $approvedBy = null) {
        $sql = "UPDATE leave_applications SET status = ?, approved_by = ?, approved_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$status, $approvedBy, $id]);
    }

    // Leave balance related methods - Now calculated dynamically from leave_applications
    public function getLeaveBalance($userId, $leaveTypeId, $year) {
        // Get the maximum days allowed for this leave type
        $stmt = $this->pdo->prepare("SELECT max_days FROM leave_types WHERE id = ?");
        $stmt->execute([$leaveTypeId]);
        $leaveType = $stmt->fetch();
        
        if (!$leaveType) {
            return 0;
        }
        
        $maxDays = $leaveType['max_days'];
        
        // Calculate used days from approved leave applications for this year
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(SUM(total_days), 0) as used_days
            FROM leave_applications 
            WHERE user_id = ? AND leave_type_id = ? AND status = 'approved' AND YEAR(start_date) = ?
        ");
        $stmt->execute([$userId, $leaveTypeId, $year]);
        $result = $stmt->fetch();
        
        $usedDays = (int)$result['used_days'];
        $balance = $maxDays - $usedDays;
        
        // Ensure balance doesn't go negative
        return max(0, $balance);
    }

    public function updateLeaveBalance($userId, $leaveTypeId, $year, $usedDays) {
        // This method is no longer needed since balances are calculated dynamically
        // But keeping it for backward compatibility
        return true;
    }

    // Method to get leave balance for a user
    public function getLeaveBalanceForUser($userId, $leaveTypeId, $year) {
        $balance = $this->getLeaveBalance($userId, $leaveTypeId, $year);
        
        // Get the maximum days allowed for this leave type
        $stmt = $this->pdo->prepare("SELECT max_days FROM leave_types WHERE id = ?");
        $stmt->execute([$leaveTypeId]);
        $leaveType = $stmt->fetch();
        
        if (!$leaveType) {
            return null;
        }
        
        $maxDays = $leaveType['max_days'];
        
        // Calculate used days
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(SUM(total_days), 0) as used_days
            FROM leave_applications 
            WHERE user_id = ? AND leave_type_id = ? AND status = 'approved' AND YEAR(start_date) = ?
        ");
        $stmt->execute([$userId, $leaveTypeId, $year]);
        $result = $stmt->fetch();
        
        return [
            'total_days' => $maxDays,
            'used_days' => (int)$result['used_days'],
            'balance' => $balance
        ];
    }

    // Method to initialize leave balance for a user (no longer needed)
    public function initializeLeaveBalance($userId, $leaveTypeId, $totalDays, $year) {
        // This method is no longer needed since balances are calculated dynamically
        return true;
    }

    // System settings related methods
    public function getSystemSetting($key) {
        $stmt = $this->pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : null;
    }

    public function updateSystemSetting($key, $value) {
        $sql = "INSERT INTO system_settings (setting_key, setting_value) 
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE setting_value = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$key, $value, $value]);
    }

    // Audit log related methods
    public function logActivity($userId, $action, $details = null) {
        $sql = "INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $userId,
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    }

    // Notification related methods
    public function createNotification($userId, $title, $message, $type = 'info') {
        $sql = "INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$userId, $title, $message, $type]);
    }

    public function getUnreadNotifications($userId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM notifications 
            WHERE user_id = ? AND is_read = 0 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function markNotificationAsRead($notificationId) {
        $stmt = $this->pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
        return $stmt->execute([$notificationId]);
    }
} 