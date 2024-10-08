<?php
session_start();
require "../config/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
	$taskId = $_POST["task_id"];
	$newStatus = $_POST["new_status"];

	// Ensure the status is one of the allowed values
	if (!in_array($newStatus, ["pending", "in_progress", "completed"])) {
		echo "Invalid status";
		exit();
	}

	// Update the task's status in the database
	$stmt = $pdo->prepare(
		"UPDATE tasks SET status = :status WHERE id = :task_id"
	);
	$stmt->execute([
		"status" => $newStatus,
		"task_id" => $taskId,
	]);

	echo "Task status updated successfully";
}
