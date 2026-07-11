<?php
require_once '../db_connection.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] != "admin") {
    header("location: ../login.php");
    exit();
}

$page_title = "View Room Details - Admin Dashboard";

// Check if room ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_msg'] = "Room ID is required.";
    header("location: rooms.php");
    exit();
}

$room_id = $_GET['id'];

// Get room details
$sql = "SELECT * FROM Room WHERE Room_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $room_id);
$stmt->execute();
$room = $stmt->get_result()->fetch_assoc();

if (!$room) {
    $_SESSION['error_msg'] = "Room not found.";
    header("location: rooms.php");
    exit();
}

// Get students assigned to this room
$sql_students = "SELECT * FROM Student WHERE Room_ID = ? ORDER BY Student_Name";
$stmt_students = $conn->prepare($sql_students);
$stmt_students->bind_param("i", $room_id);
$stmt_students->execute();
$result_students = $stmt_students->get_result();

// Get repair requests for this room
$sql_repairs = "SELECT r.*, s.Student_Name, m.Staff_Name 
                FROM RepairRequest r 
                LEFT JOIN Student s ON r.Student_ID = s.Student_ID 
                LEFT JOIN MaintenanceStaff m ON r.Staff_ID = m.Staff_ID
                WHERE r.Room_ID = ? 
                ORDER BY r.Request_Date DESC";
$stmt_repairs = $conn->prepare($sql_repairs);
$stmt_repairs->bind_param("i", $room_id);
$stmt_repairs->execute();
$result_repairs = $stmt_repairs->get_result();

// Get booking history for this room
$sql_bookings = "SELECT b.*, s.Student_Name 
                FROM Booking b 
                LEFT JOIN Student s ON b.Student_ID = s.Student_ID 
                WHERE b.Room_ID = ? 
                ORDER BY b.Booking_Date DESC";
$stmt_bookings = $conn->prepare($sql_bookings);
$stmt_bookings->bind_param("i", $room_id);
$stmt_bookings->execute();
$result_bookings = $stmt_bookings->get_result();

