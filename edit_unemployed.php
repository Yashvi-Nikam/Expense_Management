<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch latest unemployed data from occupation_details
$data = [];
$fields = ['name', 'income_source', 'budget', 'extra_budget', 'food_expense', 'transport_expense', 'internet_expense', 'learning_expense', 'other_expense', 'monthly_saving', 'goal_amount', 'goal'];

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
    <title>Edit Unemployed Expense Details</title>
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
        input[type="text"], select, textarea {
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
        .section {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .section h3 {
            margin-top: 0;
            color: #333;
        }
    </style>
    <script>
        function validateUnemployed() {
            let budget = document.getElementById("budget").value;
            let extra_budget = document.getElementById("extra_budget").value;
            let food = document.getElementById("food").value;
            let transport = document.getElementById("transport").value;
            let internet = document.getElementById("internet").value;
            let other = document.getElementById("other").value;
            let saving = document.getElementById("saving").value;

            if (isNaN(budget) || budget <= 0) {
                alert("Please enter a valid number in Budget.");
                return false;
            }
            if (isNaN(extra_budget) || extra_budget < 0) {
                alert("Please enter a valid number in Extra Budget.");
                return false;
            }
            if (isNaN(food) || food < 0) {
                alert("Please enter a valid number in Food Expense.");
                return false;
            }
            if (isNaN(transport) || transport < 0) {
                alert("Please enter a valid number in Transport Expense.");
                return false;
            }
            if (isNaN(internet) || internet < 0) {
                alert("Please enter a valid number in Mobile / Internet Expense.");
                return false;
            }
            if (isNaN(other) || other < 0) {
                alert("Please enter a valid number in Other Expenses.");
                return false;
            }
            if (isNaN(saving) || saving < 0) {
                alert("Please enter a valid number in Monthly Saving Amount.");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Edit Unemployed Expense Details</h2>
        <form action="unemp.php" method="post" onsubmit="return validateUnemployed()">
            <input type="hidden" name="is_update" value="1">

            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($data['name']); ?>" required>

            <label for="source">Source of Money</label>
            <select id="source" name="source">
                <option value="Family Support" <?php echo ($data['income_source'] == 'Family Support') ? 'selected' : ''; ?>>Family Support</option>
                <option value="Savings" <?php echo ($data['income_source'] == 'Savings') ? 'selected' : ''; ?>>Savings</option>
                <option value="Freelancing" <?php echo ($data['income_source'] == 'Freelancing') ? 'selected' : ''; ?>>Freelancing</option>
                <option value="Other" <?php echo ($data['income_source'] == 'Other') ? 'selected' : ''; ?>>Other</option>
            </select>

            <div class="section">
                <h3>Income</h3>
                <label for="budget">Monthly Available Budget</label>
                <input type="text" id="budget" name="budget" value="<?php echo htmlspecialchars($data['budget']); ?>" required>

                <label for="extra_budget">Any Extra income if any?</label>
                <input type="text" id="extra_budget" name="extra_budget" value="<?php echo htmlspecialchars($data['extra_budget']); ?>">
            </div>

            <div class="section">
                <h3>Monthly Expenses</h3>
                <label for="food">Food Expense</label>
                <input type="text" id="food" name="food_expense" value="<?php echo htmlspecialchars($data['food_expense']); ?>" required>

                <label for="transport">Transport Expense</label>
                <input type="text" id="transport" name="transport_expense" value="<?php echo htmlspecialchars($data['transport_expense']); ?>" required>

                <label for="internet">Mobile / Internet</label>
                <input type="text" id="internet" name="internet_expense" value="<?php echo htmlspecialchars($data['internet_expense']); ?>" required>

                <label for="learning">Learning / Job Search Expense</label>
                <input type="text" id="learning" name="learning_expense" value="<?php echo htmlspecialchars($data['learning_expense']); ?>" required>

                <label for="other">Other Expenses</label>
                <input type="text" id="other" name="other_expense" value="<?php echo htmlspecialchars($data['other_expense']); ?>">
            </div>

            <label for="saving">Monthly Saving Amount</label>
            <input type="text" id="saving" name="saving" value="<?php echo htmlspecialchars($data['goal_amount']); ?>" required>

            <label for="goal">Saving Goal</label>
            <input type="text" id="goal" name="goal" value="<?php echo htmlspecialchars($data['goal']); ?>">

            <button type="submit">Update Details</button>
        </form>
    </div>
</body>
</html>