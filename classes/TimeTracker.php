<?php
class TimeTracker {
    private $date;
    private $start_time;
    private $end_time;

        /**
     * Get the value of date
     */ 
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set the value of date
     *
     * @return  self
     */ 
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get the value of start_time
     */ 
    public function getStart_time()
    {
        return $this->start_time;
    }

    /**
     * Set the value of start_time
     *
     * @return  self
     */ 
    public function setStart_time($start_time)
    {
        $this->start_time = $start_time;

        return $this;
    }

    /**
     * Get the value of end_time
     */ 
    public function getEnd_time()
    {
        return $this->end_time;
    }

    /**
     * Set the value of end_time
     *
     * @return  self
     */ 
    public function setEnd_time($end_time)
    {
        $this->end_time = $end_time;

        return $this;
    }

    public static function getLastTimeTrackerByUserId($pdo, $user_id) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM time_tracker WHERE user_id = :user_id ORDER BY id DESC LIMIT 1");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            return "Error: " . $e->getMessage();
        }
    }

    public static function getActiveTimeTracker($pdo, $user_id) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM time_tracker WHERE user_id = :user_id AND active = 1");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            return "Error: " . $e->getMessage();
        }
    }

    public static function getWorkedTimeByUserIdAndDate($pdo, $user_id, $date) {
        try {
            $stmt = $pdo->prepare("SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(TIMEDIFF(end_time, start_time)))) AS worked_time FROM time_tracker WHERE user_id = :user_id AND DATE(start_time) = :date");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':date', $date);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            return "Error: " . $e->getMessage();
        }
    }

    public static function clockIn($pdo, $user_id) {
        try {
            $stmt = $pdo->prepare("INSERT INTO time_tracker (user_id) VALUES (:user_id)");
            $stmt->bindParam(':user_id', $user_id);

            $stmt->execute();

            return date('Y-m-d H:i:s');
        } catch (PDOException $e) {
            return "Error: " . $e->getMessage();
        }
    }

    public static function clockOut($pdo, $user_id) {
        try {
            $date = date('Y-m-d H:i:s');

            $stmt = $pdo->prepare("UPDATE time_tracker SET active = 0, end_time = :end_time WHERE user_id = :user_id AND active = 1");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':end_time', $date);

            $stmt->execute();

            return date('Y-m-d H:i:s');
        } catch (PDOException $e) {
            return "Error: " . $e->getMessage();
        }
    }

    public static function getDistinctMonthsByLocation(PDO $pdo, $year, $location_id)
    {
        try {
            // $stmt = $pdo->prepare("SELECT DISTINCT LPAD(MONTH(start_time), 2, '0') AS month_number, MONTHNAME(start_time) AS month_name FROM time_tracker, users WHERE time_tracker.user_id = users.id AND users.location_id = :location_id ORDER BY month_number ASC");
            $stmt = $pdo->prepare("SELECT DISTINCT LPAD(MONTH(start_time), 2, '0') AS month_number, MONTHNAME(start_time) AS month_name FROM time_tracker, user_locations WHERE time_tracker.user_id = user_locations.user_id AND user_locations.location_id = :location_id AND YEAR(start_time) = :year ORDER BY month_number ASC");
            $stmt->bindParam(':year', $year);
            $stmt->bindParam(':location_id', $location_id);
            $stmt->execute();
            $months = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $months ?: [];
        } catch (PDOException $e) {
            error_log('Database error in getDistinctMonths(): ' . $e->getMessage());
            throw new Exception('Database error: Unable to retrieve months time tracker');
        }
    }
}