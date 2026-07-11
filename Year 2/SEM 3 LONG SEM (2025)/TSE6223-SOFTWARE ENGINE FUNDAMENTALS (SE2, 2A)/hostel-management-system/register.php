<?php
require_once 'db_connection.php';

$page_title = "Register - Hostel Management System";
$error = "";
$success = "";

// Process registration form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_type = sanitize_input($_POST['user_type']);
    $office_location = sanitize_input($_POST['office_location'] ?? '');
    $course = sanitize_input($_POST['course'] ?? '');
    $emergency_contact = sanitize_input($_POST['emergency_contact'] ?? '');
    
    // Validate input
    if (empty($name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password) || empty($user_type)) {
        $error = "All fields are required";
    } elseif ($user_type == 'student' && (empty($course) || empty($emergency_contact))) {
        $error = "Course and Emergency Contact are required for students";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        // Table selection based on user type
        $table = "";
        $id_column = "";
        $name_column = "";
        $email_column = "";
        $phone_column = "";
        $password_column = "";
        $insert_sql = "";
        
        switch ($user_type) {
            case 'student':
                $table = "Student";
                $id_column = "Student_ID";
                $name_column = "Student_Name";
                $email_column = "Student_Email";
                $phone_column = "Student_Phone";
                $password_column = "Student_Password";
                $insert_sql = "INSERT INTO $table ($name_column, $email_column, $phone_column, $password_column, Course, Emergency_Contact) VALUES (?, ?, ?, ?, ?, ?)";
                break;
            case 'admin':
                $table = "Admin";
                $id_column = "Admin_ID";
                $name_column = "Admin_Name";
                $email_column = "Admin_Email";
                $phone_column = "Admin_Phone";
                $password_column = "Admin_Password";
                $insert_sql = "INSERT INTO $table ($name_column, $email_column, $phone_column, $password_column, Office_Location) VALUES (?, ?, ?, ?, ?)";
                break;
            case 'staff':
                $table = "MaintenanceStaff";
                $id_column = "Staff_ID";
                $name_column = "Staff_Name";
                $email_column = "Staff_Email";
                $phone_column = "Staff_Phone";
                $password_column = "Staff_Password";
                $insert_sql = "INSERT INTO $table ($name_column, $email_column, $phone_column, $password_column) VALUES (?, ?, ?, ?)";
                break;
            default:
                $error = "Invalid user type";
                break;
        }
        
        if (empty($error)) {
            // Check if email already exists
            $check_sql = "SELECT * FROM $table WHERE $email_column = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $error = "Email already exists. Please use a different email or login";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user
                $insert_stmt = $conn->prepare($insert_sql);
                
                if ($user_type == 'student') {
                    $insert_stmt->bind_param("ssssss", $name, $email, $phone, $hashed_password, $course, $emergency_contact);
                } elseif ($user_type == 'admin') {
                    $insert_stmt->bind_param("sssss", $name, $email, $phone, $hashed_password, $office_location);
                } else {
                    $insert_stmt->bind_param("ssss", $name, $email, $phone, $hashed_password);
                }
                
                if ($insert_stmt->execute()) {
                    $success = "Registration successful! You can now login.";
                } else {
                    $error = "Error: " . $insert_stmt->error;
                }
                
                $insert_stmt->close();
            }
            
            $check_stmt->close();
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
                <div class="card-header bg-primary text-white">
                    <h3 class="text-center font-weight-light my-2">Create Account</h3>
                </div>
                <div class="card-body">
                    <?php if(!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if(!empty($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="form-floating mb-3">
                            <select class="form-select" id="user_type" name="user_type" required onchange="toggleUserTypeFields()">
                                <option value="">Select User Type</option>
                                <option value="student">Student</option>
                                <option value="admin">Administrator</option>
                                <option value="staff">Maintenance Staff</option>
                            </select>
                            <label for="user_type">User Type</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input class="form-control" id="name" name="name" type="text" placeholder="Enter your name" required />
                            <label for="name">Full Name</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input class="form-control" id="email" name="email" type="email" placeholder="name@example.com" required />
                            <label for="email">Email address</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input class="form-control" id="phone" name="phone" type="tel" placeholder="Phone number" required />
                            <label for="phone">Phone Number</label>
                        </div>
                        
                        <!-- Student-specific fields -->
                        <div class="form-floating mb-3" id="course_field" style="display: none;">
                            <select class="form-select" id="course" name="course">
                                <option value="">Select Course</option>
                                <option value="Computer Science">Computer Science</option>
                                <option value="Information Technology">Information Technology</option>
                                <option value="Software Engineering">Software Engineering</option>
                                <option value="Business Administration">Business Administration</option>
                                <option value="Accounting">Accounting</option>
                                <option value="Marketing">Marketing</option>
                                <option value="Engineering">Engineering</option>
                                <option value="Medicine">Medicine</option>
                                <option value="Law">Law</option>
                                <option value="Other">Other</option>
                            </select>
                            <label for="course">Course/Program</label>
                        </div>
                        
                        <div class="form-floating mb-3" id="emergency_contact_field" style="display: none;">
                            <input class="form-control" id="emergency_contact" name="emergency_contact" type="tel" placeholder="Emergency contact number" />
                            <label for="emergency_contact">Emergency Contact</label>
                            <div class="form-text">Enter a phone number for emergency situations</div>
                        </div>
                        
                        <!-- Admin-specific field -->
                        <div class="form-floating mb-3" id="office_location_field" style="display: none;">
                            <input class="form-control" id="office_location" name="office_location" type="text" placeholder="Enter office location" />
                            <label for="office_location">Office Location</label>
                            <div class="form-text">Enter your office location (e.g., Building A, Floor 3, Room 301)</div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-floating mb-3 mb-md-0">
                                    <input class="form-control" id="password" name="password" type="password" placeholder="Create a password" required />
                                    <label for="password">Password</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3 mb-md-0">
                                    <input class="form-control" id="confirm_password" name="confirm_password" type="password" placeholder="Confirm password" required />
                                    <label for="confirm_password">Confirm Password</label>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 mb-0">
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-block">Create Account</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center py-3">
                    <div class="small"><a href="login.php">Have an account? Go to login</a></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleUserTypeFields() {
    const userType = document.getElementById('user_type').value;
    const officeLocationField = document.getElementById('office_location_field');
    const courseField = document.getElementById('course_field');
    const emergencyContactField = document.getElementById('emergency_contact_field');
    
    // Hide all conditional fields first
    officeLocationField.style.display = 'none';
    courseField.style.display = 'none';
    emergencyContactField.style.display = 'none';
    
    // Clear all conditional field values
    document.getElementById('office_location').value = '';
    document.getElementById('course').value = '';
    document.getElementById('emergency_contact').value = '';
    
    // Show relevant fields based on user type
    if (userType === 'admin') {
        officeLocationField.style.display = 'block';
        document.getElementById('office_location').required = true;
    } else if (userType === 'student') {
        courseField.style.display = 'block';
        emergencyContactField.style.display = 'block';
        document.getElementById('course').required = true;
        document.getElementById('emergency_contact').required = true;
    } else {
        // Remove required attributes for staff
        document.getElementById('office_location').required = false;
        document.getElementById('course').required = false;
        document.getElementById('emergency_contact').required = false;
    }
}
</script>

<?php
$content = ob_get_clean();
include 'layout.php';
?>