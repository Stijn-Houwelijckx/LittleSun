<?php
include_once (__DIR__ . "../../classes/Db.php");
include_once (__DIR__ . "../../classes/User.php");
include_once (__DIR__ . "../../classes/Manager.php");
include_once (__DIR__ . "../../classes/Employee.php");
include_once (__DIR__ . "../../classes/Task.php");
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', 'error.log');

$current_page = 'hubworkers';

$pdo = Db::getInstance();
$manager = User::getUserById($pdo, $_SESSION["user_id"]);
$selectedTask = Task::getTaskById($pdo, 1);

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

$employees = Employee::getAllEmployees($pdo, $manager["location_id"]);
$allTasks =  Task::getAllTasks($pdo);
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
        <h1>Employees at my hublocation</h1>
        <div class="hubs">
            <?php foreach ($employees As $employee) : ?>
                <div class="hub">
                    <img src="../assets/images/<?php echo $employee["profileImg"]?>" alt="profileImg">
                    <p><?php echo $employee["firstname"]?></p>
                    <p><?php echo $employee["lastname"]?></p>
                    <a class="btn" href="hubworkerDetails.php?employee=<?php echo $employee["id"]; ?>">Assign task</a>     
                </div>
            <?php endforeach ?>
        </div>
    </div>
</body>

</html>