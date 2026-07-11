<?php
require_once 'includes/config.php';

// Redirect if not logged in
if (!is_logged_in()) {
    redirect_with_message('login.php', 'Please log in to apply for leave.', 'error');
}

$user = get_logged_in_user();
$message = '';

// Get leave types
$leave_types = get_all_leave_types();

// Filter to show only the 6 specific leave types
$allowed_leave_types = [
    'Annual Leave' => 14,
    'Emergency Leave' => 3,
    'Maternity Leave' => 90,
    'Paternity Leave' => 150,
    'Sick Leave' => 14,
    'Unpaid Leave' => 30
];

$filtered_leave_types = [];
foreach ($leave_types as $type) {
    if (array_key_exists($type['name'], $allowed_leave_types)) {
        $type['max_days'] = $allowed_leave_types[$type['name']];
        $filtered_leave_types[] = $type;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leave_type_id = intval($_POST['leave_type_id'] ?? 0);
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $reason = trim($_POST['reason'] ?? '');
    $total_days = intval($_POST['total_days'] ?? 0);

    // Basic validation
    if (!$leave_type_id || !$start_date || !$end_date || !$reason) {
        $message = '<div class="error-message">All fields are required.</div>';
    } elseif (strtotime($end_date) < strtotime($start_date)) {
        $message = '<div class="error-message">End date cannot be before start date.</div>';
    } else {
        // Check if dates are in the past
        $today = date('Y-m-d');
        if ($start_date < $today) {
            $message = '<div class="error-message">Cannot apply for leave on past dates. Please select today or a future date.</div>';
        } elseif ($end_date < $today) {
            $message = '<div class="error-message">Cannot apply for leave on past dates. Please select today or a future date.</div>';
        } else {
            // Validate dates
            if ($start_date > $end_date) {
                $message = '<div class="error-message">End date must be after start date.</div>';
            } else {
                // Calculate total days server-side as well for validation
                $start = new DateTime($start_date);
                $end = new DateTime($end_date);
                $interval = $start->diff($end);
                $calculated_days = $interval->days + 1; // Include both start and end dates
                
                // Use the calculated days if the submitted total_days is 0 or incorrect
                if ($total_days == 0 || $total_days != $calculated_days) {
                    $total_days = $calculated_days;
                }

                // Get leave type details
                $stmt = $conn->prepare("SELECT * FROM leave_types WHERE id = ?");
                $stmt->bind_param("i", $leave_type_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $leave_type = null;
                foreach ($filtered_leave_types as $type) {
                    if ($type['id'] == $leave_type_id) {
                        $leave_type = $type;
                        break;
                    }
                }

                if ($total_days > $leave_type['max_days']) {
                    $message = '<div class="error-message">Total days (' . $total_days . ') exceeds maximum allowed days (' . $leave_type['max_days'] . ') for ' . $leave_type['name'] . '.</div>';
                } else {
                    // Insert into leave_applications
                    $stmt = $conn->prepare("INSERT INTO leave_applications (user_id, leave_type_id, start_date, end_date, total_days, reason, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())");
                    $stmt->bind_param('iissis', $user['id'], $leave_type_id, $start_date, $end_date, $total_days, $reason);
                    if ($stmt->execute()) {
                        $message = '<div class="success-message">Leave application submitted successfully! Total days: ' . $total_days . '</div>';
                    } else {
                        $message = '<div class="error-message">Failed to submit leave application. Please try again.</div>';
                    }
                    $stmt->close();
                }
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $request_id = intval($_POST['request_id']);
    $user_id = $_SESSION['user_id'];
    // Check status before deleting
    $stmt = $conn->prepare("SELECT status FROM leave_applications WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $request_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if (!$row) {
        echo json_encode(['success' => false, 'message' => 'Request not found.']);
        exit;
    }
    if ($row['status'] !== 'pending') {
        echo json_encode(['success' => false, 'message' => 'Cannot delete approved or rejected requests.']);
        exit;
    }
    // Only delete if pending
    $stmt = $conn->prepare("DELETE FROM leave_applications WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $request_id, $user_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Leave request deleted.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete leave request.']);
    }
    exit;
}

// After the leave application form, show the user's leave requests
$sql = "SELECT la.*, lt.name as leave_type_name FROM leave_applications la JOIN leave_types lt ON la.leave_type_id = lt.id WHERE la.user_id = ? ORDER BY la.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$user_requests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Leave - LeaveTrack</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-container {
            max-width: 500px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
        }
        .form-title {
            text-align: center;
            margin-bottom: 24px;
        }
        .success-message {
            color: var(--success, green);
            background: #e6f9e6;
            border: 1px solid #b2e6b2;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 16px;
        }
        .error-message {
            color: var(--error, red);
            background: #ffeaea;
            border: 1px solid #ffb2b2;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 16px;
        }
        .days-info {
            margin-top: 8px;
            font-size: 0.9em;
            color: #666;
        }
        .days-warning {
            color: var(--error, red);
        }
        .leave-type-info {
            margin-top: 4px;
            font-size: 0.85em;
            color: #666;
            font-style: italic;
        }
        .leave-duration-options {
            display: flex;
            gap: 32px;
            align-items: center;
            margin-top: 6px;
        }
        .leave-duration-options label {
            display: flex;
            align-items: center;
            font-size: 1rem;
            cursor: pointer;
            gap: 6px;
        }
        .leave-duration-options input[type="radio"] {
            accent-color: #2563eb; 
            width: 18px;
            height: 18px;
        }
    </style>
</head>
<body>
<?php include 'includes/taskbar.php'; ?>

    <div class="form-container">
        <h2 class="form-title">Apply for Leave</h2>
        <?php if (!empty($message)): ?>
        <script>
            showToast(
                <?php echo json_encode(strip_tags($message)); ?>,
                <?php echo (strpos($message, 'error-message') !== false) ? "'#e74c3c'" : "'#27ae60'"; ?>
            );
        </script>
        <?php endif; ?>
        <form method="POST" action="" id="leaveForm">
            <!-- Leave Type -->
            <div class="form-group">
                <label for="leave_type_id">Leave Type</label>
                <select name="leave_type_id" id="leave_type_id" required>
                    <option value="">-- Select Leave Type --</option>
                    <?php foreach ($filtered_leave_types as $type): ?>
                        <option value="<?php echo htmlspecialchars($type['id']); ?>" 
                                data-max-days="<?php echo htmlspecialchars($type['max_days']); ?>">
                            <?php echo htmlspecialchars($type['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div id="maxDaysInfo" class="leave-type-info"></div>
            </div>

            <!-- Leave Duration -->
            <div class="form-group">
                <label>Leave Duration</label>
                <div class="leave-duration-options">
                    <label>
                        <input type="radio" name="leave_duration" value="single" checked> One Day
                    </label>
                    <label>
                        <input type="radio" name="leave_duration" value="multiple"> Multiple Days
                    </label>
                </div>
            </div>

            <!-- date selection -->
            <div id="single-day-group" class="form-group">
                <label for="single_day">Day</label>
                <input type="date" id="single_day" name="single_day">
            </div>
            <div id="multi-day-group" class="form-row" style="display: none;">
                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" id="start_date" name="start_date">
                </div>
                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date">
                </div>
            </div>
            <!-- Hidden fields for single day mode -->
            <input type="hidden" id="hidden_start_date">
            <input type="hidden" id="hidden_end_date">
            <div id="daysInfo" class="days-info"></div>
            <input type="hidden" name="total_days" id="total_days" value="0">
            <div class="form-group">
                <label for="reason">Reason</label>
                <textarea name="reason" id="reason" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary" id="submitBtn">Submit Application</button>
            <a href="index.php" class="btn btn-secondary" style="margin-left: 10px;">Back to Home</a>
        </form>
    </div>

    <div class="form-container" style="margin-top: 40px;">
        <h2 class="form-title">Your Leave Requests</h2>
        <?php if (empty($user_requests)): ?>
            <p style="color: #888;">No leave requests found.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Dates</th>
                        <th>Days</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($user_requests as $req): ?>
                    <tr id="row-<?php echo $req['id']; ?>">
                        <td><?php echo htmlspecialchars($req['leave_type_name']); ?></td>
                        <td><?php echo htmlspecialchars($req['start_date']); ?> to <?php echo htmlspecialchars($req['end_date']); ?></td>
                        <td><?php echo htmlspecialchars($req['total_days']); ?></td>
                        <td><?php echo ucfirst($req['status']); ?></td>
                        <td>
                            <?php if ($req['status'] === 'pending'): ?>
                                <button class="btn btn-danger btn-sm delete-btn" data-request-id="<?php echo $req['id']; ?>">Remove</button>
                            <?php else: ?>
                                <span style="color: #888;">Locked</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <script>
    function removeLeaveRequest(requestId) {
        if (!confirm('Are you sure you want to remove this leave request?')) return;
        fetch('apply_leave.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=delete&request_id=${encodeURIComponent(requestId)}`
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.success) location.reload();
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('leaveForm');
        const leaveTypeSelect = document.getElementById('leave_type_id');
        const singleDayInput = document.getElementById('single_day');
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const daysInfo = document.getElementById('daysInfo');
        const maxDaysInfo = document.getElementById('maxDaysInfo');
        const totalDaysInput = document.getElementById('total_days');
        const submitBtn = document.getElementById('submitBtn');

        // Set minimum date to today for all date inputs
        const today = new Date().toISOString().split('T')[0];
        singleDayInput.min = today;
        startDateInput.min = today;
        endDateInput.min = today;

        // Add validation for past dates
        function validateDateInput(input) {
            const selectedDate = new Date(input.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (selectedDate < today) {
                showToast('Cannot select dates in the past. Please choose today or a future date.', '#e74c3c');
                input.value = '';
                return false;
            }
            return true;
        }

        // Add event listeners for date validation
        singleDayInput.addEventListener('change', function() {
            if (validateDateInput(this)) {
                // For single day mode, set both start and end dates to visible fields
                startDateInput.value = this.value;
                endDateInput.value = this.value;
                
                // Set total days to 1 for single day
                totalDaysInput.value = '1';
                
                // Enable submit button for single day
                submitBtn.disabled = false;
            }
        });
        
        startDateInput.addEventListener('change', function() {
            if (validateDateInput(this)) {
                calculateDays();
            }
        });
        
        endDateInput.addEventListener('change', function() {
            if (validateDateInput(this)) {
                calculateDays();
            }
        });

        function calculateDays() {
            if (startDateInput.value && endDateInput.value) {
                const start = new Date(startDateInput.value);
                const end = new Date(endDateInput.value);
                
                // Ensure we're working with dates at midnight to avoid timezone issues
                start.setHours(0, 0, 0, 0);
                end.setHours(0, 0, 0, 0);
                
                // Calculate the difference in days
                const diffTime = end.getTime() - start.getTime();
                const totalDays = Math.floor(diffTime / (1000 * 60 * 60 * 24)) + 1;
                
                // Get selected leave type max days
                const selectedOption = leaveTypeSelect.options[leaveTypeSelect.selectedIndex];
                const maxDays = selectedOption.dataset.maxDays ? parseInt(selectedOption.dataset.maxDays) : 0;
                
                // Update the hidden input
                totalDaysInput.value = totalDays;
                
                if (totalDays > maxDays && maxDays > 0) {
                    daysInfo.innerHTML = `Total days: <span class="days-warning">${totalDays} days (exceeds maximum ${maxDays} days)</span>`;
                    submitBtn.disabled = true;
                } else if (totalDays <= 0) {
                    daysInfo.innerHTML = `Total days: <span class="days-warning">Invalid date range</span>`;
                    submitBtn.disabled = true;
                } else {
                    daysInfo.innerHTML = `Total days: ${totalDays} days`;
                    submitBtn.disabled = false;
                }
            } else {
                daysInfo.textContent = '';
                totalDaysInput.value = '0';
                submitBtn.disabled = true;
            }
        }

        function updateMaxDaysInfo() {
            const selectedOption = leaveTypeSelect.options[leaveTypeSelect.selectedIndex];
            if (selectedOption.value) {
                const maxDays = selectedOption.dataset.maxDays;
                maxDaysInfo.textContent = `Maximum allowed: ${maxDays} days`;
            } else {
                maxDaysInfo.textContent = '';
            }
            calculateDays();
        }

        leaveTypeSelect.addEventListener('change', updateMaxDaysInfo);

        form.addEventListener('submit', function(e) {
            const duration = document.querySelector('input[name="leave_duration"]:checked').value;
            
            if (duration === 'single') {
                const day = document.getElementById('single_day').value;
                if (!day) {
                    e.preventDefault();
                    showToast('Please select a date for single day leave.', '#e74c3c');
                    return;
                }
                
                // Set visible fields for single day
                startDateInput.value = day;
                endDateInput.value = day;
                totalDaysInput.value = '1';
                
                // Validate the selected date
                const selectedDate = new Date(day);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                if (selectedDate < today) {
                    e.preventDefault();
                    showToast('Cannot select dates in the past. Please choose today or a future date.', '#e74c3c');
                    return;
                }
            } else {
                // Multiple days validation
                if (startDateInput.value && endDateInput.value) {
                    const start = new Date(startDateInput.value);
                    const end = new Date(endDateInput.value);
                    
                    if (end < start) {
                        e.preventDefault();
                        showToast('End date cannot be before start date', '#e74c3c');
                        return;
                    }

                    const selectedOption = leaveTypeSelect.options[leaveTypeSelect.selectedIndex];
                    const maxDays = selectedOption.dataset.maxDays ? parseInt(selectedOption.dataset.maxDays) : 0;
                    const totalDays = parseInt(totalDaysInput.value);

                    if (totalDays > maxDays) {
                        e.preventDefault();
                        showToast(`Total days (${totalDays}) exceeds maximum allowed days (${maxDays}) for this leave type.`, '#e74c3c');
                        return;
                    }
                }
            }
        });

        document.querySelectorAll('input[name="leave_duration"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                if (this.value === 'single') {
                    document.getElementById('single-day-group').style.display = '';
                    document.getElementById('multi-day-group').style.display = 'none';
                    document.getElementById('daysInfo').style.display = 'none';
                    document.getElementById('daysInfo').textContent = '';
                    // Clear multiple day fields when switching to single day
                    startDateInput.value = '';
                    endDateInput.value = '';
                    totalDaysInput.value = '0';
                    submitBtn.disabled = true;
                } else {
                    document.getElementById('single-day-group').style.display = 'none';
                    document.getElementById('multi-day-group').style.display = '';
                    document.getElementById('daysInfo').style.display = '';
                    // Clear single day field when switching to multiple days
                    singleDayInput.value = '';
                    totalDaysInput.value = '0';
                    submitBtn.disabled = true;
                }
            });
        });

        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const requestId = this.dataset.requestId;
                if (!confirm('Are you sure you want to delete this leave request?')) return;

                fetch('apply_leave.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=delete&request_id=${encodeURIComponent(requestId)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove the row from the table/list
                        const row = document.getElementById('row-' + requestId);
                        if (row) row.remove();

                        // Show toast notification
                        showToast(data.message, '#27ae60');
                    } else {
                        alert(data.message);
                    }
                });
            });
        });
    });

    function showToast(message, color = '#27ae60') {
        // Remove any existing toast
        let oldToast = document.getElementById('custom-toast');
        if (oldToast) oldToast.remove();

        let toast = document.createElement('div');
        toast.id = 'custom-toast';
        toast.innerText = message;
        toast.style.position = 'fixed';
        toast.style.top = '100px';
        toast.style.left = '50%';
        toast.style.transform = 'translateX(-50%)';
        toast.style.background = color;
        toast.style.color = 'white';
        toast.style.padding = '24px 32px';
        toast.style.borderRadius = '12px';
        toast.style.boxShadow = '0 2px 16px rgba(0,0,0,0.15)';
        toast.style.zIndex = 9999;
        toast.style.fontSize = '22px';
        toast.style.fontWeight = 'bold';
        document.body.appendChild(toast);

        // Remove toast after 3.5 seconds
        let removeToast = () => { if (toast) toast.remove(); };
        let timeout = setTimeout(removeToast, 3500);

        // Remove toast on any user interaction
        ['click', 'keydown', 'submit'].forEach(event => {
            document.addEventListener(event, function handler() {
                removeToast();
                clearTimeout(timeout);
                document.removeEventListener(event, handler, true);
            }, true);
        });
    }
    </script>
</body>
</html> 