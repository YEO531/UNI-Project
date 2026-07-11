<?php
/**
 * User Model
 * 
 * Handles user-related operations
 */
class User {
    private $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        require_once __DIR__ . '/Database.php';
        $this->db = Database::getInstance();
    }
    
    /**
     * Get user by ID
     * 
     * @param int $id User ID
     * @return array|false User data
     */
    public function getById($id) {
        return $this->db->fetchOne(
            "SELECT * FROM users WHERE id = ?", 
            [$id]
        );
    }
    
    /**
     * Get user by email
     * 
     * @param string $email User email
     * @return array|false User data
     */
    public function getByEmail($email) {
        return $this->db->fetchOne(
            "SELECT * FROM users WHERE email = ?", 
            [$email]
        );
    }
    
    /**
     * Get all users
     * 
     * @param string $role Filter by role (optional)
     * @return array Users
     */
    public function getAll($role = null) {
        if ($role) {
            return $this->db->fetchAll(
                "SELECT * FROM users WHERE role = ? ORDER BY name", 
                [$role]
            );
        }
        
        return $this->db->fetchAll(
            "SELECT * FROM users ORDER BY name"
        );
    }
    
    /**
     * Update user profile
     * 
     * @param int $id User ID
     * @param array $data User data to update
     * @return bool Success status
     */
    public function updateProfile($id, $data) {
        // Remove sensitive fields that shouldn't be updated directly
        unset($data['id'], $data['password_hash'], $data['role']);
        
        try {
            $this->db->update('users', $data, 'id = ?', [$id]);
            return true;
        } catch (Exception $e) {
            error_log('Update profile error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Change user password
     * 
     * @param int $id User ID
     * @param string $currentPassword Current password
     * @param string $newPassword New password
     * @return bool|string Success status or error message
     */
    public function changePassword($id, $currentPassword, $newPassword) {
        // Get current user
        $user = $this->getById($id);
        if (!$user) {
            return 'User not found';
        }
        
        // Verify current password
        if (!password_verify($currentPassword, $user['password_hash'])) {
            return 'Current password is incorrect';
        }
        
        // Validate new password
        if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
            return 'New password must be at least ' . PASSWORD_MIN_LENGTH . ' characters';
        }
        
        // Update password
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        
        try {
            $this->db->update('users', 
                ['password_hash' => $passwordHash],
                'id = ?', 
                [$id]
            );
            return true;
        } catch (Exception $e) {
            error_log('Change password error: ' . $e->getMessage());
            return 'System error occurred';
        }
    }
    
    /**
     * Upload profile image
     * 
     * @param int $id User ID
     * @param array $file Uploaded file data
     * @return bool|string Success status or error message
     */
    public function uploadProfileImage($id, $file) {
        // Check if file is valid
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return 'Invalid file upload';
        }
        
        // Check file size
        if ($file['size'] > MAX_UPLOAD_SIZE) {
            return 'File size exceeds limit';
        }
        
        // Check file type
        $fileInfo = pathinfo($file['name']);
        $extension = strtolower($fileInfo['extension']);
        
        if (!in_array($extension, ALLOWED_EXTENSIONS)) {
            return 'File type not allowed';
        }
        
        // Generate unique filename
        $filename = 'profile_' . $id . '_' . time() . '.' . $extension;
        $uploadPath = UPLOAD_DIR . 'profiles/';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $uploadPath . $filename)) {
            return 'Failed to save file';
        }
        
        // Update user profile
        try {
            $this->db->update('users', 
                ['profile_image' => 'uploads/profiles/' . $filename],
                'id = ?', 
                [$id]
            );
            return true;
        } catch (Exception $e) {
            error_log('Upload profile image error: ' . $e->getMessage());
            return 'System error occurred';
        }
    }
    
    /**
     * Add notification for user
     * 
     * @param int $userId User ID
     * @param string $title Notification title
     * @param string $message Notification message
     * @param string $type Notification type
     * @return int|bool Notification ID or false on failure
     */
    public function addNotification($userId, $title, $message, $type = 'info') {
        try {
            return $this->db->insert('notifications', [
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'type' => $type
            ]);
        } catch (Exception $e) {
            error_log('Add notification error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user notifications
     * 
     * @param int $userId User ID
     * @param bool $unreadOnly Get only unread notifications
     * @return array Notifications
     */
    public function getNotifications($userId, $unreadOnly = false) {
        $sql = "SELECT * FROM notifications WHERE user_id = ?";
        $params = [$userId];
        
        if ($unreadOnly) {
            $sql .= " AND is_read = 0";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Mark notification as read
     * 
     * @param int $notificationId Notification ID
     * @param int $userId User ID (for security)
     * @return bool Success status
     */
    public function markNotificationAsRead($notificationId, $userId) {
        try {
            $this->db->update('notifications', 
                ['is_read' => 1],
                'id = ? AND user_id = ?', 
                [$notificationId, $userId]
            );
            return true;
        } catch (Exception $e) {
            error_log('Mark notification error: ' . $e->getMessage());
            return false;
        }
    }
}
