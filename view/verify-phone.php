<?php
$pageTitle = $title ?? 'Verify Phone';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Library Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body class="bg-gray-50 dark:bg-gray-950 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-8">
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-phone text-4xl text-blue-600 dark:text-blue-400"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Verify Your Phone</h1>
                <p class="text-gray-600 dark:text-gray-300 mt-2">Enter the 6-digit code sent to your phone</p>
            </div>
            
            <?php if (isset($message) && $message): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $success ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!$success || !isset($success)): ?>
            <form action="<?php echo BASE_URL; ?>/verify-phone" method="POST" class="space-y-6">
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Verification Code
                    </label>
                    <input 
                        type="text" 
                        id="code" 
                        name="code" 
                        maxlength="6"
                        placeholder="Enter 6-digit code"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white text-center text-2xl tracking-widest"
                        required
                        autocomplete="one-time-code"
                        pattern="[0-9]{6}"
                        inputmode="numeric"
                    >
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        Enter the 6-digit code sent to your phone
                    </p>
                </div>
                
                <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
                    <i class="fas fa-check mr-2"></i> Verify Phone
                </button>
            </form>
            <?php else: ?>
                <div class="text-center">
                    <a href="<?php echo BASE_URL; ?>/user-dashboard" class="inline-block px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
                        <i class="fas fa-arrow-right mr-2"></i> Go to Dashboard
                    </a>
                </div>
            <?php endif; ?>
            
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Didn't receive the code?
                    <a href="<?php echo BASE_URL; ?>/resend-verification" class="text-blue-600 dark:text-blue-400 hover:underline font-medium">
                        Resend Code
                    </a>
                </p>
                <a href="<?php echo BASE_URL; ?>/home" class="text-sm text-gray-500 dark:text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 mt-2 inline-block">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Home
                </a>
            </div>
        </div>
    </div>
</body>
</html>