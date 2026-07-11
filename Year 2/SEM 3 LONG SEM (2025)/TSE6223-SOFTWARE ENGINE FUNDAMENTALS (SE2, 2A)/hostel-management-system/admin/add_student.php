<?php
require_once '../db_connection.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] != "admin") {
    header("location: ../login.php");
    exit();
}

$page_title = "Add New Student - Admin Dashboard";

// Initialize form variables
$student_name = $student_email = $student_phone = $student_password = $room_id = $course = $emergency_contact = "";
$student_name_err = $student_email_err = $student_phone_err = $student_password_err = $course_err = $emergency_contact_err = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate name
    if (empty(trim($_POST["student_name"]))) {
        $student_name_err = "Please enter student name.";
    } else {
        $student_name = trim($_POST["student_name"]);
    }
    
    // Validate email
    if (empty(trim($_POST["student_email"]))) {
        $student_email_err = "Please enter student email.";
    } else {
        // Prepare a select statement
        $sql = "SELECT Student_ID FROM Student WHERE Student_Email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $param_email);
        $param_email = trim($_POST["student_email"]);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $student_email_err = "This email is already registered.";
        } else {
            $student_email = trim($_POST["student_email"]);
        }
        $stmt->close();
    }
    
    // Validate phone
    if (empty(trim($_POST["student_phone"]))) {
        $student_phone_err = "Please enter student phone.";
    } else {
        $student_phone = trim($_POST["student_phone"]);
    }
    
    // Validate password
    if (empty(trim($_POST["student_password"]))) {
        $student_password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["student_password"])) < 6) {
        $student_password_err = "Password must have at least 6 characters.";
    } else {
        $student_password = trim($_POST["student_password"]);
    }
    
    // Validate course
    if (empty(trim($_POST["course"]))) {
        $course_err = "Please select a course.";
    } else {
        $course = trim($_POST["course"]);
    }
    
    // Validate emergency contact
    if (empty(trim($_POST["emergency_contact"]))) {
        $emergency_contact_err = "Please enter emergency contact.";
    } else {
        $emergency_contact = trim($_POST["emergency_contact"]);
    }
    
    // Get room ID (optional)
    $room_id = !empty($_POST["room_id"]) ? $_POST["room_id"] : NULL;
    
    // Check input errors before inserting into database
    if (empty($student_name_err) && empty($student_email_err) && empty($student_phone_err) && empty($student_password_err) && empty($course_err) && empty($emergency_contact_err)) {
        
        // Prepare an insert statement
        $sql = "INSERT INTO Student (Student_Name, Student_Email, Student_Phone, Student_Password, Course, Emergency_Contact, Room_ID) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", $param_name, $param_email, $param_phone, $param_password, $param_course, $param_emergency_contact, $param_room_id);
        
        // Set parameters
        $param_name = $student_name;
        $param_email = $student_email;
        $param_phone = $student_phone;
        $param_password = password_hash($student_password, PASSWORD_DEFAULT); // Hash the password
        $param_course = $course;
        $param_emergency_contact = $emergency_contact;
        $param_room_id = $room_id;
        
        // Attempt to execute the statement
        if ($stmt->execute()) {
            // If room is assigned, update room occupancy
            if (!empty($room_id)) {
                $update_room = "UPDATE Room SET Current_Occupancy = Current_Occupancy + 1 WHERE Room_ID = ?";
                $room_stmt = $conn->prepare($update_room);
                $room_stmt->bind_param("i", $room_id);
                $room_stmt->execute();
                $room_stmt->close();
            }
            
            // Create success message and redirect
            $_SESSION['success_msg'] = "Student added successfully!";
            header("location: students.php");
            exit();
        } else {
            $_SESSION['error_msg'] = "Something went wrong. Please try again later.";
        }
        
        // Close statement
        $stmt->close();
    }
}

// Get available rooms for dropdown
$rooms_sql = "SELECT Room_ID, Room_Type, Capacity, Current_Occupancy, Status FROM Room WHERE Status = 'Available' OR Status = 'Occupied' ORDER BY Room_ID";
$rooms_result = $conn->query($rooms_sql);

