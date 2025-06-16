<?php

namespace App\Service;

class ImageService
{
    private string $uploadDir;
    private array $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    private int $maxFileSize = 5242880; // 5MB

    public function __construct(string $baseUploadPath = null)
    {
        $this->uploadDir = $baseUploadPath ?? __DIR__ . '/../../public/assets/uploads/';

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * Updates an existing contact
     *
     * @param array $file
     * @return array ['success' => bool, 'path' => string|null, 'error' => string|null]
     */
    public function upload(array $file): array
    {
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'path' => null,
                'error' => 'The file was not downloaded or an error occurred'
            ];
        }

        if (!in_array($file['type'], $this->allowedTypes)) {
            return [
                'success' => false,
                'path' => null,
                'error' => 'Invalid file type. Only JPG, PNG and GIF are allowed'
            ];
        }

        if ($file['size'] > $this->maxFileSize) {
            return [
                'success' => false,
                'path' => null,
                'error' => 'File size exceeds the allowed limit of ' . $this->formatFileSize($this->maxFileSize)
            ];
        }

        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '.' . $fileExtension;
        $uploadPath = $this->uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return [
                'success' => true,
                'path' => 'assets/uploads/' . $fileName,
                'error' => null
            ];
        }

        return [
            'success' => false,
            'path' => null,
            'error' => 'Failed to save file'
        ];
    }

    /**
     * Deletes an image along a relative path
     *
     * @param string $relativePath
     * @return bool
     */
    public function delete(string $relativePath): bool
    {
        $fullPath = __DIR__ . '/../../public/' . $relativePath;

        if (file_exists($fullPath) && is_file($fullPath)) {
            return unlink($fullPath);
        }

        return false;
    }

    /**
     * Checks the validity of the image
     *
     * @param array|null $file
     * @return array ['valid' => bool, 'error' => string|null]
     */
    public function validate(array $file = null): array
    {
        if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return ['valid' => true, 'error' => null];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [
                'valid' => false,
                'error' => 'File upload error. Error code: ' . $file['error']
            ];
        }

        if (!in_array($file['type'], $this->allowedTypes)) {
            return [
                'valid' => false,
                'error' => 'Invalid file type. Only JPG, PNG and GIF are allowed'
            ];
        }

        if ($file['size'] > $this->maxFileSize) {
            return [
                'valid' => false,
                'error' => 'File size exceeds the allowed limit of 5MB'
            ];
        }

        return ['valid' => true, 'error' => null];
    }

    /**
     * Converts file size in bytes to human-readable format
     *
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    private function formatFileSize(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
