<?php

namespace App\Controller;

use App\Request\Validator;

abstract class BaseController
{
    protected string $currentUserLogin;
    protected string $currentDateUTC;
    protected ?Validator $validator = null;
    protected bool $isAjax;

    public function __construct()
    {
        $this->currentUserLogin = $_SESSION['user_login'] ?? 'Guest';
        $this->currentDateUTC = date('Y-m-d H:i:s');
        $this->isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                         strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        // Validator initialization on demand - will be created on the first access
        // through the getValidator() getter.
    }

    /**
     * Checking user authorization
     */
    protected function checkAuth(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . url('auth.login'));
            exit;
        }
    }

    /**
     * Lazy validator initialization
     */
    protected function getValidator(): Validator
    {
        if ($this->validator === null) {
            $this->validator = new Validator();
        }
        return $this->validator;
    }

    /**
     * AJAX response with successful result
     */
    protected function ajaxSuccess(array $data = []): void
    {
        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => true], $data));
        exit;
    }

    /**
     * AJAX response with error
     */
    protected function ajaxError(array $errors = [], string $message = ''): void
    {
        header('Content-Type: application/json');
        $response = ['success' => false];

        if (!empty($message)) {
            $response['message'] = $message;
        }

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        echo json_encode($response);
        exit;
    }

    /**
     * Redirects to the specified URL
     */
    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Get ID from the last URL segment
     */
    protected function getIdFromUrl(): int
    {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $segments = explode('/', trim($path, '/'));
        return (int)(end($segments));
    }

    protected function redirectIfAuthenticated(string $url = 'home'): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirect(url($url));
        }
    }
}