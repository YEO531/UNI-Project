<?php
require_once 'includes/config.php';

// Check if user just registered
$registration_success = false;
if (isset($_GET['registered']) && $_GET['registered'] == '1') {
    $registration_success = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LeaveTrack</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            background: linear-gradient(135deg, #e0e7ff 0%, #f5f7fa 100%);
            font-family: 'Segoe UI', 'Arial', sans-serif;
            min-height: 100vh;
        }

        .auth-container {
            max-width: 400px;
            margin: 100px auto;
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
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
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

        .auth-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .form-group label {
            font-size: 1rem;
            color: #374151;
            font-weight: 500;
        }
        .form-group input {
            padding: 12px 14px;
            border: 1.5px solid #d1d5db;
            border-radius: 10px;
            font-size: 1rem;
            background: #f9fafb;
            transition: border 0.2s, box-shadow 0.2s;
            outline: none;
            box-shadow: none;
        }
        .form-group input:focus {
            border: 1.5px solid #2563eb;
            box-shadow: 0 0 0 2px #2563eb22;
            background: #fff;
        }
        .auth-links {
            text-align: center;
            margin-top: 20px;
        }
        .auth-links a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        .auth-links a:hover {
            text-decoration: underline;
            color: #1e40af;
        }
        .success-message {
            background: #e0f7e9;
            color: #218a5a;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #b6e7d6;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .floating-notification {
            position: fixed;
            top: 30px;
            right: 30px;
            min-width: 260px;
            max-width: 320px;
            padding: 18px 22px;
            border-radius: 12px;
            box-shadow: 0 4px 24px #0001;
            display: flex;
            align-items: flex-start;
            gap: 16px;
            opacity: 0;
            pointer-events: none;
            z-index: 9999;
            transition: opacity 0.3s, transform 0.3s;
            transform: translateY(-20px);
        }
        .floating-notification.show {
            opacity: 1;
            pointer-events: auto;
            transform: translateY(0);
        }
        .floating-notification.success {
            background: #e0f7e9;
            color: #218a5a;
            border: 1px solid #b6e7d6;
        }
        .floating-notification.error {
            background: #fde8e8;
            color: #b91c1c;
            border: 1px solid #f5c2c7;
        }
        .notification-icon {
            font-size: 1.6rem;
            margin-right: 8px;
            margin-top: 2px;
        }
        @media (max-width: 500px) {
            .auth-container {
                margin: 30px 8px;
                padding: 18px 8px;
            }
            .floating-notification {
                right: 10px;
                left: 10px;
                top: 10px;
                max-width: unset;
            }
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
            <h2>Welcome Back</h2>
            <p>Please login to your account</p>
        </div>

        <?php if ($registration_success): ?>
        <div class="success-message" style="background: #d4edda; color: #155724; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
            <i class="fas fa-check-circle"></i>
            Registration successful! Please login with your credentials.
        </div>
        <?php endif; ?>

        <form id="loginForm" class="auth-form">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" required>
            </div>

            <button type="submit" class="btn btn-primary">Login</button>
        </form>

        <div class="auth-links">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>

    <div class="floating-notification success" id="successNotification">
        <div class="notification-icon">
            <i class="fas fa-check"></i>
        </div>
        <div>
            <h4>Success!</h4>
            <p>Login successful</p>
        </div>
    </div>

    <div class="floating-notification error" id="errorNotification">
        <div class="notification-icon">
            <i class="fas fa-exclamation"></i>
        </div>
        <div>
            <h4>Error!</h4>
            <p>Invalid email or password</p>
        </div>
    </div>

    <script>
        const API_BASE_URL = 'api';
        const ENDPOINTS = {
            auth: `${API_BASE_URL}/auth.php`
        };

        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            try {
                const response = await fetch(ENDPOINTS.auth, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'login',
                        email: email,
                        password: password
                    })
                });

                const result = await response.json();

                if (result.status === 'success') {
                    // Store user data in localStorage
                    localStorage.setItem('user', JSON.stringify(result.user));
                    
                    // Show success notification
                    showNotification('success', 'Login successful');
                    
                    // Redirect to dashboard
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1000);
                } else {
                    showNotification('error', result.message);
                }
            } catch (error) {
                console.error('Login error:', error);
                showNotification('error', 'Failed to login');
            }
        });

        function showNotification(type, message) {
            const notification = document.getElementById(`${type}Notification`);
            const messageElement = notification.querySelector('p');
            
            if (message) {
                messageElement.textContent = message;
            }
            
            notification.classList.add('show');
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }
    </script>
</body>
</html> 