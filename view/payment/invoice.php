<?php
// Data passed from controller
$invoice_number = $invoice_number ?? 'INV-' . date('Y') . '-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
$date = $date ?? date('d M Y');
$user = $user ?? null;
$book = $book ?? null;
$loan = $loan ?? null;
$payment = $payment ?? null;
$borrowed_at = $borrowed_at ?? null;
$due_date = $due_date ?? null;
$qrCode = $qrCode ?? null;

// User details
$userName = $user ? $user->getName() : 'N/A';
$userId = $user ? 'U' . str_pad($user->getId(), 5, '0', STR_PAD_LEFT) : 'N/A';
$userEmail = $user ? $user->getEmail()->getValue() : 'N/A';
$userPhone = $user && $user->getPhone() ? $user->getPhone()->getValue() : 'N/A';

// ✅ Profile Image – check if file exists
$hasProfileImage = false;
$userProfileImage = BASE_URL . '/public/images/default-avatar.png';

if ($user && $user->getProfileImage()) {
    $imagePath = BASE_PATH . '/public/' . $user->getProfileImage();
    if (file_exists($imagePath)) {
        $hasProfileImage = true;
        $userProfileImage = BASE_URL . '/' . $user->getProfileImage();
    }
}

// Book details
$bookTitle = $book ? $book->getTitle() : 'N/A';
$bookAuthor = $book ? $book->getAuthor() : 'N/A';
$bookIsbn = $book ? $book->getIsbn() : 'N/A';
$bookId = $book ? 'B-' . str_pad($book->getId(), 6, '0', STR_PAD_LEFT) : 'N/A';

// Payment details
$amount = $payment && $payment->getAmount() ? $payment->getAmount()->getAmount() : 0;
$paymentMethod = $payment ? ucfirst($payment->getPaymentMethod()) : 'N/A';
$transactionId = $payment ? $payment->getTransactionReference() : 'TXN-' . rand(100000, 999999);

// Dates
$borrowedDate = $borrowed_at ? $borrowed_at->format('d M Y') : 'N/A';
$dueDate = $due_date ? $due_date->format('d M Y') : 'N/A';

// Days left
$daysLeft = 0;
$isOverdue = false;
if ($due_date) {
    $now = new \DateTimeImmutable();
    $daysLeft = $now->diff($due_date)->days;
    if ($due_date < $now) {
        $isOverdue = true;
        $daysLeft = $now->diff($due_date)->days;
    }
}
$isLibrarian = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'librarian';
if ($isLibrarian) {
    // ✅ Redirect librarian to the dashboard payment page
    $backUrl = BASE_URL . '/librarian/dashboard?page=payments';
    $doneUrl = BASE_URL . '/librarian/dashboard?page=payments';
} else {
    // ✅ Redirect regular users to their dashboard
    $backUrl = BASE_URL . '/user-dashboard';
    $doneUrl = BASE_URL . '/user-dashboard';
}

