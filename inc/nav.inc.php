<?php 
    include_once (__DIR__ . "../../classes/User.php");
    include_once (__DIR__ . "../../classes/Db.php");


    $pdo = Db::getInstance();
    $user = User::getUserById($pdo, $_SESSION["user_id"]);

    $role = $user["typeOfUser"];
    
    if ($role == "admin" || $role == "manager") {
        $pathExtention = "../";
    } else {
        $pathExtention = "";
    }
?>  

<nav class="desktopNav">
    <div class="column">
        <div class="top">
            <div class="logo" style="background-image: url('<?php echo $pathExtention ?>assets/images/favicon.png');"></div>
            <p class="border"></p>
            <div class="role">
                <p><?php echo $role ?></p>
            </div>
        </div>
        <div class="menu">
            <?php if ($role == "admin") : ?>
                <a href="dashboard.php">
                    <div>
                        <img class="<?php echo ($current_page == 'home') ? 'homeItem active' : 'homeItem'; ?>"
                            src="../assets/icons/Home.svg" alt="home">
                        <p>Home</p>
                    </div>
                </a>
                <a href="hubLocations.php">
                    <div>
                        <img class="<?php echo ($current_page == 'locations') ? 'locationsItem active' : 'locationsItem'; ?>"
                            src="../assets/icons/location.svg" alt="locations">
                        <p>Hublocations</p>
                    </div>
                </a>
                <a href="users.php">
                    <div>
                        <img class="<?php echo ($current_page == 'users') ? 'usersItem active' : 'usersItem'; ?>"
                            src="../assets/icons/users.svg" alt="users">
                        <p>Users</p>
                    </div>
                </a>
                <a href="tasks.php">
                    <div>
                        <img class="<?php echo ($current_page == 'tasks') ? 'usersItem active' : 'usersItem'; ?>"
                            src="../assets/icons/task.svg" alt="tasks">
                        <p>Tasks</p>
                    </div>
                </a>
            <?php endif ?>

            <?php if ($role == "manager") : ?>
                <a href="dashboard.php">
                    <div>
                        <img class="<?php echo ($current_page == 'home') ? 'homeItem active' : 'homeItem'; ?>"
                            src="../assets/icons/Home.svg" alt="home">
                        <p>Home</p>
                    </div>
                </a>
                <a href="employees.php">
                    <div>
                        <img class="<?php echo ($current_page == 'employees') ? 'usersItem active' : 'usersItem'; ?>"
                            src="../assets/icons/users.svg" alt="users">
                        <p>Employees</p>
                    </div>
                </a>
                <a href="hubworkers.php">
                    <div>
                        <img class="<?php echo ($current_page == 'hubworkers') ? 'usersItem active' : 'usersItem'; ?>"
                            src="../assets/icons/employee.svg" alt="Employees">
                        <p>Employees</p>
                    </div>
                </a>
                <a href="calendar.php">
                    <div>
                        <img class="<?php echo ($current_page == 'calendar') ? 'usersItem active' : 'usersItem'; ?>"
                            src="../assets/icons/calendar.svg" alt="Calendar">
                        <p>Calendar</p>
                    </div>
                </a>
            <?php endif ?>

            <?php if ($role == "employee") : ?>
                <a href="calendar.php">
                    <div>
                        <img class="<?php echo ($current_page == 'calendar') ? 'usersItem active' : 'usersItem'; ?>"
                            src="assets/icons/calendar.svg" alt="Calendar">
                        <p>Calendar</p>
                    </div>
                </a>
            <?php endif; ?>
                        
        </div>
        <div class="settings">
            <a href="<?php echo $pathExtention ?>logout.php">
                <div>
                    <img class="logoutItem" src="<?php echo $pathExtention ?>assets/icons/logout.svg" alt="logout">
                    <p>Logout</p>
                </div>
            </a>
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