<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch latest housewife data from occupation_details
$data = [];
$fields = ['full_name', 'monthly_budget', 'extra_budget', 'income_source', 'groceries_expense', 'utilities_expense', 'education_expense', 'transportation_expense', 'shopping_expense', 'other_expense', 'monthly_saving', 'goal_amount', 'goal'];

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
    <title>Edit Household Budget Details</title>
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
        .radio-group {
            margin-bottom: 12px;
        }
        .radio-group input {
            margin-right: 10px;
        }
    </style>
    <script>
        function validateHousewife() {
            let monthlyBudget = document.getElementById("monthlyBudget").value;
            let extra_budget = document.getElementById("extra_Budget").value;
            let groceries = document.getElementById("groceries").value;
            let utilities = document.getElementById("utilities").value;
            let education = document.getElementById("education").value;
            let transportation = document.getElementById("transportation").value;
            let shopping = document.getElementById("shopping").value;
            let otherExpenses = document.getElementById("otherExpenses").value;
            let savings = document.getElementById("monthlySavings").value;

            if (isNaN(monthlyBudget) || monthlyBudget <= 0) {
                alert("Please enter a valid Monthly Budget.");
                return false;
            }

            if (isNaN(extra_budget) || extra_budget < 0) {
                alert("Please enter a valid Extra Budget.");
                return false;
            }

            if (isNaN(groceries) || groceries < 0) {
                alert("Please enter a valid Groceries expense.");
                return false;
            }

            if (isNaN(utilities) || utilities < 0) {
                alert("Please enter a valid Utilities expense.");
                return false;
            }

            if (isNaN(education) || education < 0) {
                alert("Please enter a valid Education expense.");
                return false;
            }

            if (isNaN(transportation) || transportation < 0) {
                alert("Please enter a valid Transportation expense.");
                return false;
            }

            if (isNaN(shopping) || shopping < 0) {
                alert("Please enter a valid Shopping expense.");
                return false;
            }

            if (isNaN(otherExpenses) || otherExpenses < 0) {
                alert("Please enter a valid Other Expenses.");
                return false;
            }

            if (isNaN(savings) || savings < 0) {
                alert("Please enter a valid Savings amount.");
                return false;
            }

            return true;
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Edit Household Budget Details</h2>
        <form action="housewife.php" method="post" onsubmit="return validateHousewife()">
            <input type="hidden" name="is_update" value="1">

            <label for="fullName">What is your full name?</label>
            <input type="text" id="fullName" name="fullName" value="<?php echo htmlspecialchars($data['full_name']); ?>" required>

            <label for="monthlyBudget">Total monthly household budget</label>
            <input type="text" id="monthlyBudget" name="monthlyBudget" value="<?php echo htmlspecialchars($data['monthly_budget']); ?>" required>

            <label for="extra_Budget">Extra household budget if any</label>
            <input type="text" id="extra_Budget" name="extra_Budget" value="<?php echo htmlspecialchars($data['extra_budget']); ?>">

            <label>Main source of income</label>
            <div class="radio-group">
                <input type="radio" name="incomeSource" value="Husband" <?php echo ($data['income_source'] == 'Husband') ? 'checked' : ''; ?> required> Husband
                <input type="radio" name="incomeSource" value="Family Business" <?php echo ($data['income_source'] == 'Family Business') ? 'checked' : ''; ?>> Family Business
                <input type="radio" name="incomeSource" value="Other" <?php echo ($data['income_source'] == 'Other') ? 'checked' : ''; ?>> Other
            </div>

            <label for="groceries">Groceries per month</label>
            <input type="text" id="groceries" name="groceries" value="<?php echo htmlspecialchars($data['groceries_expense']); ?>" required>

            <label for="utilities">Utilities (Electricity, Water, Gas)</label>
            <input type="text" id="utilities" name="utilities" value="<?php echo htmlspecialchars($data['utilities_expense']); ?>" required>

            <label for="education">Children Education</label>
            <input type="text" id="education" name="education" value="<?php echo htmlspecialchars($data['education_expense']); ?>" required>

            <label for="transportation">Transportation</label>
            <input type="text" id="transportation" name="transportation" value="<?php echo htmlspecialchars($data['transportation_expense']); ?>" required>

            <label for="shopping">Personal / Household Shopping</label>
            <input type="text" id="shopping" name="shopping" value="<?php echo htmlspecialchars($data['shopping_expense']); ?>" required>

            <label for="otherExpenses">Other Monthly Expenses</label>
            <input type="text" id="otherExpenses" name="otherExpenses" value="<?php echo htmlspecialchars($data['other_expense']); ?>">

            <label for="monthlySavings">Amount needed for saving goal</label>
            <input type="text" id="monthlySavings" name="monthlySavings" value="<?php echo htmlspecialchars($data['goal_amount']); ?>" required>

            <label for="savingGoal">Saving Goal</label>
            <input type="text" id="savingGoal" name="savingGoal" value="<?php echo htmlspecialchars($data['goal']); ?>" required>

            <button type="submit">Update Details</button>
        </form>
    </div>
</body>
</html>