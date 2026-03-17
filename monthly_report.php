<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$current_month = date('m');
$current_year = date('Y');

/* USER NAME */
$userQuery = $conn->prepare("SELECT name FROM users WHERE user_id=?");
$userQuery->bind_param("i", $user_id);
$userQuery->execute();
$user_name = $userQuery->get_result()->fetch_assoc()['name'] ?? "User";

/* MONTHLY INCOME */
$incomeQuery = $conn->prepare("SELECT SUM(amount) AS total FROM income WHERE user_id=? AND MONTH(created_at)=? AND YEAR(created_at)=?");
$incomeQuery->bind_param("iii", $user_id, $current_month, $current_year);
$incomeQuery->execute();
$monthly_income = $incomeQuery->get_result()->fetch_assoc()['total'] ?? 0;

/* MONTHLY EXPENSE */
$expenseQuery = $conn->prepare("SELECT SUM(amount) AS total FROM expenses WHERE user_id=? AND MONTH(created_at)=? AND YEAR(created_at)=?");
$expenseQuery->bind_param("iii", $user_id, $current_month, $current_year);
$expenseQuery->execute();
$monthly_expense = $expenseQuery->get_result()->fetch_assoc()['total'] ?? 0;

/* MONTHLY SAVINGS */
$monthly_savings = $monthly_income - $monthly_expense;

/* CURRENT MONTH GOAL */
$goalQuery = $conn->prepare("SELECT savings_amount, goal_amount, goal_purpose FROM goals WHERE user_id=? AND start_month=? AND start_year=? ORDER BY created_at DESC LIMIT 1");
$goalQuery->bind_param("iii", $user_id, $current_month, $current_year);
$goalQuery->execute();
$goal = $goalQuery->get_result()->fetch_assoc();

$savings_amount = $goal['savings_amount'] ?? 0;
$goal_amount = $goal['goal_amount'] ?? 0;
$goal_purpose = $goal['goal_purpose'] ?? 'No goal set';

$goal_progress = $goal_amount > 0 ? min(100, ($savings_amount / $goal_amount) * 100) : 0;

/* EXPENSE BREAKDOWN */
$expenseBreakdown = [];
$breakdownQuery = $conn->prepare("
    SELECT field_name, SUM(field_value) AS total
    FROM occupation_details
    WHERE user_id=? AND field_name LIKE '%expense%' AND MONTH(created_at)=? AND YEAR(created_at)=?
    GROUP BY field_name
");
$breakdownQuery->bind_param("iii", $user_id, $current_month, $current_year);
$breakdownQuery->execute();
$result = $breakdownQuery->get_result();

while ($row = $result->fetch_assoc()) {
    $expenseBreakdown[] = $row;
}

/* MONTHLY TRANSACTIONS */
$transactions = [];
$transactionQuery = $conn->prepare("
    SELECT 'Income' AS type, amount, created_at, 'Income Entry' AS description
    FROM income
    WHERE user_id=? AND MONTH(created_at)=? AND YEAR(created_at)=?
    UNION ALL
    SELECT 'Expense', amount, created_at, 'Expense Entry'
    FROM expenses
    WHERE user_id=? AND MONTH(created_at)=? AND YEAR(created_at)=?
    ORDER BY created_at DESC
");
$transactionQuery->bind_param("iiiiii", $user_id, $current_month, $current_year, $user_id, $current_month, $current_year);
$transactionQuery->execute();
$result = $transactionQuery->get_result();

while ($row = $result->fetch_assoc()) {
    $transactions[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Report - <?php echo date('F Y'); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: #fff;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h1 {
            margin-top: 0;
            color: #333;
            text-align: center;
        }
        .summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .card {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border-left: 4px solid #4caf50;
        }
        .card.income { border-left-color: #2196f3; }
        .card.expense { border-left-color: #f44336; }
        .card.savings { border-left-color: #ff9800; }
        .card.goal { border-left-color: #9c27b0; }
        .card h3 {
            margin: 0 0 10px 0;
            font-size: 2em;
            color: #333;
        }
        .card p {
            margin: 0;
            color: #666;
        }
        .goal-progress {
            background: #e0e0e0;
            border-radius: 10px;
            height: 20px;
            margin-top: 10px;
        }
        .goal-progress-bar {
            background: #4caf50;
            height: 100%;
            border-radius: 10px;
            width: <?php echo $goal_progress; ?>%;
        }
        .breakdown, .transactions {
            margin-bottom: 30px;
        }
        .breakdown h2, .transactions h2 {
            color: #333;
            border-bottom: 2px solid #4caf50;
            padding-bottom: 10px;
        }
        .breakdown ul {
            list-style: none;
            padding: 0;
        }
        .breakdown li {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f0f0f0;
        }
        .nav {
            text-align: center;
            margin-top: 30px;
        }
        .nav a {
            display: inline-block;
            margin: 0 10px;
            padding: 10px 20px;
            text-decoration: none;
            color: #4caf50;
            border: 1px solid #4caf50;
            border-radius: 4px;
        }
        .nav a:hover {
            background: #4caf50;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Monthly Report for <?php echo date('F Y'); ?></h1>
        <p style="text-align: center; color: #666;">Welcome, <?php echo htmlspecialchars($user_name); ?>!</p>

        <div class="summary">
            <div class="card income">
                <h3>$<?php echo number_format($monthly_income, 2); ?></h3>
                <p>Total Income</p>
            </div>
            <div class="card expense">
                <h3>$<?php echo number_format($monthly_expense, 2); ?></h3>
                <p>Total Expenses</p>
            </div>
            <div class="card savings">
                <h3>$<?php echo number_format($monthly_savings, 2); ?></h3>
                <p>Monthly Savings</p>
            </div>
            <div class="card goal">
                <h3><?php echo round($goal_progress); ?>%</h3>
                <p>Goal Progress</p>
                <div class="goal-progress">
                    <div class="goal-progress-bar"></div>
                </div>
                <p style="margin-top: 10px; font-size: 0.9em;">Goal: <?php echo htmlspecialchars($goal_purpose); ?> ($<?php echo number_format($goal_amount, 2); ?>)</p>
            </div>
        </div>

        <div class="breakdown">
            <h2>Expense Breakdown</h2>
            <?php if (empty($expenseBreakdown)): ?>
                <p>No expense details available for this month.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($expenseBreakdown as $item): ?>
                        <li>
                            <span><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $item['field_name']))); ?></span>
                            <span>$<?php echo number_format($item['total'], 2); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <div class="transactions">
            <h2>Monthly Transactions</h2>
            <?php if (empty($transactions)): ?>
                <p>No transactions found for this month.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $transaction): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($transaction['created_at']); ?></td>
                                <td><?php echo htmlspecialchars($transaction['type']); ?></td>
                                <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                                <td>$<?php echo number_format($transaction['amount'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="nav">
            <a href="dashboard.php">Back to Dashboard</a>
            <a href="expenseHistory.php">View Expense History</a>
        </div>
    </div>
</body>
</html>
