<?php

include_once (__DIR__ . "/../classes/Db.php");
include_once (__DIR__ . "/../classes/User.php");
include_once (__DIR__ . "/../classes/Employee.php");
include_once (__DIR__ . "/../classes/TimeTracker.php");

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
        $clockInTime = TimeTracker::clockIn($pdo, $_SESSION["user_id"]);

        $response = [
            "status" => "success",
            "clockInTime" => $clockInTime
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