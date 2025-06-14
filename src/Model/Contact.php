<?php

namespace App\Model;

use PDO;

class Contact
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    public function getAllByUserId(int $userId): array
    {
        $stmt = $this->db->prepare('
            SELECT * FROM contacts 
            WHERE user_id = :user_id 
            ORDER BY last_name, first_name
        ');
        $stmt->execute(['user_id' => $userId]);
        
        return $stmt->fetchAll();
    }
    
    public function getById(int $id, int $userId): ?array
    {
        $stmt = $this->db->prepare('
            SELECT * FROM contacts 
            WHERE id = :id AND user_id = :user_id 
            LIMIT 1
        ');
        $stmt->execute([
            'id' => $id,
            'user_id' => $userId
        ]);
        
        $contact = $stmt->fetch();
        return $contact ?: null;
    }
    
    public function create(array $data): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO contacts (user_id, first_name, last_name, phone, email, image_path, created_at) 
            VALUES (:user_id, :first_name, :last_name, :phone, :email, :image_path, NOW())
        ');
        
        $stmt->execute([
            'user_id' => $data['user_id'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'image_path' => $data['image_path'] ?? null
        ]);
        
        return (int)$this->db->lastInsertId();
    }
    
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare('
            UPDATE contacts 
            SET first_name = :first_name, 
                last_name = :last_name, 
                phone = :phone, 
                email = :email,
                image_path = :image_path,
                updated_at = NOW()
            WHERE id = :id AND user_id = :user_id
        ');
        
        return $stmt->execute([
            'id' => $id,
            'user_id' => $data['user_id'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'image_path' => $data['image_path']
        ]);
    }
    
    public function delete(int $id, int $userId): bool
    {
        // First get the image path to delete the file
        $contact = $this->getById($id, $userId);
        
        $stmt = $this->db->prepare('
            DELETE FROM contacts 
            WHERE id = :id AND user_id = :user_id
        ');
        
        $result = $stmt->execute([
            'id' => $id,
            'user_id' => $userId
        ]);
        
        // Delete the image file if exists
        if ($result && $contact && !empty($contact['image_path'])) {
            $filePath = __DIR__ . '/../../public/' . $contact['image_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        
        return $result;
    }
}