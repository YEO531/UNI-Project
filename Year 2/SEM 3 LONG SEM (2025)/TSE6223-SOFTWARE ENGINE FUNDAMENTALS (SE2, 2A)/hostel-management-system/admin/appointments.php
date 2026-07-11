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
$page_title = "Manage Appointments - Hostel Management System";

// Check if the user has access to this page (only admins should access)
if ($user_role != "admin") {
    header("location: ../index.php");
    exit();
}

// Process form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $appointment_id = sanitize_input($_POST['appointment_id']);
    $status = sanitize_input($_POST['status']);
    
    $sql = "UPDATE Appointment SET Status = ? WHERE Appointment_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $appointment_id);
    
    if ($stmt->execute()) {
        $success_message = "Appointment status updated successfully!";
    } else {
        $error_message = "Error updating appointment status: " . $conn->error;
    }
    $stmt->close();
}

// Fetch all appointments with student info
$sql = "SELECT a.*, s.Student_Name, r.Room_Type 
        FROM Appointment a
        JOIN Student s ON a.Student_ID = s.Student_ID
        JOIN Room r ON a.Room_ID = r.Room_ID
        ORDER BY a.Appointment_Date DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$appointments = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Build content for template
ob_start();
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Manage Appointments</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Manage Appointments</li>
    </ol>
    
    <?php if(isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <?php if(isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
    <!-- Appointments List -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            All Appointments
        </div>
        <div class="card-body">
            <?php if(count($appointments) > 0): ?>
                <table id="appointmentsTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Appointment ID</th>
                            <th>Student</th>
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
                                <td><?php echo $appointment['Student_Name']; ?></td>
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
                                    <button type="button" class="btn btn-sm btn-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#updateModal<?php echo $appointment['Appointment_ID']; ?>">
                                        Update Status
                                    </button>
                                    
                                    <!-- Update Status Modal -->
                                    <div class="modal fade" id="updateModal<?php echo $appointment['Appointment_ID']; ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Update Appointment Status</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="appointment_id" value="<?php echo $appointment['Appointment_ID']; ?>">
                                                        
                                                        <div class="mb-3">
                                                            <label for="status<?php echo $appointment['Appointment_ID']; ?>" class="form-label">Status:</label>
                                                            <select class="form-select" id="status<?php echo $appointment['Appointment_ID']; ?>" name="status" required>
                                                                <option value="Pending" <?php echo ($appointment['Status'] == 'Pending' ? 'selected' : ''); ?>>Pending</option>
                                                                <option value="Confirmed" <?php echo ($appointment['Status'] == 'Confirmed' ? 'selected' : ''); ?>>Confirmed</option>
                                                                <option value="Cancelled" <?php echo ($appointment['Status'] == 'Cancelled' ? 'selected' : ''); ?>>Cancelled</option>
                                                                <option value="Completed" <?php echo ($appointment['Status'] == 'Completed' ? 'selected' : ''); ?>>Completed</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" name="update_status" class="btn btn-primary">Save changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info">No appointments found.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Initialize DataTable
    $(document).ready(function() {
        $('#appointmentsTable').DataTable({
            order: [[3, 'asc']] // Sort by date & time column in ascending order
        });
    });
</script>

<?php
$content = ob_get_clean();
include '../layout.php';
?>