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
            var_dump($_POST["delete"]);
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
                <div class="hubLocation">
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

                <div class="popupIsManager">
                    <p>Do you really want to delete this hublocation?</p>
                    <div class="btns">
                        <a href="#" class="close">No</a>
                        <form action="" method="POST" id="deleteHublocation">
                            <input type="text" name="delete[<?php echo $hubLocation["id"] ?>]" hidden value="<?php echo $hubLocation["id"] ?>>">
                            <button type="submit" class="btn deleteHublocation">Yes</button>
                        </form>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
        </div>
    </div>

    <script>
        <?php if ($hubLocations != null): ?>
            document.querySelectorAll(".hubLocation .remove").forEach(function(element) {
                element.addEventListener("click", function (e) {
                    // Verkrijg de ouder hubLocation van het verwijderen knopelement
                    var hubLocation = e.target.closest(".hubLocation");
                    
                    // Verkrijg de bijbehorende popupIsManager voor deze hubLocation
                    var popupIsManager = document.querySelector(".popupIsManager");
                    
                    // Toon de popup
                    popupIsManager.style.display = "flex";
                    
                    // Voeg event listener toe aan de close knop van de popup
                    popupIsManager.querySelector(".close").addEventListener("click", function (e) {
                        // Verberg de popup
                        popupIsManager.style.display = "none";
                    });
                });
            });

            document.querySelector("#deleteHublocation").addEventListener("click", function (e) {
                document.querySelector("#deleteHublocation").submit();
            });
        <?php endif; ?>

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