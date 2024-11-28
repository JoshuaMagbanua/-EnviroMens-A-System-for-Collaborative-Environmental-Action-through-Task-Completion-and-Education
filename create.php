<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Task</title>
    <link rel="stylesheet" href="create_style.css"> <!-- Link to external CSS -->
</head>
<body>
    <h1>Create Task</h1>
    <form class="form-container" action="process_task.php" method="post">
        <input type="text" name="task" placeholder="Task" required>
        <input type="date" name="task_due" required>
        <input type="text" name="task_leader" placeholder="Task Leader" required>
        <input type="text" name="cause" placeholder="Cause">
        <select name="category" required>
            <option value="">Select Category</option>
            <option value="Work">Work</option>
            <option value="Personal">Personal</option>
            <option value="Other">Other</option>
        </select>
        <select name="points" required>
            <option value="">Select Points</option>
            <option value="10">10</option>
            <option value="20">20</option>
            <option value="30">30</option>
        </select>
        <button align="center" type="submit">Post</button>
    </form>
</body>
</html>