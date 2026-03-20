<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch latest self-employed data from occupation_details
$data = [];
$fields = ['name', 'profession', 'income', 'extra_income', 'rent_expense', 'materials_expense', 'salary_expense', 'utilities_expense', 'food_expense', 'transport_expense', 'other_expense', 'monthly_saving', 'goal_amount', 'goal'];

foreach ($fields as $field) {
    $r = pg_query_params($conn,
        "SELECT field_value, field_text FROM occupation_details WHERE user_id=$1 AND field_name=$2 ORDER BY created_at DESC LIMIT 1",
        array($user_id, $field)
    );
    $result = pg_fetch_assoc($r);
    $raw = $result['field_value'] ?? $result['field_text'] ?? '';
    $data[$field] = is_numeric($raw) ? floatval($raw) : $raw;
}

pg_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Self-Employed Expense Details</title>
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
        function validateSelf() {
            let income = document.getElementById("income").value;
            let extra_income = document.getElementById("extra_income").value;
            let rent = document.getElementById("rent").value;
            let materials = document.getElementById("materials").value;
            let salary = document.getElementById("salary").value;
            let electricity = document.getElementById("electricity").value;
            let food = document.getElementById("food").value;
            let transport = document.getElementById("transport").value;
            let other = document.getElementById("other").value;
            let saving = document.getElementById("saving").value;

            if (isNaN(income) || income < 0) {
                alert("Please enter numbers only in Monthly Income.");
                return false;
            }
            if (isNaN(extra_income) || extra_income < 0) {
                alert("Please enter numbers only in Other Income.");
                return false;
            }
            if (isNaN(rent) || rent < 0) {
                alert("Please enter a valid number in Rent.");
                return false;
            }
            if (isNaN(materials) || materials < 0) {
                alert("Please enter a valid number in Materials.");
                return false;
            }
            if (isNaN(salary) || salary < 0) {
                alert("Please enter a valid number in Staff Salary.");
                return false;
            }
            if (isNaN(electricity) || electricity < 0) {
                alert("Please enter a valid number in Electricity / Internet.");
                return false;
            }
            if (isNaN(food) || food < 0) {
                alert("Please enter a valid number in Food / Grocery.");
                return false;
            }
            if (isNaN(transport) || transport < 0) {
                alert("Please enter a valid number in Transport.");
                return false;
            }
            if (isNaN(other) || other < 0) {
                alert("Please enter a valid number in Other Expenses.");
                return false;
            }
            if (isNaN(saving) || saving <= 0) {
                alert("Please enter a valid positive number in Monthly Saving Amount.");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Edit Self-Employed Expense Details</h2>
        <form action="selfemp.php" method="post" onsubmit="return validateSelf()">
            <input type="hidden" name="is_update" value="1">

            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($data['name']); ?>" required>

            <label for="business">Business / Profession</label>
            <input type="text" id="business" name="business" value="<?php echo htmlspecialchars($data['profession']); ?>" required>

            <div class="section">
                <h3>Income</h3>
                <label for="income">Monthly Income</label>
                <input type="text" id="income" name="income" value="<?php echo htmlspecialchars($data['income']); ?>" required>

                <label for="extra_income">Other Income</label>
                <input type="text" id="extra_income" name="extra_income" value="<?php echo htmlspecialchars($data['extra_income']); ?>">
            </div>

            <div class="section">
                <h3>Business Expenses</h3>
                <label for="rent">Shop / Office Rent</label>
                <input type="text" id="rent" name="rent_expense" value="<?php echo htmlspecialchars($data['rent_expense']); ?>">

                <label for="materials">Raw Materials / Supplies</label>
                <input type="text" id="materials" name="materials_expense" value="<?php echo htmlspecialchars($data['materials_expense']); ?>" required>

                <label for="salary">Staff Salary</label>
                <input type="text" id="salary" name="salary_expense" value="<?php echo htmlspecialchars($data['salary_expense']); ?>" required>

                <label for="electricity">Electricity / Internet</label>
                <input type="text" id="electricity" name="utilities_expense" value="<?php echo htmlspecialchars($data['utilities_expense']); ?>">
            </div>

            <div class="section">
                <h3>Personal Expenses</h3>
                <label for="food">Food / Grocery</label>
                <input type="text" id="food" name="food_expense" value="<?php echo htmlspecialchars($data['food_expense']); ?>" required>

                <label for="transport">Transport</label>
                <input type="text" id="transport" name="transport_expense" value="<?php echo htmlspecialchars($data['transport_expense']); ?>" required>

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