<?php
require_once '../db_connection.php';

// Check if user is logged in
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user_role"])) {
    header("location: ../login.php");
    exit();
}

// Get user role and set page title
$user_role = $_SESSION["user_role"];
$user_id = $_SESSION["user_id"];
$page_title = "Manage Repair Requests - Hostel Management System";

// Check if the user has access to this page (only staff should access)
if ($user_role != "staff") {
    header("location: ../index.php");
    exit();
}

// Process form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $request_id = sanitize_input($_POST['request_id']);
    $status = sanitize_input($_POST['status']);
    $scheduled_date = null;
    
    if ($status == "Scheduled" && !empty($_POST['scheduled_date'])) {
        $scheduled_date = sanitize_input($_POST['scheduled_date']);
    }
    
    $sql = "UPDATE RepairRequest SET Status = ?";
    $params = array($status);
    $types = "s";
    
    if ($scheduled_date) {
        $sql .= ", Scheduled_Date = ?";
        $params[] = $scheduled_date;
        $types .= "s";
    }
    
    $sql .= " WHERE Request_ID = ?";
    $params[] = $request_id;
    $types .= "i";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        $success_message = "Repair request updated successfully!";
    } else {
        $error_message = "Error updating repair request: " . $conn->error;
    }
    $stmt->close();
}

// Fetch all repair requests for staff view
$sql = "SELECT r.*, s.Student_Name, room.Room_Type 
        FROM RepairRequest r
        JOIN Student s ON r.Student_ID = s.Student_ID
        JOIN Room room ON r.Room_ID = room.Room_ID
        ORDER BY 
            CASE 
                WHEN r.Status = 'Pending' THEN 1
                WHEN r.Status = 'Scheduled' THEN 2
                WHEN r.Status = 'In Progress' THEN 3
                WHEN r.Status = 'Completed' THEN 4
                WHEN r.Status = 'Cancelled' THEN 5
            END,
            r.Request_Date DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$repair_requests = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Build content for template
ob_start();
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Manage Repair Requests</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Manage Repair Requests</li>
    </ol>
    
    <?php if(isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <?php if(isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
    <!-- Repair Requests List -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            All Repair Requests
        </div>
        <div class="card-body">
            <?php if(count($repair_requests) > 0): ?>
                <table id="repairRequestsTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Student</th>
                            <th>Room</th>
                            <th>Repair Type</th>
                            <th>Description</th>
                            <th>Request Date</th>
                            <th>Scheduled Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($repair_requests as $request): ?>
                            <tr>
                                <td><?php echo $request['Request_ID']; ?></td>
                                <td><?php echo $request['Student_Name']; ?></td>
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
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#updateModal<?php echo $request['Request_ID']; ?>">
                                        Update Status
                                    </button>

                                    <!-- Update Status Modal -->
                                    <div class="modal fade" id="updateModal<?php echo $request['Request_ID']; ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Update Request Status</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="request_id" value="<?php echo $request['Request_ID']; ?>">
                                                        
                                                        <div class="mb-3">
                                                            <label for="status" class="form-label">Status:</label>
                                                            <select class="form-select" id="status<?php echo $request['Request_ID']; ?>" name="status" required>
                                                                <option value="Pending" <?php echo ($request['Status'] == 'Pending' ? 'selected' : ''); ?>>Pending</option>
                                                                <option value="Scheduled" <?php echo ($request['Status'] == 'Scheduled' ? 'selected' : ''); ?>>Scheduled</option>
                                                                <option value="In Progress" <?php echo ($request['Status'] == 'In Progress' ? 'selected' : ''); ?>>In Progress</option>
                                                                <option value="Completed" <?php echo ($request['Status'] == 'Completed' ? 'selected' : ''); ?>>Completed</option>
                                                                <option value="Cancelled" <?php echo ($request['Status'] == 'Cancelled' ? 'selected' : ''); ?>>Cancelled</option>
                                                            </select>
                                                        </div>
                                                        
                                                        <div class="mb-3 scheduled-date-field" id="scheduledDateField<?php echo $request['Request_ID']; ?>">
                                                            <label for="scheduled_date" class="form-label">Scheduled Date:</label>
                                                            <input type="date" class="form-control" id="scheduled_date<?php echo $request['Request_ID']; ?>" name="scheduled_date" value="<?php echo $request['Scheduled_Date']; ?>">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" name="update_status" class="btn btn-primary">Save changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    No repair requests found.
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
</style>

<script>
    // Initialize DataTable
    $(document).ready(function() {
        $('#repairRequestsTable').DataTable({
            "order": [[ 0, "desc" ]], // Order by Request ID descending (newest first)
            "pageLength": 10,
            "responsive": true,
            "columnDefs": [
                { "orderable": false, "targets": [4, 8] }, // Make description and actions columns non-sortable
                { "width": "8%", "targets": [0] },      // Request ID
                { "width": "15%", "targets": [1] },     // Student
                { "width": "12%", "targets": [2] },     // Room
                { "width": "10%", "targets": [3] },     // Repair Type
                { "width": "20%", "targets": [4] },     // Description
                { "width": "10%", "targets": [5] },     // Request Date
                { "width": "10%", "targets": [6] },     // Scheduled Date
                { "width": "10%", "targets": [7] },     // Status
                { "width": "5%", "targets": [8] }       // Actions
            ]
        });
        
        // Show/hide scheduled date field based on status for each modal
        <?php foreach($repair_requests as $request): ?>
        $('#status<?php echo $request['Request_ID']; ?>').change(function() {
            var selectedStatus = $(this).val();
            if (selectedStatus === 'Scheduled') {
                $('#scheduledDateField<?php echo $request['Request_ID']; ?>').show();
            } else {
                $('#scheduledDateField<?php echo $request['Request_ID']; ?>').hide();
            }
        }).change(); // Trigger on page load
        <?php endforeach; ?>
    });
</script>

<?php
$content = ob_get_clean();
include '../layout.php';
?>