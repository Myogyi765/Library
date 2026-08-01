<?php
$loan = $loan ?? null;
$user = $user ?? null;
$book = $book ?? null;
$fine = $fine ?? 0;
$overdueDays = $overdueDays ?? 0;
$isOverdue = $isOverdue ?? false;
$pageTitle = 'Scan Result';

$status = $loan ? $loan->getStatus()->getValue() : '';
$isActive = $status === 'active';
$isAwaitingPayment = $status === 'awaiting_payment';
$isReturned = $status === 'returned';

// Format fine
$fineFormatted = number_format($fine);

// Check if current user is librarian
$isLibrarian = ($_SESSION['user_role'] ?? '') === 'librarian';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Result - Library</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ── CSS Variables ── */
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --success: #22c55e;
            --success-hover: #16a34a;
            --warning: #f59e0b;
            --warning-hover: #d97706;
            --danger: #dc2626;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --gray-900: #0f172a;
            --radius: 12px;
            --shadow: 0 20px 60px rgba(0,0,0,0.08);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #f1f5f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            line-height: 1.6;
        }

        .container {
            max-width: 650px;
            width: 100%;
            background: #ffffff;
            border-radius: var(--radius);
            padding: 30px 32px 40px;
            box-shadow: var(--shadow);
            border: 1px solid var(--gray-200);
        }

        /* ── Header ── */
        .header {
            text-align: center;
            margin-bottom: 28px;
        }
        .header .icon {
            font-size: 52px;
            color: var(--primary);
            margin-bottom: 6px;
        }
        .header h1 {
            font-size: 26px;
            font-weight: 700;
            color: var(--gray-900);
            letter-spacing: -0.5px;
        }
        .header .subtitle {
            color: var(--gray-500);
            font-size: 14px;
            margin-top: 2px;
        }

        /* ── Flash Messages ── */
        .flash-message {
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 16px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            border-left: 4px solid transparent;
        }
        .flash-success {
            background: #f0fdf4;
            border-color: #22c55e;
            color: #166534;
        }
        .flash-error {
            background: #fef2f2;
            border-color: #dc2626;
            color: #991b1b;
        }
        .flash-warning {
            background: #fffbeb;
            border-color: #f59e0b;
            color: #92400e;
        }

        /* ── Cards ── */
        .card {
            background: var(--gray-50);
            padding: 16px 20px;
            border-radius: var(--radius);
            border: 1px solid var(--gray-200);
            margin-bottom: 12px;
            transition: border-color 0.2s;
        }
        .card:hover {
            border-color: var(--gray-300);
        }
        .card .label {
            font-size: 12px;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            margin-bottom: 2px;
        }
        .card .value {
            font-size: 17px;
            font-weight: 600;
            color: var(--gray-900);
        }
        .card .sub {
            font-size: 13px;
            color: var(--gray-500);
            margin-top: 2px;
        }

        /* ── Status Badge ── */
        .status-badge {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 600;
        }
        .status-active   { background: #dcfce7; color: #16a34a; }
        .status-returned { background: var(--gray-200); color: var(--gray-600); }
        .status-pending  { background: #fef3c7; color: #d97706; }
        .status-overdue  { background: #fee2e2; color: #dc2626; }
        .status-awaiting_payment { background: #ffedd5; color: #ea580c; }
        .status-rejected { background: var(--gray-200); color: var(--gray-500); }

        /* ── Grid for Overdue & Fine ── */
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        .card-overdue {
            background: #fef2f2;
            border-color: #fca5a5;
        }
        .card-fine {
            background: #fef2f2;
            border-color: #fca5a5;
        }
        .card-fine .value,
        .card-overdue .value {
            color: var(--danger);
        }
        .card-ok {
            background: #f0fdf4;
            border-color: #86efac;
        }
        .card-ok .value {
            color: #16a34a;
        }

        /* ── Warning Box ── */
        .warning-box {
            background: #fef2f2;
            border: 1px solid #fca5a5;
            border-radius: 10px;
            padding: 14px 18px;
            color: #991b1b;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .warning-box i {
            font-size: 22px;
            color: #dc2626;
            flex-shrink: 0;
        }

        /* ── Buttons ── */
        .btn {
            padding: 12px 28px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-success {
            background: var(--success);
            color: white;
        }
        .btn-success:hover {
            background: var(--success-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(34,197,94,0.3);
        }
        .btn-success:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        .btn-secondary {
            background: var(--gray-200);
            color: var(--gray-800);
        }
        .btn-secondary:hover {
            background: var(--gray-300);
            transform: translateY(-1px);
        }
        .btn-secondary-outline {
            background: transparent;
            color: var(--gray-600);
            border: 1.5px solid var(--gray-300);
            padding: 10px 24px;
        }
        .btn-secondary-outline:hover {
            background: var(--gray-50);
            border-color: var(--gray-400);
            transform: translateY(-1px);
        }
        .btn-warning {
            background: var(--warning);
            color: white;
        }
        .btn-warning:hover {
            background: var(--warning-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(245,158,11,0.3);
        }

        /* ── Actions ── */
        .actions {
            display: flex;
            gap: 12px;
            margin-top: 24px;
            flex-wrap: wrap;
            align-items: center;
        }
        .actions .flex-1 {
            flex: 1;
            min-width: 200px;
        }
        .actions .flex-2 {
            flex: 2;
            min-width: 200px;
        }

        /* ── Bottom Navigation ── */
        .bottom-nav {
            display: flex;
            gap: 16px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--gray-200);
            justify-content: center;
            flex-wrap: wrap;
        }
        .bottom-nav .btn {
            min-width: 140px;
        }

        /* ── Utility ── */
        .mt-2 { margin-top: 12px; }
        .text-center { text-align: center; }
        .w-full { width: 100%; }

        /* ── Responsive ── */
        @media (max-width: 480px) {
            .container {
                padding: 20px 16px;
            }
            .grid-2 {
                grid-template-columns: 1fr;
            }
            .actions {
                flex-direction: column;
            }
            .actions .flex-1,
            .actions .flex-2 {
                min-width: unset;
                width: 100%;
            }
            .btn {
                width: 100%;
                justify-content: center;
            }
            .bottom-nav {
                flex-direction: column;
                align-items: center;
            }
            .bottom-nav .btn {
                width: 100%;
            }
            .header h1 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Header -->
    <div class="header">
        <div class="icon"><i class="fas fa-qrcode"></i></div>
        <h1>Scan Result</h1>
        <p class="subtitle">Loan details retrieved from QR Code</p>
    </div>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="flash-message flash-success">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success_message']) ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['warning_message'])): ?>
        <div class="flash-message flash-warning">
            <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($_SESSION['warning_message']) ?>
        </div>
        <?php unset($_SESSION['warning_message']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="flash-message flash-error">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error_message']) ?>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <?php if ($loan && $user && $book): ?>
        <!-- User -->
        <div class="card">
            <div class="label"><i class="fas fa-user"></i> User</div>
            <div class="value"><?= htmlspecialchars($user->getName()) ?></div>
            <div class="sub">
                <?= htmlspecialchars($user->getEmail()->getValue()) ?> |
                <?= $user->getPhone() ? htmlspecialchars($user->getPhone()->getValue()) : 'No phone' ?>
            </div>
        </div>

        <!-- Book -->
        <div class="card">
            <div class="label"><i class="fas fa-book"></i> Book</div>
            <div class="value"><?= htmlspecialchars($book->getTitle()) ?></div>
            <div class="sub">
                <?= htmlspecialchars($book->getAuthor()) ?> | ISBN: <?= htmlspecialchars($book->getIsbn()) ?>
            </div>
        </div>

        <!-- Loan Details -->
        <div class="card">
            <div class="label"><i class="fas fa-calendar-alt"></i> Loan Details</div>
            <div class="value">Loan #<?= $loan->getId() ?></div>
            <div class="sub">
                Borrowed: <?= $loan->getBorrowedAt() ? $loan->getBorrowedAt()->format('d M Y') : 'N/A' ?> |
                Due: <?= $loan->getDueDate() ? $loan->getDueDate()->format('d M Y') : 'N/A' ?>
            </div>
        </div>

        <!-- Overdue & Fine -->
        <div class="grid-2">
            <div class="card <?= $isOverdue ? 'card-overdue' : 'card-ok' ?>">
                <div class="label"><i class="fas fa-clock"></i> Overdue Days</div>
                <div class="value">
                    <?php if ($isOverdue): ?>
                        <span style="color:#dc2626;"><?= $overdueDays ?> days</span>
                    <?php else: ?>
                        <span style="color:#16a34a;">0 days</span>
                    <?php endif; ?>
                </div>
                <?php if ($isOverdue): ?>
                    <div class="sub" style="color:#dc2626; font-weight:500;">
                        <i class="fas fa-exclamation-triangle"></i> Overdue!
                    </div>
                <?php endif; ?>
            </div>

            <div class="card <?= $fine > 0 ? 'card-fine' : 'card-ok' ?>">
                <div class="label"><i class="fas fa-money-bill-wave"></i> Fine (MMK)</div>
                <div class="value">
                    <?php if ($fine > 0): ?>
                        <span style="color:#dc2626;"><?= $fineFormatted ?></span>
                    <?php else: ?>
                        <span style="color:#16a34a;">0</span>
                    <?php endif; ?>
                </div>
                <?php if ($fine > 0): ?>
                    <div class="sub" style="color:#dc2626; font-weight:500;">
                        <i class="fas fa-coins"></i> Fine applies
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Overdue Warning -->
        <?php if ($isOverdue && $isActive): ?>
            <div class="warning-box">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>Overdue!</strong> This book is overdue by <strong><?= $overdueDays ?> days</strong>.
                    Fine of <strong><?= $fineFormatted ?> MMK</strong> must be paid before returning.
                </div>
            </div>
        <?php endif; ?>

        <!-- Status -->
        <div class="card">
            <div class="label"><i class="fas fa-info-circle"></i> Status</div>
            <div class="value">
                <?php
                $statusClass = 'status-' . $status;
                $displayStatus = ucfirst(str_replace('_', ' ', $status));
                if ($status === 'active' && $isOverdue) {
                    $displayStatus = 'Overdue';
                    $statusClass = 'status-overdue';
                }
                ?>
                <span class="status-badge <?= $statusClass ?>">
                    <?= $displayStatus ?>
                </span>
                <?php if ($status === 'awaiting_payment'): ?>
                    <span style="font-size:12px; color:#ea580c; display:block; margin-top:4px;">
                        <i class="fas fa-clock"></i> Awaiting fine payment
                    </span>
                <?php endif; ?>
                <?php if ($status === 'returned'): ?>
                    <span style="font-size:12px; color:#475569; display:block; margin-top:4px;">
                        <i class="fas fa-check"></i> Returned successfully
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Actions -->
        <div class="actions">
            <?php if ($status === 'active'): ?>
                <?php if ($fine > 0): ?>
                    <?php if ($isLibrarian): ?>
                        <!-- Librarian: show message only -->
                        <div class="warning-box" style="width:100%; margin-bottom:0;">
                            <i class="fas fa-info-circle"></i>
                            <div>
                                <strong>Fine Due!</strong> The user must pay the fine of <strong><?= $fineFormatted ?> MMK</strong> before returning.
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- User: show Pay Fine button -->
                        <button class="btn btn-success" disabled style="flex:1; justify-content:center;">
                            <i class="fas fa-lock"></i> Pay Fine First
                        </button>
                        <a href="<?= BASE_URL ?>/payment/submit/<?= $loan->getId() ?>?type=fine" class="btn btn-warning" style="flex:1; justify-content:center;">
                            <i class="fas fa-money-bill-wave"></i> Pay Fine
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- No fine – return normally -->
                    <form action="<?= BASE_URL ?>/librarian/scan/return" method="POST" style="flex:1;">
                        <input type="hidden" name="loan_id" value="<?= $loan->getId() ?>">
                        <button type="submit" class="btn btn-success" style="width:100%; justify-content:center;">
                            <i class="fas fa-undo-alt"></i> Return Book Now
                        </button>
                    </form>
                <?php endif; ?>
            <?php elseif ($status === 'awaiting_payment'): ?>
                <?php if ($isLibrarian): ?>
                    <div class="warning-box" style="width:100%; margin-bottom:0;">
                        <i class="fas fa-clock"></i>
                        <div>
                            <strong>Awaiting Payment.</strong> The user must pay the fine before the book can be returned.
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/payment/submit/<?= $loan->getId() ?>?type=fine" class="btn btn-warning" style="flex:1; justify-content:center;">
                        <i class="fas fa-money-bill-wave"></i> Pay Fine
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Bottom Navigation -->
        <div class="bottom-nav">
            <a href="<?= BASE_URL ?>/librarian/scanner" class="btn btn-secondary-outline">
                <i class="fas fa-qrcode"></i> Scan Again
            </a>
            <a href="<?= BASE_URL ?>/librarian/dashboard?page=loans" class="btn btn-secondary-outline">
                <i class="fas fa-arrow-left"></i> Back to Loans
            </a>
        </div>

    <?php else: ?>
        <!-- No data -->
        <div class="card" style="text-align:center; padding:40px 20px;">
            <i class="fas fa-exclamation-circle" style="font-size:48px; color:#94a3b8; display:block; margin-bottom:12px;"></i>
            <p style="color:#64748b; font-size:16px;">No loan details found.</p>
            <div style="margin-top:16px;">
                <a href="<?= BASE_URL ?>/librarian/scanner" class="btn btn-secondary">
                    <i class="fas fa-qrcode"></i> Scan Again
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>
</body>
</html>