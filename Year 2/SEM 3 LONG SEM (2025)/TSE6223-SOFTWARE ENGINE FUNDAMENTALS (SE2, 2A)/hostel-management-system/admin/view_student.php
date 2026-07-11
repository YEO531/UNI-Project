<?php
require_once '../db_connection.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] != "admin") {
    header("location: ../login.php");
    exit();
}

$page_title = "View Student - Admin Dashboard";

// Check if student ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_msg'] = "Student ID is required!";
    header("location: students.php");
    exit();
}

$student_id = $_GET['id'];

// Get student information with room details (including Course and Emergency_Contact)
$sql = "SELECT s.*, r.Room_Type, r.Capacity 
        FROM Student s 
        LEFT JOIN Room r ON s.Room_ID = r.Room_ID
        WHERE s.Student_ID = ?";

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

// Get booking history
$sql_bookings = "SELECT b.*, r.Room_Type 
                FROM Booking b 
                JOIN Room r ON b.Room_ID = r.Room_ID 
                WHERE b.Student_ID = ? 
                ORDER BY b.Booking_Date DESC";

$stmt_bookings = $conn->prepare($sql_bookings);
$stmt_bookings->bind_param("i", $student_id);
$stmt_bookings->execute();
$bookings_result = $stmt_bookings->get_result();

// Get appointment history
$sql_appointments = "SELECT a.*, r.Room_Type 
                    FROM Appointment a 
                    JOIN Room r ON a.Room_ID = r.Room_ID 
                    WHERE a.Student_ID = ? 
                    ORDER BY a.Appointment_Date DESC";

$stmt_appointments = $conn->prepare($sql_appointments);
$stmt_appointments->bind_param("i", $student_id);
$stmt_appointments->execute();
$appointments_result = $stmt_appointments->get_result();

// Get payment history
$sql_payments = "SELECT * FROM Payment 
                WHERE Student_ID = ? 
                ORDER BY Payment_Date DESC";

$stmt_payments = $conn->prepare($sql_payments);
$stmt_payments->bind_param("i", $student_id);
$stmt_payments->execute();
$payments_result = $stmt_payments->get_result();

// Get repair request history
$sql_repairs = "SELECT r.*, rm.Room_Type, ms.Staff_Name 
                FROM RepairRequest r 
                JOIN Room rm ON r.Room_ID = rm.Room_ID 
                LEFT JOIN MaintenanceStaff ms ON r.Staff_ID = ms.Staff_ID
                WHERE r.Student_ID = ? 
                ORDER BY r.Request_Date DESC";

$stmt_repairs = $conn->prepare($sql_repairs);
$stmt_repairs->bind_param("i", $student_id);
$stmt_repairs->execute();
$repairs_result = $stmt_repairs->get_result();

