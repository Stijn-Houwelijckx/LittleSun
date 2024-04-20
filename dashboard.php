<?php
include_once (__DIR__ . "/classes/Db.php");
include_once (__DIR__ . "/classes/User.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', 'error.log');

session_start();

$current_page = 'home';

$pdo = Db::getInstance();
$user = User::getUserById($pdo, $_SESSION["user_id"]);

if (!isset($_SESSION["user_id"]) || $user["typeOfUser"] != "employee") {
    header("Location: login.php?notLoggedIn=true");
    exit();
}
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
    
    <div id="userDashboard">
        <div class="bento-grid">
            <div class="bento-item">
                <h2 class="bento-item-title">Request time off</h2>
                <div class="request">
                    <p class="request-reason"><span class="request-label">Reason:</span> Vacation</p>
                    <p class="request-date-time"><span class="request-label">When:</span> 12/08/2024 08:00 - 15/08/2024 08:00</p>
                    <p class="request-status"><span class="request-label">Status:</span> <span class="request-status-text request-status-pending">Pending</span></p>
                </div>
                <div class="request">
                    <p class="request-reason"><span class="request-label">Reason:</span> Vacation</p>
                    <p class="request-date-time"><span class="request-label">When:</span> 12/08/2024 08:00 - 15/08/2024 08:00</p>
                    <p class="request-status"><span class="request-label">Status:</span> <span class="request-status-text request-status-approved">Approved</span></p>
                </div>
                <div class="request">
                    <p class="request-reason"><span class="request-label">Reason:</span> Vacation</p>
                    <p class="request-date-time"><span class="request-label">When:</span> 12/08/2024 08:00 - 15/08/2024 08:00</p>
                    <p class="request-status"><span class="request-label">Status:</span> <span class="request-status-text request-status-declined">Declined</span></p>
                </div>
                <p>You don't have time off requests</p>
                <button class="btn bento-item-button">Request time off</button>
            </div>
        </div>
    </div>
</body>
</html>