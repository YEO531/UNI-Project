<?php
require_once '../includes/config.php';

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Get departments from the departments table
        $stmt = $conn->prepare("SELECT id, name, description FROM departments ORDER BY name");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $departments = [];
        
        if ($result->num_rows > 0) {
            // Use departments from database
            while ($row = $result->fetch_assoc()) {
                $departments[] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'description' => $row['description']
                ];
            }
        }

        // If no departments found in the table, provide default list
        if (empty($departments)) {
            $default_departments = [
                ['name' => 'Business Development', 'description' => 'Business growth and strategic partnerships'],
                ['name' => 'Communications', 'description' => 'Internal and external communications'],
                ['name' => 'Compliance', 'description' => 'Regulatory compliance and risk management'],
                ['name' => 'Customer Service', 'description' => 'Customer support and service'],
                ['name' => 'Engineering', 'description' => 'Engineering and technical development'],
                ['name' => 'Facilities', 'description' => 'Facilities management and maintenance'],
                ['name' => 'Finance', 'description' => 'Financial operations and accounting'],
                ['name' => 'Human Resources', 'description' => 'HR and personnel management'],
                ['name' => 'Information Technology', 'description' => 'IT infrastructure and support'],
                ['name' => 'Legal', 'description' => 'Legal affairs and compliance'],
                ['name' => 'Marketing', 'description' => 'Marketing and brand management'],
                ['name' => 'Operations', 'description' => 'Operations management'],
                ['name' => 'Product Management', 'description' => 'Product strategy and development'],
                ['name' => 'Quality Assurance', 'description' => 'Quality control and testing'],
                ['name' => 'Research and Development', 'description' => 'R&D and innovation'],
                ['name' => 'Sales', 'description' => 'Sales and revenue generation'],
                ['name' => 'Security', 'description' => 'Security and safety management'],
                ['name' => 'Training', 'description' => 'Employee training and development'],
                ['name' => 'Other', 'description' => 'Other or not listed']
            ];
            
            foreach ($default_departments as $dept) {
                $departments[] = [
                    'id' => null,
                    'name' => $dept['name'],
                    'description' => $dept['description']
                ];
            }
        }
        
        echo json_encode([
            'status' => 'success',
            'departments' => $departments
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to fetch departments: ' . $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed'
    ]);
}
?> 