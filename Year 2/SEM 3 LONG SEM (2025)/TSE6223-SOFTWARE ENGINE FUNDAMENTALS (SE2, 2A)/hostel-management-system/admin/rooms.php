<?php
require_once '../db_connection.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] != "admin") {
    header("location: ../login.php");
    exit();
}

$page_title = "Manage Rooms - Admin Dashboard";

// Handle room status update
if (isset($_GET['action']) && $_GET['action'] == 'update_status' && isset($_GET['id']) && isset($_GET['status'])) {
    $room_id = $_GET['id'];
    $status = $_GET['status'];
    
    // Validate status
    $valid_statuses = ['Available', 'Occupied', 'Maintenance', 'Reserved'];
    if (in_array($status, $valid_statuses)) {
        $sql = "UPDATE Room SET Status = ? WHERE Room_ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $room_id);
        
        if ($stmt->execute()) {
            $_SESSION['success_msg'] = "Room status updated successfully!";
        } else {
            $_SESSION['error_msg'] = "Error updating room status: " . $conn->error;
        }
    } else {
        $_SESSION['error_msg'] = "Invalid room status.";
    }
    
    header("location: rooms.php");
    exit();
}

// Handle room deletion
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $room_id = $_GET['id'];
    
    // First check if room has students
    $sql_check = "SELECT COUNT(*) as student_count FROM Student WHERE Room_ID = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $room_id);
    $stmt_check->execute();
    $student_count = $stmt_check->get_result()->fetch_assoc()['student_count'];
    
    if ($student_count > 0) {
        $_SESSION['error_msg'] = "Cannot delete room. There are $student_count students assigned to this room.";
    } else {
        $sql = "DELETE FROM Room WHERE Room_ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $room_id);
        
        if ($stmt->execute()) {
            $_SESSION['success_msg'] = "Room deleted successfully!";
        } else {
            $_SESSION['error_msg'] = "Error deleting room: " . $conn->error;
        }
    }
    
    header("location: rooms.php");
    exit();
}

// Search and filter functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter_type = isset($_GET['filter_type']) ? $_GET['filter_type'] : '';
$filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';

$where_conditions = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where_conditions[] = "(Room_ID LIKE ? OR Room_Type LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

if (!empty($filter_type)) {
    $where_conditions[] = "Room_Type = ?";
    $params[] = $filter_type;
    $types .= "s";
}

if (!empty($filter_status)) {
    $where_conditions[] = "Status = ?";
    $params[] = $filter_status;
    $types .= "s";
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = " WHERE " . implode(" AND ", $where_conditions);
}

// Get all rooms
$sql = "SELECT * FROM Room" . $where_clause . " ORDER BY Room_ID";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Get room type counts for filter dropdown
$sql_types = "SELECT Room_Type, COUNT(*) as count FROM Room GROUP BY Room_Type ORDER BY Room_Type";
$result_types = $conn->query($sql_types);
$room_types = [];
while ($row = $result_types->fetch_assoc()) {
    $room_types[$row['Room_Type']] = $row['count'];
}

// Get room status counts for filter dropdown
$sql_statuses = "SELECT Status, COUNT(*) as count FROM Room GROUP BY Status ORDER BY Status";
$result_statuses = $conn->query($sql_statuses);
$room_statuses = [];
while ($row = $result_statuses->fetch_assoc()) {
    $room_statuses[$row['Status']] = $row['count'];
}

