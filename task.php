<?php
class Task {
    private $task;
    private $task_due;
    private $task_leader;
    private $cause;
    private $category;
    private $points;
    private $db_conn;

    public function __construct($task, $task_due, $task_leader, $cause, $category, $points, $db_conn) {
        $this->task = $task;
        $this->task_due = $task_due;
        $this->task_leader = $task_leader;
        $this->cause = $cause;
        $this->category = $category;
        $this->points = $points;
        $this->db_conn = $db_conn;
    }

    public function saveToDatabase() {
        $sql = "INSERT INTO tasks (task, task_due, task_leader, cause, category, points) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db_conn->prepare($sql);
        // Fix: Change bind_param types to match points as integer
        $stmt->bind_param("sssssi", $this->task, $this->task_due, $this->task_leader, $this->cause, $this->category, $this->points);

        if ($stmt->execute()) {
            // Add header redirect instead of echo
            header("Location: processTask.php");
            exit();
        } else {
            // Add proper error handling
            throw new Exception("Error saving task: " . $stmt->error);
        }

        $stmt->close();
    }
}
?>