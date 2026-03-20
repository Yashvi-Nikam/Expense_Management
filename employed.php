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

$profession         = isset($_POST['business'])          ? $_POST['business']                    : '';
$income             = isset($_POST['income'])             ? floatval($_POST['income'])             : 0;
$other_income       = isset($_POST['other_income'])       ? floatval($_POST['other_income'])       : 0;
$businessExpenses   = isset($_POST['businessExpenses'])   ? floatval($_POST['businessExpenses'])   : 0;
$rent               = isset($_POST['rent'])               ? floatval($_POST['rent'])               : 0;
$materials          = isset($_POST['materials'])          ? floatval($_POST['materials'])          : 0;
$utilities          = isset($_POST['utilities'])          ? floatval($_POST['utilities'])          : 0;
$personalExpenses   = isset($_POST['personalExpenses'])   ? floatval($_POST['personalExpenses'])   : 0;
$otherExpenses      = isset($_POST['otherExpenses'])      ? floatval($_POST['otherExpenses'])      : 0;
$saving_goal_amount = isset($_POST['savings'])            ? floatval($_POST['savings'])            : 0;
$goal               = isset($_POST['savingGoal'])         ? $_POST['savingGoal']                  : '';


/* --------------------------
CURRENT MONTH & YEAR
---------------------------*/

$current_month = date('n');
$current_year  = date('Y');


/* --------------------------
CHECK DUPLICATE SUBMISSION
---------------------------*/

$check = pg_query_params($conn,
    "SELECT * FROM goals WHERE user_id=$1 AND start_month=$2 AND start_year=$3",
    array($user_id, $current_month, $current_year)
);
$is_update = pg_num_rows($check) > 0;


/* --------------------------
CALCULATIONS
---------------------------*/

$total_income   = $income + $other_income;

$total_expense  = $businessExpenses + $rent + $materials +
                  $utilities + $personalExpenses + $otherExpenses;

$calculated_monthly_saving = $total_income - $total_expense;


/* --------------------------
STORE OCCUPATION DETAILS
---------------------------*/

$fields = [
    'profession'        => $profession,
    'income'            => $income,
    'other_income'      => $other_income,
    'business_expense'  => $businessExpenses,
    'rent_expense'      => $rent,
    'materials_expense' => $materials,
    'utilities_expense' => $utilities,
    'personal_expense'  => $personalExpenses,
    'other_expense'     => $otherExpenses,
    'monthly_saving'    => $calculated_monthly_saving,
    'goal_amount'       => $saving_goal_amount,
    'goal'              => $goal
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
SUCCESS MESSAGE
---------------------------*/

$message = $is_update ? 'Employed details updated successfully!' : 'Employed details saved successfully!';
echo "<script>
alert('$message');
window.location.href='dashboard.php';
</script>";

?>