<?php

include_once (__DIR__ . "/../classes/Db.php");
include_once (__DIR__ . "/../classes/User.php");
include_once (__DIR__ . "/../classes/Employee.php");
include_once (__DIR__ . "/../classes/TimeTracker.php");
include_once (__DIR__ . "/../classes/CalendarItem.php");

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

// Check if the form is submitted
if (isset($_POST)) {
    try {
        $clockOutTime = TimeTracker::clockOut($pdo, $_SESSION["user_id"]);

        // Calculate worked time
        $lastTimeTracker = TimeTracker::getLastTimeTrackerByUserId($pdo, $_SESSION["user_id"]);
        $fullWorkedTime = TimeTracker::getWorkedTimeByUserIdAndDate($pdo, $_SESSION["user_id"], date("Y-m-d"));
        
        $start_time = strtotime($lastTimeTracker["start_time"]);
        $end_time = strtotime($lastTimeTracker["end_time"]);
        
        $worked_time_seconds = $end_time - $start_time;
        
        $hours = floor($worked_time_seconds / 3600);
        $minutes = floor(($worked_time_seconds % 3600) / 60);
        $seconds = $worked_time_seconds % 60;
        
        $worked_time_string = $hours . " hours, " . $minutes . " minutes and " . $seconds . ($seconds == 1 ? " second" : " seconds");
        
        $workedTime = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
        
        $plannedWorkTime = CalendarItem::getPlannedWorkTimeByUserIdAndDate($pdo, $_SESSION["user_id"], date("Y-m-d"));
        $plannedWorkHours = $plannedWorkTime['total_time']; // Assuming this is in the format HH:MM:SS
        $plannedWorkSeconds = strtotime($plannedWorkHours) - strtotime('TODAY');
        
        $totalWorkSeconds = strtotime($fullWorkedTime["worked_time"]) - strtotime('TODAY'); // Convert the time string to seconds
        
        if ($plannedWorkTime['total_time'] != null) {
            if ($totalWorkSeconds > $plannedWorkSeconds) {
                $overtimeSeconds = $totalWorkSeconds - $plannedWorkSeconds;
                $overtimeHours = floor($overtimeSeconds / 3600);
                $overtimeMinutes = floor(($overtimeSeconds % 3600) / 60);
                $overtimeSeconds = $overtimeSeconds % 60;
                $overtime = sprintf("%02d:%02d:%02d", $overtimeHours, $overtimeMinutes, $overtimeSeconds);
            
                TimeTracker::saveOvertime($pdo, $_SESSION["user_id"], $lastTimeTracker["id"], $overtime);
            } else {
                $overtime = "00:00:00"; // No overtime
            
                TimeTracker::saveOvertime($pdo, $_SESSION["user_id"], $lastTimeTracker["id"], $overtime);
            }
        } else {
            $overtime = $fullWorkedTime["worked_time"];
        }

        $response = [
            "status" => "success",
            "clockOutTime" => $clockOutTime,
            "plannedWorkTime" => $plannedWorkTime['total_time'], // Assuming this is in the format HH:MM:SS
            "workedTime" => $worked_time_string,
            "workedTimeHours" => $workedTime,
            "plannedWorkHours" => $plannedWorkTime['total_time'],
            "overtime" => $overtime,
            "plannedWorkSeconds" => $plannedWorkSeconds,
            "totalWorkSeconds" => $totalWorkSeconds,
            "fullworktime" => $fullWorkedTime["worked_time"],
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