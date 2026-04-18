<?php
session_start();
require 'db_connect.php';

/* Check login session */
if(!isset($_SESSION['user_id'])){
    header("Location: signin.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* Fetch goal info */
$result = pg_query_params($conn, "
    SELECT goal_amount, goal_purpose 
    FROM goals 
    WHERE user_id = $1 
    ORDER BY created_at DESC 
    LIMIT 1
", array($user_id));

$goalResult = pg_fetch_assoc($result);

/* Calculate savings from occupation_details */
$income_result = pg_query_params($conn, "
    SELECT SUM(field_value) as total FROM occupation_details 
    WHERE user_id=$1 AND field_name LIKE '%income%'
", array($user_id));
$total_income = pg_fetch_assoc($income_result)['total'] ?? 0;

$expense_result = pg_query_params($conn, "
    SELECT SUM(field_value) as total FROM occupation_details 
    WHERE user_id=$1 AND field_name LIKE '%expense%'
", array($user_id));
$total_expense = pg_fetch_assoc($expense_result)['total'] ?? 0;

$savings_amount = $total_income - $total_expense;
$goal_purpose = $goalResult['goal_purpose'] ?? "your savings goal";
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Congratulations!</title>

<style>
body{
    font-family:'Poppins',sans-serif;
    background:linear-gradient(135deg,#4ade80,#22d3ee);
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
    margin:0;
}

.card{
    background:white;
    padding:40px;
    border-radius:20px;
    box-shadow:0 15px 35px rgba(0,0,0,0.2);
    max-width:500px;
    text-align:center;
}

.card h1{
    font-size:32px;
    color:#16a34a;
    margin-bottom:20px;
}

.card p{
    font-size:18px;
    color:#374151;
}

.amount{
    font-size:22px;
    font-weight:bold;
    color:#2563eb;
}

.card button{
    margin-top:25px;
    padding:12px 25px;
    background:#4f46e5;
    color:white;
    border:none;
    border-radius:10px;
    cursor:pointer;
    font-size:16px;
    transition:0.2s;
}

.card button:hover{
    background:#6366f1;
}

#celebrationBanner{
position:fixed;
top:-100px;
left:0;
width:100%;
text-align:center;
background:linear-gradient(90deg,#16a34a,#22d3ee);
color:white;
font-size:22px;
font-weight:bold;
padding:20px;
letter-spacing:1px;
box-shadow:0 5px 15px rgba(0,0,0,0.2);
transition:top 0.8s ease;
z-index:9999;
}
.card {
    background: white;
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.2);
    max-width: 500px;
    text-align: center;
    transition: box-shadow 1s ease-in-out;
    animation: glow 2s infinite alternate;
}

@keyframes glow {
    0% {
        box-shadow: 0 0 20px rgba(79,70,229,0.4), 0 0 40px rgba(79,70,229,0.2);
    }
    100% {
        box-shadow: 0 0 30px rgba(79,70,229,0.8), 0 0 60px rgba(79,70,229,0.4);
    }
}

.coin {
    position: fixed;
    width: 25px;
    height: 25px;
    background: gold;
    border-radius: 50%;
    top: -50px;
    left: 50%;
    opacity: 0.9;
    animation: fall 4s linear infinite;
    box-shadow: 0 0 5px rgba(0,0,0,0.3);
}

@keyframes fall {
    0% { transform: translateY(0) rotate(0deg); }
    100% { transform: translateY(600px) rotate(360deg); }
}

</style>
</head>

<body>

<div id="celebrationBanner">
🎉 GOAL ACHIEVED! YOU DID IT! 🎉
</div>

<div class="card">

<h1>🎉 Congratulations!</h1>

<p>You’ve successfully reached your savings goal!</p>

<p>
Saved Amount: 
<span class="amount">₹<?php echo number_format($savings_amount,2); ?></span>
</p>


<p>
Goal Purpose: 
<strong><?php echo htmlspecialchars($goal_purpose); ?></strong>
</p>

<button onclick="window.location.href='dashboard.php'">
Back to Dashboard
</button>

</div>
 <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

<script>

window.onload=function(){

/* slide celebration banner */

let banner=document.getElementById("celebrationBanner");

setTimeout(function(){
banner.style.top="0";
},300);


/* confetti burst */

var duration = 3 * 1000;
var end = Date.now() + duration;

(function frame() {
confetti({
particleCount:5,
angle:60,
spread:55,
origin:{x:0}
});
confetti({
particleCount:5,
angle:120,
spread:55,
origin:{x:1}
});

if(Date.now() < end){
requestAnimationFrame(frame);
}

})();

}

// Auto redirect after 5 seconds
setTimeout(function(){
    window.location.href = 'dashboard.php';
}, 5000);

// Create 10 coins
for(let i=0;i<10;i++){
    let coin = document.createElement('div');
    coin.classList.add('coin');
    coin.style.left = Math.random() * window.innerWidth + 'px';
    coin.style.animationDuration = (3 + Math.random()*2) + 's';
    document.body.appendChild(coin);
}

</script>
</body>
</html>