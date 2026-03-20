<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$conn = pg_connect("host=localhost dbname=expense_management user=postgres password=postgres");
if (!$conn) {
    die("Connection failed: " . pg_last_error());
}

// Username
$r = pg_query_params($conn, "SELECT name FROM users WHERE user_id=$1", array($user_id));
$username = pg_fetch_assoc($r)['name'] ?? 'User';

// ================= AUTO FINALIZATION =================

$current_month = date('n');
$current_year  = date('Y');

$prev_month = $current_month;
$prev_year  = $current_year;

if ($prev_month == 0) {
    $prev_month = 12;
    $prev_year  = $current_year - 1;
}

$r = pg_query_params($conn, 
    "SELECT * FROM monthly_history WHERE user_id=$1 AND month=$2 AND year=$3",
    array($user_id, $prev_month, $prev_year)
);

if (pg_num_rows($r) == 0) {

    // totals
    $r2 = pg_query_params($conn, "SELECT SUM(amount) AS total FROM income WHERE user_id=$1", array($user_id));
    $total_income = pg_fetch_assoc($r2)['total'] ?? 0;

    $r2 = pg_query_params($conn, "SELECT SUM(amount) AS total FROM expenses WHERE user_id=$1", array($user_id));
    $total_expense = pg_fetch_assoc($r2)['total'] ?? 0;

    $r2 = pg_query_params($conn, "SELECT goal_amount, goal_purpose FROM goals WHERE user_id=$1", array($user_id));
    $goal = pg_fetch_assoc($r2);

    $savings = $total_income - $total_expense;

    // insert summary
    pg_query_params($conn, "
        INSERT INTO monthly_history 
        (user_id, total_income, total_expense, savings, goal_amount, goal_purpose, month, year) 
        VALUES ($1, $2, $3, $4, $5, $6, $7, $8)
    ", array($user_id, $total_income, $total_expense, $savings, $goal['goal_amount'], $goal['goal_purpose'], $prev_month, $prev_year));

    // insert breakdown
    $r2 = pg_query_params($conn, 
        "SELECT field_name, field_value FROM occupation_details WHERE user_id=$1", 
        array($user_id)
    );

    while ($row = pg_fetch_assoc($r2)) {

        $name  = $row['field_name'];
        $value = $row['field_value'];

        if ($value <= 0) continue;

        if (strpos($name, 'income') !== false || strpos($name, 'expense') !== false) {

            $type = (strpos($name, 'income') !== false) ? 'income' : 'expense';

            pg_query_params($conn, "
                INSERT INTO monthly_breakdown 
                (user_id, month, year, type, category, amount) 
                VALUES ($1, $2, $3, $4, $5, $6)
            ", array($user_id, $prev_month, $prev_year, $type, $name, $value));
        }
    }
}

// ================= SEARCH =================

$record      = null;
$income_data  = [];
$expense_data = [];

if (isset($_GET['month'], $_GET['year'])) {

    $m = $_GET['month'];
    $y = $_GET['year'];

    $r = pg_query_params($conn, 
        "SELECT * FROM monthly_history WHERE user_id=$1 AND month=$2 AND year=$3",
        array($user_id, $m, $y)
    );
    $record = pg_fetch_assoc($r);

    if ($record) {
        $r = pg_query_params($conn, 
            "SELECT category, amount, type FROM monthly_breakdown WHERE user_id=$1 AND month=$2 AND year=$3",
            array($user_id, $m, $y)
        );

        while ($row = pg_fetch_assoc($r)) {
            if ($row['type'] == 'income') $income_data[] = $row;
            else $expense_data[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Expense History</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

body {
    font-family: 'Segoe UI', sans-serif;
    background: linear-gradient(135deg, #8ea6ff, #bfa9e6);
    margin: 0;
    min-height: 100vh;
}

/* Container */
.container {
    max-width: 1000px;
    margin: 60px auto;
    padding: 35px;
    background: rgba(255,255,255,0.96);
    border-radius: 20px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.2);
    text-align: center;
}

/* Headings */
h1 {
    font-size: 34px;
    margin-bottom: 5px;
    color: #2c2c2c;
}

p {
    font-size: 18px;
    color: #555;
}

h3 {
    font-size: 24px;
    margin-bottom: 20px;
    color: #333;
}

/* Form */
form select, button {
    padding: 12px 16px;
    margin: 10px;
    border-radius: 12px;
    border: none;
    font-size: 16px;
}

/* Buttons */
button {
    background: #5e70c4;
    color: white;
    cursor: pointer;
    transition: 0.3s;
}

button:hover {
    background: #4a5bb0;
}

/* Nav buttons */
.nav-buttons {
    margin-top: 25px;
}

.nav-buttons a {
    text-decoration: none;
    margin: 6px;
    padding: 12px 18px;
    border-radius: 12px;
    background: #2f2f2f;
    color: white;
    font-size: 15px;
    transition: 0.3s;
}

.nav-buttons a:hover {
    background: #000;
}

/* Card */
.card {
    margin-top: 35px;
    padding: 35px;
    background: white;
    border-radius: 20px;
    text-align: left;
    transition: 0.3s;
}

.card:hover {
    transform: translateY(-4px);
}

/* Summary Section (TOP BLOCK) */
.summary-box {
    background: #eff7d0;
    padding: 20px;
    border-radius: 15px;
    margin-bottom: 25px;
}

/* Rows */
.row {
    display: flex;
    justify-content: space-between;
    margin: 14px 0;
    font-size: 18px;
    font-weight: 600;
}

/* Section Titles */
.section-title {
    margin-top: 30px;
    margin-bottom: 12px;
    font-weight: bold;
    font-size: 20px;
    padding: 10px;
    border-radius: 10px;
}

/* Income Section */
.income-section {
    background: #e5fdef;
    padding: 15px;
    border-radius: 12px;
}

/* Expense Section */
.expense-section {
    background: #ffe8e8;
    padding: 15px;
    border-radius: 12px;
}

/* Items */
.item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    font-size: 17px;
}

.divider {
    height: 1px;
    background: #e0e0e0;
    margin: 25px 0;
    opacity: 0.7;
}

/* Colors */
.income {
    color: #27ae60;
    font-weight: 700;
}

.expense {
    color: #c0392b;
    font-weight: 700;
}

</style>

</head>

<body>

<div class="container">

<h1>Welcome <?php echo htmlspecialchars($username); ?></h1>
<p>This is your monthly history</p>

<!-- SEARCH FORM -->
<form method="GET">
<select name="month" required>
<option value="">Month</option>
<?php for ($i=1;$i<=12;$i++): ?>
<option value="<?php echo $i; ?>"><?php echo date("F", mktime(0,0,0,$i,1)); ?></option>
<?php endfor; ?>
</select>

<select name="year" required>
<option value="">Year</option>
<?php for ($y=2023;$y<=date('Y');$y++): ?>
<option value="<?php echo $y; ?>"><?php echo $y; ?></option>
<?php endfor; ?>
</select>

<button>Search</button>
</form>


<!-- SHOW DATA ONLY IF FOUND -->
<?php if ($record): ?>

<div class="card">

<h3>
<?php echo date("F Y", mktime(0,0,0,$record['month'],1,$record['year'])); ?>
</h3>

<!-- SUMMARY BOX -->
<div class="summary-box">

<div class="row income">
<span><i class="fa-solid fa-arrow-up"></i> Income</span>
<span>₹<?php echo number_format($record['total_income'],2); ?></span>
</div>

<div class="row expense">
<span><i class="fa-solid fa-arrow-down"></i> Expense</span>
<span>₹<?php echo number_format($record['total_expense'],2); ?></span>
</div>

<div class="row">
<span><i class="fa-solid fa-piggy-bank"></i> Savings</span>
<span>₹<?php echo number_format($record['savings'],2); ?></span>
</div>

<div class="row">
<span><i class="fa-solid fa-bullseye"></i> Goal</span>
<span>₹<?php echo number_format($record['goal_amount'],2); ?></span>
</div>

<div class="row">
<span><i class="fa-solid fa-flag"></i> Purpose</span>
<span><?php echo htmlspecialchars($record['goal_purpose']); ?></span>
</div>

</div>
<div class="divider"></div>

<!-- INCOME SECTION -->
<div class="income-section">
<div class="section-title">Income Distribution</div>

<?php if (!empty($income_data)): ?>
    <?php foreach ($income_data as $i): ?>
    <div class="item income">
        <span><?php echo htmlspecialchars($i['category']); ?></span>
        <span>₹<?php echo number_format($i['amount'],2); ?></span>
    </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>No income data found</p>
<?php endif; ?>

</div>
<div class="divider"></div>

<!-- EXPENSE SECTION -->
<div class="expense-section">
<div class="section-title">Expense Distribution</div>

<?php if (!empty($expense_data)): ?>
    <?php foreach ($expense_data as $e): ?>
    <div class="item expense">
        <span><?php echo htmlspecialchars($e['category']); ?></span>
        <span>₹<?php echo number_format($e['amount'],2); ?></span>
    </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>No expense data found</p>
<?php endif; ?>

</div>

</div> <!-- END CARD -->

<?php elseif (isset($_GET['month'])): ?>

<p style="margin-top:20px; color:#c0392b; font-weight:600;">
No record found for selected month.
</p>

<?php endif; ?>


<!-- NAV BUTTONS -->
<div class="nav-buttons">
<a href="dashboard.php">⬅ Dashboard</a>
<a href="expenseHistory.php">🔄 Refresh</a>
</div>

</div>

</body>
</html>
