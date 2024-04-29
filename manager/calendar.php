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

if (isset($_POST['taskSelector'])) {
    unset($_SESSION['selectedTaskId']);
    $_SESSION['selectedTaskId'] = $_POST['taskSelector'];
}

if (isset($_POST['userSelector'])) {
    $_SESSION['selectedUserId'] = $_POST['userSelector'];
}

if (isset($_SESSION['selectedTaskId'])){
    $selectedTaskId = intval($_SESSION['selectedTaskId']) ?? 1;
    $selectedTask = Task::getTaskById($pdo, $selectedTaskId);
    $allUsersByTaskTypeAndDate = CalendarItem::getAllUsersByTaskTypeAndEventDate($pdo, $selectedTaskId);
}


if (isset($_POST['event_date'], $_POST['event_title'], $_POST['event_description'], $_POST['start_time'], $_POST['end_time'])) {
    $calendarItem = new CalendarItem;
    try {
        $selectedTask = Task::getTaskById($pdo, $_POST["task_select"]);
        $event_date = $_POST['event_date'];
        $event_title = $_POST['event_title'];
        $event_description = $_POST['event_description'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];

        $calendarItem->setEvent_date($event_date);
        $calendarItem->setEvent_title($event_title);
        $calendarItem->setEvent_description($event_description);
        $calendarItem->setEvent_location($manager["location_id"]);
        $calendarItem->setStart_time($start_time);
        $calendarItem->setEnd_time($end_time);

        $newCalendaritem = $calendarItem->addCalendarItem($pdo, $_SESSION["user_id"]);
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

$allCalendarItems = CalendarItem::getAllEmployees($pdo, $user["location_id"]);
$allEmployeesByLocation = Employee::getAllEmployeesByLocation($pdo, $user["location_id"]);

$groupedCalendarItems = [];
foreach ($allCalendarItems as $calendarItem) {
    $date = new DateTime($calendarItem["start_time"]);
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
                            <?php foreach ($groupedCalendarItems[$dayKey] as $index => $item): ?>
                                <?php 
                                    $red = ($index * 70) % 256;
                                    $green = ($index * 120) % 256;
                                    $blue = ($index * 170) % 256;
                                    $itemColor = "rgb($red, $green, $blue)";
                                ?>
                                <p class="calendarItem" style="background-color: <?php echo $itemColor; ?>">
                                    <?php $time = strtotime($item["start_time"]); $time_formatted = date('H:i', $time); echo $time_formatted; ?>
                                    - <?php echo $item["event_description"] ?>
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
    <div class="popupAddCalendarItem">
        <i class="fa fa-plus"></i>
        <form action="" method="post" id="addCalendarItem">
            <div class="text">
                <div class="column">
                    <form id="taskSelectorForm" method="post" action="">
                        <label for="taskSelector">Selecteer tasktype:</label>
                        <select name="taskSelector" id="taskSelector">
                            <?php if ($taskTypes && is_array($taskTypes)): ?>
v                                <?php foreach ($taskTypes as $taskType) : ?>
                                    <option value="<?php echo $taskType["id"]; ?>" <?php if ($selectedTaskId === $taskType["id"]) echo "selected"; ?>>
                                        <?php echo htmlspecialchars($taskType["id"]); ?>
                                    </option>
                                <?php endforeach ?>                            <?php else: ?>
                                <option>Geen taken beschikbaar</option>
                            <?php endif; ?>
                        </select>
                    </form>
                </div>
                <div class="column">
                    <label for="event_date">Event_date:</label>
                    <input type="date" name="event_date" id="event_date" placeholder="Event_date">
                </div>
                <div class="row">
                    <div class="column">
                        <label for="timeslot">Timeslots:</label>
                        <div>
                            <input type="checkbox" value="08:00 - 09:00">
                            <p>08:00 - 09:00</p>
                        </div>
                        <div>
                            <input type="checkbox" value="09:00 - 10:00">
                            <p>09:00 - 10:00</p>
                        </div>
                        <div>
                            <input type="checkbox" value="10:00 - 11:00">
                            <p>10:00 - 11:00</p>
                        </div>
                        <div>
                            <input type="checkbox" value="11:00 - 12:00">
                            <p>11:00 - 12:00</p>
                        </div>
                        <div>
                            <input type="checkbox" value="12:00 - 13:00">
                            <p>12:00 - 13:00</p>
                        </div>
                        <div>
                            <input type="checkbox" value="13:00 - 14:00">
                            <p>13:00 - 14:00</p>
                        </div>
                    </div>
                    <div class="column">
                        <div>
                            <input type="checkbox" value="14:00 - 15:00">
                            <p>14:00 - 15:00</p>
                        </div>
                        <div>
                            <input type="checkbox" value="15:00 - 16:00">
                            <p>15:00 - 16:00</p>
                        </div>
                        <div>
                            <input type="checkbox" value="16:00 - 17:00">
                            <p>17:00 - 18:00</p>
                        </div>
                        <div>
                            <input type="checkbox" value="18:00 - 19:00">
                            <p>18:00 - 19:00</p>
                        </div>
                        <div>
                            <input type="checkbox" value="19:00 - 20:00">
                            <p>19:00 - 20:00</p>
                        </div>
                        <div>
                            <input type="checkbox" value="20:00 - 21:00">
                            <p>20:00 - 21:00</p>
                        </div>
                    </div>
                </div>
                <div class="column">
                    <label for="userSelector">Select user:</label>
                    <select name="userSelector" id="userSelector">
                        <?php if ($allUsersByTaskTypeAndDate && is_array($allUsersByTaskTypeAndDate) && count($allUsersByTaskTypeAndDate) > 0): ?>
                            <?php foreach ($allUsersByTaskTypeAndDate as $userByTaskTypeAndDate) : ?>
                                <?php 
                                $userId = $userByTaskTypeAndDate["id"] ?? null;
                                $userName = $userByTaskTypeAndDate["firstname"] . " " . $userByTaskTypeAndDate["lastname"] ?? '';
                                ?>
                                <option value="<?php echo htmlspecialchars($userName); ?>">
                                    <?php echo htmlspecialchars($userName); ?>
                                </option>
                            <?php endforeach ?>
                        <?php else: ?>
                            <option>No users available</option>
                        <?php endif; ?>
                    </select>
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
    <script>
        document.getElementById('taskSelector').addEventListener('change', function () {
            var taskId = this.value;
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'get_users_by_task.php');
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function () {
                if (xhr.status === 200) {
                    var userSelector = document.getElementById('userSelector');
                    userSelector.innerHTML = '';
                    var response = JSON.parse(xhr.responseText);
                    response.users.forEach(function(user) {
                        var option = document.createElement('option');
                        option.value = user.id;
                        option.textContent = user.name;
                        userSelector.appendChild(option);
                    });
                } else {
                    console.log('Request failed. Returned status of ' + xhr.status);
                }
            };
            xhr.send('taskId=' + taskId);
        });
        document.querySelector("#taskSelector").addEventListener("change", function(e) {
            var form = this.closest('form');
            form.submit();
        });
    </script>
</body>
</html>

