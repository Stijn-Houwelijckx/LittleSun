<?php
include_once (__DIR__ . "../../classes/Db.php");
include_once (__DIR__ . "../../classes/User.php");
include_once (__DIR__ . "../../classes/Employee.php");
include_once (__DIR__ . "../../classes/Task.php");
include_once (__DIR__ . "../../classes/CalendarItem.php");

// Hier plaats je de logica om gebruikers op te halen op basis van de taak
// Bijvoorbeeld:
$taskId = $_POST['taskId'] ?? null;
$users = CalendarItem::getAllUsersByTaskTypeAndEventDate(Db::getInstance(), $taskId);

// Retourneer de gebruikers als JSON
echo json_encode(['users' => $users]);
