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

// Data passed from controller: $loans, $books
$loans = $loans ?? [];
$books = $books ?? [];
$user = $user ?? null;

// Calculate stats
$totalBorrowed = count($loans);
$activeLoans = 0;
$overdueLoans = 0;
$now = new \DateTime();

foreach ($loans as $loan) {
    $statusString = $loan->getStatus()->getValue();
    if ($statusString === 'active') {
        $activeLoans++;
        if ($loan->getDueDate() < $now) {
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

        <!-- Welcome Section – Auto‑adapts to dark/light mode -->
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

        <!-- ===== MY PROFILE – Full width (Quick Actions removed) ===== -->
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
                        <a href="<?php echo BASE_URL; ?>/profile/edit" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                            <i class="fas fa-edit"></i> Edit Profile
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- ===== BORROWED BOOKS (bottom) ===== -->
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
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Book</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Author</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Borrowed Date</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Due Date</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Days Left</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Invoice</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Refund</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <?php foreach ($loans as $loan): 
                                $book = $books[$loan->getBookId()] ?? null;
                                $dueDate = $loan->getDueDate();
                                $isOverdue = $now > $dueDate;
                                $daysLeft = $now->diff($dueDate)->days;
                                $statusString = $loan->getStatus()->getValue();

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
                            ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30 transition">
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                        <?= htmlspecialchars($book ? $book->getTitle() : 'Unknown') ?>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                        <?= htmlspecialchars($book ? $book->getAuthor() : 'Unknown') ?>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                        <?= $loan->getBorrowedAt() ? $loan->getBorrowedAt()->format('M d, Y') : '—' ?>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                        <?= $dueDate ? $dueDate->format('M d, Y') : '—' ?>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <?php if ($statusString === 'returned'): ?>
                                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">Returned</span>
                                        <?php elseif ($isOverdue && $statusString === 'active'): ?>
                                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">Overdue</span>
                                        <?php elseif ($statusString === 'active'): ?>
                                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Active</span>
                                        <?php elseif ($statusString === 'pending'): ?>
                                            <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">Pending</span>
                                        <?php elseif ($statusString === 'awaiting_payment'): ?>
                                            <span class="px-2 py-1 text-xs rounded-full bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400">Awaiting Payment</span>
                                        <?php elseif ($statusString === 'rejected'): ?>
                                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">Rejected</span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300"><?= ucfirst(str_replace('_', ' ', $statusString)) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <?php if ($statusString === 'active'): ?>
                                            <?php if ($isOverdue): ?>
                                                <span class="text-red-600 dark:text-red-400 font-bold">Overdue!</span>
                                            <?php else: ?>
                                                <span class="text-gray-700 dark:text-gray-300"><?= $daysLeft ?> days</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-gray-400">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <?php if ($invoiceLink): ?>
                                            <a href="<?= $invoiceLink ?>" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300" title="View Invoice">
                                                <i class="fas fa-file-invoice"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-400">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <?php if ($payment && $payment->getStatus()->isApproved()): ?>
                                            <?php if ($refundStatus === 'completed'): ?>
                                                <span class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                                    <i class="fas fa-check-circle"></i> Refunded
                                                </span>
                                            <?php elseif ($refundStatus === 'pending'): ?>
                                                <span class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                                                    <i class="fas fa-clock"></i> Pending
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