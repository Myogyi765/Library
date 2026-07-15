<?php

namespace App\User\Presentation\Validator;

class UserValidator
{
    private array $errors = [];

    public function validateRegister(array $data): bool
    {
        $this->errors = [];

        if (empty($data['name']) || strlen($data['name']) < 2) {
            $this->errors['name'] = 'Name must be at least 2 characters';
        }

        $method = $data['register_method'] ?? 'email';

        if ($method === 'email') {
            if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $this->errors['email'] = 'Please enter a valid email address';
            }
        } else { // phone
            $phone = $data['phone'] ?? '';
            if (empty($phone) || $phone === '+95') {
                $this->errors['phone'] = 'Please enter a valid phone number';
            } elseif (!preg_match('/^\+95[0-9]{7,10}$/', $phone)) {
                $this->errors['phone'] = 'Please enter a valid Myanmar phone number (+95XXXXXXXXX)';
            }
        }

        if (empty($data['password'])) {
            $this->errors['password'] = 'Password is required';
        } elseif (strlen($data['password']) < 8) {
            $this->errors['password'] = 'Password must be at least 8 characters';
        }

        if (!empty($data['password']) && strlen($data['password']) >= 8) {
            if (!preg_match('/[A-Z]/', $data['password'])) {
                $this->errors['password'] = 'Password must contain at least one uppercase letter';
            }
            if (!preg_match('/[a-z]/', $data['password'])) {
                $this->errors['password'] = 'Password must contain at least one lowercase letter';
            }
            if (!preg_match('/[0-9]/', $data['password'])) {
                $this->errors['password'] = 'Password must contain at least one number';
            }
            if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $data['password'])) {
                $this->errors['password'] = 'Password must contain at least one special character';
            }
        }

        if (empty($data['confirm_password'])) {
            $this->errors['confirm_password'] = 'Please confirm your password';
        } elseif ($data['password'] !== $data['confirm_password']) {
            $this->errors['confirm_password'] = 'Passwords do not match';
        }

        if (empty($data['terms']) || $data['terms'] !== 'on') {
            $this->errors['terms'] = 'You must agree to the Terms of Service';
        }

        return empty($this->errors);
    }

    public function validateLogin(array $data): bool
    {
        $this->errors = [];

        $email = trim($data['email'] ?? '');
        $phone = trim($data['phone'] ?? '');

        if (empty($email) && (empty($phone) || $phone === '+95')) {
            $this->errors['general'] = 'Email or Phone is required';
            return false;
        }

        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Please enter a valid email address';
        }

        if (!empty($phone) && $phone !== '+95') {
            $normalized = $this->normalizePhone($phone);
            if (!preg_match('/^\+95[0-9]{7,10}$/', $normalized)) {
                $this->errors['phone'] = 'Please enter a valid Myanmar phone number (+95XXXXXXXXX)';
            }
        }

        if (empty($data['password'])) {
            $this->errors['password'] = 'Password is required';
        }

        return empty($this->errors);
    }

    private function normalizePhone(string $phone): string
    {
        $phone = trim($phone);
        
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);
        
        if (preg_match('/^0[0-9]{9,10}$/', $phone)) {
            return '+95' . substr($phone, 1);
        }
        
        if (preg_match('/^9[0-9]{7,9}$/', $phone)) {
            return '+95' . $phone;
        }
        
        return $phone;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function reset(): void
    {
        $this->errors = [];
    }
}