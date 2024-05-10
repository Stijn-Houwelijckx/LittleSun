<?php
include_once (__DIR__ . "../../classes/Db.php");
include_once (__DIR__ . "../../classes/User.php");
include_once (__DIR__ . "../../classes/Employee.php");
include_once (__DIR__ . "../../classes/Task.php");
include_once (__DIR__ . "../../classes/CalendarItem.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['taskId'])) {
    // Store the selected task ID in the session
    $_SESSION['selectedTaskId'] = $_POST['taskId'];
    $allUsersByTaskTypeAndDate = CalendarItem::getAllUsersByTaskTypeAndEventDate($pdo, $_SESSION['selectedTaskId']);

    // Optionally, you can perform additional actions here, such as validating the task ID or logging the action

    // Send a response back to the client indicating success
    $response = ['status' => 'success', 'message' => 'Task ID stored in session'];
    echo json_encode($response);
    exit;
} else {
    // If the request method is not POST or if the taskId parameter is not set, return an error response
    $response = ['status' => 'error', 'message' => 'Invalid request'];
    echo json_encode($response);
    exit;
}

?>

