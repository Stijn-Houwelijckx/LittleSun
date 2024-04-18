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
        <select>
            <?php foreach ($allTasks as $task): ?>
                <option value="<?php echo $task["id"] ?>">
                    <?php echo htmlspecialchars($task["task"]) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <div class="users"></div>
            <?php if (!empty($users)): ?>
                <form action="" method="post" id="taskForm">
                    <div class="user">
                        <div class="text">
                            <div class="column">
                            <label for="task">Task:</label>
                            <input type="text" name="task" id="task" value="">
                            </div>
                            
                            
                        </div>
                    </div>
                    <div class="buttons">
                        <button type="submit" class="btn">Opslaan</button>
                    </div>
                </form>

                
            <?php endif; ?>
            <form action="" method="post" id="removetask"><button class="btn">Verwijderen</button></form>
        </div>
    </div>

</body>