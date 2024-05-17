<?php
include_once(__DIR__ . "../../classes/Db.php");
include_once(__DIR__ . "../../classes/User.php");
include_once(__DIR__ . "../../classes/Manager.php");
include_once(__DIR__ . "../../classes/Employee.php");
include_once(__DIR__ . "../../classes/Task.php");

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

// $employee_id = isset($_GET["employee"]) ? intval($_GET["employee"]) : 0;

if (isset($_GET["employee"]) && $_GET["employee"] > 0) {
    $employee_id = intval($_GET["employee"]);
    // var_dump($employee_id);
} else {
    header("Location: hubworkers.php?error=invalidEmployee");
    exit();
}

// Fetch user details based on employee_id
$employee = User::getUserById($pdo, $employee_id);

if (isset($_POST["firstname"])) {
    try {
        $user = new User();
        $user->setFirstname($_POST['firstname']);
        $user->setLastname($_POST['lastname']);
        $user->setEmail($_POST['email']);
        $user->updateUser($pdo, $_POST["user_id"], "employee");

        $employee = User::getUserById($pdo, $employee_id);
    } catch (Exception $e) {
        error_log('Database error: ' . $e->getMessage());
    }
}

if (!$employee) {
    header("Location: hubworkers.php?error=invalidEmployee");
    exit();
}

$allTasks = Task::getAllTasks($pdo);

if (isset($_POST["submitTask"])) {
    try {
        foreach ($allTasks as $task) {
            $taskId = $task["id"];
            $isAssigned = isset($_POST["Task_$taskId"]) ? 1 : 0;
            Task::assignTaskToUser($pdo, $employee_id, $taskId, $isAssigned);
        }
        header("Location: hubworkers.php");
        exit();
    } catch (Exception $e) {
        error_log('Database error: ' . $e->getMessage());
    }
}

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
    <?php include_once('../inc/nav.inc.php'); ?>
    <div id="hubworkerDetails">
        <h1>Edit hubworker</h1>
        <div class="elements">
            <div class="editEmployee">
                <h2>Edit Employee</h2>
                <form action="" method="post">
                    <div class="column">
                        <label for="firstname">First Name:</label>
                        <input type="text" name="firstname" id="firstname" value="<?php echo htmlspecialchars($employee['firstname']); ?>">
                    </div>
                    <div class="column">
                        <label for="lastname">Last Name:</label>
                        <input type="text" name="lastname" id="lastname" value="<?php echo htmlspecialchars($employee['lastname']); ?>">
                    </div>
                    <div class="column">
                        <label for="email">Email:</label>
                        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($employee['email']); ?>">
                    </div>
                    <input type="hidden" name="user_id" id="user_id" value="<?php echo htmlspecialchars($employee['id']); ?>">
                    <button type="submit" class="btn">Save</button>
                </form>
            </div>
            <div class="tasks">
                <h2>Tasks</h2>
                <form action="" method="post">
                    <?php foreach ($allTasks as $task): ?>
                        <div>
                            <input type="checkbox" id="task_<?php echo $task["id"]; ?>" name="Task_<?php echo $task["id"]; ?>" value="<?php echo $task["id"]; ?>" <?php if (in_array($task["id"], $employeeTaskIds)) echo 'checked'; ?>>
                            <label for="task_<?php echo $task["id"]; ?>"><?php echo htmlspecialchars($task["task"]); ?></label>
                        </div>
                    <?php endforeach; ?>
                    <input type="submit" name="submitTask" class="btn" value="Save"></input>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
