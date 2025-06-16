<?php

namespace App\Controller;

use App\Model\Contact;
use App\Service\ImageService;

class ContactController extends BaseController
{
    private Contact $contactModel;
    private ImageService $imageService;

    public function __construct()
    {
        parent::__construct();
        $this->checkAuth();

        $this->contactModel = new Contact();
        $this->validator = $this->getValidator();
        $this->imageService = new ImageService();
    }

    /**
     * Display the list of contacts
     */
    public function index(): void
    {
        $userId = $_SESSION['user_id'];
        $contacts = $this->contactModel->getAllByUserId($userId);

        $currentDateUTC = $this->currentDateUTC;
        $currentUserLogin = $this->currentUserLogin;

        include __DIR__ . '/../View/contact/list.php';
    }

    /**
     * View a single contact
     */
    public function show(int $id = 0): void
    {
        $userId = $_SESSION['user_id'];
        $contactId = $id ?: (int)($_GET['id'] ?? 0);

        if (!$contactId) {
            $this->redirect(url('contacts.index'));
        }

        $contact = $this->contactModel->getById($contactId, $userId);

        $currentDateUTC = $this->currentDateUTC;
        $currentUserLogin = $this->currentUserLogin;

        include __DIR__ . '/../View/contact/view.php';
    }

    /**
     * Add a new contact
     */
    public function store(): void
    {
        $userId = $_SESSION['user_id'];
        $errors = [];

        $currentDateUTC = $this->currentDateUTC;
        $currentUserLogin = $this->currentUserLogin;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $firstName = $_POST['first_name'] ?? '';
            $lastName = $_POST['last_name'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $email = $_POST['email'] ?? '';

            $this->validator->validateName($firstName, 'first_name');
            $this->validator->validateName($lastName, 'last_name');
            $this->validator->validatePhone($phone);
            $this->validator->validateEmail($email);

            $imageValidation = $this->imageService->validate($_FILES['image'] ?? null);
            if (!$imageValidation['valid']) {
                $this->validator->addError('image', $imageValidation['error']);
            }

            $errors = $this->validator->getErrors();

            if (empty($errors)) {
                $imagePath = null;

                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = $this->imageService->upload($_FILES['image']);

                    if ($uploadResult['success']) {
                        $imagePath = $uploadResult['path'];
                    } else {
                        $errors['image'] = $uploadResult['error'];
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
                        if ($this->isAjax) {
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

            if ($this->isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'errors' => $errors
                ]);
                exit;
            }
        }

        include __DIR__ . '/../View/contact/form.php';
    }

    /**
     * Edit a contact
     */
    public function edit(): void
    {
        $userId = $_SESSION['user_id'];
        $contactId = (int)($_GET['id'] ?? 0);

        if (!$contactId) {
            header('Location: ' . url('contacts.index'));
            exit;
        }

        $contact = $this->contactModel->getById($contactId, $userId);

        if (!$contact) {
            header('Location: ' . url('contacts.index'));
            exit;
        }

        $_POST = $contact;

        $formTitle = "Edit contact";
        $formAction = url('contacts', ['id' => $contactId]);
        $formMethod = "POST";
        $methodField = '<input type="hidden" name="_method" value="PUT">';

        $currentDateUTC = $this->currentDateUTC;
        $currentUserLogin = $this->currentUserLogin;

        include __DIR__ . '/../View/contact/form.php';
    }

    /**
     * Updates an existing contact
     */
    public function update(): void
    {
        $userId = $_SESSION['user_id'];
        $errors = [];
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

        // Получаем ID из URL
        $contactId = $this->getIdFromUrl();

        if (!$contactId) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'ID контакта не указан']);
                exit;
            } else {
                header('Location: ' . url('contacts.index'));
                exit;
            }
        }

        $contact = $this->contactModel->getById($contactId, $userId);
        if (!$contact) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Контакт не найден']);
                exit;
            } else {
                header('Location: ' . url('contacts.index'));
                exit;
            }
        }

        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $putMethod = isset($_POST['_method']) && strtoupper($_POST['_method']) === 'PUT';

        if ($requestMethod === 'POST' && $putMethod) {
            $this->validator->validateName($_POST['first_name'] ?? '', 'first_name');
            $this->validator->validateName($_POST['last_name'] ?? '', 'last_name');
            $this->validator->validatePhone($_POST['phone'] ?? '');
            $this->validator->validateEmail($_POST['email'] ?? '');

            if (!empty($_FILES['image']['name'])) {
                $imageValidation = $this->imageService->validate($_FILES['image']);
                if (!$imageValidation['valid']) {
                    $this->validator->addError('image', $imageValidation['error']);
                }
            }

            $errors = $this->validator->getErrors();

            if (empty($errors)) {
                $contactData = [
                    'first_name' => $_POST['first_name'],
                    'last_name' => $_POST['last_name'],
                    'phone' => $_POST['phone'],
                    'email' => $_POST['email'],
                    'id' => $contactId,
                    'user_id' => $userId
                ];

                if (!empty($_FILES['image']['name'])) {
                    $uploadResult = $this->imageService->upload($_FILES['image']);

                    if ($uploadResult['success']) {
                        $contactData['image_path'] = $uploadResult['path'];

                        if (!empty($contact['image_path'])) {
                            $this->imageService->delete($contact['image_path']);
                        }
                    } else {
                        $errors['image'] = $uploadResult['error'];
                    }
                }

                if (empty($errors)) {
                    $success = $this->contactModel->update($contactData);

                    if ($success) {
                        $updatedContact = $this->contactModel->getById($contactId, $userId);

                        if ($isAjax) {
                            $this->ajaxSuccess(['contact' => $updatedContact]);
                        } else {
                            header('Location: ' . url('contacts.index'));
                            exit;
                        }
                    } else {
                        $errors['general'] = 'Не удалось обновить контакт';
                    }
                }
            }

            if ($isAjax) {
                $this->ajaxError($errors);
            }
        }

        if (!empty($errors)) {
            $_POST['id'] = $contactId;

            if (empty($_FILES['image']['name']) && !empty($contact['image_path'])) {
                $_POST['image_path'] = $contact['image_path'];
            }

            $formTitle = "Редактировать контакт";
            $formAction = url('contacts', ['id' => $contactId]);
            $formMethod = "POST";
            $methodField = '<input type="hidden" name="_method" value="PUT">';

            $currentDateUTC = $this->currentDateUTC;
            $currentUserLogin = $this->currentUserLogin;

            include __DIR__ . '/../View/contact/form.php';
            exit;
        }

        header('Location: ' .  url('contacts.index'));
        exit;
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
                header('Location: ' .  url('contacts.index'));
                exit;
            }
        }

        $contact = $this->contactModel->getById($contactId, $userId);

        if ($contact && !empty($contact['image_path'])) {
            $this->imageService->delete($contact['image_path']);
        }

        $success = $this->contactModel->delete($contactId, $userId);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => $success]);
            exit;
        } else {
            header('Location: ' .  url('contacts.index'));
            exit;
        }
    }
}