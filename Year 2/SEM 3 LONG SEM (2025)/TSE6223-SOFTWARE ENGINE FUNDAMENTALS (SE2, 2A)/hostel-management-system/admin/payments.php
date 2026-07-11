<?php
require_once '../db_connection.php';
//session_start();

// Check if user is logged in as admin
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user_role"]) || $_SESSION["user_role"] != "admin") {
    header("location: ../login.php");
    exit();
}

// Get user data
$user_role = $_SESSION["user_role"];
$user_id = $_SESSION["user_id"];
$page_title = "Manage Payments - Hostel Management System";

// Process form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle payment status update
    if (isset($_POST['update_payment'])) {
        $payment_id = sanitize_input($_POST['payment_id']);
        $status = sanitize_input($_POST['status']);
        
        $sql = "UPDATE Payment SET Status = ? WHERE Payment_ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $payment_id);
        
        if ($stmt->execute()) {
            $success_message = "Payment status updated successfully!";
        } else {
            $error_message = "Error updating payment status: " . $conn->error;
        }
        $stmt->close();
    }
    
    // Handle new payment creation
    if (isset($_POST['create_payment'])) {
        $student_id = sanitize_input($_POST['student_id']);
        $amount = sanitize_input($_POST['amount']);
        $payment_method = sanitize_input($_POST['payment_method']);
        $purpose = sanitize_input($_POST['purpose']);
        $payment_date = date('Y-m-d');
        $status = "Pending";
        
        $sql = "INSERT INTO Payment (Student_ID, Amount, Payment_Date, Payment_Method, Purpose, Status) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("idssss", $student_id, $amount, $payment_date, $payment_method, $purpose, $status);
        
        if ($stmt->execute()) {
            $success_message = "Payment created successfully!";
        } else {
            $error_message = "Error creating payment: " . $conn->error;
        }
        $stmt->close();
    }
}

// Fetch all payments with student info
$sql = "SELECT p.*, s.Student_Name FROM Payment p 
        JOIN Student s ON p.Student_ID = s.Student_ID 
        ORDER BY p.Payment_Date DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$payments = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get all students for payment creation
$sql = "SELECT Student_ID, Student_Name FROM Student ORDER BY Student_Name";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$students = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Build content for template
ob_start();
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Manage Payments</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Payments</li>
    </ol>
    
    <?php if(isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <?php if(isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
    <!-- Create Payment Form (Admin) -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-plus me-1"></i>
            Create New Payment
        </div>
        <div class="card-body">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="student_id" class="form-label">Student:</label>
                        <select class="form-select" id="student_id" name="student_id" required>
                            <option value="">Select a student</option>
                            <?php foreach($students as $student): ?>
                                <option value="<?php echo $student['Student_ID']; ?>"><?php echo $student['Student_Name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="amount" class="form-label">Amount:</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0" required>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="payment_method" class="form-label">Payment Method:</label>
                        <select class="form-select" id="payment_method" name="payment_method" required>
                            <option value="Credit Card">Credit Card</option>
                            <option value="Debit Card">Debit Card</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Cash">Cash</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="purpose" class="form-label">Purpose:</label>
                        <input type="text" class="form-control" id="purpose" name="purpose" required>
                    </div>
                </div>
                <button type="submit" name="create_payment" class="btn btn-primary">Create Payment</button>
            </form>
        </div>
    </div>
    
    <!-- Payments List -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            All Payments
        </div>
        <div class="card-body">
            <?php if(count($payments) > 0): ?>
                <table id="paymentsTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Payment ID</th>
                            <th>Student</th>
                            <th>Amount</th>
                            <th>Payment Date</th>
                            <th>Payment Method</th>
                            <th>Purpose</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($payments as $payment): ?>
                            <tr>
                                <td><?php echo $payment['Payment_ID']; ?></td>
                                <td><?php echo $payment['Student_Name']; ?></td>
                                <td>$<?php echo number_format($payment['Amount'], 2); ?></td>
                                <td><?php echo date('M d, Y', strtotime($payment['Payment_Date'])); ?></td>
                                <td><?php echo $payment['Payment_Method']; ?></td>
                                <td><?php echo $payment['Purpose']; ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        switch($payment['Status']) {
                                            case 'Pending': echo 'warning'; break;
                                            case 'Completed': echo 'success'; break;
                                            case 'Failed': echo 'danger'; break;
                                            case 'Refunded': echo 'info'; break;
                                            default: echo 'secondary';
                                        }
                                    ?>">
                                        <?php echo $payment['Status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#updateModal<?php echo $payment['Payment_ID']; ?>">
                                        Update Status
                                    </button>

                                    <!-- Update Status Modal -->
                                    <div class="modal fade" id="updateModal<?php echo $payment['Payment_ID']; ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Update Payment Status</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="payment_id" value="<?php echo $payment['Payment_ID']; ?>">
                                                        
                                                        <div class="mb-3">
                                                            <label for="status" class="form-label">Status:</label>
                                                            <select class="form-select" id="status" name="status" required>
                                                                <option value="Pending" <?php echo ($payment['Status'] == 'Pending' ? 'selected' : ''); ?>>Pending</option>
                                                                <option value="Completed" <?php echo ($payment['Status'] == 'Completed' ? 'selected' : ''); ?>>Completed</option>
                                                                <option value="Failed" <?php echo ($payment['Status'] == 'Failed' ? 'selected' : ''); ?>>Failed</option>
                                                                <option value="Refunded" <?php echo ($payment['Status'] == 'Refunded' ? 'selected' : ''); ?>>Refunded</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" name="update_payment" class="btn btn-primary">Save changes</button>
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
                <div class="alert alert-info">No payments found.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Initialize DataTable
    $(document).ready(function() {
        $('#paymentsTable').DataTable();
    });
</script>

<?php
$content = ob_get_clean();
include '../layout.php';
?>