<?php
class User {
    private string $fullname;
    private int $age;
    private string $gender;
    private string $continent;
    private string $password;

    public function __construct(string $fullname, int $age, string $gender, string $continent, string $password) {
        $this->fullname = $fullname;
        $this->age = $age;
        $this->gender = $gender;
        $this->continent = $continent;
        $this->password = $password;
    }
}

class SignupValidator {
    private array $errors = [];

    public function validate(array $data): bool {
        $this->errors = [];

        if (empty($data['fullname']) || empty($data['age']) || 
            empty($data['gender']) || empty($data['continent']) || 
            empty($data['password'])) {
            $this->addError('All fields are required');
            return false;
        }

        if (!is_numeric($data['age']) || $data['age'] < 1 || $data['age'] > 120) {
            $this->addError('Invalid age');
            return false;
        }

        if (strlen($data['password']) < 6) {
            $this->addError('Password must be at least 6 characters long');
            return false;
        }

        $validGenders = ['male', 'female', 'other'];
        if (!in_array($data['gender'], $validGenders)) {
            $this->addError('Invalid gender selection');
            return false;
        }

        $validContinents = ['africa', 'asia', 'europe', 'north_america', 
                           'south_america', 'australia', 'antarctica'];
        if (!in_array($data['continent'], $validContinents)) {
            $this->addError('Invalid continent selection');
            return false;
        }

        return true;
    }

    public function addError(string $error): void {
        $this->errors[] = $error;
    }

    public function getErrors(): array {
        return $this->errors;
    }
}

class SignupProcessor {
    private SignupValidator $validator;
    private ?string $successMessage = null;

    public function __construct() {
        $this->validator = new SignupValidator();
    }

    public function processSignup(array $data): bool {
        if (!$this->validator->validate($data)) {
            return false;
        }

        
        $user = new User(
            trim($data['fullname']),
            (int)$data['age'],
            trim($data['gender']),
            trim($data['continent']),
            password_hash($data['password'], PASSWORD_DEFAULT)
        );

        $this->successMessage = 'Signup successful! You can now login.';
        return true;
    }

    public function getValidator(): SignupValidator {
        return $this->validator;
    }

    public function getSuccessMessage(): ?string {
        return $this->successMessage;
    }
}

$processor = new SignupProcessor();
$success = false;
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $success = $processor->processSignup($_POST);
    if (!$success) {
        $errors = $processor->getValidator()->getErrors();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Signup</title>
    <link rel="stylesheet" href="signup.css">
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
        <div class="signup-container">
            <?php if (!empty($errors)): ?>
                <?php foreach ($errors as $error): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message"><?php echo htmlspecialchars($processor->getSuccessMessage()); ?></div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div class="form-group">
                    <input type="text" name="fullname" placeholder="Full Name" class="signup-input" required>
                </div>
                
                <div class="form-group">
                    <input type="number" name="age" placeholder="Age" class="signup-input" required>
                </div>
                
                <div class="form-group">
                    <select name="gender" class="signup-input" required>
                        <option value="" disabled selected>Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <select name="continent" class="signup-input" required>
                        <option value="" disabled selected>Select Continent</option>
                        <option value="africa">Africa</option>
                        <option value="asia">Asia</option>
                        <option value="europe">Europe</option>
                        <option value="north_america">North America</option>
                        <option value="south_america">South America</option>
                        <option value="australia">Australia/Oceania</option>
                        <option value="antarctica">Antarctica</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <input type="password" name="password" placeholder="Password" class="signup-input" required>
                </div>
                
                <div class="form-group button-center">
                    <button type="submit" class="signup-button">Sign Up</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 