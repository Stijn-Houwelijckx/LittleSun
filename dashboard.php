<?php
include_once (__DIR__ . "/classes/Db.php");
include_once (__DIR__ . "/classes/User.php");
include_once (__DIR__ . "/classes/TimeOffRequest.php");
include_once (__DIR__ . "/classes/Task.php");

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

// Process time off request form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["reason"]) && isset($_POST["startdate"]) && isset($_POST["enddate"])) {
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
}

$myTasks = Task::mytasks($pdo, $_SESSION["user_id"]);
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
            <div class="bento-item">
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
        </div>
        <div class="bento-item">
            <h2 class="bento-item-title">Clock In /Clock Out</h2>
                <div id="clockInForm">
                    <input class="btn bento-item-button" type="button" id="startButton" value="Start Work">
                </div>
            <div id="clockInInfo"></div>
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
        <div class="pop-up-overlay">
            <div class="time-off-popup">
                <button class="btn-close"><i class="fa fa-window-close-o"></i></button>
                <form class="popup-form" action="" method="post">
                    <div class="form-column">
                        <label for="reason">Reason for time off:</label>
                        <input type="text" name="reason" id="reason" required placeholder="Reason">
                    </div>
                    <div class="form-column">
                        <label for="startdate">Start date and time:</label>
                        <input type="datetime-local" name="startdate" id="startdate" required>
                    </div>
                    <div class="form-column">
                        <label for="enddate">End date and time:</label>
                        <input type="datetime-local" name="enddate" id="enddate" required>
                    </div>
                    <div class="form-column">
                        <label for="description">Description (not required):</label>
                        <textarea name="description" id="description" cols="30" rows="5" placeholder="Give more info if necessary."></textarea>
                    </div>
                    <button class="btn btn-submit">Submit request</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const btnRequest = document.querySelector(".bento-item-button");
        const popupOverlay = document.querySelector(".pop-up-overlay");
        const popup = document.querySelector(".time-off-popup");
        const btnClose = document.querySelector(".btn-close");
        const btnSubmit = document.querySelector(".btn-submit");

        // Show time off request popup when button is clicked
        btnRequest.addEventListener("click", () => {
            popupOverlay.style.display = "block";
            popup.style.display = "block";
        });

        // Close time off request popup when close button is clicked
        btnClose.addEventListener("click", () => {
            popupOverlay.style.display = "none";
            popup.style.display = "none";
        });

        // Clock In / Clock Out functionality
        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById("startButton").addEventListener("click", function() {
                var startButton = document.getElementById("startButton"); // Reference to the button

                var xhr = new XMLHttpRequest();
                xhr.open("POST", "clockin.php", true); // Make sure the URL matches the location of the PHP file
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        // Update the innerHTML of the button to "End Work" or "Start Work" depending on the current text
                        startButton.value = startButton.value === "Start Work" ? "End Work" : "Start Work";

                        // Toggle the 'clock-out' class of the button
                        startButton.classList.toggle("clock-out");

                        // Update the information next to the button
                        document.getElementById("clockInInfo").innerHTML = xhr.responseText;
                    }
                };
                xhr.send("start_work=" + (startButton.value === "Start Work" ? "true" : "false")); // Send the correct value for start_work
            });
        });
    </script>
</body>
</html>