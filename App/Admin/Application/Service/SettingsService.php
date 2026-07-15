<?php

namespace App\Admin\Application\Service;

use App\Admin\Infrastructure\Persistence\SettingRepository;

class SettingsService
{
    private SettingRepository $repository;

    public function __construct(SettingRepository $repository)
    {
        $this->repository = $repository;
    }

    
    private function get(string $key, $default = null)
    {
        $value = $this->repository->findByKey($key);
        return $value !== null ? $value : $default;
    }

    
    public function getSetting(string $key, $default = null)
    {
        return $this->get($key, $default);
    }

    
    public function getSettings(): array
    {
        return $this->repository->findAll();
    }

    
    public function updateSetting(string $key, $value): void
    {
        // Convert booleans to integers for storage
        if (is_bool($value)) {
            $value = $value ? '1' : '0';
        }

        // Delegate to repository (must implement insert/update logic)
        $this->repository->set($key, $value);
    }

    // ─── Convenience Getters ──────────────────────────────────
    public function getFinePerDay(): int { return (int) $this->get('fine_per_day', 500); }
    public function getBorrowingFee(): int { return (int) $this->get('borrowing_fee', 0); }
    public function getMaxBorrowDays(): int { return (int) $this->get('max_borrow_days', 14); }
    public function getMaxBorrowLimit(): int { return (int) $this->get('max_borrow_limit', 5); }
    public function getGracePeriodDays(): int { return (int) $this->get('grace_period_days', 3); }
    public function getMembershipFee(): int { return (int) $this->get('membership_fee', 0); }
    public function getLateReturnFee(): int { return (int) $this->get('late_return_fee', 0); }
}
