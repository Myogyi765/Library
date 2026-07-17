<?php
$page = $page ?? ($_GET['page'] ?? 'dashboard');
$stats = $stats ?? [];
$loans = $loans ?? [];
$users = $users ?? [];
$books = $books ?? [];
$allBooks = $allBooks ?? [];
$categories = $categories ?? [];
$categoryMap = $categoryMap ?? [];
?>

<?php if ($page === 'dashboard'): ?>
    <div class="space-y-6">
        <!-- ===== HEADER ===== -->
      <!-- ===== HEADER ===== -->
<div class="flex items-end justify-between">
    <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">of library operations</p>
    </div>
    <div class="flex items-center gap-4">
        <!-- ✅ Scan Button – Camera Icon -->
<a href="<?= BASE_URL ?>/librarian/scanner" 
   class="flex items-center gap-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
              d="M3 8V5a1 1 0 011-1h3M3 16v3a1 1 0 001 1h3M21 8V5a1 1 0 00-1-1h-3M21 16v3a1 1 0 01-1 1h-3"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" 
              d="M7 10h1v4H7zM10 10h1v4h-1zM13 10h1v4h-1zM16 10h1v4h-1z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
              d="M5 12h14"/>
        <circle cx="12" cy="12" r="1.5" fill="currentColor" stroke="none"/>
    </svg>
    <span class="text-sm font-medium">Scan</span>
</a>
        <div class="text-sm text-gray-500 dark:text-gray-400">
            <?= date('M j, Y') ?>
        </div>
    </div>
</div>

        <!-- ===== STATS CARDS (Simple) ===== -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Users</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?= number_format($stats['users'] ?? 0) ?></p>
                    </div>
                    <i class="fas fa-users text-blue-500 text-2xl"></i>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Books</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?= number_format($stats['books'] ?? 0) ?></p>
                    </div>
                    <i class="fas fa-book text-indigo-500 text-2xl"></i>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Active Loans</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?= number_format($stats['activeLoans'] ?? 0) ?></p>
                    </div>
                    <i class="fas fa-hand-holding text-green-500 text-2xl"></i>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Overdue</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?= number_format($stats['overdue'] ?? 0) ?></p>
                    </div>
                    <i class="fas fa-clock text-red-500 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- ===== QUICK ACTIONS (without scan) ===== -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <a href="<?= BASE_URL ?>/librarian/dashboard?page=books_create" class="flex flex-col items-center justify-center p-4 rounded-lg bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30 transition text-blue-600 dark:text-blue-400">
                    <i class="fas fa-plus-circle text-2xl"></i>
                    <span class="mt-1 text-sm font-medium">Add Book</span>
                </a>
                <a href="<?= BASE_URL ?>/librarian/loans/create" class="flex flex-col items-center justify-center p-4 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 hover:bg-indigo-100 dark:hover:bg-indigo-900/30 transition text-indigo-600 dark:text-indigo-400">
                    <i class="fas fa-hand-holding-heart text-2xl"></i>
                    <span class="mt-1 text-sm font-medium">Issue Book</span>
                </a>
                <a href="<?= BASE_URL ?>/librarian/users/create" class="flex flex-col items-center justify-center p-4 rounded-lg bg-purple-50 dark:bg-purple-900/20 hover:bg-purple-100 dark:hover:bg-purple-900/30 transition text-purple-600 dark:text-purple-400">
                    <i class="fas fa-user-plus text-2xl"></i>
                    <span class="mt-1 text-sm font-medium">Add User</span>
                </a>
                <a href="<?= BASE_URL ?>/librarian/dashboard?page=payments" class="flex flex-col items-center justify-center p-4 rounded-lg bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30 transition text-green-600 dark:text-green-400">
                    <i class="fas fa-credit-card text-2xl"></i>
                    <span class="mt-1 text-sm font-medium">Payments</span>
                </a>
            </div>
        </div>

        <!-- ===== RECENT ACTIVITY (Simple) ===== -->
        <?php if (!empty($stats['recentActivities'] ?? [])): ?>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Activity</h2>
                    <a href="<?= BASE_URL ?>/librarian/dashboard?page=loans" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">View All</a>
                </div>
                <div class="space-y-3">
                    <?php foreach ($stats['recentActivities'] as $activity): ?>
                        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3 last:border-0 last:pb-0">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    <?= htmlspecialchars($activity['user'] ?? 'Unknown') ?>
                                    <?= strtolower($activity['action'] ?? 'updated') ?>
                                    <?= htmlspecialchars($activity['book'] ?? 'a book') ?>
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400"><?= htmlspecialchars($activity['date'] ?? '') ?></p>
                            </div>
                            <span class="px-2 py-1 rounded-full text-xs font-medium <?php
                                $status = $activity['status'] ?? '';
                                if ($status === 'returned') echo 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
                                elseif ($status === 'overdue') echo 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
                                else echo 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
                            ?>">
                                <?= htmlspecialchars(ucfirst($status ?: 'Active')) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php elseif ($page === 'users'): ?>
    <?php include BASE_PATH . '/view/librarian/users/index.php'; ?>

<?php elseif ($page === 'books'): ?>
    <?php include BASE_PATH . '/view/librarian/books/index.php'; ?>

<?php elseif ($page === 'loans'): ?>
    <?php include BASE_PATH . '/view/librarian/loans/index.php'; ?>

<?php elseif ($page === 'payments'): ?>
    <?php include BASE_PATH . '/view/librarian/payments/index.php'; ?>

<?php elseif ($page === 'refunds'): ?>
    <?php include BASE_PATH . '/view/librarian/refunds/index.php'; ?>

<?php elseif ($page === 'books_create'): ?>
    <!-- ===== BOOKS CREATE FORM ===== -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                <i class="fas fa-plus-circle text-blue-600 mr-2"></i>Add New Book
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Add a new book to the library catalog</p>
        </div>
        <a href="<?= BASE_URL ?>/librarian/dashboard?page=books" class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
            <i class="fas fa-arrow-left mr-1"></i> Back to Books
        </a>
    </div>
    <?php include BASE_PATH . '/view/librarian/books/create.php'; ?>

<?php else: ?>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-8 text-center text-gray-500 dark:text-gray-400">
        The requested page is not yet.
    </div>
<?php endif; ?>