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

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php?error=notLoggedIn");
    exit();
}

$pdo = Db::getInstance();
$manager = User::getUserById($pdo, $_SESSION["user_id"]);

if ($manager["typeOfUser"] != "manager") {
    header("Location: ../login.php?error=notManager");
    exit();
}

$employee_id = isset($_GET["employee"]) ? intval($_GET["employee"]) : 0;

if (isset($_POST["submitTask"])) {
    try {
        $selectedTasks = [];
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'Task_') === 0) {
                $taskId = intval($value);
                if ($taskId > 0) {
                    $selectedTasks[] = $taskId;
                    Task::addTaskToUser($pdo, $employee_id, $taskId);
                    var_dump($employee_id);
                }
            }
        }
    } catch (Exception $e) {
        error_log('Database error: ' . $e->getMessage());
    }
}

$allTasks = Task::getAllTasks($pdo);

// Haal de taken op die de werknemer heeft
$employeeTasks = Task::getTasksByEmployeeId($pdo, $employee_id);

// Bouw een array van taak-ID's die de werknemer heeft
$employeeTaskIds = array_column($employeeTasks, 'id');

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
    <div id="hubworkerDetails">
        <h1>Mijn opdrachten</h1>
        <div class="tasks">
            <form action="" method="post">
                <?php foreach ($allTasks as $task): ?>
                    <div>
                        <input type="checkbox" id="task_<?php echo $task["id"]; ?>" name="Task_<?php echo $task["id"]; ?>" value="<?php echo $task["id"]; ?>" <?php if (in_array($task["id"], $employeeTaskIds)) echo 'checked'; ?>>
                        <label for="task_<?php echo $task["id"]; ?>"><?php echo htmlspecialchars($task["task"]); ?></label>
                    </div>
                <?php endforeach; ?>
                <input type="submit" name="submitTask" class="btn" value="opslaan"></input>
            </form>
        </div>
    </div>
</body>

</html>