// Build content for template
ob_start();
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Student Details</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="students.php">Students</a></li>
        <li class="breadcrumb-item active">View Student</li>
    </ol>
    
    <div class="row">
        <div class="col-xl-4">
            <!-- Student Information Card -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-user me-1"></i>
                        Student Information
                    </div>
                    <div>
                        <a href="edit_student.php?id=<?= $student['Student_ID'] ?>" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                            <i class="fas fa-user-graduate fa-3x text-primary"></i>
                        </div>
                        <h4 class="mt-3"><?= htmlspecialchars($student['Student_Name']) ?></h4>
                        <p class="text-muted">Student ID: <?= $student['Student_ID'] ?></p>
                    </div>
                    
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <th><i class="fas fa-envelope me-2"></i> Email:</th>
                                <td><?= htmlspecialchars($student['Student_Email']) ?></td>
                            </tr>
                            <tr>
                                <th><i class="fas fa-phone me-2"></i> Phone:</th>
                                <td><?= htmlspecialchars($student['Student_Phone']) ?></td>
                            </tr>
                            <tr>
                                <th><i class="fas fa-graduation-cap me-2"></i> Course:</th>
                                <td>
                                    <?php if (!empty($student['Course'])): ?>
                                        <span class="badge bg-info"><?= htmlspecialchars($student['Course']) ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Not Specified</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th><i class="fas fa-phone-alt me-2"></i> Emergency Contact:</th>
                                <td>
                                    <?php if (!empty($student['Emergency_Contact'])): ?>
                                        <a href="tel:<?= htmlspecialchars($student['Emergency_Contact']) ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($student['Emergency_Contact']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Not Provided</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th><i class="fas fa-home me-2"></i> Room Assignment:</th>
                                <td>
                                    <?php if ($student['Room_ID']): ?>
                                        <span class="badge bg-success">Room #<?= $student['Room_ID'] ?> (<?= $student['Room_Type'] ?>)</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Not Assigned</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="students.php" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                    <?php if (!$student['Room_ID']): ?>
                        <a href="assign_room.php?student_id=<?= $student['Student_ID'] ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-home"></i> Assign Room
                        </a>
                    <?php else: ?>
                        <a href="unassign_room.php?student_id=<?= $student['Student_ID'] ?>" class="btn btn-danger btn-sm" 
                           onclick="return confirm('Are you sure you want to unassign this room? This action cannot be undone.')">
                            <i class="fas fa-times"></i> Unassign Room
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-xl-8">
            <!-- Tabs for Different History Records -->
            <div class="card mb-4">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="bookings-tab" data-bs-toggle="tab" data-bs-target="#bookings" type="button" role="tab" aria-controls="bookings" aria-selected="true">
                                <i class="fas fa-calendar-check me-1"></i> Bookings
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="appointments-tab" data-bs-toggle="tab" data-bs-target="#appointments" type="button" role="tab" aria-controls="appointments" aria-selected="false">
                                <i class="fas fa-calendar me-1"></i> Appointments
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="payments-tab" data-bs-toggle="tab" data-bs-target="#payments" type="button" role="tab" aria-controls="payments" aria-selected="false">
                                <i class="fas fa-money-bill me-1"></i> Payments
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="repairs-tab" data-bs-toggle="tab" data-bs-target="#repairs" type="button" role="tab" aria-controls="repairs" aria-selected="false">
                                <i class="fas fa-tools me-1"></i> Repairs
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <!-- Bookings Tab -->
                        <div class="tab-pane fade show active" id="bookings" role="tabpanel" aria-labelledby="bookings-tab">
                            <?php if ($bookings_result->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Booking ID</th>
                                                <th>Room</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($booking = $bookings_result->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?= $booking['Booking_ID'] ?></td>
                                                    <td>Room #<?= $booking['Room_ID'] ?> (<?= $booking['Room_Type'] ?>)</td>
                                                    <td><?= date('F j, Y', strtotime($booking['Booking_Date'])) ?></td>
                                                    <td>
                                                        <?php
                                                        $status_class = '';
                                                        switch ($booking['Status']) {
                                                            case 'Pending':
                                                                $status_class = 'bg-warning text-dark';
                                                                break;
                                                            case 'Confirmed':
                                                                $status_class = 'bg-success';
                                                                break;
                                                            case 'Cancelled':
                                                                $status_class = 'bg-danger';
                                                                break;
                                                            case 'Completed':
                                                                $status_class = 'bg-info';
                                                                break;
                                                        }
                                                        ?>
                                                        <span class="badge <?= $status_class ?>"><?= $booking['Status'] ?></span>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info mb-0">No booking history found.</div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Appointments Tab -->
                        <div class="tab-pane fade" id="appointments" role="tabpanel" aria-labelledby="appointments-tab">
                            <?php if ($appointments_result->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Appointment ID</th>
                                                <th>Room</th>
                                                <th>Date & Time</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($appointment = $appointments_result->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?= $appointment['Appointment_ID'] ?></td>
                                                    <td>Room #<?= $appointment['Room_ID'] ?> (<?= $appointment['Room_Type'] ?>)</td>
                                                    <td><?= date('F j, Y g:i A', strtotime($appointment['Appointment_Date'])) ?></td>
                                                    <td>
                                                        <?php
                                                        $status_class = '';
                                                        switch ($appointment['Status']) {
                                                            case 'Pending':
                                                                $status_class = 'bg-warning text-dark';
                                                                break;
                                                            case 'Confirmed':
                                                                $status_class = 'bg-success';
                                                                break;
                                                            case 'Cancelled':
                                                                $status_class = 'bg-danger';
                                                                break;
                                                            case 'Completed':
                                                                $status_class = 'bg-info';
                                                                break;
                                                        }
                                                        ?>
                                                        <span class="badge <?= $status_class ?>"><?= $appointment['Status'] ?></span>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info mb-0">No appointment history found.</div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Payments Tab -->
                        <div class="tab-pane fade" id="payments" role="tabpanel" aria-labelledby="payments-tab">
                            <?php if ($payments_result->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Payment ID</th>
                                                <th>Amount</th>
                                                <th>Date</th>
                                                <th>Method</th>
                                                <th>Purpose</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($payment = $payments_result->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?= $payment['Payment_ID'] ?></td>
                                                    <td>$<?= number_format($payment['Amount'], 2) ?></td>
                                                    <td><?= date('F j, Y', strtotime($payment['Payment_Date'])) ?></td>
                                                    <td><?= $payment['Payment_Method'] ?></td>
                                                    <td><?= htmlspecialchars($payment['Purpose']) ?></td>
                                                    <td>
                                                        <?php
                                                        $status_class = '';
                                                        switch ($payment['Status']) {
                                                            case 'Pending':
                                                                $status_class = 'bg-warning text-dark';
                                                                break;
                                                            case 'Completed':
                                                                $status_class = 'bg-success';
                                                                break;
                                                            case 'Failed':
                                                                $status_class = 'bg-danger';
                                                                break;
                                                            case 'Refunded':
                                                                $status_class = 'bg-info';
                                                                break;
                                                        }
                                                        ?>
                                                        <span class="badge <?= $status_class ?>"><?= $payment['Status'] ?></span>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info mb-0">No payment history found.</div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Repairs Tab -->
                        <div class="tab-pane fade" id="repairs" role="tabpanel" aria-labelledby="repairs-tab">
                            <?php if ($repairs_result->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Request ID</th>
                                                <th>Room</th>
                                                <th>Description</th>
                                                <th>Request Date</th>
                                                <th>Scheduled Date</th>
                                                <th>Assigned Staff</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($repair = $repairs_result->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?= $repair['Request_ID'] ?></td>
                                                    <td>Room #<?= $repair['Room_ID'] ?> (<?= $repair['Room_Type'] ?>)</td>
                                                    <td><?= htmlspecialchars($repair['Description']) ?></td>
                                                    <td><?= date('F j, Y', strtotime($repair['Request_Date'])) ?></td>
                                                    <td>
                                                        <?= $repair['Scheduled_Date'] ? date('F j, Y', strtotime($repair['Scheduled_Date'])) : '<span class="badge bg-secondary">Not Scheduled</span>' ?>
                                                    </td>
                                                    <td><?= $repair['Staff_Name'] ? htmlspecialchars($repair['Staff_Name']) : '<span class="badge bg-secondary">Not Assigned</span>' ?></td>
                                                    <td>
                                                        <?php
                                                        $status_class = '';
                                                        switch ($repair['Status']) {
                                                            case 'Pending':
                                                                $status_class = 'bg-warning text-dark';
                                                                break;
                                                            case 'Scheduled':
                                                                $status_class = 'bg-info';
                                                                break;
                                                            case 'In Progress':
                                                                $status_class = 'bg-primary';
                                                                break;
                                                            case 'Completed':
                                                                $status_class = 'bg-success';
                                                                break;
                                                            case 'Cancelled':
                                                                $status_class = 'bg-danger';
                                                                break;
                                                        }
                                                        ?>
                                                        <span class="badge <?= $status_class ?>"><?= $repair['Status'] ?></span>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info mb-0">No repair request history found.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Enable Bootstrap tooltips
    $(document).ready(function() {
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>

<?php
$content = ob_get_clean();
include '../layout.php';
?>