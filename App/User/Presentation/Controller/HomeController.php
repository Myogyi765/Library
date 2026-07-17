<?php

namespace App\User\Presentation\Controller;

use App\Shared\Base\BaseController;
use App\Book\Domain\Repository\BookRepositoryInterface;
use App\Admin\Application\Service\DashboardStatisticsService;

class HomeController extends BaseController
{
    private BookRepositoryInterface $bookRepository;
    private DashboardStatisticsService $dashboardStats;

    public function __construct(
        BookRepositoryInterface $bookRepository,
        DashboardStatisticsService $dashboardStats
    ) {
        parent::__construct(null);
        $this->bookRepository = $bookRepository;
        $this->dashboardStats = $dashboardStats;
    }

    public function index(): void
    {
        if (!defined('BASE_PATH')) {
            define('BASE_PATH', dirname(__DIR__, 4));
        }

        $stats = $this->dashboardStats->getStats();

        $latestBooks = $this->bookRepository->getLatestBooks(4);
        $latestBooksArray = array_map(function($book) {
            return [
                'id' => $book->getId(),
                'title' => $book->getTitle(),
                'author' => $book->getAuthor(),
                'isbn' => $book->getIsbn(),
                'category_id' => $book->getCategoryId(),
                'description' => $book->getDescription(),
                'cover_image' => $book->getCoverImage(),
                'quantity' => $book->getQuantity(),
                'available_quantity' => $book->getAvailableQuantity(),
                'created_at' => $book->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $book->getUpdatedAt()->format('Y-m-d H:i:s'),
                'rating' => null,
                'review_count' => 0,
                'is_new' => false,
                'featured' => false,
            ];
        }, $latestBooks);

        $this->view('home', [
            'pageTitle' => 'Welcome to Library Management System',
            'basePath' => BASE_PATH,
            'baseUrl' => BASE_URL ?? '/Library/public',
            'stats' => $stats,
            'latestBooks' => $latestBooksArray,
        ]);
    }
}