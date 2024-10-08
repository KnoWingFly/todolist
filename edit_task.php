<?php
session_start();
require "config/db.php";

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
	header("Location: views/login.php");
	exit();
}

$user_id = $_SESSION["user_id"];

// Check if task_id is passed in URL
if (!isset($_GET["task_id"])) {
	echo "No task selected for editing.";
	exit();
}

$task_id = $_GET["task_id"];

// Fetch the task details for the given task_id
$stmt = $pdo->prepare(
	"SELECT * FROM tasks WHERE id = :task_id AND EXISTS (SELECT 1 FROM todo_lists WHERE id = tasks.list_id AND user_id = :user_id)"
);
$stmt->execute(["task_id" => $task_id, "user_id" => $user_id]);
$task = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$task) {
	echo "Task not found or you don't have permission to edit this task.";
	exit();
}

// Fetch distinct labels for the user to reuse
$labelStmt = $pdo->prepare("
    SELECT DISTINCT label 
    FROM tasks 
    WHERE EXISTS (SELECT 1 FROM todo_lists WHERE id = tasks.list_id AND user_id = :user_id)
    AND label IS NOT NULL
");
$labelStmt->execute(["user_id" => $user_id]);
$labels = $labelStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task</title>
    <link rel="stylesheet" href="../node_modules/bootstrap/dist/css/bootstrap.min.css" />
</head>
<body>

    <div class="container mt-5">
        <h1>Edit Task</h1>

        <!-- Edit Task Form -->
        <form method="POST" action="actions/update_task.php">
            <input type="hidden" name="task_id" value="<?= $task["id"] ?>">

            <div class="form-group mb-3">
                <label for="taskTitle">Title</label>
                <input type="text" class="form-control" id="taskTitle" name="taskTitle" value="<?= htmlspecialchars(
                	$task["title"]
                ) ?>" required>
            </div>

            <div class="form-group mb-3">
                <label for="dueDate">Due Date (dd/mm/yyyy)</label>
                <input type="date" class="form-control" id="dueDate" name="dueDate" value="<?= htmlspecialchars(
                	$task["due_date"]
                ) ?>" required>
            </div>

            <!-- Card Color Picker -->
            <div class="form-group mb-3">
                <label for="taskCardColor">Card Color</label>
                <input type="color" class="form-control" id="taskCardColor" name="card_color" value="<?= htmlspecialchars(
                	$task["card_color"] ?? "#ffffff"
                ) ?>">
            </div>

            <!-- Label field with option to reuse labels -->
            <!-- <div class="form-group mb-3">
                <label for="taskLabel">Label</label>
                <input type="text" class="form-control" id="taskLabel" name="taskLabel" value="<?= htmlspecialchars(
                	$task["label"]
                ) ?>" placeholder="e.g. Urgent, Work">
            </div> -->

            <!-- Dropdown for reusing labels -->
            <!-- <?php if (!empty($labels)): ?>
                <div class="form-group mb-3">
                    <label for="existingLabels">Reuse Existing Label</label>
                    <select id="existingLabels" class="form-control" onchange="reuseLabel()">
                        <option value="">Choose an existing label</option>
                        <?php foreach ($labels as $label): ?>
                            <option value="<?= htmlspecialchars(
                            	$label["label"]
                            ) ?>">
                                <?= htmlspecialchars($label["label"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?> -->

            <!-- Task Status Dropdown -->
            <div class="form-group mb-3">
                <label for="taskStatus">Task Status</label>
                <select id="taskStatus" name="status" class="form-control" required>
                    <option value="pending" <?= $task["status"] === "pending"
                    	? "selected"
                    	: "" ?>>Pending</option>
                    <option value="in_progress" <?= $task["status"] ===
                    "in_progress"
                    	? "selected"
                    	: "" ?>>In Progress</option>
                    <option value="completed" <?= $task["status"] ===
                    "completed"
                    	? "selected"
                    	: "" ?>>Completed</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <script src="../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Reuse an existing label
        function reuseLabel() {
            const select = document.getElementById('existingLabels');
            const label = select.options[select.selectedIndex].value;

            if (label) {
                document.getElementById('taskLabel').value = label;
            } else {
                document.getElementById('taskLabel').value = '';
            }
        }
    </script>
</body>
</html>
