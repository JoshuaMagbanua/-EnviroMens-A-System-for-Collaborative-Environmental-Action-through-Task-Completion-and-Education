<?php
session_start();

require_once '../database/dbConnection.php';
require_once '../classes/UserManager.php';

$error = '';
$success = '';
$alertMessage = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $userManager = new UserManager($db);
        
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $age = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT);
        $gender = filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_STRING);
        $continent = filter_input(INPUT_POST, 'continent', FILTER_SANITIZE_STRING);
        $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
        $profile_picture = filter_input(INPUT_POST, 'profile_picture', FILTER_SANITIZE_STRING) ?? 'profile1.png';

        if (empty($username) || $age === false || empty($gender) || empty($continent) || empty($password)) {
            $alertMessage = [
                'icon' => 'error',
                'title' => 'Invalid Input',
                'text' => 'All fields are required!'
            ];
        } elseif ($age < 1 || $age > 120) {
            $alertMessage = [
                'icon' => 'error',
                'title' => 'Invalid Age',
                'text' => 'Please enter a valid age between 1 and 120'
            ];
        } else {
            try {
                $result = $userManager->createUser($username, $age, $gender, $continent, $password, $profile_picture);
                
                if ($result === "success") {
                    $alertMessage = [
                        'icon' => 'success',
                        'title' => 'Success!',
                        'text' => 'Account created successfully! Redirecting to login...',
                        'redirect' => true
                    ];
                } else {
                    $alertMessage = [
                        'icon' => 'error',
                        'title' => 'Oops...',
                        'text' => $result
                    ];
                }
            } catch (Exception $e) {
                $alertMessage = [
                    'icon' => 'error',
                    'title' => 'Error',
                    'text' => 'An unexpected error occurred. Please try again.'
                ];
            }
        }
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
                showConfirmButton: <?php echo isset($alertMessage['redirect']) ? 'false' : 'true' ?>,
                <?php if (isset($alertMessage['redirect'])): ?>
                timer: 2000
                <?php endif; ?>
            }).then(() => {
                <?php if (isset($alertMessage['redirect'])): ?>
                window.location.href = 'login.php';
                <?php endif; ?>
            });
        });
    </script>
    <?php endif; ?>

    <div class="background">
        <nav class="navbar">
            <div class="nav-content">
                <ul class="nav-links">
                    <li><a href="login.php">Log In</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="signup.php">Sign Up</a></li>
                </ul>
            </div>
        </nav>

        <div class="content-container">
            <div class="logo-container">
                <img src="../photos/logo.png" alt="Logo" class="logo">
            </div>
            
            <div class="signup-container">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                    <div class="form-group">
                        <input type="text" name="username" placeholder="Username" 
                               class="signup-input" required 
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
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
                        <label for="profile_picture">Choose your profile picture:</label>
                        <div class="profile-pictures-container">
                            <div class="profile-grid">
                                <?php 
                                $profilePictures = [
                                    1 => 'profile1.png',
                                    2 => 'profile2.png',
                                    3 => 'profile3.png',
                                    4 => 'profile4.png',
                                    5 => 'profile5.png',
                                    6 => 'profile6.png',
                                    7 => 'profile7.png',
                                    8 => 'profile8.png',
                                    9 => 'profile9.png'
                                ];
                                
                                foreach($profilePictures as $i => $file): ?>
                                    <div class="profile-item">
                                        <input type="radio" name="profile_picture" 
                                               id="profile<?php echo $i; ?>" 
                                               value="<?php echo htmlspecialchars($file); ?>" 
                                               <?php echo ($i === 1) ? 'checked' : ''; ?>>
                                        <label for="profile<?php echo $i; ?>">
                                            <img src="../profile_pictures/<?php echo htmlspecialchars($file); ?>" 
                                                 alt="Profile Picture <?php echo $i; ?>">
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="signup-button">Sign Up</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../js/profile-preview.js"></script>
</body>
</html> 