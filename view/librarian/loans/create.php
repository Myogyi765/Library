<?php
$pageTitle = 'Issue Book';
$maxBorrowDays = $maxBorrowDays ?? 14;
?>
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- ✨ Glassmorphism Header -->
    <div class="relative overflow-hidden bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500 dark:from-indigo-900 dark:via-purple-900 dark:to-pink-800 rounded-3xl p-8 mb-8 text-white shadow-2xl shadow-indigo-500/20 dark:shadow-indigo-900/30">
        <!-- Background Decorative Circles -->
        <div class="absolute top-0 right-0 w-40 h-40 bg-white/10 rounded-full blur-2xl -mr-10 -mt-10"></div>
        <div class="absolute bottom-0 left-0 w-60 h-60 bg-black/5 rounded-full blur-2xl -ml-20 -mb-20"></div>
        
        <div class="relative flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-hand-holding-heart text-2xl text-white"></i>
                </div>
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold tracking-tight">Issue Book</h1>
                    <p class="text-indigo-100 dark:text-indigo-200 text-sm mt-1">
                        <i class="fas fa-circle text-[6px] align-middle mr-2"></i>
                        Create a new loan for a library member
                    </p>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/librarian/loans" 
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-white/20 backdrop-blur-sm hover:bg-white/30 text-white font-medium rounded-xl transition-all border border-white/20 shadow-lg hover:shadow-xl hover:scale-105">
                <i class="fas fa-arrow-left text-sm"></i> Back to Loans
            </a>
        </div>
    </div>

    <!-- 📝 Main Form Card -->
    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 overflow-hidden transition-all hover:shadow-indigo-500/10">
        <!-- Card Header with Accent Border -->
        <div class="px-6 py-5 border-b border-gray-200/50 dark:border-gray-700/50 bg-gradient-to-r from-indigo-50/50 to-purple-50/50 dark:from-gray-900/30 dark:to-gray-800/30 flex items-center gap-3">
            <div class="w-1 h-8 bg-gradient-to-b from-indigo-500 to-purple-500 rounded-full"></div>
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                <i class="fas fa-pen-alt text-indigo-500 dark:text-indigo-400"></i>
                Loan Details
            </h2>
            <span class="ml-auto text-xs text-gray-400 dark:text-gray-500 bg-gray-200/50 dark:bg-gray-700/50 px-3 py-1 rounded-full border border-gray-200/50 dark:border-gray-600/50">
                <i class="far fa-clock mr-1"></i> New Request
            </span>
        </div>

        <form action="<?= BASE_URL ?>/librarian/loans/store" method="POST" class="p-6 md:p-8 space-y-6">
            
            <!-- User Selection -->
            <div class="space-y-1.5">
                <label for="user_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                    <i class="fas fa-user text-indigo-500 dark:text-indigo-400 mr-2"></i>User <span class="text-red-500">*</span>
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                        <i class="fas fa-user-circle text-gray-400 dark:text-gray-500 group-focus-within:text-indigo-500 transition-colors"></i>
                    </div>
                    <select name="user_id" id="user_id" required 
                            class="w-full pl-10 pr-10 py-3 bg-gray-50/50 dark:bg-gray-900/50 border-2 border-gray-200 dark:border-gray-700 rounded-2xl 
                                   text-gray-900 dark:text-gray-200 text-sm
                                   focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 dark:focus:border-indigo-400 transition-all duration-300
                                   appearance-none cursor-pointer hover:border-indigo-300 dark:hover:border-indigo-600 shadow-sm">
                        <option value="">— Select a user —</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user->getId() ?>">
                                <?= htmlspecialchars($user->getName()) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <i class="fas fa-chevron-down text-gray-400 dark:text-gray-500 text-xs"></i>
                    </div>
                </div>
            </div>

            <!-- Book Selection -->
            <div class="space-y-1.5">
                <label for="book_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                    <i class="fas fa-book text-indigo-500 dark:text-indigo-400 mr-2"></i>Book <span class="text-red-500">*</span>
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                        <i class="fas fa-book-open text-gray-400 dark:text-gray-500 group-focus-within:text-indigo-500 transition-colors"></i>
                    </div>
                    <select name="book_id" id="book_id" required 
                            class="w-full pl-10 pr-10 py-3 bg-gray-50/50 dark:bg-gray-900/50 border-2 border-gray-200 dark:border-gray-700 rounded-2xl 
                                   text-gray-900 dark:text-gray-200 text-sm
                                   focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 dark:focus:border-indigo-400 transition-all duration-300
                                   appearance-none cursor-pointer hover:border-indigo-300 dark:hover:border-indigo-600 shadow-sm">
                        <option value="">— Select a book —</option>
                        <?php foreach ($books as $book): ?>
                            <option value="<?= $book->getId() ?>">
                                <?= htmlspecialchars($book->getTitle()) ?> 
                                <span class="text-gray-400 dark:text-gray-500">(Available: <?= $book->getAvailableQuantity() ?>)</span>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <i class="fas fa-chevron-down text-gray-400 dark:text-gray-500 text-xs"></i>
                    </div>
                </div>
                <!-- Book availability hint -->
                <div class="flex items-center gap-2 mt-1">
                    <div class="w-1.5 h-1.5 rounded-full bg-green-400"></div>
                    <p class="text-[10px] text-gray-400 dark:text-gray-500">Select a book with available copies</p>
                </div>
            </div>

            <!-- Due Days (read-only but editable) -->
            <div class="space-y-1.5">
                <label for="due_days" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                    <i class="fas fa-calendar-day text-indigo-500 dark:text-indigo-400 mr-2"></i>Due in (days)
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                        <i class="fas fa-clock text-gray-400 dark:text-gray-500 group-focus-within:text-indigo-500 transition-colors"></i>
                    </div>
                    <input type="number" name="due_days" id="due_days" 
                           value="<?= htmlspecialchars($maxBorrowDays) ?>" 
                           min="1" max="30"
                           class="w-full pl-10 pr-16 py-3 bg-indigo-50/50 dark:bg-indigo-900/20 border-2 border-indigo-200 dark:border-indigo-800 rounded-2xl 
                                  text-gray-900 dark:text-gray-200 font-semibold text-lg
                                  focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 dark:focus:border-indigo-400 transition-all duration-300
                                  [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none
                                  shadow-inner">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none bg-indigo-500/10 dark:bg-indigo-500/20 rounded-r-2xl px-4">
                        <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400">days</span>
                    </div>
                </div>
                
                <!-- 🏷️ Stylish Info Badge -->
                <div class="mt-3 p-3 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-950/30 dark:to-purple-950/30 rounded-2xl border border-indigo-100 dark:border-indigo-800/50 flex flex-wrap items-center gap-2 text-sm">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 rounded-full text-xs font-bold">
                        <i class="fas fa-cog text-[10px]"></i> Current Setting
                    </span>
                    <span class="font-bold text-indigo-600 dark:text-indigo-300 text-lg leading-none"><?= $maxBorrowDays ?></span>
                    <span class="text-gray-500 dark:text-gray-400 text-xs">days</span>
                    <span class="text-gray-400 dark:text-gray-500 text-xs hidden sm:inline">•</span>
                    <span class="text-gray-500 dark:text-gray-400 text-xs">
                        <i class="far fa-edit mr-1"></i> Change in 
                        <a href="<?= BASE_URL ?>/admin/settings" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium hover:text-indigo-800 transition">
                            Admin → Fine & Fee Settings
                        </a>
                    </span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-8 mt-4 border-t-2 border-dashed border-gray-200 dark:border-gray-700/50">
                <a href="<?= BASE_URL ?>/librarian/loans" 
                   class="w-full sm:w-auto px-8 py-3 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-2xl transition-all duration-300 text-center border-2 border-transparent hover:border-gray-300 dark:hover:border-gray-600">
                    <i class="fas fa-times mr-2"></i> Cancel
                </a>
                <button type="submit" 
                        class="w-full sm:w-auto px-10 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 
                               text-white font-bold rounded-2xl transition-all duration-300 shadow-lg shadow-indigo-500/30 hover:shadow-xl hover:shadow-indigo-500/40 
                               flex items-center justify-center gap-2 active:scale-95 transform hover:scale-[1.02]">
                    <i class="fas fa-check-circle text-lg"></i> Issue Book
                </button>
            </div>

        </form>
    </div>
</div>