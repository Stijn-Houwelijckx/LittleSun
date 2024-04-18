<?php
include_once (__DIR__ . "../../classes/Db.php");
include_once (__DIR__ . "../../classes/User.php");
include_once (__DIR__ . "../../classes/Manager.php");

session_start();

$pdo = Db::getInstance();
$manager = User::getUserById($pdo, $_SESSION["user_id"]);

if (!isset($_SESSION["user_id"]) && $manager["typeOfUser"] != "manager") {
    header("Location: login.php?notLoggedIn=true");
    exit();
}

 if (isset($_POST['firstname']) && isset($_POST['lastname']) && isset($_POST['email']) && isset($_POST['password'])){
    $user = new User;

    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $location = $_POST['location'];

    $user->setFirstname($firstname);
    $user->setLastname($lastname);
    $user->setTypeOfUser("employee");
    $user->setEmail($email);
    $user->setPassword($password);
    $user->setLocation($manager["location"]);

    $user->addUser($pdo);

    header("Location: users.php");
    exit();
 }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="icon" type="image/x-icon" href="../assets/images/favicon.png">
</head>
<body>
<form action="" method="post" id="userForm">
                    <div class="user">
                        <div class="text">
                            <div class="column">
                            <label for="firstname">Firstname:</label>
                            <input type="text" name="firstname" id="firstname">
                            </div>
                            <div class="column">
                            <label for="lastname">Lastname:</label>
                            <input type="text" name="lastname" id="lastname">
                            </div>
                            <div class="column">
                            <label for="email">E-mail:</label>
                            <input type="text" name="email" id="email" >
                            </div>
                            <div class="column">
                            <label for="password">password:</label>
                            <input type="text" name="password" id="password" >
                            </div>
                            </div>
                    </div>
                    <div class="buttons">
                        <button type="submit" class="btn">Opslaan</button>
                    </div>
                </form> 
</body>
</html>