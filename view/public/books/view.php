<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$pageTitle = 'Book Details';
include BASE_PATH . '/view/layout/header.php';

$book = $book ?? null;
if (!$book) {
    echo '<div class="container mx-auto px-6 py-12 max-w-5xl"><div class="bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900 text-red-700 dark:text-red-400 p-6 rounded-2xl text-center font-semibold">Book profile could not be found in the system archives.</div></div>';
    include BASE_PATH . '/view/layout/footer.php';
    return;
}

// Build category map
$categoryMap = [];
if (isset($categories)) {
    foreach ($categories as $cat) {
        $categoryMap[$cat->getId()] = $cat->getName();
    }
}

// ---- Get user's loan status ----
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

// Cover image
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
<!-- MINIMAL REFRESH – Same layout, refined details                   -->
<!-- ================================================================ -->
<style>
    .book-card {
        border-radius: 1.25rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04), 0 1px 3px rgba(0,0,0,0.03);
        transition: box-shadow 0.2s ease;
    }
    .dark .book-card {
        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    }
    .book-card:hover {
        box-shadow: 0 8px 30px rgba(0,0,0,0.06), 0 2px 6px rgba(0,0,0,0.04);
    }
    .dark .book-card:hover {
        box-shadow: 0 8px 30px rgba(0,0,0,0.3);
    }

    .cover-shadow {
        box-shadow: 0 6px 18px rgba(0,0,0,0.08);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .cover-shadow:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 28px rgba(0,0,0,0.12);
    }
    .dark .cover-shadow {
        box-shadow: 0 6px 18px rgba(0,0,0,0.4);
    }
    .dark .cover-shadow:hover {
        box-shadow: 0 12px 28px rgba(0,0,0,0.6);
    }

    .stat-box {
        background: #f9fafb;
        border: 1px solid #f1f3f5;
        border-radius: 0.75rem;
        padding: 0.5rem 0.75rem;
    }
    .dark .stat-box {
        background: #1e293b;
        border-color: #2d3a4f;
    }

    .action-btn {
        background: #4f46e5;
        transition: all 0.15s ease;
    }
    .action-btn:hover {
        background: #4338ca;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(79,70,229,0.25);
    }
    .dark .action-btn:hover {
        box-shadow: 0 4px 12px rgba(79,70,229,0.4);
    }
</style>

