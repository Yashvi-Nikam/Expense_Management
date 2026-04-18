<?php
session_start();
require 'db_connect.php';

if(!isset($_SESSION['user_id'])){
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['user_id'];

/* USER NAME */
$r = pg_query_params($conn, "SELECT name FROM users WHERE user_id=$1", array($user_id));
$user_name = pg_fetch_assoc($r)['name'] ?? "User";

/* TOTAL INCOME */
$r = pg_query_params($conn, "
    SELECT SUM(field_value) t FROM occupation_details 
    WHERE user_id=$1 AND field_name LIKE '%income%' AND field_value > 0
", array($user_id));
$total_income = pg_fetch_assoc($r)['t'] ?? 0;

/* TOTAL EXPENSE */
$r = pg_query_params($conn, "
    SELECT SUM(field_value) t FROM occupation_details 
    WHERE user_id=$1 AND field_name LIKE '%expense%'
", array($user_id));
$total_expense = pg_fetch_assoc($r)['t'] ?? 0;

/* GOAL */
$r = pg_query_params($conn, "
    SELECT goal_amount FROM goals 
    WHERE user_id=$1 
    ORDER BY created_at DESC LIMIT 1
", array($user_id));
$g = pg_fetch_assoc($r);

$goal_amount    = $g['goal_amount']    ?? 0;
$savings_amount = $total_income - $total_expense;

$percent = $goal_amount > 0 ? ($savings_amount / $goal_amount) * 100 : 0;
$percent = round($percent);

/* LIMIT PROGRESS TO 100% */
if($percent > 100) $percent = 100;

/* PIE DATA */
$categories = [];
$amounts    = [];

$r = pg_query_params($conn, "
    SELECT field_name, SUM(field_value) total
    FROM occupation_details
    WHERE user_id=$1 AND field_name LIKE '%expense%'
    GROUP BY field_name
", array($user_id));

while($row = pg_fetch_assoc($r)){
    $categories[] = $row['field_name'];
    $amounts[]    = $row['total'];
}

/* MONTHLY */
$transactions = [];

/* Get all occupation details from current month */
$r = pg_query_params($conn, "
    SELECT field_name, field_value, created_at
    FROM occupation_details
    WHERE user_id=$1
    ORDER BY created_at DESC
", array($user_id));

while($row = pg_fetch_assoc($r)){
    $field_name = $row['field_name'];
    $is_income = strpos($field_name, 'income') !== false;
    $type = $is_income ? 'Income' : 'Expense';
    
    $transactions[] = [
        'type' => $type,
        'amount' => floatval($row['field_value']),
        'created_at' => $row['created_at'],
        'description' => ucfirst(str_replace('_', ' ', $field_name))
    ];
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Dashboard</title>
<link rel="stylesheet" href="dashboard.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>


<!-- MENU BUTTON -->
<button class="menuBtn" onclick="toggleMenu()">☰</button>

<!-- TOP BAR -->
<div class="topbar">

<div class="leftTop">
<h1 class="welcomeText">Welcome, <?php echo htmlspecialchars($user_name); ?></h1>
</div>

<div class="top-right">

<!-- SEARCH -->
<div class="search-container">
<input type="text" id="searchInput" placeholder="Search here">
<button onclick="triggerSearch()">🔍</button>
</div>

<!-- PROFILE -->
<div class="profile-container">

<div class="profileBox small" onclick="toggleProfileMenu()">
<span id="topInitials"><?php echo strtoupper(substr($user_name,0,2)); ?></span>
<img id="topProfilePhoto" style="display:none;">
</div>

<div id="profileMenu" class="profileMenu">
<button onclick="openPage('edit_profile.php')">Profile</button>
<button onclick="window.location.href='logout.php'">Logout</button>
</div>

</div>
</div>
</div>

<!-- SIDEBAR -->
<div id="sidebar" class="sidebar">

<input type="file" id="photoUpload" accept="image/*">

<span id="profileInitials">
<?php echo strtoupper(substr($user_name,0,2)); ?>
</span>

<img id="profilePhoto" class="profile" style="display:none;">

<button onclick="openPage('edit_profile.php')">User Info</button>

<button onclick="openPage('expenseHistory.php')">
Expense History
</button>

<button onclick="openPage('monthly_report.php')">
Monthly Report
</button>

<button onclick="toggleSettings()">Settings</button>

<div id="settingsMenu" class="submenu">
<button onclick="openPage('forgot_password.php')">
Reset Password
</button>

<button onclick="deleteAccount()">Delete Account</button>

</button>
</div>

<button onclick="window.location.href='logout.php'">
Logout
</button>

</div>

<div id="overlay" onclick="toggleMenu()"></div>

<div class="main">

<div class="summary">

<div class="card">
<h3>Total Income</h3>
<p>₹<?php echo $total_income;?></p>
</div>

<div class="card">
<h3>Total Expense</h3>
<p>₹<?php echo $total_expense;?></p>
</div>

<div class="card">
<h3>Total Savings</h3>
<p>₹<?php echo $total_income-$total_expense;?></p>
</div>

</div>


<!-- CHARTS -->

<div class="chart-container">

<div class="chart-box">
<h3>Expense Distribution</h3>
<canvas id="pieChart"></canvas>
</div>

<div class="chart-box">
<h3>Income vs Expense</h3>
<canvas id="barChart"></canvas>
</div>

</div>


<!-- PROGRESS -->

<div class="progress-box">

<h3>Savings Progress</h3>

<div class="progress-bar">

<div
class="progress-fill"
style="width:<?php echo $percent;?>%">
<?php echo $percent;?>%
</div>

</div>

</div>

</div>



<script>

const pieLabels = <?php echo json_encode($categories); ?>;
const pieData = <?php echo json_encode($amounts); ?>;

const totalIncome = <?php echo $total_income;?>;
const totalExpense = <?php echo $total_expense;?>;

const savingsPercent = <?php echo $percent;?>;

</script>

<script src="dashboard.js"></script>

</body>
</html>