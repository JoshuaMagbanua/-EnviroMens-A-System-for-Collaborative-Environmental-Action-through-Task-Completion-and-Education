<?php
session_start();
require_once '../database/dbConnection.php';

// Initialize error variable
$error = '';

$database = new Database();
$conn = $database->getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    try {
        $query = "SELECT * FROM users WHERE username = :username";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['username'] = $row['username'];
                header("Location: task_list.php");
                exit();
            } else {
                $error = "Invalid password";
            }
        } else {
            $error = "User not found";
        }
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <nav class="navbar">
        <ul>
            <li><a href="signup.php">Sign Up</a></li>
            <li><a href="about.php">About Us</a></li>
            <li><a href="homepage.php">Home</a></li>
        </ul>
    </nav>
    <div class="background">
        <img src="../photos/logo.png" class="logo" alt="Logo">
        <div class="login-container">
            <?php if($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div class="form-group">
                    <input type="text" name="username" placeholder="Username" class="login-input" required>
                </div>
                
                <div class="form-group">
                    <input type="password" name="password" placeholder="Password" class="login-input" required>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="login-button">Log In</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 