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

if (isset($_GET["employee"])) {
    $employee_id = intval($_GET["employee"]);
    if (isset($_POST["submitTask"])) {
        try {
            $selectedTasks = [];
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'Task_') === 0) {
                    $taskId = intval($value);
                    $selectedTasks[] = $taskId;
                    // Update task for user
                    var_dump($employee_id);
                    var_dump($taskId);
                    Task::addTaskToUser($pdo, $employee_id, $taskId);
                }
            }
            var_dump($selectedTasks);
        } catch (Exception $e) {
            error_log('Database error: ' . $e->getMessage());
        }
    }
}




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
    <div id="hubworkerDetails">
        <h1>Mijn opdrachten</h1>
        <div class="tasks">
            <form action="" method="post">
                <?php foreach ($allTasks as $allTask): ?>
                    <div>
                        <input type="checkbox" id="task_<?php echo $allTask["id"]; ?>" name="Task_<?php echo $allTask["id"]; ?>" value="<?php echo $allTask["id"]; ?>">
                        <label for="task_<?php echo $allTask["id"]; ?>"><?php echo htmlspecialchars($allTask["task"]); ?></label>
                    </div>
                <?php endforeach; ?>
                <input type="text" hidden name="<?php echo $allTask["id"]; ?>">
                <input type="submit" name="submitTask" class="btn" value="opslaan"></input>
            </form>
        </div>
    </div>
</body>

</html>