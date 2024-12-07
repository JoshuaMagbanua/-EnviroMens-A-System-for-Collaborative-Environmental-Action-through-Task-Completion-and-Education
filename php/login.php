<?php
session_start();
require_once '../database/dbConnection.php';

// Initialize error variable
$alertMessage = '';

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
                $_SESSION['profile_picture'] = $row['profile_picture'];
                $_SESSION['total_points'] = $row['total_points'];
                $_SESSION['tasks_completed'] = $row['tasks_completed'];
                $alertMessage = [
                    'icon' => 'success',
                    'title' => 'Success!',
                    'text' => 'Login successful! Redirecting...',
                    'redirect' => true
                ];
            } else {
                $alertMessage = [
                    'icon' => 'error',
                    'title' => 'Invalid Password',
                    'text' => 'The password you entered is incorrect.'
                ];
            }
        } else {
            $alertMessage = [
                'icon' => 'error',
                'title' => 'Invalid Password',
                'text' => 'The password you entered is incorrect.'
            ];
        }
    } catch(PDOException $e) {
        $alertMessage = [
            'icon' => 'error',
            'title' => 'Error',
            'text' => 'An error occurred. Please try again.'
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login</title>
    <link rel="stylesheet" href="../css/login.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.9.0/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.9.0/dist/sweetalert2.all.min.js"></script>
</head>
<body>
    <?php if (!empty($alertMessage)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: '<?php echo $alertMessage['icon']; ?>',
                title: '<?php echo $alertMessage['title']; ?>',
                text: '<?php echo $alertMessage['text']; ?>',
                <?php if (isset($alertMessage['redirect'])): ?>
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                window.location.href = 'task_list.php';
                <?php else: ?>
                showConfirmButton: true
                <?php endif; ?>
            });
        });
    </script>
    <?php endif; ?>

    <nav class="navbar">
        <div class="nav-content">
            <ul class="nav-links">
                <li><a href="login.php">Log In</a></li>
                <li><a href="about.php">About Us</a></li>
                <li><a href="signup.php">Sign Up</a></li>
            </ul>
        </div>
    </nav>
    <div class="background">
        <img src="../photos/logo.png" class="logo" alt="Logo">
        <div class="login-container">
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