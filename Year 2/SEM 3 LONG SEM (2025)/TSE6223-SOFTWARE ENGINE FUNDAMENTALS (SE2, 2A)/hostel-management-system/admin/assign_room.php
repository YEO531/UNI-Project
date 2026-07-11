
<?php
require_once '../db_connection.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] != "admin") {
    header("location: ../login.php");
    exit();
}

$page_title = "Assign Room - Admin Dashboard";

// Check if student ID is provided
if (!isset($_GET['student_id']) || empty($_GET['student_id'])) {
    $_SESSION['error_msg'] = "Student ID is required!";
    header("location: students.php");
    exit();
}

$student_id = $_GET['student_id'];

// Get student information
$sql_student = "SELECT * FROM Student WHERE Student_ID = ?";
$stmt_student = $conn->prepare($sql_student);
$stmt_student->bind_param("i", $student_id);
$stmt_student->execute();
$result_student = $stmt_student->get_result();

if ($result_student->num_rows == 0) {
    $_SESSION['error_msg'] = "Student not found!";
    header("location: students.php");
    exit();
}

$student = $result_student->fetch_assoc();

// Check if student already has a room
if ($student['Room_ID']) {
    $_SESSION['error_msg'] = "Student already has a room assigned (Room #{$student['Room_ID']})!";
    header("location: view_student.php?id=$student_id");
    exit();
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $room_id = $_POST['room_id'];
    $booking_date = date('Y-m-d'); // Current date for booking
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // 1. Check if room exists and has space
        $sql_room = "SELECT * FROM Room WHERE Room_ID = ? AND Status != 'Maintenance' AND Current_Occupancy < Capacity";
        $stmt_room = $conn->prepare($sql_room);
        $stmt_room->bind_param("i", $room_id);
        $stmt_room->execute();
        $result_room = $stmt_room->get_result();
        
        if ($result_room->num_rows == 0) {
            throw new Exception("Selected room is not available or at full capacity.");
        }
        
        $room = $result_room->fetch_assoc();
        
        // 2. Update student record with room assignment
        $sql_update_student = "UPDATE Student SET Room_ID = ? WHERE Student_ID = ?";
        $stmt_update_student = $conn->prepare($sql_update_student);
        $stmt_update_student->bind_param("ii", $room_id, $student_id);
        
        if (!$stmt_update_student->execute()) {
            throw new Exception("Failed to update student record: " . $conn->error);
        }
        
        // 3. Update room occupancy and status
        $new_occupancy = $room['Current_Occupancy'] + 1;
        $new_status = ($new_occupancy >= $room['Capacity']) ? 'Occupied' : 'Available';
        
        $sql_update_room = "UPDATE Room SET Current_Occupancy = ?, Status = ? WHERE Room_ID = ?";
        $stmt_update_room = $conn->prepare($sql_update_room);
        $stmt_update_room->bind_param("isi", $new_occupancy, $new_status, $room_id);
        
        if (!$stmt_update_room->execute()) {
            throw new Exception("Failed to update room record: " . $conn->error);
        }
        
        // 4. Create booking record
        $booking_status = "Confirmed";
        $sql_booking = "INSERT INTO Booking (Student_ID, Room_ID, Booking_Date, Status) VALUES (?, ?, ?, ?)";
        $stmt_booking = $conn->prepare($sql_booking);
        $stmt_booking->bind_param("iiss", $student_id, $room_id, $booking_date, $booking_status);
        
        if (!$stmt_booking->execute()) {
            throw new Exception("Failed to create booking record: " . $conn->error);
        }
        
        // Commit transaction
        $conn->commit();
        
        $_SESSION['success_msg'] = "Room #{$room_id} successfully assigned to " . htmlspecialchars($student['Student_Name']) . "!";
        header("location: view_student.php?id=$student_id");
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        $_SESSION['error_msg'] = $e->getMessage();
        header("location: assign_room.php?student_id=$student_id");
        exit();
    }
}

// Get available rooms (not at full capacity and not under maintenance)
$sql_rooms = "SELECT * FROM Room WHERE Current_Occupancy < Capacity AND Status != 'Maintenance' ORDER BY Room_ID";
$result_rooms = $conn->query($sql_rooms);

