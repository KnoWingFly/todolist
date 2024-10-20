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
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            font-family: system-ui;
        }
        
        h1, h3 {
            color: white;
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

        /* Modal background color for visibility */
        .modal-content {
            background-color: #f8f9fa;
            color: black;
        }

        .logout-btn {
            margin-top: 20px;
        }
        * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* reset button */
button {
    appearance: none;
    background-color: transparent;
    border: none;
    cursor: pointer;
    outline: none;
    padding: 0;
    margin: 0;
    font-family: inherit;
    font-size: inherit;
    color: inherit;
    text-decoration: none;
    text-transform: none;
    line-height: normal;
    overflow: visible;
}

body {
    min-height: 100svh;
    background-color: rgb(15, 23, 42);
    color: white;
    display: grid;
    place-content: center;
    font-size: 1rem;
    font-family: system-ui;
}

nav {
    --_clr-txt: rgb(255, 255, 255);
    --_clr-txt-svg: rgb(147, 158, 184);
    --_ani-speed: 6s; /* speed of rotating text */
    display: flex;
    gap: 1rem;
    font-size: 1.4rem;
}

nav > button {
    position: relative;
    display: grid;
    place-content: center;
    grid-template-areas: 'stack';
    padding: 0 1.5rem;
    text-transform: uppercase;
    font-weight: 300;
}

/* place button items on top of each other */
nav > button > span {
    transition: all 300ms ease-in-out;
    grid-area: stack;
}

/* nav icon */
nav > button > span:last-of-type {
    margin-top: 0.25rem;
    transform: scale(0);
    transition-delay: 0ms;
    border-radius: 50%;
}

/* hover - hide text */
nav > button:focus-visible > span:first-of-type,
nav > button:hover > span:first-of-type {
    transform: scale(0);
}

/* hover - reveal icon */
nav > button:focus-visible > span:last-of-type,
nav > button:hover > span:last-of-type {
    transform: scale(1);
}

/* nav SVG circular text */
nav > button > svg {
    position: absolute;
    width: 200px;
    height: 200px;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    transform-origin: center;
    opacity: 0;
    text-transform: uppercase;
    transition: all 300ms ease-in-out;
    color: var(--_clr-txt-svg);
}

/* hover - reveal rotating SVG */
nav > button:focus-visible > svg,
nav > button:hover > svg {
    transform: translate(-50%, -50%) scale(1);
    opacity: 1;
    transition-delay: 150ms;
}

/* rotating SVG text */
button svg g {
    transform-origin: center;
    animation: rotate var(--_ani-speed) linear infinite;
}

@keyframes rotate {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

    </style>
</head>
<body>

    <div class="container mt-5">
        <h1 class="text-center">Welcome to Your To-Do List</h1>

        <nav class="d-flex justify-content-center">
        <button type="button" class="nav-button btn btn-transparent" title="Profile">
            <span>Profile</span>
            <span class="material-symbols-outlined" aria-hidden="true"></span>
            <svg viewBox="0 0 300 300" aria-hidden="true">
                <g>
                    <text fill="currentColor">
                        <textPath xlink:href="#circlePath">Profile</textPath>
                    </text>
                    <text fill="currentColor">
                        <textPath xlink:href="#circlePath" startOffset="50%">Profile</textPath>
                    </text>
                </g>
            </svg>
        </button>
        <button type="button" class="nav-button btn btn-transparent" title="Log Out">
            <span>Log Out</span>
            <span class="material-symbols-outlined" aria-hidden="true"></span>
            <svg viewBox="0 0 300 300" aria-hidden="true">
                <g>
                    <text fill="currentColor">
                        <textPath xlink:href="#circlePath">Log Out</textPath>
                    </text>
                    <text fill="currentColor">
                        <textPath xlink:href="#circlePath" startOffset="50%">Log Out</textPath>
                    </text>
                </g>
            </svg>
        </button>
    </nav>

    <!-- SVG template with dynamic text -->
    <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 300 300" width="0" height="0">
        <defs>
            <path id="circlePath" d="M 150, 150 m -50, 0 a 50,50 0 0,1 100,0 a 50,50 0 0,1 -100,0" />
        </defs>
    </svg>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


        <!-- Task Search and Filter Section -->
        <div class="row filter-container mb-4">
            <!-- Task Search Bar -->
            <div class="col-md-6">
                <input type="text" id="taskSearch" class="form-control" placeholder="Search tasks..." onkeyup="searchTasks()">
            </div>

            <!-- Task Status Filter -->
            <div class="col-md-6 text-end">
                <select id="statusFilter" class="form-select w-50 d-inline-block" onchange="filterTasks()">
                    <option value="">All Tasks</option>
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
        </div>

        <!-- Add Task Form -->
        <div class="row">
            <div class="col-md-4">
                <h3 class="text-center">Add a Task</h3>
                <form id="taskForm" method="POST" action="actions/add_task.php">
                    <div class="form-group mb-2">
                        <label for="taskTitle">Title</label>
                        <input type="text" class="form-control" id="taskTitle" name="taskTitle" required>
                    </div>
                    <div class="form-group mb-2">
                        <label for="dueDate">Due Date (dd/mm/yyyy)</label>
                        <input type="date" class="form-control" id="dueDate" name="dueDate" required>
                    </div>

                    <!-- Card Color Picker -->
                    <div class="form-group mb-2">
                        <label for="taskColor">Card Color</label>
                        <input type="color" class="form-control" id="taskColor" name="card_color" value="#ffffff">
                    </div>

                    <!-- Dropdown for task status -->
                    <div class="form-group mb-2">
                        <label for="taskStatus">Task Status</label>
                        <select id="taskStatus" name="status" class="form-control" required>
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Add Task</button>
                </form>
            </div>
        </div>

        <!-- Task Columns -->
        <div class="row mt-5">
            <!-- Pending Tasks Column -->
            <div class="col-md-4">
                <h3 class="text-center">Pending</h3>
                <div class="task-container" id="pending" ondrop="drop(event)" ondragover="allowDrop(event)">
                    <?php if (!empty($pendingTasks)): ?>
                        <?php foreach ($pendingTasks as $task): ?>
                            <div class="task task-item" id="task-<?= $task[
                            	"id"
                            ] ?>" draggable="true" ondragstart="drag(event)" 
                                style="background-color: <?= htmlspecialchars(
                                	$task["card_color"] ?? "#ffffff"
                                ) ?>;" data-status="<?= $task["status"] ?>">
                                <strong><?= htmlspecialchars(
                                	$task["title"]
                                ) ?></strong><br>
                                Due: <?= htmlspecialchars($task["due_date"]) ?>
                                <div class="task-btns">
                                    <button class="btn btn-sm btn-warning" onclick="editTask(<?= $task[
                                    	"id"
                                    ] ?>)">Edit</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteTask(<?= $task[
                                    	"id"
                                    ] ?>)">Delete</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No pending tasks available.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- In Progress Tasks Column -->
            <div class="col-md-4">
                <h3 class="text-center">In Progress</h3>
                <div class="task-container" id="in_progress" ondrop="drop(event)" ondragover="allowDrop(event)">
                    <?php if (!empty($inProgressTasks)): ?>
                        <?php foreach ($inProgressTasks as $task): ?>
                            <div class="task task-item" id="task-<?= $task[
                            	"id"
                            ] ?>" draggable="true" ondragstart="drag(event)" 
                                style="background-color: <?= htmlspecialchars(
                                	$task["card_color"] ?? "#ffffff"
                                ) ?>;" data-status="<?= $task["status"] ?>">
                                <strong><?= htmlspecialchars(
                                	$task["title"]
                                ) ?></strong><br>
                                Due: <?= htmlspecialchars($task["due_date"]) ?>
                                <div class="task-btns">
                                    <button class="btn btn-sm btn-warning" onclick="editTask(<?= $task[
                                    	"id"
                                    ] ?>)">Edit</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteTask(<?= $task[
                                    	"id"
                                    ] ?>)">Delete</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No in-progress tasks available.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Completed Tasks Column -->
            <div class="col-md-4">
                <h3 class="text-center">Completed</h3>
                <div class="task-container" id="completed" ondrop="drop(event)" ondragover="allowDrop(event)">
                    <?php if (!empty($completedTasks)): ?>
                        <?php foreach ($completedTasks as $task): ?>
                            <div class="task task-item" id="task-<?= $task[
                            	"id"
                            ] ?>" draggable="true" ondragstart="drag(event)" 
                                style="background-color: <?= htmlspecialchars(
                                	$task["card_color"] ?? "#ffffff"
                                ) ?>;" data-status="<?= $task["status"] ?>">
                                <strong><?= htmlspecialchars(
                                	$task["title"]
                                ) ?></strong><br>
                                Due: <?= htmlspecialchars($task["due_date"]) ?>
                                <div class="task-btns">
                                    <button class="btn btn-sm btn-warning" onclick="editTask(<?= $task[
                                    	"id"
                                    ] ?>)">Edit</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteTask(<?= $task[
                                    	"id"
                                    ] ?>)">Delete</button>
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

    <!-- Modal for viewing task details -->
    <div class="modal fade" id="taskDetailsModal" tabindex="-1" aria-labelledby="taskDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="taskDetailsModalLabel">Task Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Task details will be loaded here via AJAX -->
                    <div id="taskDetailsContent"></div>
                    <!-- Edit/Delete buttons in the modal -->
                    <div class="text-end mt-3">
                        <button class="btn btn-sm btn-warning" id="editModalBtn">Edit</button>
                        <button class="btn btn-sm btn-danger" id="deleteModalBtn">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle drag and drop functionality
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

            // Send an AJAX request to update the task status in the database
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

        // Task Search Functionality
        function searchTasks() {
            const searchTerm = document.getElementById('taskSearch').value.toLowerCase();
            const tasks = document.querySelectorAll('.task-item');
            
            tasks.forEach(task => {
                const taskTitle = task.querySelector('strong').textContent.toLowerCase();
                if (taskTitle.includes(searchTerm)) {
                    task.style.display = 'block';
                } else {
                    task.style.display = 'none';
                }
            });
        }

        // Task Filter Functionality
        function filterTasks() {
            const selectedStatus = document.getElementById('statusFilter').value;
            const tasks = document.querySelectorAll('.task-item');
            
            tasks.forEach(task => {
                const taskStatus = task.getAttribute('data-status');
                if (selectedStatus === '' || taskStatus === selectedStatus) {
                    task.style.display = 'block';
                } else {
                    task.style.display = 'none';
                }
            });
        }

        // View task details
        function viewTaskDetails(taskId) {
            const xhr = new XMLHttpRequest();
            xhr.open("GET", `actions/get_task_details.php?task_id=${taskId}`, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    document.getElementById('taskDetailsContent').innerHTML = xhr.responseText;

                    // Set up edit and delete buttons in modal
                    document.getElementById('editModalBtn').onclick = function() {
                        editTask(taskId);
                    };
                    document.getElementById('deleteModalBtn').onclick = function() {
                        deleteTask(taskId);
                    };

                    const modal = new bootstrap.Modal(document.getElementById('taskDetailsModal'));
                    modal.show();
                }
            };
            xhr.send();
        }

        // Edit task
        function editTask(taskId) {
            window.location.href = `edit_task.php?task_id=${taskId}`;
        }

        // Delete task
        function deleteTask(taskId) {
            if (confirm("Are you sure you want to delete this task?")) {
                const xhr = new XMLHttpRequest();
                xhr.open("POST", "actions/delete_task.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        window.location.reload();
                    }
                };
                xhr.send(`task_id=${taskId}`);
            }
        }
    </script>
</body>
</html>
