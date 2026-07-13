<?php

namespace App\Admin\Infrastructure\Persistence;

use PDO;

class SettingRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    
    public function findByKey(string $key): ?string
    {
        $sql = "SELECT setting_value FROM settings WHERE setting_key = :key LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['key' => $key]); 
        $result = $stmt->fetchColumn();
        return $result !== false ? (string) $result : null;
    }

    
    public function findAll(): array
    {
        $sql = "SELECT setting_key, setting_value FROM settings";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

   
    public function set(string $key, string $value): void
    {
        
        $sql = "INSERT INTO settings (setting_key, setting_value) 
                VALUES (:key, :value) 
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'key'   => $key,
            'value' => $value
        ]);
    }

    public function update(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }
    }
}