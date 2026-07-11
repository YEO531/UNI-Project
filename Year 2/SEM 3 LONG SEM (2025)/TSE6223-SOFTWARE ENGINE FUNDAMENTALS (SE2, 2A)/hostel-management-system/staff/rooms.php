<?php
require_once '../db_connection.php';

// Check if user is logged in and is maintenance staff
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] != "staff") {
    header("location: ../login.php");
    exit();
}

$staff_id = $_SESSION["user_id"];
$staff_name = $_SESSION["user_name"];
$page_title = "Manage Rooms - Hostel Management System";

// Process form submissions for updating room status
$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_room'])) {
    $room_id = sanitize_input($_POST['room_id']);
    $status = sanitize_input($_POST['status']);
    $notes = sanitize_input($_POST['maintenance_notes']);
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Update room status
        $sql_update = "UPDATE Room SET Status = ? WHERE Room_ID = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $status, $room_id);
        $stmt_update->execute();
        
        // If status is changed to Maintenance, log this in a maintenance record
        if ($status == 'Maintenance') {
            $sql_log = "INSERT INTO MaintenanceLog (Room_ID, Staff_ID, Start_Date, Notes) 
                       VALUES (?, ?, CURDATE(), ?)";
            $stmt_log = $conn->prepare($sql_log);
            $stmt_log->bind_param("iis", $room_id, $staff_id, $notes);
            $stmt_log->execute();
        }
        
        // If status is changed from Maintenance to Available, update maintenance record
        if ($status == 'Available') {
            $sql_check = "SELECT * FROM MaintenanceLog 
                         WHERE Room_ID = ? AND End_Date IS NULL 
                         ORDER BY Log_ID DESC LIMIT 1";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("i", $room_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            
            if ($result_check->num_rows > 0) {
                $sql_complete = "UPDATE MaintenanceLog SET End_Date = CURDATE(), 
                                Resolution_Notes = CONCAT(Notes, ' | Completed: ', ?) 
                                WHERE Room_ID = ? AND End_Date IS NULL";
                $completion_note = "Work completed by " . $staff_name;
                $stmt_complete = $conn->prepare($sql_complete);
                $stmt_complete->bind_param("si", $completion_note, $room_id);
                $stmt_complete->execute();
            }
        }
        
        // Commit transaction
        $conn->commit();
        $success_message = "Room status updated successfully!";
        
    } catch (mysqli_sql_exception $exception) {
        $conn->rollback();
        $error_message = "Error updating room status: " . $exception->getMessage();
    }
}

// Get all rooms with their current status and occupancy information
$sql = "SELECT r.*, 
        (SELECT COUNT(*) FROM Student s WHERE s.Room_ID = r.Room_ID) as Student_Count,
        (SELECT GROUP_CONCAT(s.Student_Name SEPARATOR ', ') FROM Student s WHERE s.Room_ID = r.Room_ID) as Occupants,
        (SELECT COUNT(*) FROM RepairRequest rr WHERE rr.Room_ID = r.Room_ID AND rr.Status IN ('Pending', 'Scheduled', 'In Progress')) as Pending_Repairs,
        (SELECT m.Start_Date FROM MaintenanceLog m WHERE m.Room_ID = r.Room_ID AND m.End_Date IS NULL ORDER BY m.Log_ID DESC LIMIT 1) as Maintenance_Start,
        (SELECT m.Notes FROM MaintenanceLog m WHERE m.Room_ID = r.Room_ID AND m.End_Date IS NULL ORDER BY m.Log_ID DESC LIMIT 1) as Maintenance_Notes
        FROM Room r
        ORDER BY 
        CASE 
            WHEN r.Status = 'Maintenance' THEN 1
            WHEN r.Status = 'Occupied' THEN 2
            WHEN r.Status = 'Available' THEN 3
            WHEN r.Status = 'Reserved' THEN 4
        END,
        r.Room_ID";

$result = $conn->query($sql);
$rooms = $result->fetch_all(MYSQLI_ASSOC);

