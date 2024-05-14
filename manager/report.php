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

// $current_page = 'home';

$pdo = Db::getInstance();
$manager = User::getUserById($pdo, $_SESSION["user_id"]);

$allEmployees = array();

if (isset($_SESSION["user_id"]) && $manager["typeOfUser"] == "manager") {
    try {
        if(isset($_GET['userId'])){
            $employee = Employee::getEmployeeById($pdo, $_GET['userId']);
            $allEmployees = array($employee);           
        } else {
            $allEmployees = Employee::getAllEmployeesByLocation($pdo, $manager["location_id"]);
        }
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
        <div class="report-container">
            <div class="table">
                <div class="table-header">
                    <div class="header__item"><a id="employee" class="filter__link" href="#">Employee</a></div>
                    <div class="header__item"><a id="time_planned" class="filter__link filter__link--number" href="#">Time Planned</a></div>
                    <div class="header__item"><a id="time_worked" class="filter__link filter__link--number" href="#">Time Worked</a></div>
                    <div class="header__item"><a id="time_off" class="filter__link filter__link--number" href="#">Time Off</a></div>
                    <div class="header__item"><a id="overtime" class="filter__link filter__link--number" href="#">Overtime</a></div>
                    <div class="header__item"><a id="sick_time" class="filter__link filter__link--number" href="#">Sick Time</a></div>
                </div>
                <div class="table-content">
                    <?php foreach($allEmployees as $employee) : ?>
                        <?php
                            $plannedTime = CalendarItem::getPlannedWorkTimeByUserId($pdo, $employee["id"]);
                            $workedTime = TimeTracker::getWorkedTimeByUserId($pdo, $employee["id"]);
                            $timeOff = TimeOffRequest::getTimeOffByUserId($pdo, $employee["id"]);

                            // Calculate overtime
                            $plannedTimeInSeconds = strtotime($plannedTime["total_time"]);
                            $workedTimeInSeconds = strtotime($workedTime["total_time"]);
                            
                            if ($workedTimeInSeconds > $plannedTimeInSeconds) {
                                $overtime = $workedTimeInSeconds - $plannedTimeInSeconds;
                            } else {
                                $overtime = 0;
                            }

                            // Get sick time
                            $sickTime = SickLeave::getTotalSickTimeByUserId($pdo, $employee["id"]);
                        ?>

                        <div class="table-row">
                            <div class="table-data"><?php echo htmlspecialchars($employee["firstname"]) . " " . htmlspecialchars($employee["lastname"]); ?></div>
                            <div class="table-data"><?php echo htmlspecialchars($plannedTime["total_time"]); ?></div>
                            <div class="table-data"><?php echo htmlspecialchars($workedTime["total_time"]); ?></div>
                            <div class="table-data"><?php echo htmlspecialchars($timeOff["total_time"]); ?></div>
                            <div class="table-data"><?php echo date('H:i:s', $overtime); ?></div>
                            <div class="table-data"><?php echo htmlspecialchars($sickTime["total_time"]); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="../javascript/report.js"></script>
</body>
</html>