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

$current_page = 'reports';

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
    </div>
</body>

<script>
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