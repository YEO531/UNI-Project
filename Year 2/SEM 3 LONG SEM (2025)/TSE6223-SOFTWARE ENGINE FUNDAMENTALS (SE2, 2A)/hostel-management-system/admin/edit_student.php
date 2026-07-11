<?php
require_once '../db_connection.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] != "admin") {
    header("location: ../login.php");
    exit();
}

$page_title = "Edit Student - Admin Dashboard";

// Check if student ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_msg'] = "Student ID is missing!";
    header("location: students.php");
    exit();
}

$student_id = $_GET['id'];

// Initialize form variables
$student_name = $student_email = $student_phone = $room_id = $course = $emergency_contact = "";
$student_name_err = $student_email_err = $student_phone_err = $course_err = $emergency_contact_err = "";
$password_change = false;
$new_password = "";
$new_password_err = "";

// Fetch student data
$sql = "SELECT * FROM Student WHERE Student_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $student = $result->fetch_assoc();
    $student_name = $student['Student_Name'];
    $student_email = $student['Student_Email'];
    $student_phone = $student['Student_Phone'];
    $current_room_id = $student['Room_ID'];
    $room_id = $current_room_id;
    $course = $student['Course'];
    $emergency_contact = $student['Emergency_Contact'];
} else {
    $_SESSION['error_msg'] = "Student not found!";
    header("location: students.php");
    exit();
}
$stmt->close();

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
        // Check if the email address belongs to another user
        $sql = "SELECT Student_ID FROM Student WHERE Student_Email = ? AND Student_ID != ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $param_email, $param_student_id);
        $param_email = trim($_POST["student_email"]);
        $param_student_id = $student_id;
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $student_email_err = "This email is already registered to another student.";
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
    
    // Check if password should be changed
    $password_change = isset($_POST["change_password"]) && $_POST["change_password"] == "1";
    
    // Validate new password if the change password checkbox is checked
    if ($password_change) {
        if (empty(trim($_POST["new_password"]))) {
            $new_password_err = "Please enter a new password.";
        } elseif (strlen(trim($_POST["new_password"])) < 6) {
            $new_password_err = "Password must have at least 6 characters.";
        } else {
            $new_password = trim($_POST["new_password"]);
        }
    }
    
    // Get room ID (optional)
    $room_id = !empty($_POST["room_id"]) ? $_POST["room_id"] : NULL;
    
    // Check input errors before updating the database
    if (empty($student_name_err) && empty($student_email_err) && empty($student_phone_err) && 
        empty($course_err) && empty($emergency_contact_err) &&
        (!$password_change || empty($new_password_err))) {
        
        // Prepare an update statement
        if ($password_change) {
            $sql = "UPDATE Student SET Student_Name = ?, Student_Email = ?, Student_Phone = ?, Student_Password = ?, Course = ?, Emergency_Contact = ?, Room_ID = ? WHERE Student_ID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssii", $param_name, $param_email, $param_phone, $param_password, $param_course, $param_emergency_contact, $param_room_id, $param_student_id);
            $param_password = password_hash($new_password, PASSWORD_DEFAULT);
        } else {
            $sql = "UPDATE Student SET Student_Name = ?, Student_Email = ?, Student_Phone = ?, Course = ?, Emergency_Contact = ?, Room_ID = ? WHERE Student_ID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssii", $param_name, $param_email, $param_phone, $param_course, $param_emergency_contact, $param_room_id, $param_student_id);
        }
        
        // Set parameters
        $param_name = $student_name;
        $param_email = $student_email;
        $param_phone = $student_phone;
        $param_course = $course;
        $param_emergency_contact = $emergency_contact;
        $param_room_id = $room_id;
        $param_student_id = $student_id;
        
        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            // Handle room occupancy updates if the room assignment changed
            if ($current_room_id != $room_id) {
                // Decrease occupancy of previous room if there was one
                if (!empty($current_room_id)) {
                    $update_old_room = "UPDATE Room SET Current_Occupancy = Current_Occupancy - 1 WHERE Room_ID = ?";
                    $old_room_stmt = $conn->prepare($update_old_room);
                    $old_room_stmt->bind_param("i", $current_room_id);
                    $old_room_stmt->execute();
                    $old_room_stmt->close();
                }
                
                // Increase occupancy of new room if there is one
                if (!empty($room_id)) {
                    $update_new_room = "UPDATE Room SET Current_Occupancy = Current_Occupancy + 1 WHERE Room_ID = ?";
                    $new_room_stmt = $conn->prepare($update_new_room);
                    $new_room_stmt->bind_param("i", $room_id);
                    $new_room_stmt->execute();
                    $new_room_stmt->close();
                }
            }
            
            // Create success message and redirect
            $_SESSION['success_msg'] = "Student information updated successfully!";
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
$rooms_sql = "SELECT Room_ID, Room_Type, Capacity, Current_Occupancy, Status FROM Room 
              WHERE (Status = 'Available' OR Status = 'Occupied') AND
              (Room_ID = ? OR Current_Occupancy < Capacity) 
              ORDER BY Room_ID";
