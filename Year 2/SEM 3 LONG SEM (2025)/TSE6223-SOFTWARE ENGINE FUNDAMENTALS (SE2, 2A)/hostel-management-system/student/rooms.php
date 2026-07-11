<?php
require_once '../db_connection.php';

// Check if user is logged in and is a student
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] != "student") {
    header("location: ../login.php");
    exit();
}

$student_id = $_SESSION["user_id"];
$student_name = $_SESSION["user_name"];

// Get filter parameter
$room_type_filter = isset($_GET['room_type']) ? $_GET['room_type'] : '';

// Only show rooms if a specific type is selected
$result = null;
if (!empty($room_type_filter)) {
    $sql = "SELECT * FROM Room WHERE Status = 'Available' AND Room_Type = '" . $conn->real_escape_string($room_type_filter) . "' ORDER BY Room_ID";
    $result = $conn->query($sql);
}

// Get all room types for filter dropdown
$sql_types = "SELECT DISTINCT Room_Type FROM Room WHERE Status = 'Available' ORDER BY Room_Type";
$result_types = $conn->query($sql_types);

// Get student's current room
$sql_student = "SELECT s.*, r.Room_Type, r.Status as Room_Status FROM Student s 
                LEFT JOIN Room r ON s.Room_ID = r.Room_ID 
                WHERE s.Student_ID = ?";
$stmt = $conn->prepare($sql_student);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result_student = $stmt->get_result();
$student_data = $result_student->fetch_assoc();

$page_title = "View Rooms - Hostel Management System";

// Process booking request
$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_room'])) {
    $room_id = $_POST['room_id'];
    
    // Check if student already has an active booking
    $sql_check = "SELECT COUNT(*) as active_bookings FROM Booking 
                 WHERE Student_ID = ? AND Status IN ('Pending', 'Confirmed')";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $student_id);
    $stmt_check->execute();
    $active_bookings = $stmt_check->get_result()->fetch_assoc()['active_bookings'];
    
    if ($active_bookings > 0) {
        $error_message = "You already have an active booking. Please cancel it before making a new one.";
    } else {
        // Create new booking
        $sql_book = "INSERT INTO Booking (Student_ID, Room_ID, Booking_Date, Status) 
                    VALUES (?, ?, CURDATE(), 'Pending')";
        $stmt_book = $conn->prepare($sql_book);
        $stmt_book->bind_param("ii", $student_id, $room_id);
        
        if ($stmt_book->execute()) {
            $success_message = "Room booking request submitted successfully. It is pending approval.";
        } else {
            $error_message = "Error creating booking: " . $conn->error;
        }
    }
}

// Function to get room type color
function getRoomTypeColor($room_type) {
    switch (strtolower($room_type)) {
        case 'single': return 'primary';
        case 'double': return 'success';
        case 'triple': return 'warning';
        case 'quad': return 'info';
        default: return 'secondary';
    }
}

// Function to get room type description
function getRoomTypeDescription($room_type) {
    switch (strtolower($room_type)) {
        case 'single': return 'Single bed, study desk, closet, private bathroom';
        case 'double': return 'Two single beds, study desks, closets, shared bathroom';
        case 'triple': return 'Three single beds, study desks, closets, shared bathroom';
        case 'quad': return 'Four single beds, study desks, closets, shared bathroom';
        default: return 'Standard room amenities';
    }
}