// Build content for template
ob_start();
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Room Details</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="rooms.php">Rooms</a></li>
        <li class="breadcrumb-item active">Room <?= $room['Room_ID'] ?></li>
    </ol>
    
    <div class="row">
        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-door-open me-1"></i>
                        Room Information
                    </div>
                    <div>
                        <a href="edit_room.php?id=<?= $room['Room_ID'] ?>" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Room ID:</th>
                                <td><?= $room['Room_ID'] ?></td>
                            </tr>
                            <tr>
                                <th>Room Type:</th>
                                <td><?= $room['Room_Type'] ?></td>
                            </tr>
                            <tr>
                                <th>Capacity:</th>
                                <td><?= $room['Capacity'] ?></td>
                            </tr>
                            <tr>
                                <th>Current Occupancy:</th>
                                <td>
                                    <?= $room['Current_Occupancy'] ?> / <?= $room['Capacity'] ?>
                                    <?php if ($room['Current_Occupancy'] == $room['Capacity']): ?>
                                        <span class="badge bg-danger">Full</span>
                                    <?php elseif ($room['Current_Occupancy'] > 0): ?>
                                        <span class="badge bg-warning text-dark">Partial</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Empty</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <?php
                                    $status_class = '';
                                    switch ($room['Status']) {
                                        case 'Available':
                                            $status_class = 'bg-success';
                                            break;
                                        case 'Occupied':
                                            $status_class = 'bg-danger';
                                            break;
                                        case 'Maintenance':
                                            $status_class = 'bg-warning text-dark';
                                            break;
                                        case 'Reserved':
                                            $status_class = 'bg-info text-dark';
                                            break;
                                    }
                                    ?>
                                    <span class="badge <?= $status_class ?>"><?= $room['Status'] ?></span>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="d-grid gap-2 mt-3">
                        <div class="dropdown">
                            <button class="btn btn-primary btn-sm dropdown-toggle w-100" type="button" data-bs-toggle="dropdown">
                                Update Status
                            </button>
                            <ul class="dropdown-menu w-100">
                                <li><a class="dropdown-item" href="rooms.php?action=update_status&id=<?= $room['Room_ID'] ?>&status=Available">Available</a></li>
                                <li><a class="dropdown-item" href="rooms.php?action=update_status&id=<?= $room['Room_ID'] ?>&status=Occupied">Occupied</a></li>
                                <li><a class="dropdown-item" href="rooms.php?action=update_status&id=<?= $room['Room_ID'] ?>&status=Maintenance">Maintenance</a></li>
                                <li><a class="dropdown-item" href="rooms.php?action=update_status&id=<?= $room['Room_ID'] ?>&status=Reserved">Reserved</a></li>
                            </ul>
                        </div>
                        <a href="rooms.php" class="btn btn-secondary btn-sm">Back to Rooms</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-users me-1"></i>
                    Current Residents
                </div>
                <div class="card-body">
                    <?php if ($result_students->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Student ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($student = $result_students->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $student['Student_ID'] ?></td>
                                            <td><?= $student['Student_Name'] ?></td>
                                            <td><?= $student['Student_Email'] ?></td>
                                            <td><?= $student['Student_Phone'] ?></td>
                                            <td>
                                                <a href="../students/view_student.php?id=<?= $student['Student_ID'] ?>" class="btn btn-info btn-sm">View</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">No students are currently assigned to this room.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Repair Requests -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-tools me-1"></i>
                    Repair Requests
                </div>
                <div class="card-body">
                    <?php if ($result_repairs->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Description</th>
                                        <th>Student</th>
                                        <th>Assigned Staff</th>
                                        <th>Requested Date</th>
                                        <th>Scheduled Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($repair = $result_repairs->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $repair['Request_ID'] ?></td>
                                            <td><?= substr($repair['Description'], 0, 50) . (strlen($repair['Description']) > 50 ? '...' : '') ?></td>
                                            <td><?= $repair['Student_Name'] ?></td>
                                            <td><?= $repair['Staff_Name'] ?? 'Not Assigned' ?></td>
                                            <td><?= date('M d, Y', strtotime($repair['Request_Date'])) ?></td>
                                            <td>
                                                <?= $repair['Scheduled_Date'] ? date('M d, Y', strtotime($repair['Scheduled_Date'])) : 'Not Scheduled' ?>
                                            </td>
                                            <td>
                                                <?php
                                                $repair_status_class = '';
                                                switch ($repair['Status']) {
                                                    case 'Pending':
                                                        $repair_status_class = 'bg-warning text-dark';
                                                        break;
                                                    case 'Scheduled':
                                                        $repair_status_class = 'bg-info text-dark';
                                                        break;
                                                    case 'In Progress':
                                                        $repair_status_class = 'bg-primary';
                                                        break;
                                                    case 'Completed':
                                                        $repair_status_class = 'bg-success';
                                                        break;
                                                    case 'Cancelled':
                                                        $repair_status_class = 'bg-danger';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge <?= $repair_status_class ?>"><?= $repair['Status'] ?></span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">No repair requests found for this room.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Booking History -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-history me-1"></i>
                    Booking History
                </div>
                <div class="card-body">
                    <?php if ($result_bookings->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Booking ID</th>
                                        <th>Student</th>
                                        <th>Booking Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($booking = $result_bookings->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $booking['Booking_ID'] ?></td>
                                            <td><?= $booking['Student_Name'] ?></td>
                                            <td><?= date('M d, Y', strtotime($booking['Booking_Date'])) ?></td>
                                            <td>
                                                <?php
                                                $booking_status_class = '';
                                                switch ($booking['Status']) {
                                                    case 'Pending':
                                                        $booking_status_class = 'bg-warning text-dark';
                                                        break;
                                                    case 'Confirmed':
                                                        $booking_status_class = 'bg-success';
                                                        break;
                                                    case 'Cancelled':
                                                        $booking_status_class = 'bg-danger';
                                                        break;
                                                    case 'Completed':
                                                        $booking_status_class = 'bg-info text-dark';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge <?= $booking_status_class ?>"><?= $booking['Status'] ?></span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">No booking history found for this room.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../layout.php';
?>