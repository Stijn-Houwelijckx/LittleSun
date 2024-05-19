<?php
class Report {

    // Functions for planned work time

    public static function getPlannedWorkTimeByUserIdBetweenDate (PDO $pdo, $user_id, $year, $month)
    {
        try {
            $startDate = "";
            $endDate = "";

            if ($month != null) {
                $startDate = $year . '-' . $month . '-01';
                $endDate = $year . '-' . $month . '-31';
            } else {
                $startDate = $year . '-01-01';
                $endDate = $year . '-12-31';
            }

            // Select planned work time for a user between two dates
            $stmt = $pdo->prepare("SELECT SEC_TO_TIME(SUM(TIMESTAMPDIFF(SECOND, start_time, end_time))) AS total_time
            FROM calendar
            WHERE user_id = :user_id
            AND event_date BETWEEN :start_date AND :end_date
            ");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // If there are no results, return 00:00:00 in total_time
            if ($result['total_time'] == null) {
                return ['total_time' => '00:00:00'];
            } else {
                return $result;
            }
        } catch (PDOException $e) {
            return "Error: " . $e->getMessage();
        }
    }

    public static function getPlannedWorkTimeForTaskByUserIdBetweenDate (PDO $pdo, $user_id, $task_id, $year, $month) {
        try {
            $startDate = "";
            $endDate = "";

            if ($month != null) {
                $startDate = $year . '-' . $month . '-01';
                $endDate = $year . '-' . $month . '-31';
            } else {
                $startDate = $year . '-01-01';
                $endDate = $year . '-12-31';
            }

            // Select planned work time for a user for a task between two dates
            $stmt = $pdo->prepare("SELECT SEC_TO_TIME(SUM(TIMESTAMPDIFF(SECOND, start_time, end_time))) AS total_time
            FROM calendar
            WHERE user_id = :user_id
            AND task_id = :task_id
            AND event_date BETWEEN :start_date AND :end_date
            ");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':task_id', $task_id);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // If there are no results, return 00:00:00 in total_time
            if ($result['total_time'] == null) {
                return ['total_time' => '00:00:00'];
            } else {
                return $result;
            }
        } catch (PDOException $e) {
            return "Error: " . $e->getMessage();
        }
    }

    // Functions for worked time

    public static function getWorkedTimeByUserIdBetweenDate($pdo, $user_id, $year, $month) {
        try {
            $startDate = "";
            $endDate = "";

            if ($month != null) {
                $startDate = $year . '-' . $month . '-01';
                $endDate = $year . '-' . $month . '-31';
            } else {
                $startDate = $year . '-01-01';
                $endDate = $year . '-12-31';
            }

            // Select worked time for a user between two dates
            $stmt = $pdo->prepare("SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(TIMEDIFF(end_time, start_time)))) AS total_time
            FROM time_tracker
            WHERE user_id = :user_id
            AND DATE(start_time) BETWEEN :start_date AND :end_date
            ");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // If there are no results, return 00:00:00 in total_time
            if ($result['total_time'] == null) {
                return ['total_time' => '00:00:00'];
            } else {
                return $result;
            }
        } catch (PDOException $e) {
            return "Error: " . $e->getMessage();
        }
    }

    public static function getWorkedTimeForTaskByUserIdBetweenDate($pdo, $user_id, $task_id, $year, $month) {
        try {
            $startDate = "";
            $endDate = "";
    
            if ($month != null) {
                $startDate = $year . '-' . $month . '-01';
                $endDate = $year . '-' . $month . '-31';
            } else {
                $startDate = $year . '-01-01';
                $endDate = $year . '-12-31';
            }
    
            // Select worked time for a user for a task between two dates
            $stmt = $pdo->prepare("SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(time_worked))) AS total_time
                FROM work_entries
                WHERE user_id = :user_id
                AND task_id = :task_id
                AND event_date BETWEEN :start_date AND :end_date
            ");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':task_id', $task_id);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
            // If there are no results, return 00:00:00 in total_time
            if ($result['total_time'] === null) {
                return ['total_time' => '00:00:00'];
            } else {
                return $result;
            }
        } catch (PDOException $e) {
            return "Error: " . $e->getMessage();
        }
    }
    

    // Functions for time off time

    public static function getTimeOffByUserIdBetweenDate(PDO $pdo, $user_id, $year, $month)
    {
        try {
            $startDate = "";
            $endDate = "";

            if ($month != null) {
                $startDate = $year . '-' . $month . '-01';
                $endDate = $year . '-' . $month . '-31';
            } else {
                $startDate = $year . '-01-01';
                $endDate = $year . '-12-31';
            }

            // Select time off for a user between two dates
            $stmt = $pdo->prepare("SELECT SEC_TO_TIME(SUM(TIMESTAMPDIFF(SECOND, start_date, end_date))) as total_time
            FROM time_off_requests
            WHERE user_id = :user_id AND status = 'Approved'
            AND DATE(start_date) BETWEEN :start_date AND :end_date
            ");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // If there are no results, return 00:00:00 in total_time
            if ($result['total_time'] == null) {
                return ['total_time' => '00:00:00'];
            } else {
                return $result;
            }
        } catch (PDOException $e) {
            return "Error: " . $e->getMessage();
        }
    }

    // Functions for sick time

    public static function getTotalSickTimeByUserIdBetweenDate(PDO $pdo, $user_id, $year, $month)
    {
        try {
            $startDate = "";
            $endDate = "";
    
            if ($month != null) {
                $startDate = $year . '-' . $month . '-01';
                $endDate = $year . '-' . $month . '-31';
            } else {
                $startDate = $year . '-01-01';
                $endDate = $year . '-12-31';
            }

            // Select sick time for a user between two dates
            $stmt = $pdo->prepare("SELECT SEC_TO_TIME(SUM(TIMESTAMPDIFF(SECOND, start_date, end_date))) as total_time
            FROM sick_leave
            WHERE user_id = :user_id
            AND DATE(start_date) BETWEEN :start_date AND :end_date
            ");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // If there are no results, return 00:00:00 in total_time
            if ($result['total_time'] == null) {
                return ['total_time' => '00:00:00'];
            } else {
                return $result;
            }
        } catch (PDOException $e) {
            return "Error: " . $e->getMessage();
        }
    }

    public static function getTotalSickTimeForTaskByUserIdBetweenDate(PDO $pdo, $user_id, $task_id, $year, $month)
    {
        try {
            $startDate = "";
            $endDate = "";
    
            if ($month != null) {
                $startDate = $year . '-' . $month . '-01';
                $endDate = $year . '-' . $month . '-31';
            } else {
                $startDate = $year . '-01-01';
                $endDate = $year . '-12-31';
            }

            // Select sick time for a user between two dates
            $stmt = $pdo->prepare("SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(time_planned))) AS total_time
            FROM work_entries
            WHERE user_id = :user_id
            AND task_id = :task_id
            AND event_date BETWEEN :start_date AND :end_date
            AND is_sick = 1
            ");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':task_id', $task_id);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // If there are no results, return 00:00:00 in total_time
            if ($result['total_time'] == null) {
                return ['total_time' => '00:00:00'];
            } else {
                return $result;
            }
        } catch (PDOException $e) {
            return "Error: " . $e->getMessage();
        }
    }
}