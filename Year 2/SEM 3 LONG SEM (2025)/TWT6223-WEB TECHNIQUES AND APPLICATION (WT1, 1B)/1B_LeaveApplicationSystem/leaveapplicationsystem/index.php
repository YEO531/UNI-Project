<?php
require_once 'includes/config.php';

// Check if user is logged in
check_access();

// Get current user data
$user = get_logged_in_user();

// Ensure user exists
if (!$user) {
    redirect_with_message('login.php', '', 'warning');
}

// Get user's leave balances
$leave_balances = [];
$leave_types = get_all_leave_types();

// Only these 6 types should be shown
$allowed_leave_types = [
    'Annual Leave',
    'Emergency Leave',
    'Maternity Leave',
    'Paternity Leave',
    'Sick Leave',
    'Unpaid Leave'
];

// Build a map for quick lookup and avoid duplicates
$leave_type_map = [];
foreach ($leave_types as $type) {
    if (in_array($type['name'], $allowed_leave_types)) {
        $leave_type_map[$type['name']] = $type; // Use name as key to avoid duplicates
    }
}

// Now, build the filtered array in the correct order
$filtered_leave_types = [];
foreach ($allowed_leave_types as $name) {
    if (isset($leave_type_map[$name])) {
        $filtered_leave_types[] = $leave_type_map[$name];
        $leave_balances[$leave_type_map[$name]['id']] = get_leave_balance($user['id'], $leave_type_map[$name]['id']);
    }
}

