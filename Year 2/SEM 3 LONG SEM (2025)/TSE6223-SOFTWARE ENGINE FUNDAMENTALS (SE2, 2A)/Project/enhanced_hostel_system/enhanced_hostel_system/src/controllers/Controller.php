<?php
/**
 * Base Controller Class
 * 
 * Provides common functionality for all controllers
 */
class Controller {
    protected $db;
    protected $auth;
    protected $user;
    
    /**
     * Constructor
     */
    public function __construct() {
        require_once __DIR__ . '/../models/Database.php';
        require_once __DIR__ . '/../models/Auth.php';
        require_once __DIR__ . '/../models/User.php';
        require_once __DIR__ . '/../utils/Util.php';
        
        $this->db = Database::getInstance();
        $this->auth = new Auth();
        $this->user = new User();
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Render a view
     * 
     * @param string $view View file path
     * @param array $data Data to pass to the view
     * @param string $layout Layout file path
     */
    protected function render($view, $data = [], $layout = 'default') {
        // Extract data to make variables available in view
        extract($data);
        
        // Get current user if logged in
        $currentUser = $this->auth->isLoggedIn() ? $this->auth->getCurrentUser() : null;
        
        // Start output buffering
        ob_start();
        
        // Include view file
        include __DIR__ . '/../views/' . $view . '.php';
        
        // Get view content
        $content = ob_get_clean();
        
        // Include layout file
        include __DIR__ . '/../views/layouts/' . $layout . '.php';
    }
    
    /**
     * Check if user is logged in
     * 
     * @return bool
     */
    protected function isLoggedIn() {
        return $this->auth->isLoggedIn();
    }
    
    /**
     * Check if user has a specific role
     * 
     * @param string|array $roles Role(s) to check
     * @return bool
     */
    protected function hasRole($roles) {
        return $this->auth->hasRole($roles);
    }
    
    /**
     * Require authentication
     * 
     * @param string $redirect URL to redirect to if not authenticated
     */
    protected function requireAuth($redirect = '/login') {
        if (!$this->isLoggedIn()) {
            Util::setFlash('error', 'Please login to access this page');
            Util::redirect($redirect);
        }
    }
    
    /**
     * Require specific role
     * 
     * @param string|array $roles Role(s) to require
     * @param string $redirect URL to redirect to if not authorized
     */
    protected function requireRole($roles, $redirect = '/login') {
        $this->requireAuth($redirect);
        
        if (!$this->hasRole($roles)) {
            Util::setFlash('error', 'You do not have permission to access this page');
            Util::redirect($redirect);
        }
    }
    
    /**
     * Verify CSRF token
     * 
     * @param string $token Token to verify
     * @return bool
     */
    protected function verifyCsrfToken($token) {
        return Util::verifyCsrfToken($token);
    }
    
    /**
     * Require valid CSRF token
     * 
     * @param string $redirect URL to redirect to if token is invalid
     */
    protected function requireCsrfToken($redirect = null) {
        if (!isset($_POST['csrf_token']) || !$this->verifyCsrfToken($_POST['csrf_token'])) {
            Util::setFlash('error', 'Invalid or expired form submission');
            
            if ($redirect) {
                Util::redirect($redirect);
            } else {
                // Redirect back to the referring page
                Util::redirect($_SERVER['HTTP_REFERER'] ?? '/');
            }
        }
    }
    
    /**
     * Get current user
     * 
     * @return array|null User data
     */
    protected function getCurrentUser() {
        return $this->auth->getCurrentUser();
    }
    
    /**
     * Log activity
     * 
     * @param string $action Action performed
     * @param string $entityType Entity type
     * @param int $entityId Entity ID
     */
    protected function logActivity($action, $entityType = null, $entityId = null) {
        $userId = $this->isLoggedIn() ? $_SESSION['user']['id'] : null;
        Util::logActivity($action, $userId, $entityType, $entityId);
    }
    
    /**
     * Validate input
     * 
     * @param array $data Input data
     * @param array $rules Validation rules
     * @return array Validation errors
     */
    protected function validate($data, $rules) {
        return Util::validate($data, $rules);
    }
    
    /**
     * Handle API response
     * 
     * @param mixed $data Response data
     * @param int $status HTTP status code
     */
    protected function jsonResponse($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Handle API error
     * 
     * @param string $message Error message
     * @param int $status HTTP status code
     */
    protected function jsonError($message, $status = 400) {
        $this->jsonResponse(['error' => $message], $status);
    }
}
