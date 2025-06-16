<?php

namespace App\Controller;

use App\Model\User;

class AuthController extends BaseController
{
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
        $this->validator = $this->getValidator();
    }

    public function login(): void
    {
        $this->redirectIfAuthenticated('contacts.index');

        $errors = [];
        $currentDateUTC = date('Y-m-d H:i:s');
        $currentUserLogin = 'Guest';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $login = $_POST['login'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($login)) {
                $errors['login'] = 'Login is required';
            }

            if (empty($password)) {
                $errors['password'] = 'Password is required';
            }

            if (empty($errors)) {
                $user = $this->userModel->findByLogin($login);

                if ($user && $this->userModel->verifyPassword($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_login'] = $user['login'];

                    header('Location: ' . url('contacts.index'));
                    exit;
                } else {
                    $errors['auth'] = 'Invalid login or password';
                }
            }
        }

        include __DIR__ . '/../View/auth/login.php';
    }

    public function register(): void
    {
        // Redirect if already logged in
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . url('home'));
            exit;
        }

        $errors = [];
        $currentDateUTC = date('Y-m-d H:i:s');
        $currentUserLogin = 'Guest';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $login = $_POST['login'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            // Validate login
            $this->validator->validateLogin($login);

            // Validate email
            $this->validator->validateEmail($email);

            // Validate password
            $this->validator->validatePassword($password);

            // Confirm password
            if ($password !== $confirmPassword) {
                $errors['confirm_password'] = 'Passwords do not match';
            }

            // Merge errors
            $errors = array_merge($errors, $this->validator->getErrors());

            // Check if login already exists
            if (empty($errors['login']) && $this->userModel->findByLogin($login)) {
                $errors['login'] = 'This login is already taken';
            }

            // Check if email already exists
            if (empty($errors['email']) && $this->userModel->findByEmail($email)) {
                $errors['email'] = 'This email is already registered';
            }

            if (empty($errors)) {
                if ($this->userModel->create($login, $email, $password)) {
                    $_SESSION['register_success'] = true;
                    header('Location: ' . url('auth.login'));
                    exit;
                } else {
                    $errors['general'] = 'Error creating account. Please try again.';
                }
            }
        }

        include __DIR__ . '/../View/auth/register.php';
    }

    public function logout(): void
    {
        session_unset();
        session_destroy();
        header('Location: ' . url('auth.login'));
        exit;
    }
}