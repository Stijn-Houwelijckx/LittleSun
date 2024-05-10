<?php
// TimeTracker.php
class TimeTracker {
    public static function clockInOut($pdo, $user_id, $clock_in, $current_time) {
        try {
            if ($clock_in) {
                // Clock in
                $start_time = date('H:i:s', strtotime($current_time));
                $stmt = $pdo->prepare("INSERT INTO clock_in_time (user_id, start_time_full, start_time) VALUES (:user_id, :current_time, :start_time)");
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':current_time', $current_time);
                $stmt->bindParam(':start_time', $start_time);
                $stmt->execute();
    
                return "You clocked in at: " . $start_time;
            } else {
                // Clock out
                $end_time = date('H:i:s', strtotime($current_time));
                $stmt = $pdo->prepare("INSERT INTO clock_out_time (user_id, end_time_full, end_time) VALUES (:user_id, :current_time, :end_time)");
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':current_time', $current_time);
                $stmt->bindParam(':end_time', $end_time);
                $stmt->execute();
    
                // Calculate worked time
                $worked_time = self::calculateWorkedTime($pdo, $user_id, $current_time);
                return "You clocked out at: " . $end_time. "<br/>" . $worked_time;
            }
        } catch (PDOException $e) {
            return "Error: " . $e->getMessage();
        }
    }
    

    // Method to calculate worked time
    private static function calculateWorkedTime($pdo, $user_id, $end_time) {
        try {
            // Retrieve start time from the database
            $stmt = $pdo->prepare("SELECT start_time_full FROM clock_in_time WHERE user_id = :user_id ORDER BY id DESC LIMIT 1");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            $start_time_full = $stmt->fetchColumn();
    
            // Retrieve planned start and end time from the calendar table
            $stmt = $pdo->prepare("SELECT start_time, end_time FROM calendar WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            $calendar_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            // Calculate worked time
            $start_timestamp = strtotime($start_time_full);
            $end_timestamp = strtotime($end_time);
            $worked_time_seconds = $end_timestamp - $start_timestamp;
    
            // Calculate planned work time
            $planned_work_time_seconds = 0;
            foreach ($calendar_data as $data) {
                $planned_start_timestamp = strtotime($data['start_time']);
                $planned_end_timestamp = strtotime($data['end_time']);
                $planned_work_time_seconds += $planned_end_timestamp - $planned_start_timestamp;
            }
    
            // Format worked time
            $worked_hours = floor($worked_time_seconds / 3600);
            $worked_minutes = floor(($worked_time_seconds % 3600) / 60);
    
            // Format planned work time
            $planned_hours = floor($planned_work_time_seconds / 3600);
            $planned_minutes = floor(($planned_work_time_seconds % 3600) / 60);
            
            return "Worked time: " . $worked_hours . " hours and " . $worked_minutes . " minutes.<br>Planned work time: " . $planned_hours . " hours and " . $planned_minutes . " minutes";
        } catch (PDOException $e) {
            return "Error: " . $e->getMessage();
        }
    }
    
}
?>
