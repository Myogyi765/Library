<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$pageTitle = 'Book Collection';
include BASE_PATH . '/view/layout/header.php';

// Dynamic Category Icon Map
$categoryIconMap = [
    'all'         => '✨',
    'art'         => '🎨',
    'business'    => '💼',
    'cooking'     => '🍳',
    'health'      => '🩺',
    'history'     => '📜',
    'networking'  => '🌐',
    'programming' => '💻',
    'science'     => '🔬',
    'travel'      => '🧳',
    'others'      => '📦',
    'general'     => '📚'
];

$categoryMap = [];
foreach ($categories as $cat) {
    $categoryMap[$cat->getId()] = $cat->getName();
}
?>

<!-- ================================================================ -->
<!-- COMPACT 4‑COLUMN GRID WITH GENEROUS SIDE MARGINS                 -->
<!-- ================================================================ -->
<style>
    :root {
        --primary: #2563eb;
        --primary-hover: #1d4ed8;
        --primary-light: rgba(37, 99, 235, 0.05);
        --accent-cartoon: #ffb938;
        --radius-card: 0.65rem;          /* smaller radius */
        --bg-light: #f8fafc;
        --bg-dark: #0f172a;
        --card-light: #ffffff;
        --card-dark: #1e293b;
        --shadow-sm: 0 1px 4px rgba(0,0,0,0.04);
        --shadow-md: 0 4px 16px rgba(15,23,42,0.04);
        --shadow-hover: 0 8px 24px rgba(37,99,235,0.10);
        --border-light: #e2e8f0;
        --border-dark: rgba(255,255,255,0.06);
    }

    .book-catalog {
        background-color: var(--bg-light);
        background-image: radial-gradient(at 50% 0%, rgba(37,99,235,0.04) 0px, transparent 60%);
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }
    .dark .book-catalog {
        background-color: var(--bg-dark);
        background-image: radial-gradient(at 50% 0%, rgba(37,99,235,0.08) 0px, transparent 50%);
    }

    .catalog-container {
        position: relative;
        z-index: 10;
    }

    /* Header – slightly tighter */
    .glass-header {
        background: var(--card-light);
        border: 1px solid var(--border-light);
        border-radius: 0.75rem;
        padding: 0.75rem 1.25rem;
        box-shadow: var(--shadow-sm);
    }
    .dark .glass-header {
        background: var(--card-dark);
        border-color: var(--border-dark);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .search-filter-wrapper {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        background: #f1f5f9;
        border: 1px solid transparent;
        border-radius: 0.6rem;
        padding: 0.2rem 0.6rem;
        transition: all 0.2s ease;
    }
    .dark .search-filter-wrapper {
        background: #0f172a;
        border-color: var(--border-dark);
    }
    .search-filter-wrapper:focus-within {
        border-color: var(--primary);
        background: var(--card-light);
        box-shadow: 0 0 0 3px rgba(37,99,235,0.08);
    }
    .dark .search-filter-wrapper:focus-within {
        background: var(--card-dark);
    }

    .search-filter-wrapper input {
        background: transparent !important;
        border: none !important;
        outline: none !important;
        font-size: 0.75rem;
        font-weight: 500;
        color: #0f172a;
        width: 130px;
        padding: 0.15rem 0;
    }
    .dark .search-filter-wrapper input { color: #f1f5f9; }

    .category-select-container {
        position: relative;
        display: flex;
        align-items: center;
        width: 130px;
    }
    .search-filter-wrapper select {
        background: transparent !important;
        border: none !important;
        outline: none !important;
        font-size: 0.7rem;
        font-weight: 600;
        width: 100%;
        padding-right: 1.25rem;
        padding-left: 0.25rem;
        appearance: none;
        color: #475569;
        cursor: pointer;
        text-align: center;
        text-align-last: center;
    }
    .dark .search-filter-wrapper select { color: #cbd5e1; }
    .search-filter-wrapper select option { text-align: left; }

    /* Grid – 4 columns on large screens */
    .book-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        margin-top: 1.5rem;
    }
    @media (min-width: 640px) {
        .book-grid { grid-template-columns: repeat(3, 1fr); gap: 1rem; }
    }
    @media (min-width: 768px) {
        .book-grid { grid-template-columns: repeat(4, 1fr); gap: 1.25rem; }
    }
    @media (min-width: 1024px) {
        .book-grid { grid-template-columns: repeat(4, 1fr); gap: 1.25rem; } /* exactly 4 */
    }

    /* Smaller cards */
    .book-card {
        background: var(--card-light);
        border: 1px solid var(--border-light);
        border-radius: var(--radius-card);
        box-shadow: var(--shadow-sm);
        transition: all 0.25s ease;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .dark .book-card {
        background: var(--card-dark);
        border-color: var(--border-dark);
    }
    .book-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-hover);
        border-color: var(--primary);
    }

    .book-cover-wrapper {
        position: relative;
        overflow: hidden;
        background: #f1f5f9;
        aspect-ratio: 3/4;
        display: block;
    }
    .dark .book-cover-wrapper { background: #0f172a; }
    .book-cover-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.2s ease;
    }
    .book-card:hover .book-cover-wrapper img {
        transform: scale(1.02);
    }

    .stock-badge {
        position: absolute;
        top: 0.3rem;
        right: 0.3rem;
        padding: 0.1rem 0.35rem;
        border-radius: 0.25rem;
        font-size: 0.5rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        color: #16a34a;
    }
    .dark .stock-badge {
        background: #0f172a;
        border-color: rgba(255,255,255,0.08);
        color: #4ade80;
    }
    .stock-badge.out {
        color: #dc2626;
        border-color: #fecaca;
    }
    .dark .stock-badge.out { color: #f87171; border-color: rgba(255,255,255,0.08); }

    /* Compact info area */
    .book-info {
        padding: 0.4rem 0.5rem 0.5rem;
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
        flex-grow: 1;
    }

    .book-title {
        font-size: 0.7rem;
        font-weight: 700;
        line-height: 1.2;
        color: #0f172a;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 1.7rem;
    }
    .dark .book-title { color: #f1f5f9; }

    .book-author {
        font-size: 0.6rem;
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
        padding-top: 0.2rem;
        border-top: 1px solid #f1f5f9;
        font-size: 0.55rem;
    }
    .dark .book-meta { border-color: rgba(255,255,255,0.05); }

    .book-category-tag {
        font-weight: 700;
        color: var(--primary);
    }
    .dark .book-category-tag { color: #60a5fa; }

    .book-qty {
        font-weight: 600;
        color: #94a3b8;
    }

    .btn-view {
        margin-top: 0.2rem;
        padding: 0.2rem 0.1rem;
        border-radius: 0.25rem;
        font-size: 0.6rem;
        font-weight: 700;
        text-align: center;
        background: #f1f5f9;
        color: #475569 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.2rem;
        transition: all 0.15s ease;
    }
    .btn-view:hover {
        background: var(--primary);
        color: white !important;
    }
    .dark .btn-view { background: #0f172a; color: #94a3b8 !important; }
    .dark .btn-view:hover { background: #2563eb; color: white !important; }

    .cartoon-float {
        animation: subtleFloat 4s ease-in-out infinite;
    }
    @keyframes subtleFloat {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-2px); }
    }

    .empty-state {
        background: var(--card-light);
        border: 1px dashed var(--border-light);
        border-radius: 0.75rem;
        padding: 2rem;
        text-align: center;
    }
    .dark .empty-state {
        background: var(--card-dark);
        border-color: var(--border-dark);
    }
</style>

<!-- ================================================================ -->
<!-- CONTAINER WITH EXTRA SIDE SPACE (wider padding, narrower max)    -->
<!-- ================================================================ -->
<div class="book-catalog min-h-screen py-8 transition-colors duration-300">
    <!-- max-w-5xl + extra px-8 md:px-16 to create generous side margins -->
    <div class="container mx-auto px-8 md:px-16 max-w-5xl catalog-container">

        <!-- Header Controls (unchanged logic) -->
        <div class="glass-header mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-500 to-blue-600 shadow-sm border border-blue-400/10">
                    <svg class="w-6 h-6 text-white cartoon-float" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                        <circle cx="12" cy="10" r="2" fill="var(--accent-cartoon)" stroke="none"></circle>
                    </svg>
                </div>
                <div>
                    <h1 class="text-base font-black text-slate-700 dark:text-white tracking-tight flex items-center gap-2">
                        <?= htmlspecialchars($pageTitle) ?>
                        <span id="headerCounter" class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                            <?= count($books) ?>
                        </span>
                    </h1>
                    <p class="text-[10px] font-semibold text-slate-400 dark:text-slate-500">
                        Explore our curation of exquisite editions
                    </p>
                </div>
            </div>

            <div class="search-filter-wrapper self-start md:self-auto shadow-sm">
                <div class="relative flex items-center">
                    <svg class="w-3 h-3 text-slate-400 mr-1" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" id="searchInput" placeholder="Search title or author..." class="py-0.5 bg-transparent border-0 focus:ring-0 focus:outline-none dark:text-white text-xs font-medium">
                </div>
                
                <span class="w-[1px] h-4 bg-slate-200 dark:bg-slate-700"></span>
                
                <div class="category-select-container">
                    <select id="categoryFilter" class="bg-transparent border-0 focus:ring-0 focus:outline-none dark:text-white appearance-none cursor-pointer text-xs font-bold tracking-wide">
                        <option value=""><?= $categoryIconMap['all'] ?> All Categories</option>
                        <?php 
                        $othersCategory = null;
                        foreach ($categories as $cat): 
                            $cleanName = strtolower(trim($cat->getName()));
                            if ($cleanName === 'others') {
                                $othersCategory = $cat;
                                continue;
                            }
                            $emoji = $categoryIconMap[$cleanName] ?? $categoryIconMap['general'];
                        ?>
                            <option value="<?= $cat->getId() ?>">
                                <?= $emoji ?> <?= htmlspecialchars($cat->getName()) ?>
                            </option>
                        <?php endforeach; ?>
                        <?php if ($othersCategory): 
                            $cleanOthersName = strtolower(trim($othersCategory->getName()));
                            $othersEmoji = $categoryIconMap[$cleanOthersName] ?? $categoryIconMap['others'];
                        ?>
                            <option value="<?= $othersCategory->getId() ?>">
                                <?= $othersEmoji ?> <?= htmlspecialchars($othersCategory->getName()) ?>
                            </option>
                        <?php endif; ?>
                    </select>
                    <div class="absolute right-0 pointer-events-none text-slate-400 flex items-center justify-center">
                        <svg class="w-2.5 h-2.5 stroke-[3.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grid -->
        <div class="relative">
            <div id="catalogEmptyState" class="empty-state <?= empty($books) ? '' : 'hidden' ?> mb-6">
                <h3 class="text-xs font-bold text-slate-800 dark:text-slate-200">No matching books discovered</h3>
            </div>

            <?php if (!empty($books)): ?>
                <div class="book-grid" id="bookGrid">
                    <?php foreach ($books as $index => $book): 
                        $inStock = $book->getAvailableQuantity() > 0;
                        $coverImage = $book->getCoverImage();
                        $coverUrl = $coverImage ? ((strpos($coverImage, 'http') === 0) ? $coverImage : BASE_URL . $coverImage) : '';
                        $cleanCatName = isset($categoryMap[$book->getCategoryId()]) ? strtolower(trim($categoryMap[$book->getCategoryId()])) : 'general';
                        $catEmoji = $categoryIconMap[$cleanCatName] ?? $categoryIconMap['general'];
                    ?>
                    <div class="book-card" data-category="<?= $book->getCategoryId() ?>">
                        <a href="<?= BASE_URL ?>/books/<?= $book->getId() ?>" class="book-cover-wrapper">
                            <?php if ($coverUrl): ?>
                                <img src="<?= $coverUrl ?>" alt="<?= htmlspecialchars($book->getTitle()) ?>" loading="lazy" onerror="this.style.display='none'; this.parentElement.querySelector('.fallback-icon').style.display='flex';">
                                <div class="fallback-icon absolute inset-0 flex items-center justify-center text-slate-300 dark:text-slate-600 bg-slate-50 dark:bg-slate-900" style="display:none;">
                                    <i class="fas fa-book text-base opacity-20"></i>
                                </div>
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center text-slate-300 dark:text-slate-600 bg-slate-50 dark:bg-slate-900">
                                    <i class="fas fa-book text-base opacity-20"></i>
                                </div>
                            <?php endif; ?>
                            
                            <span class="stock-badge <?= $inStock ? 'in' : 'out' ?>">
                                <?= $inStock ? 'In' : 'Out' ?>
                            </span>
                        </a>

                        <div class="book-info">
                            <h3 class="book-title" title="<?= htmlspecialchars($book->getTitle()) ?>">
                                <?= htmlspecialchars($book->getTitle()) ?>
                            </h3>
                            <p class="book-author"><?= htmlspecialchars($book->getAuthor()) ?></p>
                            
                            <div class="book-meta">
                                <span class="book-category-tag" title="<?= htmlspecialchars($categoryMap[$book->getCategoryId()] ?? 'General') ?>">
                                    <?= $catEmoji ?> <?= htmlspecialchars($categoryMap[$book->getCategoryId()] ?? 'General') ?>
                                </span>
                                <span class="book-qty">
                                    <?= $book->getAvailableQuantity() ?> Qty
                                </span>
                            </div>
                            
                            <a href="<?= BASE_URL ?>/books/<?= $book->getId() ?>" class="btn-view">
                                <span>Details</span>
                                <i class="fas fa-arrow-right text-[6px]"></i>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const categoryFilter = document.getElementById('categoryFilter');
    const searchInput = document.getElementById('searchInput');
    const bookGrid = document.getElementById('bookGrid');
    const emptyState = document.getElementById('catalogEmptyState');
    const headerCounter = document.getElementById('headerCounter');
    const bookCards = document.querySelectorAll('.book-card');

    function filterBooks() {
        const query = searchInput.value.toLowerCase().trim();
        const cat = categoryFilter.value;
        let matchCount = 0;

        bookCards.forEach((card) => {
            const title = card.querySelector('.book-title')?.textContent?.toLowerCase() || '';
            const author = card.querySelector('.book-author')?.textContent?.toLowerCase() || '';
            const category = card.dataset.category || '';

            const matchSearch = title.includes(query) || author.includes(query);
            const matchCategory = cat === '' || category === cat;

            if (matchSearch && matchCategory) {
                card.style.display = '';
                matchCount++;
            } else {
                card.style.display = 'none';
            }
        });

        if (headerCounter) headerCounter.textContent = matchCount;

        if (matchCount === 0) {
            if (bookGrid) bookGrid.style.display = 'none';
            if (emptyState) emptyState.classList.remove('hidden');
        } else {
            if (bookGrid) bookGrid.style.display = '';
            if (emptyState) emptyState.classList.add('hidden');
        }
    }

    if (searchInput) searchInput.addEventListener('input', filterBooks);
    if (categoryFilter) categoryFilter.addEventListener('change', filterBooks);
    filterBooks();
});
</script>

<?php include BASE_PATH . '/view/layout/footer.php'; ?>