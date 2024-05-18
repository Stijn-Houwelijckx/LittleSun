<?php
// Include necessary classes
include_once (__DIR__ . "../../classes/Db.php");
include_once (__DIR__ . "../../classes/User.php");
include_once (__DIR__ . "../../classes/Manager.php");
include_once (__DIR__ . "../../classes/Employee.php");
include_once (__DIR__ . "../../classes/TimeOffRequest.php");
include_once (__DIR__ . "../../classes/Task.php");
include_once (__DIR__ . "../../classes/TimeTracker.php");
include_once (__DIR__ . "../../classes/CalendarItem.php");
include_once (__DIR__ . "../../classes/SickLeave.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', 'error.log');

session_start();

$current_page = 'reports';

$pdo = Db::getInstance();
$user = User::getUserById($pdo, $_SESSION["user_id"]);

if (isset($_SESSION["user_id"]) && $user["typeOfUser"] == "manager") {
    $allEmployeesByLocation = Employee::getAllEmployeesByLocation($pdo, $user["location_id"]);

    $allYears = CalendarItem::getDistinctYearsByLocation($pdo, $user["location_id"]);
    // $allMonths = CalendarItem::getDistinctMonthsByLocation($pdo, date("Y"), $user["location_id"]);

    $calenderMonths = CalendarItem::getDistinctMonthsByLocation($pdo, date("Y"), $user["location_id"]);
    $timeTrackerMonths = TimeTracker::getDistinctMonthsByLocation($pdo, date("Y"), $user["location_id"]);
    $timeOffMonths = TimeOffRequest::getDistinctMonthsByLocation($pdo, date("Y"), $user["location_id"]);
    $sickLeaveMonths = SickLeave::getDistinctMonthsByLocation($pdo, date("Y"), $user["location_id"]);

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

    $allMonths = array_values($uniqueMonths);

    try {
        $pdo = Db::getInstance();
        $user = User::getUserById($pdo, $_SESSION["user_id"]);
    } catch (Exception $e) {
        error_log('Database error: ' . $e->getMessage());
    }
} else {
    header("Location: ../login.php?error=notLoggedManager");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LittleSun</title>
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="icon" type="image/x-icon" href="../assets/images/favicon.png">
</head>

<body>
    <?php include_once ('../inc/nav.inc.php'); ?>

    <div class="dashboard">
        <div class="bento-grid">
            <div class="bento-grid-row">
                <div class="bento-item">
                    <h2 class="bento-item-title">Reports</h2>
                    <!-- <a href="report.php" class="btn">Generate report</a> -->

                    <div class="column">
                        <label for="yearSelector">Select year:</label>
                        <select name="yearSelector" id="yearSelector">
                            <?php foreach ($allYears as $year) : ?>
                                <option value="<?php echo $year["year"] ?>" <?php echo $year["year"] == date('Y') ? 'selected' : '' ?>><?php echo $year["year"] ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>

                    <div class="column">
                        <label for="monthSelector">Select month (select no month for a report of the full year):</label>
                        <select name="monthSelector" id="monthSelector">
                            <option value="">--- select month ---</option>
                            <?php foreach ($allMonths as $month) : ?>
                                <option value="<?php echo $month["month_number"] ?>"><?php echo $month["month_name"] ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>

                    <div class="column">
                        <label for="userSelector">Select user (select no user for a report of all users):</label>
                        <select name="userSelector" id="userSelector">
                            <option value="" disabled selected>--- select user ---</option>
                            <?php foreach ($allEmployeesByLocation as $employee) : ?>
                                <option value="<?php echo $employee["id"] ?>"><?php echo $employee["firstname"] . " " . $employee["lastname"] ?></option>
                            <?php endforeach ?>
                        </select>
                        <!-- <p>Select no user if you want to generate a report about all users.</p> -->
                    </div>

                    <button class="report-btn btn">Generate report</button>
                </div>
            </div>
        </div>
    </div>
</body>

<script src="../javascript/reports.js"></script>

</html>