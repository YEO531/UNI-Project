<?php
// Include database connection
require_once '../db_connection.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] != "admin") {
    header("location: ../login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Fetch admin data
$stmt = $conn->prepare("SELECT * FROM Admin WHERE Admin_ID = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['message'] = 'Admin profile not found.';
    $_SESSION['message_type'] = 'danger';
    header('Location: dashboard.php');
    exit();
}

$admin = $result->fetch_assoc();

// Process form submission for profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $admin_name = trim($_POST['admin_name']);
    $admin_email = trim($_POST['admin_email']);
    $admin_phone = trim($_POST['admin_phone']);
    $office_location = trim($_POST['office_location']);
    
    // Validate inputs
    if (empty($admin_name) || empty($admin_email)) {
        $error_message = 'Name and email are required fields.';
    } else {
        // Check if email already exists for another admin
        $check_email = $conn->prepare("SELECT Admin_ID FROM Admin WHERE Admin_Email = ? AND Admin_ID != ?");
        $check_email->bind_param("si", $admin_email, $admin_id);
        $check_email->execute();
        $email_result = $check_email->get_result();
        
        if ($email_result->num_rows > 0) {
            $error_message = 'Email already exists for another admin.';
        } else {
            // Update admin profile
            $update_stmt = $conn->prepare("UPDATE Admin SET Admin_Name = ?, Admin_Email = ?, Admin_Phone = ?, Office_Location = ? WHERE Admin_ID = ?");
            $update_stmt->bind_param("ssssi", $admin_name, $admin_email, $admin_phone, $office_location, $admin_id);
            
            if ($update_stmt->execute()) {
                $success_message = 'Profile updated successfully.';
                // Update session data
                $_SESSION['user_name'] = $admin_name;
                $_SESSION['user_email'] = $admin_email;
                
                // Refresh admin data
                $stmt->execute();
                $admin = $stmt->get_result()->fetch_assoc();
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
        $stmt = $conn->prepare("SELECT Admin_Password FROM Admin WHERE Admin_ID = ?");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin_data = $result->fetch_assoc();
        
        if (password_verify($current_password, $admin_data['Admin_Password'])) {
            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password
            $update_stmt = $conn->prepare("UPDATE Admin SET Admin_Password = ? WHERE Admin_ID = ?");
            $update_stmt->bind_param("si", $hashed_password, $admin_id);
            
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

// Set page title
$page_title = 'Admin Profile';

// Include the layout template
ob_start();
?>

<div class="container py-4">
    <h1 class="mb-4">Admin Profile</h1>
    
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
                            <label for="admin_id" class="form-label">Admin ID</label>
                            <input type="text" class="form-control" id="admin_id" name="admin_id" value="<?php echo htmlspecialchars($admin['Admin_ID']); ?>" readonly>
                            <div class="form-text">Admin ID cannot be modified.</div>
                        </div>
                        <div class="mb-3">
                            <label for="admin_name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="admin_name" name="admin_name" value="<?php echo htmlspecialchars($admin['Admin_Name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="admin_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="admin_email" name="admin_email" value="<?php echo htmlspecialchars($admin['Admin_Email']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="admin_phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="admin_phone" name="admin_phone" value="<?php echo htmlspecialchars($admin['Admin_Phone']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="office_location" class="form-label">Office Location</label>
                            <input type="text" class="form-control" id="office_location" name="office_location" value="<?php echo htmlspecialchars($admin['Office_Location'] ?? ''); ?>" placeholder="e.g., Building A, Floor 3, Room 301">
                            <div class="form-text">Enter your office location or work address.</div>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-warning text-dark">
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
                        <button type="submit" name="change_password" class="btn btn-warning text-dark">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../layout.php';
?>