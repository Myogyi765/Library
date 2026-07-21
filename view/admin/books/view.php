<?php
// view/admin/books/view.php
$book = $book ?? null;
$categoryName = $categoryName ?? 'Uncategorized';

if (!$book) {
    $_SESSION['error_message'] = 'Book not found.';
    header('Location: ' . BASE_URL . '/admin/books');
    exit;
}

// ✅ Fix cover image source – handle both local and external URLs
$coverImage = $book->getCoverImage();
$imageSrc = null;
if ($coverImage) {
    if (filter_var($coverImage, FILTER_VALIDATE_URL)) {
        $imageSrc = $coverImage; // External URL (e.g., OpenLibrary)
    } else {
        $imageSrc = BASE_URL . $coverImage; // Local path
    }
}
?>

<style>
    .profile-card { transition: all 0.2s ease; }
    .profile-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 0.6rem 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .dark .info-row { border-bottom-color: #1e293b; }
    .info-row:last-child { border-bottom: none; }
    .info-label { color: #64748b; font-size: 0.85rem; font-weight: 500; }
    .dark .info-label { color: #94a3b8; }
    .info-value { color: #1e293b; font-weight: 600; font-size: 0.9rem; }
    .dark .info-value { color: #e2e8f0; }
    
    /* Button Group – improved styling */
    .btn-group { display: flex; flex-wrap: wrap; gap: 0.5rem; }
    .btn-group .btn {
        padding: 0.5rem 1.2rem;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.15s;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        border: none;
        cursor: pointer;
        text-decoration: none;
        line-height: 1.4;
    }
    .btn-group .btn:hover { 
        transform: translateY(-2px); 
        box-shadow: 0 4px 12px rgba(0,0,0,0.15); 
    }
    .btn-edit { 
        background: #f59e0b; 
        color: white; 
    }
    .btn-edit:hover { 
        background: #d97706; 
    }
    .btn-delete { 
        background: #ef4444; 
        color: white; 
    }
    .btn-delete:hover { 
        background: #dc2626; 
    }
    .btn-back { 
        background: #e2e8f0; 
        color: #475569; 
    }
    .dark .btn-back { 
        background: #334155; 
        color: #94a3b8; 
    }
    .btn-back:hover { 
        background: #cbd5e1; 
    }
    .dark .btn-back:hover { 
        background: #475569; 
    }
    /* Cover image */
    .book-cover {
        width: 80px;
        height: 110px;
        object-fit: cover;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        background: #e2e8f0;
    }
    .book-cover-placeholder {
        width: 80px;
        height: 110px;
        background: #e2e8f0;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-size: 2rem;
    }
    .dark .book-cover-placeholder {
        background: #1e293b;
        color: #64748b;
    }
</style>

<div class="flex flex-col space-y-5">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                <i class="fas fa-book text-blue-600 dark:text-blue-400 mr-2"></i>Book Details
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">View book information</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/books" class="btn btn-back">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <!-- Profile Card -->
    <div class="profile-card bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
        <!-- Header with Cover Image -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30 flex items-center gap-4">
            <?php if ($imageSrc): ?>
                <img src="<?= $imageSrc ?>" alt="<?= htmlspecialchars($book->getTitle()) ?>" 
                     class="book-cover"
                     onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'book-cover-placeholder\'><i class=\'fas fa-book\'></i></div>'">
            <?php else: ?>
                <div class="book-cover-placeholder">
                    <i class="fas fa-book"></i>
                </div>
            <?php endif; ?>
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($book->getTitle()) ?></h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">by <?= htmlspecialchars($book->getAuthor()) ?></p>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs text-gray-500 dark:text-gray-400">ISBN: <?= htmlspecialchars($book->getIsbn() ?? 'N/A') ?></span>
                </div>
            </div>
        </div>

        <!-- Details -->
        <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-1">
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-tag text-blue-500 mr-2 w-4"></i>Title</span>
                    <span class="info-value"><?= htmlspecialchars($book->getTitle()) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-user text-blue-500 mr-2 w-4"></i>Author</span>
                    <span class="info-value"><?= htmlspecialchars($book->getAuthor()) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-barcode text-blue-500 mr-2 w-4"></i>ISBN</span>
                    <span class="info-value"><?= htmlspecialchars($book->getIsbn() ?? 'N/A') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-folder text-blue-500 mr-2 w-4"></i>Category</span>
                    <span class="info-value"><?= htmlspecialchars($categoryName) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-cubes text-blue-500 mr-2 w-4"></i>Quantity</span>
                    <span class="info-value"><?= $book->getQuantity() ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-check-circle text-blue-500 mr-2 w-4"></i>Available</span>
                    <span class="info-value text-green-600"><?= $book->getAvailableQuantity() ?></span>
                </div>
                <?php if ($book->getDescription()): ?>
                    <div class="info-row col-span-2">
                        <span class="info-label"><i class="fas fa-align-left text-blue-500 mr-2 w-4"></i>Description</span>
                        <span class="info-value text-sm font-normal"><?= nl2br(htmlspecialchars($book->getDescription())) ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Actions -->
        <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30">
            <div class="btn-group">
                <a href="<?= BASE_URL ?>/admin/books/edit/<?= $book->getId() ?>" class="btn btn-edit">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <form action="<?= BASE_URL ?>/admin/books/delete/<?= $book->getId() ?>" method="POST" class="inline" 
                      onsubmit="return confirm('Are you sure you want to delete this book? This action cannot be undone.')">
                    <button type="submit" class="btn btn-delete">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>