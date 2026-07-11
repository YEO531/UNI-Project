<?php
require_once 'database/Database.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<h2>Invalid request</h2>';
    exit();
}

$applicationId = (int)$_GET['id'];
$db = Database::getInstance()->getConnection();

$stmt = $db->prepare('
    SELECT la.*, lt.name as leave_type_name, u.first_name, u.last_name, u.employee_id, u.department
    FROM leave_applications la
    JOIN leave_types lt ON la.leave_type_id = lt.id
    JOIN users u ON la.user_id = u.id
    WHERE la.id = ?
');
$stmt->execute([$applicationId]);
$application = $stmt->fetch();

if (!$application) {
    echo '<h2>Leave request not found.</h2>';
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Review Leave Request</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .review-container {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 32px;
        }
        .review-container h2 {
            margin-bottom: 24px;
        }
        .review-details p {
            margin: 10px 0;
            font-size: 1.1em;
        }
        .review-details strong {
            min-width: 120px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="review-container">
        <h2>Leave Request Details</h2>
        <div class="review-details">
            <p><strong>Employee:</strong> <?php echo htmlspecialchars($application['first_name'] . ' ' . $application['last_name']); ?> (<?php echo htmlspecialchars($application['employee_id']); ?>)</p>
            <p><strong>Department:</strong> <?php echo htmlspecialchars($application['department']); ?></p>
            <p><strong>Leave Type:</strong> <?php echo htmlspecialchars($application['leave_type_name']); ?></p>
            <p><strong>Start Date:</strong> <?php echo htmlspecialchars($application['start_date']); ?></p>
            <p><strong>End Date:</strong> <?php echo htmlspecialchars($application['end_date']); ?></p>
            <p><strong>Total Days:</strong> <?php echo htmlspecialchars($application['total_days']); ?></p>
            <p><strong>Reason:</strong> <?php echo nl2br(htmlspecialchars($application['reason'])); ?></p>
            <p><strong>Status:</strong> <?php echo ucfirst(htmlspecialchars($application['status'])); ?></p>
        </div>
    </div>
</body>
</html> 