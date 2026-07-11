<?php
require_once '../database/Database.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$db = Database::getInstance();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Get user profile
        if (isset($_GET['user_id'])) {
            $user = $db->getUserById($_GET['user_id']);
            if ($user) {
                // Remove sensitive data
                unset($user['password']);
                echo json_encode(['status' => 'success', 'data' => $user]);
            } else {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'User not found']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'User ID is required']);
        }
        break;

    case 'PUT':
        // Update user profile
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['user_id'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'User ID is required']);
            exit();
        }

        // Verify user is updating their own profile
        if ($data['user_id'] != $_SESSION['user_id']) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'You can only update your own profile']);
            exit();
        }

        $user = $db->getUserById($data['user_id']);
        if (!$user) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
            exit();
        }

        // Handle password update if provided
        if (isset($data['new_password'])) {
            if (!isset($data['current_password'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Current password is required']);
                exit();
            }

            // Verify current password
            if (!password_verify($data['current_password'], $user['password'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect']);
                exit();
            }

            // Update password
            $data['password'] = password_hash($data['new_password'], PASSWORD_DEFAULT);
        }

        // Update user profile
        $updateData = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'department' => $data['department'],
            'position' => $data['position']
        ];

        if (isset($data['password'])) {
            $updateData['password'] = $data['password'];
        }

        try {
            $db->updateUser($data['user_id'], $updateData);
            
            // Log the activity
            $db->logActivity(
                $data['user_id'],
                'Profile Update',
                'User updated their profile information'
            );

            echo json_encode([
                'status' => 'success',
                'message' => 'Profile updated successfully'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to update profile: ' . $e->getMessage()
            ]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        break;
}
?> 