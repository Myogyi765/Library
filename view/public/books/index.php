<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$pageTitle = 'Books Catalog';
include BASE_PATH . '/view/layout/header.php';

$categoryMap = [];
foreach ($categories as $cat) {
    $categoryMap[$cat->getId()] = $cat->getName();
}
?>

<!-- Page Background -->
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50/30 to-indigo-50/50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 py-6">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="relative overflow-hidden bg-white/60 dark:bg-gray-800/60 backdrop-blur-xl rounded-2xl shadow-xl border border-white/30 dark:border-gray-700/30 p-5 mb-6">
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-blue-500/10 dark:bg-blue-400/5 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-indigo-500/10 dark:bg-indigo-400/5 rounded-full blur-3xl"></div>
            
            <div class="relative flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="hidden sm:flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 shadow-lg shadow-blue-500/25">
                        <i class="fas fa-book-open text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
                            Book Catalog
                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                <?= count($books) ?>
                            </span>
                        </h1>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 flex items-center gap-1.5">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-gradient-to-r from-blue-500 to-indigo-500"></span>
                            Discover your next read
                        </p>
                    </div>
                </div>

                <!-- Search + Category Dropdown -->
                <div class="flex flex-wrap items-center gap-2 bg-white/50 dark:bg-gray-700/50 rounded-full p-1 shadow-inner border border-gray-200/50 dark:border-gray-600/50">
                    <div class="relative flex-1 min-w-[140px]">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-xs"></i>
                        <input type="text" id="searchInput" placeholder="Search title, author..." 
                               class="w-full pl-8 pr-3 py-2 text-xs rounded-full bg-transparent border-0 focus:ring-0 focus:outline-none dark:text-white placeholder-gray-500 dark:placeholder-gray-400">
                    </div>
                    <div class="w-px h-6 bg-gray-300/50 dark:bg-gray-600/50"></div>
                    <div class="relative">
                        <select id="categoryFilter" class="text-xs rounded-full bg-transparent border-0 px-4 py-2 pr-8 focus:ring-0 focus:outline-none dark:text-white appearance-none cursor-pointer min-w-[130px]">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat->getId() ?>"><?= htmlspecialchars($cat->getName()) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-[10px] pointer-events-none"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Pills -->
        <div class="relative mb-5">
            <div class="flex flex-nowrap overflow-x-auto gap-2 pb-3 scrollbar-hide category-pills-wrapper">
                <button class="category-pill active px-4 py-1.5 text-xs font-medium rounded-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-md shadow-blue-500/20 dark:shadow-blue-900/30 whitespace-nowrap hover:shadow-lg hover:scale-105 transition-all duration-200 flex-shrink-0" data-category="">✨ All</button>
                <?php 
                $categoryEmojis = [
                    'Programming' => '💻',
                    'Networking'   => '🌐',
                    'History'      => '🏛️',
                    'Business'     => '💼',
                    'Art'          => '🎨',
                    'Travel'       => '✈️',
                    'Cooking'      => '🍳',
                    'Healthy'      => '💚',
                    'Science'      => '🔬',
                    'Others'       => '📦'
                ];
                foreach ($categories as $cat): 
                    $emoji = $categoryEmojis[$cat->getName()] ?? '📘';
                ?>
                    <button class="category-pill px-4 py-1.5 text-xs font-medium rounded-full bg-white/70 dark:bg-gray-800/70 text-gray-700 dark:text-gray-300 border border-gray-200/50 dark:border-gray-700/50 whitespace-nowrap hover:bg-white dark:hover:bg-gray-700 hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 flex-shrink-0" data-category="<?= $cat->getId() ?>">
                        <?= $emoji ?> <?= htmlspecialchars($cat->getName()) ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <div class="absolute right-0 top-0 bottom-0 w-12 bg-gradient-to-l from-slate-50 via-slate-50/80 to-transparent dark:from-gray-900 dark:via-gray-900/80 flex items-center justify-end pr-2 pointer-events-none">
                <i class="fas fa-chevron-right text-gray-400 dark:text-gray-500 text-xs animate-pulse"></i>
            </div>
        </div>

        <?php if (empty($books)): ?>
            <!-- Empty State -->
            <div class="text-center py-12 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-200/50 dark:border-gray-700/50 shadow-lg">
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-900/30 dark:to-indigo-900/30 mb-4">
                    <i class="fas fa-book-open text-4xl text-blue-600 dark:text-blue-400"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 dark:text-white">No books found</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Try adjusting your search or filter</p>
            </div>
        <?php else: ?>
            <!-- Book Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4" id="bookGrid">
                <?php foreach ($books as $book): 
                    $inStock = $book->getAvailableQuantity() > 0;
                ?>
                <div class="group bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-2xl hover:shadow-blue-500/10 dark:hover:shadow-blue-900/20 transition-all duration-300 overflow-hidden border border-gray-200/50 dark:border-gray-700/50 flex flex-col h-full book-card hover:-translate-y-1.5">
                    
                    <!-- Cover Image -->
                    <div class="relative overflow-hidden bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 aspect-square flex-shrink-0">
                        <?php if ($book->getCoverImage()): ?>
                            <img src="<?= BASE_URL . $book->getCoverImage() ?>" 
                                 alt="<?= htmlspecialchars($book->getTitle()) ?>" 
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500 ease-out">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-gray-400 dark:text-gray-500">
                                <i class="fas fa-book text-5xl opacity-20 group-hover:opacity-40 transition-opacity"></i>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Stock Badge -->
                        <span class="absolute top-2 right-2 px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider shadow-lg 
                            <?= $inStock ? 'bg-emerald-500/90 text-white' : 'bg-rose-500/90 text-white' ?>">
                            <?= $inStock ? 'In Stock' : 'Out of Stock' ?>
                        </span>
                    </div>

                    <!-- Book Info -->
                    <div class="p-3 flex flex-col flex-1">
                        <h3 class="text-xs font-bold text-gray-900 dark:text-white leading-tight line-clamp-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" 
                            title="<?= htmlspecialchars($book->getTitle()) ?>">
                            <?= htmlspecialchars($book->getTitle()) ?>
                        </h3>
                        <p class="text-[10px] text-gray-600 dark:text-gray-400 mt-0.5 truncate">
                            <?= htmlspecialchars($book->getAuthor()) ?>
                        </p>

                        <div class="mt-2 flex flex-wrap items-center gap-1.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[8px] font-semibold bg-blue-100/80 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 truncate max-w-[70px]">
                                <?= htmlspecialchars($categoryMap[$book->getCategoryId()] ?? 'Uncategorized') ?>
                            </span>
                            <span class="inline-flex items-center text-[8px] text-gray-500 dark:text-gray-400">
                                <i class="fas fa-copy mr-0.5 text-gray-400 dark:text-gray-500"></i>
                                <?= $book->getAvailableQuantity() ?>
                            </span>
                        </div>

                        <!-- View Details button – always blue -->
                        <a href="<?= BASE_URL ?>/books/<?= $book->getId() ?>" 
                           class="mt-auto pt-1.5 w-full inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-[10px] font-medium px-2 py-1.5 rounded-lg transition-all duration-200 shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                            <i class="fas fa-eye mr-1.5 text-[9px]"></i> View Details
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Category Pills
    const categoryPills = document.querySelectorAll('.category-pill');
    const categoryFilter = document.getElementById('categoryFilter');

    categoryPills.forEach(pill => {
        pill.addEventListener('click', function() {
            categoryPills.forEach(p => {
                p.classList.remove('active', 'bg-gradient-to-r', 'from-blue-600', 'to-indigo-600', 'text-white', 'shadow-md', 'shadow-blue-500/20', 'dark:shadow-blue-900/30');
                p.classList.add('bg-white/70', 'dark:bg-gray-800/70', 'text-gray-700', 'dark:text-gray-300', 'border', 'border-gray-200/50', 'dark:border-gray-700/50');
            });
            this.classList.remove('bg-white/70', 'dark:bg-gray-800/70', 'text-gray-700', 'dark:text-gray-300', 'border', 'border-gray-200/50', 'dark:border-gray-700/50');
            this.classList.add('active', 'bg-gradient-to-r', 'from-blue-600', 'to-indigo-600', 'text-white', 'shadow-md', 'shadow-blue-500/20', 'dark:shadow-blue-900/30');
            categoryFilter.value = this.dataset.category;
            // Here you would filter the grid
            console.log('Filter by category:', this.dataset.category);
        });
    });

    categoryFilter.addEventListener('change', function() {
        const val = this.value;
        categoryPills.forEach(pill => {
            if (pill.dataset.category === val) {
                pill.click();
            }
        });
    });

    // Search
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        document.querySelectorAll('.book-card').forEach(card => {
            const title = card.querySelector('h3').textContent.toLowerCase();
            const author = card.querySelector('p').textContent.toLowerCase();
            card.style.display = (title.includes(query) || author.includes(query)) ? '' : 'none';
        });
    });
});
</script>

<style>
/* Professional system font stack */
body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
}

.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
.category-pill.active {
    background: linear-gradient(to right, #2563eb, #4f46e5) !important;
    color: white !important;
    box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2), 0 2px 4px -1px rgba(37, 99, 235, 0.1);
}
</style>

<?php include BASE_PATH . '/view/layout/footer.php'; ?>