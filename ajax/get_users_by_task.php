<?php
// Zorg ervoor dat de sessie is gestart
session_start();

// Controleer of er een taak-ID is ontvangen via GET
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['taskId'])) {
    // Inclusief benodigde klassen
    include_once (__DIR__ . "../../classes/Db.php");
    include_once (__DIR__ . "../../classes/CalendarItem.php");

    // Maak verbinding met de database
    $pdo = Db::getInstance();

    // Haal de taak-ID op van de querystring
    $taskId = $_GET['taskId'];

    // Roep de functie aan om alle gebruikers op te halen op basis van de taak-ID
    $allUsersByTaskTypeAndDate = CalendarItem::getAllUsersByTaskTypeAndEventDate($pdo, $taskId);

    // Stuur de lijst met gebruikers terug als JSON
    $response = ['users' => $allUsersByTaskTypeAndDate];
    echo json_encode($response);
    exit;
} else {
    // Als er geen geldige taak-ID is ontvangen, retourneer dan een foutmelding
    $response = ['error' => 'Invalid request'];
    echo json_encode($response);
    exit;
}
?>
