<?php
include_once (__DIR__ . "../classes/Db.php");

// Als de "Start Work" knop is ingedrukt
if (isset($_POST['start_work'])) {
    try {
        $pdo = Db::getInstance();

        if ($_POST['start_work'] === 'true') {
            // Huidige datum en tijd ophalen
            $start_time_full = date('Y-m-d H:i:s'); // Volledige datum en tijd
            $start_time = date('H:i:s', strtotime($start_time_full)); // Alleen de tijd

            // Query om de starttijd van de werknemer toe te voegen aan de database
            $stmt = $pdo->prepare("INSERT INTO clock_in_time (start_time_full, start_time) VALUES (:start_time_full, :start_time)");
            $stmt->bindParam(':start_time_full', $start_time_full);
            $stmt->bindParam(':start_time', $start_time);
            $stmt->execute();

            echo "You clocked in at: " . $start_time; // Toon de starttijd (alleen tijd, geen datum)
        } elseif ($_POST['start_work'] === 'false') {
            // Huidige datum en tijd ophalen
            $end_time_full = date('Y-m-d H:i:s'); // Volledige datum en tijd
            $end_time = date('H:i:s', strtotime($end_time_full)); // Alleen de tijd

            // Query om de eindtijd van de werknemer toe te voegen aan de database
            $stmt = $pdo->prepare("INSERT INTO clock_out_time (end_time_full, end_time) VALUES (:end_time_full, :end_time)");
            $stmt->bindParam(':end_time_full', $end_time_full);
            $stmt->bindParam(':end_time', $end_time);
            $stmt->execute();

            echo "You clocked out at: " . $end_time; // Toon de eindtijd (alleen tijd, geen datum)
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
