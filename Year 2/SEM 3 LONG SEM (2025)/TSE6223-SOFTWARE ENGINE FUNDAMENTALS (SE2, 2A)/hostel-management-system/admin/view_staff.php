<?php
require_once '../db_connection.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] != "admin") {
    header("location: ../login.php");
    exit();
}

$page_title = "View Staff Details - Admin Dashboard";

// Check if staff ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_msg'] = "No staff member selected.";
    header("location: staff.php");
    exit();
}

$staff_id = $_GET['id'];

// Get staff details
$sql = "SELECT * FROM MaintenanceStaff WHERE Staff_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_msg'] = "Staff member not found.";
    header("location: staff.php");
    exit();
}

$staff = $result->fetch_assoc();

// Get repair requests assigned to this staff
$sql_repairs = "SELECT rr.*, s.Student_Name, r.Room_Type, r.Room_ID 
                FROM RepairRequest rr
                JOIN Student s ON rr.Student_ID = s.Student_ID
                JOIN Room r ON rr.Room_ID = r.Room_ID
                WHERE rr.Staff_ID = ?
                ORDER BY rr.Request_Date DESC";
$stmt_repairs = $conn->prepare($sql_repairs);
$stmt_repairs->bind_param("i", $staff_id);
$stmt_repairs->execute();
$repair_requests = $stmt_repairs->get_result();

// Get repair request statistics
$sql_stats = "SELECT 
                COUNT(*) as total_requests,
                COUNT(CASE WHEN Status = 'Pending' THEN 1 END) as pending_requests,
                COUNT(CASE WHEN Status = 'Scheduled' THEN 1 END) as scheduled_requests,
                COUNT(CASE WHEN Status = 'In Progress' THEN 1 END) as in_progress_requests,
                COUNT(CASE WHEN Status = 'Completed' THEN 1 END) as completed_requests,
                COUNT(CASE WHEN Status = 'Cancelled' THEN 1 END) as cancelled_requests
              FROM RepairRequest
              WHERE Staff_ID = ?";
$stmt_stats = $conn->prepare($sql_stats);
$stmt_stats->bind_param("i", $staff_id);
$stmt_stats->execute();
$stats = $stmt_stats->get_result()->fetch_assoc();

