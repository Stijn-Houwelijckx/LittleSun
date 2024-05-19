<?php
session_start();
session_unset();

include_once (__DIR__ . "/classes/User.php");
include_once (__DIR__ . "/classes/Db.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', 'error.log');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = filter_input(INPUT_POST, 'password');

    if ($email && $password) {
        $pdo = Db::getInstance();
        $user = User::getUserByEmail($pdo, $email);

        if ($user && password_verify($password, $user['password'])) {
            // Gebruiker succesvol ingelogd
            $_SESSION['user_id'] = $user['id'];
            if ($user["typeOfUser"] == "admin") {
                header("Location: admin/hubLocations.php"); // Stuur door naar het dashboard of een andere pagina
                exit();
            } 
            if($user["typeOfUser"] == "manager") {
                header("Location: manager/dashboard.php"); // Stuur door naar het dashboard of een andere pagina
                exit();
            } else {
                header("Location: dashboard.php"); // Stuur door naar het dashboard of een andere pagina
                exit();
            }
        } else {
            $error = "Firstname or password is not correct.";
        }
    } else {
        $error = "Fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>LittleSun - Login</title>
    <link rel="stylesheet" href="https://use.typekit.net/kqy0ynu.css" />
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>" />
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>

<body>
    <div id="login">
        <div class="text">
            <h1>Sign in</h1>
            <p>We just need some information to log you in </p>

            <form class="form form--login" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <div class="column">
                    <label for="email">E-mail</label>
                    <input type="text" id="email" name="email" placeholder="JohnDoe@gmail.com">
                </div>
                <div class="column passwordInput">
                    <label for="password">Password</label>
                    <div class="row">
                        <input type="password" id="password" name="password" placeholder="password">
                        <i class="fa fa-eye-slash"></i>
                    </div>
                </div>
                <?php if (isset($error)): ?>
                <p class="error-message">
                    <?php echo $error ?>
                </p>
            <?php endif; ?>
                <button type="submit" class="btn" id="btnsignup">Sign in</button>
            </form>
            <div class="row">
            </div>
        </div>
        <div class="image"></div>
    </div>

    <script>
        var eyeIcons = document.querySelectorAll('.fa-eye-slash');
        eyeIcons.forEach(function (eyeIcon) {
            eyeIcon.addEventListener('click', function () {
                var passwordInput = this.parentElement.querySelector('input[type="password"], input[type="text"]');
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    this.classList.remove('fa-eye-slash');
                    this.classList.add('fa-eye');
                } else {
                    passwordInput.type = 'password';
                    this.classList.remove('fa-eye');
                    this.classList.add('fa-eye-slash');
                }
            });
        });
    </script>
</body>

</html>