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
            <a href="calendar.php?view=daily" class="btn <?php if (isset($_GET["view"])) { echo $_GET["view"] === "daily" ? "active" : ""; } ?> daily">Daily</a>
            <a href="calendar.php?view=weekly" class="btn <?php if (isset($_GET["view"])) { echo $_GET["view"] === "weekly" ? "active" : ""; } ?> weekly">Weekly</a>
            <a href="calendar.php?view=monthly" class="btn <?php if (isset($_GET["view"])) { echo $_GET["view"] === "monthly" ? "active" : ""; } ?> monthly">Monthly</a>
            <a href="addCalendarItem.php" class="btn big">+ Add agendaItem</a>  
        </div>  

        <div class="dailyview">
            <div id="top">
                <i class="fa fa-angle-left" id="prevDay"></i>
                <div>
                    <h2 id="currentDate">
                        <?php 
                            $today = new DateTime();
                            echo $today->format('d F Y');
                        ?>
                    </h2>
                </div>
                <i class="fa fa-angle-right" id="nextDay"></i>
            </div>

            <div id="days">
                <h3 id="currentDay"><?php echo $today->format('D'); ?></h3>
            </div>

            <div id="day">
                <?php 
                    $startDate = new DateTime($_POST['date'] ?? $today->format('Y-m-d'));
                    $dayKey = $startDate->format('Y-m-d');
                    $totalItems = count($groupedCalendarItems[$dayKey] ?? []);
                ?>
                <div class="day" style="min-height: <?php echo $totalItems * 30 + 250 ?>px;">
                    <p><?php echo $startDate->format('d'); ?></p>
                    <div id="dayItems">
                        <?php if (isset($groupedCalendarItems[$dayKey]) && !empty($groupedCalendarItems[$dayKey])): ?>
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
                        <?php else: ?>
                            <p>No calendar items for this day.</p>
                        <?php endif; ?>
                    </div>
                </div>
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


        <div class="weeklyview">
            <div id="top">
                <i class="fa fa-angle-left" id="prevWeek"></i>
                <div>
                    <h2>
                        <?php 
                            $today = new DateTime();
                            $startDate = clone $today;
                            $startDate->modify('last monday');
                            $endDate = clone $startDate;
                            $endDate->modify('next sunday');
                            echo $startDate->format('d F Y') . ' - ' . $endDate->format('d F Y');
                        ?>
                    </h2>
                </div>
                <i class="fa fa-angle-right" id="nextWeek"></i>
            </div>

            <div id="days">
                <?php 
                    $today = new DateTime();
                    $startDate = clone $today;
                    $startDate->modify('last monday');

                    for ($i = 0; $i < 7; $i++) {
                        echo '<h3>' . $startDate->format('D') . '</h3>';
                        $startDate->modify('+1 day');
                    }
                ?>
            </div>

            <div id="week">
                <?php 
                    $startDate = new DateTime();
                    $startDate->modify('last monday');

                    $endDate = clone $startDate;
                    $endDate->modify('next sunday');

                    while ($startDate <= $endDate) {
                        $dayKey = $startDate->format('Y-m-d');
                        $totalItems = count($groupedCalendarItems[$dayKey] ?? []);
                ?>
                        <div class="day" style="min-height: <?php echo $totalItems * 30 + 250 ?>px;">
                            <p><?php echo $startDate->format('d'); ?></p>
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
                <?php 
                        $startDate->modify('+1 day');
                    }
                ?>
            </div>
        </div>

        <div class="monthlyview">   
            <div id="top">
                <i class="fa fa-angle-left" id="prevMonth"></i>
                <div>
                    <h2><?php echo date('F', strtotime('2000-' . $currentMonth . '-01')); ?></h2>
                    <h2>-</h2>
                    <h2><?php echo $currentYear; ?></h2>
                </div>
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
                        <input type="hidden" id="currentDateInput" value="<?php echo $today->format('Y-m-d'); ?>">
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

    <input type="hidden" id="currentDateInput" value="<?php echo $today->format('Y-m-d'); ?>">

    <script src="calendar.js"></script>
    <script>    
        <?php if ($_GET["view"] == "daily"): ?>
            document.querySelector(".dailyview").style.display = "flex";
            document.querySelector(".weeklyview").style.display = "none";
            document.querySelector(".monthlyview").style.display = "none";
        <?php endif; ?>

        <?php if ($_GET["view"] == "weekly"): ?>
            document.querySelector(".dailyview").style.display = "none";
            document.querySelector(".weeklyview").style.display = "flex";
            document.querySelector(".monthlyview").style.display = "none";
        <?php endif; ?>

        <?php if ($_GET["view"] == "monthly"): ?>
            document.querySelector(".dailyview").style.display = "none";
            document.querySelector(".weeklyview").style.display = "none";
            document.querySelector(".monthlyview").style.display = "flex";
        <?php endif; ?>
    </script>
    <script>const groupedCalendarItems = <?php echo json_encode($groupedCalendarItems); ?>;
</script>
</body>
</html>

