<?php
class TaskForm {
    private $categories = ['Work', 'Personal', 'Other'];
    private $pointValues = [10, 20, 30];

    public function render() {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Create Task</title>
            <link rel="stylesheet" href="createStyle.css">
        </head>
        <body>
            <h1>Create Task</h1>
            <form class="form-container" action="processTask.php" method="post">
                <?php $this->renderInputField('task', 'text', 'Task', true); ?>
                <?php $this->renderInputField('task_due', 'date', '', true); ?>
                <?php $this->renderInputField('task_leader', 'text', 'Task Leader', true); ?>
                <?php $this->renderInputField('cause', 'text', 'Cause', false); ?>
                <?php $this->renderCategorySelect(); ?>
                <?php $this->renderPointsSelect(); ?>
                <button align="center" type="submit">Post</button>
            </form>
        </body>
        </html>
        <?php
    }

    private function renderInputField($name, $type, $placeholder, $required) {
        $requiredAttr = $required ? 'required' : '';
        echo "<input type=\"$type\" name=\"$name\" placeholder=\"$placeholder\" $requiredAttr>";
    }

    private function renderCategorySelect() {
        echo '<select name="category" required>';
        echo '<option value="">Select Category</option>';
        foreach ($this->categories as $category) {
            echo "<option value=\"$category\">$category</option>";
        }
        echo '</select>';
    }

    private function renderPointsSelect() {
        echo '<select name="points" required>';
        echo '<option value="">Select Points</option>';
        foreach ($this->pointValues as $points) {
            echo "<option value=\"$points\">$points</option>";
        }
        echo '</select>';
    }
}

$taskForm = new TaskForm();
$taskForm->render();
?>