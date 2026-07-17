<?php
// view/librarian/books/index.php – Full page with header and table
// Expects $books (flat array) and $categoryMap (passed from controller)
?>
<!-- ===== BOOKS PAGE HEADER ===== -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            <i class="fas fa-book text-blue-600 mr-2"></i>Book Management
        </h1>
        <p class="text-sm text-gray-700 dark:text-gray-400">Manage library catalog</p>
    </div>
    <a href="<?= BASE_URL ?>/librarian/dashboard?page=books_create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2 transition shadow-md hover:shadow-lg">
        <i class="fas fa-plus"></i> Add Book
    </a>
</div>

<!-- ===== BOOKS TABLE ===== -->
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Cover</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Title</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Author</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Category</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Qty</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Available</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <?php if (!empty($books)): ?>
                    <?php foreach ($books as $book): 
                        // ---- ✅ FIXED: Handle full URLs and relative paths ----
                        $cover = $book->getCoverImage();
                        $imageSrc = null;
                        if ($cover) {
                            // If it's a full URL (starts with http:// or https://), use directly
                            if (strpos($cover, 'http://') === 0 || strpos($cover, 'https://') === 0) {
                                $imageSrc = $cover;
                            } else {
                                // Otherwise, prepend BASE_URL (assume it's a relative path)
                                $imageSrc = BASE_URL . $cover;
                            }
                        }
                    ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30 transition">
                            <td class="px-4 py-3">
                                <?php if ($imageSrc): ?>
                                    <img src="<?= $imageSrc ?>" 
                                         alt="<?= htmlspecialchars($book->getTitle()) ?>" 
                                         class="w-12 h-16 object-cover rounded"
                                         onerror="this.style.display='none'; this.parentElement.querySelector('.fallback-icon').style.display='flex';">
                                    <div class="fallback-icon w-12 h-16 bg-gray-200 dark:bg-gray-700 rounded flex items-center justify-center text-gray-400" style="display:none;">
                                        <i class="fas fa-book text-2xl"></i>
                                    </div>
                                <?php else: ?>
                                    <div class="w-12 h-16 bg-gray-200 dark:bg-gray-700 rounded flex items-center justify-center text-gray-400">
                                        <i class="fas fa-book text-2xl"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($book->getTitle()) ?></td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300"><?= htmlspecialchars($book->getAuthor()) ?></td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                <?php
                                $categoryId = $book->getCategoryId();
                                echo htmlspecialchars($categoryMap[$categoryId] ?? 'N/A');
                                ?>
                            </td>
                            <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300"><?= $book->getQuantity() ?></td>
                            <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300"><?= $book->getAvailableQuantity() ?></td>
                            <td class="px-4 py-3 text-right">
                                <a href="<?= BASE_URL ?>/librarian/books/edit/<?= $book->getId() ?>" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 mr-2" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/librarian/books/delete/<?= $book->getId() ?>" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" title="Delete" onclick="return confirm('Are you sure you want to delete this book?')">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-700 dark:text-gray-400">
                            No books found. 
                            <a href="<?= BASE_URL ?>/librarian/dashboard?page=books_create" class="text-blue-600 dark:text-blue-400 hover:underline">Add your first book</a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>