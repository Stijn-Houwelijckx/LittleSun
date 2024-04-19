<?php
include_once (__DIR__ . "/../classes/Db.php");
include_once (__DIR__ . "/../classes/User.php");
include_once (__DIR__ . "/../classes/Location.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', 'error.log');

session_start();

$current_page = 'Locations';

$pdo = Db::getInstance();
$user = User::getUserById($pdo, $_SESSION["user_id"]);

if (isset($_SESSION["user_id"]) && $user["typeOfUser"] == "admin") {
    if (isset($_GET["hubLocation"])) {
        $location = Location::getLocationById($pdo, $_GET["hubLocation"]);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        try {
            // $name = $_POST["name"];
            // $city = $_POST["city"];
            // $country = $_POST["country"];

            $location = new Location;

            $location -> setName($_POST["name"]);
            $location -> setCity($_POST["city"]);
            $location -> setCountry($_POST["country"]);

            $location -> updateLocation($pdo, $_GET["hubLocation"]);

            header("Location: hubLocations.php");
            exit();
        } catch (Exception $e) {
            error_log('Database error: ' . $e->getMessage());
        }
    }
} else {
    header("Location: ../login.php?error=notLoggedIn");
    exit();
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
    <div id="addHubLocation">
        <h1>Edit hublocation</h1>
        <form action="" method="post" enctype="multipart/form-data">
            <div class="column">
                <label for="name">Name</label>
                <input type="text" name="name" value="<?php echo $location["name"] ?>" required>
            </div>
            <div class="column">
                <label for="city">City</label>
                <input type="text" name="city" value="<?php echo $location["city"] ?>" required>
            </div>
            <div class="column">
                <label for="country">Country</label>
                <input type="text" name="country" value="<?php echo $location["country"] ?>" required>
            </div>
            <button type="submit" class="btn">Submit</button>
        </form>
    </div>
</body>

</html>
