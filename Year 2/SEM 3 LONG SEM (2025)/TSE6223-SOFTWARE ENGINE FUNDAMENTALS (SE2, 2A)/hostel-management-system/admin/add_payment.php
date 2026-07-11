<?php
require_once '../db_connection.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] != "admin") {
    header("location: ../login.php");
    exit();
}

$page_title = "Add Payment - Admin Dashboard";

// Check if student ID is provided
if (!isset($_GET['student_id']) || empty($_GET['student_id'])) {
    $_SESSION['error_msg'] = "Student ID is required!";
    header("location: students.php");
    exit();
}

$student_id = $_GET['student_id'];

// Get student information
$sql = "SELECT * FROM Student WHERE Student_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error_msg'] = "Student not found!";
    header("location: students.php");
    exit();
}

$student = $result->fetch_assoc();

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize input data
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $payment_method = filter_input(INPUT_POST, 'payment_method', FILTER_SANITIZE_STRING);
    $purpose = filter_input(INPUT_POST, 'purpose', FILTER_SANITIZE_STRING);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    $payment_date = filter_input(INPUT_POST, 'payment_date', FILTER_SANITIZE_STRING);
    
    // Validation
    $errors = [];
    
    if (!$amount || $amount <= 0) {
        $errors[] = "Valid amount is required!";
    }
    
    if (empty($payment_method)) {
        $errors[] = "Payment method is required!";
    }
    
    if (empty($purpose)) {
        $errors[] = "Payment purpose is required!";
    }
    
    if (empty($status)) {
        $errors[] = "Payment status is required!";
    }
    
    if (empty($payment_date)) {
        $errors[] = "Payment date is required!";
    }
    
    // If no errors, insert the payment
    if (empty($errors)) {
        $sql = "INSERT INTO Payment (Student_ID, Amount, Payment_Date, Payment_Method, Purpose, Status) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("idssss", $student_id, $amount, $payment_date, $payment_method, $purpose, $status);
        
        if ($stmt->execute()) {
            $_SESSION['success_msg'] = "Payment added successfully!";
            header("location: view_student.php?id=" . $student_id);
            exit();
        } else {
            $errors[] = "Error adding payment: " . $conn->error;
        }
    }
}

// Build content for template
ob_start();
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Add Payment</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="students.php">Students</a></li>
        <li class="breadcrumb-item"><a href="view_student.php?id=<?= $student_id ?>">View Student</a></li>
        <li class="breadcrumb-item active">Add Payment</li>
    </ol>
    
    <div class="row">
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-money-bill me-1"></i>
                    Add Payment for <?= htmlspecialchars($student['Student_Name']) ?>
                </div>
                <div class="card-body">
                    <?php if (isset($errors) && !empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="add_payment.php?student_id=<?= $student_id ?>">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input class="form-control" id="amount" name="amount" type="number" step="0.01" min="0.01" required />
                                    <label for="amount">Amount ($)</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="payment_method" name="payment_method" required>
                                        <option value="">Select payment method</option>
                                        <option value="Credit Card">Credit Card</option>
                                        <option value="Debit Card">Debit Card</option>
                                        <option value="Bank Transfer">Bank Transfer</option>
                                        <option value="Cash">Cash</option>
                                    </select>
                                    <label for="payment_method">Payment Method</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input class="form-control" id="purpose" name="purpose" type="text" required />
                                    <label for="purpose">Payment Purpose</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="">Select status</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Completed" selected>Completed</option>
                                        <option value="Failed">Failed</option>
                                        <option value="Refunded">Refunded</option>
                                    </select>
                                    <label for="status">Payment Status</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input class="form-control" id="payment_date" name="payment_date" type="date" value="<?= date('Y-m-d') ?>" required />
                                    <label for="payment_date">Payment Date</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4 mb-0">
                            <div class="d-flex align-items-center justify-content-between">
                                <a href="view_student.php?id=<?= $student_id ?>" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Add Payment</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // You can add any necessary client-side validation here
    });
</script>

<?php
$content = ob_get_clean();
include '../layout.php';
?>