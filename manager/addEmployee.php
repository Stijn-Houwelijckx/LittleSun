<?php
include_once (__DIR__ . "../../classes/Db.php");
include_once (__DIR__ . "../../classes/User.php");
include_once (__DIR__ . "../../classes/Manager.php");
include_once (__DIR__ . "../../classes/Task.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', 'error.log');

session_start();

$current_page = 'employees';

$pdo = Db::getInstance();
$manager = User::getUserById($pdo, $_SESSION["user_id"]);

if (!isset($_SESSION["user_id"]) || $manager["typeOfUser"] != "manager") {
    header("Location: login.php?notLoggedIn=true");
    exit();
}

if (isset($_POST['firstname'], $_POST['lastname'], $_POST['email'], $_POST['password'])){
    $user = new User;

    try {
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $email = $_POST['email'];
        $password = $_POST['password'];

        $user->setFirstname($firstname);
        $user->setLastname($lastname);
        $user->setEmail($email);
        $user->setPassword($password);
        $user->setLocation_id($manager["location_id"]);

        $newUserId = $user->addUser($pdo, "employee");

        if ($newUserId) {
            $user->addToLocation($pdo, $newUserId);
            Task::linkTasksToUser($pdo, $newUserId); // Link tasks to the new user
            // header("Location: employees.php");
            header("Location: hubworkers.php");
            exit();
        }
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User</title>
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="icon" type="image/x-icon" href="../assets/images/favicon.png">
</head>
<body>
    <?php include_once ('../inc/nav.inc.php'); ?>
    <div id="usersAdmin">
        <h1>Add new employee</h1>
        <div class="users">
            <form action="" method="post" id="userForm">
                <div class="user">
                    <div class="text">
                        <div class="column">
                            <label for="firstname">Firstname:</label>
                            <input type="text" name="firstname" id="firstname" placeholder="Firstname" required>
                        </div>
                        <div class="column">
                            <label for="lastname">Lastname:</label>
                            <input type="text" name="lastname" id="lastname" placeholder="Lastname" required>
                        </div>
                        <div class="column">
                            <label for="email">E-mail:</label>
                            <input type="text" name="email" id="email" placeholder="Email" required>
                        </div>
                        <div class="column">
                            <label for="password">Password:</label>
                            <input type="password" name="password" id="password" placeholder="Password" required>
                        </div>
                    </div>
                </div>
                <?php if (isset($error)): ?>
                    <p class="error"><?php echo $error; ?></p>
                <?php endif; ?>
                <div class="buttons">
                    <button type="submit" class="btn">Add employee</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

<?php
// Function to check if the string contains special characters
function containsSpecialChars($str) {
    return preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $str);
}