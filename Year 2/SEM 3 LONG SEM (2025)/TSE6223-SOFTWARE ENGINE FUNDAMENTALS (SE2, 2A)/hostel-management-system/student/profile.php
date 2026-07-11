<?php

// Include database connection
require_once '../db_connection.php';

// Check if user is logged in and is a student
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] != "student") {
    header("location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Fetch student data
$stmt = $conn->prepare("
    SELECT s.*, r.Room_Type, r.Room_ID, r.Status AS Room_Status 
    FROM Student s 
    LEFT JOIN Room r ON s.Room_ID = r.Room_ID 
    WHERE s.Student_ID = ?
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['message'] = 'Student profile not found.';
    $_SESSION['message_type'] = 'danger';
    header('Location: dashboard.php');
    exit();
}

$student = $result->fetch_assoc();

// Process form submission for profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $student_name = trim($_POST['student_name']);
    $student_email = trim($_POST['student_email']);
    $student_phone = trim($_POST['student_phone']);
    $course = trim($_POST['course']);
    $emergency_contact = trim($_POST['emergency_contact']);
    
    // Validate inputs
    if (empty($student_name) || empty($student_email)) {
        $error_message = 'Name and email are required fields.';
    } else {
        // Check if email already exists for another student
        $check_email = $conn->prepare("SELECT Student_ID FROM Student WHERE Student_Email = ? AND Student_ID != ?");
        $check_email->bind_param("si", $student_email, $student_id);
        $check_email->execute();
        $email_result = $check_email->get_result();
        
        if ($email_result->num_rows > 0) {
            $error_message = 'Email already exists for another student.';
        } else {
            // Update student profile including course and emergency contact
            $update_stmt = $conn->prepare("UPDATE Student SET Student_Name = ?, Student_Email = ?, Student_Phone = ?, Course = ?, Emergency_Contact = ? WHERE Student_ID = ?");
            $update_stmt->bind_param("sssssi", $student_name, $student_email, $student_phone, $course, $emergency_contact, $student_id);
            
            if ($update_stmt->execute()) {
                $success_message = 'Profile updated successfully.';
                // Update session data
                $_SESSION['user_name'] = $student_name;
                $_SESSION['user_email'] = $student_email;
                
                // Refresh student data
                $stmt->execute();
                $student = $stmt->get_result()->fetch_assoc();
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
        $stmt = $conn->prepare("SELECT Student_Password FROM Student WHERE Student_ID = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $student_data = $result->fetch_assoc();
        
        if (password_verify($current_password, $student_data['Student_Password'])) {
            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password
            $update_stmt = $conn->prepare("UPDATE Student SET Student_Password = ? WHERE Student_ID = ?");
            $update_stmt->bind_param("si", $hashed_password, $student_id);
            
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

// Fetch payment history
$payment_stmt = $conn->prepare("
    SELECT * FROM Payment 
    WHERE Student_ID = ? 
    ORDER BY Payment_Date DESC 
    LIMIT 5
");
$payment_stmt->bind_param("i", $student_id);
$payment_stmt->execute();
$payment_result = $payment_stmt->get_result();

// Fetch repair requests
$repair_stmt = $conn->prepare("
    SELECT r.*, rs.Staff_Name 
    FROM RepairRequest r 
    LEFT JOIN MaintenanceStaff rs ON r.Staff_ID = rs.Staff_ID 
    WHERE r.Student_ID = ? 
    ORDER BY r.Request_Date DESC 
    LIMIT 5
");
$repair_stmt->bind_param("i", $student_id);
$repair_stmt->execute();
$repair_result = $repair_stmt->get_result();

// Set page title
$page_title = 'Student Profile';

// Include the layout template
ob_start();
?>

<div class="container py-4">
    <h1 class="mb-4">Student Profile</h1>
    
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
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-circle me-2"></i>Personal Information
                    </h5>
                </div>
                <div class="card-body">
                    <form method="post" action="">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="student_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="student_name" name="student_name" value="<?php echo htmlspecialchars($student['Student_Name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="student_email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="student_email" name="student_email" value="<?php echo htmlspecialchars($student['Student_Email']); ?>" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="student_phone" class="form-label">Phone Number</label>
                                <input type="text" class="form-control" id="student_phone" name="student_phone" value="<?php echo htmlspecialchars($student['Student_Phone']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="course" class="form-label">Course/Program</label>
                                <select class="form-select" id="course" name="course">
                                    <option value="">Select Course</option>
                                    <option value="Computer Science" <?php echo ($student['Course'] == 'Computer Science') ? 'selected' : ''; ?>>Computer Science</option>
                                    <option value="Information Technology" <?php echo ($student['Course'] == 'Information Technology') ? 'selected' : ''; ?>>Information Technology</option>
                                    <option value="Software Engineering" <?php echo ($student['Course'] == 'Software Engineering') ? 'selected' : ''; ?>>Software Engineering</option>
                                    <option value="Business Administration" <?php echo ($student['Course'] == 'Business Administration') ? 'selected' : ''; ?>>Business Administration</option>
                                    <option value="Accounting" <?php echo ($student['Course'] == 'Accounting') ? 'selected' : ''; ?>>Accounting</option>
                                    <option value="Marketing" <?php echo ($student['Course'] == 'Marketing') ? 'selected' : ''; ?>>Marketing</option>
                                    <option value="Engineering" <?php echo ($student['Course'] == 'Engineering') ? 'selected' : ''; ?>>Engineering</option>
                                    <option value="Medicine" <?php echo ($student['Course'] == 'Medicine') ? 'selected' : ''; ?>>Medicine</option>
                                    <option value="Law" <?php echo ($student['Course'] == 'Law') ? 'selected' : ''; ?>>Law</option>
                                    <option value="Other" <?php echo ($student['Course'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="emergency_contact" class="form-label">Emergency Contact</label>
                            <input type="tel" class="form-control" id="emergency_contact" name="emergency_contact" value="<?php echo htmlspecialchars($student['Emergency_Contact']); ?>">
                            <div class="form-text">Enter a phone number for emergency situations</div>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Profile
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-key me-2"></i>Change Password
                    </h5>
                </div>
                <div class="card-body">
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                <div class="form-text">Password must be at least 8 characters long.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-info text-white">
                            <i class="fas fa-lock me-2"></i>Change Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Student Info Card -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-id-card me-2"></i>Student Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Course/Program</h6>
                        <p class="mb-0"><?php echo !empty($student['Course']) ? htmlspecialchars($student['Course']) : 'Not specified'; ?></p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Emergency Contact</h6>
                        <p class="mb-0">
                            <?php if (!empty($student['Emergency_Contact'])): ?>
                                <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($student['Emergency_Contact']); ?>
                            <?php else: ?>
                                Not specified
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="mb-0">
                        <h6 class="text-muted mb-1">Student ID</h6>
                        <p class="mb-0">#<?php echo $student['Student_ID']; ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Room Information Card -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-home me-2"></i>Room Information
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($student['Room_ID'])): ?>
                    <h6 class="card-subtitle mb-2 text-muted">Current Room</h6>
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-light p-3 rounded me-3">
                            <i class="fas fa-bed fa-2x text-success"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Room #<?php echo $student['Room_ID']; ?></h5>
                            <p class="mb-0"><?php echo $student['Room_Type']; ?> Room</p>
                        </div>
                    </div>
                    <div class="mb-3">
                        <span class="badge bg-<?php echo ($student['Room_Status'] == 'Occupied') ? 'danger' : 'success'; ?>">
                            <?php echo $student['Room_Status']; ?>
                        </span>
                    </div>
                    <a href="rooms.php" class="btn btn-outline-success btn-sm">View Details</a>
                    <?php else: ?>
                    <div class="text-center py-3">
                        <i class="fas fa-home fa-3x text-muted mb-3"></i>
                        <p>You haven't been assigned a room yet.</p>
                        <a href="rooms.php" class="btn btn-success btn-sm">Browse Available Rooms</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Payment History Section -->
        <div class="col-md-6">
            <div class="card mb-4 payment-card">
                <div class="card-header bg-warning">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-money-bill-wave me-2"></i>Recent Payments
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($payment_result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($payment = $payment_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($payment['Payment_Date'])); ?></td>
                                    <td>$<?php echo number_format($payment['Amount'], 2); ?></td>
                                    <td><?php echo $payment['Payment_Method']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo ($payment['Status'] == 'Completed') ? 'success' : 
                                                (($payment['Status'] == 'Pending') ? 'warning' : 
                                                (($payment['Status'] == 'Failed') ? 'danger' : 'info')); 
                                        ?>">
                                            <?php echo $payment['Status']; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="payments.php" class="btn btn-outline-warning btn-sm">View All Payments</a>
                    <?php else: ?>
                    <div class="text-center py-3">
                        <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                        <p>No payment records found.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Repair Requests Section -->
        <div class="col-md-6">
            <div class="card mb-4 repair-card">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tools me-2"></i>Recent Repair Requests
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($repair_result->num_rows > 0): ?>
                    <div class="accordion" id="repairRequestsAccordion">
                        <?php $counter = 1; while ($repair = $repair_result->fetch_assoc()): ?>
                        <div class="accordion-item mb-2 border">
                            <h2 class="accordion-header" id="repair-<?php echo $repair['Request_ID']; ?>">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#repair-collapse-<?php echo $repair['Request_ID']; ?>" aria-expanded="false" aria-controls="repair-collapse-<?php echo $repair['Request_ID']; ?>">
                                    <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                        <span><strong><?php echo date('M d, Y', strtotime($repair['Request_Date'])); ?></strong></span>
                                        <span class="badge bg-<?php 
                                            echo ($repair['Status'] == 'Completed') ? 'success' : 
                                                (($repair['Status'] == 'Pending') ? 'warning' : 
                                                (($repair['Status'] == 'In Progress') ? 'primary' : 
                                                (($repair['Status'] == 'Scheduled') ? 'info' : 'secondary'))); 
                                        ?>">
                                            <?php echo $repair['Status']; ?>
                                        </span>
                                    </div>
                                </button>
                            </h2>
                            <div id="repair-collapse-<?php echo $repair['Request_ID']; ?>" class="accordion-collapse collapse" aria-labelledby="repair-<?php echo $repair['Request_ID']; ?>" data-bs-parent="#repairRequestsAccordion">
                                <div class="accordion-body">
                                    <p><strong>Description:</strong> <?php echo htmlspecialchars($repair['Description']); ?></p>
                                    <p><strong>Room:</strong> #<?php echo $repair['Room_ID']; ?></p>
                                    <?php if (!empty($repair['Staff_Name'])): ?>
                                    <p><strong>Assigned To:</strong> <?php echo htmlspecialchars($repair['Staff_Name']); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($repair['Scheduled_Date'])): ?>
                                    <p><strong>Scheduled Date:</strong> <?php echo date('M d, Y', strtotime($repair['Scheduled_Date'])); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php $counter++; endwhile; ?>
                    </div>
                    <a href="repair_requests.php" class="btn btn-outline-danger btn-sm mt-2">View All Requests</a>
                    <?php else: ?>
                    <div class="text-center py-3">
                        <i class="fas fa-wrench fa-3x text-muted mb-3"></i>
                        <p>No repair requests found.</p>
                        <a href="repair_requests.php" class="btn btn-danger btn-sm">Submit New Request</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../layout.php';
?>