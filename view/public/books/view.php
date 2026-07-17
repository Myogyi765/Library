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

// ================================================================
// ✅ FIX: Handle cover image correctly (full URL or local path)
// ================================================================
$coverImage = $book->getCoverImage();
if ($coverImage) {
    if (strpos($coverImage, 'http://') === 0 || strpos($coverImage, 'https://') === 0) {
        $coverUrl = $coverImage;
    } else {
        $coverUrl = BASE_URL . $coverImage;
    }
} else {
    $coverUrl = null;
}
?>

<!-- ================================================================ -->
<!-- PREMIUM STYLES (Match Catalog Design)                           -->
<!-- ================================================================ -->
<style>
    .book-detail-card {
        background: rgba(255,255,255,0.6);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,0.3);
        border-radius: 1.5rem;
        box-shadow: 0 8px 32px rgba(0,0,0,0.04);
        overflow: hidden;
    }
    .dark .book-detail-card {
        background: rgba(15,23,42,0.5);
        border-color: rgba(255,255,255,0.06);
        box-shadow: 0 8px 32px rgba(0,0,0,0.3);
    }
    .book-detail-cover {
        background: rgba(255,255,255,0.3);
        backdrop-filter: blur(4px);
        border-radius: 1rem;
        transition: transform 0.3s ease;
    }
    .dark .book-detail-cover {
        background: rgba(15,23,42,0.3);
    }
    .book-detail-cover img {
        border-radius: 0.75rem;
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        transition: transform 0.3s ease;
        max-height: 480px;
        object-fit: contain;
        width: 100%;
    }
    .dark .book-detail-cover img {
        box-shadow: 0 8px 24px rgba(0,0,0,0.3);
    }
    .book-detail-cover img:hover {
        transform: scale(1.02);
    }
    .btn-outline {
        transition: all 0.2s ease;
    }
    .btn-outline:hover {
        transform: translateY(-2px);
    }
</style>