// Build content for template
ob_start();
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Staff Details</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="staff.php">Maintenance Staff</a></li>
        <li class="breadcrumb-item active">Staff Details</li>
    </ol>
    
    <div class="row">
        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user me-1"></i>
                    Staff Information
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="rounded-circle bg-secondary text-white d-inline-flex justify-content-center align-items-center" 
                             style="width: 100px; height: 100px; font-size: 40px;">
                            <?= substr($staff['Staff_Name'], 0, 1) ?>
                        </div>
                        <h3 class="mt-3"><?= htmlspecialchars($staff['Staff_Name']) ?></h3>
                        <p class="text-muted">Maintenance Staff</p>
                    </div>
                    
                    <dl class="row">
                        <dt class="col-sm-4">Staff ID:</dt>
                        <dd class="col-sm-8"><?= $staff['Staff_ID'] ?></dd>
                        
                        <dt class="col-sm-4">Email:</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($staff['Staff_Email']) ?></dd>
                        
                        <dt class="col-sm-4">Phone:</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($staff['Staff_Phone']) ?></dd>
                    </dl>
                    
                    <div class="d-grid gap-2 mt-4">
                        <a href="edit_staff.php?id=<?= $staff['Staff_ID'] ?>" class="btn btn-warning">
                            <i class="fas fa-edit me-1"></i> Edit Staff
                        </a>
                        <a href="staff.php?action=delete&id=<?= $staff['Staff_ID'] ?>" class="btn btn-danger"
                           onclick="return confirm('Are you sure you want to delete this staff member? This action cannot be undone.')">
                            <i class="fas fa-trash me-1"></i> Delete Staff
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i>
                    Workload Statistics
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4 mb-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body py-2">
                                    <h2 class="mb-0"><?= $stats['total_requests'] ?? 0 ?></h2>
                                    <div>Total Requests</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-warning text-dark">
                                <div class="card-body py-2">
                                    <h2 class="mb-0"><?= ($stats['pending_requests'] ?? 0) + ($stats['scheduled_requests'] ?? 0) ?></h2>
                                    <div>Pending/Scheduled</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-success text-white">
                                <div class="card-body py-2">
                                    <h2 class="mb-0"><?= $stats['completed_requests'] ?? 0 ?></h2>
                                    <div>Completed</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($stats['total_requests'] > 0): ?>
                        <div class="mt-4">
                            <h6 class="fw-bold">Request Status Breakdown</h6>
                            <div class="progress" style="height: 25px;">
                                <?php
                                $pending_percent = ($stats['pending_requests'] / $stats['total_requests']) * 100;
                                $scheduled_percent = ($stats['scheduled_requests'] / $stats['total_requests']) * 100;
                                $in_progress_percent = ($stats['in_progress_requests'] / $stats['total_requests']) * 100;
                                $completed_percent = ($stats['completed_requests'] / $stats['total_requests']) * 100;
                                $cancelled_percent = ($stats['cancelled_requests'] / $stats['total_requests']) * 100;
                                ?>
                                <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $pending_percent ?>%" 
                                     title="Pending: <?= $stats['pending_requests'] ?>" data-bs-toggle="tooltip"
                                     aria-valuenow="<?= $pending_percent ?>" aria-valuemin="0" aria-valuemax="100">
                                    <?= $pending_percent > 10 ? $stats['pending_requests'] : '' ?>
                                </div>
                                <div class="progress-bar bg-info" role="progressbar" style="width: <?= $scheduled_percent ?>%" 
                                     title="Scheduled: <?= $stats['scheduled_requests'] ?>" data-bs-toggle="tooltip"
                                     aria-valuenow="<?= $scheduled_percent ?>" aria-valuemin="0" aria-valuemax="100">
                                    <?= $scheduled_percent > 10 ? $stats['scheduled_requests'] : '' ?>
                                </div>
                                <div class="progress-bar" role="progressbar" style="width: <?= $in_progress_percent ?>%" 
                                     title="In Progress: <?= $stats['in_progress_requests'] ?>" data-bs-toggle="tooltip"
                                     aria-valuenow="<?= $in_progress_percent ?>" aria-valuemin="0" aria-valuemax="100">
                                    <?= $in_progress_percent > 10 ? $stats['in_progress_requests'] : '' ?>
                                </div>
                                <div class="progress-bar bg-success" role="progressbar" style="width: <?= $completed_percent ?>%" 
                                     title="Completed: <?= $stats['completed_requests'] ?>" data-bs-toggle="tooltip"
                                     aria-valuenow="<?= $completed_percent ?>" aria-valuemin="0" aria-valuemax="100">
                                    <?= $completed_percent > 10 ? $stats['completed_requests'] : '' ?>
                                </div>
                                <div class="progress-bar bg-secondary" role="progressbar" style="width: <?= $cancelled_percent ?>%" 
                                     title="Cancelled: <?= $stats['cancelled_requests'] ?>" data-bs-toggle="tooltip"
                                     aria-valuenow="<?= $cancelled_percent ?>" aria-valuemin="0" aria-valuemax="100">
                                    <?= $cancelled_percent > 10 ? $stats['cancelled_requests'] : '' ?>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-2 small">
                                <span class="text-warning">Pending</span>
                                <span class="text-info">Scheduled</span>
                                <span class="text-primary">In Progress</span>
                                <span class="text-success">Completed</span>
                                <span class="text-secondary">Cancelled</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-tools me-1"></i>
                    Assigned Repair Requests
                </div>
                <div class="card-body">
                    <?php if ($repair_requests->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="repairTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Description</th>
                                        <th>Student</th>
                                        <th>Room</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = $repair_requests->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $row['Request_ID'] ?></td>
                                            <td><?= htmlspecialchars(substr($row['Description'], 0, 30)) . (strlen($row['Description']) > 30 ? '...' : '') ?></td>
                                            <td><?= htmlspecialchars($row['Student_Name']) ?></td>
                                            <td><?= htmlspecialchars($row['Room_Type']) ?> (<?= $row['Room_ID'] ?>)</td>
                                            <td><?= date('M d, Y', strtotime($row['Request_Date'])) ?></td>
                                            <td>
                                                <?php 
                                                $status_class = '';
                                                switch($row['Status']) {
                                                    case 'Pending': $status_class = 'bg-warning text-dark'; break;
                                                    case 'Scheduled': $status_class = 'bg-info text-white'; break;
                                                    case 'In Progress': $status_class = 'bg-primary text-white'; break;
                                                    case 'Completed': $status_class = 'bg-success text-white'; break;
                                                    case 'Cancelled': $status_class = 'bg-secondary text-white'; break;
                                                }
                                                ?>
                                                <span class="badge <?= $status_class ?>"><?= $row['Status'] ?></span>
                                            </td>
                                            <td>
                                                <a href="../repairs/view_repair.php?id=<?= $row['Request_ID'] ?>" class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            No repair requests assigned to this staff member yet.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mt-3">
        <a href="staff.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Staff List
        </a>
    </div>
</div>

<script>
    // Initialize datatable and tooltips
    $(document).ready(function() {
        $('#repairTable').DataTable({
            responsive: true,
            "order": [[0, "desc"]],
            "pageLength": 5
        });
        
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>

<?php
$content = ob_get_clean();
include '../layout.php';
?>