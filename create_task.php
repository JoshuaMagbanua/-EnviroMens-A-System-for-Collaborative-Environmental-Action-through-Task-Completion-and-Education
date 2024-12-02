<?php
session_start();
require_once '../database/dbConnection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$conn = $database->getConnection();

// Get username from database
try {
    $query = "SELECT username FROM users WHERE user_id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":user_id", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $username = $result['username'] ?? 'User';
} catch(PDOException $e) {
    $username = 'User';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $task = trim($_POST['task']);
    $due_date = trim($_POST['task_due']);
    $task_leader = $_SESSION['user_id'];
    $cause = trim($_POST['cause']);
    $category = trim($_POST['category']);
    $points = trim($_POST['points']);

    try {
        $query = "INSERT INTO tasks (name, due_date, task_leader, cause, category, points) 
                 VALUES (:name, :due_date, :task_leader, :cause, :category, :points)";
        $stmt = $conn->prepare($query);
        
        $stmt->bindParam(":name", $task);
        $stmt->bindParam(":due_date", $due_date);
        $stmt->bindParam(":task_leader", $task_leader);
        $stmt->bindParam(":cause", $cause);
        $stmt->bindParam(":category", $category);
        $stmt->bindParam(":points", $points);

        if ($stmt->execute()) {
            header("Location: task_list.php?created=success");
            exit();
        }
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Task</title>
    <link rel="stylesheet" href="../css/create_task.css">
</head>
<body>
    <div class="container">
        <h1>Create Task</h1>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="task-form">
            <div class="form-group">
                <label for="task">Task:</label>
                <input type="text" id="task" name="task" required>
            </div>

            <div class="form-group">
                <label for="task_due">Task Due:</label>
                <input type="date" id="task_due" name="task_due" required>
            </div>

            <div class="form-group">
                <label for="task_leader">Task Leader:</label>
                <input type="text" id="task_leader" name="task_leader" value="<?php echo htmlspecialchars($username); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="cause">Cause:</label>
                <input type="text" id="cause" name="cause" required>
            </div>

            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category" required>
                    <option value="" disabled selected>Select Category</option>
                    <option value="air">Air</option>
                    <option value="water">Water</option>
                    <option value="land">Land</option>
                </select>
            </div>

            <div class="form-group">
                <label for="points">Points</label>
                <select id="points" name="points" required>
                    <option value="" disabled selected>Select Points</option>
                    <option value="30">30</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="200">200</option>
                </select>
            </div>

            <button type="submit" class="post-btn">Post</button>
        </form>
    </div>
</body>
</html> 