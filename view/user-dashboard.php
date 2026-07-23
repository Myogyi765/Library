<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_authenticated']) || $_SESSION['user_authenticated'] !== true) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

$pageTitle = 'User Dashboard';

// Include header
include __DIR__ . '/layout/header.php';

// Data passed from controller: $loans (enriched array), $books, $user
// $loans is an array of arrays with keys: 'loan', 'fine', 'overdue_days', 'is_overdue'
$loans = $loans ?? [];
$books = $books ?? [];
$user = $user ?? null;

// ---- FIX: Convert $books to associative array by ID for easy lookup ----
$booksById = [];
foreach ($books as $book) {
    $booksById[$book->getId()] = $book;
}

// Calculate stats from enriched loans
$totalBorrowed = count($loans);
$activeLoans = 0;
$overdueLoans = 0;

foreach ($loans as $item) {
    $loan = $item['loan'];
    $statusString = $loan->getStatus()->getValue();
    if ($statusString === 'active') {
        $activeLoans++;
        if ($item['is_overdue']) {
            $overdueLoans++;
        }
    }
}

// ---- Permission Checks ----
$container = $GLOBALS['container'] ?? null;
$authorization = null;
$hasViewProfile = false;
$hasEditProfile = false;
$hasViewNotifications = false;

if ($container && $container->has(\App\Shared\Core\Authorization\Authorization::class)) {
    $authorization = $container->get(\App\Shared\Core\Authorization\Authorization::class);
    $hasViewProfile = $authorization->hasPermission('view_profile');
    $hasEditProfile = $authorization->hasPermission('edit_profile');
    $hasViewNotifications = $authorization->hasPermission('view_notifications');
}

// ---- Profile Image ----
$profileImage = $_SESSION['user_profile_image'] ?? null;
$profileImageUrl = $profileImage ? BASE_URL . '/' . $profileImage : null;

// ---- Payment & Invoice Repositories (for invoice link) ----
$paymentRepo = null;
$invoiceRepo = null;
try {
    if ($container) {
        $paymentRepo = $container->get(\App\Payment\Domain\Repository\PaymentRepositoryInterface::class);
        $invoiceRepo = $container->get(\App\Invoice\Domain\Repository\InvoiceRepositoryInterface::class);
    }
} catch (\Exception $e) {
    // Repositories not available – skip invoice links
}
?>

