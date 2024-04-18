<?php
include_once (__DIR__ . "../../classes/Db.php");
include_once (__DIR__ . "../../classes/User.php");
include_once (__DIR__ . "../../classes/Manager.php");
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', 'error.log');

$current_page = 'users';

$pdo = Db::getInstance();
$manager = User::getUserById($pdo, $_SESSION["user_id"]);

if (isset($_SESSION["user_id"]) && $manager["typeOfUser"] == "manager") {
    try {
        $pdo = Db::getInstance();
        $manager = User::getUserById($pdo, $_SESSION["user_id"]);
    } catch (Exception $e) {
        error_log('Database error: ' . $e->getMessage());
    }
} else {
    header("Location: ../login.php?error=notLoggedIn");
    exit();
}

$hubworkers = Manager::getHubWorkers($pdo, $manager["location_id"]);
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
    <div id="hubworkers">
        <h1>Hubworkers at <?php echo $hubworkers[0]["name"] ?></h1>
        <div class="hubs">
            <?php foreach ($hubworkers As $hubworker) : ?>
                <div class="hub">
                <p><?php echo $hubworker["firstname"]?></p>
                <p><?php echo $hubworker["lastname"]?></p>
                <p><?php echo $hubworker["email"]?></p>
                <p><?php echo $hubworker["name"]?></p>
                </div>
            <?php endforeach ?>
        </div>
    </div>
</body>

</html>