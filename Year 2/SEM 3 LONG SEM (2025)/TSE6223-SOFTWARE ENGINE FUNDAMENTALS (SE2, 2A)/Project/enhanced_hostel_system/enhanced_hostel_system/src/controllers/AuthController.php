<?php
/**
 * Auth Controller
 * 
 * Handles authentication-related operations
 */
class AuthController extends Controller {
    /**
     * Display login form
     */
    public function login() {
        // If already logged in, redirect to dashboard
        if ($this->isLoggedIn()) {
            Util::redirect('/dashboard');
        }
        
        $this->render('auth/login');
    }
    
    /**
     * Process login form
     */
    public function processLogin() {
        // Verify CSRF token
        $this->requireCsrfToken('/login');
        
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']) && $_POST['remember'] === 'on';
        
        // Validate input
        $errors = $this->validate(
            ['email' => $email, 'password' => $password],
            ['email' => 'required|email', 'password' => 'required']
        );
        
        if (!empty($errors)) {
            Util::setFlash('error', 'Please correct the errors in the form');
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = ['email' => $email];
            Util::redirect('/login');
            return;
        }
        
        // Attempt login
        $result = $this->auth->login($email, $password, $remember);
        
        if (is_array($result) && isset($result['error'])) {
            Util::setFlash('error', $result['message']);
            $_SESSION['form_data'] = ['email' => $email];
            Util::redirect('/login');
            return;
        }
        
        if ($result) {
            // Log activity
            $this->logActivity('login_success');
            
            // Redirect based on role
            if ($this->hasRole('admin')) {
                Util::redirect('/admin/dashboard');
            } else {
                Util::redirect('/dashboard');
            }
        } else {
            Util::setFlash('error', 'Invalid email or password');
            $_SESSION['form_data'] = ['email' => $email];
            Util::redirect('/login');
        }
    }
    
    /**
     * Display registration form
     */
    public function register() {
        // If already logged in, redirect to dashboard
        if ($this->isLoggedIn()) {
            Util::redirect('/dashboard');
        }
        
        $this->render('auth/register');
    }
    
    /**
     * Process registration form
     */
    public function processRegister() {
        // Verify CSRF token
        $this->requireCsrfToken('/register');
        
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate input
        $errors = $this->validate(
            [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => $password,
                'confirm_password' => $confirmPassword
            ],
            [
                'name' => 'required|max:180',
                'email' => 'required|email|max:180',
                'phone' => 'max:14',
                'password' => 'required|min:8',
                'confirm_password' => 'required'
            ]
        );
        
        // Check if passwords match
        if ($password !== $confirmPassword) {
            $errors['confirm_password'] = 'Passwords do not match';
        }
        
        if (!empty($errors)) {
            Util::setFlash('error', 'Please correct the errors in the form');
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone
            ];
            Util::redirect('/register');
            return;
        }
        
        // Register user
        $result = $this->auth->register([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
            'role' => 'student' // Default role
        ]);
        
        if (is_array($result) && isset($result['error'])) {
            Util::setFlash('error', $result['message']);
            $_SESSION['form_data'] = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone
            ];
            Util::redirect('/register');
            return;
        }
        
        if ($result) {
            // Log activity
            $this->logActivity('user_registered');
            
            Util::setFlash('success', 'Registration successful! You can now login.');
            Util::redirect('/login');
        } else {
            Util::setFlash('error', 'Registration failed. Please try again.');
            $_SESSION['form_data'] = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone
            ];
            Util::redirect('/register');
        }
    }
    
    /**
     * Process logout
     */
    public function logout() {
        if ($this->isLoggedIn()) {
            $this->logActivity('logout');
            $this->auth->logout();
        }
        
        Util::redirect('/login');
    }
    
    /**
     * Display forgot password form
     */
    public function forgotPassword() {
        // If already logged in, redirect to dashboard
        if ($this->isLoggedIn()) {
            Util::redirect('/dashboard');
        }
        
        $this->render('auth/forgot_password');
    }
    
    /**
     * Process forgot password form
     */
    public function processForgotPassword() {
        // Verify CSRF token
        $this->requireCsrfToken('/forgot-password');
        
        $email = $_POST['email'] ?? '';
        
        // Validate input
        $errors = $this->validate(
            ['email' => $email],
            ['email' => 'required|email']
        );
        
        if (!empty($errors)) {
            Util::setFlash('error', 'Please enter a valid email address');
            $_SESSION['form_errors'] = $errors;
            Util::redirect('/forgot-password');
            return;
        }
        
        // Request password reset
        $token = $this->auth->requestPasswordReset($email);
        
        // Always show success message to prevent email enumeration
        Util::setFlash('success', 'If your email exists in our system, you will receive a password reset link shortly.');
        
        // In a real application, send email with reset link
        // For demo purposes, we'll store the token in session
        if ($token) {
            $_SESSION['demo_reset_token'] = $token;
            $_SESSION['demo_reset_email'] = $email;
        }
        
        Util::redirect('/login');
    }
    
    /**
     * Display reset password form
     */
    public function resetPassword() {
        // If already logged in, redirect to dashboard
        if ($this->isLoggedIn()) {
            Util::redirect('/dashboard');
        }
        
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            Util::setFlash('error', 'Invalid password reset token');
            Util::redirect('/login');
            return;
        }
        
        $this->render('auth/reset_password', ['token' => $token]);
    }
    
    /**
     * Process reset password form
     */
    public function processResetPassword() {
        // Verify CSRF token
        $this->requireCsrfToken('/login');
        
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($token)) {
            Util::setFlash('error', 'Invalid password reset token');
            Util::redirect('/login');
            return;
        }
        
        // Validate input
        $errors = $this->validate(
            [
                'password' => $password,
                'confirm_password' => $confirmPassword
            ],
            [
                'password' => 'required|min:8',
                'confirm_password' => 'required'
            ]
        );
        
        // Check if passwords match
        if ($password !== $confirmPassword) {
            $errors['confirm_password'] = 'Passwords do not match';
        }
        
        if (!empty($errors)) {
            Util::setFlash('error', 'Please correct the errors in the form');
            $_SESSION['form_errors'] = $errors;
            Util::redirect('/reset-password?token=' . urlencode($token));
            return;
        }
        
        // Reset password
        $result = $this->auth->resetPassword($token, $password);
        
        if ($result) {
            Util::setFlash('success', 'Your password has been reset successfully. You can now login with your new password.');
            Util::redirect('/login');
        } else {
            Util::setFlash('error', 'Password reset failed. The token may be invalid or expired.');
            Util::redirect('/forgot-password');
        }
    }
}
