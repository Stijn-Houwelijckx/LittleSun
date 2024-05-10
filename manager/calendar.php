<?php
include_once (__DIR__ . "../../classes/Db.php");
include_once (__DIR__ . "../../classes/User.php");
include_once (__DIR__ . "../../classes/Employee.php");
include_once (__DIR__ . "../../classes/Task.php");
include_once (__DIR__ . "../../classes/CalendarItem.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', 'error.log');

session_start();

$current_page = 'calendar';

$pdo = Db::getInstance();
$user = User::getUserById($pdo, $_SESSION["user_id"]);
$manager = User::getUserById($pdo, $_SESSION["user_id"]);

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

$selectedUser = User::getUserById($pdo, 1);

if (isset($_POST['eventDatePicker'], $_POST['event_title'], $_POST['event_description'])) {
    $calendarItem = new CalendarItem;
    try {
        $event_date = $_POST['eventDatePicker'];
        $employeeId = $_POST['userSelector'];
        $taskId = $_POST['taskSelector'];
        $event_title = $_POST['event_title'];
        $event_description = $_POST['event_description'];
        
        $selectedTimeslots = $_POST['timeslots']; // Haal de geselecteerde tijdsloten op
        $calendarItem->setEvent_date($event_date);
        $calendarItem->setEvent_title($event_title);
        $calendarItem->setEvent_description($event_description);
        $calendarItem->setEvent_location($manager["location_id"]);

        $newCalendaritem = $calendarItem->addCalendarItem($pdo, $employeeId, $taskId, $selectedTimeslots);
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
    }
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

$allCalendarItems = CalendarItem::getAllCalenderItems($pdo, $user["location_id"]);
$allEmployeesByLocation = Employee::getAllEmployeesByLocation($pdo, $user["location_id"]);

$groupedCalendarItems = [];

$userColors = [];
foreach ($allDaysThisMonth as $day) {
    $groupedCalendarItems[$day] = [];
    foreach ($allEmployeesByLocation as $employee) {
        $userId = $employee["id"];
        // Genereer een kleur voor de gebruiker
        $red = ($userId * 70) % 256;
        $green = ($userId * 120) % 256;
        $blue = ($userId * 170) % 256;
        $userColors[$userId] = "rgb($red, $green, $blue)";
    }
}


foreach ($allCalendarItems as $calendarItem) {
    $date = new DateTime($calendarItem["event_date"]);
    $day = $date->format('Y-m-d');
    $groupedCalendarItems[$day][] = $calendarItem;
}


$taskTypes = Task::getAllTasks($pdo);
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
            <a href="" class="btn big">+ Add agendaItem</a>  
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
                            <?php foreach ($groupedCalendarItems[$dayKey] as $item): ?>
                                <?php 
                                    $userId = $item["user_id"];
                                    $itemColor = $userColors[$userId];
                                ?>
                                <p class="calendarItem" style="background-color: <?php echo $itemColor; ?>">
                                <?php $time = strtotime($item["start_time"]); $time_formatted = date('H:i', $time); echo $time_formatted; ?>
                                - 
                                <?php $time = strtotime($item["end_time"]); $time_formatted = date('H:i', $time); echo $time_formatted; ?>
                                :
                                <?php echo $item["task"] ?>
                                </p>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No calendar items for this day.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="legenda">
                <?php foreach ($allEmployeesByLocation as $employee): ?>
                    <?php 
                        $userId = $employee["id"];
                        $userColor = $userColors[$userId];
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
                                    <p class="calendarItem" style="background-color: <?php echo $itemColor; ?>">
                                        <?php echo $item["start_time"] ?> - <?php echo $item ?>
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
                                <p class="calendarItem" style="background-color: <?php echo $itemColor; ?>">
                                    <?php echo $item["start_time"] ?> - <?php echo $item ?>
                                </p>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="legenda">
                <?php foreach ($allEmployeesByLocation as $index => $employee): ?>
                    <div class="employee">
                        <p class="color" style="background-color: <?php echo $userColor; ?>"></p>
                        <p><?php echo $employee["firstname"] . " " . $employee["lastname"]?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="popupAddCalendarItem">
        <i class="fa fa-plus"></i>
        <form action="" method="post" id="addCalendarItem">
            <div class="text">
                <div class="column">
                    <label for="eventDatePicker">Event_date:</label>
                    <input type="date" name="eventDatePicker" id="eventDatePicker" placeholder="Event_date">
                </div>
                <div class="column">
                    <label for="userSelector">Select user:</label>
                    <select name="userSelector" id="userSelector" disabled>
                        <option value="" disabled selected>--- select user ---</option>
                    </select>
                </div>
                <div class="column">
                    <label for="taskSelector">Select task:</label>
                    <select name="taskSelector" id="taskSelector" disabled>
                        <option value="" disabled selected>--- select task ---</option>
                    </select>
                </div>
                <div class="row">
                    <div class="column">
                        <p>Timeslots:</p>
                        <div>
                            <input type="checkbox" name="timeslots[]" class="timeslot" id="timeslot_1" value="08:00 - 09:00" disabled>
                            <label for="timeslot_1">08:00 - 09:00</label>
                        </div>
                        <div>
                            <input type="checkbox" name="timeslots[]" class="timeslot" id="timeslot_2" value="09:00 - 10:00" disabled>
                            <label for="timeslot_2">09:00 - 10:00</label>
                        </div>
                        <div>
                            <input type="checkbox" name="timeslots[]" class="timeslot" id="timeslot_3" value="10:00 - 11:00" disabled>
                            <label for="timeslot_3">10:00 - 11:00</label>
                        </div>
                        <div>
                            <input type="checkbox" name="timeslots[]" class="timeslot" id="timeslot_4" value="11:00 - 12:00" disabled>
                            <label for="timeslot_4">11:00 - 12:00</label>
                        </div>
                        <div>
                            <input type="checkbox" name="timeslots[]" class="timeslot" id="timeslot_5" value="12:00 - 13:00" disabled>
                            <label for="timeslot_5">12:00 - 13:00</label>
                        </div>
                        <div>
                            <input type="checkbox" name="timeslots[]" class="timeslot" id="timeslot_6" value="13:00 - 14:00" disabled>
                            <label for="timeslot_6">13:00 - 14:00</label>
                        </div>
                    </div>
                    <div class="column">
                        <div>
                            <input type="checkbox" name="timeslots[]" class="timeslot" id="timeslot_7" value="14:00 - 15:00" disabled>
                            <label for="timeslot_7">14:00 - 15:00</label>
                        </div>
                        <div>
                            <input type="checkbox" name="timeslots[]" class="timeslot" id="timeslot_8" value="15:00 - 16:00" disabled>
                            <label for="timeslot_8">15:00 - 16:00</label>
                        </div>
                        <div>
                            <input type="checkbox" name="timeslots[]" class="timeslot" id="timeslot_9" value="16:00 - 17:00" disabled>
                            <label for="timeslot_9">16:00 - 17:00</label>
                        </div>
                        <div>
                            <input type="checkbox" name="timeslots[]" class="timeslot" id="timeslot_10" value="17:00 - 18:00" disabled>
                            <label for="timeslot_10">17:00 - 18:00</label>
                        </div>
                        <div>
                            <input type="checkbox" name="timeslots[]" class="timeslot" id="timeslot_11" value="18:00 - 19:00" disabled>
                            <label for="timeslot_11">18:00 - 19:00</label>
                        </div>
                        <div>
                            <input type="checkbox" name="timeslots[]" class="timeslot" id="timeslot_12" value="19:00 - 20:00" disabled>
                            <label for="timeslot_12">19:00 - 20:00</label>
                        </div>
                        <div>
                            <input type="checkbox" name="timeslots[]" class="timeslot" id="timeslot_13" value="20:00 - 21:00" disabled>
                            <label for="timeslot_13">20:00 - 21:00</label>
                        </div>
                    </div>
                </div>
                <div class="column">
                    <label for="event_title">Event_title:</label>
                    <input type="text" name="event_title" id="event_title" placeholder="Event_title">
                </div>
                <div class="column">
                    <label for="event_description">Event_description:</label>
                    <textarea name="event_description" id="event_description" placeholder="Event_description"></textarea>
                </div>
            </div>
            <div class="buttons">
                <button type="submit" class="btn">Save</button>
            </div>
        </form>
    </div>

    <input type="hidden" id="currentDateInput" value="<?php echo $today->format('Y-m-d'); ?>">

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
        document.querySelector(".big").addEventListener("click", function(e){
            document.querySelector(".popupAddCalendarItem").style.display = "flex";
            e.preventDefault();
            document.querySelector(".popupAddCalendarItem .fa-plus").addEventListener("click", function(e){
                document.querySelector(".popupAddCalendarItem").style.display = "none";
            });
        });
    </script>
    <script>const groupedCalendarItems = <?php echo json_encode($groupedCalendarItems); ?>;
    </script>
    <script src="../javascript/calendar.js"></script>
    <script src="../javascript/addCalendarItem.js"></script>
</body>
</html>

