<?php

namespace App\User\Presentation\Validator;

class UserValidator
{
    private array $errors = [];

    // ✅ Register Validation
    public function validateRegister(array $data): bool
    {
        $this->errors = [];

        // Validate name
        if (empty($data['name']) || strlen($data['name']) < 2) {
            $this->errors['name'] = 'Name must be at least 2 characters';
        }

        // Determine registration method
        $method = $data['register_method'] ?? 'email';

        // Validate email OR phone based on method
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

        // Validate password
        if (empty($data['password'])) {
            $this->errors['password'] = 'Password is required';
        } elseif (strlen($data['password']) < 8) {
            $this->errors['password'] = 'Password must be at least 8 characters';
        }

        // Validate password strength (optional - keep or remove as needed)
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

        // Validate confirm password
        if (empty($data['confirm_password'])) {
            $this->errors['confirm_password'] = 'Please confirm your password';
        } elseif ($data['password'] !== $data['confirm_password']) {
            $this->errors['confirm_password'] = 'Passwords do not match';
        }

        // Validate terms
        if (empty($data['terms']) || $data['terms'] !== 'on') {
            $this->errors['terms'] = 'You must agree to the Terms of Service';
        }

        return empty($this->errors);
    }

    // ✅ Login Validation
    public function validateLogin(array $data): bool
    {
        $this->errors = [];

        $email = trim($data['email'] ?? '');
        $phone = trim($data['phone'] ?? '');

        // Check if at least one is provided (email or phone)
        if (empty($email) && (empty($phone) || $phone === '+95')) {
            $this->errors['general'] = 'Email or Phone is required';
            return false;
        }

        // Validate email if provided
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Please enter a valid email address';
        }

        // Validate phone if provided and not empty
        if (!empty($phone) && $phone !== '+95') {
            $normalized = $this->normalizePhone($phone);
            if (!preg_match('/^\+95[0-9]{7,10}$/', $normalized)) {
                $this->errors['phone'] = 'Please enter a valid Myanmar phone number (+95XXXXXXXXX)';
            }
        }

        // Validate password
        if (empty($data['password'])) {
            $this->errors['password'] = 'Password is required';
        }

        return empty($this->errors);
    }

    private function normalizePhone(string $phone): string
    {
        $phone = trim($phone);
        
        // Remove spaces and special characters
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);
        
        // If phone starts with 0, convert to +95
        if (preg_match('/^0[0-9]{9,10}$/', $phone)) {
            return '+95' . substr($phone, 1);
        }
        
        // If phone is just digits and starts with 9
        if (preg_match('/^9[0-9]{7,9}$/', $phone)) {
            return '+95' . $phone;
        }
        
        return $phone;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    // ✅ Helper method to check if validation passed
    public function passes(): bool
    {
        return empty($this->errors);
    }

    // ✅ Helper method to check if validation failed
    public function fails(): bool
    {
        return !empty($this->errors);
    }

    // ✅ Reset errors
    public function reset(): void
    {
        $this->errors = [];
    }
}