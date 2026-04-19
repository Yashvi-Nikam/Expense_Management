<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

/* INR FORMAT FUNCTION */
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

/* TOTAL INCOME & EXPENSE */
$r = pg_query_params($conn, "SELECT amount FROM income WHERE user_id=$1", array($user_id));
$total_income = pg_fetch_assoc($r)['amount'] ?? 0;

$r = pg_query_params($conn, "SELECT amount FROM expenses WHERE user_id=$1", array($user_id));
$total_expense = pg_fetch_assoc($r)['amount'] ?? 0;

$savings = $total_income - $total_expense;

/* INCOME BREAKDOWN */
$incomeBreakdown = [];
$r = pg_query_params($conn, "
    SELECT field_name, field_value AS total
    FROM occupation_details
    WHERE user_id=$1
    AND field_name LIKE '%income%'
    AND field_value > 0
", array($user_id));
while ($row = pg_fetch_assoc($r)) {
    $incomeBreakdown[] = $row;
}

/* EXPENSE BREAKDOWN */
$expenseBreakdown = [];
$r = pg_query_params($conn, "
    SELECT field_name, field_value AS total
    FROM occupation_details
    WHERE user_id=$1
    AND field_name LIKE '%expense%'
    AND field_value > 0
", array($user_id));
while ($row = pg_fetch_assoc($r)) {
    $expenseBreakdown[] = $row;
}

/* GOALS DATA */
$goals = [];
$r = pg_query_params($conn, "
    SELECT goal_amount, goal_purpose
    FROM goals
    WHERE user_id=$1
    LIMIT 1
", array($user_id));

while ($row = pg_fetch_assoc($r)) {
    $goals[] = $row;
}

pg_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Monthly Report</title>

<style>
/* 🌿 GLOBAL STYLING */
body {
    font-family: 'Segoe UI', sans-serif;
    background: linear-gradient(135deg, #1e1e2f, #2c3e50);
    padding: 20px;
    margin: 0;
}

/* 📦 MAIN CONTAINER */
.container {
    max-width: 950px;
    margin: auto;
    background: rgba(255, 255, 255, 0.9);
    padding: 25px;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.3);
}

/* 🧾 HEADINGS */
h1 {
    text-align: center;
    font-weight: 600;
    margin-bottom: 10px;
    color: #020000;
}

h2 {
    margin-top: 35px;
    margin-bottom: 10px;
    color: #000000;
    border-left: 5px solid #4cdaaf;
    padding-left: 10px;
}

/* 📊 TABLES (ALL TABLES SAME STYLE) */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
    overflow: hidden;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    background: white;
}

th {
    background: linear-gradient(135deg, #8b47e4, #397bee);
    color: white;
    padding: 12px;
    text-align: left;
    font-weight: 600;
}

td {
    padding: 12px;
    border-bottom: 1px solid #eee;
}

/* Alternate row color */
tr:nth-child(even) {
    /* background: #fafafa; */
    background: rgba(255,255,255,0.03);
}

/* Hover effect */
tr:hover {
    /* background: #f1f8e9;*/
    background: rgba(0, 229, 255, 0.08);
    transition: 0.2s;
}

/* 🎯 GOALS TABLE EXTRA */
.goal-name {
    font-weight: 600;
    color: #333;
}

/* STATUS BADGES */
.status {
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
}

.status.success {
    background: #e8f5e9;
    color: #2e7d32;
}

.status.pending {
    background: #fff3e0;
    color: #ef6c00;
}

/* 🔗 NAVIGATION BUTTONS */
.nav {
    text-align: center;
    margin-top: 30px;
}

.nav a {
    margin: 10px;
    padding: 10px 20px;
    border-radius: 25px;
    background: linear-gradient(135deg, #00e5ff, #00bcd4);
    color: #002b36;
    text-decoration: none;
    font-weight: 600;
    box-shadow: 0 4px 15px rgba(0, 229, 255, 0.4);
    transition: all 0.2s ease;
}

.nav a:hover {
    transform: translateY(-2px) scale(1.03);
    box-shadow: 0 6px 20px rgba(0, 229, 255, 0.6);
}

/* 📝 OPTIONAL SUBTEXT */
.subtitle {
    text-align: center;
    color: #666;
    margin-bottom: 20px;
}
</style>

</head>
<body>

<div class="container">

    <h1>Monthly Report for <?php echo date('F Y'); ?></h1>

    <!-- 📊 SUMMARY TABLE -->
    <h2>Summary</h2>
    <table style="margin-bottom: 30px;">
        <tr>
            <th>Total Income</th>
            <th>Total Expenses</th>
            <th>Total Savings</th>
        </tr>
        <tr>
            <td><strong><?php echo formatINR($total_income); ?></strong></td>
            <td><strong><?php echo formatINR($total_expense); ?></strong></td>
            <td><strong><?php echo formatINR($savings); ?></strong></td>
        </tr>
    </table>

    <!-- ✅ INCOME BREAKDOWN -->
<h2>Income Breakdown</h2>

<?php if (empty($incomeBreakdown)): ?>
    <p>No income data available.</p>
<?php else: ?>
    <table>
        <tr>
            <th>Category</th>
            <th>Total Amount</th>
        </tr>
        <?php foreach ($incomeBreakdown as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $item['field_name']))); ?></td>
                <td><?php echo formatINR($item['total']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

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

<!-- ✅ GOALS TABLE (MODERN STYLE) -->
<h2>Your Savings Goals</h2>

<?php if (empty($goals)): ?>
    <p>No goals set yet.</p>
<?php else: ?>
    <table class="goal-table">
        <tr>
            <th>Goal</th>
            <th>Saved</th>
            <th>Target</th>
            <th>Remaining</th>
            <th>Status</th>
        </tr>

        <?php foreach ($goals as $goal):  
            $target = $goal['goal_amount'];
            $purpose = $goal['goal_purpose'];
            $remaining = $target - $savings;
        ?>
        <tr>
            <td class="goal-name"><?php echo htmlspecialchars($purpose); ?></td>
            <td><?php echo formatINR($savings); ?></td>
            <td><?php echo formatINR($target); ?></td>
            <td><?php echo formatINR($remaining); ?></td>
            <td>
                <?php if ($remaining <= 0): ?>
                    <span class="status success">🎉 Achieved</span>
                <?php else: ?>
                    <span class="status pending">Keep saving 💪</span>
                <?php endif; ?>
            </td>
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