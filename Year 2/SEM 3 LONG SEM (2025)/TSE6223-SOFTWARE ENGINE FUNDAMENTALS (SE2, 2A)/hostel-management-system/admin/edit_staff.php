<?php
require_once '../db_connection.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] != "admin") {
    header("location: ../login.php");
    exit();
}

$page_title = "Edit Staff - Admin Dashboard";

// Check if staff ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_msg'] = "Staff ID is required.";
    header("location: staff.php");
    exit();
}

$staff_id = intval($_GET['id']);

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate input
    $staff_name = trim($_POST['staff_name']);
    $staff_email = trim($_POST['staff_email']);
    $staff_phone = trim($_POST['staff_phone']);
    $change_password = isset($_POST['change_password']) ? true : false;
    $staff_password = $_POST['staff_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    if (empty($staff_name)) {
        $errors[] = "Staff name is required";
    }
    
    if (empty($staff_email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($staff_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    } else {
        // Check if email already exists but belongs to another staff member
        $check_sql = "SELECT Staff_ID FROM MaintenanceStaff WHERE Staff_Email = ? AND Staff_ID != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("si", $staff_email, $staff_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $errors[] = "Email already exists for another staff member";
        }
    }
    
    // Check password if change_password is checked
    if ($change_password) {
        if (empty($staff_password)) {
            $errors[] = "Password is required";
        } elseif (strlen($staff_password) < 6) {
            $errors[] = "Password must be at least 6 characters long";
        } elseif ($staff_password !== $confirm_password) {
            $errors[] = "Passwords do not match";
        }
    }
    
    if (empty($errors)) {
        if ($change_password) {
            // Update staff with new password
            $hashed_password = password_hash($staff_password, PASSWORD_DEFAULT);
            $update_sql = "UPDATE MaintenanceStaff SET Staff_Name = ?, Staff_Email = ?, Staff_Phone = ?, Staff_Password = ? WHERE Staff_ID = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ssssi", $staff_name, $staff_email, $staff_phone, $hashed_password, $staff_id);
        } else {
            // Update staff without changing password
            $update_sql = "UPDATE MaintenanceStaff SET Staff_Name = ?, Staff_Email = ?, Staff_Phone = ? WHERE Staff_ID = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("sssi", $staff_name, $staff_email, $staff_phone, $staff_id);
        }
        
        if ($update_stmt->execute()) {
            $_SESSION['success_msg'] = "Staff member updated successfully!";
            header("location: staff.php");
            exit();
        } else {
            $errors[] = "Error updating staff member: " . $conn->error;
        }
    }
}

// Get staff details
$sql = "SELECT * FROM MaintenanceStaff WHERE Staff_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error_msg'] = "Staff member not found.";
    header("location: staff.php");
    exit();
}

$staff = $result->fetch_assoc();

// Get repair request statistics for this staff member
$sql_repair_stats = "SELECT 
    COUNT(*) AS total_requests,
    COUNT(CASE WHEN Status = 'Pending' THEN 1 END) AS pending_requests,
    COUNT(CASE WHEN Status = 'Scheduled' THEN 1 END) AS scheduled_requests,
    COUNT(CASE WHEN Status = 'In Progress' THEN 1 END) AS in_progress_requests,
    COUNT(CASE WHEN Status = 'Completed' THEN 1 END) AS completed_requests,
    COUNT(CASE WHEN Status = 'Cancelled' THEN 1 END) AS cancelled_requests
FROM RepairRequest
WHERE Staff_ID = ?";

$stmt_stats = $conn->prepare($sql_repair_stats);
$stmt_stats->bind_param("i", $staff_id);
$stmt_stats->execute();
$repair_stats = $stmt_stats->get_result()->fetch_assoc();