// Build content for template
ob_start();
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Add New Student</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="students.php">Students</a></li>
        <li class="breadcrumb-item active">Add New Student</li>
    </ol>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-user-plus me-1"></i>
            Student Information
        </div>
        <div class="card-body">
            <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="text" name="student_name" class="form-control <?= (!empty($student_name_err)) ? 'is-invalid' : ''; ?>" id="studentName" placeholder="John Doe" value="<?= $student_name; ?>">
                            <label for="studentName">Full Name</label>
                            <div class="invalid-feedback"><?= $student_name_err; ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="email" name="student_email" class="form-control <?= (!empty($student_email_err)) ? 'is-invalid' : ''; ?>" id="studentEmail" placeholder="name@example.com" value="<?= $student_email; ?>">
                            <label for="studentEmail">Email Address</label>
                            <div class="invalid-feedback"><?= $student_email_err; ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="tel" name="student_phone" class="form-control <?= (!empty($student_phone_err)) ? 'is-invalid' : ''; ?>" id="studentPhone" placeholder="123-456-7890" value="<?= $student_phone; ?>">
                            <label for="studentPhone">Phone Number</label>
                            <div class="invalid-feedback"><?= $student_phone_err; ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="password" name="student_password" class="form-control <?= (!empty($student_password_err)) ? 'is-invalid' : ''; ?>" id="studentPassword" placeholder="Password">
                            <label for="studentPassword">Password</label>
                            <div class="invalid-feedback"><?= $student_password_err; ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <select name="course" class="form-select <?= (!empty($course_err)) ? 'is-invalid' : ''; ?>" id="courseSelect">
                                <option value="">Select Course</option>
                                <option value="Computer Science" <?= ($course == 'Computer Science') ? 'selected' : ''; ?>>Computer Science</option>
                                <option value="Information Technology" <?= ($course == 'Information Technology') ? 'selected' : ''; ?>>Information Technology</option>
                                <option value="Software Engineering" <?= ($course == 'Software Engineering') ? 'selected' : ''; ?>>Software Engineering</option>
                                <option value="Business Administration" <?= ($course == 'Business Administration') ? 'selected' : ''; ?>>Business Administration</option>
                                <option value="Accounting" <?= ($course == 'Accounting') ? 'selected' : ''; ?>>Accounting</option>
                                <option value="Marketing" <?= ($course == 'Marketing') ? 'selected' : ''; ?>>Marketing</option>
                                <option value="Engineering" <?= ($course == 'Engineering') ? 'selected' : ''; ?>>Engineering</option>
                                <option value="Medicine" <?= ($course == 'Medicine') ? 'selected' : ''; ?>>Medicine</option>
                                <option value="Law" <?= ($course == 'Law') ? 'selected' : ''; ?>>Law</option>
                                <option value="Other" <?= ($course == 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                            <label for="courseSelect">Course/Program</label>
                            <div class="invalid-feedback"><?= $course_err; ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="tel" name="emergency_contact" class="form-control <?= (!empty($emergency_contact_err)) ? 'is-invalid' : ''; ?>" id="emergencyContact" placeholder="Emergency contact number" value="<?= $emergency_contact; ?>">
                            <label for="emergencyContact">Emergency Contact</label>
                            <div class="invalid-feedback"><?= $emergency_contact_err; ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="form-floating mb-3">
                    <select name="room_id" class="form-select" id="roomSelect">
                        <option value="">No Room Assignment</option>
                        <?php while ($room = $rooms_result->fetch_assoc()): ?>
                            <?php 
                            // Only show rooms that aren't full
                            if ($room['Current_Occupancy'] < $room['Capacity']): 
                                $spaces_left = $room['Capacity'] - $room['Current_Occupancy'];
                            ?>
                                <option value="<?= $room['Room_ID'] ?>" <?= ($room_id == $room['Room_ID']) ? 'selected' : ''; ?>>
                                    Room #<?= $room['Room_ID'] ?> (<?= $room['Room_Type'] ?>, <?= $spaces_left ?> space(s) left)
                                </option>
                            <?php endif; ?>
                        <?php endwhile; ?>
                    </select>
                    <label for="roomSelect">Room Assignment (Optional)</label>
                </div>
                
                <div class="mt-4 mb-0">
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-block">Add Student</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-footer text-center py-3">
            <div class="small"><a href="students.php">Cancel and return to student list</a></div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../layout.php';
?>