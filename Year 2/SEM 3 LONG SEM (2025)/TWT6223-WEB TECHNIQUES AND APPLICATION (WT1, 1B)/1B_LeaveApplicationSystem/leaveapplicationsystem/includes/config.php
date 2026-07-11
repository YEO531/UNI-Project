<?php
// Database Connection Settings
$host = "localhost";
$username = "root";
$password = "";
$database = "leave_management_system";

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set to handle UTF-8 data
$conn->set_charset("utf8mb4");

// PDO connection for Database.php
try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("PDO Connection failed: " . $e->getMessage());
}

// Function to sanitize input data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Function to check user role
function get_user_role() {
    return isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;
}

// Function to redirect with message
function redirect_with_message($location, $message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header("Location: $location");
    exit();
}

// Function to display message and clear it from session
function display_message() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = $_SESSION['message_type'] ?? 'info';
        
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
}

// Function to check if user has access to the page
function check_access($allowed_roles = []) {
    if (!is_logged_in()) {
    }
    
    if (!empty($allowed_roles) && !in_array(get_user_role(), $allowed_roles)) {
        redirect_with_message('index.php', 'You do not have permission to access this page', 'danger');
    }
}

// Function to get logged in user data
function get_logged_in_user() {
    global $conn;
    if (!is_logged_in()) {
        return null;
    }
    
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

// Function to log user activity
function log_activity($user_id, $action, $description) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $action, $description);
    return $stmt->execute();
}

// Function to get user's leave balance
function get_leave_balance($user_id, $leave_type_id) {
    global $conn;
    
    // Get the maximum days allowed for this leave type
    $stmt = $conn->prepare("SELECT max_days FROM leave_types WHERE id = ?");
    $stmt->bind_param("i", $leave_type_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $leave_type = $result->fetch_assoc();
    $stmt->close();
    
    if (!$leave_type) {
        return 0;
    }
    
    $max_days = $leave_type['max_days'];
    $year = date('Y');
    
    // Calculate used days from approved leave applications for this year
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(total_days), 0) as used_days
        FROM leave_applications 
        WHERE user_id = ? AND leave_type_id = ? AND status = 'approved' AND YEAR(start_date) = ?
    ");
    $stmt->bind_param("iii", $user_id, $leave_type_id, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    $used_days = (int)$row['used_days'];
    $balance = $max_days - $used_days;
    
    // Ensure balance doesn't go negative
    return max(0, $balance);
}

// Function to format date
function format_date($date) {
    return date('F j, Y', strtotime($date));
}

// Function to calculate date difference
function calculate_date_difference($start_date, $end_date) {
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $interval = $start->diff($end);
    return $interval->days + 1; // Include both start and end dates
}

// Function to check if date is a working day
function is_working_day($date) {
    $day_of_week = date('N', strtotime($date));
    return $day_of_week <= 5; // Monday to Friday
}

// Function to get department name
function get_department_name($department_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT name FROM departments WHERE id = ?");
    $stmt->bind_param("i", $department_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $department = $result->fetch_assoc();
    return $department ? $department['name'] : 'Unknown Department';
}

// Function to get leave type name
function get_leave_type_name($leave_type_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT name FROM leave_types WHERE id = ?");
    $stmt->bind_param("i", $leave_type_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $leave_type = $result->fetch_assoc();
    return $leave_type ? $leave_type['name'] : 'Unknown Leave Type';
}

// Function to check if user is admin
function is_admin() {
    return get_user_role() === 'admin';
}

// Function to check if user is manager
function is_manager() {
    return get_user_role() === 'manager';
}

// Function to check if user is HR
function is_hr() {
    return get_user_role() === 'hr';
}

// Function to check if user is employee
function is_employee() {
    return get_user_role() === 'employee';
}

// Function to get all departments
function get_all_departments() {
    global $conn;
    $result = $conn->query("SELECT DISTINCT name, id, description FROM departments ORDER BY name");
    $departments = [];
    $seen = [];  // Track seen department names
    while ($row = $result->fetch_assoc()) {
        if (!isset($seen[$row['name']])) {  // Only add if we haven't seen this department name
        $departments[] = $row;
            $seen[$row['name']] = true;
        }
    }
    return $departments;
}

// Function to get all leave types
function get_all_leave_types() {
    global $conn;
    $result = $conn->query("SELECT * FROM leave_types ORDER BY name");
    $leave_types = [];
    while ($row = $result->fetch_assoc()) {
        $leave_types[] = $row;
    }
    return $leave_types;
}

// Function to get user's department
function get_user_department($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT department_id FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    return $user ? get_department_name($user['department_id']) : 'Unknown Department';
}

// Function to get user's manager
function get_user_manager($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT manager_id FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user && $user['manager_id']) {
        $stmt = $conn->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
        $stmt->bind_param("i", $user['manager_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $manager = $result->fetch_assoc();
        return $manager ? $manager['first_name'] . ' ' . $manager['last_name'] : 'No Manager Assigned';
    }
    
    return 'No Manager Assigned';
}

// Function to show alert message and redirect
function alert($message) {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = 'error';
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?> 