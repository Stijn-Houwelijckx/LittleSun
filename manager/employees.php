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

if (isset($_POST["firstname"])) {
    try {
        $user = new User();
        $user->setFirstname($_POST['firstname']);
        $user->setLastname($_POST['lastname']);
        $user->setEmail($_POST['email']);
        $user->updateUser($pdo, $_POST["user_id"], "employee");
    } catch (Exception $e) {
        error_log('Database error: ' . $e->getMessage());
    }
}

$users = Employee::getAllEmployeesByLocation($pdo, $manager["location_id"]);
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

    <style>
        .popup {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
            top: 50%;
            left: 50%
        }

        .popup-content {
            display: flex;
            flex-direction: column;
            gap: 16px;
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
        }

        .popup-content .column{
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .popup-content .column input{
            border: 1px solid var(--black);
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <?php include_once ('../inc/nav.inc.php'); ?>
    <div id="usersAdmin">
        <div class="row">
            <h1>Employees</h1>
            <a href="addEmployee.php" class="btn">+ Add</a>
        </div>
        <div class="error">
            <?php if (isset($error)) : ?>
                <p><?php echo $error; ?></p>
            <?php endif; ?>
        </div>
        <div class="employees">
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                    <div class="employee">
                        <p><?php echo $user["firstname"] ?></p>
                        <p><?php echo $user["lastname"] ?></p>
                        <p><?php echo $user["email"] ?></p>
                        <i class="fa fa-edit" onclick="openPopup('<?php echo $user['id']; ?>', '<?php echo $user['firstname']; ?>', '<?php echo $user['lastname']; ?>', '<?php echo $user["email"]; ?>')"></i>
                    </div>
                <?php endforeach; ?>
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
                <input type="hidden" name="user_id" id="user_id">
                <button type="submit" class="btn">Save</button>
            </form>
        </div>
    </div>


    <script>
        function submitUserForm() {
            document.getElementById("userSelector").submit();
        }

        function openPopup(id, firstname, lastname, email) {
            document.getElementById("user_id").value = id;
            document.getElementById("firstname").value = firstname;
            document.getElementById("lastname").value = lastname;
            document.getElementById("email").value = email;
            document.getElementById("editPopup").style.display = "block";
        }

        function closePopup() {
            document.getElementById("editPopup").style.display = "none";
        }

        function saveChanges() {
            // Voer hier code uit om wijzigingen op te slaan
            closePopup();
        }
    </script>

</body>