<?php
include_once (__DIR__ . "../../classes/Db.php");
include_once (__DIR__ . "../../classes/User.php");
include_once (__DIR__ . "../../classes/Manager.php");
include_once (__DIR__ . "../../classes/Employee.php");
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', 'error.log');

$current_page = 'employees';

$pdo = Db::getInstance();
$user = User::getUserById($pdo, $_SESSION["user_id"]);
$manager = User::getUserById($pdo, $_SESSION["user_id"]);

if (!isset($_SESSION["user_id"]) || $manager["typeOfUser"] != "manager") {
    header("Location: ../login.php?notLoggedIn=true");
    exit();
}

$users = Employee::getAllEmployeesByLocation($pdo, $manager["location_id"]);
$selectedUser = $users[0];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["id"])) {
        try {
            User::deleteUser($pdo, $_POST["id"]);
            $selectedUser = User::getUserById($pdo, 0);
        } catch (Exception $e) {
            error_log('Database error: ' . $e->getMessage());
        }
    }

    if (isset($_POST["user_id"])) {
        try {
            $user_id = $_POST["user_id"];
            $selectedUser = User::getUserById($pdo, $user_id);

        } catch (Exception $e) {
            error_log('Database error: ' . $e->getMessage());
        }
    }

    if (isset($_POST["firstname"])) {
        try {
            $user = new User();
            $user->setFirstname($_POST['firstname']);
            $user->setLastname($_POST['lastname']);
            $user->setEmail($_POST['email']);
            $user->updateUser($pdo, $_POST["user_id"], "employee");

            $selectedUser = User::getUserById($pdo, $_POST["user_id"]);
        } catch (Exception $e) {
            error_log('Database error: ' . $e->getMessage());
        }
    }
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
    <div id="usersAdmin">
        <div class="row">
            <h1>Employees</h1>
            <a href="addEmployee.php" class="btn">+ Toevoegen</a>
        </div>
        <form action="" id="userSelector" onchange="submitUserForm()" method="post">
        <select name="user_id">
            <?php foreach ($users as $user): ?>
                <option value="<?php echo $user["id"] ?>" <?php if ($user["id"] == $selectedUser["id"]) echo "selected"; ?>>
                    <?php echo htmlspecialchars($user["firstname"]) . " " . htmlspecialchars($user["lastname"]) ?>
                </option>
            <?php endforeach; ?>
        </select>
        </form>
        <div class="users">
            <?php if (!empty($users)): ?>
                <form action="" method="post" id="userForm">
                    <div class="user">
                        <div class="text">
                            <div class="column">
                            <label for="firstname">Firstname:</label>
                            <input type="text" name="firstname" id="firstname" value="<?php echo htmlspecialchars($selectedUser["firstname"]); ?>">
                            </div>
                            <div class="column">
                            <label for="lastname">Lastname:</label>
                            <input type="text" name="lastname" id="lastname" value="<?php echo htmlspecialchars($selectedUser["lastname"]); ?>">
                            </div>
                            <div class="column">
                            <label for="email">E-mail:</label>
                            <input type="text" name="email" id="email" value="<?php echo htmlspecialchars($selectedUser["email"]); ?>">
                            </div>
                            <div class="column">
                            <input type="hidden" name="user_id" id="user_id" value="<?php echo htmlspecialchars($selectedUser["id"]); ?>">
                        </div>
                    </div>
                    <div class="buttons">
                        <button type="submit" class="btn">Opslaan</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>


    <script>
        function submitUserForm() {
            document.getElementById("userSelector").submit();
        }
    </script>

</body>