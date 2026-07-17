<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$pageTitle = 'Books Catalog';
include BASE_PATH . '/view/layout/header.php';

$categoryMap = [];
foreach ($categories as $cat) {
    $categoryMap[$cat->getId()] = $cat->getName();
}
?>

<!-- ================================================================ -->
<!-- PREMIUM STYLES – Editorial Layout & Modern Glassmorphism       -->
<!-- ================================================================ -->
<style>
    :root {
        --primary: #4f46e5;          /* Premium Indigo */
        --primary-hover: #4338ca;    
        --primary-light: rgba(79, 70, 229, 0.1);
        --radius-card: 1rem;         /* Modern smooth curves */
        --radius-pill: 9999px;
        --bg-light: #f8fafc;         
        --bg-dark: #090d16;          
        --card-light: #ffffff;
        --card-dark: #121826;
        --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.02);
        --shadow-md: 0 8px 30px rgba(0, 0, 0, 0.03);
        --shadow-hover: 0 20px 40px rgba(79, 70, 229, 0.12);
        --border-light: #e2e8f0;
        --border-dark: rgba(255, 255, 255, 0.06);
    }

    /* Premium Mesh Background */
    .book-catalog {
        background-color: var(--bg-light);
        background-image: 
            radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.06) 0px, transparent 50%),
            radial-gradient(at 100% 0%, rgba(14, 165, 233, 0.05) 0px, transparent 50%),
            radial-gradient(at 50% 100%, rgba(79, 70, 229, 0.03) 0px, transparent 50%);
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        position: relative;
        overflow: hidden;
    }
    
    .dark .book-catalog {
        background-color: var(--bg-dark);
        background-image: 
            radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.12) 0px, transparent 50%),
            radial-gradient(at 100% 0%, rgba(14, 165, 233, 0.08) 0px, transparent 50%),
            radial-gradient(at 50% 100%, rgba(79, 70, 229, 0.05) 0px, transparent 50%);
    }

    .catalog-container {
        position: relative;
        z-index: 10;
    }

    /* Floating Glass Header */
    .glass-header {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid var(--border-light);
        border-radius: 1.25rem;
        padding: 1.25rem 1.75rem;
        box-shadow: var(--shadow-md);
        transition: all 0.3s ease;
    }
    .dark .glass-header {
        background: rgba(18, 24, 38, 0.7);
        border-color: var(--border-dark);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
    }

    /* Refined Search Bar Deck */
    .search-filter-wrapper {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        background: #ffffff;
        border: 1px solid var(--border-light);
        border-radius: 0.85rem;
        padding: 0.45rem 0.75rem;
        box-shadow: var(--shadow-sm);
        transition: all 0.2s ease;
    }
    .dark .search-filter-wrapper {
        background: #151c2c;
        border-color: var(--border-dark);
    }
    .search-filter-wrapper:focus-within {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
    }

    .search-filter-wrapper input {
        background: transparent !important;
        border: none !important;
        outline: none !important;
        font-size: 0.85rem;
        font-weight: 500;
        color: #0f172a;
        width: 140px;
        transition: width 0.3s ease;
    }
    .search-filter-wrapper input:focus {
        width: 180px;
    }
    .dark .search-filter-wrapper input { color: #f8fafc; }

    .search-filter-wrapper select {
        background: transparent !important;
        border: none !important;
        outline: none !important;
        font-size: 0.8rem;
        font-weight: 600;
        padding-right: 1.5rem;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right center;
        background-size: 0.9rem;
        color: #64748b;
        cursor: pointer;
    }
    .dark .search-filter-wrapper select { color: #94a3b8; }

    /* Modern Fluid Responsive Grid */
    .book-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.25rem;
    }
    @media (min-width: 640px) {
        .book-grid { grid-template-columns: repeat(3, 1fr); gap: 1.5rem; }
    }
    @media (min-width: 1024px) {
        .book-grid { grid-template-columns: repeat(4, 1fr); gap: 2rem; }
    }

    /* Exquisite Premium Cards */
    .book-card {
        background: var(--card-light);
        border: 1px solid var(--border-light);
        border-radius: var(--radius-card);
        box-shadow: var(--shadow-md);
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        position: relative;
    }
    .dark .book-card {
        background: var(--card-dark);
        border-color: var(--border-dark);
    }
    
    .book-card:hover {
        transform: translateY(-6px);
        box-shadow: var(--shadow-hover);
        border-color: rgba(79, 70, 229, 0.3);
    }
    .dark .book-card:hover {
        background: #161e30;
        border-color: rgba(79, 70, 229, 0.4);
    }

    /* Cover Wrapper with Hover Scale */
    .book-cover-wrapper {
        position: relative;
        overflow: hidden;
        background: #f1f5f9;
        aspect-ratio: 3/4; 
        display: block;
    }
    .dark .book-cover-wrapper { background: #0c101a; }
    
    .book-cover-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .book-card:hover .book-cover-wrapper img {
        transform: scale(1.05);
    }

    /* Glowing Elegant Micro-Badges */
    .stock-badge {
        position: absolute;
        top: 0.75rem;
        right: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        z-index: 10;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }
    .stock-badge.in { 
        background: rgba(220, 252, 231, 0.9); 
        color: #166534; 
        border: 1px solid rgba(74, 222, 128, 0.3);
    }
    .dark .stock-badge.in { 
        background: rgba(20, 83, 45, 0.85); 
        color: #4ade80; 
        border: 1px solid rgba(74, 222, 128, 0.2);
    }
    .stock-badge.out { 
        background: rgba(254, 226, 226, 0.9); 
        color: #991b1b; 
        border: 1px solid rgba(248, 113, 113, 0.3);
    }
    .dark .stock-badge.out { 
        background: rgba(127, 29, 29, 0.85); 
        color: #f87171; 
        border: 1px solid rgba(248, 113, 113, 0.2);
    }

    /* Polished Info Panel & Clean Typography */
    .book-info {
        padding: 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
        flex-grow: 1;
    }
    
    .book-title {
        font-size: 0.875rem; /* Clean text-sm standard */
        font-weight: 700;
        line-height: 1.3;
        color: #0f172a;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 2.25rem;
        transition: color 0.2s ease;
    }
    .dark .book-title { color: #f1f5f9; }
    .book-card:hover .book-title {
        color: var(--primary);
    }
    .dark .book-card:hover .book-title {
        color: #818cf8;
    }

    .book-author {
        font-size: 0.75rem;
        font-weight: 500;
        color: #64748b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .dark .book-author { color: #94a3b8; }

    .book-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: auto;
        padding-top: 0.5rem;
    }

    .book-category-tag {
        padding: 0.2rem 0.5rem;
        border-radius: 0.375rem;
        font-size: 0.65rem;
        font-weight: 600;
        background: var(--primary-light);
        color: var(--primary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 90px;
    }
    .dark .book-category-tag {
        background: rgba(129, 140, 248, 0.1);
        color: #a5b4fc;
    }

    .book-qty {
        font-size: 0.7rem;
        font-weight: 600;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    .dark .book-qty { color: #94a3b8; }

    /* High-End Micro Action Button */
    .btn-view {
        margin-top: 0.75rem;
        padding: 0.5rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-align: center;
        background: var(--primary);
        color: white !important;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.25rem;
    }
    .btn-view:hover { 
        background: var(--primary-hover); 
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
    }
    .dark .btn-view { background: #4f46e5; }
    .dark .btn-view:hover { background: #6366f1; }

    /* Smooth Entrance Animations */
    .book-card {
        opacity: 0;
        animation: premiumFadeUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    @keyframes premiumFadeUp {
        0% { opacity: 0; transform: translateY(12px); }
        100% { opacity: 1; transform: translateY(0); }
    }

    /* Minimalist Empty State Card */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: rgba(255, 255, 255, 0.5);
        border-radius: 1.25rem;
        border: 1px dashed var(--border-light);
        backdrop-filter: blur(10px);
    }
    .dark .empty-state {
        background: rgba(18, 24, 38, 0.5);
        border-color: var(--border-dark);
    }
</style>

<!-- ================================================================ -->
<!-- MAIN CONTENT                                                     -->
<!-- ================================================================ -->
<div class="book-catalog min-h-screen py-10 transition-colors duration-300">
    <div class="container mx-auto px-4 max-w-5xl catalog-container">

        <!-- Glass Header Banner -->
        <div class="glass-header mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-600 shadow-md">
                    <i class="fas fa-book-open text-white text-xs"></i>
                </div>
                <div>
                    <h1 class="text-base font-extrabold text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                        Books Catalog
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300">
                            <?= count($books) ?>
                        </span>
                    </h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Explore our curation of exquisite editions
                    </p>
                </div>
            </div>

            <!-- Custom Refined Controls Box -->
            <div class="search-filter-wrapper self-start md:self-auto">
                <div class="relative flex items-center">
                    <i class="fas fa-search text-gray-400 dark:text-gray-500 text-[11px] ml-1"></i>
                    <input type="text" id="searchInput" placeholder="Search title or author..." class="pl-2 pr-1 py-1 rounded-full bg-transparent border-0 focus:ring-0 focus:outline-none dark:text-white text-xs">
                </div>
                <span class="w-[1px] h-4 bg-gray-200 dark:bg-slate-700"></span>
                <div class="relative flex items-center">
                    <select id="categoryFilter" class="bg-transparent border-0 py-1 focus:ring-0 focus:outline-none dark:text-white appearance-none cursor-pointer text-xs">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat->getId() ?>"><?= htmlspecialchars($cat->getName()) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <?php if (empty($books)): ?>
            <div class="empty-state">
                <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-search text-gray-400 dark:text-gray-600 text-sm"></i>
                </div>
                <h3 class="text-sm font-bold text-gray-800 dark:text-slate-200">No items match criteria</h3>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Try relaxing your search terms or category selections.</p>
            </div>
        <?php else: ?>
            <div class="book-grid" id="bookGrid">
                <?php foreach ($books as $index => $book): 
                    $inStock = $book->getAvailableQuantity() > 0;
                    $coverImage = $book->getCoverImage();
                    if ($coverImage) {
                        if (strpos($coverImage, 'http://') === 0 || strpos($coverImage, 'https://') === 0) {
                            $coverUrl = $coverImage;
                        } else {
                            $coverUrl = BASE_URL . $coverImage;
                        }
                    } else {
                        $coverUrl = '';
                    }
                ?>
                <div class="book-card" data-category="<?= $book->getCategoryId() ?>" style="animation-delay: <?= $index * 0.015 ?>s;">
                    <!-- Cover Container -->
                    <a href="<?= BASE_URL ?>/books/<?= $book->getId() ?>" class="book-cover-wrapper">
                        <?php if ($coverUrl): ?>
                            <img src="<?= $coverUrl ?>" 
                                 alt="<?= htmlspecialchars($book->getTitle()) ?>" 
                                 loading="lazy"
                                 onerror="this.style.display='none'; this.parentElement.querySelector('.fallback-icon').style.display='flex';">
                            <div class="fallback-icon absolute inset-0 flex items-center justify-center text-gray-300 dark:text-gray-600 bg-slate-100 dark:bg-slate-900" style="display:none;">
                                <i class="fas fa-book text-2xl opacity-20"></i>
                            </div>
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-gray-300 dark:text-gray-600 bg-slate-100 dark:bg-slate-900">
                                <i class="fas fa-book text-2xl opacity-20"></i>
                            </div>
                        <?php endif; ?>
                        
                        <span class="stock-badge <?= $inStock ? 'in' : 'out' ?>">
                            <?= $inStock ? 'In Stock' : 'Out' ?>
                        </span>
                    </a>

                    <!-- Details Box -->
                    <div class="book-info">
                        <h3 class="book-title" title="<?= htmlspecialchars($book->getTitle()) ?>">
                            <?= htmlspecialchars($book->getTitle()) ?>
                        </h3>
                        <p class="book-author"><?= htmlspecialchars($book->getAuthor()) ?></p>
                        
                        <div class="book-meta">
                            <span class="book-category-tag">
                                <?= htmlspecialchars($categoryMap[$book->getCategoryId()] ?? 'General') ?>
                            </span>
                            <span class="book-qty">
                                <i class="fas fa-layer-group text-[8px] opacity-40"></i>
                                <?= $book->getAvailableQuantity() ?> Available
                            </span>
                        </div>
                        
                        <a href="<?= BASE_URL ?>/books/<?= $book->getId() ?>" class="btn-view">
                            <span>View Details</span>
                            <i class="fas fa-arrow-right text-[8px]"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ================================================================ -->
<!-- JAVASCRIPT – Smooth Reactive Filtering                           -->
<!-- ================================================================ -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const categoryFilter = document.getElementById('categoryFilter');
    const searchInput = document.getElementById('searchInput');
    const bookCards = document.querySelectorAll('.book-card');

    function filterBooks() {
        const query = searchInput.value.toLowerCase().trim();
        const cat = categoryFilter.value;

        bookCards.forEach((card) => {
            const title = card.querySelector('.book-title')?.textContent?.toLowerCase() || '';
            const author = card.querySelector('.book-author')?.textContent?.toLowerCase() || '';
            const category = card.dataset.category || '';

            const matchSearch = title.includes(query) || author.includes(query);
            const matchCategory = cat === '' || category === cat;

            if (matchSearch && matchCategory) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }

    searchInput.addEventListener('input', filterBooks);
    categoryFilter.addEventListener('change', filterBooks);
    filterBooks();
});
</script>

<?php include BASE_PATH . '/view/layout/footer.php'; ?>