<?php
$message = $message ?? 'Invalid QR Code.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Error</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 500px;
            width: 100%;
            background: white;
            border-radius: 16px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.12);
        }
        .error-icon {
            font-size: 60px;
            color: #dc2626;
            margin-bottom: 15px;
        }
        h1 { color: #1a1a2e; font-size: 24px; }
        p { color: #64748b; margin: 10px 0 20px; }
        .btn {
            padding: 12px 28px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: 0.25s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #4f46e5;
            color: white;
        }
        .btn:hover { background: #4338ca; }
    </style>
</head>
<body>
<div class="container">
    <div class="error-icon"><i class="fas fa-exclamation-triangle"></i></div>
    <h1>Scan Error</h1>
    <p><?= htmlspecialchars($message) ?></p>
    <a href="<?= BASE_URL ?>/librarian/dashboard?page=loans" class="btn">
        <i class="fas fa-arrow-left"></i> Back to Loans
    </a>
</div>
</body>
</html>