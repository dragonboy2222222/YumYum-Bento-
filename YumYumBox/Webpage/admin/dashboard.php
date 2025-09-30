<?php
// Start the session and check if the user is logged in as an admin
session_start();
if (!isset($_SESSION["username"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

// Include the database connection file
require_once("../dbconnect.php");

try {
    // Corrected SQL to join subscriptions, checkouts, and payments tables for weekly stats
    $sql_summary = "
        SELECT 
            COUNT(s.id) AS total_orders, 
            SUM(p.amount) AS total_revenue 
        FROM subscriptions s
        JOIN checkouts c ON s.checkout_id = c.id
        JOIN payments p ON c.id = p.checkout_id
        WHERE p.paid_at BETWEEN CURDATE() - INTERVAL 7 DAY AND CURDATE()
    ";
    $stmt_summary = $conn->query($sql_summary);
    $data_summary = $stmt_summary->fetch(PDO::FETCH_ASSOC);

    $total_orders = $data_summary['total_orders'];
    $total_revenue = $data_summary['total_revenue'];

    // Daily Sales Data for the line chart
    $sales_data = [];
    $labels = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i day"));
        $day_name = date('l', strtotime("-$i day"));
        $labels[] = $day_name;

        $sql_daily = "
            SELECT 
                COUNT(s.id) AS daily_orders
            FROM subscriptions s
            JOIN checkouts c ON s.checkout_id = c.id
            JOIN payments p ON c.id = p.checkout_id
            WHERE p.paid_at = :date
        ";
        $stmt_daily = $conn->prepare($sql_daily);
        $stmt_daily->execute(['date' => $date]);
        $daily_sales = $stmt_daily->fetch(PDO::FETCH_ASSOC);
        $sales_data[] = $daily_sales['daily_orders'];
    }

    // Monthly Subscription Growth Data for the bar chart
    $monthly_growth = [];
    $month_labels = [];
    for ($i = 5; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-$i month"));
        $month_name = date('M Y', strtotime("-$i month"));
        $month_labels[] = $month_name;

        $sql_monthly = "
            SELECT COUNT(id) AS monthly_count
            FROM subscriptions
            WHERE DATE_FORMAT(start_date, '%Y-%m') = :month
        ";
        $stmt_monthly = $conn->prepare($sql_monthly);
        $stmt_monthly->execute(['month' => $month]);
        $monthly_data = $stmt_monthly->fetch(PDO::FETCH_ASSOC);
        $monthly_growth[] = $monthly_data['monthly_count'];
    }

    // Payment Method Distribution for the pie chart
    $sql_payments = "
        SELECT method, COUNT(id) as count
        FROM payments
        GROUP BY method
    ";
    $stmt_payments = $conn->query($sql_payments);
    $payment_methods = $stmt_payments->fetchAll(PDO::FETCH_ASSOC);
    
    $payment_labels = array_column($payment_methods, 'method');
    $payment_counts = array_column($payment_methods, 'count');

} catch (PDOException $e) {
    // In case of an error, set default values and display a message
    echo "Database Error: " . $e->getMessage();
    $total_orders = 0;
    $total_revenue = 0;
    $sales_data = [0, 0, 0, 0, 0, 0, 0];
    $labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    $monthly_growth = [0, 0, 0, 0, 0, 0];
    $month_labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
    $payment_labels = ['N/A'];
    $payment_counts = [1];
}

// Fetch total number of users
try {
    $sql_users = "SELECT COUNT(id) AS total_users FROM users";
    $stmt_users = $conn->query($sql_users);
    $users_data = $stmt_users->fetch(PDO::FETCH_ASSOC);
    $total_users = $users_data['total_users'];
} catch (PDOException $e) {
    $total_users = 0;
}

// Fetch total number of lunchboxes
try {
    $sql_lunchboxes = "SELECT COUNT(id) AS total_lunchboxes FROM lunchboxes";
    $stmt_lunchboxes = $conn->query($sql_lunchboxes);
    $lunchboxes_data = $stmt_lunchboxes->fetch(PDO::FETCH_ASSOC);
    $total_lunchboxes = $lunchboxes_data['total_lunchboxes'];
} catch (PDOException $e) {
    $total_lunchboxes = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lunchbox Admin Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* Define new color variables */
        :root {
            --red-dark: #993333;
            --red-medium: #cc3300;
            --cream: #f8f4ec;
            --white: #ffffff;
            --gray-dark: #333333;
            --gray-light: #eeeeee;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--cream);
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background-color: var(--red-dark);
            color: var(--white);
            padding: 30px;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100%;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 40px;
            font-weight: 700;
            color: var(--cream);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sidebar a {
            display: block;
            color: var(--white);
            text-decoration: none;
            padding: 15px 20px;
            margin-bottom: 10px;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background-color: var(--red-medium);
            color: var(--white);
            transform: translateX(5px);
        }

        .logout {
            margin-top: auto;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .logout a {
            color: var(--white);
            font-weight: 600;
            text-transform: uppercase;
        }

        .logout a:hover {
            color: var(--cream);
        }

        .main {
            margin-left: 280px;
            padding: 40px;
            width: calc(100% - 280px);
        }

        .main h1 {
            color: var(--red-dark);
            margin-bottom: 25px;
            font-weight: 700;
        }

        .card-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .card {
            background: var(--white);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .card h2 {
            color: var(--red-medium);
            margin-bottom: 15px;
            font-size: 1.5rem;
        }

        .card p,
        .card ul {
            color: var(--gray-dark);
        }

        .card ul {
            list-style-type: none;
        }

        .card li {
            padding: 8px 0;
            border-bottom: 1px solid var(--gray-light);
        }
        
        .card li:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>YumYum Admin</h2>
        <a href="dashboard.php" class="active">📊 Dashboard</a>
        <a href="insertProduct.php">🍱 Manage Lunchboxes</a>
        <a href="viewUser.php">📋 View Users</a>
        <a href="viewProduct.php">📦 Lunchbox Reports</a>
        <a href="insertmenu.php">🧾 Insert Menus</a>
        <a href="viewmenu.php">📖 View Menus</a>
        <a href="insertPlans.php">📅 Insert Plans</a>
        <a href="adddiscounts.php">📊 Promotion</a>
        <a href="viewreview.php" >⭐️ View Reviews</a>
        <a href="admin_subscriptions.php" >📝 View Subscriptions</a>

        <div class="logout">
            <a href="../login.php">🚪 Logout</a>
        </div>
    </div>

    <div class="main">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>! 👋</h1>
        
        <div class="card-container">
            <div class="card">
                <h2>Dashboard Overview</h2>
                <p>Welcome to the YumYum Admin Panel. Here you can manage everything related to your e-commerce store, from orders and products to customer information and communication.</p>
            </div>

            <div class="card">
                <h2>Quick Stats</h2>
                <ul>
                    <li>Total Orders Last 7 Days: **<?php echo htmlspecialchars($total_orders); ?>**</li>
                    <li>Total Revenue Last 7 Days: **$<?php echo htmlspecialchars(number_format($total_revenue, 2)); ?>**</li>
                    <li>Total Users: **<?php echo htmlspecialchars($total_users); ?>**</li>
                    <li>Total Lunchboxes: **<?php echo htmlspecialchars($total_lunchboxes); ?>**</li>
                </ul>
            </div>
            
            <div class="card">
                <h2>Sales and Orders Chart (Last 7 Days)</h2>
                <canvas id="salesChart"></canvas>
            </div>
            
            <div class="card">
                <h2>Monthly Subscription Growth (Last 6 Months)</h2>
                <canvas id="monthlyChart"></canvas>
            </div>

            <div class="card">
                <h2>Payment Method Distribution</h2>
                <canvas id="paymentChart"></canvas>
            </div>
            
        </div>
    </div>

    <script>
        // PHP data passed to JavaScript
        var salesData = <?php echo json_encode($sales_data); ?>;
        var labels = <?php echo json_encode($labels); ?>;
        var monthlyData = <?php echo json_encode($monthly_growth); ?>;
        var monthlyLabels = <?php echo json_encode($month_labels); ?>;
        var paymentLabels = <?php echo json_encode($payment_labels); ?>;
        var paymentCounts = <?php echo json_encode($payment_counts); ?>;

        // Sales Chart (Line Chart)
        var salesCtx = document.getElementById('salesChart').getContext('2d');
        var salesChart = new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Sales This Week',
                    data: salesData,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Monthly Subscription Chart (Bar Chart)
        var monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        var monthlyChart = new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: 'New Subscriptions',
                    data: monthlyData,
                    backgroundColor: 'rgba(255, 159, 64, 0.5)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Payment Method Chart (Pie Chart)
        var paymentCtx = document.getElementById('paymentChart').getContext('2d');
        var paymentChart = new Chart(paymentCtx, {
            type: 'pie',
            data: {
                labels: paymentLabels,
                datasets: [{
                    data: paymentCounts,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)'
                    ],
                    borderColor: 'rgba(255, 255, 255, 1)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                let label = tooltipItem.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += tooltipItem.raw;
                                return label;
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>