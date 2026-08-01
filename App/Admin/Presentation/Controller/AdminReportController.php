<?php
namespace App\Admin\Presentation\Controller;

use App\Shared\Base\BaseController;
use App\Admin\Application\Service\ReportService;

class AdminReportController extends BaseController
{
    private ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index(): void
    {
        $reportData = $this->reportService->getDashboardReport();

        $this->view('admin-dashboard', [
            'pageTitle' => 'Reports',
            'content' => BASE_PATH . '/view/admin/reports.php',
            'reportData' => $reportData
        ]);
    }

    public function exportCsv(): void
    {
        $popularBooks = $this->reportService->getPopularBooks();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="report_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Title', 'Borrows']);
        foreach ($popularBooks as $book) {
            fputcsv($output, [$book['title'], $book['borrows']]);
        }
        fclose($output);
        exit;
    }

    public function exportPdf(): void
    {
        header('Content-Type: text/plain; charset=utf-8');
        echo 'PDF export is not implemented yet. Please use the CSV export or contact your system administrator.';
        exit;
    }

    
    public function activities(): void
    {
        $this->ensureAdmin();

        $container = $GLOBALS['container'] ?? null;
        if (!$container) {
            throw new \RuntimeException('Container not available.');
        }

        $db = $container->get('db');

        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        $userSearch = trim($_GET['user'] ?? '');

        $page = (int)($_GET['page'] ?? 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $where = [];
        $params = [];

        if ($startDate && $endDate) {
            $where[] = "DATE(n.created_at) BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $startDate;
            $params[':end_date'] = $endDate;
        } elseif ($startDate) {
            $where[] = "DATE(n.created_at) >= :start_date";
            $params[':start_date'] = $startDate;
        } elseif ($endDate) {
            $where[] = "DATE(n.created_at) <= :end_date";
            $params[':end_date'] = $endDate;
        }

        if ($userSearch !== '') {
            $where[] = "u.name LIKE :user_search";
            $params[':user_search'] = '%' . $userSearch . '%';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $countSql = "
            SELECT COUNT(*) 
            FROM notifications n
            LEFT JOIN users u ON n.user_id = u.id
            $whereClause
        ";
        $countStmt = $db->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();
        $totalPages = ceil($total / $perPage);

        $sql = "
            SELECT 
                COALESCE(u.name, 'System') AS user,
                CONCAT(n.type, ': ', n.title) AS action,
                n.created_at AS date
            FROM notifications n
            LEFT JOIN users u ON n.user_id = u.id
            $whereClause
            ORDER BY n.created_at DESC
            LIMIT :offset, :limit
        ";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        $activities = $stmt->fetchAll();

        $this->view('admin-dashboard', [
            'pageTitle' => 'All Activities',
            'content' => BASE_PATH . '/view/admin/activities.php',
            'activities' => $activities,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'perPage' => $perPage,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'userSearch' => $userSearch
        ]);
    }
}
