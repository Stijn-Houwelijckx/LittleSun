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

    if (isset($_POST["deleteUser"])) {
        try {
            User::deleteUser($pdo, $_POST["deleteUser"]);
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
            <a href="addHubManager.php" class="btn">+ Add</a>
        </div>
        <div class="users">
            <?php if (!empty($users)): ?>
                <form action="" method="post" id="userForm">
                    <?php foreach ($users As $user): ?>
                        <div class="user">
                            <p><?php echo $user["firstname"] ?></p>
                            <p><?php echo $user["lastname"] ?></p>
                            <p><?php echo $user["email"] ?></p>
                            <p><?php echo $user["typeOfUser"] ?></p>
                            <i class="fa fa-edit" onclick="openPopup('<?php echo $user['id']; ?>', '<?php echo $user['firstname']; ?>', '<?php echo $user['lastname']; ?>', '<?php echo $user["email"]; ?>')"></i>
                            <i class="fa fa-trash" onclick="openDeletePopup('<?php echo $user['id']; ?>', '<?php echo $user['firstname']; ?>', '<?php echo $user['lastname']; ?>')"></i>
                        </div>
                    <?php endforeach ?>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div id="editPopup" class="popup">
        <div class="popup-content">
            <span class="close" onclick="closePopup()">&times;</span>
            <h2>Edit Employee</h2>
            <form action="" method="post">
                <div class="column">
                    <label for="firstname">First Name:</label>
                    <input type="text" name="firstname" id="firstname">
                </div>
                <div class="column">
                    <label for="lastname">Last Name:</label>
                    <input type="text" name="lastname" id="lastname">
                </div>
                <div class="column">
                    <label for="email">Email:</label>
                    <input type="text" name="email" id="email">
                </div>
                <div class="column">
                    <label for="typeOfUser">Type of User:</label>
                    <select name="typeOfUser" id="typeOfUser">
                        <option value="admin">Admin</option>
                        <option value="manager">Manager</option>
                        <option value="employee">Employee</option>
                    </select>
                </div>
                <input type="hidden" name="user_id" id="user_id">
                <button type="submit" class="btn">Save</button>
            </form>
        </div>
    </div>

    <div id="deletePopup" class="popup">
        <div class="popup-content">
            <span class="close" onclick="closeDeletePopup()">&times;</span>
            <h2>Delete User</h2>
            <p>Are you sure you want to delete user <span id="deleteUserFirstname"></span> <span id="deleteUserLastname"></span>?</p>
            <form action="" method="post" id="deleteUserForm">
                <input type="hidden" name="deleteUser" id="deleteUserId">
                <button type="submit" class="btn">Yes, delete</button>
                <button type="button" class="btn" onclick="closeDeletePopup()">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        function submitUserForm() {
            document.getElementById("userSelector").submit();
        }

        document.querySelector(".users .remove").addEventListener("click", function (e) {
            document.querySelector(".popupIsManager").style.display = "flex";
            document.querySelector(".popupIsManager .close").addEventListener("click", function (e) {
                document.querySelector(".popupIsManager").style.display = "none";
            });
        });

        <?php if ($selectedUser != null): ?>
            document.querySelector("#checkboxTypeOfUser").addEventListener("change", function (e) {
                document.querySelector(".popupIsManager").style.display = "flex";
                document.querySelector(".popupIsManager .close").addEventListener("click", function (e) {
                    document.querySelector(".popupIsManager").style.display = "none";
                    document.querySelector("#checkboxTypeOfUser").checked ^= 1;
                });
                e.preventDefault();
            });
        <?php endif; ?>
            
        document.querySelector(".deleteUser").addEventListener("click", function (e) {
            document.querySelector("#changeTypeOfUser").submit();
        });

        function openPopup(id, firstname, lastname, email, typeOfUser) {
            document.getElementById("user_id").value = id;
            document.getElementById("firstname").value = firstname;
            document.getElementById("lastname").value = lastname;
            document.getElementById("email").value = email;
            document.getElementById("typeOfUser").value = typeOfUser; // Stel het juiste type gebruiker in
            document.getElementById("editPopup").style.display = "block";
        }

        function openDeletePopup(id, firstname, lastname) {
            document.getElementById("deleteUserId").value = id;
            document.getElementById("deleteUserFirstname").textContent = firstname;
            document.getElementById("deleteUserLastname").textContent = lastname;
            document.getElementById("deletePopup").style.display = "block";
        }

        function closePopup() {
            document.getElementById("editPopup").style.display = "none";
        }

        function closeDeletePopup() {
            document.getElementById("deletePopup").style.display = "none";
        }

        function saveChanges() {
            // Voer hier code uit om wijzigingen op te slaan
            closePopup();
        }
    </script>
</body>