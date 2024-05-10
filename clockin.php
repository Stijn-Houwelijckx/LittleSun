<?php
// Include TimeTracker class
include_once (__DIR__ . "/classes/TimeTracker.php");
include_once (__DIR__ . "/classes/Db.php");

session_start(); // Start the session

// If the "Start Work" button is pressed
if (isset($_POST['start_work'])) {
    try {
        $pdo = Db::getInstance();

        // Get the user ID from the session
        $user_id = $_SESSION['user_id'];

        // Check if the button value is "true" (Start Work) or "false" (End Work)
        $clock_in = ($_POST['start_work'] === 'true') ? true : false;

        // Call TimeTracker::clockInOut method to handle clock in/out functionality
        $current_time = date('Y-m-d H:i:s'); // Current time
        
        // Record clock in/out time and get response message
        $response = TimeTracker::clockInOut($pdo, $user_id, $clock_in, $current_time);

        echo $response;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
