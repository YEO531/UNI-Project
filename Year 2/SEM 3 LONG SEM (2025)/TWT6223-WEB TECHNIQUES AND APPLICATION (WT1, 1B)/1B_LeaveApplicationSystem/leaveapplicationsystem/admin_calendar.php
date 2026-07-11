<?php
require_once 'includes/config.php';
require_once 'includes/AdminManager.php';

// Check if user is logged in and is an admin
if (!is_logged_in() || !is_admin()) {
    redirect_with_message('login.php', 'Please log in as an administrator to view this page.', 'error');
}

// Fetch all leave applications with user details
$leaves = [];
$sql = "SELECT la.id, la.user_id, 
        CONCAT(u.first_name, ' ', u.last_name) AS employee_name, 
        u.email AS employee_email, 
        u.department AS department_name, 
        lt.name AS leave_type, la.start_date, la.end_date, 
        la.status, la.reason, la.created_at
        FROM leave_applications la
        JOIN users u ON la.user_id = u.id
        JOIN leave_types lt ON la.leave_type_id = lt.id
        ORDER BY la.created_at DESC";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $leaves[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Leave Overview - LeaveTrack</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .container {
            max-width: 1400px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .page-header {
            background: #fff;
            padding: 24px;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            margin-bottom: 24px;
        }
        .page-title {
            font-size: 2em;
            color: #333;
            margin: 0 0 10px 0;
        }
        .page-subtitle {
            color: #666;
            margin: 0;
        }
        .table-container {
            background: #fff;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }
        .table-header {
            background: #3b5bdb;
            color: #fff;
            padding: 20px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .table-title {
            font-size: 1.3em;
            margin: 0;
        }
        .filter-controls {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .filter-controls select, .filter-controls input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        .table-responsive {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }
        .status-approved {
            background: #d4edda;
            color: #155724;
        }
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .employee-info {
            display: flex;
            flex-direction: column;
        }
        .employee-name {
            font-weight: 600;
            color: #333;
        }
        .employee-email {
            font-size: 12px;
            color: #666;
        }
        .department-badge {
            background: #e3f2fd;
            color: #1976d2;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        .leave-duration {
            font-weight: 500;
        }
        .reason-cell {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .reason-full {
            display: none;
            position: absolute;
            background: #fff;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            max-width: 300px;
            z-index: 1000;
        }
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            text-align: center;
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #3b5bdb;
            margin-bottom: 5px;
        }
        .stat-label {
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
<?php include 'includes/taskbar.php'; ?>

<div class="container">
    <div class="page-header">
        <h1 class="page-title">Employee Leave Overview</h1>
        <p class="page-subtitle">View all employee leave applications and their status</p>
    </div>

    <?php
    // Calculate statistics
    $total_leaves = count($leaves);
    $approved_leaves = count(array_filter($leaves, function($leave) { return $leave['status'] === 'approved'; }));
    $pending_leaves = count(array_filter($leaves, function($leave) { return $leave['status'] === 'pending'; }));
    $rejected_leaves = count(array_filter($leaves, function($leave) { return $leave['status'] === 'rejected'; }));
    ?>

    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-number"><?php echo $total_leaves; ?></div>
            <div class="stat-label">Total Applications</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $approved_leaves; ?></div>
            <div class="stat-label">Approved</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $pending_leaves; ?></div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $rejected_leaves; ?></div>
            <div class="stat-label">Rejected</div>
        </div>
    </div>

    <div class="table-container">
        <div class="table-header">
            <h2 class="table-title">Leave Applications</h2>
            <div class="filter-controls">
                <select id="statusFilter">
                    <option value="">All Status</option>
                    <option value="approved">Approved</option>
                    <option value="pending">Pending</option>
                    <option value="rejected">Rejected</option>
                </select>
                <select id="departmentFilter">
                    <option value="">All Departments</option>
                    <?php
                    $departments = array_unique(array_column($leaves, 'department_name'));
                    foreach ($departments as $dept) {
                        echo "<option value='$dept'>$dept</option>";
                    }
                    ?>
                </select>
                <input type="text" id="searchInput" placeholder="Search employee...">
            </div>
        </div>
        
        <div class="table-responsive">
            <table id="leaveTable">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Leave Type</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Reason</th>
                        <th>Applied Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leaves as $leave): ?>
                    <tr data-status="<?php echo $leave['status']; ?>" 
                        data-department="<?php echo htmlspecialchars($leave['department_name']); ?>"
                        data-employee="<?php echo htmlspecialchars($leave['employee_name']); ?>">
                        <td>
                            <div class="employee-info">
                                <span class="employee-name"><?php echo htmlspecialchars($leave['employee_name']); ?></span>
                                <span class="employee-email"><?php echo htmlspecialchars($leave['employee_email']); ?></span>
                            </div>
                        </td>
                        <td>
                            <span class="department-badge"><?php echo htmlspecialchars($leave['department_name']); ?></span>
                        </td>
                        <td><?php echo htmlspecialchars($leave['leave_type']); ?></td>
                        <td class="leave-duration">
                            <?php 
                            $start = new DateTime($leave['start_date']);
                            $end = new DateTime($leave['end_date']);
                            $duration = $start->diff($end)->days + 1;
                            echo $start->format('M d, Y') . ' - ' . $end->format('M d, Y') . ' (' . $duration . ' day' . ($duration > 1 ? 's' : '') . ')';
                            ?>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo $leave['status']; ?>">
                                <?php echo ucfirst($leave['status']); ?>
                            </span>
                        </td>
                        <td class="reason-cell" title="<?php echo htmlspecialchars($leave['reason']); ?>">
                            <?php echo htmlspecialchars($leave['reason']); ?>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($leave['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('leaveTable');
    const statusFilter = document.getElementById('statusFilter');
    const departmentFilter = document.getElementById('departmentFilter');
    const searchInput = document.getElementById('searchInput');

    function filterTable() {
        const statusValue = statusFilter.value.toLowerCase();
        const departmentValue = departmentFilter.value.toLowerCase();
        const searchValue = searchInput.value.toLowerCase();

        const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

        for (let row of rows) {
            const status = row.getAttribute('data-status');
            const department = row.getAttribute('data-department').toLowerCase();
            const employee = row.getAttribute('data-employee').toLowerCase();

            const statusMatch = !statusValue || status === statusValue;
            const departmentMatch = !departmentValue || department === departmentValue;
            const searchMatch = !searchValue || employee.includes(searchValue);

            if (statusMatch && departmentMatch && searchMatch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    }

    statusFilter.addEventListener('change', filterTable);
    departmentFilter.addEventListener('change', filterTable);
    searchInput.addEventListener('input', filterTable);
});
</script>
</body>
</html> 