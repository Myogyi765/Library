<?php
// view/librarian/books/index.php
// Variables available:
//   $books (array of Book entities)
//   $categories (array of Category entities)
//   $categoryMap (associative array: category_id => category_name)
//   $currentPage, $totalPages, $totalBooks, $perPage (pagination)
//   $search, $categoryId (filter parameters)
?>
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            <i class="fas fa-book text-blue-600 mr-2"></i>Book Management
        </h1>
        <p class="text-sm text-gray-700 dark:text-gray-400">
            Manage library catalog
            <span class="ml-2 text-xs bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full">
                <?= number_format($totalBooks ?? 0) ?> books total
            </span>
        </p>
    </div>
    <a href="<?= BASE_URL ?>/librarian/books/create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2 transition shadow-md hover:shadow-lg">
        <i class="fas fa-plus"></i> Add Book
    </a>
</div>

<!-- ─── Search & Filter ─── -->
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 mb-6">
    <form action="<?= BASE_URL ?>/librarian/books" method="GET" class="flex flex-wrap items-center gap-3">
        <div class="flex-1 min-w-[200px]">
            <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>" 
                   placeholder="Search by title or author..." 
                   class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm">
        </div>
        
        <div>
            <select name="category" class="border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat->getId() ?>" <?= (($categoryId ?? 0) == $cat->getId()) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat->getName()) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition">
            <i class="fas fa-search mr-1"></i> Filter
        </button>
        
        <a href="<?= BASE_URL ?>/librarian/books" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 text-sm">
            <i class="fas fa-times"></i> Clear
        </a>
    </form>
</div>

<!-- ─── Books Table ─── -->
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-400 uppercase">Cover</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-400 uppercase">Title</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-400 uppercase">Author</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-400 uppercase">Category</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-400 uppercase">Qty</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-400 uppercase">Available</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 dark:text-gray-400 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <?php if (!empty($books)): ?>
                    <?php $counter = (($currentPage ?? 1) - 1) * ($perPage ?? 10) + 1; ?>
                    <?php foreach ($books as $book): 
                        $cover = $book->getCoverImage();
                        $imageSrc = null;
                        if ($cover) {
                            if (strpos($cover, 'http://') === 0 || strpos($cover, 'https://') === 0) {
                                $imageSrc = $cover;
                            } else {
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
                                $categoryIdVal = $book->getCategoryId();
                                echo htmlspecialchars($categoryMap[$categoryIdVal] ?? 'N/A');
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
                            <a href="<?= BASE_URL ?>/librarian/books/create" class="text-blue-600 dark:text-blue-400 hover:underline">Add your first book</a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- ─── PAGINATION ─── -->
    <?php if (($totalPages ?? 0) > 1): ?>
    <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30 flex flex-col sm:flex-row items-center justify-between gap-3 rounded-b-xl">
        <span class="text-sm text-gray-600 dark:text-gray-400">
            Showing <strong><?= count($books) ?></strong> of <strong><?= number_format($totalBooks ?? 0) ?></strong> books
            <span class="text-xs text-gray-400 dark:text-gray-500">(Page <?= $currentPage ?? 1 ?> of <?= $totalPages ?? 1 ?>)</span>
        </span>
        
        <div class="flex items-center gap-1.5 flex-wrap">
            <?php
            $current = $currentPage ?? 1;
            $total = $totalPages ?? 1;
            $queryParams = http_build_query([
                'search' => $search ?? '',
                'category' => $categoryId ?? ''
            ]);
            ?>
            
            <!-- Previous -->
            <?php if ($current > 1): ?>
                <a href="?<?= $queryParams ?>&page_num=<?= $current - 1 ?>" 
                   class="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-sm">
                    <i class="fas fa-chevron-left"></i>
                </a>
            <?php else: ?>
                <button class="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 opacity-40 cursor-not-allowed text-sm" disabled>
                    <i class="fas fa-chevron-left"></i>
                </button>
            <?php endif; ?>

            <?php
            $start = max(1, $current - 2);
            $end = min($total, $current + 2);
            ?>
            
            <?php if ($start > 1): ?>
                <a href="?<?= $queryParams ?>&page_num=1" class="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-sm">1</a>
                <?php if ($start > 2): ?>
                    <span class="text-gray-400 dark:text-gray-500 px-1">…</span>
                <?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start; $i <= $end; $i++): ?>
                <?php if ($i == $current): ?>
                    <span class="px-3 py-1.5 rounded-lg bg-blue-600 text-white border border-blue-600 text-sm font-bold"><?= $i ?></span>
                <?php else: ?>
                    <a href="?<?= $queryParams ?>&page_num=<?= $i ?>" class="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-sm"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($end < $total): ?>
                <?php if ($end < $total - 1): ?>
                    <span class="text-gray-400 dark:text-gray-500 px-1">…</span>
                <?php endif; ?>
                <a href="?<?= $queryParams ?>&page_num=<?= $total ?>" class="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-sm"><?= $total ?></a>
            <?php endif; ?>

            <!-- Next -->
            <?php if ($current < $total): ?>
                <a href="?<?= $queryParams ?>&page_num=<?= $current + 1 ?>" 
                   class="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-sm">
                    <i class="fas fa-chevron-right"></i>
                </a>
            <?php else: ?>
                <button class="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 opacity-40 cursor-not-allowed text-sm" disabled>
                    <i class="fas fa-chevron-right"></i>
                </button>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>