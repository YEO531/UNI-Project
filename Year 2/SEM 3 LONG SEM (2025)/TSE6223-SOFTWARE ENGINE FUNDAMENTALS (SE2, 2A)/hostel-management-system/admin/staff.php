
<?php
require_once '../db_connection.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] != "admin") {
    header("location: ../login.php");
    exit();
}

$page_title = "Manage Staff - Admin Dashboard";

// Handle staff deletion
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $staff_id = $_GET['id'];
    $sql = "DELETE FROM MaintenanceStaff WHERE Staff_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $staff_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_msg'] = "Staff member deleted successfully!";
    } else {
        $_SESSION['error_msg'] = "Error deleting staff member: " . $conn->error;
    }
    
    header("location: staff.php");
    exit();
}

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_condition = '';
$params = [];
$types = '';

if (!empty($search)) {
    $search_condition = " WHERE Staff_Name LIKE ? OR Staff_Email LIKE ? OR Staff_Phone LIKE ?";
    $search_param = "%$search%";
    $params = [$search_param, $search_param, $search_param];
    $types = "sss";
}

// Get all staff members
$sql = "SELECT * FROM MaintenanceStaff"
       . $search_condition .
       " ORDER BY Staff_ID";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Get repair requests statistics for staff
$sql_stats = "SELECT ms.Staff_ID, ms.Staff_Name, 
              COUNT(CASE WHEN rr.Status = 'Pending' THEN 1 END) as pending_requests,
              COUNT(CASE WHEN rr.Status = 'In Progress' THEN 1 END) as in_progress_requests,
              COUNT(CASE WHEN rr.Status = 'Completed' THEN 1 END) as completed_requests
              FROM MaintenanceStaff ms
              LEFT JOIN repairrequest rr ON ms.Staff_ID = rr.Staff_ID
              GROUP BY ms.Staff_ID";
$result_stats = $conn->query($sql_stats);
$staff_stats = [];

if ($result_stats) {
    while ($row = $result_stats->fetch_assoc()) {
        $staff_stats[$row['Staff_ID']] = $row;
    }
}

// Build content for template
ob_start();
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Manage Maintenance Staff</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Maintenance Staff</li>
    </ol>
    
    <?php if (isset($_SESSION['success_msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success_msg'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_msg']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_msg'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error_msg'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_msg']); ?>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-hard-hat me-1"></i>
                Staff Management
            </div>
            <div>
                <a href="add_staff.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add New Staff
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Search Form -->
            <form action="" method="GET" class="mb-4">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search by name, email, or phone..." value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <?php if (!empty($search)): ?>
                        <a href="staff.php" class="btn btn-secondary">Clear</a>
                    <?php endif; ?>
                </div>
            </form>
            
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="staffTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Workload</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['Staff_ID'] ?></td>
                                    <td><?= htmlspecialchars($row['Staff_Name']) ?></td>
                                    <td><?= htmlspecialchars($row['Staff_Email']) ?></td>
                                    <td><?= htmlspecialchars($row['Staff_Phone']) ?></td>
                                    <td>
                                        <?php if (isset($staff_stats[$row['Staff_ID']])): ?>
                                            <div class="d-flex justify-content-between">
                                                <span class="badge bg-warning me-1" data-bs-toggle="tooltip" title="Pending">
                                                    <?= $staff_stats[$row['Staff_ID']]['pending_requests'] ?? 0 ?>
                                                </span>
                                                <span class="badge bg-info me-1" data-bs-toggle="tooltip" title="In Progress">
                                                    <?= $staff_stats[$row['Staff_ID']]['in_progress_requests'] ?? 0 ?>
                                                </span>
                                                <span class="badge bg-success" data-bs-toggle="tooltip" title="Completed">
                                                    <?= $staff_stats[$row['Staff_ID']]['completed_requests'] ?? 0 ?>
                                                </span>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">No Data</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="view_staff.php?id=<?= $row['Staff_ID'] ?>" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="edit_staff.php?id=<?= $row['Staff_ID'] ?>" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="staff.php?action=delete&id=<?= $row['Staff_ID'] ?>" class="btn btn-danger btn-sm" 
                                           onclick="return confirm('Are you sure you want to delete this staff member? This action cannot be undone.')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No staff members found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-chart-bar me-1"></i>
            Repair Request Statistics
        </div>
        <div class="card-body">
            <div class="row">
                <?php
                // Get overall repair request statistics
                $sql_repair_stats = "SELECT 
                    COUNT(*) AS total_requests,
                    COUNT(CASE WHEN Status = 'Pending' THEN 1 END) AS pending_requests,
                    COUNT(CASE WHEN Status = 'In Progress' THEN 1 END) AS in_progress_requests,
                    COUNT(CASE WHEN Status = 'Completed' THEN 1 END) AS completed_requests,
                    COUNT(CASE WHEN Status = 'Cancelled' THEN 1 END) AS cancelled_requests
                FROM RepairRequest";
                
                $repair_stats = $conn->query($sql_repair_stats)->fetch_assoc();
                ?>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-primary text-white mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>Total Requests</div>
                                <div class="h3"><?= $repair_stats['total_requests'] ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-warning text-dark mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>Pending</div>
                                <div class="h3"><?= $repair_stats['pending_requests'] ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-info text-white mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>In Progress</div>
                                <div class="h3"><?= $repair_stats['in_progress_requests'] ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-success text-white mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>Completed</div>
                                <div class="h3"><?= $repair_stats['completed_requests'] ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Initialize datatable
    $(document).ready(function() {
        $('#staffTable').DataTable({
            responsive: true,
            "paging": true,
            "searching": false, // Disable built-in search as we have custom search
            "info": true,
            "order": [[0, "desc"]]
        });
    });
</script>

<?php
$content = ob_get_clean();
include '../layout.php';
?>