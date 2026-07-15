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
}