<?php 
    include_once (__DIR__ . "../../classes/User.php");
    include_once (__DIR__ . "../../classes/Db.php");
    $pdo = Db::getInstance();
    $user = User::getUserById($pdo, $_SESSION["user_id"]);
?>

<nav class="desktopNav">
    <div class="column">
        <div class="top">
            <div class="logo"></div>
            <p class="border"></p>
        </div>
        <div class="menu">
            <a href="dashboard.php">
            <div>
                <img class="<?php echo ($current_page == 'home') ? 'homeItem active' : 'homeItem'; ?>"
                    src="../assets/icons/Home.svg" alt="homeIcon">
                <p>Home</p>
            </div>
            </a>
            <?php if ($user["typeOfUser"] == "admin") : ?>
                <a href="hubLocations.php">
                <div>
                    <img class="<?php echo ($current_page == 'locations') ? 'locationsItem active' : 'locationsItem'; ?>"
                        src="../assets/icons/location.svg" alt="locationsIcon">
                    <p>Home</p>
                </div>
            </a>
            <a href="users.php">
                <div>
                    <img class="<?php echo ($current_page == 'users') ? 'usersItem active' : 'usersItem'; ?>"
                        src="../assets/icons/Users.svg" alt="usersIcon">
                    <p>Home</p>
                </div>
            </a>
            <?php endif ?>
        </div>
    </div>
    <div class="column center">
        <i class="fa fa-angle-double-right" aria-hidden="true"></i>
    </div>
</nav>
<script>
    function executeOnMaxWidth1200(callback) {
        const maxWidthQuery = window.matchMedia("(max-width: 1200px)");
        if (maxWidthQuery.matches) {
            callback();
        }
        maxWidthQuery.addListener((event) => {
            if (event.matches) {
                callback();
            }
        });
    }

    function executeOnMinWidth1200(callback) {
        const minWidthQuery = window.matchMedia("(min-width: 1200px)");
        if (minWidthQuery.matches) {
            callback();
        }
        minWidthQuery.addListener((event) => {
            if (event.matches) {
                callback();
            }
        });
    }

    executeOnMaxWidth1200(function () {
        let navIcon = document.querySelector(".desktopNav .center i");
        let desktopNav = document.querySelector(".desktopNav");
        let isOpen = false;

        navIcon.addEventListener("click", function (e) {
            if (!isOpen) {
                desktopNav.classList.add("open-desktopNav"); // Voeg de klasse toe om de animatie te starten
            } else {
                desktopNav.classList.remove("open-desktopNav"); // Verwijder de klasse om de animatie om te keren
            }
            isOpen = !isOpen;
        });
    });

    executeOnMinWidth1200(function () {
        let navIcon = document.querySelector(".desktopNav .center i");
        let desktopNav = document.querySelector(".desktopNav");
        let pTags = document.querySelectorAll(".desktopNav .column div div p");
        let desktopNavLogo = document.querySelector(".desktopNav .column .top .logo");
        let isOpen = false;

        navIcon.addEventListener("click", function (e) {
            if (!isOpen) {
                desktopNavLogo.style.backgroundImage = "url('assets/images/favicon.png')";
                desktopNavLogo.style.width = "128px";
                desktopNav.style.width = "200px";
                pTags.forEach(pTag => {
                    pTag.style.display = "flex";
                });
            } else {
                desktopNav.style.width = "120px";
                desktopNavLogo.style.backgroundImage = "url('assets/images/favicon.png')"; // Terugkeren naar standaard achtergrondafbeelding
                desktopNavLogo.style.width = "48px";
                pTags.forEach(pTag => {
                    pTag.style.display = "none";
                });
            }
            isOpen = !isOpen;
        });
    });



</script>