<?php
require_once 'includes/config.php';
require_once 'includes/AdminManager.php';

// Check if user is logged in and has admin privileges
check_access(['admin', 'super_admin']);

// Get current user data
$user = get_logged_in_user();

// Initialize AdminManager
$admin_manager = new AdminManager($conn, $user['id']);

// Function to initialize or update leave balances
function update_leave_balances($conn, $user_id, $leave_type_id, $days_change = 0) {
    $year = date('Y');
    
    // Get leave type max days
    $stmt = $conn->prepare("SELECT max_days FROM leave_types WHERE id = ?");
    $stmt->bind_param('i', $leave_type_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $leave_type = $result->fetch_assoc();
    $max_days = $leave_type['max_days'];
    $stmt->close();
    
    // Check if balance record exists
    $stmt = $conn->prepare("
        SELECT id, used_days, balance 
        FROM leave_balances 
        WHERE user_id = ? AND leave_type_id = ? AND year = ?
    ");
    $stmt->bind_param('iii', $user_id, $leave_type_id, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing balance
        $balance = $result->fetch_assoc();
        $new_used_days = max(0, $balance['used_days'] + $days_change);
        $new_balance = max(0, $max_days - $new_used_days);
        
        $stmt = $conn->prepare("
            UPDATE leave_balances 
            SET used_days = ?, balance = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->bind_param('iii', $new_used_days, $new_balance, $balance['id']);
        $stmt->execute();
    } else {
        // Initialize new balance
        $used_days = max(0, $days_change);
        $balance = max(0, $max_days - $used_days);
        
        $stmt = $conn->prepare("
            INSERT INTO leave_balances (user_id, leave_type_id, total_days, used_days, balance, year)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param('iiiiii', $user_id, $leave_type_id, $max_days, $used_days, $balance, $year);
        $stmt->execute();
    }
    $stmt->close();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $application_id = (int)$_POST['application_id'];
        
        // Get application details first
        $stmt = $conn->prepare("
            SELECT user_id, leave_type_id, total_days, status 
            FROM leave_applications 
            WHERE id = ?
        ");
        $stmt->bind_param('i', $application_id);
        $stmt->execute();
        $application = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        switch ($action) {
            case 'approve':
                $stmt = $conn->prepare("UPDATE leave_applications SET status = 'approved', approved_by = ?, approved_at = NOW() WHERE id = ?");
                $stmt->bind_param("ii", $user['id'], $application_id);
                if ($stmt->execute()) {
                    // Update leave balance
                    if ($application['status'] !== 'approved') {
                        update_leave_balances($conn, $application['user_id'], $application['leave_type_id'], $application['total_days']);
                    }
                    
                    // Create notification
                    $stmt = $conn->prepare("
                        INSERT INTO notifications (user_id, title, message, type) 
                        SELECT la.user_id, 'Leave Request Approved', 
                               CONCAT('Your leave request from ', la.start_date, ' to ', la.end_date, ' has been approved.'), 
                               'success' 
                        FROM leave_applications la WHERE la.id = ?
                    ");
                    $stmt->bind_param("i", $application_id);
                    $stmt->execute();
                    
                    // Log admin action
                    $admin_manager->logAction('approve_leave', 'leave_application', $application_id, "Approved leave application ID: $application_id");
                    
                    // Create admin notification
                    $admin_manager->createNotification(
                        'pending_approval', 
                        'Leave Request Approved', 
                        "Leave application #$application_id has been approved", 
                        'medium'
                    );
                    
                    log_activity($user['id'], 'approve_leave', "Approved leave application ID: $application_id");
                    
                }
                break;
                
            case 'reject':
                $rejection_reason = sanitize_input($_POST['rejection_reason']);
                $stmt = $conn->prepare("UPDATE leave_applications SET status = 'rejected', approved_by = ?, approved_at = NOW() WHERE id = ?");
                $stmt->bind_param("ii", $user['id'], $application_id);
                if ($stmt->execute()) {
                    // Update leave balance if it was previously approved
                    if ($application['status'] === 'approved') {
                        update_leave_balances($conn, $application['user_id'], $application['leave_type_id'], -$application['total_days']);
                    }
                    
                    // Create notification
                    $stmt = $conn->prepare("
                        INSERT INTO notifications (user_id, title, message, type) 
                        SELECT la.user_id, 'Leave Request Rejected', 
                               CONCAT('Your leave request from ', la.start_date, ' to ', la.end_date, ' has been rejected. Reason: ', ?), 
                               'error' 
                        FROM leave_applications la WHERE la.id = ?
                    ");
                    $stmt->bind_param("si", $rejection_reason, $application_id);
                    $stmt->execute();
                    
                    // Log admin action
                    $admin_manager->logAction('reject_leave', 'leave_application', $application_id, "Rejected leave application ID: $application_id. Reason: $rejection_reason");
                    
                    // Create admin notification
                    $admin_manager->createNotification(
                        'pending_approval', 
                        'Leave Request Rejected', 
                        "Leave application #$application_id has been rejected", 
                        'medium'
                    );
                    
                    log_activity($user['id'], 'reject_leave', "Rejected leave application ID: $application_id");
                }
                break;
                
            case 'update':
                $start_date = sanitize_input($_POST['start_date']);
                $end_date = sanitize_input($_POST['end_date']);
                $total_days = (int)$_POST['total_days'];
                $reason = sanitize_input($_POST['reason']);
                $status = sanitize_input($_POST['status']);
                
                // Validate dates and recalculate total_days if needed
                if ($start_date > $end_date) {
                    redirect_with_message('admin.php', 'End date must be after start date', 'error');
                }
                
                // Recalculate total_days server-side for accuracy
                $start = new DateTime($start_date);
                $end = new DateTime($end_date);
                $interval = $start->diff($end);
                $calculated_days = $interval->days + 1;
                
                // Use calculated days if the submitted value is incorrect
                if ($total_days != $calculated_days) {
                    $total_days = $calculated_days;
                }
                
                $stmt = $conn->prepare("
                    UPDATE leave_applications 
                    SET start_date = ?, end_date = ?, total_days = ?, reason = ?, status = ?, updated_at = NOW() 
                    WHERE id = ?
                ");
                $stmt->bind_param("ssissi", $start_date, $end_date, $total_days, $reason, $status, $application_id);
                if ($stmt->execute()) {
                    // Log admin action
                    $admin_manager->logAction('edit_leave', 'leave_application', $application_id, "Updated leave application ID: $application_id (Total days: $total_days)");
                    
                    log_activity($user['id'], 'update_leave', "Updated leave application ID: $application_id");
                }
                break;
        }
    }
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$department_filter = isset($_GET['department']) ? $_GET['department'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build query for leave applications
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

$query .= " ORDER BY la.created_at DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$applications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get departments for filter
$departments = get_all_departments();

// Get admin statistics using AdminManager
$admin_stats = $admin_manager->getAdminStatistics();

// Get recent admin actions
$recent_actions = $admin_manager->getRecentActions(5);

// Get unread admin notifications
$unread_notifications = $admin_manager->getUnreadNotifications(5);

// Get admin dashboard widgets
$dashboard_widgets = $admin_manager->getDashboardWidgets();

// Get admin settings
$dashboard_refresh_interval = $admin_manager->getSetting('dashboard_refresh_interval', 300);
$max_pending_display = $admin_manager->getSetting('max_pending_display', 50);

// Get leave type application statistics for main leave types
$main_leave_types = [
    'Annual Leave',
    'Emergency Leave',
    'Maternity Leave',
    'Paternity Leave',
    'Sick Leave',
    'Unpaid Leave'
];
$leave_type_stats = [];
$stats_query = "
    SELECT lt.name AS leave_type, 
           COUNT(DISTINCT la.user_id) AS user_count, 
           COUNT(*) AS application_count
    FROM leave_applications la
    JOIN leave_types lt ON la.leave_type_id = lt.id
    WHERE lt.name IN ('" . implode("','", $main_leave_types) . "')
    GROUP BY la.leave_type_id
    ORDER BY lt.name;
";
$stats_result = $conn->query($stats_query);
while ($row = $stats_result->fetch_assoc()) {
    $leave_type_stats[$row['leave_type']] = [
        'user_count' => $row['user_count'],
        'application_count' => $row['application_count']
    ];
}
foreach ($main_leave_types as $type) {
    if (!isset($leave_type_stats[$type])) {
        $leave_type_stats[$type] = ['user_count' => 0, 'application_count' => 0];
    }
}

// Fetch all users
$users = [];
$user_result = $conn->query("SELECT id, first_name, last_name, email FROM users ORDER BY first_name, last_name");
while ($row = $user_result->fetch_assoc()) {
    $users[] = $row;
}

// Fetch all leave types and build a map of name => [id, max_days]
$leave_types_map = [];
$type_result = $conn->query("SELECT id, name, max_days FROM leave_types");
while ($row = $type_result->fetch_assoc()) {
    $leave_types_map[$row['name']] = [
        'id' => $row['id'],
        'max_days' => $row['max_days']
    ];
}

// Calculate leave balances for all users and leave types based on leave_balances and approved applications
$user_leave_balances = [];
foreach ($users as $user_row) {
    $user_id = $user_row['id'];
    $user_leave_balances[$user_id] = [];
    $year = date('Y');
    
    // Initialize all leave types with default values
    foreach ($main_leave_types as $type) {
        $user_leave_balances[$user_id][$type] = 0;
        $user_leave_balances[$user_id]['used_' . $type] = 0;
        $user_leave_balances[$user_id]['total_' . $type] = 0;
    }
    
    // Get all leave balances and approved applications for the user for current year
    $stmt = $conn->prepare("
        SELECT 
            lt.name as leave_type,
            lt.max_days as total_days,
            COALESCE(SUM(CASE WHEN la.status = 'approved' THEN la.total_days ELSE 0 END), 0) as used_days,
            lt.max_days - COALESCE(SUM(CASE WHEN la.status = 'approved' THEN la.total_days ELSE 0 END), 0) as balance
        FROM leave_types lt
        LEFT JOIN leave_applications la ON lt.id = la.leave_type_id 
            AND la.user_id = ? 
            AND YEAR(la.start_date) = ?
        WHERE lt.name IN ('" . implode("','", $main_leave_types) . "')
        GROUP BY lt.id, lt.name, lt.max_days
    ");
    $stmt->bind_param('ii', $user_id, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Update with actual balances from database
    while ($row = $result->fetch_assoc()) {
        $user_leave_balances[$user_id][$row['leave_type']] = max(0, $row['balance']);
        $user_leave_balances[$user_id]['used_' . $row['leave_type']] = $row['used_days'];
        $user_leave_balances[$user_id]['total_' . $row['leave_type']] = $row['total_days'];
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - LeaveTrack</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .admin-container {
            padding: 20px;
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .admin-title h1 {
            color: var(--dark);
            margin-bottom: 5px;
        }

        .admin-title p {
            color: var(--gray);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: var(--card-shadow);
            text-align: center;
        }

        .stat-card h3 {
            font-size: 2.5em;
            color: var(--primary);
            margin-bottom: 10px;
        }

        .stat-card p {
            color: var(--gray);
            font-weight: 600;
        }

        .filters-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: var(--card-shadow);
            margin-bottom: 30px;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .applications-table {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: var(--card-shadow);
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .table th {
            background: var(--light);
            font-weight: 600;
            color: var(--dark);
        }

        .table tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 500;
        }

        .status-pending {
            background: rgba(252, 163, 17, 0.2);
            color: var(--warning);
        }

        .status-approved {
            background: rgba(76, 201, 240, 0.2);
            color: var(--success);
        }

        .status-rejected {
            background: rgba(231, 57, 70, 0.2);
            color: var(--danger);
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn-sm {
            padding: 8px 16px;
            font-size: 14px;
            border-radius: 8px;
            margin-top: 10px;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            position: relative;
        }

        .close {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: var(--gray);
        }

        .close:hover {
            color: var(--dark);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .admin-sidebar {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: var(--card-shadow);
            margin-bottom: 20px;
        }

        .admin-sidebar h3 {
            color: var(--dark);
            margin-bottom: 15px;
            font-size: 1.1em;
        }

        .notification-item {
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 10px;
            background: var(--light);
            border-left: 4px solid var(--primary);
        }

        .notification-item.unread {
            background: rgba(67, 97, 238, 0.1);
            border-left-color: var(--primary);
        }

        .notification-item h4 {
            font-size: 0.9em;
            margin-bottom: 5px;
            color: var(--dark);
        }

        .notification-item p {
            font-size: 0.8em;
            color: var(--gray);
            margin: 0;
        }

        .action-item {
            padding: 8px;
            border-radius: 5px;
            margin-bottom: 8px;
            background: var(--light);
            font-size: 0.85em;
        }

        .action-item .action-type {
            font-weight: 600;
            color: var(--primary);
        }

        .action-item .action-time {
            color: var(--gray);
            font-size: 0.8em;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .table {
                font-size: 0.9em;
            }
        }

        .same-size-btn {
            min-width: 100px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1em;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'includes/taskbar.php'; ?>
        
        <div class="admin-container">
            <div class="admin-header">
                <div class="admin-title">
                    <h1><i class="fas fa-user-shield"></i> Admin Dashboard</h1>
                    <p>Manage leave requests</p>
                </div>
            </div>

            <?php display_message(); ?>

            <!-- Combined Statistics and Leave Type Applicants Cards -->
            <div class="stats-grid" style="margin-bottom: 40px;">
                <div class="stat-card">
                    <h3><?php echo $admin_stats['total_applications']; ?></h3>
                    <p>Total Applications</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $admin_stats['pending_applications']; ?></h3>
                    <p>Pending</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $admin_stats['approved_applications']; ?></h3>
                    <p>Approved</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $admin_stats['rejected_applications']; ?></h3>
                    <p>Rejected</p>
                </div>
                <?php foreach ($main_leave_types as $type): ?>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $leave_type_stats[$type]['user_count']; ?></h3>
                        <p><?php echo htmlspecialchars($type); ?> Applicants</p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Leave Type Application Stats -->
            <div class="applications-table" style="margin-top: 30px; margin-bottom: 40px;">
                <h3 style="margin-bottom: 20px; color: var(--dark);">Leave Type Application Statistics</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Leave Type</th>
                            <th>Unique Applicants</th>
                            <th>Total Applications</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leave_type_stats as $type => $stat): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($type); ?></td>
                            <td><?php echo $stat['user_count']; ?></td>
                            <td><?php echo $stat['application_count']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Leave Balances Table for All Users -->
            <div class="applications-table" style="margin-top: 40px;">
                <h3 style="margin-bottom: 20px; color: var(--dark);">User Leave Balances</h3>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Annual Leave</th>
                                <th>Emergency Leave</th>
                                <th>Maternity Leave</th>
                                <th>Paternity Leave</th>
                                <th>Sick Leave</th>
                                <th>Unpaid Leave</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user_row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user_row['first_name'] . ' ' . $user_row['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user_row['email']); ?></td>
                                    <?php foreach ($main_leave_types as $type): ?>
                                        <td>
                                            <?php 
                                                $balance = $user_leave_balances[$user_row['id']][$type] ?? 0;
                                                $used = $user_leave_balances[$user_row['id']]['used_' . $type] ?? 0;
                                                $total = $user_leave_balances[$user_row['id']]['total_' . $type] ?? 0;
                                                echo "$balance / $total";
                                                if ($used > 0) {
                                                    echo " <small class='text-muted'>($used used)</small>";
                                                }
                                            ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Admin Sidebar with Notifications and Recent Actions -->
            <div class="admin-sidebar" style="margin-top: 40px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <!-- Notifications -->
                    <div>
                        <h3><i class="fas fa-bell"></i> Recent Notifications</h3>
                        <?php if (empty($unread_notifications)): ?>
                            <p style="color: var(--gray); font-size: 0.9em;">No new notifications</p>
                        <?php else: ?>
                            <?php foreach ($unread_notifications as $notification): ?>
                                <div class="notification-item unread">
                                    <h4><?php echo htmlspecialchars($notification['title']); ?></h4>
                                    <p><?php echo htmlspecialchars($notification['message']); ?></p>
                                    <small style="color: var(--gray);"><?php echo date('M j, Y g:i A', strtotime($notification['created_at'])); ?></small>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Recent Actions -->
                    <div>
                        <h3><i class="fas fa-history"></i> Recent Actions</h3>
                        <?php if (empty($recent_actions)): ?>
                            <p style="color: var(--gray); font-size: 0.9em;">No recent actions</p>
                        <?php else: ?>
                            <?php foreach ($recent_actions as $action): ?>
                                <div class="action-item">
                                    <div class="action-type"><?php echo ucfirst(str_replace('_', ' ', $action['action_type'])); ?></div>
                                    <div><?php echo htmlspecialchars($action['details']); ?></div>
                                    <div class="action-time"><?php echo date('M j, Y g:i A', strtotime($action['created_at'])); ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-section" id="filters-section">
                <h3 style="margin-bottom: 20px; color: var(--dark);">Filters</h3>
                <form method="GET" class="filters-grid" action="#filters-section">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Department</label>
                        <select name="department" class="form-control">
                            <option value="">All Departments</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['name']; ?>" <?php echo $department_filter === $dept['name'] ? 'selected' : ''; ?>>
                                    <?php echo $dept['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date From</label>
                        <input type="date" name="date_from" value="<?php echo $date_from; ?>" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Date To</label>
                        <input type="date" name="date_to" value="<?php echo $date_to; ?>" class="form-control">
                    </div>
                    <div class="form-group" style="display: flex; align-items: end; gap: 10px;">
                        <button type="submit" class="btn btn-primary same-size-btn">Apply Filters</button>
                        <button type="button" class="btn btn-primary same-size-btn" id="clearFiltersBtn">Clear</button>
                    </div>
                </form>
            </div>

            <!-- Applications Table -->
            <div class="applications-table" id="applications-table">
                <h3 style="margin-bottom: 20px; color: var(--dark);">Leave Applications</h3>
                
                <?php if (empty($applications)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>No leave applications found</p>
                    </div>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Leave Type</th>
                                <th>Dates</th>
                                <th>Days</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Applied On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applications as $app): ?>
                                <tr>
                                    <td>
                                        <div>
                                            <strong><?php echo $app['first_name'] . ' ' . $app['last_name']; ?></strong>
                                            <div style="font-size: 0.9em; color: var(--gray);">
                                                <?php echo $app['employee_id']; ?> - <?php echo $app['department']; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo $app['leave_type_name']; ?></td>
                                    <td>
                                        <?php echo format_date($app['start_date']); ?> to<br>
                                        <?php echo format_date($app['end_date']); ?>
                                    </td>
                                    <td>
                                        <strong><?php echo $app['total_days']; ?> days</strong>
                                        <?php 
                                        // Show calculation verification
                                        $start = new DateTime($app['start_date']);
                                        $end = new DateTime($app['end_date']);
                                        $calculated = $start->diff($end)->days + 1;
                                        if ($app['total_days'] != $calculated) {
                                            echo '<br><small style="color: orange;">⚠️ Calculated: ' . $calculated . ' days</small>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;">
                                            <?php echo $app['reason']; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $app['status']; ?>">
                                            <?php echo ucfirst($app['status']); ?>
                                        </span>
                                        <?php if ($app['approver_name']): ?>
                                            <div style="font-size: 0.8em; color: var(--gray); margin-top: 5px;">
                                                by <?php echo $app['approver_name']; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo format_date($app['created_at']); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <?php if ($app['status'] === 'pending'): ?>
                                                <button class="btn btn-sm btn-primary" onclick="approveApplication(<?php echo $app['id']; ?>)">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="rejectApplication(<?php echo $app['id']; ?>)">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            <?php endif; ?>
                                            <button class="btn btn-sm btn-outline" onclick="editApplication(<?php echo $app['id']; ?>)">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div id="approveModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('approveModal')">&times;</span>
            <h3>Approve Leave Request</h3>
            <p>Are you sure you want to approve this leave request?</p>
            <form method="POST" style="margin-top: 20px;" action="#applications-table">
                <input type="hidden" name="action" value="approve">
                <input type="hidden" name="application_id" id="approveApplicationId">
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Yes, Approve</button>
                    <button type="button" class="btn btn-outline" onclick="closeModal('approveModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('rejectModal')">&times;</span>
            <h3>Reject Leave Request</h3>
            <form method="POST" action="#applications-table">
                <input type="hidden" name="action" value="reject">
                <input type="hidden" name="application_id" id="rejectApplicationId">
                <div class="form-group">
                    <label>Rejection Reason</label>
                    <textarea name="rejection_reason" class="form-control" rows="4" required></textarea>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-danger">Reject</button>
                    <button type="button" class="btn btn-outline" onclick="closeModal('rejectModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editModal')">&times;</span>
            <h3>Edit Leave Request</h3>
            <form method="POST" id="editForm" action="#applications-table">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="application_id" id="editApplicationId">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" name="start_date" id="editStartDate" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>End Date</label>
                        <input type="date" name="end_date" id="editEndDate" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Total Days</label>
                        <input type="number" name="total_days" id="editTotalDays" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" id="editStatus" class="form-control" required>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Reason</label>
                    <textarea name="reason" id="editReason" class="form-control" rows="4" required></textarea>
                </div>
                
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <button type="button" class="btn btn-outline" onclick="closeModal('editModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Auto-refresh dashboard based on admin setting
        const refreshInterval = <?php echo $dashboard_refresh_interval * 1000; ?>;
        if (refreshInterval > 0) {
            setInterval(function() {
                location.reload();
            }, refreshInterval);
        }

        function approveApplication(id) {
            document.getElementById('approveApplicationId').value = id;
            document.getElementById('approveModal').style.display = 'block';
        }

        function rejectApplication(id) {
            document.getElementById('rejectApplicationId').value = id;
            document.getElementById('rejectModal').style.display = 'block';
        }

        function editApplication(id) {
            // Fetch application data and populate form
            fetch(`api/get_application.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const app = data.application;
                        document.getElementById('editApplicationId').value = app.id;
                        document.getElementById('editStartDate').value = app.start_date;
                        document.getElementById('editEndDate').value = app.end_date;
                        document.getElementById('editTotalDays').value = app.total_days;
                        document.getElementById('editStatus').value = app.status;
                        document.getElementById('editReason').value = app.reason;
                        document.getElementById('editModal').style.display = 'block';
                    } else {
                        alert('Error loading application data');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading application data');
                });
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }

        // Calculate days when dates change
        document.getElementById('editStartDate').addEventListener('change', calculateDays);
        document.getElementById('editEndDate').addEventListener('change', calculateDays);

        function calculateDays() {
            const startDate = document.getElementById('editStartDate').value;
            const endDate = document.getElementById('editEndDate').value;
            
            if (startDate && endDate) {
                const start = new Date(startDate);
                const end = new Date(endDate);
                
                // Ensure we're working with dates at midnight to avoid timezone issues
                start.setHours(0, 0, 0, 0);
                end.setHours(0, 0, 0, 0);
                
                // Calculate the difference in days
                const diffTime = end.getTime() - start.getTime();
                const totalDays = Math.floor(diffTime / (1000 * 60 * 60 * 24)) + 1;
                
                if (totalDays >= 0) {
                    document.getElementById('editTotalDays').value = totalDays;
                } else {
                    document.getElementById('editTotalDays').value = 0;
                    alert('Invalid date range. End date must be after start date.');
                }
            }
        }

        // Clear filters form functionality
        document.addEventListener('DOMContentLoaded', function() {
            var clearBtn = document.getElementById('clearFiltersBtn');
            if (clearBtn) {
                clearBtn.addEventListener('click', function() {
                    var form = clearBtn.closest('form');
                    if (form) {
                        // Clear all form inputs
                        var inputs = form.querySelectorAll('input, select');
                        inputs.forEach(function(input) {
                            if (input.type === 'date' || input.type === 'text') {
                                input.value = '';
                            } else if (input.tagName === 'SELECT') {
                                input.selectedIndex = 0;
                            }
                        });
                        
                        // Submit the form to apply the cleared filters
                        form.submit();
                    }
                });
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            var dateFrom = document.querySelector('input[name=\"date_from\"]');
            var dateTo = document.querySelector('input[name=\"date_to\"]');
            var filtersForm = document.querySelector('.filters-grid');
            if (dateFrom && dateTo && filtersForm) {
                dateFrom.addEventListener('change', function() { filtersForm.submit(); });
                dateTo.addEventListener('change', function() { filtersForm.submit(); });
            }
        });
    </script>
</body>
</html> 