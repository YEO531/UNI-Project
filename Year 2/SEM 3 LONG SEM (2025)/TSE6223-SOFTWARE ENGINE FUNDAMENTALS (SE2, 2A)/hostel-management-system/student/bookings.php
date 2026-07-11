<?php
require_once '../db_connection.php';

// Check if user is logged in and is a student
//session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] != "student") {
    header("location: ../login.php");
    exit();
}

$student_id = $_SESSION["user_id"];
$student_name = $_SESSION["user_name"];

// Get student's bookings
$sql = "SELECT b.*, r.Room_Type 
        FROM Booking b
        JOIN Room r ON b.Room_ID = r.Room_ID
        WHERE b.Student_ID = ?
        ORDER BY b.Booking_Date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

// Handle booking cancellation
$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_booking'])) {
    $booking_id = $_POST['booking_id'];
    
    // Check if booking belongs to the student
    $sql_check = "SELECT * FROM Booking WHERE Booking_ID = ? AND Student_ID = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $booking_id, $student_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows > 0) {
        $booking_data = $result_check->fetch_assoc();
        
        // Can only cancel pending or confirmed bookings
        if ($booking_data['Status'] == 'Pending' || $booking_data['Status'] == 'Confirmed') {
            // Update booking status
            $sql_update = "UPDATE Booking SET Status = 'Cancelled' WHERE Booking_ID = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("i", $booking_id);
            
            if ($stmt_update->execute()) {
                $success_message = "Booking cancelled successfully";
                
                // Refresh the page to show updated data
                header("Location: bookings.php?success=cancelled");
                exit();
            } else {
                $error_message = "Error cancelling booking: " . $conn->error;
            }
        } else {
            $error_message = "This booking cannot be cancelled because it is already " . $booking_data['Status'];
        }
    } else {
        $error_message = "Invalid booking request";
    }
}

// Check for success message in URL
if (isset($_GET['success']) && $_GET['success'] == 'cancelled') {
    $success_message = "Booking cancelled successfully";
}

$page_title = "My Bookings - Hostel Management System";

// Build content for template
ob_start();
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">My Bookings</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">My Bookings</li>
    </ol>
    
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><?= $success_message ?></div>
    <?php endif; ?>
    
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?= $error_message ?></div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-book me-1"></i>
            Your Booking History
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="bookingsTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Room Number</th>
                            <th>Room Type</th>
                            <th>Booking Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['Booking_ID'] ?></td>
                                    <td><?= $row['Room_ID'] ?></td>
                                    <td><?= $row['Room_Type'] ?></td>
                                    <td><?= date('Y-m-d', strtotime($row['Booking_Date'])) ?></td>
                                    <td>
                                        <?php
                                        $status_class = '';
                                        switch ($row['Status']) {
                                            case 'Confirmed':
                                                $status_class = 'bg-success';
                                                break;
                                            case 'Pending':
                                                $status_class = 'bg-warning';
                                                break;
                                            case 'Cancelled':
                                                $status_class = 'bg-danger';
                                                break;
                                            case 'Completed':
                                                $status_class = 'bg-info';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?= $status_class ?>">
                                            <?= $row['Status'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($row['Status'] == 'Pending' || $row['Status'] == 'Confirmed'): ?>
                                            <form method="post" action="" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                                <input type="hidden" name="booking_id" value="<?= $row['Booking_ID'] ?>">
                                                <button type="submit" name="cancel_booking" class="btn btn-danger btn-sm">
                                                    Cancel
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button class="btn btn-secondary btn-sm" disabled>No Actions</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No booking records found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-info-circle me-1"></i>
            Booking Information
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Booking Process</h5>
                    <ol>
                        <li>Browse available rooms in the <a href="rooms.php">View Rooms</a> section.</li>
                        <li>Select a room and submit a booking request.</li>
                        <li>Admin will review your request and approve or reject it.</li>
                        <li>If approved, you will be assigned to the room and can move in.</li>
                        <li>Payment must be made within 48 hours of booking confirmation.</li>
                    </ol>
                </div>
                <div class="col-md-6">
                    <h5>Booking Status Meanings</h5>
                    <ul>
                        <li><span class="badge bg-warning">Pending</span> - Your booking request is awaiting admin approval.</li>
                        <li><span class="badge bg-success">Confirmed</span> - Your booking has been approved by admin.</li>
                        <li><span class="badge bg-danger">Cancelled</span> - The booking was cancelled by you or admin.</li>
                        <li><span class="badge bg-info">Completed</span> - You have successfully occupied the room.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../layout.php';
?>