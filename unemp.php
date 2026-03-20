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
CHECK IF UPDATE
---------------------------*/

$is_update = isset($_POST['is_update']) && $_POST['is_update'] == '1';


/* --------------------------
GET FORM VALUES
---------------------------*/

$name               = isset($_POST['name'])              ? $_POST['name']                       : '';
$income_source      = isset($_POST['source'])            ? $_POST['source']                     : '';
$budget             = isset($_POST['income'])            ? floatval($_POST['income'])            : 0;
$extra_budget       = isset($_POST['extra_income'])      ? floatval($_POST['extra_income'])      : 0;
$food_expense       = isset($_POST['food_expense'])      ? floatval($_POST['food_expense'])      : 0;
$transport_expense  = isset($_POST['transport_expense']) ? floatval($_POST['transport_expense']) : 0;
$internet_expense   = isset($_POST['internet_expense'])  ? floatval($_POST['internet_expense'])  : 0;
$learning_expense   = isset($_POST['learning_expense'])  ? floatval($_POST['learning_expense'])  : 0;
$other_expense      = isset($_POST['other_expense'])     ? floatval($_POST['other_expense'])     : 0;
$saving_goal_amount = isset($_POST['saving'])            ? floatval($_POST['saving'])            : 0;
$goal               = isset($_POST['goal'])              ? $_POST['goal']                       : '';


/* --------------------------
CURRENT MONTH & YEAR
---------------------------*/

$current_month = date('n');
$current_year  = date('Y');


/* --------------------------
CHECK DUPLICATE SUBMISSION (only for new submissions)
---------------------------*/

if(!$is_update){
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
⚠️ NOTE: Original code had a bug — used $income/$extra_income
instead of $budget/$extra_budget. Fixed below.
---------------------------*/

$total_income  = $budget + $extra_budget;

$total_expense = $food_expense + $transport_expense + $internet_expense +
                 $learning_expense + $other_expense;

$calculated_saving = $total_income - $total_expense;


/* --------------------------
STORE OCCUPATION DETAILS
---------------------------*/

$fields = [
    'name'               => $name,
    'income_source'      => $income_source,
    'budget'             => $budget,
    'extra_budget'       => $extra_budget,
    'food_expense'       => $food_expense,
    'transport_expense'  => $transport_expense,
    'internet_expense'   => $internet_expense,
    'learning_expense'   => $learning_expense,
    'other_expense'      => $other_expense,
    'monthly_saving'     => $calculated_saving,
    'goal_amount'        => $saving_goal_amount,
    'goal'               => $goal
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
        array($goal, $saving_goal_amount, $calculated_saving, $user_id, $current_month, $current_year)
    );
} else {
    pg_query_params($conn,
        "INSERT INTO goals (user_id, goal_purpose, goal_amount, savings_amount, start_month, start_year) VALUES ($1, $2, $3, $4, $5, $6)",
        array($user_id, $goal, $saving_goal_amount, $calculated_saving, $current_month, $current_year)
    );
}


/* --------------------------
SUCCESS MESSAGE
---------------------------*/

$message = $is_update ? 'Unemployed details updated successfully!' : 'Unemployed details saved successfully!';
echo "<script>
alert('$message');
window.location.href='dashboard.php';
</script>";

?>