<?php
/**
 * Authentication Class
 * 
 * Handles user authentication, registration, and session management
 */
class Auth {
    private $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        require_once __DIR__ . '/../models/Database.php';
        $this->db = Database::getInstance();
    }
    
    /**
     * Login a user
     * 
     * @param string $email User email
     * @param string $password User password
     * @param bool $remember Remember login
     * @return array|bool User data on success, false on failure
     */
    public function login($email, $password, $remember = false) {
        // Check if account is locked
        $user = $this->db->fetchOne(
            "SELECT * FROM users WHERE email = ?", 
            [$email]
        );
        
        if (!$user) {
            return false;
        }
        
        // Check if account is locked
        if ($user['locked_until'] && new DateTime($user['locked_until']) > new DateTime()) {
            $this->addActivityLog('login_attempt_locked', $user['id']);
            return ['error' => 'account_locked', 'message' => 'Account is temporarily locked. Try again later.'];
        }
        
        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            // Increment login attempts
            $attempts = $user['login_attempts'] + 1;
            
            // Lock account if max attempts reached
            if ($attempts >= MAX_LOGIN_ATTEMPTS) {
                $lockedUntil = (new DateTime())->add(new DateInterval('PT' . LOCKOUT_TIME . 'S'));
                $this->db->update('users', 
                    [
                        'login_attempts' => 0,
                        'locked_until' => $lockedUntil->format('Y-m-d H:i:s')
                    ],
                    'id = ?', 
                    [$user['id']]
                );
                
                $this->addActivityLog('account_locked', $user['id']);
                return ['error' => 'account_locked', 'message' => 'Too many failed attempts. Account locked temporarily.'];
            } else {
                $this->db->update('users', 
                    ['login_attempts' => $attempts],
                    'id = ?', 
                    [$user['id']]
                );
                
                $this->addActivityLog('login_failed', $user['id']);
                return false;
            }
        }
        
        // Reset login attempts and update last login
        $this->db->update('users', 
            [
                'login_attempts' => 0,
                'locked_until' => null,
                'last_login' => (new DateTime())->format('Y-m-d H:i:s')
            ],
            'id = ?', 
            [$user['id']]
        );
        
        // Set session
        $_SESSION['user'] = [
            'id' => $user['id'],
            'role' => $user['role'],
            'name' => $user['name'],
            'email' => $user['email'],
            'csrf_token' => $this->generateCsrfToken()
        ];
        
        // Set remember-me cookie if requested
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $expires = time() + (30 * 24 * 60 * 60); // 30 days
            
            // Store token in database
            $this->db->insert('remember_tokens', [
                'user_id' => $user['id'],
                'token' => password_hash($token, PASSWORD_DEFAULT),
                'expires_at' => date('Y-m-d H:i:s', $expires)
            ]);
            
            // Set cookie
            setcookie('remember_token', $user['id'] . ':' . $token, $expires, '/', '', true, true);
        }
        
        $this->addActivityLog('login_success', $user['id']);
        return $user;
    }
    
    /**
     * Register a new user
     * 
     * @param array $userData User data
     * @return int|bool User ID on success, false on failure
     */
    public function register($userData) {
        // Validate email uniqueness
        $existingUser = $this->db->fetchOne(
            "SELECT id FROM users WHERE email = ?", 
            [$userData['email']]
        );
        
        if ($existingUser) {
            return ['error' => 'email_exists', 'message' => 'Email already exists'];
        }
        
        // Validate password strength
        if (strlen($userData['password']) < PASSWORD_MIN_LENGTH) {
            return ['error' => 'weak_password', 'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters'];
        }
        
        // Hash password
        $passwordHash = password_hash($userData['password'], PASSWORD_DEFAULT);
        
        // Insert user
        try {
            $userId = $this->db->insert('users', [
                'name' => $userData['name'],
                'email' => $userData['email'],
                'phone' => $userData['phone'] ?? null,
                'password_hash' => $passwordHash,
                'role' => $userData['role'] ?? 'student'
            ]);
            
            $this->addActivityLog('user_registered', $userId);
            return $userId;
        } catch (Exception $e) {
            error_log('Registration error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Logout the current user
     */
    public function logout() {
        $userId = $_SESSION['user']['id'] ?? null;
        
        // Clear session
        session_unset();
        session_destroy();
        
        // Clear remember-me cookie
        if (isset($_COOKIE['remember_token'])) {
            list($cookieUserId, $token) = explode(':', $_COOKIE['remember_token']);
            
            // Remove token from database
            $this->db->delete('remember_tokens', 'user_id = ?', [$cookieUserId]);
            
            // Expire cookie
            setcookie('remember_token', '', time() - 3600, '/', '', true, true);
        }
        
        if ($userId) {
            $this->addActivityLog('logout', $userId);
        }
    }
    
    /**
     * Check if user is logged in
     * 
     * @return bool
     */
    public function isLoggedIn() {
        return isset($_SESSION['user']);
    }
    
    /**
     * Check if user has a specific role
     * 
     * @param string|array $roles Role(s) to check
     * @return bool
     */
    public function hasRole($roles) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        if (is_array($roles)) {
            return in_array($_SESSION['user']['role'], $roles);
        }
        
        return $_SESSION['user']['role'] === $roles;
    }
    
    /**
     * Get current user data
     * 
     * @return array|null
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return $this->db->fetchOne(
            "SELECT * FROM users WHERE id = ?", 
            [$_SESSION['user']['id']]
        );
    }
    
    /**
     * Generate CSRF token
     * 
     * @return string
     */
    public function generateCsrfToken() {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }
    
    /**
     * Verify CSRF token
     * 
     * @param string $token Token to verify
     * @return bool
     */
    public function verifyCsrfToken($token) {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Add activity log
     * 
     * @param string $action Action performed
     * @param int $userId User ID
     * @param string $entityType Entity type
     * @param int $entityId Entity ID
     */
    private function addActivityLog($action, $userId = null, $entityType = null, $entityId = null) {
        $this->db->insert('activity_logs', [
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    }
    
    /**
     * Initiate password reset
     * 
     * @param string $email User email
     * @return bool
     */
    public function requestPasswordReset($email) {
        $user = $this->db->fetchOne(
            "SELECT id FROM users WHERE email = ?", 
            [$email]
        );
        
        if (!$user) {
            return false;
        }
        
        // Generate token
        $token = bin2hex(random_bytes(32));
        $expires = (new DateTime())->add(new DateInterval('PT1H')); // 1 hour
        
        // Store token in database
        $this->db->update('users', 
            [
                'reset_token' => $token,
                'reset_token_expires' => $expires->format('Y-m-d H:i:s')
            ],
            'id = ?', 
            [$user['id']]
        );
        
        $this->addActivityLog('password_reset_requested', $user['id']);
        
        // Token would be sent via email in a real application
        return $token;
    }
    
    /**
     * Reset password with token
     * 
     * @param string $token Reset token
     * @param string $password New password
     * @return bool
     */
    public function resetPassword($token, $password) {
        $user = $this->db->fetchOne(
            "SELECT id FROM users WHERE reset_token = ? AND reset_token_expires > NOW()", 
            [$token]
        );
        
        if (!$user) {
            return false;
        }
        
        // Validate password strength
        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            return false;
        }
        
        // Update password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        $this->db->update('users', 
            [
                'password_hash' => $passwordHash,
                'reset_token' => null,
                'reset_token_expires' => null
            ],
            'id = ?', 
            [$user['id']]
        );
        
        $this->addActivityLog('password_reset_completed', $user['id']);
        return true;
    }
}