// Build content for template
ob_start();
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Manage Rooms</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Rooms</li>
    </ol>
    
    <?php if (isset($_SESSION['success_msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success_msg'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_msg']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_msg'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error_msg'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_msg']); ?>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-door-open me-1"></i>
                Room Management
            </div>
            <div>
                <a href="add_room.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add New Room
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Search and Filter Form -->
            <form action="" method="GET" class="mb-4">
                <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Search Room ID" value="<?= htmlspecialchars($search) ?>">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="filter_type" class="form-select">
                            <option value="">All Room Types</option>
                            <?php foreach ($room_types as $type => $count): ?>
                                <option value="<?= $type ?>" <?= $filter_type == $type ? 'selected' : '' ?>>
                                    <?= $type ?> (<?= $count ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="filter_status" class="form-select">
                            <option value="">All Statuses</option>
                            <?php foreach ($room_statuses as $status => $count): ?>
                                <option value="<?= $status ?>" <?= $filter_status == $status ? 'selected' : '' ?>>
                                    <?= $status ?> (<?= $count ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                            <?php if (!empty($search) || !empty($filter_type) || !empty($filter_status)): ?>
                                <a href="rooms.php" class="btn btn-secondary">Clear Filters</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </form>
            
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="roomsTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Room ID</th>
                            <th>Type</th>
                            <th>Capacity</th>
                            <th>Occupancy</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['Room_ID'] ?></td>
                                    <td><?= $row['Room_Type'] ?></td>
                                    <td><?= $row['Capacity'] ?></td>
                                    <td>
                                        <?= $row['Current_Occupancy'] ?> / <?= $row['Capacity'] ?>
                                        <?php if ($row['Current_Occupancy'] == $row['Capacity']): ?>
                                            <span class="badge bg-danger">Full</span>
                                        <?php elseif ($row['Current_Occupancy'] > 0): ?>
                                            <span class="badge bg-warning text-dark">Partial</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Empty</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $status_class = '';
                                        switch ($row['Status']) {
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
                                        <span class="badge <?= $status_class ?>"><?= $row['Status'] ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="view_room.php?id=<?= $row['Room_ID'] ?>" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit_room.php?id=<?= $row['Room_ID'] ?>" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="rooms.php?action=delete&id=<?= $row['Room_ID'] ?>" class="btn btn-danger btn-sm" 
                                               onclick="return confirm('Are you sure you want to delete this room? This action cannot be undone.')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No rooms found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-chart-pie me-1"></i>
            Room Statistics
        </div>
        <div class="card-body">
            <div class="row">
                <?php
                // Get overall room statistics
                $sql_room_stats = "SELECT 
                    COUNT(*) AS total_rooms,
                    SUM(Capacity) AS total_capacity,
                    SUM(Current_Occupancy) AS total_occupancy,
                    SUM(CASE WHEN Status = 'Available' THEN 1 ELSE 0 END) AS available_rooms,
                    SUM(CASE WHEN Status = 'Occupied' THEN 1 ELSE 0 END) AS occupied_rooms,
                    SUM(CASE WHEN Status = 'Maintenance' THEN 1 ELSE 0 END) AS maintenance_rooms,
                    SUM(CASE WHEN Status = 'Reserved' THEN 1 ELSE 0 END) AS reserved_rooms
                FROM Room";
                
                $room_stats = $conn->query($sql_room_stats)->fetch_assoc();
                
                // Calculate occupancy rate
                $occupancy_rate = 0;
                if ($room_stats['total_capacity'] > 0) {
                    $occupancy_rate = round(($room_stats['total_occupancy'] / $room_stats['total_capacity']) * 100);
                }
                ?>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-primary text-white mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>Total Rooms</div>
                                <div class="h3"><?= $room_stats['total_rooms'] ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-success text-white mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>Available</div>
                                <div class="h3"><?= $room_stats['available_rooms'] ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-danger text-white mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>Occupied</div>
                                <div class="h3"><?= $room_stats['occupied_rooms'] ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-warning text-dark mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>Maintenance</div>
                                <div class="h3"><?= $room_stats['maintenance_rooms'] ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-bed me-1"></i>
                            Capacity Details
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Total Capacity: <?= $room_stats['total_capacity'] ?> beds</h5>
                                    <h5>Current Occupancy: <?= $room_stats['total_occupancy'] ?> students</h5>
                                    <h5>Available Beds: <?= $room_stats['total_capacity'] - $room_stats['total_occupancy'] ?></h5>
                                </div>
                                <div class="col-md-6">
                                    <div class="progress" style="height: 24px;">
                                        <div class="progress-bar" role="progressbar" style="width: <?= $occupancy_rate ?>%;" 
                                             aria-valuenow="<?= $occupancy_rate ?>" aria-valuemin="0" aria-valuemax="100">
                                            <?= $occupancy_rate ?>% Occupied
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-door-open me-1"></i>
                            Room Types
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Room Type</th>
                                            <th>Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($room_types as $type => $count): ?>
                                            <tr>
                                                <td><?= $type ?></td>
                                                <td><?= $count ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Initialize datatable
    $(document).ready(function() {
        $('#roomsTable').DataTable({
            responsive: true,
            "paging": true,
            "searching": false, // Disable built-in search as we have custom search
            "info": true,
            "order": [[0, "asc"]]
        });
    });
</script>

<?php
$content = ob_get_clean();
include '../layout.php';
?>