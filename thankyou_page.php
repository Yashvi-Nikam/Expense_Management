<?php
session_start();
require 'db_connect.php';

$user_id = $_SESSION['user_id'] ?? 1;

// Fetch savings goal info
$goalQuery = $conn->prepare("SELECT savings_amount, goal_amount FROM goals WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$goalQuery->bind_param("i", $user_id);
$goalQuery->execute();
$goalResult = $goalQuery->get_result()->fetch_assoc();

$savings_amount = $goalResult['savings_amount'] ?? 0;
$goal_amount = $goalResult['goal_amount'] ?? 0;

// Optional: you can fetch a goal name or description if you store it
$goal_name = $goalResult['goal_name'] ?? "your savings goal";
?>