<?php
session_start();
require "../config/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["task_id"])) {
	$task_id = $_POST["task_id"];

	$stmt = $pdo->prepare("DELETE FROM tasks WHERE id = :task_id");
	$stmt->execute(["task_id" => $task_id]);

	echo "Task deleted successfully";
}
