<?php
include_once (__DIR__ . "../../classes/Db.php");
include_once (__DIR__ . "../../classes/User.php");
include_once (__DIR__ . "../../classes/Employee.php");
include_once (__DIR__ . "../../classes/Task.php");
include_once (__DIR__ . "../../classes/CalendarItem.php");
include_once (__DIR__ . "../../classes/SickLeave.php");
include_once (__DIR__ . "../../classes/WorkEntry.php");

// Zet error reporting om ongewenste meldingen uit te schakelen
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', 'error.log');

date_default_timezone_set('Europe/Brussels');

// // Fetch users by availability AJAX
// if (isset($_POST['eventDatePicker'])) {
//     // Fetch users
// }

// // Fetch tasks by user AJAX
// if (isset($_POST['userSelector'])) {
//     // Fetch tasks
// }

// // Validate event form submission
// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     $errors = [];

//     // Check if the required fields are filled out
//     if (empty($_POST['eventDatePicker']) || empty($_POST['userSelector']) || empty($_POST['taskSelector']) || empty($_POST['timeslots'])) {
//         $errors[] = "You didn't fill out all the fields.";
//     }

//     // If there are no errors, process the input
//     if (empty($errors)) {
//         // Process form submission
//     } else {
//         // Display errors
        
//     }
// }

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

if (isset($_POST['eventDatePicker'])) {
    $calendarItem = new CalendarItem;
    try {
        $event_date = $_POST['eventDatePicker'];
        $employeeId = $_POST['userSelector'];
        $taskId = $_POST['taskSelector'];
        
        $selectedTimeslots = $_POST['timeslots'] ?? []; // Haal de geselecteerde tijdsloten op
        $calendarItem->setEvent_date($event_date) ?? null;
        $calendarItem->setEvent_location($manager["location_id"]) ?? null;

        $newCalendaritem = $calendarItem->addCalendarItem($pdo, $employeeId, $taskId, $selectedTimeslots);

        // Add a work_entry

        // Get the start time from the first timeslot
        $start_time = explode(' - ', reset($selectedTimeslots))[0];

        // Get the end time from the last timeslot
        $end_time = explode(' - ', end($selectedTimeslots))[1];

        // Convert start and end times to DateTime objects
        $start_datetime = DateTime::createFromFormat('H:i', $start_time);
        $end_datetime = DateTime::createFromFormat('H:i', $end_time);

        // Calculate the difference between start and end times
        $time_diff = $start_datetime->diff($end_datetime);

        // Format the difference as HH:MM:SS
        $time_planned = $time_diff->format('%H:%I:%S');

        // Create a new work_entry
        $workEntry = new WorkEntry;

        // Set the user_id, task_id, event_date and time_planned
        $workEntry->setUser_id($employeeId);
        $workEntry->setTask_id($taskId);
        $workEntry->setEvent_date($event_date);
        $workEntry->setTimePlanned($time_planned);

        // Add the work_entry to the database
        $newWorkEntry = $workEntry->addWorkEntry($pdo);

    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
    }
}

function generateDaysForMonth($year, $month) {
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $days = [];
    
    // Start with the first day of the month
    $firstDayOfMonth = new DateTime(sprintf('%04d-%02d-%02d', $year, $month, 1));
    $dayOfWeek = $firstDayOfMonth->format('N'); // Get the day of the week (1 for Monday, 7 for Sunday)
    
    // Add days from the previous month to fill the beginning of the week
    if ($dayOfWeek != 1) {
        $firstDayOfMonth->modify('-' . ($dayOfWeek - 1) . ' days');
    }
    
    // Generate days for the current month including the days from the previous and next month to complete the weeks
    $currentDay = clone $firstDayOfMonth;
    while (count($days) < ($daysInMonth + ($dayOfWeek - 1) + (7 - (($daysInMonth + ($dayOfWeek - 1)) % 7)))) {
        $days[] = $currentDay->format('Y-m-d');
        $currentDay->modify('+1 day');
    }

    return $days;
}

$currentYear = date('Y');
$currentMonth = isset($_GET['month']) ? $_GET['month'] : date('m');
$allDaysThisMonth = generateDaysForMonth($currentYear, $currentMonth);

$date = new DateTime($allDaysThisMonth[0]);
$dayOfWeek = $date->format('N');

// No need to add empty days manually, they are now included in $allDaysThisMonth

$allCalendarItems = CalendarItem::getAllCalenderItems($pdo, $user["location_id"]);
$allEmployeesByLocation = Employee::getAllEmployeesByLocation($pdo, $user["location_id"]);

$groupedCalendarItems = [];

