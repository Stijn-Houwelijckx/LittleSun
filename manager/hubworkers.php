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

$employees = Employee::getAllEmployeesByLocation($pdo, $manager["location_id"]);
$allTasks =  Task::getAllTasks($pdo);
$myTasks = Task::mytasks($pdo, $_SESSION["user_id"]);

// Haal de locatie van de ingelogde manager op
$managerLocationId = $manager["location_id"];

// Haal alle werknemers op die bij dezelfde locatie als de manager zijn
$employees = Employee::getAllEmployeesByLocation($pdo, $managerLocationId);

// Maak een array aan om de taken van alle werknemers op te slaan
$allEmployeeTasks = array();

// Loop door alle werknemers en haal hun taken op
foreach ($employees as $employee) {
    $employeeId = $employee["id"];
    // Haal taken op voor de specifieke werknemer
    $employeeTasks = Task::mytasks($pdo, $employeeId);
    // Voeg de taken toe aan de array, gebruik de werknemer-ID als sleutel
    $allEmployeeTasks[$employeeId] = $employeeTasks;
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
    <div id="hubworkers">
        <div class="row">
            <h1>Employees at my hublocation</h1>
            <a href="addEmployee.php" class="btn">+ Add</a>
        </div>
        <div class="hubs">
            <?php foreach ($employees as $employee) : ?>
                <div class="hub">
                    <div class="name-container">
                        <img src="../assets/images/<?php echo $employee["profileImg"] ?>" alt="profileImg">
                        <p><?php echo $employee["firstname"] ?></p>
                        <p><?php echo $employee["lastname"] ?></p>
                    </div>
                    <?php
                    // Haal taken op voor deze werknemer
                    $employeeId = $employee["id"];
                    $employeeTasks = $allEmployeeTasks[$employeeId];
                    ?>
                    <?php if (count($employeeTasks) > 0) : ?>
                        <p>
                            <?php 
                            // Maak een array van alle taaknamen
                            $taskNames = array_column($employeeTasks, 'task');
                            // Voeg de taaknamen samen tot één string met een komma ertussen
                            echo implode(', ', $taskNames);
                            ?>
                        </p>
                    <?php else : ?>
                        <p>This employee doesn't have any tasks</p>
                    <?php endif ?>
                    <a class="btn" href="hubworkerDetails.php?employee=<?php echo $employee["id"]; ?>">Edit user</a>     
                </div>
            <?php endforeach ?>
        </div>
    </div>
</body>

</html>