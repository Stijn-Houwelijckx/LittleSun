<?php
include_once (__DIR__ . "../../classes/Db.php");
include_once (__DIR__ . "../../classes/User.php");
include_once (__DIR__ . "../../classes/Manager.php");
include_once (__DIR__ . "../../classes/Employee.php");
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', 'error.log');

$current_page = 'users';

$pdo = Db::getInstance();
$user = User::getUserById($pdo, $_SESSION["user_id"]);
$selectedUser = null;

if (isset($_SESSION["user_id"]) && $user["typeOfUser"] == "admin") {
    try {
        $pdo = Db::getInstance();
        $user = User::getAll($pdo);

    } catch (Exception $e) {
        error_log('Database error: ' . $e->getMessage());
    }
} else {
    header("Location: ../login.php?error=notLoggedIn");
    exit();
}


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

            if (!empty($_POST['password'])) {
                $user->setPassword($_POST['password']);
                $user->updatePassword($pdo, $_POST["user_id"]);
            }

            $user->updateUser($pdo, $_POST["user_id"], $_POST['typeOfUser']);

            $selectedUser = User::getUserById($pdo, $_POST["user_id"]);
        } catch (Exception $e) {
            error_log('Database error: ' . $e->getMessage());
        }
    }

    if (isset($_POST["changeTypeOfUser"])) {
        try {
            User::updateTypeOfUser($pdo, $_POST["user_id"], $_POST["changeTypeOfUser"]);
            $selectedUser = User::getUserById($pdo, $_POST["user_id"]);
        } catch (Exception $e) {
            error_log('Database error: ' . $e->getMessage());
        }
    }
}

// $users = User::getAll($pdo);
$employees = Employee::getAllEmployees($pdo);
$managers = Manager::getAllManagers($pdo);
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
            <h1>users</h1>
            <a href="addHubManager.php" class="btn">+ Toevoegen</a>
        </div>
        <form action="" id="userSelector" onchange="submitUserForm()" method="post">
            <div class="column">
                <label for="user_id">Select a user:</label>
                <select name="user_id" id="user_id">
                    <option value="" disabled <?php if ($selectedUser == null) echo "selected"; ?>>--- Managers ---</option>
                    <?php foreach ($managers as $manager): ?>
                        <option value="<?php echo $manager["id"] ?>" <?php if ($selectedUser != null && $manager["id"] == $selectedUser["id"]) echo "selected"; ?>>
                            <?php echo htmlspecialchars($manager["firstname"]) . " " . htmlspecialchars($manager["lastname"]) ?>
                        </option>
                    <?php endforeach; ?>
                    <option value="" disabled>--- Employees ---</option>
                    <?php foreach ($employees as $employee): ?>
                        <option value="<?php echo $employee["id"] ?>" <?php if ($selectedUser != null &&  $employee["id"] == $selectedUser["id"]) echo "selected"; ?>>
                            <?php echo htmlspecialchars($employee["firstname"]) . " " . htmlspecialchars($employee["lastname"]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
        <div class="users">
            <?php if (!empty($managers || $employees) && $selectedUser != null): ?>
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
                                <input type="hidden" name="typeOfUser" id="typeOfUser" value="<?php echo htmlspecialchars($selectedUser["typeOfUser"]); ?>">
                            </div>
                            <div class="column">
                                <label for="password">Give new password if needed:</label>
                                <input type="password" name="password" id="password" value="" placeholder="New password">
                            </div>
                        </div>
                    </div>
                    <div class="buttons">
                        <button type="submit" class="btn">Opslaan</button>
                    </div>
                </form>
                <form action="" method="post" id="changeTypeOfUser">
                    <div class="row">
                        <label for="checkboxTypeOfUser">Manager:</label>
                        <input type="checkbox" id="checkboxTypeOfUser" <?php if ($selectedUser["typeOfUser"] == "manager") echo "checked"; ?>>
                        <input type="text" name="changeTypeOfUser" value="<?php echo ($selectedUser["typeOfUser"] == "manager")? "employee" : "manager" ?>" hidden>
                        <input type="text" name="user_id" value="<?php echo $selectedUser["id"] ?>" hidden>
                    </div>
                </form>
                <div class="popupIsManager">
                    <p>Do you really want to change admin status?</p>
                    <div class="btns">
                        <a href="#" class="close">No</a>
                        <form action="" method="POST">
                            <input type="text" name="user_id" hidden value="<?php echo $selectedUser["id"] ?>>">
                            <button type="button" class="btn confirm-admin">Yes</button>
                        </form>
                    </div>
                </div>
                <!-- <button class="btn remove">Verwijderen</button> -->
            <?php endif; ?>
        </div>
    </div>


    <script>
        function submitUserForm() {
            document.getElementById("userSelector").submit();
        }

        // document.querySelector(".users .remove").addEventListener("click", function (e) {
        //     document.querySelector(".popup").style.display = "flex";
        //     document.querySelector(".popup .close").addEventListener("click", function (e) {
        //         document.querySelector(".popup").style.display = "none";
        //     });
        // });

        <? if ($selectedUser != null): ?>
            document.querySelector("#checkboxTypeOfUser").addEventListener("change", function (e) {
                // if (this.checked) {
                    document.querySelector(".popupIsManager").style.display = "flex";
                    document.querySelector(".popupIsManager .close").addEventListener("click", function (e) {
                        document.querySelector(".popupIsManager").style.display = "none";
                        document.querySelector("#checkboxTypeOfUser").checked ^= 1;
                    });

                    e.preventDefault();
                // }

                document.querySelector(".confirm-admin").addEventListener("click", function (e) {
                    document.querySelector("#changeTypeOfUser").submit();
                });
            });
        <? endif; ?>

    </script>

</body>