// Build content for template
ob_start();
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Available Rooms</h1>
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><?= $success_message ?></div>
    <?php endif; ?>
    
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?= $error_message ?></div>
    <?php endif; ?>
    
    <!-- Current Room Section -->
    <div class="row mb-4">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-home me-1"></i>
                    Your Current Room
                </div>
                <div class="card-body">
                    <?php if ($student_data['Room_ID']): ?>
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Room Details</h5>
                                <p><strong>Room Number:</strong> <?= $student_data['Room_ID'] ?></p>
                                <p><strong>Room Type:</strong> <?= $student_data['Room_Type'] ?></p>
                                <p><strong>Status:</strong> <?= $student_data['Room_Status'] ?></p>
                            </div>
                            <div class="col-md-6">
                                <h5>Contact Information</h5>
                                <p><strong>Student ID:</strong> <?= $student_data['Student_ID'] ?></p>
                                <p><strong>Name:</strong> <?= $student_data['Student_Name'] ?></p>
                                <p><strong>Email:</strong> <?= $student_data['Student_Email'] ?></p>
                                <p><strong>Phone:</strong> <?= $student_data['Student_Phone'] ?></p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            You currently do not have a room assigned. Please book a room from the available options below.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-search me-1"></i>
            Search Rooms
        </div>
        <div class="card-body">
            <form method="get" action="">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <label for="room_type" class="form-label">Select Room Type</label>
                        <select class="form-select" id="room_type" name="room_type" required>
                            <option value="">Choose a room type...</option>
                            <?php while ($type_row = $result_types->fetch_assoc()): ?>
                                <option value="<?= $type_row['Room_Type'] ?>" 
                                        <?= ($room_type_filter == $type_row['Room_Type']) ? 'selected' : '' ?>>
                                    <?= ucfirst($type_row['Room_Type']) ?> Room
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i>Search
                        </button>
                        <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Available Rooms Cards -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-bed me-1"></i>
            Available Rooms
            <?php if (!empty($room_type_filter)): ?>
                <span class="badge bg-info ms-2"><?= ucfirst($room_type_filter) ?> Rooms</span>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?php if (!empty($room_type_filter) && $result && $result->num_rows > 0): ?>
                <div class="row">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-header bg-<?= getRoomTypeColor($row['Room_Type']) ?> text-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">
                                            <i class="fas fa-door-open me-2"></i>
                                            Room <?= $row['Room_ID'] ?>
                                        </h5>
                                        <span class="badge bg-light text-dark">
                                            <?= ucfirst($row['Room_Type']) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <h6 class="text-dark mb-2">
                                            <i class="fas fa-list-ul me-1"></i>Features
                                        </h6>
                                        <p class="text-muted mb-0">
                                            <small><?= getRoomTypeDescription($row['Room_Type']) ?></small>
                                        </p>
                                    </div>
                                    
                                    <div class="row text-center mb-3">
                                        <div class="col-6">
                                            <div class="border-end">
                                                <h6 class="text-primary mb-1"><?= $row['Capacity'] ?></h6>
                                                <small class="text-muted">Capacity</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <h6 class="text-success mb-1"><?= $row['Current_Occupancy'] ?></h6>
                                            <small class="text-muted">Occupied</small>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="progress" style="height: 8px;">
                                            <?php 
                                            $occupancy_percentage = ($row['Current_Occupancy'] / $row['Capacity']) * 100;
                                            $progress_color = $occupancy_percentage < 50 ? 'success' : ($occupancy_percentage < 80 ? 'warning' : 'danger');
                                            ?>
                                            <div class="progress-bar bg-<?= $progress_color ?>" 
                                                 role="progressbar" 
                                                 style="width: <?= $occupancy_percentage ?>%">
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            <?= $row['Capacity'] - $row['Current_Occupancy'] ?> spaces available
                                        </small>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i>
                                            <?= $row['Status'] ?>
                                        </span>
                                        
                                        <form method="post" action="" class="d-inline">
                                            <input type="hidden" name="room_id" value="<?= $row['Room_ID'] ?>">
                                            <button type="submit" 
                                                    name="book_room" 
                                                    class="btn btn-primary btn-sm"
                                                    <?= ($row['Current_Occupancy'] >= $row['Capacity']) ? 'disabled' : '' ?>>
                                                <i class="fas fa-calendar-plus me-1"></i>
                                                <?= ($row['Current_Occupancy'] >= $row['Capacity']) ? 'Full' : 'Book Now' ?>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php elseif (!empty($room_type_filter) && $result && $result->num_rows == 0): ?>
                <div class="text-center py-5">
                    <i class="fas fa-bed fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Available Rooms Found</h5>
                    <p class="text-muted">
                        No <?= strtolower($room_type_filter) ?> rooms are currently available. 
                        Please try a different room type.
                    </p>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Select a Room Type</h5>
                    <p class="text-muted">
                        Please select a room type from the filter above to view available rooms.
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
include '../layout.php';
?>