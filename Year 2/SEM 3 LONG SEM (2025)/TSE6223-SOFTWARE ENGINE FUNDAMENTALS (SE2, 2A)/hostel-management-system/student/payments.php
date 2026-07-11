<?php
require_once '../db_connection.php';
//session_start();

// Check if user is logged in as student
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user_role"]) || $_SESSION["user_role"] != "student") {
    header("location: ../login.php");
    exit();
}

// Get user data
$user_role = $_SESSION["user_role"];
$user_id = $_SESSION["user_id"];
$page_title = "My Payments - Hostel Management System";

// Make a new payment request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['make_payment'])) {
    $amount = sanitize_input($_POST['amount']);
    $payment_method = sanitize_input($_POST['payment_method']);
    $purpose = sanitize_input($_POST['purpose']);
    $payment_date = date('Y-m-d');
    
    $sql = "INSERT INTO Payment (Student_ID, Amount, Payment_Date, Payment_Method, Purpose, Status) 
            VALUES (?, ?, ?, ?, ?, 'Pending')";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("idsss", $user_id, $amount, $payment_date, $payment_method, $purpose);
    
    if ($stmt->execute()) {
        $success_message = "Payment submitted successfully!";
    } else {
        $error_message = "Error submitting payment: " . $conn->error;
    }
    $stmt->close();
}

// Fetch student's payments
$sql = "SELECT * FROM Payment WHERE Student_ID = ? ORDER BY Payment_Date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$payments = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Build content for template
ob_start();
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">My Payments</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Payments</li>
    </ol>
    
    <?php if(isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <?php if(isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
    <!-- Make Payment Form (Student) -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-credit-card me-1"></i>
            Make a Payment
        </div>
        <div class="card-body">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="amount" class="form-label">Amount:</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="payment_method" class="form-label">Payment Method:</label>
                        <select class="form-select" id="payment_method" name="payment_method" required>
                            <option value="Credit Card">Credit Card</option>
                            <option value="Debit Card">Debit Card</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Cash">Cash</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="purpose" class="form-label">Purpose:</label>
                    <input type="text" class="form-control" id="purpose" name="purpose" required>
                </div>
                <button type="submit" name="make_payment" class="btn btn-primary">Submit Payment</button>
            </form>
        </div>
    </div>
    
    <!-- Payments List -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            My Payment History
        </div>
        <div class="card-body">
            <?php if(count($payments) > 0): ?>
                <table id="paymentsTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Payment ID</th>
                            <th>Amount</th>
                            <th>Payment Date</th>
                            <th>Payment Method</th>
                            <th>Purpose</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($payments as $payment): ?>
                            <tr>
                                <td><?php echo $payment['Payment_ID']; ?></td>
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