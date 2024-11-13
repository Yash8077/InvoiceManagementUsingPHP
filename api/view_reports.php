<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit;
}
?>
<?php

// Load invoices from JSON file
$invoices = [];
if (file_exists('invoices.json')) {
    $invoices = json_decode(file_get_contents('invoices.json'), true);
} else {
    echo "Invoice file not found.";
}

// Prepare data for the line chart
$salesData = [];
$labels = [];

foreach ($invoices as $invoice) {
    $date = $invoice['invoice_date'];
    $total = $invoice['invoice_total'];

    if (!isset($salesData[$date])) {
        $salesData[$date] = 0;
    }

    $salesData[$date] += $total;

    if (!in_array($date, $labels)) {
        $labels[] = $date;
    }
}

sort($labels);

$chartData = [
    'labels' => $labels,
    'datasets' => [
        [
            'label' => 'Total Sales (Rs.)',
            'data' => array_values(array_map(function ($label) use ($salesData) {
                return isset($salesData[$label]) ? $salesData[$label] : 0;
            }, $labels)),
            'borderColor' => 'rgba(75, 192, 192, 1)',
            'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
            'fill' => true
        ]
    ]
];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Reports</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-gray-100 font-sans min-h-screen flex">

    <!-- Sidebar -->
    <aside class="w-64 bg-blue-900 text-white flex-shrink-0 h-screen sticky top-0 p-5 flex flex-col justify-between">
        <div>
        <h2 class="text-2xl font-semibold mb-8">Invoice Generator</h2>
            <nav class="space-y-4">
                <a href="dashboard.php" class="flex items-center px-3 py-2 hover:bg-blue-800 rounded-md transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z" />
                    </svg>
                    Dashboard
                </a>

                <a href="manage_clients.php" class="flex items-center px-3 py-2 hover:bg-blue-800 rounded-md transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Manage Clients
                </a>
                <a href="search_json.php" class="flex items-center px-3 py-2 hover:bg-blue-800 rounded-md transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12h14M7 8h14m-7 8h7" />
                    </svg>
                    Search Invoices
                </a>
                <a href="view_reports.php" class="flex items-center px-3 py-2 hover:bg-blue-800 rounded-md transition bg-blue-700 text-white">
                     <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" viewBox="0 0 24 24" fill="currentColor">
                         <path fill-rule="evenodd" d="M3 3h18a1 1 0 011 1v16a1 1 0 01-1 1H3a1 1 0 01-1-1V4a1 1 0 011-1zM5 17h2V9H5v8zm4 0h2V5H9v12zm4 0h2V7h-2v10zm4 0h2V3h-2v14z" clip-rule="evenodd" />
                    </svg>
                     View Reports
                </a>


            </nav>
        </div>
        
        <!-- Logout Button at the Bottom -->
        <a href="logout.php" class="flex items-center px-3 py-2 bg-red-600 hover:bg-red-700 rounded-md transition mt-auto">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7" />
            </svg>
            Logout
        </a>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 p-8">
        <div class="max-w-6xl mx-auto bg-white shadow-lg rounded-lg">
            <h1 class="text-3xl font-semibold text-center mb-6">Sales Reports</h1>

            <div class="max-w-full mx-auto">
                <canvas id="salesChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>

    <script>
        // Chart.js setup
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, {
            type: 'line',
            data: <?php echo json_encode($chartData); ?>,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function (tooltipItem) {
                                return 'Rs.' + tooltipItem.raw.toFixed(2);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Sales (Rs.)'
                        },
                        ticks: {
                            beginAtZero: true
                        }
                    }
                }
            }
        });
    </script>
</body>

</html>
