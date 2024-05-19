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
include_once (__DIR__ . "../../classes/Report.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', 'error.log');

session_start();

$current_page = 'reports';

$pdo = Db::getInstance();
$manager = User::getUserById($pdo, $_SESSION["user_id"]);

$allEmployees = array();

if (isset($_SESSION["user_id"]) && $manager["typeOfUser"] == "manager") {
    
    try {
        $pdo = Db::getInstance();
        $manager = User::getUserById($pdo, $_SESSION["user_id"]);
        
        $allEmployeesByLocation = Employee::getAllEmployeesByLocation($pdo, $manager["location_id"]);
        $allTasksFilter = Task::getAllTasks($pdo);
    
        $allYears = CalendarItem::getDistinctYearsByLocation($pdo, $manager["location_id"]);
        // $allMonths = CalendarItem::getDistinctMonthsByLocation($pdo, date("Y"), $user["location_id"]);
    
        $calenderMonths = CalendarItem::getDistinctMonthsByLocation($pdo, date("Y"), $manager["location_id"]);
        $timeTrackerMonths = TimeTracker::getDistinctMonthsByLocation($pdo, date("Y"), $manager["location_id"]);
        $timeOffMonths = TimeOffRequest::getDistinctMonthsByLocation($pdo, date("Y"), $manager["location_id"]);
        $sickLeaveMonths = SickLeave::getDistinctMonthsByLocation($pdo, date("Y"), $manager["location_id"]);
    
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

        // Get the user report data

        if(isset($_GET['userId'])){
            $employee = Employee::getEmployeeById($pdo, $_GET['userId']);
            $allEmployees = array($employee);           
        } else {
            $allEmployees = Employee::getAllEmployeesByLocation($pdo, $manager["location_id"]);
        }

        if(isset($_GET['year'])) {
            $yearReport = $_GET['year'];
        } else {
            $yearReport = date("Y");
        }

        if(isset($_GET['month'])) {
            $monthReport = $_GET['month'];
            $monthName = date("F", strtotime("{$yearReport}-{$monthReport}-01"));
        } else {
            $monthReport = null;
        }

        foreach($allEmployees as $employee) {
            $plannedTime = Report::getPlannedWorkTimeByUserIdBetweenDate($pdo, $employee["id"], $yearReport, $monthReport);
            $workedTime = Report::getWorkedTimeByUserIdBetweenDate($pdo, $employee["id"], $yearReport, $monthReport);
            $timeOff = Report::getTimeOffByUserIdBetweenDate($pdo, $employee["id"], $yearReport, $monthReport);

            // Calculate overtime
            $plannedTimeInSeconds = strtotime($plannedTime["total_time"]);
            $workedTimeInSeconds = strtotime($workedTime["total_time"]);
            
            if ($workedTimeInSeconds > $plannedTimeInSeconds) {
                $overtime = $workedTimeInSeconds - $plannedTimeInSeconds;
            } else {
                $overtime = 0;
            }

            // Get sick time
            $sickTime = Report::getTotalSickTimeByUserIdBetweenDate($pdo, $employee["id"], $yearReport, $monthReport);
        }

        // Get the task report data

        if(isset($_GET['taskId']) && isset($_GET['userId'])) {
            $task = Task::getTaskInfoById($pdo, $_GET['taskId']);
            $allTasks = array($task);           
        } else if(isset($_GET['taskId']) && !isset($_GET['userId'])){
            $task = Task::getTaskInfoById($pdo, $_GET['taskId']);
            $allTasks = array($task);           
        } else if(isset($_GET['userId'])) {
            $allTasks = Task::getTasksByEmployeeId($pdo, $_GET['userId']);
        } else {
            $allTasks = Task::getAllTasks($pdo);
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
        <div class="bento-grid">
            <div class="bento-grid-row">
                <div class="bento-item">
                    <h2 class="bento-item-title">Report filters</h2>
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
                            <?php foreach ($allEmployeesByLocation as $employeeByLoc) : ?>
                                <option value="<?php echo $employeeByLoc["id"]; ?>"><?php echo $employeeByLoc["firstname"] . " " . $employeeByLoc["lastname"]; ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>

                    <div class="column">
                        <label for="taskSelector">Select task (select no task for a report of all tasks):</label>
                        <select name="taskSelector" id="taskSelector">
                            <option value="" disabled selected>--- select task ---</option>
                            <?php foreach ($allTasksFilter as $taskFilter) : ?>
                                <option value="<?php echo $taskFilter["id"]; ?>"><?php echo $taskFilter["task"]; ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>

                    <button class="report-btn btn">Generate report</button>
                </div>
                
                <div class="reports-container">
                    <!-- Employee report -->
                    <div class="bento-item" style="width: 100%;">
                        <div class="row">
                            <h1>Generated user(s) report</h1>
                            <h2>Report date: <?php echo $yearReport; echo $monthReport ? " - " . $monthName : ""; ?></h2>
                            <h2>Report user: <?php echo isset($_GET['userId']) ? $employee["firstname"] . " " . $employee["lastname"] : "All users"; ?></h2>
                        </div>
    
                        <div class="report-container" style="padding-right: 0">
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
                                    <?php $totalWorkedTime = null; ?>
                                    <?php foreach($allEmployees as $employee) : ?>
                                        <?php
                                            $plannedTime = Report::getPlannedWorkTimeByUserIdBetweenDate($pdo, $employee["id"], $yearReport, $monthReport);
                                            $workedTime = Report::getWorkedTimeByUserIdBetweenDate($pdo, $employee["id"], $yearReport, $monthReport);
                                            $timeOff = Report::getTimeOffByUserIdBetweenDate($pdo, $employee["id"], $yearReport, $monthReport);
                                
                                            // Calculate overtime
                                            $plannedTimeInSeconds = strtotime($plannedTime["total_time"]);
                                            $workedTimeInSeconds = strtotime($workedTime["total_time"]);
                                            
                                            if ($workedTimeInSeconds > $plannedTimeInSeconds) {
                                                $overtime = $workedTimeInSeconds - $plannedTimeInSeconds;
                                            } else {
                                                $overtime = 0;
                                            }
                                
                                            // Get sick time
                                            $sickTime = Report::getTotalSickTimeByUserIdBetweenDate($pdo, $employee["id"], $yearReport, $monthReport);

                                            $totalWorkedTime += strtotime($workedTime["total_time"]);
                                            $totalWorkedTimeFormatted = date('H:i:s', $totalWorkedTime);
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
                                <div class="table-row" style="background-color: #939393; color: white;">
                                    <div class="table-data">Total:</div>
                                    <div class="table-data">-</div>
                                    <div class="table-data"><?php echo $totalWorkedTimeFormatted; ?></div>
                                    <div class="table-data">-</div>
                                    <div class="table-data">-</div>
                                    <div class="table-data">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
    
                    <!-- Task report -->
                    <div class="bento-item" style="width: 100%;">
                        <div class="row">
                            <h1>Generated task(s) report</h1>
                            <h2>Report date: <?php echo $yearReport; echo $monthReport ? " - " . $monthName : ""; ?></h2>
                            <h2>Report task: <?php echo isset($_GET['taskId']) ? $task["task"] : "All tasks"; ?></h2>
                        </div>
    
                        <div class="report-container" style="padding-right: 0">
                            <div class="table">
                                <div class="table-header">
                                    <div class="header__item"><a id="task_employee" class="filter__link" href="#">Employee</a></div>
                                    <div class="header__item"><a id="task_time_planned" class="filter__link filter__link--number" href="#">Time Planned</a></div>
                                    <div class="header__item"><a id="task_time_worked" class="filter__link filter__link--number" href="#">Time Worked</a></div>
                                    <div class="header__item"><a id="task_sick_time" class="filter__link filter__link--number" href="#">Sick Time</a></div>
                                </div>
                                <div class="table-content">
                                    <?php foreach($allTasks as $task) : ?>
                                        <?php
                                            if(!isset($_GET['userId'])) {
                                                $employeesByTask = Employee::getEmployeesByTaskForLocation($pdo, $task["id"], $manager["location_id"]);
                                            } else {
                                                $employeesByTask = array(Employee::getEmployeeById($pdo, $_GET['userId']));
                                            }
                                        ?>
    
                                        <div class="table-row">
                                            <div class="table-data table-subheader"><?php echo htmlspecialchars($task["task"]) ?></div>
                                        </div>
                                        <?php foreach($employeesByTask as $employee) : ?>
                                            <?php
                                                $taskPlannedTime = Report::getPlannedWorkTimeForTaskByUserIdBetweenDate($pdo, $employee["id"], $task["id"], $yearReport, $monthReport);
                                                $taskWorkedTime = Report::getWorkedTimeForTaskByUserIdBetweenDate($pdo, $employee["id"], $task["id"], $yearReport, $monthReport);
                                                $taskSickTime = Report::getTotalSickTimeForTaskByUserIdBetweenDate($pdo, $employee["id"], $task["id"], $yearReport, $monthReport);
                                            ?>

                                            <div class="table-row">
                                                <div class="table-data"><?php echo htmlspecialchars($employee["firstname"]) . " " . htmlspecialchars($employee["lastname"]); ?></div>
                                                <div class="table-data"><?php echo htmlspecialchars($taskPlannedTime["total_time"]); ?></div>
                                                <div class="table-data"><?php echo htmlspecialchars($taskWorkedTime["total_time"]); ?></div>
                                                <div class="table-data"><?php echo htmlspecialchars($taskSickTime["total_time"]); ?></div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../javascript/reports.js"></script>
    <script src="../javascript/report.js"></script>
</body>
</html>