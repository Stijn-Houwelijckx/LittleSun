<?php
include_once (__DIR__ . "/classes/Db.php");
include_once (__DIR__ . "/classes/User.php");
include_once (__DIR__ . "/classes/TimeOffRequest.php");
include_once (__DIR__ . "/classes/Task.php");
include_once (__DIR__ . "/classes/TimeTracker.php");
include_once (__DIR__ . "/classes/CalendarItem.php");
include_once (__DIR__ . "/classes/SickLeave.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', 'error.log');

session_start();

$current_page = 'home';

$pdo = Db::getInstance();
$user = User::getUserById($pdo, $_SESSION["user_id"]);

// Redirect to login page if user is not logged in or not an employee
if (!isset($_SESSION["user_id"]) || $user["typeOfUser"] != "employee") {
    header("Location: login.php?notLoggedIn=true");
    exit();
}

$userRequests = TimeOffRequest::getRequestsByUserId($pdo, $user["id"]);
$userSickLeave = SickLeave::getActiveSickLeave($pdo, $user["id"]);

// Process time off request form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($_POST["typeOfForm"] == "time-off" && isset($_POST["reason"]) && isset($_POST["startdate"]) && isset($_POST["enddate"])) {
        $timeOffRequest = new TimeOffRequest();

        $timeOffRequest->setStart_date($_POST["startdate"]);
        $timeOffRequest->setEnd_date($_POST["enddate"]);
        $timeOffRequest->setReason($_POST["reason"]);
        if (isset($_POST["description"])) {
            $timeOffRequest->setDescription($_POST["description"]);
        }

        $timeOffRequest->submitRequest($pdo, $user["id"]);

        header("Location: dashboard.php");
    }

    if ($_POST["typeOfForm"] == "sick-leave" && isset($_POST["reason"]) && isset($_POST["startdate"]) && isset($_POST["enddate"])) {
        $sickLeave = new SickLeave();

        $sickLeave->setStart_date($_POST["startdate"]);
        $sickLeave->setEnd_date($_POST["enddate"]);
        $sickLeave->setReason($_POST["reason"]);

        $sickLeave->submitSickLeave($pdo, $user["id"]);

        header("Location: dashboard.php");
    }
}

$myTasks = Task::mytasks($pdo, $_SESSION["user_id"]);

$timeTracker = TimeTracker::getActiveTimeTracker($pdo, $_SESSION["user_id"]);

$plannedWorkHours = CalendarItem::getPlannedWorkTimeByUserIdAndDate($pdo, $_SESSION["user_id"], date("Y-m-d"));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LittleSun</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.png">
</head>

