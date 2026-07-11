<?php
require_once '../db_connection.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] != "admin") {
    header("location: ../login.php");
    exit();
}

$page_title = "Schedule Appointment - Admin Dashboard";

// Check if student ID is provided
if (!isset($_GET['student_id']) || empty($_GET['student_id'])) {
    $_SESSION['error_msg'] = "Student ID is required!";
    header("location: students.php");
    exit();
}

$student_id = $_GET['student_id'];

// Get student information
$sql = "SELECT * FROM Student WHERE Student_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error_msg'] = "Student not found!";
    header("location: students.php");
    exit();
}

$student = $result->fetch_assoc();

// Get available rooms
$sql_rooms = "SELECT * FROM Room WHERE Status != 'Maintenance' ORDER BY Room_ID";
$result_rooms = $conn->query($sql_rooms);

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize input data
    $room_id = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT);
    $appointment_date = filter_input(INPUT_POST, 'appointment_date', FILTER_SANITIZE_STRING);
    $appointment_time = filter_input(INPUT_POST, 'appointment_time', FILTER_SANITIZE_STRING);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    
    // Combine date and time
    $appointment_datetime = $appointment_date . ' ' . $appointment_time;
    
    // Validation
    $errors = [];
    
    if (!$room_id) {
        $errors[] = "Valid room is required!";
    }
    
    if (empty($appointment_date)) {
        $errors[] = "Appointment date is required!";
    }
    
    if (empty($appointment_time)) {
        $errors[] = "Appointment time is required!";
    }
    
    if (empty($status)) {
        $errors[] = "Appointment status is required!";
    }
    
    // Check for appointment conflicts
    if ($room_id && !empty($appointment_datetime)) {
        $sql_check = "SELECT * FROM Appointment 
                      WHERE Room_ID = ? 
                      AND Appointment_Date = ? 
                      AND Status IN ('Pending', 'Confirmed')";
        
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("is", $room_id, $appointment_datetime);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows > 0) {
            $errors[] = "There is already an appointment scheduled for this room at the selected time!";
        }
    }
    
    // If no errors, insert the appointment
    if (empty($errors)) {
        $sql = "INSERT INTO Appointment (Student_ID, Room_ID, Appointment_Date, Status) 
                VALUES (?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiss", $student_id, $room_id, $appointment_datetime, $status);
        
        if ($stmt->execute()) {
            $_SESSION['success_msg'] = "Appointment scheduled successfully!";
            header("location: view_student.php?id=" . $student_id);
            exit();
        } else {
            $errors[] = "Error scheduling appointment: " . $conn->error;
        }
    }
}

// Build content for template
ob_start();
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Schedule Appointment</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="students.php">Students</a></li>
        <li class="breadcrumb-item"><a href="view_student.php?id=<?= $student_id ?>">View Student</a></li>
        <li class="breadcrumb-item active">Schedule Appointment</li>
    </ol>
    
    <div class="row">
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-calendar-plus me-1"></i>
                    Schedule Appointment for <?= htmlspecialchars($student['Student_Name']) ?>
                </div>
                <div class="card-body">
                    <?php if (isset($errors) && !empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="add_appointment.php?student_id=<?= $student_id ?>">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="room_id" name="room_id" required>
                                        <option value="">Select room</option>
                                        <?php while ($room = $result_rooms->fetch_assoc()): ?>
                                            <option value="<?= $room['Room_ID'] ?>">
                                                Room #<?= $room['Room_ID'] ?> (<?= $room['Room_Type'] ?>) - 
                                                <?= $room['Status'] ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                    <label for="room_id">Room</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="">Select status</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Confirmed" selected>Confirmed</option>
                                    </select>
                                    <label for="status">Appointment Status</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input class="form-control" id="appointment_date" name="appointment_date" type="date" min="<?= date('Y-m-d') ?>" required />
                                    <label for="appointment_date">Appointment Date</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input class="form-control" id="appointment_time" name="appointment_time" type="time" required />
                                    <label for="appointment_time">Appointment Time</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4 mb-0">
                            <div class="d-flex align-items-center justify-content-between">
                                <a href="view_student.php?id=<?= $student_id ?>" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Schedule Appointment</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Set default date to today
        const today = new Date().toISOString().split('T')[0];
        $('#appointment_date').val(today);
        
        // Set default time to current hour rounded up to nearest 30 minutes
        const now = new Date();
        now.setMinutes(Math.ceil(now.getMinutes() / 30) * 30);
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        $('#appointment_time').val(`${hours}:${minutes}`);
    });
</script>

<?php
$content = ob_get_clean();
include '../layout.php';
?>