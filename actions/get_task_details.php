<?php
session_start();
require "../config/db.php";

if (isset($_GET["task_id"])) {
	$task_id = $_GET["task_id"];

	$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = :task_id");
	$stmt->execute(["task_id" => $task_id]);
	$task = $stmt->fetch(PDO::FETCH_ASSOC);

	if ($task) {
		echo "<strong>Title:</strong> " .
			htmlspecialchars($task["title"]) .
			"<br>";
		echo "<strong>Due Date:</strong> " .
			htmlspecialchars($task["due_date"]) .
			"<br>";
		echo "<strong>Status:</strong> " .
			htmlspecialchars($task["status"]) .
			"<br>";
		if (!empty($task["label"])) {
			echo "<strong>Label:</strong> " .
				htmlspecialchars($task["label"]) .
				" (Color: " .
				htmlspecialchars($task["label_color"]) .
				")<br>";
		}
	} else {
		echo "Task not found.";
	}
}
