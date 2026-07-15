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

    /**
     * Fetch a single setting by key.
     *
     * @param string $key
     * @return string|null
     */
    public function findByKey(string $key): ?string
    {
        $sql = "SELECT `setting_value` FROM `settings` WHERE `setting_key` = :key LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['key' => $key]);
        $result = $stmt->fetchColumn();
        return $result !== false ? (string) $result : null;
    }

    /**
     * Fetch all settings as an associative array (key => value).
     *
     * @return array<string, string>
     */
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

    /**
     * Alias for findAll() – returns all settings as key-value array.
     *
     * @return array<string, string>
     */
    public function getAll(): array
    {
        return $this->findAll();
    }

    /**
     * Insert or update a setting.
     * Automatically converts booleans to '1'/'0'.
     *
     * @param string $key
     * @param mixed $value (string, int, bool, etc.)
     * @return void
     */
    public function set(string $key, $value): void
    {
        // Convert booleans to '1'/'0' for consistent storage
        if (is_bool($value)) {
            $value = $value ? '1' : '0';
        }
        // Convert other types to string
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

    /**
     * Update multiple settings at once.
     *
     * @param array<string, mixed> $data
     * @return void
     */
    public function update(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Get a setting with a default fallback.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $value = $this->findByKey($key);
        return $value !== null ? $value : $default;
    }

    /**
     * Delete a setting by key.
     *
     * @param string $key
     * @return void
     */
    public function delete(string $key): void
    {
        $sql = "DELETE FROM `settings` WHERE `setting_key` = :key";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['key' => $key]);
    }
}