// Build content for template
ob_start();
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Assign Room</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="students.php">Students</a></li>
        <li class="breadcrumb-item"><a href="view_student.php?id=<?= $student_id ?>">View Student</a></li>
        <li class="breadcrumb-item active">Assign Room</li>
    </ol>
    
    <?php if (isset($_SESSION['error_msg'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error_msg'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_msg']); ?>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-home me-1"></i>
                    Assign Room to <?= htmlspecialchars($student['Student_Name']) ?>
                </div>
                <div class="card-body">
                    <?php if ($result_rooms->num_rows > 0): ?>
                        <form action="" method="POST">
                            <div class="mb-3">
                                <label for="room_id" class="form-label">Select Room</label>
                                <select class="form-select" id="room_id" name="room_id" required>
                                    <option value="">-- Select Room --</option>
                                    <?php while ($room = $result_rooms->fetch_assoc()): ?>
                                        <option value="<?= $room['Room_ID'] ?>">
                                            Room #<?= $room['Room_ID'] ?> (<?= $room['Room_Type'] ?>) - 
                                            <?= $room['Current_Occupancy'] ?>/<?= $room['Capacity'] ?> Occupied
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <div class="form-text">Select an available room to assign to this student.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Student Information</label>
                                <div class="card">
                                    <div class="card-body py-2">
                                        <p class="mb-1"><strong>Name:</strong> <?= htmlspecialchars($student['Student_Name']) ?></p>
                                        <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($student['Student_Email']) ?></p>
                                        <p class="mb-0"><strong>Phone:</strong> <?= htmlspecialchars($student['Student_Phone']) ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="alert alert-info" role="alert">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Assigning a room will automatically create a booking record with today's date.
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="view_student.php?id=<?= $student_id ?>" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-check me-1"></i> Assign Room
                                </button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-warning" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            No available rooms at the moment. All rooms are either at full capacity or under maintenance.
                        </div>
                        <a href="view_student.php?id=<?= $student_id ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Student Details
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle me-1"></i>
                    Room Information
                </div>
                <div class="card-body">
                    <div id="roomDetails" class="alert alert-secondary text-center">
                        <i class="fas fa-home fa-3x mb-3"></i>
                        <p>Select a room to view its details.</p>
                    </div>
                    
                    <table class="table table-bordered table-striped d-none" id="roomInfo">
                        <tbody>
                            <tr>
                                <th>Room Number</th>
                                <td id="roomNumber">-</td>
                            </tr>
                            <tr>
                                <th>Room Type</th>
                                <td id="roomType">-</td>
                            </tr>
                            <tr>
                                <th>Capacity</th>
                                <td id="roomCapacity">-</td>
                            </tr>
                            <tr>
                                <th>Current Occupancy</th>
                                <td id="roomOccupancy">-</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td id="roomStatus">-</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // JavaScript to update room details when a room is selected
    document.addEventListener('DOMContentLoaded', function() {
        // Store room data from PHP
        const roomData = {
            <?php 
            $result_rooms->data_seek(0); // Reset result pointer
            while ($room = $result_rooms->fetch_assoc()): 
            ?>
                <?= $room['Room_ID'] ?>: {
                    id: <?= $room['Room_ID'] ?>,
                    type: "<?= $room['Room_Type'] ?>",
                    capacity: <?= $room['Capacity'] ?>,
                    occupancy: <?= $room['Current_Occupancy'] ?>,
                    status: "<?= $room['Status'] ?>"
                },
            <?php endwhile; ?>
        };
        
        // Get elements
        const roomIdSelect = document.getElementById('room_id');
        const roomDetails = document.getElementById('roomDetails');
        const roomInfo = document.getElementById('roomInfo');
        const roomNumber = document.getElementById('roomNumber');
        const roomType = document.getElementById('roomType');
        const roomCapacity = document.getElementById('roomCapacity');
        const roomOccupancy = document.getElementById('roomOccupancy');
        const roomStatus = document.getElementById('roomStatus');
        
        // Add event listener to select
        roomIdSelect.addEventListener('change', function() {
            const selectedRoomId = this.value;
            
            if (selectedRoomId) {
                // Get selected room data
                const room = roomData[selectedRoomId];
                
                // Update room info
                roomNumber.textContent = room.id;
                roomType.textContent = room.type;
                roomCapacity.textContent = room.capacity;
                roomOccupancy.textContent = room.occupancy + ' / ' + room.capacity;
                
                // Set status with appropriate badge
                let statusBadge = '';
                switch (room.status) {
                    case 'Available':
                        statusBadge = '<span class="badge bg-success">Available</span>';
                        break;
                    case 'Occupied':
                        statusBadge = '<span class="badge bg-danger">Occupied</span>';
                        break;
                    case 'Reserved':
                        statusBadge = '<span class="badge bg-warning text-dark">Reserved</span>';
                        break;
                    default:
                        statusBadge = '<span class="badge bg-secondary">' + room.status + '</span>';
                }
                roomStatus.innerHTML = statusBadge;
                
                // Show room info table, hide placeholder
                roomDetails.classList.add('d-none');
                roomInfo.classList.remove('d-none');
            } else {
                // Show placeholder, hide room info table
                roomDetails.classList.remove('d-none');
                roomInfo.classList.add('d-none');
            }
        });
    });
</script>

<?php
$content = ob_get_clean();
include '../layout.php';
?>