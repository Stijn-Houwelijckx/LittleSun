<?php
include_once (__DIR__ . "../../classes/Db.php");
include_once (__DIR__ . "../../classes/User.php");
include_once (__DIR__ . "../../classes/Location.php");
session_start();
$current_page = 'locations';

$pdo = Db::getInstance();
$user = User::getUserById($pdo, $_SESSION["user_id"]);

if (isset($_SESSION["user_id"]) && $user["typeOfUser"] == "admin") {
    try {
        $pdo = Db::getInstance();
        $user = User::getUserById($pdo, $_SESSION["user_id"]);
        $hubLocations = Location::getAll($pdo);
        if (isset($_POST["delete"])) {
            try {
                foreach ($_POST["delete"] as $id => $value) {
                    Location::deleteLocation($pdo, $id);
                }
                header("Location: {$_SERVER['PHP_SELF']}");
                exit();
            } catch (Exception $e) {
                error_log('Database error: ' . $e->getMessage());
            }
        }
    } catch (Exception $e) {
        error_log('Database error: ' . $e->getMessage());
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
    <div id="hubLocationsAdmin">
        <div class="row">
            <h1>Hub locations</h1>
            <a href="addHubLocation.php" class="btn">+ add</a>
        </div>
        <div class="hubLocations">
            <?php foreach ($hubLocations As $hubLocation) : ?>
                <div class="hubLocation">
                    <div class="hubLocationControls">
                        <a href="editHubLocation.php?hubLocation=<?php echo $hubLocation["id"]; ?>">
                            <i class="fa fa-edit"></i>
                        </a>

                        <form action="" method="post">
                            <label for="delete[<?php echo $hubLocation["id"] ?>]"><i class="fa fa-trash"></i></label>
                            <input hidden type="submit" name="delete[<?php echo $hubLocation["id"] ?>]" id="delete[<?php echo $hubLocation["id"] ?>]">
                        </form>
                    </div>
                    <div class="image" style="background-image: url('../assets/images/locations/<?php echo str_replace(' ', '', $hubLocation["image"]); ?>');"></div>
                    <div class="text">
                        <h2><?php echo $hubLocation["name"] ?></h2>
                        <p><?php echo $hubLocation["city"] ?></p>
                        <p><?php echo $hubLocation["country"] ?></p>
                    </div>
                </div>
            <?php endforeach ?>
            </div>
    </div>
</body>

</html>