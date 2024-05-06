<?php
include_once (__DIR__ . "../classes/Db.php");

session_start(); // Start the session

// If the "Start Work" button is pressed
if (isset($_POST['start_work'])) {
    try {
        $pdo = Db::getInstance();

        // Get the user ID from the session
        $user_id = $_SESSION['user_id'];

        if ($_POST['start_work'] === 'true') {
            // Get the current date and time
            $start_time_full = date('Y-m-d H:i:s'); // Full date and time
            $start_time = date('H:i:s', strtotime($start_time_full)); // Only the time

            // Query to add the employee's start time to the database
            $stmt = $pdo->prepare("INSERT INTO clock_in_time (user_id, start_time_full, start_time) VALUES (:user_id, :start_time_full, :start_time)");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':start_time_full', $start_time_full);
            $stmt->bindParam(':start_time', $start_time);
            $stmt->execute();

            echo "You clocked in at: " . $start_time; // Display the start time (time only, no date)
        } elseif ($_POST['start_work'] === 'false') {
            // Get the current date and time
            $end_time_full = date('Y-m-d H:i:s'); // Full date and time
            $end_time = date('H:i:s', strtotime($end_time_full)); // Only the time

            // Query to add the employee's end time to the database
            $stmt = $pdo->prepare("INSERT INTO clock_out_time (user_id, end_time_full, end_time) VALUES (:user_id, :end_time_full, :end_time)");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':end_time_full', $end_time_full);
            $stmt->bindParam(':end_time', $end_time);
            $stmt->execute();

            // Calculate the work time
            $stmt = $pdo->prepare("SELECT start_time, end_time FROM calendar WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $total_planned_time_seconds = 0;
            $total_worked_time_seconds = 0;

            foreach ($tasks as $task) {
                $task_start_timestamp = strtotime($task['start_time']);
                $task_end_timestamp = strtotime($task['end_time']);

                // Calculate the planned work time for this task in seconds
                $task_duration_seconds = $task_end_timestamp - $task_start_timestamp;
                $total_planned_time_seconds += $task_duration_seconds;
            }

            // Get the actual work time of the employee
            $stmt = $pdo->prepare("SELECT start_time_full FROM clock_in_time WHERE user_id = :user_id ORDER BY id DESC LIMIT 1");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            $start_time_full = $stmt->fetchColumn();

            $start_timestamp = strtotime($start_time_full);
            $end_timestamp = strtotime($end_time_full);
            $total_worked_time_seconds = $end_timestamp - $start_timestamp;

            // Format the total planned and worked time into hours and minutes
            $planned_hours = floor($total_planned_time_seconds / 3600);
            $planned_minutes = floor(($total_planned_time_seconds % 3600) / 60);

            $worked_hours = floor($total_worked_time_seconds / 3600);
            $worked_minutes = floor(($total_worked_time_seconds % 3600) / 60);

            echo "Planned work time: " . $planned_hours . " hours and " . $planned_minutes . " minutes<br>";
            echo "Worked time: " . $worked_hours . " hours and " . $worked_minutes . " minutes";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>


