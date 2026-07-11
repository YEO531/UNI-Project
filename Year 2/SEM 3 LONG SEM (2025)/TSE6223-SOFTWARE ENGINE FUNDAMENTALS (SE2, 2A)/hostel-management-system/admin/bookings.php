<?php

// Check if user is logged in and is an admin
require_once '../db_connection.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] != "admin") {
    header('Location: ../login.php');
    exit();
}

$admin_id = $_SESSION["user_id"];
$admin_name = $_SESSION["user_name"];

// Initialize variables for pagination
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Handle status filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$where_clause = '';
$filter_params = [];
$filter_types = '';

if (!empty($status_filter)) {
    $where_clause = " WHERE b.Status = ?";
    $filter_params[] = $status_filter;
    $filter_types = "s";
}

// Get total number of bookings for pagination
$count_sql = "SELECT COUNT(*) as total FROM Booking b" . $where_clause;
$count_stmt = $conn->prepare($count_sql);
if (!empty($filter_params)) {
    $count_stmt->bind_param($filter_types, ...$filter_params);
}
$count_stmt->execute();
$total_results = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_results / $records_per_page);

// Get bookings with pagination
$sql = "SELECT b.*, r.Room_Type, s.Student_Name, s.Student_Email 
        FROM Booking b
        JOIN Room r ON b.Room_ID = r.Room_ID
        JOIN Student s ON b.Student_ID = s.Student_ID"
        . $where_clause . 
        " ORDER BY b.Booking_Date DESC
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$param_types = $filter_types . "ii";
$params = array_merge($filter_params, [$records_per_page, $offset]);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Handle booking actions (approve, reject, cancel)
$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['approve_booking'])) {
        $booking_id = $_POST['booking_id'];
        $room_id = $_POST['room_id'];
        $student_id = $_POST['student_id'];
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Update booking status
            $sql_update = "UPDATE Booking SET Status = 'Confirmed' WHERE Booking_ID = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("i", $booking_id);
            $stmt_update->execute();
            
            // Update student's room assignment
            $sql_assign = "UPDATE Student SET Room_ID = ? WHERE Student_ID = ?";
            $stmt_assign = $conn->prepare($sql_assign);
            $stmt_assign->bind_param("ii", $room_id, $student_id);
            $stmt_assign->execute();
            
            // Update room's occupancy
            $sql_room = "UPDATE Room SET Current_Occupancy = Current_Occupancy + 1, 
                        Status = CASE 
                            WHEN Current_Occupancy + 1 >= Capacity THEN 'Occupied'
                            ELSE 'Available' 
                        END
                        WHERE Room_ID = ?";
            $stmt_room = $conn->prepare($sql_room);
            $stmt_room->bind_param("i", $room_id);
            $stmt_room->execute();
            
            $conn->commit();
            $success_message = "Booking approved successfully";
            
            // Refresh the page to show updated data
            header("Location: bookings.php?success=approved");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $error_message = "Error approving booking: " . $e->getMessage();
        }
    } elseif (isset($_POST['reject_booking'])) {
        $booking_id = $_POST['booking_id'];
        
        // Update booking status to cancelled
        $sql_update = "UPDATE Booking SET Status = 'Cancelled' WHERE Booking_ID = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("i", $booking_id);
        
        if ($stmt_update->execute()) {
            $success_message = "Booking rejected successfully";
            
            // Refresh the page to show updated data
            header("Location: bookings.php?success=rejected");
            exit();
        } else {
            $error_message = "Error rejecting booking: " . $conn->error;
        }
    } elseif (isset($_POST['cancel_booking'])) {
        $booking_id = $_POST['booking_id'];
        $room_id = $_POST['room_id'];
        $student_id = $_POST['student_id'];
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Update booking status
            $sql_update = "UPDATE Booking SET Status = 'Cancelled' WHERE Booking_ID = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("i", $booking_id);
            $stmt_update->execute();
            
            // Clear student's room assignment if it was confirmed
            $sql_clear = "UPDATE Student SET Room_ID = NULL WHERE Student_ID = ? AND Room_ID = ?";
            $stmt_clear = $conn->prepare($sql_clear);
            $stmt_clear->bind_param("ii", $student_id, $room_id);
            $stmt_clear->execute();
            
            // Update room's occupancy if booking was confirmed
            if ($_POST['booking_status'] == 'Confirmed') {
                $sql_room = "UPDATE Room SET 
                Current_Occupancy = GREATEST(Current_Occupancy - 1, 0),
                Status = CASE 
                    WHEN GREATEST(Current_Occupancy - 1, 0) < Capacity THEN 'Available'
                    ELSE 'Occupied' 
                END
                WHERE Room_ID = ?";
                $stmt_room = $conn->prepare($sql_room);
                $stmt_room->bind_param("i", $room_id);
                 $stmt_room->execute();
            }
            
            $conn->commit();
            $success_message = "Booking cancelled successfully";
            
            // Refresh the page to show updated data
            header("Location: bookings.php?success=cancelled");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $error_message = "Error cancelling booking: " . $e->getMessage();
        }
    } elseif (isset($_POST['complete_booking'])) {
        $booking_id = $_POST['booking_id'];
        
        // Update booking status to completed
        $sql_update = "UPDATE Booking SET Status = 'Completed' WHERE Booking_ID = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("i", $booking_id);
        
        if ($stmt_update->execute()) {
            $success_message = "Booking marked as completed successfully";
            
            // Refresh the page to show updated data
            header("Location: bookings.php?success=completed");
            exit();
        } else {
            $error_message = "Error updating booking: " . $conn->error;
        }
    }
}

