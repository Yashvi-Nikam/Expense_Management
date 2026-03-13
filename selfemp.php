<?php
session_start();
include("db_connect.php");

// 1️⃣ Check if user logged in
if(!isset($_SESSION['user_id'])){
    echo "<script>alert('User not logged in.'); window.location.href='signin.html';</script>";
    exit();
}
$user_id = $_SESSION['user_id'];
/* --------------------------
GET FORM VALUES
---------------------------*/
$profession = isset($_POST['profession']) ? $_POST['profession'] : '';
$income = isset($_POST['income']) ? floatval($_POST['income']) : 0;
$rent_expense = isset($_POST['rent_expense']) ? floatval($_POST['rent_expense']) : 0;
$materials_expense = isset($_POST['materials_expense']) ? floatval($_POST['materials_expense']) : 0;
$utilities_expense = isset($_POST['utilities_expense']) ? floatval($_POST['utilities_expense']) : 0;
$salary_expense = isset($_POST['salary_expense']) ? floatval($_POST['salary_expense']) : 0;
$food_expense=isset($POST['food_expense'])?floatval($POST['food_expense']): 0;
$transport_expense=isset($POST['transport_expense']) ? floatval($_POST['transport_expense']) : 0;
$other_expense = isset($_POST['other_expense']) ? floatval($_POST['other_expense']) : 0;
$saving = isset($_POST['saving']) ? floatval($_POST['saving']) : 0;
$goal = isset($_POST['goal']) ? $_POST['goal'] : '';

/* --------------------------
CURRENT MONTH & YEAR
---------------------------*/

$current_month = date('n');
$current_year = date('Y');

/* --------------------------
CALCULATIONS
---------------------------*/

$total_income = $income;

$total_expense = $rent_expense + $materials_expense + $utilities_expense + $salary_expense + $food_expense + $transport_expense + $other_expense;

$calculated_monthly_saving = $total_income - $total_expense;

/* --------------------------
CHECK USER ENTERED SAVINGS
---------------------------*/

if(abs($user_monthly_saving - $calculated_monthly_saving) > 0.01){
    echo "<script>
    alert('Your entered savings ($saving) does not match the calculated value ($calculated_monthly_saving). Please correct it.');
    window.history.back();
    </script>";
    exit();
}

/* --------------------------
STORE OCCUPATION DETAILS
---------------------------*/

$fields = [
    'profession' => $profession,
    'income' => $income,
    'rent_expense' => $rent_expense,
    'materials_expense' => $materials_expense,
    'utilities_expense' => $utilities_expense,
    'salary_expense' => $salary_expense,
    'other_expense' => $other_expense,
    'food_expense'=>$food_expense,
    'transport_expense'=>$transport_expense,
    'monthly_saving' => $calculated_monthly_saving,
    'goal' => $goal
];

foreach($fields as $field_name => $field_value){

    if(is_numeric($field_value)){
        $value_str = floatval($field_value);
    } else {
        $value_str = mysqli_real_escape_string($conn, $field_value);
    }

    mysqli_query($conn, "INSERT INTO occupation_details (user_id, field_name, field_value)
    VALUES ('$user_id', '$field_name', '$value_str')");
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
SUCCESS MESSAGE
---------------------------*/

echo "<script>
alert('Self-employed details saved successfully!');
window.location.href='dashboard.html';
</script>";

?>