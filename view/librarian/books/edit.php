<?php
// view/librarian/books/edit.php
$pageTitle = 'Edit Book';
?>
<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
            <i class="fas fa-edit text-blue-600 dark:text-blue-400 mr-2"></i>Edit Book
        </h2>
        <a href="<?= BASE_URL ?>/librarian/books" class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
            <i class="fas fa-arrow-left"></i> Back to Books
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <form action="<?= BASE_URL ?>/librarian/books/update/<?= $book->getId() ?>" method="POST" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Title -->
                <div class="md:col-span-2">
                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" id="title" required value="<?= htmlspecialchars($book->getTitle()) ?>"
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                </div>

                <!-- Author -->
                <div>
                    <label for="author" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Author <span class="text-red-500">*</span></label>
                    <input type="text" name="author" id="author" required value="<?= htmlspecialchars($book->getAuthor()) ?>"
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                </div>

                <!-- ISBN -->
                <div>
                    <label for="isbn" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ISBN</label>
                    <input type="text" name="isbn" id="isbn" value="<?= htmlspecialchars($book->getIsbn()) ?>"
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                </div>

                <!-- Category -->
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category <span class="text-red-500">*</span></label>
                    <select name="category_id" id="category_id" required
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category->getId() ?>" <?= $book->getCategoryId() === $category->getId() ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category->getName()) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Quantity -->
                <div>
                    <label for="quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Quantity <span class="text-red-500">*</span></label>
                    <input type="number" name="quantity" id="quantity" required min="1" value="<?= $book->getQuantity() ?>"
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                </div>

                <!-- Cover Image -->
                <div class="md:col-span-2">
                    <label for="cover_image" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cover Image</label>
                    <?php if ($book->getCoverImage()): ?>
                        <div class="mb-2">
                            <img src="<?= BASE_URL . $book->getCoverImage() ?>" alt="<?= htmlspecialchars($book->getTitle()) ?>" class="w-24 h-32 object-cover rounded">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="cover_image" id="cover_image" accept="image/*"
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Leave empty to keep current image</p>
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                    <textarea name="description" id="description" rows="4"
                              class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"><?= htmlspecialchars($book->getDescription()) ?></textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="<?= BASE_URL ?>/librarian/books" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">Cancel</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition flex items-center gap-2">
                    <i class="fas fa-save"></i> Update Book
                </button>
            </div>
        </form>
    </div>
</div>