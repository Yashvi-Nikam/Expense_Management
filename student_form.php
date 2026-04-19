<?php
session_start();
include("db_connect.php");

if(!isset($_SESSION['user_id'])){
    echo "<script>alert('User not logged in.'); window.location.href='student_form.html';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];

$main_income            = isset($_POST['pocket_money'])            ? floatval($_POST['pocket_money'])            : 0;
$other_income           = isset($_POST['other_income'])            ? floatval($_POST['other_income'])            : 0;
$food_expense           = isset($_POST['food_expense'])            ? floatval($_POST['food_expense'])            : 0;
$transportation_expense = isset($_POST['transportation_expense'])  ? floatval($_POST['transportation_expense'])  : 0;
$books_expense          = isset($_POST['books_expense'])           ? floatval($_POST['books_expense'])           : 0;
$entertainment_expense  = isset($_POST['entertainment_expense'])   ? floatval($_POST['entertainment_expense'])   : 0;
$mobile_expense         = isset($_POST['mobile_expense'])          ? floatval($_POST['mobile_expense'])          : 0;
$other_expense          = isset($_POST['other_expense'])           ? floatval($_POST['other_expense'])           : 0;
$saving_goal            = isset($_POST['saving_goal'])             ? $_POST['saving_goal']                       : '';
$goal_amount            = isset($_POST['goals_amount'])            ? floatval($_POST['goals_amount'])            : 0;
$income_source          = isset($_POST['income_source'])           ? $_POST['income_source']                     : 'Unknown';

$current_month = date('n');
$current_year  = date('Y');

/* CHECK IF ALREADY SUBMITTED - for goals */
$check = pg_query_params($conn,
    "SELECT * FROM goals WHERE user_id=$1 AND start_month=$2 AND start_year=$3",
    array($user_id, $current_month, $current_year)
);
$is_update = pg_num_rows($check) > 0;

/* CHECK IF INCOME EXISTS - separate check */
$income_check = pg_query_params($conn,
    "SELECT * FROM income WHERE user_id=$1", array($user_id));
$income_exists = pg_num_rows($income_check) > 0;

/* CHECK IF EXPENSE EXISTS - separate check */
$expense_check = pg_query_params($conn,
    "SELECT * FROM expenses WHERE user_id=$1", array($user_id));
$expense_exists = pg_num_rows($expense_check) > 0;

$total_income  = $main_income + $other_income;
$total_expense = $food_expense + $transportation_expense + $books_expense +
                 $entertainment_expense + $mobile_expense + $other_expense;
$calculated_monthly_saving = $total_income - $total_expense;

try {
    if($main_income < 0 || $other_income < 0 || $food_expense < 0 || $transportation_expense < 0 ||
       $books_expense < 0 || $entertainment_expense < 0 || $mobile_expense < 0 || $other_expense < 0 || $goal_amount < 0){
        throw new Exception("Please enter valid positive numbers for all fields.");
    }
    if($main_income <= 0){
        throw new Exception("Pocket money must be greater than 0.");
    }
    if(empty($saving_goal) || strlen($saving_goal) < 2){
        throw new Exception("Please enter a valid saving goal.");
    }
    $valid_sources = ['Parents', 'Scholarship', 'Part-time Job'];
    if(!in_array($income_source, $valid_sources)){
        throw new Exception("Invalid income source selected.");
    }

    /* STORE OCCUPATION DETAILS */
    $fields = [
        'main_income'            => $main_income,
        'other_income'           => $other_income,
        'food_expense'           => $food_expense,
        'transportation_expense' => $transportation_expense,
        'books_expense'          => $books_expense,
        'entertainment_expense'  => $entertainment_expense,
        'mobile_expense'         => $mobile_expense,
        'other_expense'          => $other_expense,
        'income_source'          => $income_source,
        'saving_goal'            => $saving_goal,
        'goal_amount'            => $goal_amount,
        'monthly_saving'         => $calculated_monthly_saving
    ];

    foreach($fields as $name => $value){
        if(is_numeric($value)){
            $value = floatval($value);
            $r = pg_query_params($conn,
                "UPDATE occupation_details SET field_value=$1 WHERE user_id=$2 AND field_name=$3",
                array($value, $user_id, $name));
            if(!$r) throw new Exception("Database error: " . pg_last_error($conn));
            if(pg_affected_rows($r) == 0){
                $insert_result = pg_query_params($conn,
                    "INSERT INTO occupation_details (user_id, field_name, field_value) VALUES ($1, $2, $3)",
                    array($user_id, $name, $value));
                if(!$insert_result) throw new Exception("Database error: " . pg_last_error($conn));
            }
        } else {
            $r = pg_query_params($conn,
                "UPDATE occupation_details SET field_text=$1 WHERE user_id=$2 AND field_name=$3",
                array($value, $user_id, $name));
            if(!$r) throw new Exception("Database error: " . pg_last_error($conn));
            if(pg_affected_rows($r) == 0){
                pg_query_params($conn,
                    "INSERT INTO occupation_details (user_id, field_name, field_text) VALUES ($1, $2, $3)",
                    array($user_id, $name, $value));
            }
        }
    }

    /* HANDLE GOALS - only once with savings_amount */
    if($is_update){
        $goal_result = pg_query_params($conn,
            "UPDATE goals SET goal_purpose=$1, goal_amount=$2, savings_amount=$3
             WHERE user_id=$4 AND start_month=$5 AND start_year=$6",
            array($saving_goal, $goal_amount, $calculated_monthly_saving,
                  $user_id, $current_month, $current_year));
        if(!$goal_result) throw new Exception("Failed to update goals: " . pg_last_error($conn));
    } else {
        $goal_result = pg_query_params($conn,
            "INSERT INTO goals (user_id, goal_purpose, goal_amount, savings_amount, start_month, start_year)
             VALUES ($1, $2, $3, $4, $5, $6)",
            array($user_id, $saving_goal, $goal_amount, $calculated_monthly_saving,
                  $current_month, $current_year));
        if(!$goal_result) throw new Exception("Failed to insert goals: " . pg_last_error($conn));
    }

    /* HANDLE INCOME */
    if($income_exists){
        pg_query_params($conn,
            "UPDATE income SET amount=$1 WHERE user_id=$2",
            array($total_income, $user_id));
    } else {
        pg_query_params($conn,
            "INSERT INTO income (user_id, amount) VALUES ($1, $2)",
            array($user_id, $total_income));
    }

    /* HANDLE EXPENSES */
    if($expense_exists){
        pg_query_params($conn,
            "UPDATE expenses SET amount=$1 WHERE user_id=$2",
            array($total_expense, $user_id));
    } else {
        pg_query_params($conn,
            "INSERT INTO expenses (user_id, amount) VALUES ($1, $2)",
            array($user_id, $total_expense));
    }

    echo "<script>
    alert('Details submitted successfully!');
    window.location.href='dashboard.php';
    </script>";
    exit();

} catch (Exception $e) {
    echo "<script>
    alert('Error: " . addslashes($e->getMessage()) . "');
    window.history.back();
    </script>";
    exit();
}
?>