$logoPath = BASE_URL . '/images/logo.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - <?= $invoice_number ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 15px;
        }

        .invoice-wrapper {
            max-width: 650px;
            width: 100%;
            background: #ffffff;
            border-radius: 6px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .invoice-header {
            background: linear-gradient(135deg, #3165a9, #2d599e);
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
        }

        .invoice-header .brand {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .invoice-header .brand img {
            height: 30px;
            width: 30px;
            border-radius: 50%;
            background: white;
            padding: 2px;
            object-fit: contain;
            display: block;
            box-sizing: border-box;
        }

        .invoice-header .brand #logo-fallback {
            background: white;
            padding: 6px;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            color: #294d7c;
            font-size: 16px;
            display: none;
            align-items: center;
            justify-content: center;
        }

        .invoice-header .brand h1 {
            color: #ffffff;
            font-size: 16px;
            font-weight: 600;
        }

        .invoice-header .invoice-meta {
            text-align: right;
            color: rgba(255, 255, 255, 0.9);
        }

        .invoice-header .invoice-meta .inv-number {
            font-size: 13px;
            font-weight: 600;
        }

        .invoice-header .invoice-meta .inv-number i {
            margin-right: 3px;
        }

        .invoice-header .invoice-meta .inv-date {
            font-size: 11px;
            opacity: 0.8;
        }

        .invoice-body {
            padding: 14px 18px 18px;
        }

        .invoice-title {
            text-align: center;
            margin-bottom: 12px;
        }

        .invoice-title h2 {
            font-size: 17px;
            font-weight: 700;
            color: #1a1a2e;
        }

        .invoice-title h2 span {
            color: #1e3a5f;
        }

        .invoice-title .sub {
            font-size: 11px;
            color: #94a3b8;
        }

        /* ─── User Info Block ────────────────────────────────────────── */
        .user-info-block {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            background: #f8fafc;
            padding: 8px 14px;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
            margin-bottom: 14px;
        }

        .user-info-block .user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid #1e3a5f;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e2e8f0;
        }

        .user-info-block .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* ✅ Profile Icon Fallback (when no image) */
        .user-info-block .user-avatar .profile-icon {
            color: #1e3a5f;
            font-size: 16px;
            background: #e2e8f0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .user-info-block .user-detail {
            flex: 1;
        }

        .user-info-block .user-detail .name {
            font-size: 14px;
            font-weight: 700;
            color: #1a1a2e;
        }

        .user-info-block .user-detail .id {
            font-size: 11px;
            color: #64748b;
        }

        .user-info-block .user-contact {
            display: flex;
            gap: 12px;
            font-size: 11px;
            color: #475569;
            flex-wrap: wrap;
        }

        .user-info-block .user-contact i {
            color: #1e3a5f;
            width: 14px;
        }

        .section-divider {
            border: none;
            border-top: 1px dashed #dce0e5;
            margin: 12px 0;
        }

        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 14px;
        }

        .details-box {
            background: #f8fafc;
            padding: 8px 12px;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
        }

        .details-box .box-title {
            font-size: 10px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 4px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 3px;
        }

        .details-box .box-title i {
            color: #1e3a5f;
        }

        .details-box .item {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
            font-size: 12px;
            gap: 10px; /* ✅ Added gap between label and value for better spacing */
        }

        .details-box .item .label {
            color: #64748b;
            flex-shrink: 0; /* prevent label from shrinking */
        }

        .details-box .item .value {
            font-weight: 600;
            color: #1a1a2e;
            text-align: right;
            word-break: break-word;
        }

        .timeline {
            display: flex;
            justify-content: space-around;
            align-items: center;
            background: #f8fafc;
            padding: 8px 14px;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
            margin-bottom: 14px;
            flex-wrap: wrap;
            gap: 4px;
        }

        .timeline .item {
            text-align: center;
        }

        .timeline .item .label {
            font-size: 9px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-weight: 600;
        }

        .timeline .item .value {
            font-size: 12px;
            font-weight: 600;
            color: #1a1a2e;
        }

        .timeline .item .value.overdue {
            color: #dc2626;
        }

        .timeline .divider {
            width: 1px;
            height: 20px;
            background: #e2e8f0;
        }

        .qr-block {
            display: flex;
            align-items: center;
            gap: 14px;
            background: #f8fafc;
            padding: 10px 14px;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
            margin-bottom: 14px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .qr-block .qr-code {
            background: white;
            padding: 4px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
        }

        .qr-block .qr-code img {
            width: 60px;
            height: 60px;
            display: block;
        }

        .qr-block .qr-info {
            color: #64748b;
            font-size: 11px;
        }

        .qr-block .qr-info strong {
            color: #1a1a2e;
        }

        .qr-block .qr-info .scan-label {
            font-size: 9px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-weight: 600;
        }

        .invoice-footer {
            text-align: center;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
        }

        .invoice-footer .warning {
            color: #dc2626;
            font-weight: 600;
            font-size: 11px;
            margin-bottom: 2px;
        }

        .invoice-footer .footer-text {
            color: #94a3b8;
            font-size: 10px;
        }

        .invoice-actions {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 14px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 5px 16px;
            border-radius: 4px;
            border: none;
            font-weight: 600;
            font-size: 12px;
            cursor: pointer;
            transition: 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .btn-primary {
            background: #305f9c;
            color: white;
        }

        .btn-primary:hover {
            background: #0f2840;
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #1a1a2e;
        }

        .btn-secondary:hover {
            background: #cbd5e1;
        }

        .btn-success {
            background: #22c55e;
            color: white;
        }

        .btn-success:hover {
            background: #16a34a;
        }

        @media (max-width: 480px) {
            .invoice-body { padding: 10px 12px 14px; }
            .invoice-header { padding: 10px 14px; flex-direction: column; text-align: center; }
            .invoice-header .invoice-meta { text-align: center; }
            .details-grid { grid-template-columns: 1fr; }
            .user-info-block { flex-direction: column; text-align: center; }
            .user-info-block .user-contact { justify-content: center; }
            .timeline { flex-direction: column; }
            .timeline .divider { display: none; }
            .qr-block { flex-direction: column; text-align: center; }
            .invoice-actions { flex-direction: column; align-items: center; }
            .btn { width: 100%; justify-content: center; }
        }

        @media print {
            body { background: white; padding: 0; }
            .invoice-wrapper { box-shadow: none; border: 1px solid #ddd; border-radius: 0; }
            .invoice-actions { display: none; }
            .invoice-header { background: #1e3a5f !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .btn { display: none; }
        }
    </style>
</head>
<body>

<div class="invoice-wrapper">
    <!-- ─── Header ──────────────────────────────────────────────────── -->
    <div class="invoice-header">
        <div class="brand">
            <img src="<?php echo BASE_URL; ?>/images/logo.png" alt="Library Logo" onerror="this.style.display='none'; document.getElementById('logo-fallback').style.display='flex';">
            <i id="logo-fallback" class="fas fa-book-open"></i>
            <h1>Library</h1>
        </div>
        <div class="invoice-meta">
            <div class="inv-number"><i class="fas fa-receipt"></i> <?= $invoice_number ?></div>
            <div class="inv-date"><i class="far fa-calendar-alt"></i> <?= $date ?></div>
        </div>
    </div>

    <!-- ─── Body ────────────────────────────────────────────────────── -->
    <div class="invoice-body">
        <!-- Title -->
        <div class="invoice-title">
            <h2>📄 <span>Booking/Borrowing</span> Details</h2>
            <div class="sub">Borrowing Confirmation</div>
        </div>

        <!-- User Info -->
        <div class="user-info-block">
            <div class="user-avatar">
                <?php if ($hasProfileImage): ?>
                    <img src="<?= $userProfileImage ?>" alt="Profile Image">
                <?php else: ?>
                    <span class="profile-icon"><i class="fas fa-user"></i></span>
                <?php endif; ?>
            </div>
            <div class="user-detail">
                <div class="name"><?= htmlspecialchars($userName) ?></div>
                <div class="id"><i class="fas fa-id-card"></i> <?= $userId ?></div>
            </div>
            <div class="user-contact">
                <span><i class="fas fa-envelope"></i> <?= htmlspecialchars($userEmail) ?></span>
                <span><i class="fas fa-phone"></i> <?= htmlspecialchars($userPhone) ?></span>
            </div>
        </div>

        <hr class="section-divider">

        <!-- Details Grid -->
        <div class="details-grid">
            <div class="details-box">
                <div class="box-title"><i class="fas fa-book"></i> Book Details</div>
                <div class="item"><span class="label">Title</span><span class="value"><?= htmlspecialchars($bookTitle) ?></span></div>
                <div class="item"><span class="label">Author</span><span class="value"><?= htmlspecialchars($bookAuthor) ?></span></div>
                <div class="item"><span class="label">ISBN</span><span class="value"><?= htmlspecialchars($bookIsbn) ?></span></div>
                <div class="item"><span class="label">Book ID</span><span class="value"><?= $bookId ?></span></div>
            </div>
            <div class="details-box">
                <div class="box-title"><i class="fas fa-credit-card"></i> Payment Details</div>
                <div class="item"><span class="label">Borrow Fee</span><span class="value"><?= number_format($amount, 0, ',', ',') ?> MMK</span></div>
                <div class="item"><span class="label">Method</span><span class="value"><?= htmlspecialchars($paymentMethod) ?></span></div>
                <div class="item"><span class="label">Transaction ID</span><span class="value"><?= htmlspecialchars($transactionId) ?></span></div>
            </div>
        </div>

        <!-- Timeline -->
        <div class="timeline">
            <div class="item">
                <div class="label"><i class="far fa-calendar-plus"></i> Borrow Date</div>
                <div class="value"><?= $borrowedDate ?></div>
            </div>
            <div class="divider"></div>
            <div class="item">
                <div class="label"><i class="far fa-calendar-check"></i> Due Date</div>
                <div class="value"><?= $dueDate ?></div>
            </div>
            <div class="divider"></div>
            <div class="item">
                <div class="label"><i class="far fa-clock"></i> <?= $isOverdue ? 'Overdue' : 'Days Left' ?></div>
                <div class="value <?= $isOverdue ? 'overdue' : '' ?>">
                    <?php if ($isOverdue): ?>
                        🔴 <?= $daysLeft ?> days overdue
                    <?php else: ?>
                        <?= $daysLeft ?> <?= $daysLeft <= 1 ? 'day' : 'days' ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- QR Code -->
        <div class="qr-block">
            <div class="qr-code">
                <?php if ($qrCode): ?>
                    <img src="<?= $qrCode ?>" alt="QR Code for Return">
                <?php else: ?>
                    <div style="width:60px;height:60px;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:10px;flex-direction:column;border:1px dashed #cbd5e1;border-radius:4px;">
                        <i class="fas fa-qrcode" style="font-size:20px;"></i>
                        <span>QR</span>
                    </div>
                <?php endif; ?>
            </div>
            <div class="qr-info">
                <div class="scan-label"><i class="fas fa-qrcode"></i> Scan QR Code</div>
                <p style="margin-top:2px; font-size:11px;">
                    <strong>User:</strong> <?= htmlspecialchars($userName) ?><br>
                    <strong>Book:</strong> <?= htmlspecialchars($bookTitle) ?><br>
                    <strong>Loan ID:</strong> #<?= $loan ? $loan->getId() : 'N/A' ?>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="invoice-footer">
            <p class="warning">⚠️ Please return the book on or before the due date.</p>
            <p class="footer-text">Late return may incur additional charges.</p>
        </div>

        <!-- Actions -->
        <div class="invoice-actions">
            <a href="<?= $backUrl ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Print
            </button>
            <a href="<?= $doneUrl ?>" class="btn btn-success">
                <i class="fas fa-hand-holding-heart"></i> Done
            </a>
        </div>
    </div>
</div>

</body>
</html>