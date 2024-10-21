<?php
// Start session
session_start();
require "config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: views/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Fetch all lists for the logged-in user
$listsStmt = $pdo->prepare(
    "SELECT id, title FROM todo_lists WHERE user_id = :user_id"
);
$listsStmt->execute(["user_id" => $user_id]);
$lists = $listsStmt->fetchAll(PDO::FETCH_ASSOC);

// If user has no lists, create a default one
if (empty($lists)) {
    $defaultListStmt = $pdo->prepare(
        "INSERT INTO todo_lists (user_id, title) VALUES (:user_id, 'Default List')"
    );
    $defaultListStmt->execute(["user_id" => $user_id]);
    $defaultListId = $pdo->lastInsertId();
    $lists[] = ["id" => $defaultListId, "title" => "Default List"];
}

// Fetch tasks for the first list (default list for user)
$currentListId = $lists[0]["id"];

$tasksStmt = $pdo->prepare("
    SELECT * FROM tasks 
    WHERE list_id = :list_id 
    ORDER BY status, due_date
");
$tasksStmt->execute(["list_id" => $currentListId]);
$tasks = $tasksStmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize task groups
$pendingTasks = [];
$inProgressTasks = [];
$completedTasks = [];

if (!empty($tasks)) {
    $pendingTasks = array_filter($tasks, function ($task) {
        return $task["status"] === "pending";
    });
    $inProgressTasks = array_filter($tasks, function ($task) {
        return $task["status"] === "in_progress";
    });
    $completedTasks = array_filter($tasks, function ($task) {
        return $task["status"] === "completed";
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User-Specific To-Do List</title>
    <link rel="stylesheet" href="../node_modules/bootstrap/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <style>
        body {
            min-height: 100vh;
            background-color: rgb(15, 23, 42);
            color: white;
            font-size: 1rem;
            font-family: system-ui;
            display: flex;
        }

        h1, h3 {
            color: white;
        }

        .sidebar {
            width: 60px;
            background-color: rgb(30, 41, 59);
            transition: width 0.3s ease;
            overflow: hidden;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
        }

        .sidebar:hover {
            width: 250px;
        }

        .sidebar-content {
            flex-grow: 1;
            width: 250px;
            opacity: 0;
            transition: opacity 0.3s ease;
            padding-top: 20px;
        }

        .sidebar:hover .sidebar-content {
            opacity: 1;
        }

        .sidebar-item {
            padding: 10px 15px;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            white-space: nowrap;
            transition: all 0.3s ease;
        }

        .sidebar-item i {
            margin-right: 15px;
            font-size: 24px;
            transition: transform 0.3s ease;
        }

        .sidebar-item span {
            opacity: 0;
            transform: translateX(-20px);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .sidebar:hover .sidebar-item span {
            opacity: 1;
            transform: translateX(0);
        }

        .sidebar-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .main-content {
            flex-grow: 1;
            margin-left: 60px;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }

        .sidebar:hover ~ .main-content {
            margin-left: 250px;
        }

        .task-container {
            min-height: 300px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .task {
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            position: relative;
            color: black;
        }

        .task-content {
            margin-bottom: 30px; /* Space for buttons */
        }

        .task-btns {
            position: absolute;
            bottom: 5px;
            right: 5px;
        }

        @media (max-width: 576px) {
            .task {
                padding-bottom: 40px; /* Increase padding to accommodate buttons */
            }

            .task-content {
                margin-bottom: 0;
            }

            .task-btns {
                position: absolute;
                bottom: 5px;
                left: 10px;
                right: 10px;
                display: flex;
                justify-content: space-between;
            }

            .task-btns button {
                flex: 1;
                margin: 0 2px;
            }
        }
        
        .task-columns {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .task-column {
            flex: 1;
            min-width: 0;
        }

        @media (min-width: 768px) {
            .task-columns {
                flex-direction: row;
            }
        }

        .filter-container {
            margin-bottom: 20px;
        }

        .btn-danger {
            margin-left: 5px;
        }

        .task-btns {
            position: absolute;
            bottom: 5px;
            right: 5px;
        }

        .modal-content {
            background-color: #f8f9fa;
            color: black;
        }

        @media (max-width: 767px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .sidebar:hover {
                width: 100%;
            }

            .main-content {
                margin-left: 0;
            }

            body {
                flex-direction: column;
            }

            .sidebar-content {
                opacity: 1;
            }

            .sidebar-item span {
                opacity: 1;
                transform: translateX(0);
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-content">
            <div class="sidebar-top">
                <h3 class="text-center mb-4">Menu</h3>
                <a href="views/profile.php" class="sidebar-item"><i class="material-icons">person</i><span>Profile</span></a>
                <a href="#" class="sidebar-item" data-bs-toggle="modal" data-bs-target="#addTaskModal"><i class="material-icons">add</i><span>Add Task</span></a>
            </div>
        </div>
        <div class="sidebar-bottom">
            <a href="/logout.php" class="sidebar-item"><i class="material-icons">exit_to_app</i><span>Logout</span></a>
        </div>
    </div>

<div class="main-content">
    <h1 class="text-center mb-4">Welcome to Your To-Do List</h1>

    <div class="row filter-container mb-4">
        <div class="col-md-6">
            <input type="text" id="taskSearch" class="form-control" placeholder="Search tasks..." onkeyup="searchTasks()">
        </div>
        <div class="col-md-6 text-end">
            <select id="statusFilter" class="form-select w-50 d-inline-block" onchange="filterTasks()">
                <option value="">All Tasks</option>
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
            </select>
        </div>
    </div>

    <div class="task-columns">
        <div class="task-column">
            <h3 class="text-center">Pending</h3>
            <div class="task-container" id="pending" ondrop="drop(event)" ondragover="allowDrop(event)">
            <?php if (!empty($pendingTasks)): ?>
                        <?php foreach ($pendingTasks as $task): ?>
                            <div class="task task-item" id="task-<?= $task["id"] ?>" draggable="true" ondragstart="drag(event)" 
                                style="background-color: <?= htmlspecialchars($task["card_color"] ?? "#ffffff") ?>;" data-status="<?= $task["status"] ?>">
                                <strong><?= htmlspecialchars($task["title"]) ?></strong><br>
                                Due: <?= htmlspecialchars($task["due_date"]) ?>
                                <div class="task-btns">
                                    <button class="btn btn-sm btn-warning" onclick="editTask(<?= $task["id"] ?>)">Edit</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteTask(<?= $task["id"] ?>)">Delete</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No pending tasks available.</p>
                    <?php endif; ?>
                </div>
            </div>


        <div class="task-column">
            <h3 class="text-center">In Progress</h3>
            <div class="task-container" id="in_progress" ondrop="drop(event)" ondragover="allowDrop(event)">
            <?php if (!empty($inProgressTasks)): ?>
                        <?php foreach ($inProgressTasks as $task): ?>
                            <div class="task task-item" id="task-<?= $task["id"] ?>" draggable="true" ondragstart="drag(event)" 
                                style="background-color: <?= htmlspecialchars($task["card_color"] ?? "#ffffff") ?>;" data-status="<?= $task["status"] ?>">
                                <strong><?= htmlspecialchars($task["title"]) ?></strong><br>
                                Due: <?= htmlspecialchars($task["due_date"]) ?>
                                <div class="task-btns">
                                    <button class="btn btn-sm btn-warning" onclick="editTask(<?= $task["id"] ?>)">Edit</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteTask(<?= $task["id"] ?>)">Delete</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No in-progress tasks available.</p>
                    <?php endif; ?>
                </div>
            </div>

        <div class="task-column">
            <h3 class="text-center">Completed</h3>
            <div class="task-container" id="completed" ondrop="drop(event)" ondragover="allowDrop(event)">
            <?php if (!empty($completedTasks)): ?>
                        <?php foreach ($completedTasks as $task): ?>
                            <div class="task task-item" id="task-<?= $task["id"] ?>" draggable="true" ondragstart="drag(event)" 
                                style="background-color: <?= htmlspecialchars($task["card_color"] ?? "#ffffff") ?>;" data-status="<?= $task["status"] ?>">
                                <strong><?= htmlspecialchars($task["title"]) ?></strong><br>
                                Due: <?= htmlspecialchars($task["due_date"]) ?>
                                <div class="task-btns">
                                    <button class="btn btn-sm btn-warning" onclick="editTask(<?= $task["id"] ?>)">Edit</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteTask(<?= $task["id"] ?>)">Delete</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No completed tasks available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Task Modal -->
    <div class="modal fade" id="addTaskModal" tabindex="-1" aria-labelledby="addTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addTaskModalLabel">Add a Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="taskForm" method="POST" action="actions/add_task.php">
                        <div class="mb-3">
                            <label for="taskTitle" class="form-label">Title</label>
                            <input type="text" class="form-control" id="taskTitle" name="taskTitle" required>
                        </div>
                        <div class="mb-3">
                            <label for="dueDate" class="form-label">Due Date</label>
                            <input type="date" class="form-control" id="dueDate" name="dueDate" required>
                        </div>
                        <div class="mb-3">
                            <label for="taskColor" class="form-label">Card Color</label>
                            <input type="color" class="form-control" id="taskColor" name="card_color" value="#ffffff">
                        </div>
                        <div class="mb-3">
                            <label for="taskStatus" class="form-label">Task Status</label>
                            <select id="taskStatus" name="status" class="form-select" required>
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Task</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


<script src="../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function allowDrop(event) {
        event.preventDefault();
    }

    function drag(event) {
        event.dataTransfer.setData("text", event.target.id);
    }

    function drop(event) {
        event.preventDefault();
        const taskId = event.dataTransfer.getData("text");
        const targetStatus = event.target.id;

        updateTaskStatus(taskId.replace('task-', ''), targetStatus);
    }

    function updateTaskStatus(taskId, newStatus) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "actions/update_task_status.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                window.location.reload();
            }
        };
        xhr.send(`task_id=${taskId}&new_status=${newStatus}`);
    }

    function searchTasks() {
        const searchTerm = document.getElementById('taskSearch').value.toLowerCase();
        const tasks = document.querySelectorAll('.task-item');
        tasks.forEach(task => {
            const taskTitle = task.querySelector('strong').textContent.toLowerCase();
            task.style.display = taskTitle.includes(searchTerm) ? 'block' : 'none';
        });
    }

    function filterTasks() {
        const selectedStatus = document.getElementById('statusFilter').value;
        const tasks = document.querySelectorAll('.task-item');
        tasks.forEach(task => {
            const taskStatus = task.getAttribute('data-status');
            task.style.display = (selectedStatus === '' || taskStatus === selectedStatus) ? 'block' : 'none';
        });
    }
</script>
</body>
</html>