// Get recent leave applications
$stmt = $conn->prepare("
    SELECT la.*, lt.name as leave_type_name 
    FROM leave_applications la 
    JOIN leave_types lt ON la.leave_type_id = lt.id 
    WHERE la.user_id = ? 
    ORDER BY la.created_at DESC 
    LIMIT 5
");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$recent_applications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get pending approvals (for managers)
$pending_approvals = [];
if (is_manager() || is_admin() || is_hr()) {
    $stmt = $conn->prepare("
        SELECT la.*, lt.name as leave_type_name, 
               u.first_name, u.last_name, u.employee_id
        FROM leave_applications la 
        JOIN leave_types lt ON la.leave_type_id = lt.id 
        JOIN users u ON la.user_id = u.id 
        WHERE la.status = 'pending' 
        ORDER BY la.created_at DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $pending_approvals = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// get upcoming public holidays
$today = date('Y-m-d');
$stmt = $conn->prepare("SELECT * FROM public_holidays WHERE date >= ? ORDER BY date ASC");
$stmt->bind_param("s", $today);
$stmt->execute();
$public_holidays = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$display_holidays = [];
foreach ($public_holidays as $holiday) {
    $display_holidays[] = $holiday; // 原本的节日

    // 判断是否为 Sunday
    $date = $holiday['date'];
    $weekday = date('w', strtotime($date)); // 0=Sunday, 1=Monday, ...
    if ($weekday == 0) {
        // 生成 replacement holiday
        $replacement = $holiday;
        // 日期+1天
        $replacement['date'] = date('Y-m-d', strtotime($date . ' +1 day'));
        // 名称加 (Replacement)
        $replacement['name'] .= ' (Replacement)';
        $display_holidays[] = $replacement;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - LeaveTrack</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .dashboard-container {
            padding: 20px;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .welcome-message h1 {
            color: var(--dark);
            margin-bottom: 5px;
        }

        .welcome-message p {
            color: var(--gray);
        }

        .quick-actions {
            display: flex;
            gap: 15px;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .dashboard-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: var(--card-shadow);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .card-header h3 {
            color: var(--dark);
            font-size: 1.1em;
        }

        .leave-balance {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px;
            border-radius: 10px;
            background: var(--light);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: 1px solid #e1e5e9;
        }

        .leave-balance:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .leave-balance i {
            font-size: 1.8em;
            color: var(--primary);
            min-width: 30px;
        }

        .balance-info h4 {
            color: var(--dark);
            margin-bottom: 4px;
            font-size: 0.95em;
            font-weight: 600;
        }

        .balance-info p {
            color: var(--gray);
            font-size: 0.85em;
            margin: 0;
        }

        .application-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .application-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-radius: 5px;
            background: var(--light);
            margin-bottom: 16px;
        }

        .application-item .btn {
            margin-left: 24px;
        }

        .application-info h4 {
            color: var(--dark);
            margin-bottom: 2px;
        }

        .application-info p {
            color: var(--gray);
            font-size: 0.9em;
        }

        .application-status {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8em;
            font-weight: 500;
        }

        .status-pending {
            background: var(--warning-light);
            color: var(--warning);
        }

        .status-approved {
            background: var(--success-light);
            color: var(--success);
        }

        .status-rejected {
            background: var(--error-light);
            color: var(--error);
        }

        .view-all {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.9em;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .view-all:hover {
            text-decoration: underline;
        }

        .dashboard-card .application-list {
            display: flex;
            flex-direction: row;
            gap: 16px;
            flex-wrap: wrap;
        }
        .dashboard-card .leave-balance {
            min-width: 160px;
            flex: 1 1 160px;
        }

        .public-holidays-section {
            margin-top: 40px;
        }
        .public-holidays-section h2 {
            text-align: center;
            background: linear-gradient(90deg, #2563eb 60%, #3f37c9 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            color: transparent;
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 28px;
        }
        .holiday-cards {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .holiday-card {
            width: 100%;
            max-width: 1000px;
            height: 300px;
            border-radius: 18px;
            background-size: cover;
            background-position: center;
            box-shadow: 0 4px 16px #0002;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            margin: 0 auto;
        }
        .holiday-info {
            font-family: 'Montserrat', 'Segoe UI', 'Arial', sans-serif;
            width: 100%;
            background: rgba(30, 41, 59, 0.65);
            color: #fff;
            padding: 20px 32px;
            border-radius: 0 18px 18px 0;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-width: 220px;
            letter-spacing: 0.5px;
        }
        .holiday-date {
            font-size: 1.15em;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .holiday-name {
            font-size: 1.4em;
            font-weight: bold;
            margin-top: 4px;
            letter-spacing: 1px;
        }

    </style>
</head>
<body>
<?php include 'includes/taskbar.php'; ?>
        <div class="dashboard-container">
            <?php display_message(); ?>

            <div class="dashboard-header">
                <div class="welcome-message">
                    <h1>Welcome, <?php echo htmlspecialchars($user['first_name']); ?>!</h1>
                    <p>Here's an overview of your leave management</p>
                </div>
                <div class="quick-actions">
                    <a href="apply_leave.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Apply Leave
                    </a>
                </div>
            </div>

            <div class="dashboard-grid">
                <!-- Leave Balances -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Leave Balances</h3>
                    </div>
                    <div class="application-list">
                        <?php foreach ($filtered_leave_types as $type): ?>
                        <div class="leave-balance">
                            <i class="fas fa-calendar-check"></i>
                            <div class="balance-info">
                                <h4><?php echo htmlspecialchars($type['name']); ?></h4>
                                <p><?php echo $leave_balances[$type['id']]; ?> days remaining</p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Recent Applications -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Recent Applications</h3>
                        <a href="calendar.php" class="View All">
                            View All<i class = "fas fa-chevron-right"></i>
                        </a>
                    </div>
                    <div class="application-list">
                        <?php if (empty($recent_applications)): ?>
                            <p class="text-muted">No recent applications</p>
                        <?php else: ?>
                            <?php foreach ($recent_applications as $application): ?>
                            <div class="application-item">
                                <div class="application-info">
                                    <h4><?php echo htmlspecialchars($application['leave_type_name']); ?></h4>
                                    <p><?php echo format_date($application['start_date']); ?> - <?php echo format_date($application['end_date']); ?></p>
                                </div>
                                <span class="application-status status-<?php echo strtolower($application['status']); ?>">
                                    <?php echo ucfirst($application['status']); ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($pending_approvals)): ?>
                <!-- Pending Approvals (for managers) -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Pending Approvals</h3>
                        <a href="admin.php" class="View-All">
                            View All <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                    <div class="application-list">
                        <?php foreach ($pending_approvals as $approval): ?>
                        <div class="application-item">
                            <div class="application-info">
                                <h4><?php echo htmlspecialchars($approval['first_name'] . ' ' . $approval['last_name']); ?></h4>
                                <p><?php echo htmlspecialchars($approval['leave_type_name']); ?> - <?php echo format_date($approval['start_date']); ?></p>
                            </div>
                            <a href="approve_leave.php?id=<?php echo $approval['id']; ?>" class="btn btn-sm btn-primary" style="padding: 8px -12px; font-size: 12px;">
                                Review
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="public-holidays-section">
                <h2>Upcoming Public Holidays</h2>
                <div class="holiday-cards">
                    <?php foreach ($display_holidays as $holiday): ?>
                        <div class="holiday-card" style="background-image: url('<?php echo htmlspecialchars($holiday['image_url']); ?>');">
                            <div class="holiday-info">
                                <div class="holiday-date">
                                    <?php
                                        $date = $holiday['date'];
                                        $weekday = date('l', strtotime($date));
                                        echo htmlspecialchars($date) . ' (' . $weekday . ')';
                                    ?>
                                </div>
                                <div class="holiday-name"><?php echo htmlspecialchars($holiday['name']); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 