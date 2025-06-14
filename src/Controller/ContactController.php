<?php

namespace App\Controller;

use App\Model\Contact;
use App\Request\Validator;

class ContactController
{
    private Contact $contactModel;
    private Validator $validator;

    public function __construct()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /auth/login');
            exit;
        }

        $this->contactModel = new Contact();
        $this->validator = new Validator();
    }

    /**
     * Display the list of contacts
     */
    public function index(): void
    {
        $userId = $_SESSION['user_id'];
        $contacts = $this->contactModel->getAllByUserId($userId);

        // Add current time and user information
        $currentDateUTC = date('Y-m-d H:i:s');
        $currentUserLogin = $_SESSION['user_login'] ?? 'LeonidGorlovan';

        include __DIR__ . '/../View/contact/list.php';
    }

    /**
     * View a single contact
     */
    public function view(int $id = 0): void
    {
        $userId = $_SESSION['user_id'];
        $contactId = $id ?: (int)($_GET['id'] ?? 0);

        if (!$contactId) {
            header('Location: /contacts');
            exit;
        }

        $contact = $this->contactModel->getById($contactId, $userId);

        if (!$contact) {
            header('Location: /contacts');
            exit;
        }

        // Add current time and user information
        $currentDateUTC = date('Y-m-d H:i:s');
        $currentUserLogin = $_SESSION['user_login'] ?? 'LeonidGorlovan';

        include __DIR__ . '/../View/contact/view.php';
    }

    /**
     * Add a new contact
     */
    public function store(): void
    {
        $userId = $_SESSION['user_id'];
        $errors = [];
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

        // Add current time and user information
        $currentDateUTC = date('Y-m-d H:i:s');
        $currentUserLogin = $_SESSION['user_login'] ?? 'LeonidGorlovan';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $firstName = $_POST['first_name'] ?? '';
            $lastName = $_POST['last_name'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $email = $_POST['email'] ?? '';

            // Validate fields
            $this->validator->validateName($firstName, 'first_name');
            $this->validator->validateName($lastName, 'last_name');
            $this->validator->validatePhone($phone);
            $this->validator->validateEmail($email);
            $this->validator->validateImage($_FILES['image'] ?? null);

            $errors = $this->validator->getErrors();

            if (empty($errors)) {
                $imagePath = null;

                // Handle image upload
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . '/../../public/assets/uploads/';

                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    $fileExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                    $fileName = uniqid() . '.' . $fileExtension;
                    $uploadPath = $uploadDir . $fileName;

                    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                        $imagePath = 'assets/uploads/' . $fileName;
                    } else {
                        $errors['image'] = 'Failed to upload image';
                    }
                }

                if (empty($errors)) {
                    $contactData = [
                        'user_id' => $userId,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'phone' => $phone,
                        'email' => $email,
                        'image_path' => $imagePath
                    ];

                    $newContactId = $this->contactModel->create($contactData);

                    if ($newContactId) {
                        if ($isAjax) {
                            $contact = $this->contactModel->getById($newContactId, $userId);
                            header('Content-Type: application/json');
                            echo json_encode([
                                'success' => true,
                                'contact' => $contact
                            ]);
                            exit;
                        } else {
                            header('Location: /contacts');
                            exit;
                        }
                    } else {
                        $errors['general'] = 'Failed to create contact';
                    }
                }
            }

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'errors' => $errors
                ]);
                exit;
            }
        }

        // If this is a GET request or validation failed, show the form
        include __DIR__ . '/../View/contact/form.php';
    }

    /**
     * Delete a contact
     */
    public function delete(int $id = 0): void
    {
        $userId = $_SESSION['user_id'];
        $contactId = $id ?: (int)($_GET['id'] ?? 0);
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

        if (!$contactId) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid contact ID']);
                exit;
            } else {
                header('Location: /contacts');
                exit;
            }
        }

        $success = $this->contactModel->delete($contactId, $userId);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => $success]);
            exit;
        } else {
            header('Location: /contacts');
            exit;
        }
    }
}