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
        if (isset($_POST["locationDeleteId"])) {
            var_dump($_POST["locationDeleteId"]);
            try {
                Location::deleteLocation($pdo, $_POST["locationDeleteId"]);
                // foreach ($_POST["locationDeleteId"] as $id) {
                //     Location::deleteLocation($pdo, $id);
                // }
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
            <div> 
                <input type="text" class="search" placeholder="Search hublocation">
                <a href="addHubLocation.php" class="btn">+ add</a>
            </div>
        </div>
        <div class="hubLocations">
            <?php if ($hubLocations == null) : ?>
                <p>No hub locations found</p>
            <?php endif; ?>
            <?php foreach ($hubLocations As $hubLocation) : ?>
                <div class="hubLocation" data-locationid="<?php echo $hubLocation["id"] ?>">
                    <div class="hubLocationControls">
                        <a href="editHubLocation.php?hubLocation=<?php echo $hubLocation["id"]; ?>">
                            <i class="fa fa-edit"></i>
                        </a>
                        <button class="remove"><i class="fa fa-trash"></i></button>
                    </div>
                    <div class="image" style="background-image: url('../assets/images/locations/<?php echo str_replace(' ', '', $hubLocation["image"]); ?>');"></div>
                    <div class="text">
                        <h2><?php echo $hubLocation["name"] ?></h2>
                    </div>
                </div>
                <?php endforeach ?>
            </div>
            <div class="pop-up-overlay">
                <div class="location-delete-popup">
                    <button class="btn-close"><i class="fa fa-window-close-o"></i></button>
                    <p>Do you really want to delete this hublocation?</p>
                <div class="row">
                    <form class="form-btns" action="" method="post">
                        <input type="hidden" name="locationDeleteId" value="">
                        <div class="btn-container">
                            <a href="#" class="btn btn-decline">No</a>
                            <button type="submit" class="btn btn-approve" name="delete">Yes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const hubLocations = document.querySelectorAll(".hubLocation");
        const removeBtns = document.querySelectorAll(".remove");
        const popupOverlay = document.querySelector(".pop-up-overlay");
        const popup = document.querySelector(".location-delete-popup");
        const btnClose = document.querySelector(".btn-close");
        const btnDecline = document.querySelector(".btn-decline");
        const btnApprove = document.querySelector(".btn-approve");

        removeBtns.forEach(function (removeBtn) {
            removeBtn.addEventListener("click", function (e) {
                popupOverlay.style.display = "block";
                popup.style.display = "block";

                const locationId = removeBtn.closest(".hubLocation").getAttribute("data-locationid");
                popup.querySelector("input[name='locationDeleteId']").value = locationId;
            });
        });

        btnClose.addEventListener("click", function (e) {
            popupOverlay.style.display = "none";
        });

        btnDecline.addEventListener("click", function (e) {
            popupOverlay.style.display = "none";
        });

        btnApprove.addEventListener("click", function (e) {
            popupOverlay.style.display = "none";
        });

        // ======================================================================================== //

        document.querySelector("#hubLocationsAdmin .search").addEventListener("keyup", function(e){
            let searchTerm = e.target.value.toLowerCase();
            let hubLocations = document.querySelectorAll(".hubLocation");

            hubLocations.forEach(function(hubLocation) {
                let hubName = hubLocation.querySelector("h2").textContent.toLowerCase();
                // let hubCity = hubLocation.querySelector("p:nth-child(2)").textContent.toLowerCase();
                // let hubCountry = hubLocation.querySelector("p:nth-child(3)").textContent.toLowerCase();

                if (hubName.includes(searchTerm))
                // || hubCity.includes(searchTerm) || hubCountry.includes(searchTerm)) 
            {
                    hubLocation.style.display = "block";
                } else {
                    hubLocation.style.display = "none";
                }
            });
        });

    </script>
</body>

</html>