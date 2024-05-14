<?php
class TimeTracker {
    private $date;
    private $start_time;
    private $end_time;
    private $overtime;

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

    /**
     * Get the value of overtime
     */ 
    public function getOvertime()
    {
        return $this->overtime;
    }

    /**
     * Set the value of overtime
     *
     * @return  self
     */ 
    public function setOvertime($overtime)
    {
        $this->overtime = $overtime;

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

    public static function getWorkedTimeByUserId($pdo, $user_id) {
        try {
            $stmt = $pdo->prepare("SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(TIMEDIFF(end_time, start_time)))) AS total_time FROM time_tracker WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            $result = $stmt->fetch();

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

    public static function saveOvertime($pdo, $user_id, $timetracker_id, $overtime) {
        try {
            $stmt = $pdo->prepare("UPDATE time_tracker SET overtime = :overtime WHERE user_id = :user_id AND id = :timetracker_id");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':timetracker_id', $timetracker_id);
            $stmt->bindParam(':overtime', $overtime);

            $stmt->execute();

            return "Overtime set to: " . $overtime;
        } catch (PDOException $e) {
            return "Error: " . $e->getMessage();
        }
    }

    public static function getOvertimeByUserId($pdo, $user_id) {
        try {
            $stmt = $pdo->prepare("SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(overtime))) AS total_overtime FROM time_tracker WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            return "Error: " . $e->getMessage();
        }
    }
}