<?php
/**
 * @var array $books  Array of Book entities
 */
?>

<style>
    /* ===== Clean & Modern Design – Blue Accent ===== */
    .book-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
    }
    .book-table thead th {
        background: #f8fafc;
        color: #1e293b;
        font-weight: 600;
        padding: 12px 16px;
        border-bottom: 2px solid #e2e8f0;
        text-align: left;
    }
    .dark .book-table thead th {
        background: #1e293b;
        color: #e2e8f0;
        border-bottom-color: #334155;
    }
    .book-table tbody tr {
        border-bottom: 1px solid #f1f5f9;
        transition: background 0.15s;
    }
    .dark .book-table tbody tr {
        border-bottom-color: #1e293b;
    }
    .book-table tbody tr:hover {
        background: #f1f5f9;
    }
    .dark .book-table tbody tr:hover {
        background: #1e293b;
    }
    .book-table tbody td {
        padding: 12px 16px;
        vertical-align: middle;
    }

    /* Book Cover */
    .book-cover {
        width: 40px;
        height: 50px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.8rem;
        color: white;
        background: #2563eb;
        flex-shrink: 0;
        margin-right: 12px;
        object-fit: cover;
        overflow: hidden;
    }
    .dark .book-cover {
        background: #3b82f6;
    }
    .book-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .book-cover .no-image {
        font-size: 1.2rem;
        color: rgba(255,255,255,0.7);
    }

    /* Status Badge */
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
    .status-badge.available { background: #dcfce7; color: #166534; }
    .dark .status-badge.available { background: #14532d; color: #86efac; }
    .status-badge.limited { background: #fef3c7; color: #92400e; }
    .dark .status-badge.limited { background: #78350f; color: #fcd34d; }
    .status-badge.outofstock { background: #fee2e2; color: #991b1b; }
    .dark .status-badge.outofstock { background: #7f1d1d; color: #fca5a5; }

    /* Action Buttons */
    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 6px;
        transition: all 0.15s;
        color: #94a3b8;
        background: transparent;
        border: none;
        cursor: pointer;
        text-decoration: none;
    }
    .action-btn:hover { background: #f1f5f9; color: #2563eb; }
    .action-btn.delete:hover { color: #dc2626; }
    .dark .action-btn { color: #64748b; }
    .dark .action-btn:hover { background: #1e293b; color: #60a5fa; }
    .dark .action-btn.delete:hover { color: #f87171; }
    .action-btn.view:hover { color: #6b7280; }

    /* Search Input */
    .search-input {
        padding: 0.5rem 1rem 0.5rem 2.5rem;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: white;
        font-size: 0.9rem;
        width: 220px;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .dark .search-input {
        background: #1e293b;
        border-color: #334155;
        color: #e2e8f0;
    }
    .search-input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        width: 260px;
    }

    /* Empty State */
    .empty-state-icon {
        opacity: 0.5;
        transition: opacity 0.3s;
    }
    .empty-state-icon:hover { opacity: 0.8; }

    /* Serial number */
    .serial-number {
        text-align: center;
        font-weight: 500;
        color: #6b7280;
        width: 50px;
    }
    .dark .serial-number {
        color: #9ca3af;
    }

    /* Pagination */
    .pagination-btn {
        padding: 0.3rem 0.8rem;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        background: white;
        color: #475569;
        font-size: 0.85rem;
        transition: all 0.2s;
        cursor: pointer;
    }
    .dark .pagination-btn {
        background: #1e293b;
        border-color: #334155;
        color: #94a3b8;
    }
    .pagination-btn:hover:not(:disabled) {
        background: #f1f5f9;
        border-color: #2563eb;
    }
    .pagination-btn:disabled { opacity: 0.4; cursor: not-allowed; }
    .pagination-btn.active {
        background: #2563eb;
        border-color: #2563eb;
        color: white;
    }
    .dark .pagination-btn.active {
        background: #3b82f6;
        border-color: #3b82f6;
        color: white;
    }

    @media (max-width: 640px) {
        .book-table thead th, .book-table tbody td {
            padding: 8px 10px;
            font-size: 0.8rem;
        }
        .search-input { width: 140px; }
        .search-input:focus { width: 180px; }
        .book-cover { width: 30px; height: 38px; margin-right: 6px; }
        .serial-number { width: 35px; }
    }
</style>

<div class="flex flex-col">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-5">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                <i class="fas fa-book text-blue-600 dark:text-blue-400 mr-2"></i>Book Management
            </h1>
            <span class="bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 text-sm font-medium px-3 py-1 rounded-full">
                <?= count($books) ?> books
            </span>
        </div>
        <div class="flex items-center gap-3 mt-3 md:mt-0">
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500"></i>
                <input type="text" placeholder="Search books..." class="search-input" id="bookSearch">
            </div>
            <a href="<?= BASE_URL ?>/admin/books/create"
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition inline-flex items-center text-sm">
                <i class="fas fa-plus mr-2"></i>Add New Book
            </a>
        </div>
    </div>

    <!-- Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 px-4 py-2 rounded-lg mb-4 flex items-center justify-between">
            <span><i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($_SESSION['success_message']) ?></span>
            <button onclick="this.parentElement.style.display='none'" class="text-green-700 dark:text-green-300 hover:text-green-900"><i class="fas fa-times"></i></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-2 rounded-lg mb-4 flex items-center justify-between">
            <span><i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($_SESSION['error_message']) ?></span>
            <button onclick="this.parentElement.style.display='none'" class="text-red-700 dark:text-red-300 hover:text-red-900"><i class="fas fa-times"></i></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="book-table">
                <thead>
                    <tr>
                        <th class="serial-number">#</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Available</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($books)): ?>
                        <?php $counter = 1; ?>
                        <?php foreach ($books as $book): ?>
                            <?php 
                                $available = $book->getAvailableQuantity();
                                $total = $book->getQuantity();
                                
                                if ($available > 0 && $available == $total) {
                                    $statusText = 'Available';
                                    $statusClass = 'available';
                                    $statusIcon = 'fa-check-circle';
                                } elseif ($available > 0 && $available < $total) {
                                    $statusText = 'Limited (' . $available . '/' . $total . ')';
                                    $statusClass = 'limited';
                                    $statusIcon = 'fa-exclamation-triangle';
                                } else {
                                    $statusText = 'Out of Stock';
                                    $statusClass = 'outofstock';
                                    $statusIcon = 'fa-times-circle';
                                }

                                // ---- ✅ CORRECTED: Image Path using basename() ----
                                $cover = $book->getCoverImage();
                                $hasImage = false;
                                $imageUrl = '';

                                if ($cover) {
                                    // Extract only the filename (remove any directory prefix)
                                    $filename = basename($cover); // e.g., '6a57034c1dd7b.jpg'
                                    
                                    // Check possible physical locations
                                    $possiblePaths = [
                                        BASE_PATH . '/public/uploads/books/' . $filename,
                                        BASE_PATH . '/uploads/books/' . $filename,
                                        BASE_PATH . '/public/storage/upload/books/' . $filename,
                                        BASE_PATH . '/storage/app/public/upload/books/' . $filename,
                                    ];
                                    
                                    foreach ($possiblePaths as $path) {
                                        if (file_exists($path)) {
                                            $hasImage = true;
                                            // Build the web URL (assuming images are served from public/uploads/books/)
                                            $imageUrl = BASE_URL . '/uploads/books/' . $filename;
                                            break;
                                        }
                                    }
                                }
                            ?>
                            <tr>
                                <td class="serial-number"><?= $counter++ ?></td>
                                <td>
                                    <div class="flex items-center">
                                        <span class="book-cover">
                                            <?php if ($hasImage): ?>
                                                <img src="<?= $imageUrl ?>" 
                                                     alt="<?= htmlspecialchars($book->getTitle()) ?>"
                                                     onerror="this.onerror=null; this.parentElement.innerHTML='<span class=\'no-image\'><i class=\'fas fa-book\'></i></span>'">
                                            <?php else: ?>
                                                <span class="no-image"><i class="fas fa-book"></i></span>
                                            <?php endif; ?>
                                        </span>
                                        <span class="font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($book->getTitle()) ?></span>
                                    </div>
                                </td>
                                <td class="text-gray-600 dark:text-gray-300"><?= htmlspecialchars($book->getAuthor()) ?></td>
                                <td class="text-gray-600 dark:text-gray-300">
                                    <?= htmlspecialchars($book->getCategoryId()) ?>
                                </td>
                                <td>
                                    <span class="status-badge <?= $statusClass ?>">
                                        <i class="fas <?= $statusIcon ?>"></i>
                                        <?= $statusText ?>
                                    </span>
                                </td>
                                <td class="text-gray-600 dark:text-gray-300">
                                    <?= $available ?> / <?= $total ?>
                                </td>
                                <td class="text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="<?= BASE_URL ?>/admin/books/show?id=<?= $book->getId() ?>"
                                           class="action-btn view" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/admin/books/edit?id=<?= $book->getId() ?>"
                                           class="action-btn" title="Edit">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/admin/books/delete?id=<?= $book->getId() ?>"
                                           class="action-btn delete" title="Delete"
                                           onclick="return confirm('Are you sure you want to delete this book? This action cannot be undone.')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="py-12 text-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-book-open empty-state-icon text-4xl text-gray-300 dark:text-gray-600 mb-2 block"></i>
                                <p class="text-lg font-medium">No books found</p>
                                <p class="text-sm">There are currently no books in the library.</p>
                                <a href="<?= BASE_URL ?>/admin/books/create" class="text-blue-600 dark:text-blue-400 hover:underline mt-2 inline-block">
                                    <i class="fas fa-plus mr-1"></i> Add your first book
                                </a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <!-- Table footer -->
        <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30 flex items-center justify-between text-sm rounded-b-xl">
            <span class="text-gray-600 dark:text-gray-400">
                Showing <strong><?= count($books) ?></strong> book<?= count($books) > 1 ? 's' : '' ?>
            </span>
            <div class="flex items-center gap-2">
                <button class="pagination-btn" disabled>Previous</button>
                <button class="pagination-btn active">1</button>
                <button class="pagination-btn" disabled>Next</button>
            </div>
        </div>
    </div>
</div>

<!-- Search Function (JavaScript) -->
<script>
document.getElementById('bookSearch')?.addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('.book-table tbody tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});
</script>