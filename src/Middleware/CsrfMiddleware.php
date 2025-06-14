<?php

namespace App\Middleware;

class CsrfMiddleware extends AbstractMiddleware
{
    private const TOKEN_KEY = 'csrf_token';
    private const TOKEN_HEADER = 'X-CSRF-TOKEN';
    private const TOKEN_FIELD = '_csrf_token';
    private const EXPIRATION = 3600; // 1 hour
    
    /**
     * Process the request with CSRF protection
     */
    public function process(): void
    {
        // Skip for GET requests
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // Generate token for forms
            $this->generateToken();
            $this->processNext();
            return;
        }
        
        // Validate token for POST/PUT/DELETE requests
        if (!$this->validateToken()) {
            http_response_code(403);
            
            // Check if it's an AJAX request
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'CSRF token validation failed']);
                exit;
            }
            
            // Regular request
            include __DIR__ . '/../View/error/csrf.php';
            exit;
        }
        
        // Regenerate token for the next request
        $this->generateToken();
        
        // Continue to the next middleware
        $this->processNext();
    }
    
    /**
     * Generate a new CSRF token and store it in the session
     */
    private function generateToken(): void
    {
        // Ensure session is started
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        // Generate a new token if it doesn't exist or has expired
        if (!isset($_SESSION[self::TOKEN_KEY]) || 
            !isset($_SESSION[self::TOKEN_KEY . '_time']) || 
            (time() - $_SESSION[self::TOKEN_KEY . '_time'] > self::EXPIRATION)) {
            
            $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(32));
            $_SESSION[self::TOKEN_KEY . '_time'] = time();
        }
    }
    
    /**
     * Validate the CSRF token from the request
     * 
     * @return bool
     */
    private function validateToken(): bool
    {
        // Ensure session is started
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        // Check if the token exists in the session
        if (!isset($_SESSION[self::TOKEN_KEY])) {
            return false;
        }
        
        $token = $_SESSION[self::TOKEN_KEY];
        
        // Get token from POST data or headers
        $requestToken = $_POST[self::TOKEN_FIELD] ?? null;
        
        if ($requestToken === null) {
            // Try to get token from header for AJAX requests
            $requestToken = $_SERVER['HTTP_' . str_replace('-', '_', strtoupper(self::TOKEN_HEADER))] ?? null;
        }
        
        // No token found in the request
        if ($requestToken === null) {
            return false;
        }
        
        // Validate token
        return hash_equals($token, $requestToken);
    }
    
    /**
     * Get the current CSRF token
     * 
     * @return string
     */
    public static function getToken(): string
    {
        // Ensure session is started
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        if (!isset($_SESSION[self::TOKEN_KEY])) {
            $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(32));
            $_SESSION[self::TOKEN_KEY . '_time'] = time();
        }
        
        return $_SESSION[self::TOKEN_KEY];
    }
    
    /**
     * Generate a CSRF token field for forms
     * 
     * @return string HTML input field
     */
    public static function getTokenField(): string
    {
        $token = self::getToken();
        return '<input type="hidden" name="' . self::TOKEN_FIELD . '" value="' . htmlspecialchars($token) . '">';
    }
}