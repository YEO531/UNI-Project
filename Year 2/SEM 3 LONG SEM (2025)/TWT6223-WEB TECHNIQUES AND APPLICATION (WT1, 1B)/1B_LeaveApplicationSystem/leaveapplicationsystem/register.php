<?php
require_once 'includes/config.php';

// Redirect if already logged in
if (is_logged_in()) {
    redirect_with_message('index.php', 'You are already logged in', 'info');
}
// Get all departments
$departments = get_all_departments();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - LeaveTrack</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            background: linear-gradient(135deg, #e0e7ff 0%, #f5f7fa 100%);
            font-family: 'Segoe UI', 'Arial', sans-serif;
            min-height: 100vh;
        }

        .auth-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background: rgba(255, 255, 255, 0.75);
            border-radius: 20px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .auth-header .logo {
            justify-content: center;
            margin-bottom: 20px;
        }

        .auth-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .auth-links {
            text-align: center;
            margin-top: 20px;
        }

        .auth-links a {
            color: var(--primary);
            text-decoration: none;
        }

        .auth-links a:hover {
            text-decoration: underline;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .error-message {
            color: var(--error);
            font-size: 0.9em;
            margin-top: 5px;
        }

        select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 16px;
            background-color: white;
            transition: border-color 0.3s ease;
        }

        select:focus {
            outline: none;
            border-color: var(--primary);
        }

        select option {
            padding: 8px;
        }

        .auth-header h2 {
            font-size: 2rem;
            font-weight: 600;
            color: #2563eb;
            margin-bottom: 8px;
        }
        .auth-header p {
            color: #6b7280;
            font-size: 1rem;
        }
        .form-group label {
            font-size: 1rem;
            color: #374151;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="logo-text">LeaveTrack</div>
            </div>
            <h2>Create Account</h2>
            <p>Please fill in your details to register</p>
        </div>

        <?php display_message(); ?>

        <form id="registerForm" class="auth-form" method="POST" action="api/auth.php">
            <input type="hidden" name="action" value="register">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
            </div>

            <div class="form-group">
                <label for="employee_id">Employee ID</label>
                <input type="text" id="employee_id" name="employee_id" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="department">Department</label>
                    <select id="department" name="department" required>
                        <option value="">Select Department</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo htmlspecialchars($dept['name']); ?>">
                                <?php echo htmlspecialchars($dept['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="position">Position</label>
                    <input type="text" id="position" name="position" required>
                </div>
            </div>

            <div class="form-group">
                <label for="user_type">Register as</label>
                <select id="user_type" name="user_type" required>
                    <option value="">Select user type</option>
                    <option value="employee">Employee</option>
                    <option value="admin">Administrator</option>
                </select>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <button type="submit" class="btn btn-primary">Register</button>
        </form>

        <div class="auth-links">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>

    <script>
        document.getElementById('registerForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const userType = document.getElementById('user_type').value;
            const department = document.getElementById('department').value;

            if (password !== confirmPassword) {
                alert('Passwords do not match');
                return;
            }

            if (!userType) {
                alert('Please select a user type');
                return;
            }

            if (!department) {
                alert('Please select a department');
                return;
            }

            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch('api/auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.status === 'success') {
                    window.location.href = 'login.php?registered=1';
                } else {
                    alert(result.message || 'Registration failed');
                }
            } catch (error) {
                console.error('Registration error:', error);
            }
        });
    </script>
</body>
</html> 