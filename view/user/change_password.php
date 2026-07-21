<?php
$baseUrl = BASE_URL ?? '';
$user = $user ?? null;
$error = $_SESSION['error_message'] ?? null;
$success = $_SESSION['success_message'] ?? null;

// Clear session messages after displaying
unset($_SESSION['error_message']);
unset($_SESSION['success_message']);



include_once __DIR__ . '/../layout/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .password-container {
            max-width: 500px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(14, 96, 189, 0.1);
        }
        .password-container h2 {
            margin-bottom: 25px;
            color: #1e4dd1;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            font-weight: 500;
            color: #555;
        }
        .form-control {
            border-radius: 8px;
            padding: 8px 8px;
        }
        .btn-primary {
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 500;
        }
        .password-strength {
            margin-top: 5px;
            font-size: 0.9rem;
        }
        .alert {
            border-radius: 8px;
        }
        .back-link {
            display: inline-block;
            margin-top: 15px;
            color: #6c757d;
            text-decoration: none;
        }
        .back-link:hover {
            color: #333;
            text-decoration: underline;
        }
        .password-requirements {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }
        .password-requirements ul {
            margin: 5px 0 0 0;
            padding-left: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="password-container">
            <h2><i class="fas fa-key me-2"></i>Change Password</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="<?= $baseUrl ?>/change-password" method="POST">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-requirements">
                        <ul>
                            <li>Must be at least 6 characters long</li>
                            <li>Should contain a mix of letters and numbers</li>
                        </ul>
                    </div>
                    <div class="password-strength" id="passwordStrength"></div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div id="passwordMatch" class="mt-1"></div>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-save me-2"></i>Change Password
                </button>
            </form>

            <a href="<?= $baseUrl ?>/profile" class="back-link">
                <i class="fas fa-arrow-left me-1"></i>Back to Profile
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const button = input.nextElementSibling;
            const icon = button.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }

        // Password strength indicator
        document.getElementById('new_password').addEventListener('input', function() {
            const password = this.value;
            const strengthDiv = document.getElementById('passwordStrength');
            let strength = 0;
            let message = '';
            let color = '';

            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[@#$%^&*!]/)) strength++;

            if (password.length === 0) {
                message = '';
                color = '';
            } else if (strength <= 2) {
                message = 'Weak';
                color = '#dc3545';
            } else if (strength <= 3) {
                message = 'Medium';
                color = '#ffc107';
            } else if (strength <= 4) {
                message = 'Strong';
                color = '#28a745';
            } else {
                message = 'Very Strong';
                color = '#17a2b8';
            }

            strengthDiv.innerHTML = message ? `<span style="color: ${color}; font-weight: 500;">Strength: ${message}</span>` : '';
        });

        // Password match validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('new_password').value;
            const confirm = this.value;
            const matchDiv = document.getElementById('passwordMatch');

            if (confirm.length === 0) {
                matchDiv.innerHTML = '';
                return;
            }

            if (password === confirm) {
                matchDiv.innerHTML = '<span style="color: #28a745;"><i class="fas fa-check-circle me-1"></i>Passwords match</span>';
            } else {
                matchDiv.innerHTML = '<span style="color: #dc3545;"><i class="fas fa-times-circle me-1"></i>Passwords do not match</span>';
            }
        });

        // Validate form before submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('new_password').value;
            const confirm = document.getElementById('confirm_password').value;

            if (password !== confirm) {
                e.preventDefault();
                alert('Passwords do not match!');
                return;
            }

            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long!');
                return;
            }
        });
    </script>
</body>
</html>

<?php
// Include footer
include_once __DIR__ . '/../layout/footer.php';
?>