// Build content for template
ob_start();
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Edit Staff Member</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="staff.php">Maintenance Staff</a></li>
        <li class="breadcrumb-item active">Edit Staff #<?= $staff_id ?></li>
    </ol>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-edit me-1"></i>
                    Staff Information
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="staff_id" class="form-label">Staff ID</label>
                            <input type="text" class="form-control" id="staff_id" value="<?= $staff['Staff_ID'] ?>" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label for="staff_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="staff_name" name="staff_name" value="<?= htmlspecialchars($staff['Staff_Name']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="staff_email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="staff_email" name="staff_email" value="<?= htmlspecialchars($staff['Staff_Email']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="staff_phone" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="staff_phone" name="staff_phone" value="<?= htmlspecialchars($staff['Staff_Phone']) ?>">
                            <div class="form-text">Format: XXX-XXX-XXXX (optional)</div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="change_password" name="change_password">
                            <label class="form-check-label" for="change_password">Change Password</label>
                        </div>
                        
                        <div id="password_fields" style="display: none;">
                            <div class="mb-3">
                                <label for="staff_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="staff_password" name="staff_password">
                                <div class="form-text">Minimum 6 characters</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="staff.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Staff Member</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i>
                    Repair Request Summary
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Requests:</span>
                            <span class="fw-bold"><?= $repair_stats['total_requests'] ?? 0 ?></span>
                        </div>
                        
                        <div class="progress mb-3" style="height: 24px;">
                            <?php
                            $total = $repair_stats['total_requests'] ?? 0;
                            $completed = $repair_stats['completed_requests'] ?? 0;
                            $completion_rate = $total > 0 ? round(($completed / $total) * 100) : 0;
                            ?>
                            <div class="progress-bar bg-success" role="progressbar" style="width: <?= $completion_rate ?>%;" 
                                 aria-valuenow="<?= $completion_rate ?>" aria-valuemin="0" aria-valuemax="100">
                                <?= $completion_rate ?>% Completed
                            </div>
                        </div>
                        
                        <div class="list-group">
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                Pending
                                <span class="badge bg-warning text-dark rounded-pill"><?= $repair_stats['pending_requests'] ?? 0 ?></span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                Scheduled
                                <span class="badge bg-info text-dark rounded-pill"><?= $repair_stats['scheduled_requests'] ?? 0 ?></span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                In Progress
                                <span class="badge bg-primary rounded-pill"><?= $repair_stats['in_progress_requests'] ?? 0 ?></span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                Completed
                                <span class="badge bg-success rounded-pill"><?= $repair_stats['completed_requests'] ?? 0 ?></span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                Cancelled
                                <span class="badge bg-secondary rounded-pill"><?= $repair_stats['cancelled_requests'] ?? 0 ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-wrench me-1"></i>
                    Recent Repair Requests
                </div>
                <div class="card-body">
                    <?php
                    // Get recent repair requests for this staff member
                    $sql_repairs = "SELECT rr.Request_ID, rr.Description, rr.Request_Date, r.Room_ID, rr.Status 
                                   FROM RepairRequest rr
                                   JOIN Room r ON rr.Room_ID = r.Room_ID
                                   WHERE rr.Staff_ID = ?
                                   ORDER BY rr.Request_Date DESC
                                   LIMIT 5";
                    $stmt_repairs = $conn->prepare($sql_repairs);
                    $stmt_repairs->bind_param("i", $staff_id);
                    $stmt_repairs->execute();
                    $repairs_result = $stmt_repairs->get_result();
                    ?>
                    
                    <?php if ($repairs_result->num_rows > 0): ?>
                        <div class="list-group">
                            <?php while ($repair = $repairs_result->fetch_assoc()): ?>
                                <a href="view_repair.php?id=<?= $repair['Request_ID'] ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Room #<?= $repair['Room_ID'] ?></h6>
                                        <small><?= date('M d, Y', strtotime($repair['Request_Date'])) ?></small>
                                    </div>
                                    <p class="mb-1 text-truncate"><?= htmlspecialchars($repair['Description']) ?></p>
                                    <small>
                                        <span class="badge 
                                            <?php
                                            switch ($repair['Status']) {
                                                case 'Pending': echo 'bg-warning text-dark'; break;
                                                case 'Scheduled': echo 'bg-info text-dark'; break;
                                                case 'In Progress': echo 'bg-primary'; break;
                                                case 'Completed': echo 'bg-success'; break;
                                                case 'Cancelled': echo 'bg-secondary'; break;
                                                default: echo 'bg-secondary';
                                            }
                                            ?>">
                                            <?= $repair['Status'] ?>
                                        </span>
                                    </small>
                                </a>
                            <?php endwhile; ?>
                        </div>
                        <div class="mt-3">
                            <a href="repair_requests.php?staff_id=<?= $staff_id ?>" class="btn btn-sm btn-outline-primary w-100">View All Assigned Repairs</a>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No repair requests assigned to this staff member.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Toggle password fields when checkbox is clicked
    document.getElementById('change_password').addEventListener('change', function() {
        const passwordFields = document.getElementById('password_fields');
        if (this.checked) {
            passwordFields.style.display = 'block';
        } else {
            passwordFields.style.display = 'none';
            // Clear password fields when unchecked
            document.getElementById('staff_password').value = '';
            document.getElementById('confirm_password').value = '';
        }
    });
</script>

<?php
$content = ob_get_clean();
include '../layout.php';
?>