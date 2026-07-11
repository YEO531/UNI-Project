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

// Get student information including room details
$sql = "SELECT s.*, r.Room_ID, r.Room_Type, r.Capacity 
        FROM Student s 
        LEFT JOIN Room r ON s.Room_ID = r.Room_ID 
        WHERE s.Student_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student_data = $result->fetch_assoc();

// Get active bookings
$sql_bookings = "SELECT COUNT(*) as active_bookings FROM Booking 
                WHERE Student_ID = ? AND Status IN ('Pending', 'Confirmed')";
$stmt_bookings = $conn->prepare($sql_bookings);
$stmt_bookings->bind_param("i", $student_id);
$stmt_bookings->execute();
$result_bookings = $stmt_bookings->get_result();
$booking_data = $result_bookings->fetch_assoc();

// Get upcoming appointments
$sql_appointments = "SELECT COUNT(*) as upcoming_appointments FROM Appointment 
                    WHERE Student_ID = ? AND Status IN ('Pending', 'Confirmed') 
                    AND Appointment_Date > NOW()";
$stmt_appointments = $conn->prepare($sql_appointments);
$stmt_appointments->bind_param("i", $student_id);
$stmt_appointments->execute();
$result_appointments = $stmt_appointments->get_result();
$appointment_data = $result_appointments->fetch_assoc();

// Get active repair requests
$sql_repairs = "SELECT COUNT(*) as active_repairs FROM RepairRequest 
                WHERE Student_ID = ? AND Status IN ('Pending', 'Scheduled', 'In Progress')";
$stmt_repairs = $conn->prepare($sql_repairs);
$stmt_repairs->bind_param("i", $student_id);
$stmt_repairs->execute();
$result_repairs = $stmt_repairs->get_result();
$repair_data = $result_repairs->fetch_assoc();

// Get recent payment history
$sql_payments = "SELECT * FROM Payment 
                WHERE Student_ID = ? 
                ORDER BY Payment_Date DESC LIMIT 5";
$stmt_payments = $conn->prepare($sql_payments);
$stmt_payments->bind_param("i", $student_id);
$stmt_payments->execute();
$result_payments = $stmt_payments->get_result();

// Get recent repair requests
$sql_recent_repairs = "SELECT rr.*, r.Room_Type 
                      FROM RepairRequest rr
                      JOIN Room r ON rr.Room_ID = r.Room_ID
                      WHERE rr.Student_ID = ? 
                      ORDER BY rr.Request_Date DESC LIMIT 5";
$stmt_recent_repairs = $conn->prepare($sql_recent_repairs);
$stmt_recent_repairs->bind_param("i", $student_id);
$stmt_recent_repairs->execute();
$result_recent_repairs = $stmt_recent_repairs->get_result();

$page_title = "Student Dashboard - Hostel Management System";

// Build content for template
ob_start();
?>

<div class="container-fluid px-4">
    <h1 class="mt-4 bg-black text-info p-3 rounded d-inline-block">Student Dashboard</h1>
    
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <h4>Room Status</h4>
                    <?php if ($student_data['Room_ID']): ?>
                        <p>Room #<?= $student_data['Room_ID'] ?> (<?= $student_data['Room_Type'] ?>)</p>
                    <?php else: ?>
                        <p>No Room Assigned</p>
                    <?php endif; ?>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="rooms.php">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <h4>Bookings</h4>
                    <p><?= $booking_data['active_bookings'] ?> Active</p>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="bookings.php">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <h4>Appointments</h4>
                    <p><?= $appointment_data['upcoming_appointments'] ?> Upcoming</p>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="appointments.php">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white mb-4">
                <div class="card-body">
                    <h4>Repair Requests</h4>
                    <p><?= $repair_data['active_repairs'] ?> Pending</p>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="repair_requests.php">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-credit-card me-1"></i>
                    Recent Payment History
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Purpose</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result_payments->num_rows > 0): ?>
                                    <?php while ($payment = $result_payments->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= date('Y-m-d', strtotime($payment['Payment_Date'])) ?></td>
                                            <td>$<?= $payment['Amount'] ?></td>
                                            <td><?= $payment['Purpose'] ?></td>
                                            <td>
                                                <span class="badge <?= $payment['Status'] == 'Completed' ? 'bg-success' : 'bg-warning' ?>">
                                                    <?= $payment['Status'] ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No payment records found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-tools me-1"></i>
                    Recent Repair Requests
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Room</th>
                                    <th>Issue</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result_recent_repairs->num_rows > 0): ?>
                                    <?php while ($repair = $result_recent_repairs->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= date('Y-m-d', strtotime($repair['Request_Date'])) ?></td>
                                            <td>Room #<?= $repair['Room_ID'] ?> (<?= $repair['Room_Type'] ?>)</td>
                                            <td><?= substr($repair['Description'], 0, 30) ?>...</td>
                                            <td>
                                                <?php
                                                $status_class = '';
                                                switch ($repair['Status']) {
                                                    case 'Completed':
                                                        $status_class = 'bg-success';
                                                        break;
                                                    case 'In Progress':
                                                        $status_class = 'bg-info';
                                                        break;
                                                    case 'Scheduled':
                                                        $status_class = 'bg-primary';
                                                        break;
                                                    default:
                                                        $status_class = 'bg-warning';
                                                }
                                                ?>
                                                <span class="badge <?= $status_class ?>">
                                                    <?= $repair['Status'] ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No repair requests found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../layout.php';
?>