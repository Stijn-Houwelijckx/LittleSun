<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['taskId'])) {
    // Store the selected task ID in the session
    $_SESSION['selectedTaskId'] = $_POST['taskId'];

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
