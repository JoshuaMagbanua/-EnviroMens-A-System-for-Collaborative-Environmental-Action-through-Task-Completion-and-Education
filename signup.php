<?php
session_start();

require_once '../database/dbConnection.php';
require_once '../classes/UserManager.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Create database connection
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $userManager = new UserManager($db);
        
        // Get and sanitize input data
        $fullname = trim($_POST['fullname']);
        $age = trim($_POST['age']);
        $gender = trim($_POST['gender']);
        $continent = trim($_POST['continent']);
        $password = trim($_POST['password']);

        // Validate input
        if (empty($fullname) || empty($age) || empty($gender) || empty($continent) || empty($password)) {
            $error = "All fields are required";
        } elseif (!is_numeric($age) || $age < 1 || $age > 120) {
            $error = "Invalid age";
        } else {
            // Attempt to create user
            $result = $userManager->createUser($fullname, $age, $gender, $continent, $password);
            
            if ($result === "success") {
                $success = "Account created successfully! You can now login.";
                // Optional: Redirect to login page after successful signup
                header("refresh:2;url=login.php");
            } else {
                $error = $result;
            }
        }
    } else {
        $error = "Database connection failed";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - EnviroMens</title>
    <link rel="stylesheet" href="../css/signup.css">
</head>
<body>
    <nav class="navbar">
        <ul>
            <li><a href="homepage.php">Home</a></li>
            <li><a href="about.php">About Us</a></li>
            <li><a href="login.php">Log In</a></li>
        </ul>
    </nav>

    <div class="background">
        <img src="../photos/logo.png" class="logo" alt="Logo">
        <div class="signup-container">
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div class="form-group">
                    <input type="text" name="fullname" placeholder="Full Name" 
                           class="signup-input" required 
                           value="<?php echo isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <input type="number" name="age" placeholder="Age" 
                           class="signup-input" required min="1" max="120"
                           value="<?php echo isset($_POST['age']) ? htmlspecialchars($_POST['age']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <select name="gender" class="signup-input" required>
                        <option value="" disabled <?php echo !isset($_POST['gender']) ? 'selected' : ''; ?>>
                            Select Gender
                        </option>
                        <option value="male" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'male') ? 'selected' : ''; ?>>
                            Male
                        </option>
                        <option value="female" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'female') ? 'selected' : ''; ?>>
                            Female
                        </option>
                        <option value="other" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'other') ? 'selected' : ''; ?>>
                            Other
                        </option>
                    </select>
                </div>
                
                <div class="form-group">
                    <select name="continent" class="signup-input" required>
                        <option value="" disabled <?php echo !isset($_POST['continent']) ? 'selected' : ''; ?>>
                            Select Continent
                        </option>
                        <option value="africa" <?php echo (isset($_POST['continent']) && $_POST['continent'] === 'africa') ? 'selected' : ''; ?>>
                            Africa
                        </option>
                        <option value="asia" <?php echo (isset($_POST['continent']) && $_POST['continent'] === 'asia') ? 'selected' : ''; ?>>
                            Asia
                        </option>
                        <option value="europe" <?php echo (isset($_POST['continent']) && $_POST['continent'] === 'europe') ? 'selected' : ''; ?>>
                            Europe
                        </option>
                        <option value="north_america" <?php echo (isset($_POST['continent']) && $_POST['continent'] === 'north_america') ? 'selected' : ''; ?>>
                            North America
                        </option>
                        <option value="south_america" <?php echo (isset($_POST['continent']) && $_POST['continent'] === 'south_america') ? 'selected' : ''; ?>>
                            South America
                        </option>
                        <option value="australia" <?php echo (isset($_POST['continent']) && $_POST['continent'] === 'australia') ? 'selected' : ''; ?>>
                            Australia/Oceania
                        </option>
                        <option value="antarctica" <?php echo (isset($_POST['continent']) && $_POST['continent'] === 'antarctica') ? 'selected' : ''; ?>>
                            Antarctica
                        </option>
                    </select>
                </div>
                
                <div class="form-group">
                    <input type="password" name="password" placeholder="Password" 
                           class="signup-input" required>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="signup-button">Sign Up</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 