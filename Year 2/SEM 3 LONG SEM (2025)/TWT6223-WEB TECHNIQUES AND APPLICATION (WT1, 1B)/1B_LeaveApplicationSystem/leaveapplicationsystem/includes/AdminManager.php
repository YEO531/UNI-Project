<?php
require_once 'config.php';

class AdminManager {
    private $conn;
    private $admin_id;

    public function __construct($conn, $admin_id) {
        $this->conn = $conn;
        $this->admin_id = $admin_id;
    }

    /**
     * Log admin action
     */
    public function logAction($action_type, $target_type, $target_id = null, $details = '') {
        $stmt = $this->conn->prepare("
            INSERT INTO admin_actions (admin_id, action_type, target_type, target_id, details, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $stmt->bind_param("ississs", 
            $this->admin_id, 
            $action_type, 
            $target_type, 
            $target_id, 
            $details, 
            $ip_address, 
            $user_agent
        );
        
        return $stmt->execute();
    }

    /**
     * Get admin setting
     */
    public function getSetting($setting_key, $default = null) {
        $stmt = $this->conn->prepare("
            SELECT setting_value, setting_type 
            FROM admin_settings 
            WHERE setting_key = ?
        ");
        $stmt->bind_param("s", $setting_key);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $value = $row['setting_value'];
            
            // Convert based on setting type
            switch ($row['setting_type']) {
                case 'boolean':
                    return (bool)$value;
                case 'integer':
                    return (int)$value;
                case 'json':
                    return json_decode($value, true);
                default:
                    return $value;
            }
        }
        
        return $default;
    }

    /**
     * Set admin setting
     */
    public function setSetting($setting_key, $setting_value, $setting_type = 'string') {
        $stmt = $this->conn->prepare("
            INSERT INTO admin_settings (setting_key, setting_value, setting_type, updated_by)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            setting_value = VALUES(setting_value),
            updated_by = VALUES(updated_by),
            updated_at = CURRENT_TIMESTAMP
        ");
        
        $stmt->bind_param("sssi", $setting_key, $setting_value, $setting_type, $this->admin_id);
        return $stmt->execute();
    }

    /**
     * Check if admin has permission
     */
    public function hasPermission($permission_type) {
        $stmt = $this->conn->prepare("
            SELECT is_granted 
            FROM admin_permissions 
            WHERE admin_id = ? AND permission_type = ? 
            AND (expires_at IS NULL OR expires_at > NOW())
        ");
        $stmt->bind_param("is", $this->admin_id, $permission_type);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return (bool)$row['is_granted'];
        }
        
        return false;
    }

    /**
     * Grant permission to admin
     */
    public function grantPermission($admin_id, $permission_type, $expires_at = null) {
        $stmt = $this->conn->prepare("
            INSERT INTO admin_permissions (admin_id, permission_type, granted_by, expires_at)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            is_granted = 1,
            granted_by = VALUES(granted_by),
            expires_at = VALUES(expires_at)
        ");
        
        $stmt->bind_param("isis", $admin_id, $permission_type, $this->admin_id, $expires_at);
        return $stmt->execute();
    }

    /**
     * Revoke permission from admin
     */
    public function revokePermission($admin_id, $permission_type) {
        $stmt = $this->conn->prepare("
            UPDATE admin_permissions 
            SET is_granted = 0, granted_by = ?
            WHERE admin_id = ? AND permission_type = ?
        ");
        
        $stmt->bind_param("iis", $this->admin_id, $admin_id, $permission_type);
        return $stmt->execute();
    }

    /**
     * Get admin dashboard widgets
     */
    public function getDashboardWidgets() {
        $stmt = $this->conn->prepare("
            SELECT * FROM admin_dashboard_widgets 
            WHERE admin_id = ? AND is_visible = 1 
            ORDER BY position ASC
        ");
        $stmt->bind_param("i", $this->admin_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Update dashboard widget
     */
    public function updateDashboardWidget($widget_id, $data) {
        $stmt = $this->conn->prepare("
            UPDATE admin_dashboard_widgets 
            SET widget_title = ?, widget_config = ?, position = ?, is_visible = ?, is_collapsed = ?
            WHERE id = ? AND admin_id = ?
        ");
        
        $widget_config = json_encode($data['widget_config'] ?? []);
        $stmt->bind_param("ssiiiii", 
            $data['widget_title'], 
            $widget_config, 
            $data['position'], 
            $data['is_visible'], 
            $data['is_collapsed'], 
            $widget_id, 
            $this->admin_id
        );
        
        return $stmt->execute();
    }

    /**
     * Create admin notification
     */
    public function createNotification($notification_type, $title, $message, $priority = 'medium', $action_url = null) {
        $stmt = $this->conn->prepare("
            INSERT INTO admin_notifications (admin_id, notification_type, title, message, priority, action_url)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param("isssss", 
            $this->admin_id, 
            $notification_type, 
            $title, 
            $message, 
            $priority, 
            $action_url
        );
        
        return $stmt->execute();
    }

    /**
     * Get unread notifications
     */
    public function getUnreadNotifications($limit = 10) {
        $stmt = $this->conn->prepare("
            SELECT * FROM admin_notifications 
            WHERE admin_id = ? AND is_read = 0 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->bind_param("ii", $this->admin_id, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Mark notification as read
     */
    public function markNotificationAsRead($notification_id) {
        $stmt = $this->conn->prepare("
            UPDATE admin_notifications 
            SET is_read = 1 
            WHERE id = ? AND admin_id = ?
        ");
        $stmt->bind_param("ii", $notification_id, $this->admin_id);
        return $stmt->execute();
    }

    /**
     * Create bulk action record
     */
    public function createBulkAction($action_type, $target_count, $parameters = []) {
        $stmt = $this->conn->prepare("
            INSERT INTO admin_bulk_actions (admin_id, action_type, target_count, parameters)
            VALUES (?, ?, ?, ?)
        ");
        
        $params_json = json_encode($parameters);
        $stmt->bind_param("isis", $this->admin_id, $action_type, $target_count, $params_json);
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }

    /**
     * Update bulk action progress
     */
    public function updateBulkAction($bulk_action_id, $completed_count, $failed_count, $status, $result_summary = null) {
        $stmt = $this->conn->prepare("
            UPDATE admin_bulk_actions 
            SET completed_count = ?, failed_count = ?, status = ?, result_summary = ?,
                completed_at = CASE WHEN ? IN ('completed', 'failed', 'cancelled') THEN NOW() ELSE NULL END
            WHERE id = ? AND admin_id = ?
        ");
        
        $completed_at = ($status === 'completed' || $status === 'failed' || $status === 'cancelled') ? 1 : 0;
        $stmt->bind_param("iissiii", $completed_count, $failed_count, $status, $result_summary, $completed_at, $bulk_action_id, $this->admin_id);
        return $stmt->execute();
    }

    /**
     * Get admin statistics
     */
    public function getAdminStatistics() {
        $stats = [];
        
        // Total applications
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM leave_applications");
        $stmt->execute();
        $stats['total_applications'] = $stmt->get_result()->fetch_assoc()['total'];
        
        // Pending applications
        $stmt = $this->conn->prepare("SELECT COUNT(*) as pending FROM leave_applications WHERE status = 'pending'");
        $stmt->execute();
        $stats['pending_applications'] = $stmt->get_result()->fetch_assoc()['pending'];
        
        // Approved applications
        $stmt = $this->conn->prepare("SELECT COUNT(*) as approved FROM leave_applications WHERE status = 'approved'");
        $stmt->execute();
        $stats['approved_applications'] = $stmt->get_result()->fetch_assoc()['approved'];
        
        // Rejected applications
        $stmt = $this->conn->prepare("SELECT COUNT(*) as rejected FROM leave_applications WHERE status = 'rejected'");
        $stmt->execute();
        $stats['rejected_applications'] = $stmt->get_result()->fetch_assoc()['rejected'];
        
        // Total users
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total_users FROM users");
        $stmt->execute();
        $stats['total_users'] = $stmt->get_result()->fetch_assoc()['total_users'];
        
        // Recent admin actions
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as recent_actions 
            FROM admin_actions 
            WHERE admin_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        $stmt->bind_param("i", $this->admin_id);
        $stmt->execute();
        $stats['recent_actions'] = $stmt->get_result()->fetch_assoc()['recent_actions'];
        
        return $stats;
    }

    /**
     * Get recent admin actions
     */
    public function getRecentActions($limit = 10) {
        $stmt = $this->conn->prepare("
            SELECT aa.*, u.first_name, u.last_name 
            FROM admin_actions aa
            JOIN users u ON aa.admin_id = u.id
            ORDER BY aa.created_at DESC 
            LIMIT ?
        ");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get admin reports
     */
    public function getAdminReports($limit = 20) {
        $stmt = $this->conn->prepare("
            SELECT ar.*, u.first_name, u.last_name 
            FROM admin_reports ar
            JOIN users u ON ar.generated_by = u.id
            WHERE ar.generated_by = ?
            ORDER BY ar.created_at DESC 
            LIMIT ?
        ");
        $stmt->bind_param("ii", $this->admin_id, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Create admin report
     */
    public function createReport($report_name, $report_type, $parameters = [], $file_path = null, $file_size = null) {
        $stmt = $this->conn->prepare("
            INSERT INTO admin_reports (report_name, report_type, generated_by, parameters, file_path, file_size)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $params_json = json_encode($parameters);
        $stmt->bind_param("ssissi", $report_name, $report_type, $this->admin_id, $params_json, $file_path, $file_size);
        
        if ($stmt->execute()) {
            $report_id = $this->conn->insert_id;
            $this->logAction('report_generated', 'report', $report_id, "Generated report: $report_name");
            return $report_id;
        }
        return false;
    }

    /**
     * Update report download count
     */
    public function incrementReportDownload($report_id) {
        $stmt = $this->conn->prepare("
            UPDATE admin_reports 
            SET download_count = download_count + 1 
            WHERE id = ? AND generated_by = ?
        ");
        $stmt->bind_param("ii", $report_id, $this->admin_id);
        return $stmt->execute();
    }

    /**
     * Get admin permissions
     */
    public function getAdminPermissions($admin_id = null) {
        $admin_id = $admin_id ?? $this->admin_id;
        
        $stmt = $this->conn->prepare("
            SELECT permission_type, is_granted, granted_at, expires_at
            FROM admin_permissions 
            WHERE admin_id = ?
            ORDER BY permission_type
        ");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get all admin settings
     */
    public function getAllSettings() {
        $stmt = $this->conn->prepare("
            SELECT * FROM admin_settings 
            ORDER BY category, setting_key
        ");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Clean up old data
     */
    public function cleanupOldData() {
        // Clean up old reports
        $retention_days = $this->getSetting('report_retention_days', 90);
        $stmt = $this->conn->prepare("
            DELETE FROM admin_reports 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
        ");
        $stmt->bind_param("i", $retention_days);
        $stmt->execute();
        
        // Clean up old audit logs
        $audit_retention_days = $this->getSetting('audit_log_retention_days', 365);
        $stmt = $this->conn->prepare("
            DELETE FROM admin_actions 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
        ");
        $stmt->bind_param("i", $audit_retention_days);
        $stmt->execute();
        
        // Clean up old notifications
        $stmt = $this->conn->prepare("
            DELETE FROM admin_notifications 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY) AND is_read = 1
        ");
        $stmt->execute();
    }
}
?> 