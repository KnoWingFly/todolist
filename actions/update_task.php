<?php
session_start();
require "../config/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
	$task_id = $_POST["task_id"];
	$title = $_POST["taskTitle"];
	$dueDate = $_POST["dueDate"];
	$status = $_POST["status"];
	$cardColor = $_POST["card_color"];
	$label = $_POST["taskLabel"];

	$stmt = $pdo->prepare(
		"UPDATE tasks SET title = :title, due_date = :due_date, status = :status, card_color = :card_color, label = :label WHERE id = :task_id"
	);
	$stmt->execute([
		"title" => $title,
		"due_date" => $dueDate,
		"status" => $status,
		"card_color" => $cardColor,
		"label" => $label,
		"task_id" => $task_id,
	]);

	header("Location: ../index.php");
	exit();
}
