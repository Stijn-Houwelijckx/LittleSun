<?php

include_once (__DIR__ . "/../classes/Db.php");
include_once (__DIR__ . "/../classes/User.php");
include_once (__DIR__ . "/../classes/CalendarItem.php");
include_once (__DIR__ . "/../classes/TimeTracker.php");
include_once (__DIR__ . "/../classes/TimeOffRequest.php");
include_once (__DIR__ . "/../classes/SickLeave.php");


error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', 'error.log');

session_start();

$pdo = Db::getInstance();
$manager = User::getUserById($pdo, $_SESSION["user_id"]);

// Check if the user is logged in and manager
if (!isset($_SESSION["user_id"]) || $manager["typeOfUser"] != "manager") {
    http_response_code(401); // Unauthorized
    exit();
}

// Check if the form is submitted
if (!empty($_POST)) {
    try {
        $year = $_POST['year'];
        $location_id = $manager["location_id"];
        $calenderMonths = CalendarItem::getDistinctMonthsByLocation($pdo, $year, $location_id);
        $timeTrackerMonths = TimeTracker::getDistinctMonthsByLocation($pdo, $year, $location_id);
        $timeOffMonths = TimeOffRequest::getDistinctMonthsByLocation($pdo, $year, $location_id);
        $sickLeaveMonths = SickLeave::getDistinctMonthsByLocation($pdo, $year, $location_id);

        // Merge arrays
        $months = array_merge($calenderMonths, $timeTrackerMonths, $timeOffMonths, $sickLeaveMonths);

        // Remove duplicates
        $uniqueMonths = [];
        foreach ($months as $month) {
            $monthKey = $month['month_number'];
            if (!isset($uniqueMonths[$monthKey])) {
                $uniqueMonths[$monthKey] = $month;
            }
        }

        // Sort the array by month_number
        usort($uniqueMonths, function($a, $b) {
            return $a['month_number'] <=> $b['month_number'];
        });
        
        $response = [
            "status" => "success",
            "months" => array_values($uniqueMonths)
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