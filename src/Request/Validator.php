<?php

namespace App\Request;

class Validator
{
    private array $errors = [];

    public function validateLogin(string $login): bool
    {
        if (empty($login)) {
            $this->errors['login'] = 'Login is required';
            return false;
        }
        
        if (!preg_match('/^[a-zA-Z0-9]{1,16}$/', $login)) {
            $this->errors['login'] = 'Login must contain only Latin letters and numbers, up to 16 characters';
            return false;
        }
        
        return true;
    }
    
    public function validateEmail(string $email): bool
    {
        if (empty($email)) {
            $this->errors['email'] = 'Email is required';
            return false;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Invalid email format';
            return false;
        }
        
        return true;
    }
    
    public function validatePassword(string $password): bool
    {
        if (empty($password)) {
            $this->errors['password'] = 'Password is required';
            return false;
        }
        
        if (strlen($password) < 6) {
            $this->errors['password'] = 'Password must be at least 6 characters';
            return false;
        }
        
        if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $this->errors['password'] = 'Password must contain uppercase and lowercase letters and numbers';
            return false;
        }
        
        return true;
    }
    
    public function validateName(string $name, string $field): bool
    {
        if (empty($name)) {
            $this->errors[$field] = 'Name is required';
            return false;
        }
        
        return true;
    }
    
    public function validatePhone(string $phone): bool
    {
        if (empty($phone)) {
            $this->errors['phone'] = 'Phone number is required';
            return false;
        }
        
        // Basic phone validation - can be adjusted based on your requirements
        if (!preg_match('/^[0-9+\-() ]{5,20}$/', $phone)) {
            $this->errors['phone'] = 'Invalid phone number format';
            return false;
        }
        
        return true;
    }
    
    public function validateImage(?array $file): bool
    {
        // Skip validation if no file is uploaded (optional field)
        if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return true;
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors['image'] = 'Error uploading file';
            return false;
        }
        
        // Check file size (5MB max)
        if ($file['size'] > 5 * 1024 * 1024) {
            $this->errors['image'] = 'Image size must not exceed 5MB';
            return false;
        }
        
        // Check file type
        $allowedTypes = ['image/jpeg', 'image/png'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime, $allowedTypes)) {
            $this->errors['image'] = 'Only JPEG and PNG images are allowed';
            return false;
        }
        
        return true;
    }
    
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }
}