<div class="container mx-auto px-4 py-8 max-w-5xl">
    <!-- Back to Catalog -->
    <nav class="text-sm mb-6">
        <a href="<?= BASE_URL ?>/books" class="inline-flex items-center gap-2 text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors">
            <i class="fas fa-arrow-left text-xs"></i> Back to Catalog
        </a>
    </nav>

    <!-- Main Card -->
    <div class="book-detail-card md:flex">
        <!-- Cover Image Section (Fixed) -->
        <div class="md:w-2/5 p-6 flex items-center justify-center bg-gray-50/50 dark:bg-gray-900/30 book-detail-cover">
            <?php if ($coverUrl): ?>
                <img src="<?= $coverUrl ?>" 
                     alt="<?= htmlspecialchars($book->getTitle()) ?>" 
                     class="w-full max-w-xs rounded-lg shadow-md object-cover"
                     loading="lazy"
                     onerror="this.style.display='none'; this.parentElement.querySelector('.fallback-icon').style.display='flex';">
                <div class="fallback-icon w-full max-w-xs aspect-[3/4] bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-800 rounded-lg flex items-center justify-center text-gray-400 dark:text-gray-500" style="display:none;">
                    <i class="fas fa-book text-6xl opacity-40"></i>
                </div>
            <?php else: ?>
                <div class="w-full max-w-xs aspect-[3/4] bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-800 rounded-lg flex items-center justify-center text-gray-400 dark:text-gray-500">
                    <i class="fas fa-book text-6xl opacity-40"></i>
                </div>
            <?php endif; ?>
        </div>

        <!-- Book Details -->
        <div class="md:w-3/5 p-6 md:p-8">
            <div class="flex items-start justify-between gap-4">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($book->getTitle()) ?></h1>
                <?php if ($book->getAvailableQuantity() > 0): ?>
                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400 whitespace-nowrap">
                        <i class="fas fa-check-circle mr-1"></i> Available
                    </span>
                <?php else: ?>
                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400 whitespace-nowrap">
                        <i class="fas fa-times-circle mr-1"></i> Out of Stock
                    </span>
                <?php endif; ?>
            </div>

            <p class="text-lg text-gray-600 dark:text-gray-300 mt-1">by <?= htmlspecialchars($book->getAuthor()) ?></p>

            <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                <div>
                    <span class="text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">ISBN</span>
                    <p class="font-medium text-gray-900 dark:text-white"><?= $book->getIsbn() ?? 'N/A' ?></p>
                </div>
                <div>
                    <span class="text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">Category</span>
                    <p class="font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($categoryMap[$book->getCategoryId()] ?? 'Unknown') ?></p>
                </div>
                <div>
                    <span class="text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">Total Copies</span>
                    <p class="font-medium text-gray-900 dark:text-white"><?= $book->getQuantity() ?></p>
                </div>
                <div>
                    <span class="text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">Available</span>
                    <p class="font-medium text-gray-900 dark:text-white"><?= $book->getAvailableQuantity() ?></p>
                </div>
            </div>

            <?php if ($book->getDescription()): ?>
                <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Description</h3>
                    <p class="mt-2 text-gray-700 dark:text-gray-300 leading-relaxed text-sm"><?= nl2br(htmlspecialchars($book->getDescription())) ?></p>
                </div>
            <?php endif; ?>

            <!-- Action Buttons (Improved) -->
            <div class="mt-8 flex flex-wrap gap-3">
                <?php if ($book->getAvailableQuantity() > 0 && isset($_SESSION['user_authenticated']) && $_SESSION['user_authenticated'] === true): ?>
                    <?php if ($userLoanStatus === 'pending'): ?>
                        <span class="bg-amber-100 text-amber-800 px-5 py-2.5 rounded-xl inline-flex items-center gap-2 border border-amber-300 shadow-sm text-sm font-medium">
                            <i class="fas fa-clock animate-pulse"></i> Request Pending
                        </span>
                    <?php elseif ($userLoanStatus === 'awaiting_payment'): ?>
                        <a href="<?= BASE_URL ?>/payment/submit/<?= $userLoanId ?>" 
                           class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl transition flex items-center gap-2 shadow-md hover:shadow-lg text-sm font-medium">
                            <i class="fas fa-credit-card"></i> Pay Now
                        </a>
                    <?php elseif ($userLoanStatus === 'active'): ?>
                        <span class="bg-blue-100 text-blue-800 px-5 py-2.5 rounded-xl inline-flex items-center gap-2 text-sm font-medium">
                            <i class="fas fa-check-circle"></i> Already Borrowed
                        </span>
                    <?php else: ?>
                        <form action="<?= BASE_URL ?>/books/borrow/<?= $book->getId() ?>" method="POST" class="inline">
                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl transition flex items-center gap-2 shadow-md hover:shadow-lg text-sm font-medium">
                                <i class="fas fa-hand-holding"></i> Borrow Book
                            </button>
                        </form>
                    <?php endif; ?>
                <?php elseif ($book->getAvailableQuantity() > 0): ?>
                    <a href="<?= BASE_URL ?>/login" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl transition flex items-center gap-2 shadow-md hover:shadow-lg text-sm font-medium">
                        <i class="fas fa-sign-in-alt"></i> Login to Borrow
                    </a>
                <?php else: ?>
                    <span class="bg-rose-600 text-white px-5 py-2.5 rounded-xl inline-flex items-center gap-2 shadow-md border border-rose-700 hover:bg-rose-700 transition cursor-not-allowed text-sm font-medium">
                        <i class="fas fa-times-circle"></i> Out of Stock
                    </span>
                <?php endif; ?>

                <a href="<?= BASE_URL ?>/books" 
                   class="bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 hover:text-gray-900 dark:hover:text-white transition-all flex items-center gap-2 px-5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 shadow-sm hover:shadow-md transform hover:-translate-y-0.5 text-sm font-medium">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/view/layout/footer.php'; ?>