// Check for success message in URL
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'approved':
            $success_message = "Booking approved successfully";
            break;
        case 'rejected':
            $success_message = "Booking rejected successfully";
            break;
        case 'cancelled':
            $success_message = "Booking cancelled successfully";
            break;
        case 'completed':
            $success_message = "Booking marked as completed successfully";
            break;
    }
}

$page_title = "Manage Bookings - Hostel Management System";

// Build content for template
ob_start();
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Manage Bookings</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Bookings</li>
    </ol>
    
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><?= $success_message ?></div>
    <?php endif; ?>
    
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?= $error_message ?></div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <i class="fas fa-book me-1"></i>
                    Student Booking Management
                </div>
                <div class="col-md-6 text-end">
                    <form method="get" action="" class="d-flex justify-content-end">
                        <select name="status" class="form-select me-2" style="width: auto;" onchange="this.form.submit()">
                            <option value="">All Statuses</option>
                            <option value="Pending" <?= $status_filter == 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="Confirmed" <?= $status_filter == 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                            <option value="Cancelled" <?= $status_filter == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            <option value="Completed" <?= $status_filter == 'Completed' ? 'selected' : '' ?>>Completed</option>
                        </select>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="bookingsTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student</th>
                            <th>Email</th>
                            <th>Room</th>
                            <th>Type</th>
                            <th>Booking Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['Booking_ID'] ?></td>
                                    <td><?= $row['Student_Name'] ?></td>
                                    <td><?= $row['Student_Email'] ?></td>
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
                                        <div class="btn-group">
                                            <?php if ($row['Status'] == 'Pending'): ?>
                                                <form method="post" action="" class="me-1">
                                                    <input type="hidden" name="booking_id" value="<?= $row['Booking_ID'] ?>">
                                                    <input type="hidden" name="room_id" value="<?= $row['Room_ID'] ?>">
                                                    <input type="hidden" name="student_id" value="<?= $row['Student_ID'] ?>">
                                                    <button type="submit" name="approve_booking" class="btn btn-success btn-sm" onclick="return confirm('Are you sure you want to approve this booking?')">
                                                        Approve
                                                    </button>
                                                </form>
                                                <form method="post" action="">
                                                    <input type="hidden" name="booking_id" value="<?= $row['Booking_ID'] ?>">
                                                    <button type="submit" name="reject_booking" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to reject this booking?')">
                                                        Reject
                                                    </button>
                                                </form>
                                            <?php elseif ($row['Status'] == 'Confirmed'): ?>
                                                <form method="post" action="" class="me-1">
                                                    <input type="hidden" name="booking_id" value="<?= $row['Booking_ID'] ?>">
                                                    <input type="hidden" name="room_id" value="<?= $row['Room_ID'] ?>">
                                                    <input type="hidden" name="student_id" value="<?= $row['Student_ID'] ?>">
                                                    <input type="hidden" name="booking_status" value="<?= $row['Status'] ?>">
                                                    <button type="submit" name="cancel_booking" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to cancel this booking? This will remove the student\'s room assignment.')">
                                                        Cancel
                                                    </button>
                                                </form>
                                                <form method="post" action="">
                                                    <input type="hidden" name="booking_id" value="<?= $row['Booking_ID'] ?>">
                                                    <button type="submit" name="complete_booking" class="btn btn-info btn-sm" onclick="return confirm('Are you sure you want to mark this booking as completed?')">
                                                        Complete
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <button class="btn btn-secondary btn-sm" disabled>No Actions</button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">No booking records found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= ($page <= 1 ? 'disabled' : '') ?>">
                            <a class="page-link" href="?page=<?= ($page-1) ?><?= (!empty($status_filter) ? '&status='.$status_filter : '') ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= ($page == $i ? 'active' : '') ?>">
                                <a class="page-link" href="?page=<?= $i ?><?= (!empty($status_filter) ? '&status='.$status_filter : '') ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($page >= $total_pages ? 'disabled' : '') ?>">
                            <a class="page-link" href="?page=<?= ($page+1) ?><?= (!empty($status_filter) ? '&status='.$status_filter : '') ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-info-circle me-1"></i>
            Booking Management Guidelines
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Booking Status Workflow</h5>
                    <ol>
                        <li><span class="badge bg-warning">Pending</span> - Initial state when student requests a booking</li>
                        <li><span class="badge bg-success">Confirmed</span> - Admin has approved the booking request</li>
                        <li><span class="badge bg-info">Completed</span> - Student has completed their stay in the room</li>
                        <li><span class="badge bg-danger">Cancelled</span> - Booking has been rejected or cancelled</li>
                    </ol>
                </div>
                <div class="col-md-6">
                    <h5>Booking Management Actions</h5>
                    <ul>
                        <li><strong>Approve</strong> - Confirms the booking, assigns the room to the student, and updates room occupancy</li>
                        <li><strong>Reject</strong> - Cancels the booking request</li>
                        <li><strong>Cancel</strong> - Cancels a confirmed booking and removes the student's room assignment</li>
                        <li><strong>Complete</strong> - Marks a booking as completed after the student's stay</li>
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