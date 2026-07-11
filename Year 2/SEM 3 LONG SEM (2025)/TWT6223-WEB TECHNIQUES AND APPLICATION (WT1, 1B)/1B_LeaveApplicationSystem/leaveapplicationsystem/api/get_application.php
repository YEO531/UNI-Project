<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../includes/config.php';

// Check if user is logged in and has admin privileges
if (!is_logged_in() || !in_array(get_user_role(), ['admin', 'super_admin'])) {
    http_response_code(403);
    echo json_encode(array("status" => "error", "message" => "Unauthorized access"));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['id'])) {
        $application_id = (int)$_GET['id'];
        
        $stmt = $conn->prepare("
            SELECT la.*, lt.name as leave_type_name, 
                   u.first_name, u.last_name, u.employee_id, u.department
            FROM leave_applications la 
            JOIN leave_types lt ON la.leave_type_id = lt.id 
            JOIN users u ON la.user_id = u.id 
            WHERE la.id = ?
        ");
        $stmt->bind_param("i", $application_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $application = $result->fetch_assoc();
            echo json_encode(array(
                "status" => "success",
                "application" => $application
            ));
        } else {
            echo json_encode(array(
                "status" => "error",
                "message" => "Application not found"
            ));
        }
    } else {
        echo json_encode(array(
            "status" => "error",
            "message" => "Application ID is required"
        ));
    }
} else {
    http_response_code(405);
    echo json_encode(array(
        "status" => "error",
        "message" => "Method not allowed"
    ));
}
?> 