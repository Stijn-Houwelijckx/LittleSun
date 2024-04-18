<?php
include_once (__DIR__ . "/classes/User.php");
include_once (__DIR__ . "/classes/Db.php");
session_start();
$current_page = 'calendar';

$pdo = Db::getInstance();
$user = User::getUserById($pdo, $_SESSION["user_id"]);

if (isset($_SESSION["user_id"])) {
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
    // get the number of days in a month (e.g. 31, 30, 28, 29)
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
 
    // create an empty array
    $days = [];
 
    // loop through all days in the month
    for ($day = 1; $day <= $daysInMonth; $day++) {
        // add the day to the array in the format "YYYY-MM-DD"
        $days[] = sprintf('%04d-%02d-%02d', $year, $month, $day);
    }
    // return the array
    return $days;
}
 
// Example usage:
$currentYear = date('Y');
$currentMonth = date('m');
$allDaysThisMonth = generateDaysForMonth($currentYear, $currentMonth);
 
// what day of the week is the first of the month?
$date = new DateTime($allDaysThisMonth[0]);
$dayOfWeek = $date->format('N'); // 1 = Monday, 7 = Sunday
 
// if it's not a Monday, let's add some empty days in front of the array
$emptyDays = array_fill(0, $dayOfWeek - 1, '');
 
// add empty strings in front of the array if necessary
array_unshift($allDaysThisMonth, ...$emptyDays);
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
            border: 1px solid #ccc;
            padding: 10px;
            height: 100px;
        }
    </style>
</head>

<body>
    <?php include_once ('inc/nav.inc.php'); ?>
    <div id="calendar">
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
                <div class="day"><p><?php echo $day ?></p></div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>