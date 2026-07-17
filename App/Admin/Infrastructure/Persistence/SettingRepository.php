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
        $sql = "SELECT `setting_value` FROM `settings` WHERE `setting_key` = :key LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['key' => $key]);
        $result = $stmt->fetchColumn();
        return $result !== false ? (string) $result : null;
    }

        public function findAll(): array
    {
        $sql = "SELECT `setting_key`, `setting_value` FROM `settings`";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    
    public function getAll(): array
    {
        return $this->findAll();
    }

   
    public function set(string $key, $value): void
    {
        if (is_bool($value)) {
            $value = $value ? '1' : '0';
        }
        $value = (string) $value;

        $sql = "INSERT INTO `settings` (`setting_key`, `setting_value`) 
                VALUES (:key, :value) 
                ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`)";
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

    public function get(string $key, $default = null)
    {
        $value = $this->findByKey($key);
        return $value !== null ? $value : $default;
    }

   
    public function delete(string $key): void
    {
        $sql = "DELETE FROM `settings` WHERE `setting_key` = :key";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['key' => $key]);
    }
}