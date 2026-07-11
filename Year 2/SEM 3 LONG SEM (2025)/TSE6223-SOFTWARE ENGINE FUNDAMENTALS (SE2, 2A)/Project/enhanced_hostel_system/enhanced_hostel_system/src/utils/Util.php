<?php
/**
 * Utility Class
 * 
 * Provides utility functions for the application
 */
class Util {
    /**
     * Generate a CSRF token input field
     * 
     * @return string HTML input field
     */
    public static function csrfField() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
    }
    
    /**
     * Verify CSRF token
     * 
     * @param string $token Token to verify
     * @return bool
     */
    public static function verifyCsrfToken($token) {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Set flash message
     * 
     * @param string $type Message type (success, error, info, warning)
     * @param string $message Message content
     */
    public static function setFlash($type, $message) {
        $_SESSION['flash'][$type] = $message;
    }
    
    /**
     * Get flash message
     * 
     * @param string $type Message type
     * @return string|null Message content or null if not exists
     */
    public static function getFlash($type) {
        if (isset($_SESSION['flash'][$type])) {
            $message = $_SESSION['flash'][$type];
            unset($_SESSION['flash'][$type]);
            return $message;
        }
        
        return null;
    }
    
    /**
     * Check if flash message exists
     * 
     * @param string $type Message type
     * @return bool
     */
    public static function hasFlash($type) {
        return isset($_SESSION['flash'][$type]);
    }
    
    /**
     * Redirect to URL
     * 
     * @param string $url URL to redirect to
     */
    public static function redirect($url) {
        header('Location: ' . $url);
        exit;
    }
    
    /**
     * Sanitize output for HTML display
     * 
     * @param string $text Text to sanitize
     * @return string Sanitized text
     */
    public static function escape($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Format date
     * 
     * @param string $date Date string
     * @param string $format Format string
     * @return string Formatted date
     */
    public static function formatDate($date, $format = 'Y-m-d') {
        return date($format, strtotime($date));
    }
    
    /**
     * Format currency
     * 
     * @param float $amount Amount
     * @param string $currency Currency symbol
     * @return string Formatted currency
     */
    public static function formatCurrency($amount, $currency = '$') {
        return $currency . number_format($amount, 2);
    }
    
    /**
     * Generate pagination links
     * 
     * @param int $currentPage Current page number
     * @param int $totalPages Total number of pages
     * @param string $url Base URL
     * @return string HTML pagination links
     */
    public static function pagination($currentPage, $totalPages, $url) {
        if ($totalPages <= 1) {
            return '';
        }
        
        $html = '<nav aria-label="Page navigation"><ul class="pagination">';
        
        // Previous button
        if ($currentPage > 1) {
            $html .= '<li class="page-item"><a class="page-link" href="' . $url . '?page=' . ($currentPage - 1) . '">&laquo; Previous</a></li>';
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link">&laquo; Previous</span></li>';
        }
        
        // Page numbers
        $startPage = max(1, $currentPage - 2);
        $endPage = min($totalPages, $currentPage + 2);
        
        if ($startPage > 1) {
            $html .= '<li class="page-item"><a class="page-link" href="' . $url . '?page=1">1</a></li>';
            if ($startPage > 2) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }
        
        for ($i = $startPage; $i <= $endPage; $i++) {
            if ($i == $currentPage) {
                $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
            } else {
                $html .= '<li class="page-item"><a class="page-link" href="' . $url . '?page=' . $i . '">' . $i . '</a></li>';
            }
        }
        
        if ($endPage < $totalPages) {
            if ($endPage < $totalPages - 1) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            $html .= '<li class="page-item"><a class="page-link" href="' . $url . '?page=' . $totalPages . '">' . $totalPages . '</a></li>';
        }
        
        // Next button
        if ($currentPage < $totalPages) {
            $html .= '<li class="page-item"><a class="page-link" href="' . $url . '?page=' . ($currentPage + 1) . '">Next &raquo;</a></li>';
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link">Next &raquo;</span></li>';
        }
        
        $html .= '</ul></nav>';
        
        return $html;
    }
    
    /**
     * Log activity
     * 
     * @param string $action Action performed
     * @param int $userId User ID
     * @param string $entityType Entity type
     * @param int $entityId Entity ID
     */
    public static function logActivity($action, $userId = null, $entityType = null, $entityId = null) {
        require_once __DIR__ . '/Database.php';
        $db = Database::getInstance();
        
        $db->insert('activity_logs', [
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    }
    
    /**
     * Validate input
     * 
     * @param array $data Input data
     * @param array $rules Validation rules
     * @return array Validation errors
     */
    public static function validate($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            // Required
            if (strpos($rule, 'required') !== false && empty($value)) {
                $errors[$field] = 'This field is required';
                continue;
            }
            
            // Skip validation if field is empty and not required
            if (empty($value) && strpos($rule, 'required') === false) {
                continue;
            }
            
            // Email
            if (strpos($rule, 'email') !== false && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = 'Invalid email address';
            }
            
            // Numeric
            if (strpos($rule, 'numeric') !== false && !is_numeric($value)) {
                $errors[$field] = 'Must be a number';
            }
            
            // Min length
            if (preg_match('/min:(\d+)/', $rule, $matches)) {
                $min = $matches[1];
                if (strlen($value) < $min) {
                    $errors[$field] = 'Must be at least ' . $min . ' characters';
                }
            }
            
            // Max length
            if (preg_match('/max:(\d+)/', $rule, $matches)) {
                $max = $matches[1];
                if (strlen($value) > $max) {
                    $errors[$field] = 'Must not exceed ' . $max . ' characters';
                }
            }
            
            // Date
            if (strpos($rule, 'date') !== false) {
                $date = date_create($value);
                if (!$date) {
                    $errors[$field] = 'Invalid date format';
                }
            }
            
            // Future date
            if (strpos($rule, 'future') !== false) {
                $date = date_create($value);
                $now = date_create('now');
                if ($date && $date <= $now) {
                    $errors[$field] = 'Date must be in the future';
                }
            }
        }
        
        return $errors;
    }
}
