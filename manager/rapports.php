<?php
// Include necessary classes
include_once (__DIR__ . "../../classes/Db.php");
include_once (__DIR__ . "../../classes/User.php");
include_once (__DIR__ . "../../classes/Manager.php");
include_once (__DIR__ . "../../classes/Employee.php");
include_once (__DIR__ . "../../classes/TimeOffRequest.php");
include_once (__DIR__ . "../../classes/Task.php");
include_once (__DIR__ . "../../classes/TimeTracker.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', 'error.log');

session_start();

$current_page = 'home';

$pdo = Db::getInstance();
$user = User::getUserById($pdo, $_SESSION["user_id"]);

if (isset($_SESSION["user_id"]) && $user["typeOfUser"] == "manager") {
    $allEmployeesByLocation = Employee::getAllEmployeesByLocation($pdo, $user["location_id"]);

    try {
        $pdo = Db::getInstance();
        $user = User::getUserById($pdo, $_SESSION["user_id"]);

        $timeOffRequests = TimeOffRequest::getAllPendingRequests($pdo, $user["location_id"]);

        if (isset($_POST["approve"])) {
            $requestId = $_POST["requestId"];
            $managerComment = $_POST["managerComment"];

            TimeOffRequest::updateRequestStatus($pdo, $requestId, "Approved", $managerComment);

            header("Location: dashboard.php");
        }
        
        if (isset($_POST["decline"])) {
            $requestId = $_POST["requestId"];
            $managerComment = $_POST["managerComment"];
            
            TimeOffRequest::updateRequestStatus($pdo, $requestId, "Declined", $managerComment);

            header("Location: dashboard.php");
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
                    <h2 class="bento-item-title">Reports</h2>
                    <!-- <a href="report.php" class="btn">Generate report</a> -->

                    <div class="column">
                        <label for="userSelector">Select user (select no user for all users):</label>
                        <select name="userSelector" id="userSelector">
                            <option value="" disabled selected>--- select user ---</option>
                            <?php foreach ($allEmployeesByLocation as $employee) : ?>
                                <option value="<?php echo $employee["id"] ?>"><?php echo $employee["firstname"] . " " . $employee["lastname"] ?></option>
                            <?php endforeach ?>
                        </select>
                        <p>Select no user if you want to generate a report about all users.</p>
                    </div>

                    <button class="report-btn btn">Generate report</button>
                </div>
            </div>
        </div>
        <div class="pop-up-overlay">
            <div class="time-off-popup">
                <button class="btn-close"><i class="fa fa-window-close-o"></i></button>
                <p class="request-creator"><span class="request-label">Employee:</span></p>
                <p class="request-reason"><span class="request-label">Reason:</span></p>
                <p class="request-date-time"><span class="request-label">When:</span></p>
                <p class="request-description"><span class="request-label">Description:</span></p>
                <div class="row">
                    <form class="form-btns" action="" method="post">
                        <input type="hidden" name="requestId" value="">
                        <label for="managerComment">Comment:</label>
                        <input type="text" name="managerComment" id="managerComment">
                        <div class="btn-container">
                            <button class="btn btn-decline" name="decline">Decline</button>
                            <button class="btn btn-approve" name="approve">Approve</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

<script>
    const requests = document.querySelectorAll(".request");
    const popupOverlay = document.querySelector(".pop-up-overlay");
    const popup = document.querySelector(".time-off-popup");
    const btnClose = document.querySelector(".btn-close");
    const btnDecline = document.querySelector(".btn-decline");
    const btnApprove = document.querySelector(".btn-approve");
    <?php
        echo 'const timeOffRequests = ' . json_encode($timeOffRequests) . ';';
    ?>

    // Update the JavaScript code to fill the popup with the selected user's time-off request details
    requests.forEach(request => {
        request.addEventListener("click", function (e) {
            const requestId = request.getAttribute("data-requestid");
            const selectedRequest = timeOffRequests.find(request => request.id === parseInt(requestId));

            if (selectedRequest) {
                const popupContent = document.querySelector(".time-off-popup");
                popupContent.querySelector(".request-creator").innerHTML = "<span class='request-label'>Employee:</span> " + selectedRequest.firstname + " " + selectedRequest.lastname;
                popupContent.querySelector(".request-reason").innerHTML = "<span class='request-label'>Reason:</span> " + selectedRequest.reason;
                popupContent.querySelector(".request-date-time").innerHTML = "<span class='request-label'>When:</span> " + selectedRequest.start_date + " - " + selectedRequest.end_date;
                popupContent.querySelector(".request-description").innerHTML = "<span class='request-label'>Description:</span> " + selectedRequest.description;
                popupContent.querySelector("input[name='requestId']").value = requestId;

                // Display the popup
                popupOverlay.style.display = "block";
            }
        });
    });

    btnClose.addEventListener("click", function (e) {
        popupOverlay.style.display = "none";
    });

    btnDecline.addEventListener("click", function (e) {
        popupOverlay.style.display = "none";
    });

    btnApprove.addEventListener("click", function (e) {
        popupOverlay.style.display = "none";
    });

    // Update the JavaScript code to send the selected user's ID to the report.php page
    const userSelector = document.querySelector("#userSelector");
    const reportBtn = document.querySelector(".report-btn");

    reportBtn.addEventListener("click", function (e) {
        const selectedUserId = userSelector.value;

        if (selectedUserId) {
            window.location.href = `report.php?userId=${selectedUserId}`;
        } else {
            window.location.href = `report.php`;
        }
    });
</script>

</html>