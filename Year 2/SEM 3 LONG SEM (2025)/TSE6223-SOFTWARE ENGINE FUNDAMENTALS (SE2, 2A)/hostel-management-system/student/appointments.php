<?php
require_once '../db_connection.php';
//session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user_role"])) {
    header("location: ../login.php");
    exit();
}

// Get user role and set page title
$user_role = $_SESSION["user_role"];
$user_id = $_SESSION["user_id"];
$page_title = "My Appointments - Hostel Management System";

// Check if the user has access to this page (only students should access)
if ($user_role != "student") {
    header("location: ../index.php");
    exit();
}

// Process form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Create new appointment
    if (isset($_POST['create_appointment'])) {
        $room_id = sanitize_input($_POST['room_id']);
        $appointment_date = sanitize_input($_POST['appointment_date']);
        $appointment_time = sanitize_input($_POST['appointment_time']);
        
        // Combine date and time
        $appointment_datetime = $appointment_date . ' ' . $appointment_time . ':00';
        
        $sql = "INSERT INTO Appointment (Student_ID, Room_ID, Appointment_Date, Status) 
                VALUES (?, ?, ?, 'Pending')";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $user_id, $room_id, $appointment_datetime);
        
        if ($stmt->execute()) {
            $success_message = "Appointment scheduled successfully!";
        } else {
            $error_message = "Error scheduling appointment: " . $conn->error;
        }
        $stmt->close();
    }
    
    // Cancel appointment
    if (isset($_POST['cancel_appointment'])) {
        $appointment_id = sanitize_input($_POST['appointment_id']);
        
        $sql = "UPDATE Appointment SET Status = 'Cancelled' WHERE Appointment_ID = ? AND Student_ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $appointment_id, $user_id);
        
        if ($stmt->execute()) {
            $success_message = "Appointment cancelled successfully!";
        } else {
            $error_message = "Error cancelling appointment: " . $conn->error;
        }
        $stmt->close();
    }
}

// Fetch student appointments
$sql = "SELECT a.*, r.Room_Type 
        FROM Appointment a
        JOIN Room r ON a.Room_ID = r.Room_ID
        WHERE a.Student_ID = ?
        ORDER BY a.Appointment_Date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$appointments = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch available rooms for appointment scheduling
$sql = "SELECT Room_ID, Room_Type, Capacity FROM Room WHERE Status = 'Available' OR Status = 'Reserved'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$available_rooms = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Build content for template
ob_start();
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">My Appointments</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">My Appointments</li>
    </ol>
    
    <?php if(isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <?php if(isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
    <!-- Schedule Appointment Form -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-calendar-plus me-1"></i>
            Schedule New Appointment
        </div>
        <div class="card-body">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="room_id" class="form-label">Room to Visit:</label>
                        <select class="form-select" id="room_id" name="room_id" required>
                            <option value="">Select a room</option>
                            <?php foreach($available_rooms as $room): ?>
                                <option value="<?php echo $room['Room_ID']; ?>">
                                    <?php echo $room['Room_Type'] . " (Room " . $room['Room_ID'] . ", Capacity: " . $room['Capacity'] . ")"; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="appointment_date" class="form-label">Date:</label>
                        <input type="date" class="form-control" id="appointment_date" name="appointment_date" 
                               min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="appointment_time" class="form-label">Time:</label>
                        <input type="time" class="form-control" id="appointment_time" name="appointment_time" required>
                    </div>
                </div>
                <button type="submit" name="create_appointment" class="btn btn-primary">Schedule Appointment</button>
            </form>
        </div>
    </div>
    
    <!-- Appointments List -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            My Appointments
        </div>
        <div class="card-body">
            <?php if(count($appointments) > 0): ?>
                <table id="appointmentsTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Appointment ID</th>
                            <th>Room</th>
                            <th>Date & Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($appointments as $appointment): ?>
                            <tr>
                                <td><?php echo $appointment['Appointment_ID']; ?></td>
                                <td><?php echo $appointment['Room_Type'] . " (Room " . $appointment['Room_ID'] . ")"; ?></td>
                                <td><?php echo date('M d, Y h:i A', strtotime($appointment['Appointment_Date'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        switch($appointment['Status']) {
                                            case 'Pending': echo 'warning'; break;
                                            case 'Confirmed': echo 'success'; break;
                                            case 'Cancelled': echo 'danger'; break;
                                            case 'Completed': echo 'info'; break;
                                            default: echo 'secondary';
                                        }
                                    ?>">
                                        <?php echo $appointment['Status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if($appointment['Status'] == 'Pending' || $appointment['Status'] == 'Confirmed'): ?>
                                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" onsubmit="return confirm('Are you sure you want to cancel this appointment?');">
                                            <input type="hidden" name="appointment_id" value="<?php echo $appointment['Appointment_ID']; ?>">
                                            <button type="submit" name="cancel_appointment" class="btn btn-sm btn-danger">Cancel</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted">No actions available</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info">You have no appointments.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Initialize DataTable
    $(document).ready(function() {
        $('#appointmentsTable').DataTable();
        
        // Set minimum date for appointment to tomorrow
        var tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        var tomorrowFormatted = tomorrow.toISOString().split('T')[0];
        document.getElementById('appointment_date').setAttribute('min', tomorrowFormatted);
    });
</script>

<?php
$content = ob_get_clean();
include '../layout.php';
?>