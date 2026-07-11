<?php
require_once '../db_connection.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] != "admin") {
    header("location: ../login.php");
    exit();
}

$page_title = "Edit Room - Admin Dashboard";

// Check if room ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_msg'] = "Room ID is required.";
    header("location: rooms.php");
    exit();
}

$room_id = intval($_GET['id']);

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate input
    $room_type = trim($_POST['room_type']);
    $capacity = intval($_POST['capacity']);
    $status = $_POST['status'];
    
    $errors = [];
    
    if (empty($room_type)) {
        $errors[] = "Room type is required";
    }
    
    if ($capacity <= 0) {
        $errors[] = "Capacity must be greater than 0";
    }
    
    // Validate status
    $valid_statuses = ['Available', 'Occupied', 'Maintenance', 'Reserved'];
    if (!in_array($status, $valid_statuses)) {
        $errors[] = "Invalid room status";
    }
    
    // Check if the new capacity is less than current occupancy
    $check_sql = "SELECT Current_Occupancy FROM Room WHERE Room_ID = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $room_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $current_occupancy = $row['Current_Occupancy'];
        if ($capacity < $current_occupancy) {
            $errors[] = "Capacity cannot be less than current occupancy ($current_occupancy)";
        }
    } else {
        $_SESSION['error_msg'] = "Room not found.";
        header("location: rooms.php");
        exit();
    }
    
    if (empty($errors)) {
        // Update room
        $update_sql = "UPDATE Room SET Room_Type = ?, Capacity = ?, Status = ? WHERE Room_ID = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("sisi", $room_type, $capacity, $status, $room_id);
        
        if ($update_stmt->execute()) {
            $_SESSION['success_msg'] = "Room updated successfully!";
            header("location: rooms.php");
            exit();
        } else {
            $errors[] = "Error updating room: " . $conn->error;
        }
    }
}

// Get room details
$sql = "SELECT * FROM Room WHERE Room_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $room_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error_msg'] = "Room not found.";
    header("location: rooms.php");
    exit();
}

$room = $result->fetch_assoc();

// Get students in this room (if any)
$sql_students = "SELECT s.Student_ID, s.Student_Name, s.Student_Email, s.Student_Phone 
                FROM Student s
                WHERE s.Room_ID = ?";
$stmt_students = $conn->prepare($sql_students);
$stmt_students->bind_param("i", $room_id);
$stmt_students->execute();
$students_result = $stmt_students->get_result();

// Build content for template
ob_start();
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Edit Room</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="rooms.php">Rooms</a></li>
        <li class="breadcrumb-item active">Edit Room #<?= $room_id ?></li>
    </ol>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-edit me-1"></i>
                    Room Information
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="room_id" class="form-label">Room ID</label>
                            <input type="text" class="form-control" id="room_id" value="<?= $room['Room_ID'] ?>" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label for="room_type" class="form-label">Room Type</label>
                            <input type="text" class="form-control" id="room_type" name="room_type" value="<?= htmlspecialchars($room['Room_Type']) ?>" required>
                            <div class="form-text">Examples: Single, Double, Triple, Quad, Suite, etc.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="capacity" class="form-label">Capacity</label>
                            <input type="number" class="form-control" id="capacity" name="capacity" value="<?= $room['Capacity'] ?>" min="1" required>
                            <div class="form-text">Maximum number of students this room can accommodate.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="current_occupancy" class="form-label">Current Occupancy</label>
                            <input type="text" class="form-control" id="current_occupancy" value="<?= $room['Current_Occupancy'] ?>" readonly>
                            <div class="form-text">This is updated automatically when students are assigned to or removed from this room.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="Available" <?= $room['Status'] == 'Available' ? 'selected' : '' ?>>Available</option>
                                <option value="Occupied" <?= $room['Status'] == 'Occupied' ? 'selected' : '' ?>>Occupied</option>
                                <option value="Maintenance" <?= $room['Status'] == 'Maintenance' ? 'selected' : '' ?>>Maintenance</option>
                                <option value="Reserved" <?= $room['Status'] == 'Reserved' ? 'selected' : '' ?>>Reserved</option>
                            </select>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="rooms.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Room</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-users me-1"></i>
                    Assigned Students
                </div>
                <div class="card-body">
                    <?php if ($students_result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($student = $students_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $student['Student_ID'] ?></td>
                                            <td><?= htmlspecialchars($student['Student_Name']) ?></td>
                                            <td>
                                                <a href="view_student.php?id=<?= $student['Student_ID'] ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No students are currently assigned to this room.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-history me-1"></i>
                    Recent Repair Requests
                </div>
                <div class="card-body">
                    <?php
                    // Get repair requests for this room
                    $sql_repairs = "SELECT rr.Request_ID, rr.Description, rr.Request_Date, rr.Status 
                                    FROM RepairRequest rr
                                    WHERE rr.Room_ID = ?
                                    ORDER BY rr.Request_Date DESC
                                    LIMIT 5";
                    $stmt_repairs = $conn->prepare($sql_repairs);
                    $stmt_repairs->bind_param("i", $room_id);
                    $stmt_repairs->execute();
                    $repairs_result = $stmt_repairs->get_result();
                    ?>
                    
                    <?php if ($repairs_result->num_rows > 0): ?>
                        <div class="list-group">
                            <?php while ($repair = $repairs_result->fetch_assoc()): ?>
                                <a href="view_repair.php?id=<?= $repair['Request_ID'] ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?= htmlspecialchars(substr($repair['Description'], 0, 50)) ?>...</h6>
                                        <small><?= date('M d, Y', strtotime($repair['Request_Date'])) ?></small>
                                    </div>
                                    <p class="mb-1">
                                        <span class="badge 
                                            <?php
                                            switch ($repair['Status']) {
                                                case 'Pending': echo 'bg-warning text-dark'; break;
                                                case 'Scheduled': echo 'bg-info text-dark'; break;
                                                case 'In Progress': echo 'bg-primary'; break;
                                                case 'Completed': echo 'bg-success'; break;
                                                case 'Cancelled': echo 'bg-secondary'; break;
                                                default: echo 'bg-secondary';
                                            }
                                            ?>">
                                            <?= $repair['Status'] ?>
                                        </span>
                                    </p>
                                </a>
                            <?php endwhile; ?>
                        </div>
                        <div class="mt-3">
                            <a href="repair_requests.php?room_id=<?= $room_id ?>" class="btn btn-sm btn-outline-primary w-100">View All Repair Requests</a>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No repair requests found for this room.</p>
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