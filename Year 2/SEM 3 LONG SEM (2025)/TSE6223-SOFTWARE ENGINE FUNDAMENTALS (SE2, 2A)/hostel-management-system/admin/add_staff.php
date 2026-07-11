<?php
require_once '../db_connection.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] != "admin") {
    header("location: ../login.php");
    exit();
}

$page_title = "Add New Staff - Admin Dashboard";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate inputs
    $name = trim($_POST['staff_name']);
    $email = trim($_POST['staff_email']);
    $phone = trim($_POST['staff_phone']);
    $password = trim($_POST['staff_password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    $errors = [];
    
    // Basic validation
    if (empty($name)) {
        $errors[] = "Staff name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email address is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($phone)) {
        $errors[] = "Phone number is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    // Check if email already exists
    $check_email = "SELECT Staff_ID FROM MaintenanceStaff WHERE Staff_Email = ?";
    $stmt = $conn->prepare($check_email);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $errors[] = "Email address is already in use";
    }
    
    // If no errors, proceed with staff creation
    if (empty($errors)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new staff
        $sql = "INSERT INTO MaintenanceStaff (Staff_Name, Staff_Email, Staff_Phone, Staff_Password) 
                VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $email, $phone, $hashed_password);
        
        if ($stmt->execute()) {
            $_SESSION['success_msg'] = "Staff member added successfully!";
            header("location: staff.php");
            exit();
        } else {
            $errors[] = "Error adding staff member: " . $conn->error;
        }
    }
}

// Build content for template
ob_start();
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Add New Maintenance Staff</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="staff.php">Maintenance Staff</a></li>
        <li class="breadcrumb-item active">Add New Staff</li>
    </ol>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-user-plus me-1"></i>
            Staff Details
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
            
            <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input class="form-control" id="staff_name" name="staff_name" type="text" 
                                value="<?= isset($name) ? htmlspecialchars($name) : '' ?>" required />
                            <label for="staff_name">Full Name</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input class="form-control" id="staff_email" name="staff_email" type="email" 
                                value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" required />
                            <label for="staff_email">Email Address</label>
                        </div>
                    </div>
                </div>
                
                <div class="form-floating mb-3">
                    <input class="form-control" id="staff_phone" name="staff_phone" type="text" 
                        value="<?= isset($phone) ? htmlspecialchars($phone) : '' ?>" required />
                    <label for="staff_phone">Phone Number</label>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input class="form-control" id="staff_password" name="staff_password" type="password" 
                                minlength="6" required />
                            <label for="staff_password">Password</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input class="form-control" id="confirm_password" name="confirm_password" type="password" 
                                minlength="6" required />
                            <label for="confirm_password">Confirm Password</label>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4 mb-0">
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-block">Add Staff Member</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <div class="mt-3">
        <a href="staff.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Staff List
        </a>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../layout.php';
?>