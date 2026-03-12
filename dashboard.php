<?php
session_start();
require 'db_connect.php';

// Logged-in user
$user_id = $_SESSION['user_id'] ?? 1;

// --- Fetch user's name ---
$userQuery = $conn->prepare("SELECT name FROM users WHERE user_id = ?");
$userQuery->bind_param("i", $user_id);
$userQuery->execute();
$userResult = $userQuery->get_result()->fetch_assoc();
$user_name = $userResult['name'] ?? 'User';

// --- Summary Data ---
$total_income = 0;
$total_expense = 0;
$savings_amount = 0;
$goal_amount = 0;

// Total income
$incomeQuery = $conn->prepare("SELECT SUM(amount) as total_income FROM income WHERE user_id = ?");
$incomeQuery->bind_param("i", $user_id);
$incomeQuery->execute();
$incomeResult = $incomeQuery->get_result()->fetch_assoc();
$total_income = $incomeResult['total_income'] ?? 0;

// Total expense
$expenseQuery = $conn->prepare("SELECT SUM(amount) as total_expense FROM expenses WHERE user_id = ?");
$expenseQuery->bind_param("i", $user_id);
$expenseQuery->execute();
$expenseResult = $expenseQuery->get_result()->fetch_assoc();
$total_expense = $expenseResult['total_expense'] ?? 0;

// Savings goal
$goalQuery = $conn->prepare("SELECT savings_amount, goal_amount FROM goals WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$goalQuery->bind_param("i", $user_id);
$goalQuery->execute();
$goalResult = $goalQuery->get_result()->fetch_assoc();
if($goalResult){
    $savings_amount = $goalResult['savings_amount'] ?? 0;
    $goal_amount = $goalResult['goal_amount'] ?? 0;
}

// --- Pie chart: Expense categories ---
$categories = [];
$amounts = [];
$pieQuery = $conn->prepare("
    SELECT field_name AS category, SUM(field_value) AS total
    FROM occupation_details
    WHERE user_id = ? AND field_name LIKE '%expense%'
    GROUP BY field_name
");
$pieQuery->bind_param("i", $user_id);
$pieQuery->execute();
$pieResult = $pieQuery->get_result();
while($row = $pieResult->fetch_assoc()){
    $categories[] = $row['category'];
    $amounts[] = $row['total'];
}

// --- Monthly Transactions ---
$transactions = [];
$month = date('m');
$year = date('Y');

$transQuery = $conn->prepare("
    SELECT 'Income' as type, amount, created_at, 'Income Entry' AS description
    FROM income
    WHERE user_id = ? AND MONTH(created_at) = ? AND YEAR(created_at) = ?
    UNION ALL
    SELECT 'Expense' as type, amount, created_at, 'Expense Entry' AS description
    FROM expenses
    WHERE user_id = ? AND MONTH(created_at) = ? AND YEAR(created_at) = ?
    ORDER BY created_at DESC
");
$transQuery->bind_param("iiiiii", $user_id, $month, $year, $user_id, $month, $year);
$transQuery->execute();
$transResult = $transQuery->get_result();
while($row = $transResult->fetch_assoc()){
    $transactions[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Finance Dashboard</title>
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
    <!-- SEARCH BAR -->
    <div class="search-container">
      <input type="text" id="searchInput" placeholder="Search here">
      <button onclick="triggerSearch()">🔍</button>
    </div>

    <!-- PROFILE MENU -->
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
  <span id="profileInitials"><?php echo strtoupper(substr($user_name,0,2)); ?></span>
  <img id="profilePhoto" class="profile" src="" style="display:none;">

  <button onclick="openPage('userInfo.php')">User Info</button>
  <button onclick="openPage('expenseHistory.php')">Expense History</button>
  <button onclick="openPage('monthlyReport.php')">Monthly Report</button>

  <button onclick="toggleSettings()">Settings</button>
  <div id="settingsMenu" class="submenu">
    <button onclick="openPage('reset_password.html')">Reset Password</button>
    <button onclick="window.location.href='delete_account.php'">Delete Account</button>
  </div>

  <button onclick="window.location.href='logout.php'">Logout</button>
</div>

<div id="overlay" onclick="toggleMenu()"></div>

<!-- MAIN CONTENT -->
<div class="main">

  <!-- Summary Cards -->
  <div class="summary">
    <div class="card">
      <h3>Total Income</h3>
      <p>₹<?php echo $total_income; ?></p>
    </div>
    <div class="card">
      <h3>Total Expense</h3>
      <p>₹<?php echo $total_expense; ?></p>
    </div>
    <div class="card">
      <h3>Total Savings</h3>
      <p>₹<?php echo $total_income - $total_expense; ?></p>
    </div>
  </div>

  <!-- PASS SAVINGS PERCENT TO JS -->
<div id="savingsData" data-percent="<?php echo round($percent); ?>"></div>

  <!-- Charts -->
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

  <!-- Savings Progress -->
  <div class="progress-box">
    <h3>Savings Goal Progress</h3>
    <div class="progress-bar">
      <?php $percent = $goal_amount > 0 ? ($savings_amount/$goal_amount)*100 : 0; ?>
      <div class="progress-fill"><?php echo round($percent); ?>%</div>
    </div>
  </div>

  <!-- Monthly Transactions -->
  <section class="monthlySection">
    <h2>Current Month Transactions</h2>
    <table>
      <tr>
        <th>Date</th>
        <th>Type</th>
        <th>Description</th>
        <th>Amount</th>
      </tr>
      <?php foreach($transactions as $t): ?>
      <tr>
        <td><?php echo date('d M', strtotime($t['created_at'])); ?></td>
        <td><?php echo $t['type']; ?></td>
        <td><?php echo $t['description'] ?? '-'; ?></td>
        <td>₹<?php echo $t['amount']; ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </section>

</div>

<!-- CHARTS SCRIPT -->
<script>
// Pie Chart
const pieLabels = <?php echo json_encode($categories); ?>;
const pieData = <?php echo json_encode($amounts); ?>;

new Chart(document.getElementById('pieChart'), {
    type: 'pie',
    data: {
        labels: pieLabels,
        datasets: [{
            data: pieData,
            backgroundColor: ['#FF6384','#36A2EB','#FFCE56','#8A2BE2','#00FF7F', '#FFA500', '#00CED1', '#FF69B4']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right',
                align: 'start',
                labels: { boxWidth: 20, padding: 10 }
            },
            tooltip: { enabled: true }
        }
    }
});

// Bar Chart
const barLabels = ['Income','Expense'];
const barData = [<?php echo $total_income; ?>, <?php echo $total_expense; ?>];

new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: {
        labels: barLabels,
        datasets: [{
            label: 'Amount',
            data: barData,
            backgroundColor: ['#36A2EB','#FF6384']
        }]
    },
    options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false }
});
</script>

<script src="dashboard.js"></script>
</body>
</html>