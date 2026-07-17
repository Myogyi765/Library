<?php
$loan = $loan ?? null;
$user = $user ?? null;
$book = $book ?? null;
$pageTitle = 'Scan Result';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Result - Library</title>
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
            max-width: 600px;
            width: 100%;
            background: white;
            border-radius: 16px;
            padding: 35px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.12);
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
        }
        .header h1 {
            font-size: 24px;
            font-weight: 700;
            color: #1a1a2e;
        }
        .header .icon {
            font-size: 50px;
            color: #4f46e5;
            margin-bottom: 8px;
        }
        .card {
            background: #f8fafc;
            padding: 16px 20px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            margin-bottom: 12px;
        }
        .card .label {
            font-size: 12px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        .card .value {
            font-size: 16px;
            font-weight: 600;
            color: #1a1a2e;
            margin-top: 2px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        .status-active { background: #dcfce7; color: #16a34a; }
        .status-returned { background: #e2e8f0; color: #475569; }
        .status-pending { background: #fef3c7; color: #d97706; }
        .btn {
            padding: 12px 24px;
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
        }
        .btn-success {
            background: #22c55e;
            color: white;
        }
        .btn-success:hover {
            background: #16a34a;
        }
        .btn-secondary {
            background: #e2e8f0;
            color: #1a1a2e;
        }
        .btn-secondary:hover {
            background: #cbd5e1;
        }
        .actions {
            display: flex;
            gap: 12px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        .flash-message {
            padding: 12px 18px;
            border-radius: 10px;
            margin-bottom: 15px;
            font-weight: 500;
        }
        .flash-success { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }
        .flash-error { background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="icon"><i class="fas fa-qrcode"></i></div>
        <h1>📖 Scan Result</h1>
        <p style="color: #94a3b8; font-size: 14px;">Loan details retrieved from QR Code</p>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="flash-message flash-success">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success_message']) ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="flash-message flash-error">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error_message']) ?>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <?php if ($loan && $user && $book): ?>
        <div class="card">
            <div class="label"><i class="fas fa-user"></i> User</div>
            <div class="value"><?= htmlspecialchars($user->getName()) ?></div>
            <div style="font-size:13px; color:#64748b; margin-top:2px;">
                <?= htmlspecialchars($user->getEmail()->getValue()) ?> | 
                <?= $user->getPhone() ? htmlspecialchars($user->getPhone()->getValue()) : 'No phone' ?>
            </div>
        </div>

        <div class="card">
            <div class="label"><i class="fas fa-book"></i> Book</div>
            <div class="value"><?= htmlspecialchars($book->getTitle()) ?></div>
            <div style="font-size:13px; color:#64748b; margin-top:2px;">
                <?= htmlspecialchars($book->getAuthor()) ?> | ISBN: <?= htmlspecialchars($book->getIsbn()) ?>
            </div>
        </div>

        <div class="card">
            <div class="label"><i class="fas fa-calendar-alt"></i> Loan Details</div>
            <div class="value">Loan #<?= $loan->getId() ?></div>
            <div style="font-size:13px; color:#64748b; margin-top:2px;">
                Borrowed: <?= $loan->getBorrowedAt() ? $loan->getBorrowedAt()->format('d M Y') : 'N/A' ?> | 
                Due: <?= $loan->getDueDate() ? $loan->getDueDate()->format('d M Y') : 'N/A' ?>
            </div>
        </div>

        <div class="card">
            <div class="label"><i class="fas fa-info-circle"></i> Status</div>
            <div class="value">
                <?php
                $status = $loan->getStatus()->getValue();
                $statusClass = 'status-' . $status;
                ?>
                <span class="status-badge <?= $statusClass ?>">
                    <?= ucfirst(str_replace('_', ' ', $status)) ?>
                </span>
            </div>
        </div>

        <?php if ($loan->getStatus()->getValue() === 'active'): ?>
            <div class="actions">
                <form action="<?= BASE_URL ?>/librarian/scan/return" method="POST" style="flex:1;">
                    <input type="hidden" name="loan_id" value="<?= $loan->getId() ?>">
                    <button type="submit" class="btn btn-success" style="width:100%; justify-content:center;">
                        <i class="fas fa-undo-alt"></i> Return Book Now
                    </button>
                </form>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div style="margin-top:15px; text-align:center;">
        <a href="<?= BASE_URL ?>/librarian/dashboard?page=loans" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Loans
        </a>
    </div>
</div>
</body>
</html>