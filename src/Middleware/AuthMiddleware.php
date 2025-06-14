<?php

namespace App\Middleware;

class AuthMiddleware extends AbstractMiddleware
{
    /**
     * Process the request with authentication check
     */
    public function process(): void
    {
        // Ensure session is started
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        // Check if user is authenticated
        if (!isset($_SESSION['user_id'])) {
            // Check if it's an AJAX request
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode(['error' => 'Authentication required']);
                exit;
            }
            
            // Redirect to login page
            header('Location: /auth/login');
            exit;
        }
        
        // Continue to the next middleware
        $this->processNext();
    }
}