<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's basic info
$userQuery = $conn->prepare("SELECT name, age, gender, occupation FROM users WHERE user_id = ?");
$userQuery->bind_param("i", $user_id);
$userQuery->execute();
$user = $userQuery->get_result()->fetch_assoc();

$name = $user['name'] ?? '';
$age = $user['age'] ?? '';
$gender = $user['gender'] ?? '';
$occupation = $user['occupation'] ?? '';

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Basic Information</title>
    <link rel="stylesheet" href="formstyle.css">
</head>
<body>
<div class="form-container">
    <h1 class="title">Edit Basic Information</h1>
    <form id="generalForm" action="basicinfoform.php" method="post">
        <input type="hidden" name="is_edit" value="1">
        <label>Full Name</label>
        <input type="text" placeholder="Enter your name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>

        <label>Age</label>
        <input type="number" placeholder="Enter your age" name="age" min="12" max="100" value="<?php echo htmlspecialchars($age); ?>" required>

        <label>Gender</label>
        <select name="gender" required>
            <option value="">Select</option>
            <option value="male" <?php echo ($gender == 'male') ? 'selected' : ''; ?>>Male</option>
            <option value="female" <?php echo ($gender == 'female') ? 'selected' : ''; ?>>Female</option>
            <option value="other" <?php echo ($gender == 'other') ? 'selected' : ''; ?>>Other</option>
        </select>
        <br>
        <label>Occupation (Cannot be changed)</label>
        <br>
        <label>
            <input type="radio" name="occupation" value="student" <?php echo ($occupation == 'student') ? 'checked readonly' : 'disabled'; ?>> Student
        </label>
        <label>
            <input type="radio" name="occupation" value="employed" <?php echo ($occupation == 'employed') ? 'checked readonly' : 'disabled'; ?>> Employed
        </label>
        <label>
            <input type="radio" name="occupation" value="housewife" <?php echo ($occupation == 'housewife') ? 'checked readonly' : 'disabled'; ?>> Homemaker
        </label>
        <label>
            <input type="radio" name="occupation" value="self-employed" <?php echo ($occupation == 'self-employed') ? 'checked readonly' : 'disabled'; ?>> Self-Employed
        </label>
        <label>
            <input type="radio" name="occupation" value="unemployed" <?php echo ($occupation == 'unemployed') ? 'checked readonly' : 'disabled'; ?>> Unemployed
        </label>        <input type="hidden" name="occupation" value="<?php echo htmlspecialchars($occupation); ?>">        <br><br>
        <button type="submit">Update Basic Info</button>
        <br><br>
        <a href="edit_student.php" style="display: inline-block; padding: 10px 20px; background: #4caf50; color: white; text-decoration: none; border-radius: 4px;">Edit Student Details</a>
    </form>
</div>
</body>
</html>