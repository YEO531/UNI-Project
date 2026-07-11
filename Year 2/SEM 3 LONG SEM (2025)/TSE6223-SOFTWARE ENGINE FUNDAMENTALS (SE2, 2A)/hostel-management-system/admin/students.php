<?php
require_once '../db_connection.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] != "admin") {
    header("location: ../login.php");
    exit();
}

$page_title = "Manage Students - Admin Dashboard";

// Handle student deletion
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $student_id = $_GET['id'];
    $sql = "DELETE FROM Student WHERE Student_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_msg'] = "Student deleted successfully!";
    } else {
        $_SESSION['error_msg'] = "Error deleting student: " . $conn->error;
    }
    
    header("location: students.php");
    exit();
}

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_condition = '';
$params = [];
$types = '';

if (!empty($search)) {
    $search_condition = " WHERE Student_Name LIKE ? OR Student_Email LIKE ? OR Student_Phone LIKE ?";
    $search_param = "%$search%";
    $params = [$search_param, $search_param, $search_param];
    $types = "sss";
}

// Get all students with room information
$sql = "SELECT s.*, r.Room_Type 
        FROM Student s 
        LEFT JOIN Room r ON s.Room_ID = r.Room_ID"
        . $search_condition .
        " ORDER BY s.Student_ID";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Build content for template
ob_start();
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Manage Students</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Students</li>
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
                <i class="fas fa-users me-1"></i>
                Student Management
            </div>
            <div>
                <a href="add_student.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add New Student
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Search Form -->
            <form action="" method="GET" class="mb-4">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search by name, email, or phone..." value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <?php if (!empty($search)): ?>
                        <a href="students.php" class="btn btn-secondary">Clear</a>
                    <?php endif; ?>
                </div>
            </form>
            
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="studentsTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Room</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['Student_ID'] ?></td>
                                    <td><?= htmlspecialchars($row['Student_Name']) ?></td>
                                    <td><?= htmlspecialchars($row['Student_Email']) ?></td>
                                    <td><?= htmlspecialchars($row['Student_Phone']) ?></td>
                                    <td>
                                        <?php if ($row['Room_ID']): ?>
                                            Room #<?= $row['Room_ID'] ?> (<?= $row['Room_Type'] ?>)
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Not Assigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="view_student.php?id=<?= $row['Student_ID'] ?>" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="edit_student.php?id=<?= $row['Student_ID'] ?>" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="students.php?action=delete&id=<?= $row['Student_ID'] ?>" class="btn btn-danger btn-sm" 
                                           onclick="return confirm('Are you sure you want to delete this student? This action cannot be undone.')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No students found.</td>
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
            Student Statistics
        </div>
        <div class="card-body">
            <div class="row">
                <?php
                // Get total number of students
                $sql_total = "SELECT COUNT(*) as total FROM Student";
                $total_students = $conn->query($sql_total)->fetch_assoc()['total'];
                
                // Get number of students with rooms assigned
                $sql_with_rooms = "SELECT COUNT(*) as total FROM Student WHERE Room_ID IS NOT NULL";
                $students_with_rooms = $conn->query($sql_with_rooms)->fetch_assoc()['total'];
                
                // Get number of students without rooms
                $students_without_rooms = $total_students - $students_with_rooms;
                ?>
                <div class="col-xl-4 col-md-6">
                    <div class="card bg-primary text-white mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>Total Students</div>
                                <div class="h3"><?= $total_students ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="card bg-success text-white mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>With Room Assignment</div>
                                <div class="h3"><?= $students_with_rooms ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="card bg-warning text-dark mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>Without Room Assignment</div>
                                <div class="h3"><?= $students_without_rooms ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Initialize datatable with search and pagination
    $(document).ready(function() {
        $('#studentsTable').DataTable({
            responsive: true,
            "paging": true,
            "searching": false, // Disable built-in search as we have custom search
            "info": true,
            "order": [[0, "desc"]]
        });
    });
</script>

<?php
$content = ob_get_clean();
include '../layout.php';
?>