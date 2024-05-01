<?php
include_once (__DIR__ . "../../classes/Db.php");
include_once (__DIR__ . "../../classes/User.php");
include_once (__DIR__ . "../../classes/Manager.php");
include_once (__DIR__ . "../../classes/Task.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', 'error.log');

session_start();


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

if (isset($_POST["deletetask"])) {
    try {
        $selectedTask = Task::getTaskById($pdo, $_POST["deletetask"]);
        Task::deleteTask($pdo, $_POST["deletetask"]);
        Task::removeTaskTypesFromUsers($pdo, $_POST["deletetask"]);
        Task::deleteUserTasks($pdo);
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
    <div id="hubLocationsAdmin">
        <div class="row">
            <h1>Tasks</h1>
            <a href="addTask.php" class="btn">+ Toevoegen</a>
        </div>
        <form action="" method="post" id="taskSelector">
            <select name="task_select" onchange=submitTaskForm()>
                <option value="" disabled selected>--- Tasks ---</option>
                <?php foreach ($allTasks as $task): ?>
                    <option value="<?php echo $task["id"]; ?>" <?php if ($task["id"] == $selectedTask) echo "selected"; ?>>
                        <?php echo htmlspecialchars($task["task"]); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <div class="popupIsManager">
            <p>Do you really want to delete this task?</p>
            <div class="btns">
                <a href="#" class="close">No</a>
                <form action="" method="POST" id="deleteTaskFromUser">
                    <input type="text" name="deletetask" hidden value="<?php echo $selectedTask; ?>">
                    <button type="submit" class="btn deleteTask">Yes</button>
                </form>
            </div>
        </div>

        <div class="tasks">
            <button class="btn">Remove task</button>
        </div>
    </div>

    <script>
        function submitTaskForm() {
            document.getElementById("taskSelector").submit();
        }

        document.querySelector(".tasks .btn").addEventListener("click", function (e) {
            document.querySelector(".popupIsManager").style.display = "flex";
            document.querySelector(".popupIsManager .close").addEventListener("click", function (e) {
                document.querySelector(".popupIsManager").style.display = "none";
            });
        });

        document.querySelector(".deleteTask").addEventListener("click", function (e) {
            document.querySelector("#deleteTaskFromUser").submit();
        });
    </script>
</body>