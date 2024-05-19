<?php

include_once (__DIR__ . "/../classes/Db.php");
include_once (__DIR__ . "/../classes/User.php");
include_once (__DIR__ . "/../classes/Employee.php");
include_once (__DIR__ . "/../classes/TimeTracker.php");
include_once (__DIR__ . "/../classes/CalendarItem.php");
include_once (__DIR__ . "/../classes/WorkEntry.php");
include_once (__DIR__ . "/../classes/Task.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', 'error.log');

// THIS NEEDS TO BE CHANGED TO THE CORRECT TIMEZONE OR REMOVED
date_default_timezone_set("Europe/Brussels");

session_start();

$pdo = Db::getInstance();
$manager = User::getUserById($pdo, $_SESSION["user_id"]);

// Check if the user is logged in and manager
if (!isset($_SESSION["user_id"]) || $manager["typeOfUser"] != "employee") {
    http_response_code(401); // Unauthorized
    exit();
}

// Initialize plannedWorkSeconds and totalWorkSeconds
$plannedWorkSeconds = 0;
$totalWorkSeconds = 0;

// Check if the form is submitted
if (isset($_POST)) {
    try {
        $clockOutTime = TimeTracker::clockOut($pdo, $_SESSION["user_id"]);

        // Calculate worked time
        $lastTimeTracker = TimeTracker::getLastTimeTrackerByUserId($pdo, $_SESSION["user_id"]);
        $fullWorkedTime = TimeTracker::getWorkedTimeByUserIdAndDate($pdo, $_SESSION["user_id"], date("Y-m-d"));

        // Get the task ID for the closest task
        $closestTaskId = Task::getClosestTaskIdForUser($pdo, $_SESSION["user_id"], new DateTime($lastTimeTracker["end_time"]));
        
        $start_time = strtotime($lastTimeTracker["start_time"]);
        $end_time = strtotime($lastTimeTracker["end_time"]);
        
        $worked_time_seconds = $end_time - $start_time;
        
        $hours = floor($worked_time_seconds / 3600);
        $minutes = floor(($worked_time_seconds % 3600) / 60);
        $seconds = $worked_time_seconds % 60;
        
        $worked_time_string = $hours . " hours, " . $minutes . " minutes and " . $seconds . ($seconds == 1 ? " second" : " seconds");
        
        $workedTime = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
        
        $plannedWorkTime = CalendarItem::getPlannedWorkTimeByUserIdAndDate($pdo, $_SESSION["user_id"], date("Y-m-d"));

        // Update the worked_time for work entry in the database
        $workEntry = new WorkEntry();
        $workEntry->setUser_id($_SESSION["user_id"]);
        $workEntry->setTask_id($closestTaskId);
        $workEntry->setEvent_date(date("Y-m-d"));
        $workEntry->setTimeWorked($workedTime);

        $workEntry->updateWorkEntryTimeWorked($pdo);
        
        // Check if plannedWorkTime is not null before using it
        if ($plannedWorkTime['total_time'] != null) {
            // Convert the planned work time to seconds
            $plannedWorkSeconds = strtotime($plannedWorkTime['total_time']) - strtotime('TODAY');

            // Calculate total work time in seconds
            $totalWorkSeconds = strtotime($fullWorkedTime["worked_time"]) - strtotime('TODAY');

            // Check if total work time exceeds planned work time
            if ($totalWorkSeconds > $plannedWorkSeconds) {
                // Calculate overtime
                $overtimeSeconds = $totalWorkSeconds - $plannedWorkSeconds;
                $overtimeHours = floor($overtimeSeconds / 3600);
                $overtimeMinutes = floor(($overtimeSeconds % 3600) / 60);
                $overtimeSeconds = $overtimeSeconds % 60;
                $overtime = sprintf("%02d:%02d:%02d", $overtimeHours, $overtimeMinutes, $overtimeSeconds);
            } else {
                $overtime = "00:00:00"; // No overtime
            }
        } else {
            // If planned work time is null, set overtime to full worked time
            $overtime = $fullWorkedTime["worked_time"];
        }

        $response = [
            "status" => "success",
            "clockOutTime" => $clockOutTime,
            "workedTime" => $worked_time_string,
            "overtime" => $overtime,
            "fullworktime" => $fullWorkedTime["worked_time"],
            "plannedWorkTime" => $plannedWorkTime['total_time'], // For debugging purposes
            "workedTimeHours" => $workedTime, // For debugging purposes
            "plannedWorkHours" => $plannedWorkTime['total_time'], // For debugging purposes
            "plannedWorkSeconds" => $plannedWorkSeconds, // For debugging purposes
            "totalWorkSeconds" => $totalWorkSeconds, // For debugging purposes
        ];
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());

        $response = [
            "status" => "error",
            "message" => "Something went wrong, please try again later."
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
} 
