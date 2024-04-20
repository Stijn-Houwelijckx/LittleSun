<?php
include_once (__DIR__ . "../../classes/Db.php");
include_once (__DIR__ . "../../classes/User.php");
include_once (__DIR__ . "../../classes/Manager.php");
include_once (__DIR__ . "../../classes/CalendarItem.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', 'error.log');

session_start();

$pdo = Db::getInstance();
$manager = User::getUserById($pdo, $_SESSION["user_id"]);

if (!isset($_SESSION["user_id"]) || $manager["typeOfUser"] != "manager") {
    header("Location: login.php?notLoggedIn=true");
    exit();
}

if (isset($_POST['event_date'], $_POST['event_title'], $_POST['event_description'])){
    $calendarItem = new CalendarItem;

    try {
        $event_date = $_POST['event_date'];
        $event_title = $_POST['event_title'];
        $event_description = $_POST['event_description'];
    
        $calendarItem->setEvent_date($event_date);
        $calendarItem->setEvent_title($event_title);
        $calendarItem->setEvent_description($event_description);
        $calendarItem->setEvent_location($manager["location_id"]);
    
        $newCalendaritem = $calendarItem->addCalendarItem($pdo);
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User</title>
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="icon" type="image/x-icon" href="../assets/images/favicon.png">
</head>
<body>
    <form action="" method="post" id="userForm">
        <div class="user">
            <div class="text">
                <div class="column">
                    <label for="event_date">Event_date:</label>
                    <input type="date" name="event_date" id="event_date" placeholder="Event_date">
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
        </div>
        <div class="buttons">
            <button type="submit" class="btn">Save</button>
        </div>
    </form>
</body>
</html>