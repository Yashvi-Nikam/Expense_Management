<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

/* ✅ INR FORMAT FUNCTION */
function formatINR($number) {
    $number = round($number, 2);
    $decimal = substr(number_format($number, 2, '.', ''), -3);
    $num = floor($number);

    $digits = strlen($num);
    $result = "";

    if ($digits > 3) {
        $last3 = substr($num, -3);
        $rest = substr($num, 0, -3);
        $rest = preg_replace("/\B(?=(\d{2})+(?!\d))/", ",", $rest);
        $result = $rest . "," . $last3;
    } else {
        $result = $num;
    }

    return "₹" . $result . $decimal;
}

$user_id = $_SESSION['user_id'];
$current_month = date('m');
$current_year = date('Y');

/* ✅ EXPENSE BREAKDOWN */
$expenseBreakdown = [];
$query = $conn->prepare("
    SELECT field_name, SUM(field_value) AS total
    FROM occupation_details
    WHERE user_id=? 
    AND field_name LIKE '%expense%' 
    AND MONTH(created_at)=? 
    AND YEAR(created_at)=?
    GROUP BY field_name
");
$query->bind_param("iii", $user_id, $current_month, $current_year);
$query->execute();
$result = $query->get_result();

while ($row = $result->fetch_assoc()) {
    $expenseBreakdown[] = $row;
}

/* ✅ TRANSACTIONS */
$transactions = [];
$query = $conn->prepare("
    SELECT 'Income' AS type, amount, created_at, 'Income Entry' AS description
    FROM income
    WHERE user_id=? AND MONTH(created_at)=? AND YEAR(created_at)=?
    UNION ALL
    SELECT 'Expense', amount, created_at, 'Expense Entry'
    FROM expenses
    WHERE user_id=? AND MONTH(created_at)=? AND YEAR(created_at)=?
    ORDER BY created_at DESC
");
$query->bind_param("iiiiii", $user_id, $current_month, $current_year, $user_id, $current_month, $current_year);
$query->execute();
$result = $query->get_result();

while ($row = $result->fetch_assoc()) {
    $transactions[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Monthly Report</title>

<style>
body {
    font-family: Arial;
    background: #f5f5f5;
    padding: 20px;
}
.container {
    max-width: 900px;
    margin: auto;
    background: white;
    padding: 20px;
    border-radius: 8px;
}
h1 {
    text-align: center;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}
th, td {
    padding: 10px;
    border: 1px solid #ddd;
}
th {
    background: #4caf50;
    color: white;
}
h2 {
    margin-top: 30px;
}
.nav {
    text-align: center;
    margin-top: 30px;
}
.nav a {
    margin: 10px;
    padding: 10px 20px;
    border: 1px solid #4caf50;
    color: #4caf50;
    text-decoration: none;
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

    <!-- ✅ EXPENSE BREAKDOWN -->
    <h2>Expense Breakdown</h2>

    <?php if (empty($expenseBreakdown)): ?>
        <p>No expense data available.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Category</th>
                <th>Total Amount</th>
            </tr>
            <?php foreach ($expenseBreakdown as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $item['field_name']))); ?></td>
                    <td><?php echo formatINR($item['total']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <!-- ✅ TRANSACTIONS -->
    <h2>Monthly Transactions</h2>

    <?php if (empty($transactions)): ?>
        <p>No transactions found.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Description</th>
                <th>Amount</th>
            </tr>

            <?php foreach ($transactions as $t): ?>
                <tr>
                    <td><?php echo htmlspecialchars($t['created_at']); ?></td>
                    <td><?php echo htmlspecialchars($t['type']); ?></td>
                    <td><?php echo htmlspecialchars($t['description']); ?></td>
                    <td><?php echo formatINR($t['amount']); ?></td>
                </tr>
            <?php endforeach; ?>

        </table>
    <?php endif; ?>

    <!-- ✅ NAVIGATION -->
    <div class="nav">
        <a href="dashboard.php">Back to Dashboard</a>
        <a href="expenseHistory.php">View Expense History</a>
    </div>

</div>
</body>
</html>