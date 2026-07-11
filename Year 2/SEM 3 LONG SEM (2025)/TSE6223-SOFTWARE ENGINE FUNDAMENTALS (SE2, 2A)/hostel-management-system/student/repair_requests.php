<?php
require_once '../db_connection.php';
//session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user_role"])) {
    header("location: ../login.php");
    exit();
}

// Get user role and set page title
$user_role = $_SESSION["user_role"];
$user_id = $_SESSION["user_id"];
$page_title = "My Repair Requests - Hostel Management System";

// Check if the user has access to this page (only students should access)
if ($user_role != "student") {
    header("location: ../index.php");
    exit();
}

// Process form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_request'])) {
    $room_id = sanitize_input($_POST['room_id']);
    $repair_type = sanitize_input($_POST['repair_type']);
    $description = sanitize_input($_POST['description']);
    $request_date = date('Y-m-d');
    
    $sql = "INSERT INTO RepairRequest (Student_ID, Room_ID, repair_type, Description, Request_Date, Status) 
            VALUES (?, ?, ?, ?, ?, 'Pending')";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisss", $user_id, $room_id, $repair_type, $description, $request_date);
    
    if ($stmt->execute()) {
        $success_message = "Repair request submitted successfully!";
    } else {
        $error_message = "Error submitting repair request: " . $conn->error;
    }
    $stmt->close();
}

// Fetch student's repair requests
$sql = "SELECT r.*, room.Room_Type 
        FROM RepairRequest r
        JOIN Room room ON r.Room_ID = room.Room_ID
        WHERE r.Student_ID = ?
        ORDER BY r.Request_Date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$repair_requests = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get student's room for the create form
$sql = "SELECT Room_ID FROM Student WHERE Student_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$student_room = $result->fetch_assoc();
$stmt->close();

// Define repair types for the dropdown
$repair_types = [
    'Toilet' => 'Toilet',
    'Electrical' => 'Electrical',
    'Furniture' => 'Furniture',
    'Door/Window' => 'Door/Window',
    'Paint/Wall' => 'Paint/Wall',
    'Air Conditioning' => 'Air Conditioning',
    'Flooring' => 'Flooring',
    'Lighting' => 'Lighting',
    'Other' => 'Other'
];

// Build content for template
ob_start();
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">My Repair Requests</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">My Repair Requests</li>
    </ol>
    
    <?php if(isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <?php if(isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
    <?php if(isset($student_room) && $student_room['Room_ID']): ?>
        <!-- Create Repair Request Form -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-tools me-1"></i>
                Create New Repair Request
            </div>
            <div class="card-body">
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <input type="hidden" name="room_id" value="<?php echo $student_room['Room_ID']; ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="repair_type" class="form-label">Repair Type:</label>
                            <select class="form-select" id="repair_type" name="repair_type" required>
                                <option value="">Select repair type...</option>
                                <?php foreach($repair_types as $value => $label): ?>
                                    <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description of the Issue:</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required 
                                  placeholder="Please provide detailed information about the issue..."></textarea>
                        <div class="form-text">Be as specific as possible to help our maintenance team understand the problem.</div>
                    </div>
                    
                    <button type="submit" name="create_request" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-1"></i>
                        Submit Request
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Repair Requests List -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            My Repair Requests
        </div>
        <div class="card-body">
            <?php if(count($repair_requests) > 0): ?>
                <table id="repairRequestsTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Room</th>
                            <th>Repair Type</th>
                            <th>Description</th>
                            <th>Request Date</th>
                            <th>Scheduled Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($repair_requests as $request): ?>
                            <tr>
                                <td><?php echo $request['Request_ID']; ?></td>
                                <td><?php echo $request['Room_Type'] . " (Room " . $request['Room_ID'] . ")"; ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        switch($request['repair_type']) {
                                            case 'Toilet': echo 'info'; break;
                                            case 'Electrical': echo 'warning'; break;
                                            case 'Furniture': echo 'secondary'; break;
                                            case 'Door/Window': echo 'primary'; break;
                                            case 'Paint/Wall': echo 'success'; break;
                                            case 'Air Conditioning': echo 'info'; break;
                                            case 'Flooring': echo 'dark'; break;
                                            case 'Lighting': echo 'warning'; break;
                                            default: echo 'light text-dark';
                                        }
                                    ?>">
                                        <?php echo $request['repair_type'] ?? 'Not specified'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="description-cell" title="<?php echo htmlspecialchars($request['Description']); ?>">
                                        <?php echo strlen($request['Description']) > 50 ? 
                                              substr($request['Description'], 0, 47) . '...' : 
                                              $request['Description']; ?>
                                    </div>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($request['Request_Date'])); ?></td>
                                <td>
                                    <?php 
                                        echo ($request['Scheduled_Date']) ? 
                                              date('M d, Y', strtotime($request['Scheduled_Date'])) : 
                                              "<span class='text-muted'>Not scheduled</span>";
                                    ?>
                                </td>
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
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    You have no repair requests. Create one above if you need maintenance assistance.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.description-cell {
    max-width: 200px;
    cursor: help;
}

.badge {
    font-size: 0.8em;
}

.form-text {
    font-size: 0.875em;
    color: #6c757d;
}
</style>

<script>
    // Initialize DataTable
    $(document).ready(function() {
        $('#repairRequestsTable').DataTable({
            "order": [[ 0, "desc" ]], // Order by Request ID descending (newest first)
            "pageLength": 10,
            "responsive": true,
            "columnDefs": [
                { "orderable": false, "targets": [3] }, // Make description column non-sortable
                { "width": "10%", "targets": [0] },     // Request ID
                { "width": "15%", "targets": [1] },     // Room
                { "width": "12%", "targets": [2] },     // Repair Type
                { "width": "25%", "targets": [3] },     // Description
                { "width": "12%", "targets": [4] },     // Request Date
                { "width": "12%", "targets": [5] },     // Scheduled Date
                { "width": "14%", "targets": [6] }      // Status
            ]
        });
    });
</script>

<?php
$content = ob_get_clean();
include '../layout.php';
?>