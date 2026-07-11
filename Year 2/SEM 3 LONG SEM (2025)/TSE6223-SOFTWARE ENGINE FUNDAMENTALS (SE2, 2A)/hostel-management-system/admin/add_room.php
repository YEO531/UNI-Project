<?php
require_once '../db_connection.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] != "admin") {
    header("location: ../login.php");
    exit();
}

$page_title = "Add New Room - Admin Dashboard";

// Initialize variables
$room_type = $capacity = "";
$errors = [];
$success = false;

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate room type
    if (empty($_POST['room_type'])) {
        $errors[] = "Room type is required";
    } else {
        $room_type = trim($_POST['room_type']);
    }
    
    // Validate capacity
    if (empty($_POST['capacity'])) {
        $errors[] = "Capacity is required";
    } else if (!is_numeric($_POST['capacity']) || $_POST['capacity'] <= 0) {
        $errors[] = "Capacity must be a positive number";
    } else {
        $capacity = (int)$_POST['capacity'];
    }
    
    // Get status if submitted, otherwise use default 'Available'
    $status = isset($_POST['status']) ? $_POST['status'] : 'Available';
    
    // Validate status
    $valid_statuses = ['Available', 'Maintenance', 'Reserved'];
    if (!in_array($status, $valid_statuses)) {
        $errors[] = "Invalid room status";
    }
    
    // If no errors, proceed with insertion
    if (empty($errors)) {
        // Default values
        $current_occupancy = 0;
        
        // Prepare SQL statement
        $sql = "INSERT INTO Room (Room_Type, Capacity, Current_Occupancy, Status) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("siis", $room_type, $capacity, $current_occupancy, $status);
        
        // Execute statement
        if ($stmt->execute()) {
            $success = true;
            $room_id = $conn->insert_id;
            
            // Redirect to the rooms page with success message
            $_SESSION['success_msg'] = "Room #$room_id has been added successfully!";
            header("location: rooms.php");
            exit();
        } else {
            $errors[] = "Error adding room: " . $conn->error;
        }
    }
}

// Get room types for dropdown
$sql_types = "SELECT DISTINCT Room_Type FROM Room ORDER BY Room_Type";
$result_types = $conn->query($sql_types);
$room_types = [];
while ($row = $result_types->fetch_assoc()) {
    $room_types[] = $row['Room_Type'];
}

// Build content for template
ob_start();
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Add New Room</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="rooms.php">Rooms</a></li>
        <li class="breadcrumb-item active">Add New Room</li>
    </ol>
    
    <div class="row">
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-plus-circle me-1"></i>
                    Room Details
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
                    
                    <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                        <div class="mb-3">
                            <label for="room_type" class="form-label">Room Type</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="room_type" name="room_type" 
                                       list="room_type_list" value="<?= htmlspecialchars($room_type) ?>" required>
                                <datalist id="room_type_list">
                                    <?php foreach ($room_types as $type): ?>
                                        <option value="<?= htmlspecialchars($type) ?>">
                                    <?php endforeach; ?>
                                </datalist>
                                <span class="input-group-text">
                                    <i class="fas fa-door-open"></i>
                                </span>
                            </div>
                            <div class="form-text">Enter room type (e.g., Single, Double, Suite)</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="capacity" class="form-label">Capacity</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="capacity" name="capacity" 
                                       min="1" max="20" value="<?= htmlspecialchars($capacity) ?>" required>
                                <span class="input-group-text">
                                    <i class="fas fa-users"></i>
                                </span>
                            </div>
                            <div class="form-text">Maximum number of students that can be accommodated</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Initial Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="Available" selected>Available</option>
                                <option value="Maintenance">Maintenance</option>
                                <option value="Reserved">Reserved</option>
                            </select>
                            <div class="form-text">Initial status of the room (Occupied status will be set automatically when students are assigned)</div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="rooms.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Add Room
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle me-1"></i>
                    Room Type Information
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Room Type</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Single</td>
                                    <td>A room assigned to one person. May have a private or shared bathroom.</td>
                                </tr>
                                <tr>
                                    <td>Double</td>
                                    <td>A room assigned to two people. May have bunk beds or single beds.</td>
                                </tr>
                                <tr>
                                    <td>Triple</td>
                                    <td>A room assigned to three people, typically with bunk beds and a single bed.</td>
                                </tr>
                                <tr>
                                    <td>Quad</td>
                                    <td>A room assigned to four people, typically with two bunk beds or four single beds.</td>
                                </tr>
                                <tr>
                                    <td>Suite</td>
                                    <td>A set of rooms with a shared common area, often with private bathrooms.</td>
                                </tr>
                                <tr>
                                    <td>Studio</td>
                                    <td>A self-contained unit with sleeping area, kitchenette and bathroom.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <h5><i class="fas fa-lightbulb me-2"></i>Tips for Adding Rooms</h5>
                        <ul>
                            <li>For consistency, use the same naming convention for similar room types.</li>
                            <li>The capacity should reflect the maximum number of students that can be accommodated.</li>
                            <li>Set rooms under renovation to "Maintenance" status.</li>
                            <li>You can add multiple rooms of the same type quickly by submitting this form multiple times.</li>
                        </ul>
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