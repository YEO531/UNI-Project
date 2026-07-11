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
    // Get filter parameters
    $status_filter = isset($_GET['status']) ? $_GET['status'] : '';
    $department_filter = isset($_GET['department']) ? $_GET['department'] : '';
    $date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
    $date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

    // Build query
    $query = "
        SELECT la.*, lt.name as leave_type_name, 
               u.first_name, u.last_name, u.employee_id, u.department,
               CONCAT(approver.first_name, ' ', approver.last_name) as approver_name
        FROM leave_applications la 
        JOIN leave_types lt ON la.leave_type_id = lt.id 
        JOIN users u ON la.user_id = u.id 
        LEFT JOIN users approver ON la.approved_by = approver.id
        WHERE 1=1
    ";

    $params = [];
    $types = "";

    if ($status_filter) {
        $query .= " AND la.status = ?";
        $params[] = $status_filter;
        $types .= "s";
    }

    if ($department_filter) {
        $query .= " AND u.department = ?";
        $params[] = $department_filter;
        $types .= "s";
    }

    if ($date_from) {
        $query .= " AND la.start_date >= ?";
        $params[] = $date_from;
        $types .= "s";
    }

    if ($date_to) {
        $query .= " AND la.end_date <= ?";
        $params[] = $date_to;
        $types .= "s";
    }

    $query .= " ORDER BY la.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";

    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $applications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Get total count for pagination
    $count_query = "
        SELECT COUNT(*) as total
        FROM leave_applications la 
        JOIN users u ON la.user_id = u.id 
        WHERE 1=1
    ";

    $count_params = [];
    $count_types = "";

    if ($status_filter) {
        $count_query .= " AND la.status = ?";
        $count_params[] = $status_filter;
        $count_types .= "s";
    }

    if ($department_filter) {
        $count_query .= " AND u.department = ?";
        $count_params[] = $department_filter;
        $count_types .= "s";
    }

    if ($date_from) {
        $count_query .= " AND la.start_date >= ?";
        $count_params[] = $date_from;
        $count_types .= "s";
    }

    if ($date_to) {
        $count_query .= " AND la.end_date <= ?";
        $count_params[] = $date_to;
        $count_types .= "s";
    }

    $count_stmt = $conn->prepare($count_query);
    if (!empty($count_params)) {
        $count_stmt->bind_param($count_types, ...$count_params);
    }
    $count_stmt->execute();
    $total_result = $count_stmt->get_result()->fetch_assoc();
    $total = $total_result['total'];

    echo json_encode(array(
        "status" => "success",
        "applications" => $applications,
        "total" => $total,
        "limit" => $limit,
        "offset" => $offset
    ));

} else {
    http_response_code(405);
    echo json_encode(array(
        "status" => "error",
        "message" => "Method not allowed"
    ));
}
?> 