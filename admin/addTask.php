<?php
include_once (__DIR__ . "../../classes/User.php");
include_once (__DIR__ . "../../classes/Db.php");
include_once (__DIR__ . "../../classes/Task.php");
include_once (__DIR__ . "../../classes/Admin.php");
session_start();
if (isset($_POST['submit'])) {
    $pdo = Db::getInstance();
    $task = new Task;
    $task->setTask($_POST['taskName']);
    $task->addTask($pdo);
}
?>

<!DOCTYPE html>
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
    <div>
    <form method="POST" action="">
    <label for="taskName">Task Name:</label>
    <input type="text" name="taskName" id="taskName" required>
    <button type="submit" name="submit">Add Task</button>
    </form>
    </div>
</body>

</html>