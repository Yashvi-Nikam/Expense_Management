<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch latest employed data from occupation_details
$data = [];
$fields = ['profession', 'income', 'other_income', 'business_expense', 'rent_expense', 'materials_expense', 'utilities_expense', 'personal_expense', 'other_expense', 'monthly_saving', 'goal_amount', 'goal'];

foreach ($fields as $field) {
    $query = $conn->prepare("SELECT field_value, field_text FROM occupation_details WHERE user_id = ? AND field_name = ? ORDER BY created_at DESC LIMIT 1");
    $query->bind_param("is", $user_id, $field);
    $query->execute();
    $result = $query->get_result()->fetch_assoc();
    $data[$field] = is_numeric($result['field_value'] ?? $result['field_text']) ? floatval($result['field_value'] ?? $result['field_text']) : ($result['field_text'] ?? '');
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Employed Expense Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url('expenseimage.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        label {
            display: block;
            margin: 10px 0 5px;
            color: #555;
        }
        input[type="text"], textarea {
            width: 100%;
            padding: 8px 10px;
            margin-bottom: 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        textarea {
            height: 80px;
            resize: vertical;
        }
        button {
            width: 100%;
            background-color: #4caf50;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
    <script>
        function validateEmployed() {
            let income = document.getElementById("income").value;
            let other_income = document.getElementById("other_income").value;
            let businessExpenses = document.getElementById("businessExpenses").value;
            let rent = document.getElementById("rent").value;
            let materials = document.getElementById("materials").value;
            let utilities = document.getElementById("utilities").value;
            let personalExpenses = document.getElementById("personalExpenses").value;
            let otherExpenses = document.getElementById("otherExpenses").value;
            let savings = document.getElementById("savings").value;

            if (isNaN(income) || income <= 0) {
                alert("Please enter a valid positive number in Monthly Income.");
                return false;
            }
            if (isNaN(other_income) || other_income < 0) {
                alert("Please enter a valid positive number in Other Income.");
                return false;
            }
            if (isNaN(businessExpenses) || businessExpenses < 0) {
                alert("Please enter a valid positive number in Business Expenses.");
                return false;
            }
            if (isNaN(rent) || rent < 0) {
                alert("Please enter a valid positive number in Rent.");
                return false;
            }
            if (isNaN(materials) || materials < 0) {
                alert("Please enter a valid positive number in Materials.");
                return false;
            }
            if (isNaN(utilities) || utilities < 0) {
                alert("Please enter a valid positive number in Utilities.");
                return false;
            }
            if (isNaN(personalExpenses) || personalExpenses < 0) {
                alert("Please enter a valid positive number in Personal Expenses.");
                return false;
            }
            if (isNaN(otherExpenses) || otherExpenses < 0) {
                alert("Please enter a valid positive number in Other Expenses.");
                return false;
            }
            if (isNaN(savings) || savings < 0) {
                alert("Please enter a valid positive number in Savings.");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
<div class="container">
    <h2>Edit Employed Expense Details</h2>
    <form id="expenseForm" action="employed.php" method="post" onsubmit="return validateEmployed()">
        <label for="business">What is your business or profession?</label>
        <input type="text" id="business" name="business" value="<?php echo htmlspecialchars($data['profession']); ?>" required>

        <label for="income">What is your approximate monthly income?</label>
        <input type="text" id="income" name="income" value="<?php echo htmlspecialchars($data['income']); ?>" required>

        <label for="other_income">What is your other income if any?</label>
        <input type="text" id="other_income" name="other_income" value="<?php echo htmlspecialchars($data['other_income']); ?>">

        <label for="businessExpenses">How much do you spend on business expenses?</label>
        <input type="text" id="businessExpenses" name="businessExpenses" value="<?php echo htmlspecialchars($data['business_expense']); ?>" required>

        <label for="rent">How much do you spend on rent or workspace?</label>
        <input type="text" id="rent" name="rent" value="<?php echo htmlspecialchars($data['rent_expense']); ?>" required>

        <label for="materials">How much do you spend on materials or supplies?</label>
        <input type="text" id="materials" name="materials" value="<?php echo htmlspecialchars($data['materials_expense']); ?>" required>

        <label for="utilities">How much do you spend on utilities (electricity, internet)?</label>
        <input type="text" id="utilities" name="utilities" value="<?php echo htmlspecialchars($data['utilities_expense']); ?>" required>

        <label for="personalExpenses">How much do you spend on personal expenses?</label>
        <input type="text" id="personalExpenses" name="personalExpenses" value="<?php echo htmlspecialchars($data['personal_expense']); ?>" required>

        <label for="otherExpenses">Do you have any other expenses?</label>
        <input type="text" id="otherExpenses" name="otherExpenses" value="<?php echo htmlspecialchars($data['other_expense']); ?>">

        <label for="savings">How much amount do you need to achieve your saving goal?</label>
        <input type="text" id="savings" name="savings" value="<?php echo htmlspecialchars($data['goal_amount']); ?>" required>

        <label for="savingGoal">What is your saving goal or item you want to purchase?</label>
        <input type="text" id="savingGoal" name="savingGoal" value="<?php echo htmlspecialchars($data['goal']); ?>">

        <button type="submit">Update Details</button>
    </form>
</div>
</body>
</html>