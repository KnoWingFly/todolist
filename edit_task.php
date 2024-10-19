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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@simonwep/pickr@1.8.1/dist/themes/classic.min.css" />
    <link rel="stylesheet" href="../node_modules/bootstrap/dist/css/bootstrap.min.css" />
    <style>
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    background-color: #1a1a1a;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
    margin: 0;
    color: #333;
}

.contact-container {
    background-color: #ffffff;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0px 8px 30px rgba(0, 0, 0, 0.2);
    width: 100%;
    max-width: 600px;
    margin: auto;
    min-width: 300px;
}

.form-wrapper {
    width: 100%;
}

.form-title {
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 25px;
    text-align: center;
    color: #1a1a1a;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #333;
}

.form-control {
    border-radius: 8px;
    padding: 12px 20px;
    font-size: 16px;
    width: 100%;
    border: 2px solid #e0e0e0;
    transition: all 0.3s ease;
    background-color: #f8f8f8;
    color: #333;
}

.form-control:focus {
    outline: none;
    border-color: #666;
    box-shadow: 0 0 0 2px rgba(102, 102, 102, 0.1);
    background-color: #ffffff;
}

.btn-submit {
    background-color: #333;
    border: none;
    padding: 12px 24px;
    color: white;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
    width: 100%;
    transition: background-color 0.3s ease;
    margin-bottom: 10px;
    font-weight: 500;
}

.btn-submit:hover {
    background-color: #1a1a1a;
}

.btn-secondary {
    background-color: #e0e0e0;
    border: none;
    padding: 12px 24px;
    color: #333;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
    width: 100%;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
    font-weight: 500;
}

.btn-secondary:hover {
    background-color: #d0d0d0;
    color: #1a1a1a;
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

@media (max-width: 768px) {
    body {
        padding: 15px;
    }
    
    .contact-container {
        padding: 20px;
    }
    
    .form-title {
        font-size: 20px;
        margin-bottom: 20px;
    }
    
    .form-control {
        padding: 10px 16px;
        font-size: 14px;
    }
    
    .btn-submit,
    .btn-secondary {
        padding: 10px 20px;
        font-size: 14px;
    }
}

@media (max-width: 480px) {
    body {
        padding: 10px;
    }
    
    .contact-container {
        padding: 15px;
        border-radius: 12px;
    }
    
    .form-title {
        font-size: 18px;
        margin-bottom: 15px;
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    .form-group label {
        font-size: 14px;
        margin-bottom: 6px;
    }
    
    .form-control {
        padding: 8px 14px;
        font-size: 13px;
    }
    
    .btn-submit,
    .btn-secondary {
        padding: 8px 16px;
        font-size: 13px;
    }
}

@media (hover: none) {
    .form-control,
    .btn-submit,
    .btn-secondary {
        -webkit-tap-highlight-color: transparent;
    }
    
    .form-control:focus {
        font-size: 16px;
    }
}

input[type="date"] {
    position: relative;
    background-color: #f8f8f8;
}

input[type="date"]::-webkit-calendar-picker-indicator {
    background-color: transparent;
    cursor: pointer;
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
}
    </style>
</head>
<body>

    <div class="contact-container">
        <div class="form-wrapper">
            <form method="POST" action="actions/update_task.php">
                <div class="form-title">Edit Task</div>

                <input type="hidden" name="task_id" value="<?= $task["id"] ?>">

                <div class="form-group mb-3">
                    <label for="taskTitle">Title</label>
                    <input type="text" class="form-control" id="taskTitle" name="taskTitle" value="<?= htmlspecialchars($task["title"]) ?>" required>
                </div>

                <div class="form-group mb-3">
                    <label for="dueDate">Due Date (dd/mm/yyyy)</label>
                    <input type="date" class="form-control" id="dueDate" name="dueDate" value="<?= htmlspecialchars($task["due_date"]) ?>" required>
                </div>

                <!-- Card Color Picker -->
                <div class="form-group mb-3 color-picker">
                    <label for="taskCardColor">Card Color</label>
                    <div id="taskCardColor"></div>
                    <input type="hidden" id="colorValue" name="card_color" value="<?= htmlspecialchars($task["card_color"] ?? "#ffffff") ?>">
                </div>

                <!-- Task Status Dropdown -->
                <div class="form-group mb-3">
                    <label for="taskStatus">Task Status</label>
                    <select id="taskStatus" name="status" class="form-control" required>
                        <option value="pending" <?= $task["status"] === "pending" ? "selected" : "" ?>>Pending</option>
                        <option value="in_progress" <?= $task["status"] === "in_progress" ? "selected" : "" ?>>In Progress</option>
                        <option value="completed" <?= $task["status"] === "completed" ? "selected" : "" ?>>Completed</option>
                    </select>
                </div>

                <!-- Submit and Cancel buttons -->
                <button type="submit" class="btn-submit">Save Changes</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@simonwep/pickr@1.8.1/dist/pickr.min.js"></script>
    
    <script>
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

    <script src="../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

