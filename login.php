<?php
session_start(); // Start session for login persistence

class LoginValidator {
    private array $errors = [];

    public function validate(array $data): bool {
        $this->errors = [];

        if (empty($data['fullname']) || empty($data['password'])) {
            $this->errors[] = 'All fields are required.';
            return false;
        }

        // Hardcoded credentials for trial
        $validUsername = 'user';
        $validPassword = '12345678';

        // Validate username
        if ($data['fullname'] !== $validUsername) {
            $this->errors[] = 'Invalid username.';
            return false;
        }

        // Validate password
        if ($data['password'] !== $validPassword) {
            $this->errors[] = 'Invalid password.';
            return false;
        }

        return true;
    }

    public function getErrors(): array {
        return $this->errors;
    }
}

class LoginProcessor {
    private LoginValidator $validator;

    public function __construct() {
        $this->validator = new LoginValidator();
    }

    public function processLogin(array $data): bool {
        if (!$this->validator->validate($data)) {
            return false;
        }

        // Set session variables on successful login
        $_SESSION['loggedIn'] = true;
        $_SESSION['username'] = $data['fullname'];

        return true;
    }

    public function getValidator(): LoginValidator {
        return $this->validator;
    }
}

// Login handling logic
$processor = new LoginProcessor();
$success = false;
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $success = $processor->processLogin($_POST);
    if (!$success) {
        $errors = $processor->getValidator()->getErrors();
    } else {
        // Redirect to homepage on success
        header("Location: homepage.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login</title>
    <link rel="stylesheet" href="login.css">
    <style>
        .button-center {
            display: flex;
            justify-content: center;
            width: 100%;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <ul>
            <li><a href="homepage.php">Home</a></li>
            <li><a href="#">About Us</a></li>
        </ul>
    </nav>
    <div class="background">
        <img src="logo.png" class="logo" alt="Logo">
        <div class="login-container">
            <?php if (!empty($errors)): ?>
                <?php foreach ($errors as $error): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div class="form-group">
                    <input type="text" name="fullname" placeholder="Username" class="login-input" required>
                </div>
                
                <div class="form-group">
                    <input type="password" name="password" placeholder="Password" class="login-input" required>
                </div>
                
                <div class="form-group button-center">
                    <button type="submit" class="login-button">Log In</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
