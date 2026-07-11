<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../includes/config.php';
require_once '../includes/AdminManager.php';

// Check if user is logged in and has admin privileges
if (!is_logged_in() || !in_array(get_user_role(), ['admin', 'super_admin'])) {
    http_response_code(403);
    echo json_encode(array("status" => "error", "message" => "Unauthorized access"));
    exit();
}

$admin_manager = new AdminManager($conn, $_SESSION['user_id']);
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch($method) {
    case 'GET':
        switch($action) {
            case 'statistics':
                $stats = $admin_manager->getAdminStatistics();
                echo json_encode(array("status" => "success", "data" => $stats));
                break;
                
            case 'settings':
                $settings = $admin_manager->getAllSettings();
                echo json_encode(array("status" => "success", "data" => $settings));
                break;
                
            case 'setting':
                $setting_key = $_GET['key'] ?? '';
                if ($setting_key) {
                    $value = $admin_manager->getSetting($setting_key);
                    echo json_encode(array("status" => "success", "data" => $value));
                } else {
                    echo json_encode(array("status" => "error", "message" => "Setting key required"));
                }
                break;
                
            case 'permissions':
                $admin_id = $_GET['admin_id'] ?? null;
                $permissions = $admin_manager->getAdminPermissions($admin_id);
                echo json_encode(array("status" => "success", "data" => $permissions));
                break;
                
            case 'notifications':
                $limit = (int)($_GET['limit'] ?? 10);
                $notifications = $admin_manager->getUnreadNotifications($limit);
                echo json_encode(array("status" => "success", "data" => $notifications));
                break;
                
            case 'reports':
                $limit = (int)($_GET['limit'] ?? 20);
                $reports = $admin_manager->getAdminReports($limit);
                echo json_encode(array("status" => "success", "data" => $reports));
                break;
                
            case 'recent_actions':
                $limit = (int)($_GET['limit'] ?? 10);
                $actions = $admin_manager->getRecentActions($limit);
                echo json_encode(array("status" => "success", "data" => $actions));
                break;
                
            case 'widgets':
                $widgets = $admin_manager->getDashboardWidgets();
                echo json_encode(array("status" => "success", "data" => $widgets));
                break;
                
            default:
                echo json_encode(array("status" => "error", "message" => "Invalid action"));
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        
        switch($action) {
            case 'setting':
                $setting_key = $data['setting_key'] ?? '';
                $setting_value = $data['setting_value'] ?? '';
                $setting_type = $data['setting_type'] ?? 'string';
                
                if ($setting_key && $setting_value !== '') {
                    $result = $admin_manager->setSetting($setting_key, $setting_value, $setting_type);
                    if ($result) {
                        $admin_manager->logAction('system_setting', 'system', null, "Updated setting: $setting_key");
                        echo json_encode(array("status" => "success", "message" => "Setting updated successfully"));
                    } else {
                        echo json_encode(array("status" => "error", "message" => "Failed to update setting"));
                    }
                } else {
                    echo json_encode(array("status" => "error", "message" => "Setting key and value required"));
                }
                break;
                
            case 'permission':
                $target_admin_id = $data['admin_id'] ?? '';
                $permission_type = $data['permission_type'] ?? '';
                $expires_at = $data['expires_at'] ?? null;
                
                if ($target_admin_id && $permission_type) {
                    $result = $admin_manager->grantPermission($target_admin_id, $permission_type, $expires_at);
                    if ($result) {
                        $admin_manager->logAction('system_setting', 'user', $target_admin_id, "Granted permission: $permission_type");
                        echo json_encode(array("status" => "success", "message" => "Permission granted successfully"));
                    } else {
                        echo json_encode(array("status" => "error", "message" => "Failed to grant permission"));
                    }
                } else {
                    echo json_encode(array("status" => "error", "message" => "Admin ID and permission type required"));
                }
                break;
                
            case 'notification':
                $notification_type = $data['notification_type'] ?? '';
                $title = $data['title'] ?? '';
                $message = $data['message'] ?? '';
                $priority = $data['priority'] ?? 'medium';
                $action_url = $data['action_url'] ?? null;
                
                if ($notification_type && $title && $message) {
                    $result = $admin_manager->createNotification($notification_type, $title, $message, $priority, $action_url);
                    if ($result) {
                        echo json_encode(array("status" => "success", "message" => "Notification created successfully"));
                    } else {
                        echo json_encode(array("status" => "error", "message" => "Failed to create notification"));
                    }
                } else {
                    echo json_encode(array("status" => "error", "message" => "Notification type, title, and message required"));
                }
                break;
                
            case 'report':
                $report_name = $data['report_name'] ?? '';
                $report_type = $data['report_type'] ?? '';
                $parameters = $data['parameters'] ?? [];
                $file_path = $data['file_path'] ?? null;
                $file_size = $data['file_size'] ?? null;
                
                if ($report_name && $report_type) {
                    $report_id = $admin_manager->createReport($report_name, $report_type, $parameters, $file_path, $file_size);
                    if ($report_id) {
                        echo json_encode(array("status" => "success", "message" => "Report created successfully", "report_id" => $report_id));
                    } else {
                        echo json_encode(array("status" => "error", "message" => "Failed to create report"));
                    }
                } else {
                    echo json_encode(array("status" => "error", "message" => "Report name and type required"));
                }
                break;
                
            case 'bulk_action':
                $action_type = $data['action_type'] ?? '';
                $target_count = $data['target_count'] ?? 0;
                $parameters = $data['parameters'] ?? [];
                
                if ($action_type && $target_count > 0) {
                    $bulk_action_id = $admin_manager->createBulkAction($action_type, $target_count, $parameters);
                    if ($bulk_action_id) {
                        echo json_encode(array("status" => "success", "message" => "Bulk action created", "bulk_action_id" => $bulk_action_id));
                    } else {
                        echo json_encode(array("status" => "error", "message" => "Failed to create bulk action"));
                    }
                } else {
                    echo json_encode(array("status" => "error", "message" => "Action type and target count required"));
                }
                break;
                
            case 'widget':
                $widget_id = $data['widget_id'] ?? '';
                $widget_data = $data['widget_data'] ?? [];
                
                if ($widget_id && !empty($widget_data)) {
                    $result = $admin_manager->updateDashboardWidget($widget_id, $widget_data);
                    if ($result) {
                        echo json_encode(array("status" => "success", "message" => "Widget updated successfully"));
                    } else {
                        echo json_encode(array("status" => "error", "message" => "Failed to update widget"));
                    }
                } else {
                    echo json_encode(array("status" => "error", "message" => "Widget ID and data required"));
                }
                break;
                
            default:
                echo json_encode(array("status" => "error", "message" => "Invalid action"));
        }
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        
        switch($action) {
            case 'notification_read':
                $notification_id = $data['notification_id'] ?? '';
                if ($notification_id) {
                    $result = $admin_manager->markNotificationAsRead($notification_id);
                    if ($result) {
                        echo json_encode(array("status" => "success", "message" => "Notification marked as read"));
                    } else {
                        echo json_encode(array("status" => "error", "message" => "Failed to mark notification as read"));
                    }
                } else {
                    echo json_encode(array("status" => "error", "message" => "Notification ID required"));
                }
                break;
                
            case 'bulk_action_progress':
                $bulk_action_id = $data['bulk_action_id'] ?? '';
                $completed_count = $data['completed_count'] ?? 0;
                $failed_count = $data['failed_count'] ?? 0;
                $status = $data['status'] ?? '';
                $result_summary = $data['result_summary'] ?? null;
                
                if ($bulk_action_id && $status) {
                    $result = $admin_manager->updateBulkAction($bulk_action_id, $completed_count, $failed_count, $status, $result_summary);
                    if ($result) {
                        echo json_encode(array("status" => "success", "message" => "Bulk action progress updated"));
                    } else {
                        echo json_encode(array("status" => "error", "message" => "Failed to update bulk action progress"));
                    }
                } else {
                    echo json_encode(array("status" => "error", "message" => "Bulk action ID and status required"));
                }
                break;
                
            default:
                echo json_encode(array("status" => "error", "message" => "Invalid action"));
        }
        break;
        
    case 'DELETE':
        switch($action) {
            case 'permission':
                $admin_id = $_GET['admin_id'] ?? '';
                $permission_type = $_GET['permission_type'] ?? '';
                
                if ($admin_id && $permission_type) {
                    $result = $admin_manager->revokePermission($admin_id, $permission_type);
                    if ($result) {
                        $admin_manager->logAction('system_setting', 'user', $admin_id, "Revoked permission: $permission_type");
                        echo json_encode(array("status" => "success", "message" => "Permission revoked successfully"));
                    } else {
                        echo json_encode(array("status" => "error", "message" => "Failed to revoke permission"));
                    }
                } else {
                    echo json_encode(array("status" => "error", "message" => "Admin ID and permission type required"));
                }
                break;
                
            default:
                echo json_encode(array("status" => "error", "message" => "Invalid action"));
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(array("status" => "error", "message" => "Method not allowed"));
}
?> 