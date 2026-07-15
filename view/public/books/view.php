<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$pageTitle = 'Book Details';
include BASE_PATH . '/view/layout/header.php';

$book = $book ?? null;
if (!$book) {
    echo '<div class="container mx-auto px-4 py-8"><div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 p-6 rounded-xl text-center">Book not found.</div></div>';
    include BASE_PATH . '/view/layout/footer.php';
    return;
}

// Build category map if categories are passed
$categoryMap = [];
if (isset($categories)) {
    foreach ($categories as $cat) {
        $categoryMap[$cat->getId()] = $cat->getName();
    }
}

// ---- Get user's loan status for this book ----
$userLoanStatus = $userLoanStatus ?? null;
$userLoanId = $userLoanId ?? null;

if ($userLoanStatus === null && isset($_SESSION['user_authenticated']) && $_SESSION['user_authenticated'] === true && isset($_SESSION['user_id'])) {
    try {
        $container = $GLOBALS['container'] ?? null;
        if ($container && $container->has(\App\Circulation\Domain\Repository\LoanRepositoryInterface::class)) {
            $loanRepo = $container->get(\App\Circulation\Domain\Repository\LoanRepositoryInterface::class);
            $loan = $loanRepo->findActiveOrPendingByUserAndBook($_SESSION['user_id'], $book->getId());
            if ($loan) {
                $userLoanStatus = $loan->getStatus()->getValue();
                $userLoanId = $loan->getId();
            }
        }
    } catch (\Exception $e) {
        // Silently fail
    }
}
?>

<div class="container mx-auto px-4 py-8 max-w-4xl">
    <nav class="text-sm mb-6">
        <a href="<?= BASE_URL ?>/books" class="text-blue-600 dark:text-blue-400 hover:underline">&larr; Back to Catalog</a>
    </nav>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="md:flex">
            <div class="md:w-2/5 p-6 flex items-center justify-center bg-gray-50 dark:bg-gray-900/30">
                <?php if ($book->getCoverImage()): ?>
                    <img src="<?= BASE_URL . $book->getCoverImage() ?>" class="w-full max-w-xs rounded-lg shadow-md" alt="Cover">
                <?php else: ?>
                    <div class="w-full max-w-xs aspect-[3/4] bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-800 rounded-lg flex items-center justify-center text-gray-400 dark:text-gray-500">
                        <i class="fas fa-book text-6xl opacity-40"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div class="md:w-3/5 p-6 md:p-8">
                <div class="flex items-start justify-between">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($book->getTitle()) ?></h1>
                    <?php if ($book->getAvailableQuantity() > 0): ?>
                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                            Available
                        </span>
                    <?php else: ?>
                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                            Out of Stock
                        </span>
                    <?php endif; ?>
                </div>

                <p class="text-xl text-gray-600 dark:text-gray-300 mt-1">by <?= htmlspecialchars($book->getAuthor()) ?></p>

                <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">ISBN</span>
                        <p class="font-medium text-gray-900 dark:text-white"><?= $book->getIsbn() ?? 'N/A' ?></p>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Category</span>
                        <p class="font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($categoryMap[$book->getCategoryId()] ?? 'Unknown') ?></p>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Quantity</span>
                        <p class="font-medium text-gray-900 dark:text-white"><?= $book->getQuantity() ?></p>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Available</span>
                        <p class="font-medium text-gray-900 dark:text-white"><?= $book->getAvailableQuantity() ?></p>
                    </div>
                </div>

                <?php if ($book->getDescription()): ?>
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Description</h3>
                        <p class="mt-2 text-gray-700 dark:text-gray-300 leading-relaxed"><?= nl2br(htmlspecialchars($book->getDescription())) ?></p>
                    </div>
                <?php endif; ?>

                <div class="mt-8 flex flex-wrap gap-3">
                    <?php if ($book->getAvailableQuantity() > 0 && isset($_SESSION['user_authenticated']) && $_SESSION['user_authenticated'] === true): ?>
                        <?php if ($userLoanStatus === 'pending'): ?>
                            <!-- 🟠 Improved "Request Pending" color: Amber/Orange for better visibility -->
                            <span class="bg-amber-100 text-amber-800 px-6 py-2.5 rounded-lg inline-flex items-center gap-2 border border-amber-300 shadow-sm">
                                <i class="fas fa-clock animate-pulse"></i> Request Pending
                            </span>
                        <?php elseif ($userLoanStatus === 'awaiting_payment'): ?>
                            <a href="<?= BASE_URL ?>/payment/submit/<?= $userLoanId ?>" 
                               class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg transition flex items-center gap-2 shadow-md hover:shadow-lg">
                                <i class="fas fa-credit-card"></i> Pay Now
                            </a>
                        <?php elseif ($userLoanStatus === 'active'): ?>
                            <span class="bg-blue-100 text-blue-800 px-6 py-2.5 rounded-lg inline-flex items-center gap-2">
                                <i class="fas fa-check-circle"></i> Already Borrowed
                            </span>
                        <?php else: ?>
                            <form action="<?= BASE_URL ?>/books/borrow/<?= $book->getId() ?>" method="POST" class="inline">
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg transition flex items-center gap-2 shadow-md hover:shadow-lg">
                                    <i class="fas fa-hand-holding"></i> Borrow Book
                                </button>
                            </form>
                        <?php endif; ?>
                    <?php elseif ($book->getAvailableQuantity() > 0): ?>
                        <a href="<?= BASE_URL ?>/login" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg transition flex items-center gap-2">
                            <i class="fas fa-sign-in-alt"></i> Login to Borrow
                        </a>
                    <?php else: ?>
                        <span class="bg-gray-400 text-white px-6 py-2.5 rounded-lg inline-flex items-center gap-2">
                            <i class="fas fa-times-circle"></i> Out of Stock
                        </span>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>/books" class="text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition flex items-center gap-2 px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/view/layout/footer.php'; ?>