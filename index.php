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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@simonwep/pickr@1.8.1/dist/themes/classic.min.css" />
    <style>

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}
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
        background-color: rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        padding: 20px;
        min-height: auto;
        }

        .task {
            padding: 20px 10px 10px; 
            margin: 5px 0;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            cursor: pointer;
            position: relative;
            color: black;
        }

        .modal-content {
            background-color: #2a2a2a;
            border-radius: 10px;
            padding: 20px;
        }

        .modal-header {
            border-bottom: none;
        }

        .modal-title {
            color: #ffffff;
            font-weight: bold;
        }

        /* Form Styles */
        .form-label {
            color: #ffffff;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .form-control, .form-select {
            background-color: #3a3a3a;
            border: none;
            border-radius: 5px;
            color: #ffffff;
            padding: 10px;
        }

        .form-control:focus, .form-select:focus {
            background-color: #3a3a3a;
            box-shadow: 0 0 0 2px rgba(138, 180, 248, 0.5);
        }

        /* Date and Time Inputs */
        input[type="date"], input[type="time"] {
            color-scheme: dark;
        }

        /* File Upload Button */
        .btn-file {
            background-color: #3a3a3a;
            color: #ffffff;
            border: 1px solid #4a4a4a;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-file:hover {
            background-color: #4a4a4a;
        }

        /* Submit Button */
        .btn-primary {
            background-color: #7289da;
            border: none;
            border-radius: 5px;
            padding: 10px;
            width: 100%;
            font-weight: bold;
        }

        .btn-primary:hover {
            background-color: #5a6ebd;
        }

        /* Custom Styles for Specific Elements */
        #eventStatus {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23ffffff' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            padding-right: 30px;
        }

        #uploadBanner {
            display: none;
        }

        .file-upload-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }

        .file-upload-wrapper input[type=file] {
            font-size: 100px;
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
        }

        .file-upload-wrapper .btn-file {
            display: inline-block;
        }

        .btn-close{
           background-color:#ffffff;
            color: #ffffff;
        }

        .task-item {
        background-color: rgba(255, 255, 255, 0.05);
        border-left: 4px solid;
        border-radius: 4px;
        padding: 5px;
        margin-bottom: 10px;
        position: relative;
        cursor: default;
        color:#bfbcbb;
         }

        .task-btns {
        position: absolute;
        bottom: 5px;
        right: 5px;
        display: flex;
        justify-content: flex-end;
        }

        .btn-icon {
            background: none;
            border: none;
            color: red;
            padding: 5px;
            margin-left: 5px;
            cursor: pointer;
        }

        .btn-icon1 {
            background: none;
            border: none;
            color: green;
            padding: 5px;
            margin-left: 5px;
            cursor: pointer;
        }

        .btn-icon:hover {
            opacity: 0.8;
        }

        .edit-btn, .delete-btn {
            width: auto;
            height: auto;
            padding: 5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            margin-left: 10px;
            background-color: transparent;
        }

        h3 {
            color: white;
            font-size: 1.2em;
            margin-bottom: 10px;
            padding: 10px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }
        
        

        .modal-body{
        color:black;
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
        flex-direction: row;
        gap: 20px;
        }

        .task-column {
            flex: 1;
            min-width: 0;
        }
        @media (max-width: 1080px) {
        .task-columns {
            flex-direction: column;
        }

        .task-column {
            width: 100%;
            margin-bottom: 20px;
        }

        .sidebar {
            width: 100%;
            height: auto;
            justify-content: flex-end; /* Changed to flex-end to align with right side */
        }
        .sidebar:hover ~ .main-content {
            margin-left: 0px;
        }

        .main-content {
            margin-left: 0;
        }
        
    }
        .btn-icon, .btn-icon1 {
        font-size: 1.2rem; /* Memperbesar ukuran ikon */
        padding: 8px; /* Menambah padding untuk area klik yang lebih besar */
        }
        @media (max-width: 1080px) {
    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
        padding: 1rem;
        background-color: rgb(30, 41, 59);
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    
    .sidebar:hover {
        width: 100%;
        box-shadow: none;
    }
    
    .main-content {
        margin-left: 0;
        width: 100%;
        padding: 1rem;
    }
    
    body {
        flex-direction: column;
        overflow-x: hidden;
    }
    
    .sidebar-content {
        opacity: 1;
        visibility: visible;
        display: flex;
        flex-direction: column;
        width: 100%;
        margin: 0 auto;
        padding: 0;
    }
    
    .sidebar-top {
        display: flex;
        flex-direction: column;
        width: 100%;
        align-items: center;
        gap: 0.75rem;
    }
    
    /* Style for the menu title */
    .sidebar-top h3 {
        width: 100%;
        text-align: center;
        margin: 0;
        padding: 0.75rem;
        background-color: rgba(255, 255, 255, 0.1);
        border-radius: 4px;
    }
    
    /* Individual menu items */
    .sidebar-item {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.75rem;
        margin: 0;
        width: 100%;
        background-color: rgba(255, 255, 255, 0.05);
        border-radius: 4px;
        text-align: center;
        transition: background-color 0.3s ease;
    }
    
    .sidebar-item:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }
    
    .sidebar-item i {
        margin-right: 0.75rem;
    }
    
    .sidebar-item span {
        opacity: 1;
        transform: none;
        display: inline;
        margin: 0;
        flex: 0 0 auto;
        min-width: 80px; /* Ensure consistent text width */
        text-align: left;
    }
    
    .sidebar-bottom {
        width: 100%;
        margin-top: 0.75rem;
        display: flex;
        justify-content: center;
    }
}

