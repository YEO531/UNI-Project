<?php
require_once 'includes/config.php';

// Check if user is logged in
check_access();

// Get current user data
$user = get_logged_in_user();

// Fetch all departments from the database
$departments = get_all_departments();

if (!$user) {
    redirect_with_message('login.php', 'Please login to access this page', 'warning');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Sanitize input data
        $first_name = sanitize_input($_POST['first_name']);
        $last_name = sanitize_input($_POST['last_name']);
        $email = sanitize_input($_POST['email']);
        $department = sanitize_input($_POST['department']);
        $position = sanitize_input($_POST['position']);

        // Prepare update data
        $updateData = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'department' => $department,
            'position' => $position
        ];

        // Handle password update if any password field is filled
        if (
            !empty($_POST['current_password']) ||
            !empty($_POST['new_password']) ||
            !empty($_POST['confirm_password'])
        ) {
            // All fields must be filled
            if (
                empty($_POST['current_password']) ||
                empty($_POST['new_password']) ||
                empty($_POST['confirm_password'])
            ) {
                alert('To change your password, please fill in all password fields.');
            }

            // Check if current password matches database
            if (!password_verify($_POST['current_password'], $user['password'])) {
                alert('Current password is incorrect');
            }

            // Check if new passwords match
            if ($_POST['new_password'] !== $_POST['confirm_password']) {
                alert('New passwords do not match');
            }

            // All checks passed, update password
            $updateData['password'] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        }

        // Update user profile
        $stmt = $conn->prepare("UPDATE users SET 
            first_name = ?, 
            last_name = ?, 
            email = ?, 
            department = ?, 
            position = ?" . 
            (!empty($updateData['password']) ? ", password = ?" : "") . 
            " WHERE id = ?");

        $params = [
            $updateData['first_name'],
            $updateData['last_name'],
            $updateData['email'],
            $updateData['department'],
            $updateData['position']
        ];

        if (!empty($updateData['password'])) {
            $params[] = $updateData['password'];
        }
        $params[] = $user['id'];

        $types = str_repeat('s', count($params) - 1) . 'i';
        $stmt->bind_param($types, ...$params);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to update profile');
        }

        // Log the activity
        log_activity($user['id'], 'Profile Update', 'User updated their profile information');

        // Update session data
        $_SESSION['user_name'] = $first_name . ' ' . $last_name;
        $_SESSION['user_position'] = $position;

        redirect_with_message('profile.php', 'Profile updated successfully');
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - LeaveTrack</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(45deg, var(--primary), var(--success));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 36px;
            font-weight: 600;
        }

        .profile-info h2 {
            margin-bottom: 5px;
            color: var(--dark);
        }

        .profile-info p {
            color: var(--gray);
        }

        .profile-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .btn-group {
            grid-column: 1 / -1;
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        /* Glow effect for outline button */
        .btn-outline {
            box-shadow: 0 2px 8px rgba(67, 97, 238, 0.15), 0 0 0 0px var(--primary);
            transition: all 0.3s ease;
        }

        .btn-outline:hover {
            box-shadow: 0 4px 16px rgba(67, 97, 238, 0.25), 0 0 24px 6px rgba(67, 97, 238, 0.4);
            transform: translateY(-2px);
        }

        .password-section {
            grid-column: 1 / -1;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .password-section h3 {
            margin-bottom: 20px;
            color: var(--dark);
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .alert-success {
            background-color: var(--success-light);
            color: var(--success);
            border: 1px solid var(--success);
        }

        .alert-error {
            background-color: var(--error-light);
            color: var(--error);
            border: 1px solid var(--error);
        }
    </style>
</head>
<body>
<?php include 'includes/taskbar.php'; ?>

    <div class="container">
        <header>
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="logo-text">LeaveTrack</div>
            </div>
            <div class="user-profile">
            </div>
        </header>

        <div class="profile-container">
            <div class="profile-header">
                <div class="profile-avatar" id="profileAvatar"><?php echo substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1); ?></div>
                <div class="profile-info">
                    <h2 id="profileName"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
                    <p id="profileRole"><?php echo htmlspecialchars($user['position']); ?></p>
                </div>
            </div>

            <form method="POST" action="profile.php">
                <div class="profile-form">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="department">Department</label>
                        <select id="department" name="department" required>
                            <option value="">-- Select Department --</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo htmlspecialchars($dept['name']); ?>"
                                    <?php if ($user['department'] == $dept['name']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($dept['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="position">Position</label>
                        <input type="text" id="position" name="position" value="<?php echo htmlspecialchars($user['position']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="employee_id">Employee ID</label>
                        <input type="text" id="employee_id" value="<?php echo htmlspecialchars($user['employee_id']); ?>" readonly>
                    </div>

                    <div class="password-section">
                        <h3>Change Password</h3>
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password">
                        </div>

                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password">
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password">
                        </div>
                    </div>

                    <div class="btn-group">
                        <button type="button" class="btn btn-outline" onclick="window.location.href='index.php'">Back to Dashboard</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
    <script>
        window.onload = function() {
            let msg = <?php echo json_encode($_SESSION['message']); ?>;
            let toast = document.createElement('div');
            toast.innerText = msg;
            toast.style.position = 'fixed';
            toast.style.top = '100px';
            toast.style.left = '50%';
            toast.style.transform = 'translateX(-50%)';
            toast.style.background = '#e74c3c';
            toast.style.color = 'white';
            toast.style.padding = '16px 24px';
            toast.style.borderRadius = '8px';
            toast.style.boxShadow = '0 2px 8px rgba(0,0,0,0.2)';
            toast.style.zIndex = 9999;
            toast.style.fontSize = '18px';
            toast.style.fontWeight = 'bold';
            document.body.appendChild(toast);
            setTimeout(() => { toast.remove(); }, 3500);
        };
    </script>
    <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
    <?php endif; ?>
</body>
</html> 