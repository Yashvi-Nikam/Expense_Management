<?php

session_start();
include("db_connect.php");

/* --------------------------
CHECK LOGIN
---------------------------*/

if(!isset($_SESSION['user_id'])){
echo "<script>
alert('User not logged in');
window.location.href='signin.php';
</script>";
exit();
}

$user_id = $_SESSION['user_id'];

/* --------------------------
CHECK IF UPDATE
---------------------------*/

$is_update = isset($_POST['is_update']) && $_POST['is_update'] == '1';


/* --------------------------
GET FORM VALUES
---------------------------*/

$fullName           = isset($_POST['fullName'])          ? $_POST['fullName']                     : '';
$monthlyincome      = isset($_POST['monthlyincome'])      ? floatval($_POST['monthlyincome'])      : 0;
$extra_income       = isset($_POST['extra_income'])       ? floatval($_POST['extra_income'])       : 0;
$incomeSource       = isset($_POST['incomeSource'])       ? $_POST['incomeSource']                 : '';
$groceries          = isset($_POST['groceries'])          ? floatval($_POST['groceries'])          : 0;
$utilities          = isset($_POST['utilities'])          ? floatval($_POST['utilities'])          : 0;
$education          = isset($_POST['education'])          ? floatval($_POST['education'])          : 0;
$transportation     = isset($_POST['transportation'])     ? floatval($_POST['transportation'])     : 0;
$shopping           = isset($_POST['shopping'])           ? floatval($_POST['shopping'])           : 0;
$otherExpenses      = isset($_POST['otherExpenses'])      ? floatval($_POST['otherExpenses'])      : 0;
$saving_goal_amount = isset($_POST['monthlySavings'])     ? floatval($_POST['monthlySavings'])     : 0;
$goal               = isset($_POST['savingGoal'])         ? $_POST['savingGoal']                  : '';


/* --------------------------
CURRENT MONTH & YEAR
---------------------------*/

$current_month = date('n');
$current_year  = date('Y');


/* --------------------------
CHECK DUPLICATE SUBMISSION (only for new submissions)
---------------------------*/

if (!$is_update) {
    $check = pg_query_params($conn,
        "SELECT * FROM goals WHERE user_id=$1 AND start_month=$2 AND start_year=$3",
        array($user_id, $current_month, $current_year)
    );

    if(pg_num_rows($check) > 0){
        echo "<script>
        alert('You have already submitted details for this month.');
        window.location.href='dashboard.php';
        </script>";
        exit();
    }
}


/* --------------------------
CALCULATIONS
---------------------------*/

$total_income  = $monthlyincome + $extra_income;

$total_expense = $groceries + $utilities + $education +
                 $transportation + $shopping + $otherExpenses;

$calculated_monthly_saving = $total_income - $total_expense;


/* --------------------------
STORE OCCUPATION DETAILS
---------------------------*/

$fields = [
    'full_name'              => $fullName,
    'monthly_income'         => $monthlyincome,
    'extra_income'           => $extra_income,
    'income_source'          => $incomeSource,
    'groceries_expense'      => $groceries,
    'utilities_expense'      => $utilities,
    'education_expense'      => $education,
    'transportation_expense' => $transportation,
    'shopping_expense'       => $shopping,
    'other_expense'          => $otherExpenses,
    'monthly_saving'         => $calculated_monthly_saving,
    'goal_amount'            => $saving_goal_amount,
    'goal'                   => $goal
];

foreach($fields as $name => $value){
    if(is_numeric($value)){
        $value = floatval($value);
        $r = pg_query_params($conn,
            "UPDATE occupation_details SET field_value=$1 WHERE user_id=$2 AND field_name=$3",
            array($value, $user_id, $name)
        );
        if(pg_affected_rows($r) == 0){
            pg_query_params($conn,
                "INSERT INTO occupation_details (user_id, field_name, field_value) VALUES ($1, $2, $3)",
                array($user_id, $name, $value)
            );
        }
    } else {
        $r = pg_query_params($conn,
            "UPDATE occupation_details SET field_text=$1 WHERE user_id=$2 AND field_name=$3",
            array($value, $user_id, $name)
        );
        if(pg_affected_rows($r) == 0){
            pg_query_params($conn,
                "INSERT INTO occupation_details (user_id, field_name, field_text) VALUES ($1, $2, $3)",
                array($user_id, $name, $value)
            );
        }
    }
}


/* --------------------------
STORE INCOME
---------------------------*/

if($is_update){
    pg_query_params($conn,
        "UPDATE income SET amount=$1 WHERE user_id=$2 AND EXTRACT(MONTH FROM created_at)=$3 AND EXTRACT(YEAR FROM created_at)=$4",
        array($total_income, $user_id, $current_month, $current_year)
    );
} else {
    pg_query_params($conn,
        "INSERT INTO income (user_id, amount) VALUES ($1, $2)",
        array($user_id, $total_income)
    );
}


/* --------------------------
STORE EXPENSE
---------------------------*/

if($is_update){
    pg_query_params($conn,
        "UPDATE expenses SET amount=$1 WHERE user_id=$2 AND EXTRACT(MONTH FROM created_at)=$3 AND EXTRACT(YEAR FROM created_at)=$4",
        array($total_expense, $user_id, $current_month, $current_year)
    );
} else {
    pg_query_params($conn,
        "INSERT INTO expenses (user_id, amount) VALUES ($1, $2)",
        array($user_id, $total_expense)
    );
}


/* --------------------------
STORE GOAL DETAILS
---------------------------*/

if($is_update){
    pg_query_params($conn,
        "UPDATE goals SET goal_purpose=$1, goal_amount=$2, savings_amount=$3 WHERE user_id=$4 AND start_month=$5 AND start_year=$6",
        array($goal, $saving_goal_amount, $calculated_monthly_saving, $user_id, $current_month, $current_year)
    );
} else {
    pg_query_params($conn,
        "INSERT INTO goals (user_id, goal_purpose, goal_amount, savings_amount, start_month, start_year) VALUES ($1, $2, $3, $4, $5, $6)",
        array($user_id, $goal, $saving_goal_amount, $calculated_monthly_saving, $current_month, $current_year)
    );
}


/* --------------------------
STORE IN EXPENSE HISTORY
---------------------------*/

if(!$is_update){
    $summary = "Housewife: Total Income $" . number_format($total_income, 2) . ", Total Expenses $" . number_format($total_expense, 2) . ", Savings $" . number_format($calculated_monthly_saving, 2);
    $details = json_encode([
        'full_name'      => $fullName,
        'monthly_income' => $monthlyincome,
        'extra_income'   => $extra_income,
        'income_source'  => $incomeSource,
        'groceries'      => $groceries,
        'utilities'      => $utilities,
        'education'      => $education,
        'transportation' => $transportation,
        'shopping'       => $shopping,
        'other_expenses' => $otherExpenses,
        'savings_goal'   => $saving_goal_amount,
        'goal'           => $goal
    ]);

    pg_query_params($conn,
        "INSERT INTO expense_history (form_type, summary, details) VALUES ($1, $2, $3)",
        array('housewife', $summary, $details)
    );
}


/* --------------------------
SUCCESS MESSAGE
---------------------------*/

$message = $is_update ? 'Household details updated successfully!' : 'Household details saved successfully!';
echo "<script>
alert('$message');
window.location.href='dashboard.php';
</script>";

?>