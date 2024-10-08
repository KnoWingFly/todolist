<?php
session_start();
require "../config/db.php";

if (!isset($_SESSION["user_id"])) {
	header("Location: ../views/login.php");
	exit();
}

$list_id = $_GET["list_id"];

// Fetch tasks for the to-do list
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE list_id = :list_id");
$stmt->execute(["list_id" => $list_id]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tasks</title>
    <link rel="stylesheet" href="../public/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Tasks for the List</h2>
        
        <form action="../actions/search_tasks.php" method="GET">
            <div class="form-group">
                <label for="search">Search Tasks</label>
                <input type="text" class="form-control" id="search" name="query" placeholder="Search tasks...">
            </div>
            <input type="hidden" name="list_id" value="<?php echo $list_id; ?>">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>

        <form action="../actions/manage_tasks.php" method="POST">
            <div class="form-group">
                <label for="task_description">New Task</label>
                <input type="text" class="form-control" id="task_description" name="task_description" required>
            </div>
            <input type="hidden" name="list_id" value="<?php echo $list_id; ?>">
            <button type="submit" class="btn btn-primary">Add Task</button>
        </form

        <h3 class="mt-5">Existing Tasks</h3>
        <ul class="list-group mt-3">
            <?php foreach ($tasks as $task): ?>
                <li class="list-group-item">
                    <?php echo htmlspecialchars($task["task_description"]); ?>
                    <span class="badge badge-<?php echo $task["is_completed"]
                    	? "success"
                    	: "secondary"; ?>">
                        <?php echo $task["is_completed"]
                        	? "Completed"
                        	: "Incomplete"; ?>
                    </span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>
