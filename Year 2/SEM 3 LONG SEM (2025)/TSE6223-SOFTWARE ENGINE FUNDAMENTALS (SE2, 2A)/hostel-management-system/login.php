<?php
require_once 'db_connection.php';

$page_title = "Login - Hostel Management System";
$error = "";

// Process login form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $user_type = sanitize_input($_POST['user_type']);
    
    if (empty($email) || empty($password) || empty($user_type)) {
        $error = "All fields are required";
    } else {
        // Check which table to query based on user type
        $table = "";
        $id_field = "";
        $name_field = "";
        
        switch ($user_type) {
            case 'student':
                $table = "Student";
                $id_field = "Student_ID";
                $name_field = "Student_Name";
                $password_field = "Student_Password";
                $email_field = "Student_Email";
                break;
            case 'admin':
                $table = "Admin";
                $id_field = "Admin_ID";
                $name_field = "Admin_Name";
                $password_field = "Admin_Password";
                $email_field = "Admin_Email";
                break;
            case 'staff':
                $table = "MaintenanceStaff";
                $id_field = "Staff_ID";
                $name_field = "Staff_Name";
                $password_field = "Staff_Password";
                $email_field = "Staff_Email";
                break;
            default:
                $error = "Invalid user type";
                break;
        }
        
        if (!empty($table)) {
            $sql = "SELECT $id_field, $name_field, $password_field FROM $table WHERE $email_field = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();
                
                // Verify password
                if (password_verify($password, $row[$password_field])) {
                    // Password is correct, start a new session
                    session_start();
                    
                    // Store data in session variables
                    $_SESSION["user_id"] = $row[$id_field];
                    $_SESSION["user_name"] = $row[$name_field];
                    $_SESSION["user_role"] = $user_type;
                    
                    // Redirect user based on role
                    switch ($user_type) {
                        case 'student':
                            header("location: student/dashboard.php");
                            break;
                        case 'admin':
                            header("location: admin/dashboard.php");
                            break;
                        case 'staff':
                            header("location: staff/dashboard.php");
                            break;
                    }
                    exit();
                } else {
                    $error = "Invalid password";
                }
            } else {
                $error = "No account found with that email";
            }
            
            $stmt->close();
        }
    }
}

// Build content for template
ob_start();
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-dark text-white">
                    <h3 class="text-center font-weight-light my-2">Login</h3>
                </div>
                <div class="card-body">
                    <?php if(!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="form-floating mb-3">
                            <input class="form-control" id="email" name="email" type="email" placeholder="name@example.com" required />
                            <label for="email">Email address</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input class="form-control" id="password" name="password" type="password" placeholder="Password" required />
                            <label for="password">Password</label>
                        </div>
                        <div class="form-group mb-3">
                            <label class="small mb-1">Login As:</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="user_type" id="studentRadio" value="student" checked>
                                <label class="form-check-label" for="studentRadio">
                                    Student
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="user_type" id="adminRadio" value="admin">
                                <label class="form-check-label" for="adminRadio">
                                    Administrator
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="user_type" id="staffRadio" value="staff">
                                <label class="form-check-label" for="staffRadio">
                                    Maintenance Staff
                                </label>
                            </div>
                        </div>
                        <div class="d-flex justify-content-center mt-4 mb-0">
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center py-3">
                    <div class="small"><a href="register.php">Need an account? Sign up!</a></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>