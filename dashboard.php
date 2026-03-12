<?php
session_start();
require 'db_connect.php';


if(!isset($_SESSION['user_id'])){
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['user_id'];

/* USER NAME */
$userQuery = $conn->prepare("SELECT name FROM users WHERE user_id=?");
$userQuery->bind_param("i",$user_id);
$userQuery->execute();
$user_name = $userQuery->get_result()->fetch_assoc()['name'] ?? "User";

/* TOTAL INCOME */
$q=$conn->prepare("SELECT SUM(amount) t FROM income WHERE user_id=?");
$q->bind_param("i",$user_id);
$q->execute();
$total_income=$q->get_result()->fetch_assoc()['t'] ?? 0;

/* TOTAL EXPENSE */
$q=$conn->prepare("SELECT SUM(amount) t FROM expenses WHERE user_id=?");
$q->bind_param("i",$user_id);
$q->execute();
$total_expense=$q->get_result()->fetch_assoc()['t'] ?? 0;

/* GOAL */
$q=$conn->prepare("SELECT savings_amount,goal_amount FROM goals WHERE user_id=? ORDER BY created_at DESC LIMIT 1");
$q->bind_param("i",$user_id);
$q->execute();
$g=$q->get_result()->fetch_assoc();

$savings_amount=$g['savings_amount'] ?? 0;
$goal_amount=$g['goal_amount'] ?? 0;

$percent = $goal_amount>0 ? ($savings_amount/$goal_amount)*100 : 0;
$percent = round($percent);

/* PIE DATA */
$categories=[];
$amounts=[];

$q=$conn->prepare("
SELECT field_name,SUM(field_value) total
FROM occupation_details
WHERE user_id=? AND field_name LIKE '%expense%'
GROUP BY field_name
");
$q->bind_param("i",$user_id);
$q->execute();
$r=$q->get_result();

while($row=$r->fetch_assoc()){
$categories[]=$row['field_name'];
$amounts[]=$row['total'];
}
 
/* MONTHLY */
$transactions=[];
$month=date('m');
$year=date('Y');

$q=$conn->prepare("
SELECT 'Income' type,amount,created_at,'Income Entry' description
FROM income
WHERE user_id=? AND MONTH(created_at)=? AND YEAR(created_at)=?
UNION ALL
SELECT 'Expense',amount,created_at,'Expense Entry'
FROM expenses
WHERE user_id=? AND MONTH(created_at)=? AND YEAR(created_at)=?
ORDER BY created_at DESC
");

$q->bind_param("iiiiii",$user_id,$month,$year,$user_id,$month,$year);
$q->execute();
$r=$q->get_result();

while($row=$r->fetch_assoc()) $transactions[]=$row;
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
<button onclick="openPage('userInfo.php')">Profile</button>
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

<button onclick="openPage('userInfo.php')">User Info</button>

<button onclick="openPage('expenseHistory.php')">
Expense History
</button>

<button onclick="openPage('monthlyReport.php')">
Monthly Report
</button>

<button onclick="toggleSettings()">Settings</button>

<div id="settingsMenu" class="submenu">
<button onclick="openPage('reset_password.html')">
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



<!-- TABLE -->

<section class="monthlySection">

<h2>Current Month Transactions</h2>

<table>

<tr>
<th>Date</th>
<th>Type</th>
<th>Description</th>
<th>Amount</th>
</tr>

<?php foreach($transactions as $t){ ?>

<tr>

<td><?php echo date('d M',strtotime($t['created_at']));?></td>
<td><?php echo $t['type'];?></td>
<td><?php echo $t['description'];?></td>
<td>₹<?php echo $t['amount'];?></td>

</tr>

<?php } ?>

</table>

</section>

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