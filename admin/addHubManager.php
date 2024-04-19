<?php
include_once (__DIR__ . "../../classes/Db.php");
include_once (__DIR__ . "../../classes/User.php");
include_once (__DIR__ . "../../classes/Manager.php");
include_once (__DIR__ . "../../classes/Task.php");
include_once (__DIR__ . "../../classes/Location.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', 'error.log');

session_start();

$current_page = 'users';

$pdo = Db::getInstance();
$manager = User::getUserById($pdo, $_SESSION["user_id"]);

if (!isset($_SESSION["user_id"]) || $manager["typeOfUser"] != "admin") {
    header("Location: login.php?notLoggedIn=true");
    exit();
}

$locations = Location::getAll($pdo);

if (isset($_POST['firstname'], $_POST['lastname'], $_POST['email'], $_POST['password'])){
    $user = new User;

    try {
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $location_id = $_POST['locations'];
    
        $user->setFirstname($firstname);
        $user->setLastname($lastname);
        $user->setEmail($email);
        $user->setPassword($password);
        $user->setLocation_id($location_id);
    
        $newUserId = $user->addUser($pdo, "manager");
        
        if ($newUserId) {
            $user->addToLocation($pdo, $newUserId);
            header("Location: users.php");
            exit();
        }
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
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
        <h1>Add new manager</h1>
        <div class="users">
            <form action="" method="post" id="userForm">
                <div class="user">
                    <div class="text">
                        <div class="column">
                            <label for="firstname">Firstname:</label>
                            <input type="text" name="firstname" id="firstname" required>
                        </div>
                        <div class="column">
                            <label for="lastname">Lastname:</label>
                            <input type="text" name="lastname" id="lastname" required>
                        </div>
                        <div class="column">
                            <label for="email">E-mail:</label>
                            <input type="text" name="email" id="email" required>
                        </div>
                        <div class="column">
                            <label for="password">Password:</label>
                            <input type="password" name="password" id="password" required>
                        </div>

                        <div class="column">
                            <label for="locations">Hub location:</label>
                            <select name="locations" id="locations">
                                <?php foreach ($locations as $location): ?>
                                    <option value="<?php echo $location["id"] ?>">
                                        <?php echo htmlspecialchars($location["name"]) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="buttons">
                    <button type="submit" class="btn">Add manager</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>