$rooms_stmt = $conn->prepare($rooms_sql);
$rooms_stmt->bind_param("i", $current_room_id);
$rooms_stmt->execute();
$rooms_result = $rooms_stmt->get_result();

// Build content for template
ob_start();
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Edit Student</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="students.php">Students</a></li>
        <li class="breadcrumb-item active">Edit Student</li>
    </ol>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-user-edit me-1"></i>
            Update Student Information
        </div>
        <div class="card-body">
            <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $student_id; ?>" method="POST">
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
                
                <div class="form-floating mb-3">
                    <input type="tel" name="student_phone" class="form-control <?= (!empty($student_phone_err)) ? 'is-invalid' : ''; ?>" id="studentPhone" placeholder="123-456-7890" value="<?= $student_phone; ?>">
                    <label for="studentPhone">Phone Number</label>
                    <div class="invalid-feedback"><?= $student_phone_err; ?></div>
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
                            <input type="tel" name="emergency_contact" class="form-control <?= (!empty($emergency_contact_err)) ? 'is-invalid' : ''; ?>" id="emergencyContact" placeholder="Emergency Contact" value="<?= $emergency_contact; ?>">
                            <label for="emergencyContact">Emergency Contact</label>
                            <div class="invalid-feedback"><?= $emergency_contact_err; ?></div>
                            <div class="form-text">Enter a phone number for emergency situations</div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="change_password" value="1" id="changePassword" <?= $password_change ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="changePassword">
                            Change Password
                        </label>
                    </div>
                </div>
                
                <div id="passwordField" class="form-floating mb-3" style="display: <?= $password_change ? 'block' : 'none'; ?>">
                    <input type="password" name="new_password" class="form-control <?= (!empty($new_password_err)) ? 'is-invalid' : ''; ?>" id="newPassword" placeholder="New Password">
                    <label for="newPassword">New Password</label>
                    <div class="invalid-feedback"><?= $new_password_err; ?></div>
                </div>
                
                <div class="form-floating mb-3">
                    <select name="room_id" class="form-select" id="roomSelect">
                        <option value="">No Room Assignment</option>
                        <?php while ($room = $rooms_result->fetch_assoc()): ?>
                            <?php 
                            // Calculate spaces left, making sure to count the current student if they're already in this room
                            $adjustment = ($room['Room_ID'] == $current_room_id) ? 1 : 0;
                            $spaces_left = $room['Capacity'] - $room['Current_Occupancy'] + $adjustment;
                            
                            // Only show rooms that aren't full or the current room
                            if ($spaces_left > 0 || $room['Room_ID'] == $current_room_id): 
                            ?>
                                <option value="<?= $room['Room_ID'] ?>" <?= ($room['Room_ID'] == $current_room_id) ? 'selected' : ''; ?>>
                                    Room #<?= $room['Room_ID'] ?> (<?= $room['Room_Type'] ?>, <?= $spaces_left ?> space(s) left)
                                </option>
                            <?php endif; ?>
                        <?php endwhile; ?>
                    </select>
                    <label for="roomSelect">Room Assignment</label>
                </div>
                
                <div class="mt-4 mb-0">
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-block">Update Student</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-footer text-center py-3">
            <div class="small"><a href="students.php">Cancel and return to student list</a></div>
        </div>
    </div>
</div>

<!-- JavaScript to toggle password field -->
<script>
    document.getElementById('changePassword').addEventListener('change', function() {
        document.getElementById('passwordField').style.display = this.checked ? 'block' : 'none';
    });
</script>

<?php
$content = ob_get_clean();
include '../layout.php';
?>