// Get maintenance history for rooms
function getMaintenanceHistory($conn, $room_id) {
    $sql = "SELECT ml.*, s.Staff_Name 
            FROM MaintenanceLog ml
            JOIN MaintenanceStaff s ON ml.Staff_ID = s.Staff_ID
            WHERE ml.Room_ID = ?
            ORDER BY ml.Start_Date DESC
            LIMIT 5";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get repair requests for rooms
function getRepairRequests($conn, $room_id) {
    $sql = "SELECT rr.*, s.Student_Name
            FROM RepairRequest rr
            JOIN Student s ON rr.Student_ID = s.Student_ID
            WHERE rr.Room_ID = ?
            ORDER BY rr.Request_Date DESC
            LIMIT 5";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Create sanitize input function if not already defined
if (!function_exists('sanitize_input')) {
    function sanitize_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
}

// Build content for template
ob_start();
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Manage Rooms</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Manage Rooms</li>
    </ol>
    
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
    <!-- Room Status Overview -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>Total Rooms</div>
                        <div class="h3 mb-0"><?php echo count($rooms); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>Available Rooms</div>
                        <div class="h3 mb-0">
                            <?php 
                            echo count(array_filter($rooms, function($room) {
                                return $room['Status'] == 'Available';
                            })); 
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>Occupied Rooms</div>
                        <div class="h3 mb-0">
                            <?php 
                            echo count(array_filter($rooms, function($room) {
                                return $room['Status'] == 'Occupied';
                            })); 
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>Maintenance Rooms</div>
                        <div class="h3 mb-0">
                            <?php 
                            echo count(array_filter($rooms, function($room) {
                                return $room['Status'] == 'Maintenance';
                            })); 
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Rooms List -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-home me-1"></i>
            All Rooms
        </div>
        <div class="card-body">
            <table id="roomsTable" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Room ID</th>
                        <th>Type</th>
                        <th>Capacity</th>
                        <th>Occupancy</th>
                        <th>Status</th>
                        <th>Occupants</th>
                        <th>Pending Repairs</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rooms as $room): ?>
                        <tr>
                            <td><?php echo $room['Room_ID']; ?></td>
                            <td><?php echo $room['Room_Type']; ?></td>
                            <td><?php echo $room['Capacity']; ?></td>
                            <td><?php echo $room['Current_Occupancy']; ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    switch($room['Status']) {
                                        case 'Available': echo 'success'; break;
                                        case 'Occupied': echo 'warning'; break;
                                        case 'Maintenance': echo 'danger'; break;
                                        case 'Reserved': echo 'info'; break;
                                        default: echo 'secondary';
                                    }
                                ?>">
                                    <?php echo $room['Status']; ?>
                                </span>
                                <?php if ($room['Status'] == 'Maintenance' && $room['Maintenance_Start']): ?>
                                    <small class="d-block mt-1">
                                        Since: <?php echo date('M d, Y', strtotime($room['Maintenance_Start'])); ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $room['Occupants'] ? $room['Occupants'] : 'None'; ?></td>
                            <td>
                                <?php if ($room['Pending_Repairs'] > 0): ?>
                                    <span class="badge bg-danger"><?php echo $room['Pending_Repairs']; ?></span>
                                <?php else: ?>
                                    <span class="badge bg-success">0</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#viewDetailsModal<?php echo $room['Room_ID']; ?>">
                                        Details
                                    </button>
                                    <button type="button" class="btn btn-sm btn-warning" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#updateStatusModal<?php echo $room['Room_ID']; ?>">
                                        Update Status
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Details and Update Modals -->
    <?php foreach ($rooms as $room): ?>
        <!-- Room Details Modal -->
        <div class="modal fade" id="viewDetailsModal<?php echo $room['Room_ID']; ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Room <?php echo $room['Room_ID']; ?> Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Room Information</h6>
                                <p><strong>Room ID:</strong> <?php echo $room['Room_ID']; ?></p>
                                <p><strong>Type:</strong> <?php echo $room['Room_Type']; ?></p>
                                <p><strong>Capacity:</strong> <?php echo $room['Capacity']; ?></p>
                                <p><strong>Current Occupancy:</strong> <?php echo $room['Current_Occupancy']; ?></p>
                                <p><strong>Status:</strong> <?php echo $room['Status']; ?></p>
                                <?php if ($room['Status'] == 'Maintenance' && $room['Maintenance_Notes']): ?>
                                    <p><strong>Maintenance Notes:</strong> <?php echo $room['Maintenance_Notes']; ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <h6>Occupants</h6>
                                <?php if ($room['Occupants']): ?>
                                    <ul>
                                        <?php foreach (explode(', ', $room['Occupants']) as $occupant): ?>
                                            <li><?php echo $occupant; ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p>No current occupants</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <!-- Repair Requests -->
                        <h6>Repair Requests</h6>
                        <?php 
                            $repair_requests = getRepairRequests($conn, $room['Room_ID']);
                            if (count($repair_requests) > 0): 
                        ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Request ID</th>
                                            <th>Student</th>
                                            <th>Description</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($repair_requests as $request): ?>
                                            <tr>
                                                <td><?php echo $request['Request_ID']; ?></td>
                                                <td><?php echo $request['Student_Name']; ?></td>
                                                <td><?php echo $request['Description']; ?></td>
                                                <td><?php echo date('M d, Y', strtotime($request['Request_Date'])); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        switch($request['Status']) {
                                                            case 'Pending': echo 'warning'; break;
                                                            case 'Scheduled': echo 'info'; break;
                                                            case 'In Progress': echo 'primary'; break;
                                                            case 'Completed': echo 'success'; break;
                                                            case 'Cancelled': echo 'danger'; break;
                                                            default: echo 'secondary';
                                                        }
                                                    ?>">
                                                        <?php echo $request['Status']; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p>No repair requests for this room.</p>
                        <?php endif; ?>
                        
                        <hr>
                        
                        <!-- Maintenance History -->
                        <h6>Maintenance History</h6>
                        <?php 
                            $maintenance_history = getMaintenanceHistory($conn, $room['Room_ID']);
                            if (count($maintenance_history) > 0): 
                        ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Staff</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($maintenance_history as $record): ?>
                                            <tr>
                                                <td><?php echo date('M d, Y', strtotime($record['Start_Date'])); ?></td>
                                                <td>
                                                    <?php 
                                                        echo $record['End_Date'] ? 
                                                              date('M d, Y', strtotime($record['End_Date'])) : 
                                                              'Ongoing';
                                                    ?>
                                                </td>
                                                <td><?php echo $record['Staff_Name']; ?></td>
                                                <td><?php echo $record['Notes']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p>No maintenance history for this room.</p>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Update Status Modal -->
        <div class="modal fade" id="updateStatusModal<?php echo $room['Room_ID']; ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Update Room <?php echo $room['Room_ID']; ?> Status</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="modal-body">
                            <input type="hidden" name="room_id" value="<?php echo $room['Room_ID']; ?>">
                            
                            <div class="mb-3">
                                <label for="status" class="form-label">Status:</label>
                                <select class="form-select" id="status<?php echo $room['Room_ID']; ?>" name="status" required>
                                    <option value="Available" <?php echo ($room['Status'] == 'Available' ? 'selected' : ''); ?>>Available</option>
                                    <option value="Occupied" <?php echo ($room['Status'] == 'Occupied' ? 'selected' : ''); ?>>Occupied</option>
                                    <option value="Maintenance" <?php echo ($room['Status'] == 'Maintenance' ? 'selected' : ''); ?>>Maintenance</option>
                                    <option value="Reserved" <?php echo ($room['Status'] == 'Reserved' ? 'selected' : ''); ?>>Reserved</option>
                                </select>
                            </div>
                            
                            <div class="mb-3 maintenance-notes-field" id="maintenanceNotesField<?php echo $room['Room_ID']; ?>" style="<?php echo ($room['Status'] == 'Maintenance' ? '' : 'display: none;'); ?>">
                                <label for="maintenance_notes" class="form-label">Maintenance Notes:</label>
                                <textarea class="form-control" id="maintenance_notes<?php echo $room['Room_ID']; ?>" name="maintenance_notes" rows="3"><?php echo $room['Maintenance_Notes']; ?></textarea>
                                <div class="form-text">Describe the maintenance issue or work being done.</div>
                            </div>
                            
                            <?php if ($room['Current_Occupancy'] > 0 && $room['Status'] != 'Maintenance'): ?>
                                <div class="alert alert-warning maintenance-warning" id="maintenanceWarning<?php echo $room['Room_ID']; ?>" style="display: none;">
                                    <strong>Warning:</strong> This room currently has occupants. Setting it to maintenance will require occupants to be relocated.
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($room['Status'] == 'Maintenance'): ?>
                                <div class="alert alert-info available-note" id="availableNote<?php echo $room['Room_ID']; ?>" style="display: none;">
                                    <strong>Note:</strong> Setting this room to Available will mark all maintenance work as completed.
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" name="update_room" class="btn btn-primary">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
    // Initialize DataTable
    $(document).ready(function() {
        $('#roomsTable').DataTable({
            "order": [[ 0, "asc" ]],
            "pageLength": 25
        });
        
        // Show/hide maintenance notes field based on status for each modal
        <?php foreach($rooms as $room): ?>
        $('#status<?php echo $room['Room_ID']; ?>').change(function() {
            var selectedStatus = $(this).val();
            if (selectedStatus === 'Maintenance') {
                $('#maintenanceNotesField<?php echo $room['Room_ID']; ?>').show();
                $('#maintenanceWarning<?php echo $room['Room_ID']; ?>').show();
            } else {
                $('#maintenanceNotesField<?php echo $room['Room_ID']; ?>').hide();
                $('#maintenanceWarning<?php echo $room['Room_ID']; ?>').hide();
            }
            
            if (selectedStatus === 'Available') {
                $('#availableNote<?php echo $room['Room_ID']; ?>').show();
            } else {
                $('#availableNote<?php echo $room['Room_ID']; ?>').hide();
            }
        });
        <?php endforeach; ?>
    });
</script>

<?php
$content = ob_get_clean();
include '../layout.php';
?>