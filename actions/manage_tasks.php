<?php
session_start();
require "../config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../views/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $task_description = htmlspecialchars($_POST["task_description"]);
    $list_id = $_POST["list_id"];

    $stmt = $pdo->prepare(
        "INSERT INTO tasks (list_id, task_description) VALUES (:list_id, :task_description)"
    );
    $stmt->execute([
        "list_id" => $list_id,
        "task_description" => $task_description,
    ]);

    header("Location: ../views/tasks.php?list_id=" . $list_id);
}