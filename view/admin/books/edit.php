<?php
// view/admin/books/edit.php
$book = $book ?? null;
$categories = $categories ?? [];
$bookId = $book->getId() ?? 0;
?>

<style>
    .form-group { margin-bottom: 1.25rem; }
    .form-label { display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.4rem; }
    .dark .form-label { color: #d1d5db; }
    .form-control {
        width: 100%;
        padding: 0.6rem 0.9rem;
        border: 1.5px solid #e5e7eb;
        border-radius: 0.6rem;
        background: #ffffff;
        color: #1f2937;
        font-size: 0.95rem;
        transition: border-color 0.2s, box-shadow 0.2s;
        outline: none;
    }
    .dark .form-control { background: #1e293b; border-color: #374151; color: #e5e7eb; }
    .form-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.15); }
    .btn-primary {
        background: #3b82f6;
        color: white;
        padding: 0.6rem 1.5rem;
        border: none;
        border-radius: 0.6rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s, transform 0.1s;
    }
    .btn-primary:hover { background: #2563eb; transform: translateY(-1px); }
    .btn-secondary {
        background: #e5e7eb;
        color: #374151;
        padding: 0.6rem 1.5rem;
        border: none;
        border-radius: 0.6rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.2s;
    }
    .dark .btn-secondary { background: #374151; color: #d1d5db; }
    .btn-secondary:hover { background: #d1d5db; }
    .dark .btn-secondary:hover { background: #4b5563; }
</style>

<div class="flex flex-col">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                <i class="fas fa-edit text-blue-600 dark:text-blue-400 mr-2"></i>Edit Book
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Editing: <strong><?= htmlspecialchars($book->getTitle()) ?></strong>
            </p>
        </div>
        <a href="<?= BASE_URL ?>/admin/books" class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
            <i class="fas fa-arrow-left mr-1"></i> Back to Books
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <form action="<?= BASE_URL ?>/admin/books/update/<?= $bookId ?>" method="POST" enctype="multipart/form-data">

            <div class="form-group">
                <label for="title" class="form-label">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" id="title" required class="form-control" value="<?= htmlspecialchars($book->getTitle()) ?>">
            </div>

            <div class="form-group">
                <label for="author" class="form-label">Author <span class="text-red-500">*</span></label>
                <input type="text" name="author" id="author" required class="form-control" value="<?= htmlspecialchars($book->getAuthor()) ?>">
            </div>

            <div class="form-group">
                <label for="isbn" class="form-label">ISBN</label>
                <input type="text" name="isbn" id="isbn" class="form-control" value="<?= htmlspecialchars($book->getIsbn() ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="category_id" class="form-label">Category <span class="text-red-500">*</span></label>
                <select name="category_id" id="category_id" required class="form-control">
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category->getId() ?>" <?= $category->getId() === $book->getCategoryId() ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category->getName()) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" rows="4" class="form-control"><?= htmlspecialchars($book->getDescription() ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="quantity" class="form-label">Quantity <span class="text-red-500">*</span></label>
                <input type="number" name="quantity" id="quantity" required min="0" class="form-control" value="<?= $book->getQuantity() ?>">
            </div>

            <div class="form-group">
                <label for="cover_image" class="form-label">Cover Image</label>
                <?php if ($book->getCoverImage()): ?>
                    <div class="mb-2">
                        <?php
                        // ✅ Check if cover image is a full URL (OpenLibrary, etc.)
                        $coverImage = $book->getCoverImage();
                        $imageSrc = (filter_var($coverImage, FILTER_VALIDATE_URL)) 
                            ? $coverImage 
                            : BASE_URL . $coverImage;
                        ?>
                        <img src="<?= $imageSrc ?>" alt="Current Cover" style="max-height: 150px; max-width: 100px; border-radius: 8px;">
                        <p class="text-xs text-gray-500 mt-1">Current cover image</p>
                    </div>
                <?php endif; ?>
                <input type="file" name="cover_image" id="cover_image" class="form-control" accept="image/*">
                <p class="text-xs text-gray-500 mt-1">Leave empty to keep current image. Allowed: JPG, PNG, GIF, WEBP.</p>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="submit" class="btn-primary"><i class="fas fa-save mr-1"></i> Update Book</button>
                <a href="<?= BASE_URL ?>/admin/books" class="btn-secondary"><i class="fas fa-times mr-1"></i> Cancel</a>
                <span class="ml-auto text-sm text-gray-400 dark:text-gray-500">
                    <a href="<?= BASE_URL ?>/admin/books/show/<?= $bookId ?>" class="text-blue-600 hover:text-blue-800 mr-3"><i class="fas fa-eye"></i> View</a>
                    <form action="<?= BASE_URL ?>/admin/books/delete/<?= $bookId ?>" method="POST" class="inline" 
                          onsubmit="return confirm('Are you sure you want to delete this book? This action cannot be undone.')">
                        <button type="submit" class="text-red-600 hover:text-red-800 bg-transparent border-0 cursor-pointer">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </span>
            </div>
        </form>
    </div>
</div>