/* Mobile devices */
@media (max-width: 576px) {
    .sidebar {
        padding: 0.75rem;
    }

    .sidebar-item {
        padding: 1rem;
        margin: 0.25rem 0;
    }
    
    .sidebar-item i {
        font-size: 1.5rem;
    }

    /* Task items */
    .task-item {
        padding: 1rem;
        padding-bottom: 60px;
        position: relative;
        margin-bottom: 1rem;
    }

    /* Task buttons container */
    .task-btns {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        padding: 0.5rem;
        background-color: rgba(255, 255, 255, 0.95);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    /* Button styles */
    .btn-icon,
    .btn-icon1 {
        padding: 0.5rem;
        margin: 0;
        flex: 0 0 auto;
    }

    /* Add touch-friendly sizing */
    .btn-icon,
    .btn-icon1,
    .sidebar-item {
        min-height: 44px;
        min-width: 44px;
    }
}

    .filter-container {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .col-md-6 {
        flex: 1;
    }

    #taskSearch, #statusFilter {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid #444;
        border-radius: 5px;
        background-color: white;
        color: black;
        font-size: 16px;
    }

    #taskSearch::placeholder {
        color: #666;
    }

    #statusFilter {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23000000' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        padding-right: 30px;
    }

    #statusFilter option {
        background-color: white;
        color: black;
    }

    @media (max-width: 768px) {
        .filter-container {
            flex-direction: column;
        }
    }

    .color-picker {
    margin-bottom: 20px;
}

.pickr {
    width: 100%;
}

.pickr button {
    width: 100%;
    border-radius: 8px;
    height: 40px;
    border: 2px solid #e0e0e0;
}

select.form-control {
    appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    background-size: 1em;
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

    <div class="row filter-container mb-2">
        <div class="col-md-6 p-2">
            <input type="text" id="taskSearch" class="form-controls" placeholder="Search tasks..." onkeyup="searchTasks()">
        </div>
        <div class="col-md-6 text-end p-2">
            <select id="statusFilter" class="form-selects" onchange="filterTasks()">
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
                                style="border-left-color: <?= htmlspecialchars($task["card_color"] ?? "#ffffff") ?>;" data-status="<?= $task["status"] ?>">
                                <strong><?= htmlspecialchars($task["title"]) ?></strong><br>
                                Due: <?= htmlspecialchars($task["due_date"]) ?>
                                 <div class="task-btns">
                                    <button class="btn btn-sm btn-icon1 edit-btn" onclick="editTask(<?= $task['id'] ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-icon delete-btn" onclick="deleteTask(<?= $task['id'] ?>)">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
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
                                style="border-left-color: <?= htmlspecialchars($task["card_color"] ?? "#ffffff") ?>;" data-status="<?= $task["status"] ?>">
                                <strong><?= htmlspecialchars($task["title"]) ?></strong><br>
                                Due: <?= htmlspecialchars($task["due_date"]) ?>
                                <div class="task-btns">
                                    <button class="btn btn-sm btn-icon1 edit-btn" onclick="editTask(<?= $task['id'] ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-icon delete-btn" onclick="deleteTask(<?= $task['id'] ?>)">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
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
                                style="border-left-color: <?= htmlspecialchars($task["card_color"] ?? "#ffffff") ?>;" data-status="<?= $task["status"] ?>">
                                <strong><?= htmlspecialchars($task["title"]) ?></strong><br>
                                Due: <?= htmlspecialchars($task["due_date"]) ?>
                                <div class="task-btns">
                                    <button class="btn btn-sm btn-icon1 edit-btn" onclick="editTask(<?= $task['id'] ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-icon delete-btn" onclick="deleteTask(<?= $task['id'] ?>)">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
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
                        <div class="mb-3 color-picker">
                        <label for="taskCardColor" class="form-label">Card Color</label>
                        <div id="taskCardColor"></div>
                        <input type="hidden" id="colorValue" name="card_color">
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

    <script src="https://cdn.jsdelivr.net/npm/@simonwep/pickr@1.8.1/dist/pickr.min.js"></script>
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

        const pickr = Pickr.create({
            el: '#taskCardColor',
            theme: 'classic',

            default: '<?= htmlspecialchars($task["card_color"] ?? "#ffffff") ?>',

            components: {
                preview: true,
                opacity: true,
                hue: true,

                interaction: {
                    hex: true,
                    rgba: true,
                    input: true,
                    save: true
                }
            }
        });

        pickr.on('change', (color) => {
            const hexColor = color.toHEXA().toString();
            document.getElementById('colorValue').value = hexColor;
        });
</script>
</body>
</html>
