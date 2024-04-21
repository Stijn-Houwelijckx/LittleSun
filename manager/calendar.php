<?php
include_once (__DIR__ . "../../classes/User.php");
include_once (__DIR__ . "../../classes/Employee.php");
include_once (__DIR__ . "../../classes/Db.php");
include_once (__DIR__ . "../../classes/CalendarItem.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', 'error.log');

session_start();

$current_page = 'calendar';

$pdo = Db::getInstance();
$user = User::getUserById($pdo, $_SESSION["user_id"]);

if (isset($_SESSION["user_id"]) && $user["typeOfUser"] == "manager") {
    $pdo = Db::getInstance();
    $user = User::getUserById($pdo, $_SESSION["user_id"]);

    try {

    } catch (Exception $e) {
        error_log('Database error: ' . $e->getMessage());
    }
} else {
    header("Location: ../login.php?error=notLoggedIn");
    exit();
}

function generateDaysForMonth($year, $month) {
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $days = [];

    for ($day = 1; $day <= $daysInMonth; $day++) {
        $days[] = sprintf('%04d-%02d-%02d', $year, $month, $day);
    }

    return $days;
}

$currentYear = date('Y');
$currentMonth = isset($_GET['month']) ? $_GET['month'] : date('m');
$allDaysThisMonth = generateDaysForMonth($currentYear, $currentMonth);

$date = new DateTime($allDaysThisMonth[0]);
$dayOfWeek = $date->format('N');

$emptyDays = array_fill(0, $dayOfWeek - 1, '');
array_unshift($allDaysThisMonth, ...$emptyDays);

$allCalendarItems = CalendarItem::getAllEmployees($pdo, $user["location_id"]);
$allEmployeesByLocation = Employee::getAllEmployeesByLocation($pdo, $user["location_id"]);

$groupedCalendarItems = [];
foreach ($allCalendarItems as $calendarItem) {
    $date = new DateTime($calendarItem["start_time"]);
    $day = $date->format('Y-m-d');
    $groupedCalendarItems[$day][] = $calendarItem;
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
    <div id="calendar">
        <div class="btns">
            <a href="" class="btn active daily">Daily</a>
            <a href="" class="btn weekly">Weekly</a>
            <a href="" class="btn monthly">Monthly</a>
            <a href="addCalendarItem.php" class="btn big">+ Add agendaItem</a>  
        </div>  
        <div class="dailyview"></div>

        <div class="weeklyview"></div>  

        <div class="monthlyview">   
            <div id="top">
                <i class="fa fa-angle-left" id="prevMonth"></i>
                <h2><?php echo date('F', strtotime('2000-' . $currentMonth . '-01')); ?></h2>
                <i class="fa fa-angle-right" id="nextMonth"></i>
            </div>
            <div id="days">
                <h3>Mon</h3>
                <h3>Tue</h3>
                <h3>Wed</h3>
                <h3>Thu</h3>
                <h3>Fri</h3>
                <h3>Sat</h3>
                <h3>Sun</h3>
            </div>
            <div id="month">
                <?php foreach ($allDaysThisMonth as $day): ?>
                    <?php 
                        $date = new DateTime($day); 
                        $dayKey = $date->format('Y-m-d');
                        $totalItems = count($groupedCalendarItems[$dayKey] ?? []);
                    ?>
                    <div class="day" style="min-height: <?php echo $totalItems * 30 + 100 ?>px;">
                        <p><?php echo $date->format('d'); ?></p>
                        <?php if (isset($groupedCalendarItems[$dayKey])): ?>
                            <?php foreach ($groupedCalendarItems[$dayKey] as $index => $item): ?>
                                <?php 
                                    $red = ($index * 70) % 256;
                                    $green = ($index * 120) % 256;
                                    $blue = ($index * 170) % 256;

                                    $itemColor = "rgb($red, $green, $blue)";
                                ?>
                                <p class="calendarItem" style="background-color: <?php echo $itemColor; ?>">
                                    <?php echo $item["start_time"] ?> - <?php echo $item["event_description"] ?>
                                </p>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="legenda">
                <?php foreach ($allEmployeesByLocation as $index => $employee): ?>
                    <?php 
                        $red = ($index * 70) % 256;
                        $green = ($index * 120) % 256;
                        $blue = ($index * 170) % 256;

                        $userColor = "rgb($red, $green, $blue)";
                    ?>
                    <div class="employee">
                        <p class="color" style="background-color: <?php echo $userColor; ?>"></p>
                        <p><?php echo $employee["firstname"] . " " . $employee["lastname"]?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
    document.querySelector(".weekly").addEventListener("click", function(e){
        toggleActiveBtns(this);
        e.preventDefault();
        showView(".weeklyview");
        hideView(".monthlyview");
        hideView(".dailyview");
    });

    document.querySelector(".monthly").addEventListener("click", function(e){
        toggleActiveBtns(this);
        e.preventDefault();
        showView(".monthlyview");
        hideView(".weeklyview");
        hideView(".dailyview");
    });

    function toggleActiveBtns(clickedBtn) {
        let activeBtns = document.querySelectorAll(".btns .active");
        activeBtns.forEach(btn => {
            btn.classList.remove("active");
        });
        clickedBtn.classList.add("active");
    }

    function showView(selector) {
        document.querySelector(selector).style.display = "flex";
    }

    function hideView(selector) {
        document.querySelector(selector).style.display = "none";
    }

    document.addEventListener('DOMContentLoaded', function () {
        const currentMonth = <?php echo $currentMonth; ?>;
        const prevMonthBtn = document.getElementById('prevMonth');
        const nextMonthBtn = document.getElementById('nextMonth');

        prevMonthBtn.addEventListener('click', function () {
            const newMonth = currentMonth <= 1 ? 12 : currentMonth - 1;
            window.location.href = `?month=${newMonth}`;
        });

        nextMonthBtn.addEventListener('click', function () {
            const newMonth = currentMonth >= 12 ? 1 : currentMonth + 1;
            window.location.href = `?month=${newMonth}`;
        });

    });
</script>
</body>
</html>

