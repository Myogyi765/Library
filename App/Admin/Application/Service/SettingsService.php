<?php

namespace App\Admin\Application\Service;

use App\Admin\Infrastructure\Persistence\SettingRepository;

class SettingsService
{
    private SettingRepository $repository;
    
    
    private ?array $settingsCache = null;

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
        if ($this->settingsCache === null) {
            $this->settingsCache = $this->repository->findAll();
        }
        return $this->settingsCache;
    }

    
    public function getAll(): array
    {
        return $this->getSettings();
    }

    
    public function updateSetting(string $key, $value): void
    {
        if (is_bool($value)) {
            $value = $value ? '1' : '0';
        }

        if (is_array($value) || is_object($value)) {
            $value = json_encode($value);
        }

        $this->repository->set($key, $value);
        
        $this->settingsCache = null;
    }

    
    public function updateSettings(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $this->updateSetting($key, $value);
        }
    }

    
    public function deleteSetting(string $key): void
    {
        $this->repository->delete($key);
        $this->settingsCache = null;
    }


    
    public function getFinePerDay(): int 
    { 
        return (int) $this->get('fine_per_day', 500); 
    }

    
    public function getBorrowingFee(): int 
    { 
        return (int) $this->get('borrowing_fee', 0); 
    }

    
    public function getMaxBorrowDays(): int 
    { 
        return (int) $this->get('max_borrow_days', 14); 
    }

    
    public function getMaxBorrowLimit(): int 
    { 
        return (int) $this->get('max_borrow_limit', 5); 
    }

    
    public function getGracePeriodDays(): int 
    { 
        return (int) $this->get('grace_period_days', 3); 
    }

    
    public function getMembershipFee(): int 
    { 
        return (int) $this->get('membership_fee', 0); 
    }

    
    public function getLateReturnFee(): int 
    { 
        return (int) $this->get('late_return_fee', 0); 
    }

    
    public function isRefundEnabled(): bool
    {
        return (bool) $this->get('enable_refunds', true);
    }

    
    public function getSystemStatus(): string
    {
        return (string) $this->get('system_status', 'active');
    }

    
    public function getDefaultRole(): string
    {
        return (string) $this->get('default_role', 'user');
    }
}
