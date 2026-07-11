<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Not authenticated'
    ]);
    exit;
}

echo json_encode([
    'status' => 'success',
    'user' => [
        'user_id' => $_SESSION['user_id'],
        'employee_id' => $_SESSION['employee_id'],
        'first_name' => $_SESSION['first_name'],
        'last_name' => $_SESSION['last_name'],
        'email' => $_SESSION['email'],
        'role' => $_SESSION['role'],
        'department' => $_SESSION['department'],
        'position' => $_SESSION['position']
    ]
]);
?> 