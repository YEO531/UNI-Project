<?php
require_once '../includes/config.php';

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    exit();
}

// Set headers for JSON response
header('Content-Type: application/json');

// Get request data
$data = json_decode(file_get_contents('php://input'), true);

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['action']) && $data['action'] === 'register') {
    try {
        // Validate required fields
        $required_fields = ['first_name', 'last_name', 'employee_id', 'email', 'password', 'department', 'position', 'user_type'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                throw new Exception("$field is required");
            }
        }

        // Sanitize input data
        $first_name = sanitize_input($data['first_name']);
        $last_name = sanitize_input($data['last_name']);
        $employee_id = sanitize_input($data['employee_id']);
        $email = sanitize_input($data['email']);
        $department = sanitize_input($data['department']);
        $position = sanitize_input($data['position']);
        $user_type = sanitize_input($data['user_type']);
        $password = $data['password'];

        // Validate user type
        if (!in_array($user_type, ['employee', 'admin'])) {
            throw new Exception("Invalid user type selected");
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception("Email already registered");
        }

        // Check if employee ID already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE employee_id = ?");
        $stmt->bind_param("s", $employee_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception("Employee ID already registered");
        }

        // Validate department exists
        $stmt = $conn->prepare("SELECT id FROM departments WHERE name = ?");
        $stmt->bind_param("s", $department);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            throw new Exception("Invalid department selected");
        }
        $department_row = $result->fetch_assoc();
        $department_id = $department_row['id'];

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = $conn->prepare("
            INSERT INTO users (
                employee_id, first_name, last_name, email, 
                password, department, department_id, position, role
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        // 6 strings (employee_id, first_name, last_name, email, password, department),
        // 1 int (department_id), then 2 strings (position, role) = 9 total
        $stmt->bind_param(
            "ssssssiss",
            $employee_id,
            $first_name,
            $last_name,
            $email,
            $hashed_password,
            $department,
            $department_id,
            $position,
            $user_type
        );

        if (! $stmt->execute()) {
            // Dump the real SQL error
            error_log("Register SQL Error: " . $stmt->error);
            throw new Exception($stmt->error);
        }

        $user_id = $conn->insert_id;

        // Log the activity
        log_activity($user_id, 'Registration', 'New user registration');

        file_put_contents('register_debug.log', date('Y-m-d H:i:s') . " - Registration successful\n", FILE_APPEND);

        echo json_encode([
            'status' => 'success',
            'message' => 'Registration successful'
        ]);

    } catch (Exception $e) {
        file_put_contents('register_debug.log', date('Y-m-d H:i:s') . " - " . $e->getMessage() . "\n", FILE_APPEND);
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
    exit();
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['action']) && $data['action'] === 'login') {
    try {
        if (empty($data['email']) || empty($data['password'])) {
            throw new Exception("Email and password are required");
        }

        $email = sanitize_input($data['email']);
        $password = $data['password'];

        // Get user by email
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (!$user || !password_verify($password, $user['password'])) {
            throw new Exception("Invalid email or password");
        }

        // Set session data
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_position'] = $user['position'];

        // Log the activity
        log_activity($user['id'], 'Login', 'User logged in');

        echo json_encode([
            'status' => 'success',
            'message' => 'Login successful',
            'user' => [
                'id' => $user['id'],
                'name' => $user['first_name'] . ' ' . $user['last_name'],
                'role' => $user['role'],
                'position' => $user['position']
            ]
        ]);

    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
    exit();
}

// Handle logout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['action']) && $data['action'] === 'logout') {
    if (is_logged_in()) {
        log_activity($_SESSION['user_id'], 'Logout', 'User logged out');
    }
    
    session_destroy();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Logged out successfully'
    ]);
    exit();
}

// Invalid request
http_response_code(400);
echo json_encode([
    'status' => 'error',
    'message' => 'Invalid request'
]);
?> 