<?php

include_once (__DIR__ . "../classes/Db.php");

if (isset($_POST['start_work'])) {
    try {

        $start_time = date('Y-m-d H:i:s');


        $stmt = Db::getInstance()->prepare("INSERT INTO clock_in_time (start_time) VALUES (:start_time)");


        $stmt->bindParam(':start_time', $start_time);


        $stmt->execute();

        echo "Work started!";
    } catch (PDOException $e) {

        echo "Error: " . $e->getMessage();
    }
}
?>
