<?php
// Monthly History - Shows past months snapshots for the logged-in user
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

$user_id = $_SESSION['user_id'];

// Database connection
$servername = "localhost";
$username_db = "root"; // Change as per your setup
$password = ""; // Change as per your setup
$dbname = "expense_management"; // Change as per your setup

$conn = new mysqli($servername, $username_db, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user name from database
$userQuery = $conn->prepare("SELECT name FROM users WHERE user_id=?");
$userQuery->bind_param("i", $user_id);
$userQuery->execute();
$username = $userQuery->get_result()->fetch_assoc()['name'] ?? 'User';

// Fetch monthly history for the user (only past months) - COMMENTED OUT FOR TESTING
/*
$current_month = date('n');
$current_year = date('Y');
$sql = "SELECT id, total_income, total_expense, savings, goal_amount, goal_purpose, month, year, created_at 
        FROM monthly_history 
        WHERE user_id = ? AND (year < ? OR (year = ? AND month < ?))
        ORDER BY year DESC, month DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('iiii', $user_id, $current_year, $current_year, $current_month);
$stmt->execute();
$result = $stmt->get_result();
*/

// TEMP: Show all for testing
$sql = "SELECT id, total_income, total_expense, savings, goal_amount, goal_purpose, month, year, created_at 
        FROM monthly_history 
        WHERE user_id = ? 
        ORDER BY year DESC, month DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$records = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Expense History</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h1 {
            margin-top: 0;
            color: #333;
        }
        .user-info {
            margin-bottom: 20px;
            padding: 10px;
            background: #e8f5e8;
            border-radius: 4px;
        }
        .filters {
            margin-bottom: 20px;
            display: flex;
            gap: 12px;
            align-items: center;
        }
        .filters a {
            text-decoration: none;
            color: #333;
            padding: 8px 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background: #fafafa;
        }
        .filters a.active {
            border-color: #4caf50;
            background: #e8f5e8;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px 12px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        th {
            background: #f0f0f0;
            text-align: left;
        }
        .small {
            font-size: 0.9em;
            color: #555;
        }
        .no-records {
            padding: 20px;
            background: #fff3cd;
            border: 1px solid #ffeeba;
            border-radius: 4px;
            color: #856404;
        }
        .nav {
            margin-top: 18px;
        }
        .nav a {
            display: inline-block;
            margin-right: 10px;
            text-decoration: none;
            color: #4caf50;
        }
        .summary {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .summary h2 {
            margin-top: 0;
            color: #333;
        }
        .summary p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Your Monthly History</h1>
        <div class="user-info">
            <strong>Welcome, <?php echo htmlspecialchars($username); ?>!</strong> (User ID: <?php echo htmlspecialchars($user_id); ?>) Here is your monthly history.
        </div>

        <?php if (empty($records)): ?>
            <div class="no-records">
                No past monthly history found yet. History shows only completed months (past months). Submit forms for future months to build history.
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Month/Year</th>
                        <th>Total Income</th>
                        <th>Total Expense</th>
                        <th>Savings</th>
                        <th>Goal Amount</th>
                        <th>Goal Purpose</th>
                        <th>Recorded At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $record): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(date('F Y', mktime(0, 0, 0, $record['month'], 1, $record['year']))); ?></td>
                            <td>$<?php echo number_format($record['total_income'], 2); ?></td>
                            <td>$<?php echo number_format($record['total_expense'], 2); ?></td>
                            <td>$<?php echo number_format($record['savings'], 2); ?></td>
                            <td>$<?php echo number_format($record['goal_amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($record['goal_purpose']); ?></td>
                            <td><?php echo htmlspecialchars($record['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div class="nav">
            <a href="dashboard.php">Back to Dashboard</a>
            <a href="expenseHistory.php">Refresh History</a>
        </div>
    </div>
</body>
</html>