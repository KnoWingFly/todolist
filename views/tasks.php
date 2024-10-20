<?php
// Memulai sesi hanya jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require "../config/db.php";

// Lanjutkan dengan sisa kode...
if (!isset($_SESSION["user_id"])) {
    header("Location: ../views/login.php");
    exit();
}

// Ambil list_id dari GET parameter
if (isset($_GET["list_id"])) {
    $list_id = $_GET["list_id"];
} else {
    die("Error: list_id is not set.");
}

// Ambil tugas untuk daftar to-do
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE list_id = :list_id");
$stmt->execute(["list_id" => $list_id]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tasks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #1A1A2E;
            color: #E0E0E0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .form-container {
            max-width: 700px;
            margin: 150px auto;
            background-color: #2E2E3A;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.6);
        }
        .form-control, .form-select {
            background-color: #3C3C4F;
            border: none;
            color: #E0E0E0;
            padding: 15px;
            font-size: 16px;
            height: 50px;
            border-radius: 10px;
        }
        .form-control:focus, .form-select:focus {
            background-color: #3C3C4F;
            color: #E0E0E0;
            box-shadow: 0 0 10px rgba(108, 99, 255, 0.8); /* Menambah shadow halus */
            border-color: #6C63FF;
        }
        .btn-custom {
            background-color: #6C63FF;
            color: white;
            font-size: 18px;
            padding: 12px;
            border-radius: 8px;
        }
        .btn-custom:hover {
            background-color: #5A54D8;
        }
        .list-group-item {
            background-color: #3C3C4F;
            color: #E0E0E0;
            font-size: 16px;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
        }
        .badge-success {
            background-color: #6C63FF;
        }
        .badge-secondary {
            background-color: #888;
        }
        h1 {
            font-size: 2.5rem;
            font-weight: 600;
        }
        label {
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1 class="text-center mb-4">Manage Tasks</h1>

            <!-- Search Form -->
            <form action="../actions/search_tasks.php" method="GET">
                <div class="mb-4">
                    <label for="search" class="form-label">Search Tasks</label>
                    <input type="text" class="form-control" id="search" name="query">
                </div>
                <input type="hidden" name="list_id" value="<?php echo $list_id; ?>">
                <button type="submit" class="btn btn-custom w-100 mb-4">Search</button>
            </form>

            <!-- Add Task Form -->
            <form action="../actions/manage_tasks.php" method="POST">
                <div class="mb-4">
                    <label for="task_description" class="form-label">New Task</label>
                    <input type="text" class="form-control" id="task_description" name="task_description" required>
                </div>
                <input type="hidden" name="list_id" value="<?php echo $list_id; ?>">
                <button type="submit" class="btn btn-custom w-100 mb-4">Add Task</button>
            </form>

            <!-- Existing Tasks -->
            <h3 class="mt-5">Existing Tasks</h3>
            <ul class="list-group mt-3">
                <?php foreach ($tasks as $task): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?php echo htmlspecialchars($task["task_description"]); ?>
                        <span class="badge badge-<?php echo $task["is_completed"] ? "success" : "secondary"; ?>">
                            <?php echo $task["is_completed"] ? "Completed" : "Incomplete"; ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</body>
</html>
