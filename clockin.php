<?php
include_once (__DIR__ . "../classes/Db.php");

// Als de "Start Work" knop is ingedrukt
if (isset($_POST['start_work'])) {
    try {
        // Huidige datum en tijd ophalen
        $start_time_full = date('Y-m-d H:i:s'); // Volledige datum en tijd
        $start_time = date('H:i:s', strtotime($start_time_full)); // Alleen de tijd

        // Query om de starttijd van de werknemer toe te voegen aan de database
        $stmt = Db::getInstance()->prepare("INSERT INTO clock_in_time (start_time_full) VALUES (:start_time_full)");

        // Bind parameters
        $stmt->bindParam(':start_time_full', $start_time_full);

        // Uitvoeren van de query
        $stmt->execute();

        echo "You clocked in at: " . $start_time; // Toon de starttijd (alleen tijd, geen datum)
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    // Als er geen record is gevonden
    echo "<p>No records found.</p>";
}
?>