<body>
    <?php include_once ('inc/nav.inc.php'); ?>
    
    <div class="dashboard">
        <div class="bento-grid">
            <div class="time-off bento-item">
                <h2 class="bento-item-title">Request time off</h2>
                <?php if (count($userRequests) > 0) : ?>
                    <?php foreach ($userRequests as $request) : ?>
                        <div class="request">
                            <p class="request-reason"><span class="request-label">Reason:</span> <?php echo $request["reason"] ?></p>
                            <p class="request-date-time"><span class="request-label">When:</span> 
                                <?php
                                    $start_date = date("F jS Y H:i", strtotime($request["start_date"]));
                                    $end_date = date("F jS Y H:i", strtotime($request["end_date"]));
                                    echo $start_date . " - " . $end_date;
                                ?>
                            </p>
                            <p class="request-status"><span class="request-label">Status:</span> <span class="request-status-text request-status-<?php echo strtolower($request["status"]) ?>"><?php echo $request["status"] ?></span></p>
                            <?php if($request["status"] != "Pending"): ?>
                                <p class="request-comment"><span class="request-label">Manager comment:</span> <?php echo $request["managerComment"] ?></p>
                            <?php endif ?>
                        </div>
                    <?php endforeach ?>
                <?php endif ?>
                <?php if (count($userRequests) == 0) : ?>
                    <p>You don't have time off requests</p>
                <?php endif ?>
                <button class="btn bento-item-button">Request time off</button>
            </div>
            <div class="bento-item">
                <div class="row">
                    <h2 class="bento-item-title" id="time-tracker-title"><?php echo $timeTracker? "Clock Out" : "Clock In"; ?></h2>
                    <?php if ($timeTracker) : ?>
                        <p class="circle"></p>
                    <?php endif ?>
                </div>
                <?php if ($plannedWorkHours["total_time"]) : ?>
                    <p id="plannedWorkHours">Planned work hours today: <?php echo $plannedWorkHours["total_time"] ?></p>
                <?php else : ?>
                    <p id="plannedWorkHours">No planned work hours for today</p>
                <?php endif ?>
    
                <button class="btn bento-item-button" id="clockInButton" style="display: <?php echo $timeTracker? "none" : "block";  ?>">Start Work</button>
                <button class="btn bento-item-button" id="clockOutButton" style="display: <?php echo $timeTracker && $timeTracker["end_time"] == null? "block" : "none";  ?>;">End Work</button>
                
                <div id="time-tracker-info">
                    <?php if ($timeTracker) : ?>
                        <?php if ($timeTracker["end_time"] == null) : ?>
                            <p id="clockInInfo">You clocked in at: <?php echo $timeTracker["start_time"] ?></p>
                        <?php endif ?>
                    <?php endif ?>
                </div>
            </div>
            <div class="myTasks">
                <h2>My tasks</h2>
                <?php if (count($myTasks) > 0) : ?>
                    <ul>
                        <?php foreach ($myTasks as $c) : ?>
                            <li><?php echo $c["task"]?></li>
                        <?php endforeach ?>
                    </ul>
                <?php endif ?>
                <?php if (count($myTasks) == 0) : ?>
                    <p>You don't have tasktypes</p>
                <?php endif ?>
            </div>
            <div class="sick-leave bento-item">
                <h2 class="bento-item-title">Sick leave</h2>
                <?php if (count($userSickLeave) > 0) : ?>
                    <?php foreach ($userSickLeave as $sl) : ?>
                        <div class="request">
                            <p class="request-reason"><span class="request-label">Reason:</span> <?php echo $sl["reason"] ?></p>
                            <p class="request-date-time"><span class="request-label">When:</span> 
                                <?php
                                    $start_date = date("F jS Y H:i", strtotime($sl["start_date"]));
                                    $end_date = date("F jS Y H:i", strtotime($sl["end_date"]));
                                    echo $start_date . " - " . $end_date;
                                ?>
                            </p>
                        </div>
                    <?php endforeach ?>
                <?php endif ?>
                <?php if (count($userSickLeave) == 0) : ?>
                    <p>You don't have active sick leave</p>
                <?php endif ?>
                <button class="btn bento-item-button">Call in sick</button>
            </div>
        </div>
        <div class="time-off pop-up-overlay">
            <div class="time-off-popup">
                <button class="btn-close"><i class="fa fa-window-close-o"></i></button>
                <form class="popup-form" action="" method="post">
                    <input hidden type="text" name="typeOfForm" value="time-off">
                    <div class="form-column">
                        <label for="t-o-reason">Reason for time off:</label>
                        <input type="text" name="reason" id="t-o-reason" required placeholder="Reason">
                    </div>
                    <div class="form-column">
                        <label for="t-o-startdate">Start date and time:</label>
                        <input type="datetime-local" name="startdate" id="t-o-startdate" required>
                    </div>
                    <div class="form-column">
                        <label for="t-o-enddate">End date and time:</label>
                        <input type="datetime-local" name="enddate" id="t-o-enddate" required>
                    </div>
                    <div class="form-column">
                        <label for="t-o-description">Description (not required):</label>
                        <textarea name="description" id="t-o-description" cols="30" rows="5" placeholder="Give more info if necessary."></textarea>
                    </div>
                    <button class="btn btn-submit">Submit request</button>
                </form>
            </div>
        </div>
        <div class="sick-leave pop-up-overlay">
            <div class="sick-leave-popup">
                <button class="btn-close"><i class="fa fa-window-close-o"></i></button>
                <form class="popup-form" action="" method="post">
                    <input hidden type="text" name="typeOfForm" value="sick-leave">
                    <div class="form-column">
                        <label for="s-l-reason">Reason sick leave:</label>
                        <input type="text" name="reason" id="s-l-reason" required placeholder="Reason">
                    </div>
                    <div class="form-column">
                        <label for="s-l-startdate">Start date and time:</label>
                        <input type="datetime-local" name="startdate" id="s-l-startdate" required>
                    </div>
                    <div class="form-column">
                        <label for="s-l-enddate">End date and time:</label>
                        <input type="datetime-local" name="enddate" id="s-l-enddate" required>
                    </div>
                    <button class="btn btn-submit">Submit sick leave</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const btnRequest_t_o = document.querySelector(".time-off .bento-item-button");
        const popupOverlay_t_o = document.querySelector(".time-off.pop-up-overlay");
        const popup_t_o = document.querySelector(".time-off-popup");
        const btnClose_t_o = document.querySelector(".time-off-popup .btn-close");
        const btnSubmit_t_o = document.querySelector(".time-off-popup .btn-submit");

        // Show time off request popup when button is clicked
        btnRequest_t_o.addEventListener("click", () => {
            popupOverlay_t_o.style.display = "block";
            popup_t_o.style.display = "block";
        });

        // Close time off request popup when close button is clicked
        btnClose_t_o.addEventListener("click", () => {
            popupOverlay_t_o.style.display = "none";
            popup_t_o.style.display = "none";
        });


        const btnRequest_s_l = document.querySelector(".sick-leave .bento-item-button");
        const popupOverlay_s_l = document.querySelector(".sick-leave.pop-up-overlay");
        const popup_s_l = document.querySelector(".sick-leave-popup");
        const btnClose_s_l = document.querySelector(".sick-leave-popup .btn-close");
        const btnSubmit_s_l = document.querySelector(".sick-leave-popup .btn-submit");

        // Show time off request popup when button is clicked
        btnRequest_s_l.addEventListener("click", () => {
            popupOverlay_s_l.style.display = "block";
            popup_s_l.style.display = "block";
        });

        // Close time off request popup when close button is clicked
        btnClose_s_l.addEventListener("click", () => {
            popupOverlay_s_l.style.display = "none";
            popup_s_s.style.display = "none";
        });
    </script>
    
    <script src="javascript/timeTracker.js"></script>
</body>
</html>