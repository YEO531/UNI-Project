<?php

// Include database connection
require_once '../db_connection.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] != "staff") {
    header("location: ../login.php");
    exit();
}

$staff_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Fetch staff data
$stmt = $conn->prepare("SELECT * FROM MaintenanceStaff WHERE Staff_ID = ?");
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['message'] = 'Staff profile not found.';
    $_SESSION['message_type'] = 'danger';
    header('Location: dashboard.php');
    exit();
}

$staff = $result->fetch_assoc();

// Process form submission for profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $staff_name = trim($_POST['staff_name']);
    $staff_email = trim($_POST['staff_email']);
    $staff_phone = trim($_POST['staff_phone']);
    
    // Validate inputs
    if (empty($staff_name) || empty($staff_email)) {
        $error_message = 'Name and email are required fields.';
    } else {
        // Check if email already exists for another staff
        $check_email = $conn->prepare("SELECT Staff_ID FROM MaintenanceStaff WHERE Staff_Email = ? AND Staff_ID != ?");
        $check_email->bind_param("si", $staff_email, $staff_id);
        $check_email->execute();
        $email_result = $check_email->get_result();
        
        if ($email_result->num_rows > 0) {
            $error_message = 'Email already exists for another staff member.';
        } else {
            // Update staff profile
            $update_stmt = $conn->prepare("UPDATE MaintenanceStaff SET Staff_Name = ?, Staff_Email = ?, Staff_Phone = ? WHERE Staff_ID = ?");
            $update_stmt->bind_param("sssi", $staff_name, $staff_email, $staff_phone, $staff_id);
            
            if ($update_stmt->execute()) {
                $success_message = 'Profile updated successfully.';
                // Update session data
                $_SESSION['user_name'] = $staff_name;
                $_SESSION['user_email'] = $staff_email;
                
                // Refresh staff data
                $stmt->execute();
                $staff = $stmt->get_result()->fetch_assoc();
            } else {
                $error_message = 'Failed to update profile: ' . $conn->error;
            }
        }
    }
}

// Process form submission for password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate inputs
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = 'All password fields are required.';
    } elseif ($new_password !== $confirm_password) {
        $error_message = 'New password and confirmation do not match.';
    } elseif (strlen($new_password) < 8) {
        $error_message = 'Password must be at least 8 characters long.';
    } else {
        // Verify current password
        $stmt = $conn->prepare("SELECT Staff_Password FROM MaintenanceStaff WHERE Staff_ID = ?");
        $stmt->bind_param("i", $staff_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $staff_data = $result->fetch_assoc();
        
        if (password_verify($current_password, $staff_data['Staff_Password'])) {
            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password
            $update_stmt = $conn->prepare("UPDATE MaintenanceStaff SET Staff_Password = ? WHERE Staff_ID = ?");
            $update_stmt->bind_param("si", $hashed_password, $staff_id);
            
            if ($update_stmt->execute()) {
                $success_message = 'Password changed successfully.';
            } else {
                $error_message = 'Failed to change password: ' . $conn->error;
            }
        } else {
            $error_message = 'Current password is incorrect.';
        }
    }
}

// Fetch repair request statistics
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) AS total_requests,
        SUM(CASE WHEN Status = 'Pending' THEN 1 ELSE 0 END) AS pending_requests,
        SUM(CASE WHEN Status = 'In Progress' THEN 1 ELSE 0 END) AS in_progress_requests,
        SUM(CASE WHEN Status = 'Completed' THEN 1 ELSE 0 END) AS completed_requests
    FROM RepairRequest 
    WHERE Staff_ID = ?
");
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$repair_stats = $stmt->get_result()->fetch_assoc();

// Set page title
$page_title = 'Staff Profile';

// Include the layout template
ob_start();
?>

<div class="container py-4">
    <h1 class="mb-4">Maintenance Staff Profile</h1>
    
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Profile Information</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="staff_name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="staff_name" name="staff_name" value="<?php echo htmlspecialchars($staff['Staff_Name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="staff_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="staff_email" name="staff_email" value="<?php echo htmlspecialchars($staff['Staff_Email']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="staff_phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="staff_phone" name="staff_phone" value="<?php echo htmlspecialchars($staff['Staff_Phone']); ?>">
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">Change Password</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <div class="form-text">Password must be at least 8 characters long.</div>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-info text-white">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0">Repair Request Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card bg-light text-center mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Total Requests</h5>
                                    <p class="card-text display-6"><?php echo $repair_stats['total_requests']; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-center mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Pending</h5>
                                    <p class="card-text display-6"><?php echo $repair_stats['pending_requests']; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-primary text-white text-center mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">In Progress</h5>
                                    <p class="card-text display-6"><?php echo $repair_stats['in_progress_requests']; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white text-center mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Completed</h5>
                                    <p class="card-text display-6"><?php echo $repair_stats['completed_requests']; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <a href="repair_requests.php" class="btn btn-outline-secondary">View All Repair Requests</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../layout.php';
?>