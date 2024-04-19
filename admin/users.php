<?php
include_once (__DIR__ . "../../classes/Db.php");
include_once (__DIR__ . "../../classes/User.php");
include_once (__DIR__ . "../../classes/Manager.php");
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', 'error.log');

$current_page = 'users';

$pdo = Db::getInstance();
$user = User::getUserById($pdo, $_SESSION["user_id"]);
$selectedUser = User::getUserById($pdo, 0);

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

            $user->updateUser($pdo, $_POST["user_id"], $_POST['typeOfUser']);

            $selectedUser = User::getUserById($pdo, $_POST["user_id"]);
        } catch (Exception $e) {
            error_log('Database error: ' . $e->getMessage());
        }
    }
}

$users = User::getAll($pdo);
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
                            
                            <div class="row">
                                <input type="hidden" name="typeOfUser" value="user">
                                <label for="checkboxTypeOfUser">Manager:</label>
                                <input type="checkbox" name="typeOfUser" id="checkboxTypeOfUser" value="manager" <?php if ($selectedUser["typeOfUser"] == "manager") echo "checked"; ?>>
                            </div>
                        </div>
                    </div>
                    <div class="buttons">
                        <button type="submit" class="btn">Opslaan</button>
                    </div>
                </form>
                <div class="popupIsManager">
                    <p>Weet je zeker dat je deze gebruiker manager wilt maken?</p>
                    <div class="btns">
                        <a href="#" class="close">Nee</a>
                        <form action="" method="POST">
                            <input type="text" name="user_admin_id" hidden value="<?php echo $selectedUser["id"] ?>>">
                            <button type="button" class="btn confirm-admin">Ja</button>
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

        document.querySelector("#checkboxTypeOfUser").addEventListener("change", function (e) {
            if (this.checked) {
                document.querySelector(".popupIsManager").style.display = "flex";
                document.querySelector(".popupIsManager .close").addEventListener("click", function (e) {
                    document.querySelector(".popupIsManager").style.display = "none";
                    document.querySelector("#checkboxIsAdmin").checked = false;
                });

                e.preventDefault();
            }

            document.querySelector(".confirm-admin").addEventListener("click", function (e) {
                document.querySelector("#userForm").submit();
            });
        });

    </script>

</body>