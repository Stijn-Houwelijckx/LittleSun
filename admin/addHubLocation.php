<?php
include_once (__DIR__ . "/../classes/Db.php");
include_once (__DIR__ . "/../classes/User.php");
include_once (__DIR__ . "/../classes/Location.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', 'error.log');

session_start();

$current_page = 'locations';

$pdo = Db::getInstance();
$user = User::getUserById($pdo, $_SESSION["user_id"]);

if (isset($_SESSION["user_id"]) && $user["typeOfUser"] == "admin") {
    try {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $location = new Location();

            $hubName = $_POST["name"];
            $image = $_FILES["image"]["name"];

            $name = $location->setName($hubName);

            if(!empty($_FILES["image"]["name"])) {
                $target_dir = "../assets/images/locations/";
                $target_file = $target_dir . basename($_FILES["image"]["name"]);
                $uploadOk = 1;
                $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
                $check = getimagesize($_FILES["image"]["tmp_name"]);
                if($check !== false) {
                    $uploadOk = 1;
                } else {
                    echo "the file is not an image.";
                    $uploadOk = 0;
                }
                if (file_exists($target_file)) {
                    echo "Sorry, the file already exists.";
                    $uploadOk = 0;
                }
                if ($_FILES["image"]["size"] > 5000000) {
                    echo "Sorry, the file is too large.";
                    $uploadOk = 0;
                }
                if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
                && $imageFileType != "gif" ) {
                    echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                    $uploadOk = 0;
                }
                if ($uploadOk == 1) {
                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                        // Location::addLocation($pdo, $_FILES["image"]["name"], $name);
                        $location->setImage($_FILES["image"]["name"]);

                        $location->addLocation($pdo);

                        header("Location: hubLocations.php");
                        exit();
                    } else {
                        echo "Sorry, there was an error uploading your file.";
                        exit();
                    }
                }
            }

            // Location::addLocation($pdo, "005677_05_121513.jpg", $name);
            $location->setImage("005677_05_121513.jpg");

            $location->addLocation($pdo);

            header("Location: hubLocations.php");
            exit();
        }
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
    } catch (Exception $e) {
        $error = $e->getMessage();
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
        <h1>Add hublocation</h1>
        <form action="" method="post" enctype="multipart/form-data">
            <div class="column">
                <label for="name">Name</label>
                <input type="text" name="name" id="name" required>
            </div>
            <div class="column">
                <label for="image">Image (not required)</label>
                <input type="file" name="image" id="image">
            </div>
            <?php if (isset($error)): ?>
                <p class="error-message"><?php echo $error; ?></p>
            <?php endif; ?>
            <button type="submit" class="btn">Submit</button>
        </form>
    </div>
</body>

</html>