$userColors = [];
foreach ($allDaysThisMonth as $day) {
    $groupedCalendarItems[$day] = [];
    foreach ($allEmployeesByLocation as $employee) {
        $userId = $employee["id"];
        // Generate a color for the user
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
    <script src="../javascript/calendar.js"></script>
</head>

<body>
    <?php include_once ('../inc/nav.inc.php'); ?>
    <div id="calendar">
    <div class="btns">
    <a href="calendar.php?view=daily" class="btn <?php if (isset($_GET["view"])) { echo $_GET["view"] === "daily" ? "active" : ""; } ?> daily">Daily</a>
    <a href="calendar.php?view=weekly" class="btn <?php if (isset($_GET["view"])) { echo $_GET["view"] === "weekly" ? "active" : ""; } ?> weekly">Weekly</a>
    <a href="calendar.php?view=monthly" class="btn <?php if (isset($_GET["view"])) { echo $_GET["view"] === "monthly" ? "active" : ""; } ?> monthly">Monthly</a>
    <a href="" class="btn big">+ Add event</a>  

    <?php
    // Check if there are any errors to display
    if (!empty($errors)) {
        // Display errors within the btns div
        foreach ($errors as $error) {
            echo "<div class='error-message'>$error</div>";
        }
    }
    ?>
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

                                    <?php
                                        // If sick, show the sick leave

                                        $eventDateTime = new DateTime($item["event_date"] . " " . $item["start_time"]);

                                        $sickLeave = SickLeave::getSickLeaveByUserIdAndDate($pdo, $item["user_id"], $eventDateTime->format("Y-m-d H:i:s"));
                                        if ($sickLeave) {
                                            echo "<span style='color: white;'> - Sick leave: " . $sickLeave["reason"] . "</span>";
                                        }
                                    ?>
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
                <i class="fa fa-angle-left" id="prevWeekButton"></i>
                <div>
                    <h2 class="thisWeek">
                        <?php 
                            $today = new DateTime();
                            $startDate = clone $today;
                            $startDate->modify('last monday');
                            $endDate = clone $startDate;
                            $endDate->modify('next sunday');
                            echo $startDate->format('d F Y') . ' - ' . $endDate->format('d F Y');
                        ?>
                    </h2>
                    <h2 id="currentWeek"></h2>
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
                        <div class="day <?php echo $dayKey == $today->format('Y-m-d')? "current_day" : "" ?>" style="min-height: <?php echo $totalItems * 30 + 250 ?>px;">
                            <p><?php echo $startDate->format('d'); ?></p>
                            <?php if (isset($groupedCalendarItems[$dayKey])): ?>
                                <?php foreach ($groupedCalendarItems[$dayKey] as $index => $item): ?>
                                    <?php 
                                        $userId = $item["user_id"];
                                        $itemColor = $userColors[$userId]; // Kleur van de gebruiker
                                    ?>
                                    <p class="calendarItem" style="background-color: <?php echo $itemColor; ?>">
                                        <?php $time = strtotime($item["start_time"]); $time_formatted = date('H:i', $time); echo $time_formatted; ?>
                                        - 
                                        <?php $time = strtotime($item["end_time"]); $time_formatted = date('H:i', $time); echo $time_formatted; ?>
                                        :
                                        <?php echo $item["task"] ?>
                                    </p>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    <?php 
                        $startDate->modify('+1 day');
                    } ?>
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
        <div class="monthlyview">   
            <div id="top">
                <i class="fa fa-angle-left" id="prevMonth"></i>
                <div>
                    <h2 id="currentMonth"></h2>
                </div>
                <i class="fa fa-angle-right" id="nextMonth"></i>
            </div>
            <div id="monthItems"> <!-- Voeg deze container toe -->
                <?php foreach ($allDaysThisMonth as $day): ?>
                    <?php 
                        $date = new DateTime($day); 
                        $dayKey = $date->format('Y-m-d');
                        $totalItems = count($groupedCalendarItems[$dayKey] ?? []);
                    ?>
                    <div class="day <?php echo $dayKey == $today->format('Y-m-d')? "current_day" : "" ?>" style="min-height: <?php echo $totalItems * 30 + 100 ?>px;">
                        <input type="hidden" class="currentDateInput" value="<?php echo $dayKey; ?>"> <!-- Gebruik een klasse in plaats van een id -->
                        <p><?php echo $date->format('d'); ?></p>
                        <?php if (isset($groupedCalendarItems[$dayKey])): ?>
                            <?php foreach ($groupedCalendarItems[$dayKey] as $index => $item): ?>
                                <?php 
                                    $userId = $item["user_id"];
                                    $itemColor = $userColors[$userId]; // Kleur van de gebruiker
                                ?>
                                <p class="calendarItem" style="background-color: <?php echo $itemColor; ?>">
                                    <?php $time = strtotime($item["start_time"]); $time_formatted = date('H:i', $time); echo $time_formatted; ?>
                                    - 
                                    <?php $time = strtotime($item["end_time"]); $time_formatted = date('H:i', $time); echo $time_formatted; ?>
                                    :
                                    <?php echo $item["task"] ?>
                                </p>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="legenda">
                <?php foreach ($allEmployeesByLocation as $index => $employee) : ?>
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
        </div>
        <div class="buttons">
            <button type="submit" class="btn">Save</button>
        </div>
        <div class="error-message" id="errorMessage" style="color: red; display: none;"></div>
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
<script>
  const groupedCalendarItems = <?php echo json_encode($groupedCalendarItems); ?>;
</script>

    <script src="../javascript/addCalendarItem.js"></script>
</body>
</html>

