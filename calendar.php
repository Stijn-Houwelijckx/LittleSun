<?php
include_once (__DIR__ . "/classes/User.php");
include_once (__DIR__ . "/classes/Db.php");
include_once (__DIR__ . "/classes/CalendarItem.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', 'error.log');

session_start();

$current_page = 'calendar';

$pdo = Db::getInstance();
$user = User::getUserById($pdo, $_SESSION["user_id"]);

if (isset($_SESSION["user_id"]) && $user["typeOfUser"] == "employee") {
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
$currentMonth = isset($_GET['month']) ? $_GET['month'] : date('m'); // get the month from the query string or default to the current month
$allDaysThisMonth = generateDaysForMonth($currentYear, $currentMonth);

$date = new DateTime($allDaysThisMonth[0]);
$dayOfWeek = $date->format('N');

$emptyDays = array_fill(0, $dayOfWeek - 1, '');
array_unshift($allDaysThisMonth, ...$emptyDays);

$myCalendarItems = CalendarItem::getMyCalendarAsEmployee($pdo, $_SESSION["user_id"]);

$groupedCalendarItems = [];
foreach ($myCalendarItems as $calendarItem) {
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
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.png">

    <style>
        div#days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            grid-gap: 10px;
        }

        div#month {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            grid-gap: 10px;
        }

        div.day {
            display: flex;
            flex-direction: column;
            gap: 16px;
            border: 1px solid #ccc;
            padding: 10px;
            min-height: 100px; /* Stel een minimale hoogte in voor de dag */
        }

        div.day p.calendarItem {
            margin: 0; /* Verwijder de marge van de paragraaf om ongewenste ruimte te voorkomen */
        }
    </style>
</head>

<body>
    <?php include_once ('inc/nav.inc.php'); ?>
    <div id="calendar">
        <div class="btns">
            <a href="" class="btn active">Daily</a>
            <a href="" class="btn">Weekly</a>
            <a href="" class="btn">Monthly</a>
        </div>      
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
                        <?php foreach ($groupedCalendarItems[$dayKey] as $item): ?>
                            <p class="calendarItem">
                                <?php echo $item["start_time"] ?> - <?php echo $item["event_description"] ?>
                            </p>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
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

