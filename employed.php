<?php

session_start();
include("db_connect.php");

/* --------------------------
CHECK LOGIN
---------------------------*/

if(!isset($_SESSION['user_id'])){
echo "<script>
alert('User not logged in');
window.location.href='signin.html';
</script>";
exit();
}

$user_id = $_SESSION['user_id'];


/* --------------------------
GET FORM VALUES
---------------------------*/

$profession = isset($_POST['business']) ? $_POST['business'] : '';

$income = isset($_POST['income']) ? floatval($_POST['income']) : 0;

$other_income = isset($_POST['other_income']) ? floatval($_POST['other_income']) : 0;

$businessExpenses = isset($_POST['businessExpenses']) ? floatval($_POST['businessExpenses']) : 0;

$rent = isset($_POST['rent']) ? floatval($_POST['rent']) : 0;

$materials = isset($_POST['materials']) ? floatval($_POST['materials']) : 0;

$utilities = isset($_POST['utilities']) ? floatval($_POST['utilities']) : 0;

$personalExpenses = isset($_POST['personalExpenses']) ? floatval($_POST['personalExpenses']) : 0;

$otherExpenses = isset($_POST['otherExpenses']) ? floatval($_POST['otherExpenses']) : 0;

$saving_goal_amount = isset($_POST['savings']) ? floatval($_POST['savings']) : 0;

$goal = isset($_POST['savingGoal']) ? $_POST['savingGoal'] : '';


/* --------------------------
CURRENT MONTH & YEAR
---------------------------*/

$current_month = date('n');
$current_year = date('Y');


/* --------------------------
CHECK DUPLICATE SUBMISSION
---------------------------*/
$check = mysqli_query($conn,
"SELECT * FROM goals
WHERE user_id='$user_id'
AND start_month='$current_month'
AND start_year='$current_year'");
$is_update = mysqli_num_rows($check) > 0;

if ($is_update) {
    // Allow update
} else {
    // Proceed with insert
}


/* --------------------------
CALCULATIONS
---------------------------*/

$total_income = $income + $other_income;

$total_expense =
$businessExpenses +
$rent +
$materials +
$utilities +
$personalExpenses +
$otherExpenses;

$calculated_monthly_saving = $total_income - $total_expense;


/* --------------------------
STORE OCCUPATION DETAILS
---------------------------*/

$fields = [

'profession' => $profession,

'income' => $income,
'other_income' => $other_income,

'business_expense' => $businessExpenses,
'rent_expense' => $rent,
'materials_expense' => $materials,
'utilities_expense' => $utilities,

'personal_expense' => $personalExpenses,
'other_expense' => $otherExpenses,

'monthly_saving' => $calculated_monthly_saving,
'goal_amount' => $saving_goal_amount,
'goal' => $goal

];

/* --------------------------
STORE OCCUPATION DETAILS
---------------------------*/
foreach($fields as $name => $value){
    if(is_numeric($value)){
        $value = floatval($value);
        $updateQuery = "UPDATE occupation_details SET field_value='$value' WHERE user_id='$user_id' AND field_name='$name'";
        mysqli_query($conn, $updateQuery);
        if (mysqli_affected_rows($conn) == 0) {
            mysqli_query($conn, "INSERT INTO occupation_details (user_id, field_name, field_value) VALUES ('$user_id', '$name', '$value')");
        }
    } else {
        $value = mysqli_real_escape_string($conn, $value);
        $updateQuery = "UPDATE occupation_details SET field_text='$value' WHERE user_id='$user_id' AND field_name='$name'";
        mysqli_query($conn, $updateQuery);
        if (mysqli_affected_rows($conn) == 0) {
            mysqli_query($conn, "INSERT INTO occupation_details (user_id, field_name, field_text) VALUES ('$user_id', '$name', '$value')");
        }
    }
}


/* --------------------------
STORE INCOME
---------------------------*/
if ($is_update) {
    mysqli_query($conn, "UPDATE income SET amount='$total_income' WHERE user_id='$user_id' AND MONTH(created_at)='$current_month' AND YEAR(created_at)='$current_year'");
} else {
    mysqli_query($conn, "INSERT INTO income (user_id, amount) VALUES ('$user_id', '$total_income')");
}


/* --------------------------
STORE EXPENSE
---------------------------*/
if ($is_update) {
    mysqli_query($conn, "UPDATE expenses SET amount='$total_expense' WHERE user_id='$user_id' AND MONTH(created_at)='$current_month' AND YEAR(created_at)='$current_year'");
} else {
    mysqli_query($conn, "INSERT INTO expenses (user_id, amount) VALUES ('$user_id', '$total_expense')");
}

/* --------------------------
STORE GOAL DETAILS
---------------------------*/
if ($is_update) {
    mysqli_query($conn, "UPDATE goals SET goal_purpose='$goal', goal_amount='$saving_goal_amount', savings_amount='$calculated_monthly_saving' WHERE user_id='$user_id' AND start_month='$current_month' AND start_year='$current_year'");
} else {
    mysqli_query($conn, "INSERT INTO goals (user_id, goal_purpose, goal_amount, savings_amount, start_month, start_year) VALUES ('$user_id', '$goal', '$saving_goal_amount', '$calculated_monthly_saving', '$current_month', '$current_year')");
}

/* --------------------------
STORE IN EXPENSE HISTORY
---------------------------*/
if (!$is_update) {
    $summary = "Employed: Total Income $" . number_format($total_income, 2) . ", Total Expenses $" . number_format($total_expense, 2) . ", Savings $" . number_format($calculated_monthly_saving, 2);
    $details = json_encode([
        'profession' => $profession,
        'income' => $income,
        'other_income' => $other_income,
        'business_expenses' => $businessExpenses,
        'rent' => $rent,
        'materials' => $materials,
        'utilities' => $utilities,
        'personal_expenses' => $personalExpenses,
        'other_expenses' => $otherExpenses,
        'savings_goal' => $saving_goal_amount,
        'goal' => $goal
    ]);

    mysqli_query($conn,
    "INSERT INTO expense_history (form_type, summary, details)
    VALUES ('employed', '$summary', '$details')");
}

/* --------------------------
SUCCESS MESSAGE
---------------------------*/

$message = $is_update ? 'Employed details updated successfully!' : 'Employed details saved successfully!';
echo "<script>
alert('$message');
window.location.href='dashboard.php';
</script>";

?>