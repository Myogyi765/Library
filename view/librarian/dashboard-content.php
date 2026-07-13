<?php
// view/librarian/dashboard-content.php
$page = $page ?? 'dashboard';
$stats = $stats ?? [];
$loans = $loans ?? [];
$users = $users ?? [];
$books = $books ?? [];          // lookup array for loans
$allBooks = $allBooks ?? [];    // flat array for book listing
$categoryMap = $categoryMap ?? [];
$categories = $categories ?? [];
// Ensure $payments is defined (will be passed from controller)
$payments = $payments ?? [];
?>
<div class="space-y-6">
    <?php if ($page === 'loans'): ?>
        <!-- ============================================================ -->
        <!-- 🔹 LOANS PAGE                                                -->
        <!-- ============================================================ -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    <i class="fas fa-hand-holding text-blue-600 mr-2"></i>Loan Records
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Manage all library loans</p>
            </div>
            <a href="<?= BASE_URL ?>/librarian/loans/create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2 transition">
                <i class="fas fa-plus"></i> Issue Book
            </a>
        </div>

        <!-- Flash Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 px-6 py-4 rounded-xl mb-6 flex items-center justify-between">
                <div><i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($_SESSION['success_message']) ?></div>
                <button onclick="this.parentElement.style.display='none'" class="text-green-700 dark:text-green-300 hover:text-green-900"><i class="fas fa-times"></i></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 px-6 py-4 rounded-xl mb-6 flex items-center justify-between">
                <div><i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($_SESSION['error_message']) ?></div>
                <button onclick="this.parentElement.style.display='none'" class="text-red-700 dark:text-red-300 hover:text-red-900"><i class="fas fa-times"></i></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <!-- Loans Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">#</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">User</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Book</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Borrowed</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Due Date</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($loans)): ?>
                            <?php foreach ($loans as $loan): 
                                $user = $users[$loan->getUserId()] ?? null;
                                $book = $books[$loan->getBookId()] ?? null;
                                $status = $loan->getStatus()->getValue();
                                
                                $statusColor = match($status) {
                                    'pending'           => 'purple',
                                    'awaiting_payment'  => 'orange',
                                    'active'            => 'green',
                                    'returned'          => 'blue',
                                    'rejected'          => 'gray',
                                    'overdue'           => 'red',
                                    default             => 'gray'
                                };
                                
                                $borrowedAt = $loan->getBorrowedAt() ? $loan->getBorrowedAt()->format('Y-m-d') : '—';
                                $dueDate = $loan->getDueDate() ? $loan->getDueDate()->format('Y-m-d') : '—';
                            ?>
                                <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-900/30 transition">
                                    <td class="px-4 py-3"><?= $loan->getId() ?></td>
                                    <td class="px-4 py-3"><?= htmlspecialchars($user ? $user->getName() : 'Unknown') ?></td>
                                    <td class="px-4 py-3"><?= htmlspecialchars($book ? $book->getTitle() : 'Unknown') ?></td>
                                    <td class="px-4 py-3"><?= $borrowedAt ?></td>
                                    <td class="px-4 py-3"><?= $dueDate ?></td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2 py-1 text-xs rounded-full bg-<?= $statusColor ?>-100 text-<?= $statusColor ?>-800 dark:bg-<?= $statusColor ?>-900/30 dark:text-<?= $statusColor ?>-300">
                                            <?= ucfirst(str_replace('_', ' ', $status)) ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <?php if ($status === 'pending'): ?>
                                            <form action="<?= BASE_URL ?>/librarian/loans/confirm/<?= $loan->getId() ?>" method="POST" class="inline">
                                                <button type="submit" class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 mr-2" title="Confirm">
                                                    <i class="fas fa-check-circle"></i>
                                                </button>
                                            </form>
                                            <form action="<?= BASE_URL ?>/librarian/loans/reject/<?= $loan->getId() ?>" method="POST" class="inline">
                                                <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 mr-2" title="Reject">
                                                    <i class="fas fa-times-circle"></i>
                                                </button>
                                            </form>
                                        <?php elseif ($status === 'awaiting_payment'): ?>
                                            <span class="text-xs text-orange-600 dark:text-orange-400">Awaiting payment</span>
                                        <?php elseif ($status === 'active'): ?>
                                            <form action="<?= BASE_URL ?>/librarian/loans/return/<?= $loan->getId() ?>" method="POST" class="inline">
                                                <button type="submit" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 mr-2" title="Return Book">
                                                    <i class="fas fa-undo-alt"></i>
                                                </button>
                                            </form>
                                            <a href="<?= BASE_URL ?>/librarian/loans/edit/<?= $loan->getId() ?>" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 mr-2" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        <?php elseif ($status === 'returned'): ?>
                                            <span class="text-xs text-green-600 dark:text-green-400">Returned</span>
                                        <?php elseif ($status === 'rejected'): ?>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">Rejected</span>
                                        <?php endif; ?>
                                        <a href="<?= BASE_URL ?>/librarian/loans/delete/<?= $loan->getId() ?>" 
                                           class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" 
                                           title="Delete" 
                                           onclick="return confirm('Delete this loan record?')">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No loans recorded.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php elseif ($page === 'users'): ?>
    <!-- ============================================================ -->
    <!-- 🔹 USERS PAGE – Only 'user' role                             -->
    <!-- ============================================================ -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                <i class="fas fa-users text-blue-600 mr-2"></i>User Management
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Manage library members (users only)</p>
        </div>
        <a href="<?= BASE_URL ?>/librarian/users/create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2 transition">
            <i class="fas fa-plus"></i> Add User
        </a>
    </div>

    <!-- Filter users to only show role = 'user' -->
    <?php
    $filteredUsers = array_filter($users, function($u) {
        return $u->getRole() === 'user';
    });
    ?>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Phone</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Role</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($filteredUsers)): ?>
                        <?php foreach ($filteredUsers as $user): ?>
                            <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-900/30 transition">
                                <td class="px-4 py-3"><?= $user->getId() ?></td>
                                <td class="px-4 py-3"><?= htmlspecialchars($user->getName()) ?></td>
                                <td class="px-4 py-3"><?= htmlspecialchars($user->getEmail()) ?></td>
                                <td class="px-4 py-3">
                                    <?php 
                                    $phone = $user->getPhone();
                                    // ✅ Show "+97 -------" when phone is empty/null
                                    if (empty($phone)) {
                                        echo '+97 -------';
                                    } else {
                                        echo htmlspecialchars($phone);
                                    }
                                    ?>
                                </td>
                                <td class="px-4 py-3"><?= ucfirst($user->getRole() ?? 'user') ?></td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-1 text-xs rounded-full <?= $user->getStatus() === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' ?>">
                                        <?= ucfirst($user->getStatus() ?? 'active') ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="<?= BASE_URL ?>/librarian/users/edit/<?= $user->getId() ?>" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 mr-2" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/librarian/users/delete/<?= $user->getId() ?>" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" title="Delete" onclick="return confirm('Delete this user?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php elseif ($page === 'books'): ?>
        <!-- ============================================================ -->
        <!-- 🔹 BOOKS PAGE                                               -->
        <!-- ============================================================ -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    <i class="fas fa-book text-blue-600 mr-2"></i>Book Management
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Manage library catalog</p>
            </div>
            <a href="<?= BASE_URL ?>/librarian/dashboard?page=books_create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2 transition">
                <i class="fas fa-plus"></i> Add Book
            </a>
        </div>

        <!-- Flash Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 px-6 py-4 rounded-xl mb-6 flex items-center justify-between">
                <div><i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($_SESSION['success_message']) ?></div>
                <button onclick="this.parentElement.style.display='none'" class="text-green-700 dark:text-green-300 hover:text-green-900"><i class="fas fa-times"></i></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 px-6 py-4 rounded-xl mb-6 flex items-center justify-between">
                <div><i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($_SESSION['error_message']) ?></div>
                <button onclick="this.parentElement.style.display='none'" class="text-red-700 dark:text-red-300 hover:text-red-900"><i class="fas fa-times"></i></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <!-- Include the book listing partial -->
        <?php
        // Pass the flat list as $books variable (the view expects $books)
        $books = $allBooks;
        include BASE_PATH . '/view/librarian/books/index.php';
        ?>

    <?php elseif ($page === 'books_create'): ?>
        <!-- ============================================================ -->
        <!-- 🔹 BOOKS CREATE FORM                                         -->
        <!-- ============================================================ -->
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

        <!-- Include the create form partial -->
        <?php include BASE_PATH . '/view/librarian/books/create.php'; ?>

    <?php elseif ($page === 'payments'): ?>
        <!-- ============================================================ -->
        <!-- 🔹 PAYMENTS PAGE (with Refund Status & Button)                -->
        <!-- ============================================================ -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    <i class="fas fa-credit-card text-purple-600 dark:text-purple-400 mr-2"></i>Payment Records
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Manage all payments</p>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 px-6 py-4 rounded-xl mb-6 flex items-center justify-between">
                <div><i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($_SESSION['success_message']) ?></div>
                <button onclick="this.parentElement.style.display='none'" class="text-green-700 dark:text-green-300 hover:text-green-900"><i class="fas fa-times"></i></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 px-6 py-4 rounded-xl mb-6 flex items-center justify-between">
                <div><i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($_SESSION['error_message']) ?></div>
                <button onclick="this.parentElement.style.display='none'" class="text-red-700 dark:text-red-300 hover:text-red-900"><i class="fas fa-times"></i></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">#</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">User</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Loan</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Amount</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Method</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Refund Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Submitted</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($payments)): ?>
                            <?php foreach ($payments as $payment): 
                                $user = $users[$payment->getUserId()] ?? null;
                                $amountValue = $payment->getAmount()->getAmount();
                                $statusValue = $payment->getStatus()->getValue(); // 'approved', 'pending_approval', 'rejected'
                                $refundStatus = $payment->getRefundStatus() ?? 'none';
                            ?>
                                <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-900/30 transition">
                                    <td class="px-4 py-3"><?= $payment->getId() ?></td>
                                    <td class="px-4 py-3"><?= htmlspecialchars($user ? $user->getName() : 'Unknown') ?></td>
                                    <td class="px-4 py-3">Loan #<?= $payment->getLoanId() ?></td>
                                    <td class="px-4 py-3"><?= number_format($amountValue, 2) ?> MMK</td>
                                    <td class="px-4 py-3"><?= ucfirst($payment->getPaymentMethod()) ?></td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            <?= $statusValue === 'approved' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 
                                               ($statusValue === 'rejected' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : 
                                               'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400') ?>">
                                            <?= ucfirst(str_replace('_', ' ', $statusValue)) ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?php
                                            $refundLabel = ucfirst(str_replace('_', ' ', $refundStatus));
                                            $refundColor = match($refundStatus) {
                                                'completed' => 'text-green-600',
                                                'pending'   => 'text-yellow-600',
                                                'none'      => 'text-gray-400',
                                                default     => 'text-gray-600'
                                            };
                                        ?>
                                        <span class="<?= $refundColor ?>"><?= $refundLabel ?></span>
                                    </td>
                                    <td class="px-4 py-3"><?= $payment->getSubmittedAt() ? $payment->getSubmittedAt()->format('Y-m-d H:i') : '—' ?></td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <?php if ($statusValue === 'pending_approval'): ?>
                                            <form action="<?= BASE_URL ?>/librarian/payments/<?= $payment->getId() ?>/approve" method="POST" class="inline">
                                                <button type="submit" class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 mr-2" title="Approve">
                                                    <i class="fas fa-check-circle"></i>
                                                </button>
                                            </form>
                                            <form action="<?= BASE_URL ?>/librarian/payments/<?= $payment->getId() ?>/reject" method="POST" class="inline">
                                                <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" title="Reject">
                                                    <i class="fas fa-times-circle"></i>
                                                </button>
                                            </form>
                                        <?php elseif ($statusValue === 'approved' && $refundStatus === 'none'): ?>
                                            <!-- ✅ Refund button (only if approved and not refunded) -->
                                            <a href="<?= BASE_URL ?>/librarian/payments/<?= $payment->getId() ?>/refund" 
                                               class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 mr-2" 
                                               title="Refund">
                                                <i class="fas fa-undo-alt"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/librarian/payments/invoice/<?= $payment->getId() ?>" 
                                               class="text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300" 
                                               title="View Invoice">
                                                <i class="fas fa-file-invoice"></i>
                                            </a>
                                        <?php elseif ($statusValue === 'approved' && $refundStatus === 'completed'): ?>
                                            <!-- Already refunded – show badge -->
                                            <span class="text-xs text-green-600 dark:text-green-400">Refunded</span>
                                            <a href="<?= BASE_URL ?>/librarian/payments/invoice/<?= $payment->getId() ?>" 
                                               class="text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300 ml-2" 
                                               title="View Invoice">
                                                <i class="fas fa-file-invoice"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-xs text-gray-400">Done</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No payments recorded.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php else: ?>
        <!-- ============================================================ -->
        <!-- 🔹 DASHBOARD VIEW – Default                                   -->
        <!-- ============================================================ -->
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Overview of library operations</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-500 dark:text-gray-400"><?= date('F j, Y') ?></span>
                <button class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    <i class="fas fa-bell text-gray-600 dark:text-gray-300"></i>
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Books</p>
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400"><?= number_format($stats['totalBooks'] ?? 0) ?></p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                        <i class="fas fa-book text-blue-600 dark:text-blue-400 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Available</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400"><?= number_format($stats['available'] ?? 0) ?></p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 dark:text-green-400 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Borrowed</p>
                        <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400"><?= number_format($stats['borrowed'] ?? 0) ?></p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/30 rounded-full flex items-center justify-center">
                        <i class="fas fa-hand-holding text-yellow-600 dark:text-yellow-400 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Overdue</p>
                        <p class="text-2xl font-bold text-red-600 dark:text-red-400"><?= number_format($stats['overdue'] ?? 0) ?></p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                        <i class="fas fa-clock text-red-600 dark:text-red-400 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4"><i class="fas fa-bolt text-yellow-500 mr-2"></i> Quick Actions</h3>
                <div class="grid grid-cols-2 gap-3">
                    <a href="<?= BASE_URL ?>/librarian/dashboard?page=books_create" class="bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg p-4 text-center transition">
                        <i class="fas fa-plus-circle text-blue-600 dark:text-blue-400 text-xl mb-1"></i>
                        <p class="text-xs text-gray-700 dark:text-gray-300">Add Book</p>
                    </a>
                    <a href="<?= BASE_URL ?>/librarian/loans/create" class="bg-indigo-50 dark:bg-indigo-900/20 hover:bg-indigo-100 dark:hover:bg-indigo-900/30 rounded-lg p-4 text-center transition">
                        <i class="fas fa-hand-holding-heart text-indigo-600 dark:text-indigo-400 text-xl mb-1"></i>
                        <p class="text-xs text-gray-700 dark:text-gray-300">Issue Book</p>
                    </a>
                    <a href="<?= BASE_URL ?>/librarian/users/create" class="bg-purple-50 dark:bg-purple-900/20 hover:bg-purple-100 dark:hover:bg-purple-900/30 rounded-lg p-4 text-center transition">
                        <i class="fas fa-user-plus text-purple-600 dark:text-purple-400 text-xl mb-1"></i>
                        <p class="text-xs text-gray-700 dark:text-gray-300">Add User</p>
                    </a>
                    <a href="<?= BASE_URL ?>/librarian/categories" class="bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30 rounded-lg p-4 text-center transition">
                        <i class="fas fa-tags text-green-600 dark:text-green-400 text-xl mb-1"></i>
                        <p class="text-xs text-gray-700 dark:text-gray-300">Categories</p>
                    </a>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4"><i class="fas fa-chart-simple text-green-500 mr-2"></i> Library Stats</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Total Users</span>
                        <span class="font-bold text-gray-900 dark:text-white"><?= number_format($stats['totalUsers'] ?? 0) ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Active Loans</span>
                        <span class="font-bold text-indigo-600 dark:text-indigo-400"><?= number_format($stats['activeLoans'] ?? 0) ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Overdue</span>
                        <span class="font-bold text-red-600 dark:text-red-400"><?= number_format($stats['overdue'] ?? 0) ?></span>
                    </div>
                    <div class="flex justify-between items-center border-t border-gray-200 dark:border-gray-700 pt-3">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Return Rate</span>
                        <?php 
                            $total = ($stats['borrowed'] ?? 0) + ($stats['available'] ?? 0);
                            $returnRate = $total > 0 ? round(($stats['available'] ?? 0) / $total * 100) : 0;
                        ?>
                        <span class="font-bold text-blue-600 dark:text-blue-400"><?= $returnRate ?>%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white"><i class="fas fa-clock text-gray-400 mr-2"></i> Recent Activities</h3>
                <a href="<?= BASE_URL ?>/librarian/dashboard?page=loans" class="text-sm text-blue-600 hover:underline">View All Loans →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="text-left py-2 px-3 text-gray-500 dark:text-gray-400">User</th>
                            <th class="text-left py-2 px-3 text-gray-500 dark:text-gray-400">Action</th>
                            <th class="text-left py-2 px-3 text-gray-500 dark:text-gray-400">Book</th>
                            <th class="text-left py-2 px-3 text-gray-500 dark:text-gray-400">Date</th>
                            <th class="text-left py-2 px-3 text-gray-500 dark:text-gray-400">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($stats['recentActivities'])): ?>
                            <?php foreach ($stats['recentActivities'] as $activity): ?>
                                <tr class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-900/50">
                                    <td class="py-2 px-3 text-gray-900 dark:text-white"><?= htmlspecialchars($activity['user']) ?></td>
                                    <td class="py-2 px-3 text-gray-600 dark:text-gray-300"><?= htmlspecialchars($activity['action']) ?></td>
                                    <td class="py-2 px-3 text-gray-600 dark:text-gray-300"><?= htmlspecialchars($activity['book']) ?></td>
                                    <td class="py-2 px-3 text-gray-500 dark:text-gray-400"><?= htmlspecialchars($activity['date']) ?></td>
                                    <td class="py-2 px-3">
                                        <?php if ($activity['status'] === 'returned'): ?>
                                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">Returned</span>
                                        <?php elseif ($activity['status'] === 'overdue'): ?>
                                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">Overdue</span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Active</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="py-4 text-center text-gray-500 dark:text-gray-400">No recent activities.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>