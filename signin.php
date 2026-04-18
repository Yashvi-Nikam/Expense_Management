<?php
session_start();
$error_message = '';
if(isset($_SESSION['login_message'])){
    $error_message = $_SESSION['login_message'];
    unset($_SESSION['login_message']); // Clear message after displaying
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login</title>

<style>

body{
    margin:0;
    padding:0;
    font-family: Arial, Helvetica, sans-serif;
    height:100vh;

  /*background*/
    background: linear-gradient(rgba(0,0,0,0.4),rgba(0,0,0,0.4)),url("moneyy.jpg");
    background-size:cover;
    background-position:center;
    background-repeat: no-repeat;
    display:flex;
    justify-content:center;
    align-items:center;
}
.login-container{
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(3px);
    padding:40px;
    width:320px;
    border-radius:10px;
    border:1px solid rgba(255,255,255,0.3);
    box-shadow:0px 10px 25px rgba(0,0,0,0.3);
    text-align:left;
    color:black;
}
.login-container h2{
    margin-bottom:20px;
    text-align:center;
    color:black;
}

.message-box{
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 5px;
    text-align: center;
    font-weight: bold;
}

.error-message{
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.success-message{
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.login-btn{
    width:100%;
    padding:12px;
    background:#667eea;
    color:white;
    border:none;
    border-radius:6px;
    cursor:pointer;
    font-size:16px;
}

.login-btn:hover{
    background:#5a67d8;
}

.divider{
    margin:20px 0;
    color:black;
    text-align:center;
}

.actions{
    display:flex;
    align-items:center;
    justify-content:space-between;
    margin-bottom:15px;
}

.small{
    font-size:13px;
    color:black;
}

.google-btn{
    width:100%;
    padding:10px;
    border:1px solid #ddd;
    border-radius:6px;
    background:white;
    cursor:pointer;
    font-size:14px;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:8px;
}

.google-btn:hover{
    background:#f5f5f5;
}
  .footer{
    margin-top:18px;
    font-size:13px;
    color:black;
    text-align:center}
    @media (max-width:420px){.card{padding:20px}
  }
  label{
    display:block;
    font-size:13px;
    margin-bottom:6px;
    color:white
  }
.input-box{
    width:100%;
    padding:10px;
    margin-bottom:15px;
    border-radius:6px;
    border:none;
}
</style>
</head>
<body>
<div class="login-container">
<h2>Login to Your Account</h2>

<?php if($error_message): ?>
    <?php 
    $is_success = strpos($error_message, 'successfully') !== false;
    $message_class = $is_success ? 'success-message' : 'error-message';
    ?>
    <div class="message-box <?php echo $message_class; ?>">
        <?php echo htmlspecialchars($error_message); ?>
    </div>
<?php endif; ?>

<form action="login.php" method="post">
<label for="Username">Username/Email</label>
<input type="text" id="Username" name="login" class="input-box" placeholder="Username or Email" required>
<label for="password">Password</label>
<input type="password" id="password" name="password" class="input-box" placeholder="Password" required>
<div class="actions">
  <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:black"><input type="checkbox" name="remember" /> Remember me</label>
  <a href="forgot_password.php" class="small">Forgot password?</a>
</div>
<button type="submit" class="login-btn">Login</button>
</form>
<div class="divider">OR</div>
<button class="google-btn">
    <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google" style="width:18px;height:18px;" />
    Continue with Google
</button>
<div class="footer">Don't have an account? <a href="signup_form.php">Sign up</a></div>
</div>
</body>
</html>
