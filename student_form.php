<?php
session_start();
include("db_connect.php");

// 1️⃣ Check if user is logged in
if(!isset($_SESSION['user_id'])){
    echo "<script>alert('User not logged in.'); window.location.href='student_form.html';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];

/* --------------------------
GET FORM VALUES
---------------------------*/

// Income
$main_income = isset($_POST['pocket_money']) ? floatval($_POST['pocket_money']) : 0;
$other_income = isset($_POST['other_income']) ? floatval($_POST['other_income']) : 0;

// Expenses
$food_expense = isset($_POST['food_expense']) ? floatval($_POST['food_expense']) : 0;
$transportation_expense = isset($_POST['transportation_expense']) ? floatval($_POST['transportation_expense']) : 0;
$books_expense = isset($_POST['books_expense']) ? floatval($_POST['books_expense']) : 0;
$entertainment_expense = isset($_POST['entertainment_expense']) ? floatval($_POST['entertainment_expense']) : 0;
$mobile_expense = isset($_POST['mobile_expense']) ? floatval($_POST['mobile_expense']) : 0;
$other_expense = isset($_POST['other_expense']) ? floatval($_POST['other_expense']) : 0;

// Goal info
$saving_goal = isset($_POST['saving_goal']) ? $_POST['saving_goal'] : '';
$goal_amount = isset($_POST['goals_amount']) ? floatval($_POST['goals_amount']) : 0;

// Income source
$income_source = isset($_POST['income_source']) ? $_POST['income_source'] : 'Unknown';

// Current month & year for tracking
$current_month = date('n'); // 1-12
$current_year = date('Y');

/* --------------------------
CHECK IF ALREADY SUBMITTED
---------------------------*/
$check = mysqli_query($conn, "SELECT * FROM goals WHERE user_id='$user_id' AND start_month='$current_month' AND start_year='$current_year'");
if(mysqli_num_rows($check) > 0){
    echo "<script>
        alert('You have already submitted your details for this month. Use Edit option in dashboard to modify.');
        window.location.href='dashboard.html';
        </script>";
    exit();
}

/* --------------------------
CALCULATIONS
---------------------------*/
$total_income = $main_income + $other_income;
$total_expense = $food_expense + $transportation_expense + $books_expense + $entertainment_expense + $mobile_expense + $other_expense;
$calculated_monthly_saving = $total_income - $total_expense;

/* --------------------------
STORE OCCUPATION DETAILS
---------------------------*/
$fields = [
    'main_income' => $main_income,
    'other_income' => $other_income,
    'income_source' => $income_source,
    'food_expense' => $food_expense,
    'transportation_expense' => $transportation_expense,
    'books_expense' => $books_expense,
    'entertainment_expense' => $entertainment_expense,
    'mobile_expense' => $mobile_expense,
    'other_expense' => $other_expense,
    'saving_goal' => $saving_goal
];

foreach($fields as $name => $value){
    if(is_numeric($value)){
        $value_str = floatval($value);
    } else {
        $value_str = mysqli_real_escape_string($conn, $value);
    }
    mysqli_query($conn, "INSERT INTO occupation_details (user_id, field_name, field_value) VALUES ('$user_id', '$name', '$value_str')");
}

/* --------------------------
STORE TOTAL INCOME
---------------------------*/
mysqli_query($conn, "INSERT INTO income (user_id, amount) VALUES ('$user_id', '$total_income')");

/* --------------------------
STORE TOTAL EXPENSE
---------------------------*/
mysqli_query($conn, "INSERT INTO expenses (user_id, amount) VALUES ('$user_id', '$total_expense')");

/* --------------------------
STORE GOALS
---------------------------*/
mysqli_query($conn, "INSERT INTO goals (user_id, goal_purpose, goal_amount, savings_amount, start_month, start_year) 
VALUES ('$user_id', '$saving_goal', '$goal_amount', '$calculated_monthly_saving', '$current_month', '$current_year')");


/* --------------------------
SUCCESS MESSAGE
---------------------------*/
echo "<script>
alert('Student details saved successfully!');
window.location.href='dashboard.php';
</script>";
?>