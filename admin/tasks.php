<?php
include_once (__DIR__ . "../../classes/Db.php");
include_once (__DIR__ . "../../classes/User.php");
include_once (__DIR__ . "../../classes/Manager.php");
include_once (__DIR__ . "../../classes/Task.php");
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', 'error.log');

$current_page = 'tasks';

$pdo = Db::getInstance();
$user = User::getUserById($pdo, $_SESSION["user_id"]);
$selectedTask = Task::getTaskById($pdo, 1);

if (isset($_SESSION["user_id"]) && $user["typeOfUser"] == "admin") {
    try {
        $pdo = Db::getInstance();
        $user = User::getUserById($pdo, $_SESSION["user_id"]);
    } catch (Exception $e) {
        error_log('Database error: ' . $e->getMessage());
    }
} else {
    header("Location: ../login.php?error=notLoggedIn");
    exit();
}

if (isset($_POST["task_select"])) {
    try {
        $selectedTask = Task::getTaskById($pdo, $_POST["task_select"]);
    } catch (Exception $e) {
        error_log('Database error: ' . $e->getMessage());
    }
}

if (isset($_POST["delete"])) {
    try {
        Task::deleteTask($pdo, $_POST["delete"]);
    } catch (Exception $e) {
        error_log('Database error: ' . $e->getMessage());
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
    <div id="usersAdmin">
        <h1>Tasks</h1>
        <form action="" method="post" id="taskSelector">
            <select name="task_select" onchange=submitTaskForm()>
                <?php foreach ($allTasks as $task): ?>
                    <option value="<?php echo $task["id"]; ?> <?php if ($task["id"] == $selectedTask["id"]) echo "selected"; ?>">
                        <?php echo htmlspecialchars($task["task"]); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <div class="tasks"></div>
            <form action="" method="post" id="removetask">
                <button type="submit" class="btn"><i class="fa fa-trash"></i> delete</button>
                <input hidden type="text" name="delete" value="<?php echo $selectedTask["id"] ?>">
            </form>
        </div>
    </div>

    <script>
        function submitTaskForm() {
            document.getElementById("taskSelector").submit();
        }
    </script>
</body>