<style>
    /* ─── LOAN TABLE (Admin Style) ─── */
    .loan-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
    }
    .loan-table thead th {
        background: #f8fafc;
        color: #1e293b;
        font-weight: 600;
        padding: 12px 16px;
        border-bottom: 2px solid #e2e8f0;
        text-align: center;
        vertical-align: middle;
    }
    .dark .loan-table thead th {
        background: #1e293b;
        color: #e2e8f0;
        border-bottom-color: #334155;
    }
    .loan-table tbody td {
        padding: 12px 16px;
        vertical-align: middle;
        text-align: center;
    }
    .loan-table tbody tr {
        transition: background 0.15s;
    }
    .loan-table tbody tr:hover {
        background: #f8fafc;
    }
    .dark .loan-table tbody tr:hover {
        background: #1e293b;
    }

    /* ─── LEFT-ALIGN BOOK & AUTHOR COLUMNS ─── */
    .loan-table thead th:nth-child(2),
    .loan-table thead th:nth-child(3),
    .loan-table tbody td:nth-child(2),
    .loan-table tbody td:nth-child(3) {
        text-align: left;
    }

    /* ─── Book Cover Image ─── */
    .book-cover-thumb {
        width: 50px;
        height: 70px;
        object-fit: cover;
        border-radius: 6px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border: 1px solid #e2e8f0;
        background: #f8fafc;
    }
    .dark .book-cover-thumb {
        border-color: #334155;
        background: #0f172a;
    }
    .book-cover-placeholder {
        width: 50px;
        height: 70px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
        border-radius: 6px;
        color: #94a3b8;
        font-size: 1.5rem;
        border: 1px solid #e2e8f0;
    }
    .dark .book-cover-placeholder {
        background: #1e293b;
        border-color: #334155;
        color: #64748b;
    }

    /* Status Badges */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.2rem 0.7rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
        border: none;
    }
    .status-badge i { font-size: 0.6rem; }
    
    .status-badge.active { 
        background: #dcfce7; 
        color: #166534; 
    }
    .dark .status-badge.active { 
        background: #14532d; 
        color: #86efac; 
    }
    
    .status-badge.returned { 
        background: #f1f5f9; 
        color: #475569; 
    }
    .dark .status-badge.returned { 
        background: #334155; 
        color: #94a3b8; 
    }
    
    .status-badge.overdue { 
        background: #fee2e2; 
        color: #991b1b; 
    }
    .dark .status-badge.overdue { 
        background: #7f1d1d; 
        color: #fca5a5; 
    }
    
    .status-badge.pending { 
        background: #fef3c7; 
        color: #92400e; 
    }
    .dark .status-badge.pending { 
        background: #78350f; 
        color: #fcd34d; 
    }
    
    .status-badge.awaiting_payment { 
        background: #ffedd5; 
        color: #9a3412; 
    }
    .dark .status-badge.awaiting_payment { 
        background: #7c2d12; 
        color: #fdba74; 
    }
    
    .status-badge.rejected { 
        background: #f1f5f9; 
        color: #475569; 
    }
    .dark .status-badge.rejected { 
        background: #334155; 
        color: #94a3b8; 
    }

    /* Action Icons */
    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 6px;
        transition: all 0.15s;
        background: transparent;
        border: none;
        cursor: pointer;
        text-decoration: none;
        font-size: 0.9rem;
        margin: 0 2px;
        color: #3b82f6;
    }
    .action-btn:hover {
        transform: scale(1.15);
        background: rgba(0,0,0,0.05);
    }
    .dark .action-btn:hover {
        background: rgba(255,255,255,0.08);
    }
    .action-btn.invoice { color: #3b82f6; }
    .action-btn.invoice:hover { background: #eff6ff; color: #2563eb; }
    .dark .action-btn.invoice { color: #60a5fa; }
    .dark .action-btn.invoice:hover { background: #1e293b; color: #93c5fd; }
    
    .action-btn.refunded { color: #22c55e; }
    .action-btn.refunded:hover { background: #f0fdf4; color: #16a34a; }
    .dark .action-btn.refunded { color: #4ade80; }
    .dark .action-btn.refunded:hover { background: #1e293b; color: #86efac; }
    
    .action-btn.pending-refund { color: #f59e0b; }
    .action-btn.pending-refund:hover { background: #fffbeb; color: #d97706; }
    .dark .action-btn.pending-refund { color: #fbbf24; }
    .dark .action-btn.pending-refund:hover { background: #1e293b; color: #fcd34d; }

    /* Responsive */
    @media (max-width: 640px) {
        .loan-table thead th, .loan-table tbody td {
            padding: 8px 10px;
            font-size: 0.8rem;
        }
        .action-btn {
            width: 24px;
            height: 24px;
            font-size: 0.8rem;
        }
        .book-cover-thumb, .book-cover-placeholder {
            width: 40px;
            height: 56px;
        }
        .book-cover-placeholder {
            font-size: 1.2rem;
        }
    }
</style>

<!-- ─── FULL PAGE WRAPPER WITH DARK MODE BACKGROUND ─── -->
<main class="min-h-screen w-full bg-gray-50 dark:bg-gray-900">
    <div class="container mx-auto px-4 py-8">

        <!-- Success Message -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 px-6 py-4 rounded-xl mb-6 flex items-center justify-between">
                <div>
                    <i class="fas fa-check-circle mr-2"></i>
                    <?php echo htmlspecialchars($_SESSION['success_message']); ?>
                </div>
                <button onclick="this.parentElement.style.display='none'" class="text-green-700 dark:text-green-300 hover:text-green-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <!-- Welcome Section -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-900 dark:to-indigo-900 rounded-2xl p-8 text-white shadow-lg mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2">
                        Welcome, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>! 👋
                    </h1>
                    <p class="text-blue-100 dark:text-blue-300">
                        Here's what's happening with your library account today.
                    </p>
                </div>
                <?php if ($hasViewProfile): ?>
                    <div class="hidden md:block">
                        <a href="<?php echo BASE_URL; ?>/profile" 
                           class="block w-20 h-20 bg-white/20 rounded-full flex items-center justify-center hover:bg-white/30 transition overflow-hidden" 
                           title="Go to Profile">
                            <?php if ($profileImageUrl): ?>
                                <img src="<?= $profileImageUrl ?>" alt="Profile" class="w-full h-full object-cover">
                            <?php else: ?>
                                <i class="fas fa-user text-4xl text-white"></i>
                            <?php endif; ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Books Borrowed -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-md border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Books Borrowed</p>
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400"><?= $totalBorrowed ?></p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                        <i class="fas fa-book-open text-blue-600 dark:text-blue-400"></i>
                    </div>
                </div>
            </div>

            <!-- Active Loans -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-md border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Active Loans</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400"><?= $activeLoans ?></p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                        <i class="fas fa-hand-holding text-green-600 dark:text-green-400"></i>
                    </div>
                </div>
            </div>

            <!-- Overdue Books -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-md border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Overdue Books</p>
                        <p class="text-2xl font-bold text-red-600 dark:text-red-400"><?= $overdueLoans ?></p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-400"></i>
                    </div>
                </div>
            </div>

            <!-- Account Status -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-md border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Account Status</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                            <?php echo ucfirst($_SESSION['user_status'] ?? 'Active'); ?>
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                        <i class="fas fa-user-check text-green-600 dark:text-green-400"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== MY PROFILE ===== -->
        <?php if ($hasViewProfile || $hasEditProfile): ?>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 p-6 mb-8  mx-auto">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                    <i class="fas fa-user-circle text-blue-600 dark:text-blue-400 mr-2"></i>
                    My Profile
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Full Name</p>
                        <p class="font-medium text-gray-900 dark:text-white">
                            <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'N/A'); ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Email</p>
                        <p class="font-medium text-gray-900 dark:text-white">
                            <?php echo htmlspecialchars($_SESSION['user_email'] ?? 'N/A'); ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Phone</p>
                        <p class="font-medium text-gray-900 dark:text-white">
                            <?php echo htmlspecialchars($_SESSION['user_phone'] ?? 'N/A'); ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                            <?php echo ($_SESSION['user_status'] ?? 'active') === 'active' 
                                ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' 
                                : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400'; ?>">
                            <?php echo ucfirst($_SESSION['user_status'] ?? 'Active'); ?>
                        </span>
                    </div>
                </div>
                <?php if ($hasEditProfile): ?>
                    <div class="mt-4">
                        <a href="<?php echo BASE_URL; ?>/profile" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                            <i class="fas fa-edit"></i> Edit Profile
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- ===== BORROWED BOOKS (with Book Cover Image, Overdue Days, Fine) ===== -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                    <i class="fas fa-book-open text-blue-600 dark:text-blue-400 mr-2"></i>
                    My Borrowed Books
                </h2>
                <span class="text-sm text-gray-500 dark:text-gray-400"><?= $totalBorrowed ?> books</span>
            </div>

            <?php if (!empty($loans)): ?>
                <div class="overflow-x-auto">
                    <table class="loan-table">
                        <thead>
                            <tr>
                                <th>Cover</th>
                                <th>Book</th>
                                <th>Author</th>
                                <th>Borrowed Date</th>
                                <th>Due Date</th>
                                <!-- NEW: Overdue Days -->
                                <th>Overdue Days</th>
                                <!-- NEW: Fine (MMK) -->
                                <th>Fine (MMK)</th>
                                <th>Status</th>
                                <th>Days Left</th>
                                <th>Invoice</th>
                                <th>Refund</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <?php foreach ($loans as $item): 
                                $loan = $item['loan'];
                                $book = $booksById[$loan->getBookId()] ?? null;
                                $dueDate = $loan->getDueDate();
                                $isOverdue = $item['is_overdue'];
                                $overdueDays = $item['overdue_days'] ?? 0;
                                $fine = $item['fine'] ?? 0;
                                $statusString = $loan->getStatus()->getValue();

                                // ---- Get Book Cover Image ----
                                $coverImage = null;
                                if ($book) {
                                    if (method_exists($book, 'getCoverImage')) {
                                        $coverImage = $book->getCoverImage();
                                    } elseif (method_exists($book, 'getImage')) {
                                        $coverImage = $book->getImage();
                                    }
                                }
                                $coverUrl = '';
                                if ($coverImage) {
                                    if (strpos($coverImage, 'http') === 0) {
                                        $coverUrl = $coverImage;
                                    } else {
                                        $coverUrl = BASE_URL . '/' . ltrim($coverImage, '/');
                                    }
                                }

                                // ---- Fetch Payment & Invoice ----
                                $payment = null;
                                $invoiceLink = null;
                                $refundStatus = null;
                                if ($paymentRepo && $invoiceRepo) {
                                    try {
                                        $payment = $paymentRepo->findByLoanId($loan->getId());
                                        if ($payment && $payment->getStatus()->isApproved()) {
                                            $invoice = $invoiceRepo->findByPaymentId($payment->getId());
                                            if ($invoice) {
                                                $invoiceLink = BASE_URL . '/invoice/' . $invoice->getId();
                                            }
                                            $refundStatus = $payment->getRefundStatus();
                                        }
                                    } catch (\Exception $e) {
                                        // ignore
                                    }
                                }

                                // Determine status badge class
                                $badgeClass = '';
                                $badgeIcon = 'fa-circle';
                                if ($statusString === 'returned') {
                                    $badgeClass = 'returned';
                                    $badgeIcon = 'fa-check-circle';
                                } elseif ($isOverdue && $statusString === 'active') {
                                    $badgeClass = 'overdue';
                                    $badgeIcon = 'fa-exclamation-circle';
                                } elseif ($statusString === 'active') {
                                    $badgeClass = 'active';
                                    $badgeIcon = 'fa-check-circle';
                                } elseif ($statusString === 'pending') {
                                    $badgeClass = 'pending';
                                    $badgeIcon = 'fa-clock';
                                } elseif ($statusString === 'awaiting_payment') {
                                    $badgeClass = 'awaiting_payment';
                                    $badgeIcon = 'fa-clock';
                                } elseif ($statusString === 'rejected') {
                                    $badgeClass = 'rejected';
                                    $badgeIcon = 'fa-times-circle';
                                } else {
                                    $badgeClass = 'returned';
                                    $badgeIcon = 'fa-circle';
                                }

                                // Calculate days left (only for active, non-overdue)
                                $daysLeft = '—';
                                if ($statusString === 'active' && !$isOverdue && $dueDate) {
                                    $now = new \DateTime();
                                    $diff = $now->diff($dueDate);
                                    $daysLeft = $diff->days . ' days';
                                }
                            ?>
                                <tr>
                                    <!-- Book Cover -->
                                    <td>
                                        <?php if ($coverUrl): ?>
                                            <img src="<?= $coverUrl ?>" 
                                                 alt="<?= htmlspecialchars($book ? $book->getTitle() : 'Book Cover') ?>"
                                                 class="book-cover-thumb"
                                                 onerror="this.style.display='none'; this.parentElement.querySelector('.book-cover-placeholder').style.display='flex';">
                                            <div class="book-cover-placeholder" style="display:none;">
                                                <i class="fas fa-book"></i>
                                            </div>
                                        <?php else: ?>
                                            <div class="book-cover-placeholder">
                                                <i class="fas fa-book"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <!-- Book (left aligned) -->
                                    <td class="font-medium text-gray-900 dark:text-white">
                                        <?= htmlspecialchars($book ? $book->getTitle() : 'Unknown') ?>
                                    </td>
                                    <!-- Author (left aligned) -->
                                    <td class="text-gray-800 dark:text-gray-300">
                                        <?= htmlspecialchars($book ? $book->getAuthor() : 'Unknown') ?>
                                    </td>
                                    <td class="text-gray-800 dark:text-gray-300">
                                        <?= $loan->getBorrowedAt() ? $loan->getBorrowedAt()->format('M d, Y') : '—' ?>
                                    </td>
                                    <td class="text-gray-800 dark:text-gray-300">
                                        <?= $dueDate ? $dueDate->format('M d, Y') : '—' ?>
                                    </td>
                                    <!-- Overdue Days -->
                                    <td>
                                        <?php if ($isOverdue && $statusString === 'active'): ?>
                                            <span class="text-red-600 dark:text-red-400 font-bold"><?= $overdueDays ?> days</span>
                                        <?php elseif ($statusString === 'awaiting_payment' && $fine > 0): ?>
                                            <span class="text-orange-600 dark:text-orange-400">Fine due</span>
                                        <?php else: ?>
                                            <span class="text-gray-400">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <!-- Fine (MMK) -->
                                    <td>
                                        <?php if ($fine > 0): ?>
                                            <span class="text-red-600 dark:text-red-400 font-bold"><?= number_format($fine) ?></span>
                                        <?php else: ?>
                                            <span class="text-gray-400">0</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="status-badge <?= $badgeClass ?>">
                                            <i class="fas <?= $badgeIcon ?>"></i>
                                            <?= ucfirst(str_replace('_', ' ', $statusString)) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($statusString === 'active'): ?>
                                            <?php if ($isOverdue): ?>
                                                <span class="text-red-600 dark:text-red-400 font-bold">Overdue!</span>
                                            <?php else: ?>
                                                <span class="text-gray-700 dark:text-gray-300"><?= $daysLeft ?></span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-gray-400">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($invoiceLink): ?>
                                            <a href="<?= $invoiceLink ?>" class="action-btn invoice" title="View Invoice">
                                                <i class="fas fa-file-invoice"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-400">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($payment && $payment->getStatus()->isApproved()): ?>
                                            <?php if ($refundStatus === 'completed'): ?>
                                                <span class="action-btn refunded" title="Refunded">
                                                    <i class="fas fa-check-circle"></i>
                                                </span>
                                            <?php elseif ($refundStatus === 'pending'): ?>
                                                <span class="action-btn pending-refund" title="Refund Pending">
                                                    <i class="fas fa-clock"></i>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-gray-400">—</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-gray-400">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                    <i class="fas fa-book-open text-4xl mb-3 block"></i>
                    <p>You haven't borrowed any books yet.</p>
                    <a href="<?= BASE_URL ?>/books" class="mt-2 inline-block text-blue-600 dark:text-blue-400 hover:underline">Browse books →</a>
                </div>
            <?php endif; ?>
        </div>

    </div>
</main>

<?php include __DIR__ . '/layout/footer.php'; ?>