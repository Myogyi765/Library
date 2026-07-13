<?php
// App/Admin/Presentation/Controller/ReportController.php
namespace App\Admin\Presentation\Controller;

use App\Shared\Base\BaseController;

class ReportController extends BaseController
{
    public function index()
    {
        // Sample report data – replace with actual service calls
        $reportData = [
            'totalBooks' => 1840,
            'availableBooks' => 1235,
            'borrowedBooks' => 605,
            'totalUsers' => 245,
            'activeLoans' => 62,
            'overdueLoans' => 11,
            'monthlyLoans' => [
                'Jan' => 45, 'Feb' => 52, 'Mar' => 68, 'Apr' => 73,
                'May' => 81, 'Jun' => 79, 'Jul' => 94, 'Aug' => 88,
                'Sep' => 102, 'Oct' => 96, 'Nov' => 110, 'Dec' => 125
            ],
            'popularBooks' => [
                ['title' => 'The Great Gatsby', 'borrows' => 34],
                ['title' => '1984', 'borrows' => 28],
                ['title' => 'To Kill a Mockingbird', 'borrows' => 25],
                ['title' => 'The Catcher in the Rye', 'borrows' => 19],
            ],
            'recentActivities' => [
                ['user' => 'John Doe', 'action' => 'Borrowed "1984"', 'date' => '2026-07-08 10:30'],
                ['user' => 'Jane Smith', 'action' => 'Returned "The Great Gatsby"', 'date' => '2026-07-08 09:15'],
                ['user' => 'Mike Johnson', 'action' => 'Registered new account', 'date' => '2026-07-07 16:45'],
                ['user' => 'Emily Davis', 'action' => 'Borrowed "To Kill a Mockingbird"', 'date' => '2026-07-07 14:20'],
            ]
        ];

        // ✅ Pass data to view
        $viewData = ['reportData' => $reportData];
        
        // ✅ Set content file path (this will be included inside the layout)
        $content = BASE_PATH . '/view/admin/reports.php';
        
        // ✅ Include the admin dashboard layout – CORRECT PATH
        include BASE_PATH . '/view/admin-dashboard.php';
    }

    // Export CSV (optional)
    public function exportCsv()
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="report_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Title', 'Borrows', 'Available']);
        // Add your data here...
        fclose($output);
        exit;
    }
}