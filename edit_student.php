<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch latest student data from occupation_details
$data = [];
$fields = ['main_income', 'other_income', 'income_source', 'food_expense', 'transportation_expense', 'books_expense', 'entertainment_expense', 'mobile_expense', 'other_expense', 'saving_goal'];

foreach ($fields as $field) {
    $query = $conn->prepare("SELECT field_value, field_text FROM occupation_details WHERE user_id = ? AND field_name = ? ORDER BY created_at DESC LIMIT 1");
    $query->bind_param("is", $user_id, $field);
    $query->execute();
    $result = $query->get_result()->fetch_assoc();
    $data[$field] = is_numeric($result['field_value'] ?? $result['field_text']) ? floatval($result['field_value'] ?? $result['field_text']) : ($result['field_text'] ?? '');
}

// Fetch goal data
$goalQuery = $conn->prepare("SELECT goal_purpose, goal_amount FROM goals WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$goalQuery->bind_param("i", $user_id);
$goalQuery->execute();
$goal = $goalQuery->get_result()->fetch_assoc();

$data['goal_purpose'] = $goal['goal_purpose'] ?? '';
$data['goal_amount'] = $goal['goal_amount'] ?? 0;

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student Expense Details</title>
    <link rel="stylesheet" href="formstyle.css">
    <script>
        function validateStudent() {
            let pocket = document.getElementById("pocketMoney").value;
            let otherIncome = document.getElementById("otherIncome").value;
            let food = document.getElementById("foodExpense").value;
            let transport = document.getElementById("transportationExpense").value;
            let books = document.getElementById("booksExpense").value;
            let entertainment = document.getElementById("entertainmentExpense").value;
            let mobile = document.getElementById("mobileExpense").value;
            let otherExpense = document.getElementById("otherExpense").value;
            let goalAmount = document.getElementById("goalAmount").value;

            if (isNaN(pocket) || pocket < 0) {
                alert("Please enter a valid number in Pocket Money.");
                return false;
            }
            if (isNaN(otherIncome) || otherIncome < 0) {
                alert("Please enter a valid number in Other Income.");
                return false;
            }
            if (isNaN(food) || food < 0) {
                alert("Please enter a valid number in Food Expense.");
                return false;
            }
            if (isNaN(transport) || transport < 0) {
                alert("Please enter a valid number in Transportation Expense.");
                return false;
            }
            if (isNaN(books) || books < 0) {
                alert("Please enter a valid number in Books Expense.");
                return false;
            }
            if (isNaN(entertainment) || entertainment < 0) {
                alert("Please enter a valid number in Entertainment Expense.");
                return false;
            }
            if (isNaN(mobile) || mobile < 0) {
                alert("Please enter a valid number in Mobile Expense.");
                return false;
            }
            if (isNaN(otherExpense) || otherExpense < 0) {
                alert("Please enter a valid number in Other Expense.");
                return false;
            }
            if (isNaN(goalAmount) || goalAmount < 0) {
                alert("Please enter a valid number in Goal Amount.");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
<div class="form-container">
    <h1>Edit Student Expense Details</h1><br><br>
    <form id="studentForm" action="student_form.php" method="post" onsubmit="return validateStudent()">
        <!-- Income Fields -->
        <label>Monthly Pocket Money / Allowance</label>
        <input type="text" name="pocket_money" id="pocketMoney" value="<?php echo htmlspecialchars($data['main_income']); ?>" required>

        <label>Other Income (Optional)</label>
        <input type="text" name="other_income" id="otherIncome" value="<?php echo htmlspecialchars($data['other_income']); ?>">

        <label>Main Source of Income</label>
        <select name="income_source" required>
            <option value="">Select</option>
            <option value="Parents" <?php echo ($data['income_source'] == 'Parents') ? 'selected' : ''; ?>>Parents</option>
            <option value="Scholarship" <?php echo ($data['income_source'] == 'Scholarship') ? 'selected' : ''; ?>>Scholarship</option>
            <option value="Part-time Job" <?php echo ($data['income_source'] == 'Part-time Job') ? 'selected' : ''; ?>>Part-time Job</option>
        </select>

        <!-- Expense Fields -->
        <label>Food / Mess Expense (per month)</label>
        <input type="text" name="food_expense" id="foodExpense" value="<?php echo htmlspecialchars($data['food_expense']); ?>" required>

        <label>Transportation Expense</label>
        <input type="text" name="transportation_expense" id="transportationExpense" value="<?php echo htmlspecialchars($data['transportation_expense']); ?>" required>

        <label>Books / Study Materials</label>
        <input type="text" name="books_expense" id="booksExpense" value="<?php echo htmlspecialchars($data['books_expense']); ?>" required>

        <label>Entertainment / Outings</label>
        <input type="text" name="entertainment_expense" id="entertainmentExpense" value="<?php echo htmlspecialchars($data['entertainment_expense']); ?>" required>

        <label>Mobile / Internet Recharge</label>
        <input type="text" name="mobile_expense" id="mobileExpense" value="<?php echo htmlspecialchars($data['mobile_expense']); ?>" required>

        <label>Other Monthly Expenses</label>
        <input type="text" name="other_expense" id="otherExpense" value="<?php echo htmlspecialchars($data['other_expense']); ?>">

        <!-- Goal Fields -->
        <label>Saving Goal (Laptop / Phone / Trip etc.)</label>
        <input type="text" name="saving_goal" id="savingGoal" value="<?php echo htmlspecialchars($data['saving_goal']); ?>" required>

        <label>Goals Amount</label>
        <input type="text" name="goals_amount" id="goalAmount" value="<?php echo htmlspecialchars($data['goal_amount']); ?>" required><br><br>

        <button type="submit">Update Details</button>
    </form>
</div>
</body>
</html>