<div class="min-h-screen bg-slate-50 dark:bg-slate-950 py-8 transition-colors duration-300">
    <div class="container mx-auto px-12 max-w-5xl">

        <!-- Breadcrumb -->
        <nav class="mb-5">
            <a href="<?= BASE_URL ?>/books" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                <i class="fas fa-arrow-left text-xs"></i> Back to collection
            </a>
        </nav>

        <!-- Main card – two‑column, compact -->
        <div class="book-card bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800/70 overflow-hidden md:flex transition-colors">

            <!-- Cover (left, 35%) -->
            <div class="md:w-[35%] p-6 flex items-center justify-center bg-slate-50/50 dark:bg-slate-800/30 border-b md:border-b-0 md:border-r border-slate-200/70 dark:border-slate-800/70">
                <div class="w-full max-w-[200px]">
                    <?php if ($coverUrl): ?>
                        <img src="<?= $coverUrl ?>"
                             alt="<?= htmlspecialchars($book->getTitle()) ?>"
                             class="w-full h-auto object-cover rounded-lg cover-shadow"
                             loading="lazy"
                             onerror="this.style.display='none'; this.parentElement.querySelector('.fallback-icon').style.display='flex';">
                        <div class="fallback-icon w-full aspect-[3/4] bg-slate-200 dark:bg-slate-700 rounded-lg flex items-center justify-center text-slate-400 dark:text-slate-500" style="display:none;">
                            <i class="fas fa-book-open text-5xl opacity-30"></i>
                        </div>
                    <?php else: ?>
                        <div class="w-full aspect-[3/4] bg-slate-200 dark:bg-slate-700 rounded-lg flex items-center justify-center text-slate-400 dark:text-slate-500">
                            <i class="fas fa-book-open text-5xl opacity-30"></i>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Metadata (right, 65%) -->
            <div class="md:w-[65%] p-6 flex flex-col justify-between">

                <!-- Title + availability row -->
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white leading-tight">
                            <?= htmlspecialchars($book->getTitle()) ?>
                        </h1>
                        <p class="text-sm text-indigo-600 dark:text-indigo-400 font-medium">
                            <?= htmlspecialchars($book->getAuthor()) ?>
                        </p>
                    </div>
                    <div class="shrink-0 mt-1">
                        <?php if ($book->getAvailableQuantity() > 0): ?>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 text-[11px] font-bold uppercase tracking-wider rounded-full bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-900/40">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Available
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 text-[11px] font-bold uppercase tracking-wider rounded-full bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-200/60 dark:border-rose-900/40">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Unavailable
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Stats – smaller, in a 2x2 grid -->
                <div class="grid grid-cols-2 gap-3 mt-4">
                    <div class="stat-box">
                        <span class="block text-[10px] font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Category</span>
                        <span class="text-sm font-semibold text-slate-800 dark:text-slate-200 flex items-center gap-1">
                            <i class="fas fa-tag text-indigo-400 text-xs"></i>
                            <?= htmlspecialchars($categoryMap[$book->getCategoryId()] ?? 'General') ?>
                        </span>
                    </div>
                    <div class="stat-box">
                        <span class="block text-[10px] font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">ISBN</span>
                        <span class="text-sm font-mono font-semibold text-slate-800 dark:text-slate-200">
                            <?= $book->getIsbn() ?: '—' ?>
                        </span>
                    </div>
                    <div class="stat-box">
                        <span class="block text-[10px] font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Total</span>
                        <span class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                            <?= $book->getQuantity() ?>
                        </span>
                    </div>
                    <div class="stat-box">
                        <span class="block text-[10px] font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Available</span>
                        <span class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                            <?= $book->getAvailableQuantity() ?>
                        </span>
                    </div>
                </div>

                <!-- Synopsis (compact) -->
                <?php if ($book->getDescription()): ?>
                    <div class="mt-4 pt-3 border-t border-slate-200/60 dark:border-slate-800/60">
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed line-clamp-3">
                            <?= nl2br(htmlspecialchars($book->getDescription())) ?>
                        </p>
                    </div>
                <?php endif; ?>

                <!-- Actions (smaller buttons) -->
                <div class="mt-5 pt-3 border-t border-slate-200/60 dark:border-slate-800/60 flex flex-wrap items-center gap-3">
                    <?php if ($book->getAvailableQuantity() > 0 && isset($_SESSION['user_authenticated']) && $_SESSION['user_authenticated'] === true): ?>
                        <?php if ($userLoanStatus === 'pending'): ?>
                            <div class="bg-amber-50 dark:bg-amber-950/30 text-amber-800 dark:text-amber-400 px-4 py-2 rounded-lg text-xs font-bold border border-amber-200 dark:border-amber-900/50 flex items-center gap-2">
                                <i class="fas fa-spinner fa-spin"></i> Pending
                            </div>
                        <?php elseif ($userLoanStatus === 'awaiting_payment'): ?>
                            <a href="<?= BASE_URL ?>/payment/submit/<?= $userLoanId ?>"
                               class="action-btn text-white px-5 py-2 rounded-lg text-xs font-bold shadow-sm flex items-center gap-2">
                                <i class="fas fa-wallet"></i> Pay now
                            </a>
                        <?php elseif ($userLoanStatus === 'active'): ?>
                            <div class="bg-blue-50 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400 px-4 py-2 rounded-lg text-xs font-bold border border-blue-200 dark:border-blue-900/50 flex items-center gap-2">
                                <i class="fas fa-check-circle"></i> Active loan
                            </div>
                        <?php else: ?>
                            <form action="<?= BASE_URL ?>/books/borrow/<?= $book->getId() ?>" method="POST" class="inline">
                                <button type="submit" class="action-btn text-white px-5 py-2 rounded-lg text-xs font-bold shadow-sm flex items-center gap-2">
                                    <i class="fas fa-hand-holding"></i> Borrow
                                </button>
                            </form>
                        <?php endif; ?>
                    <?php elseif ($book->getAvailableQuantity() > 0): ?>
                        <a href="<?= BASE_URL ?>/login" class="action-btn text-white px-5 py-2 rounded-lg text-xs font-bold shadow-sm flex items-center gap-2">
                            <i class="fas fa-sign-in-alt"></i> Login to borrow
                        </a>
                    <?php else: ?>
                        <div class="bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 px-4 py-2 rounded-lg text-xs font-bold border border-slate-200 dark:border-slate-700/60 cursor-not-allowed flex items-center gap-2">
                            <i class="fas fa-ban"></i> Out of stock
                        </div>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>/books" class="text-xs font-medium text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 transition-colors ml-auto">
                        Close <i class="fas fa-chevron-right ml-1 text-[10px]"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/view/layout/footer.php'; ?>