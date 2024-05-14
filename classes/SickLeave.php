<?php
class SickLeave {
    private $start_date;
    private $end_date;
    private $reason;

    /**
     * Get the value of start_date
     */ 
    public function getStart_date()
    {
        return $this->start_date;
    }

    /**
     * Set the value of start_date
     *
     * @return  self
     */ 
    public function setStart_date($start_date)
    {
        $this->start_date = $start_date;

        return $this;
    }

    /**
     * Get the value of end_date
     */ 
    public function getEnd_date()
    {
        return $this->end_date;
    }

    /**
     * Set the value of end_date
     *
     * @return  self
     */ 
    public function setEnd_date($end_date)
    {
        $this->end_date = $end_date;

        return $this;
    }

    /**
     * Get the value of reason
     */ 
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Set the value of reason
     *
     * @return  self
     */ 
    public function setReason($reason)
    {
        $this->reason = $reason;

        return $this;
    }

    public function submitSickLeave(PDO $pdo, $user_id)
    {
        try {
            $stmt = $pdo->prepare("INSERT INTO sick_leave (user_id, start_date, end_date, reason) VALUES (:user_id, :start_date, :end_date, :reason)");
            $stmt->bindParam(':start_date', $this->start_date);
            $stmt->bindParam(':end_date', $this->end_date);
            $stmt->bindParam(':reason', $this->reason);
            $stmt->bindParam(':user_id', $user_id);
            if ($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return false;
        }
    }

    public static function getActiveSickLeave(PDO $pdo, $user_id)
    {
        $stmt = $pdo->prepare("SELECT * FROM sick_leave WHERE user_id = :user_id AND status = 1");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function getSickLeaveByUserIdAndDate(PDO $pdo, $user_id, $date)
    {
        $stmt = $pdo->prepare("SELECT * FROM sick_leave WHERE user_id = :user_id AND :date BETWEEN start_date AND end_date AND status = 1 LIMIT 1");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':date', $date);
        $stmt->execute();
        return $stmt->fetch();
    }

    public static function getTotalSickTimeByUserId(PDO $pdo, $user_id)
    {
        $stmt = $pdo->prepare("SELECT SEC_TO_TIME(SUM(TIMESTAMPDIFF(SECOND, start_date, end_date))) as total_time
        FROM sick_leave
        WHERE user_id = :user_id AND status = 1
        ");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // If there are no results, return 00:00:00 in total_time
        if ($result['total_time'] == null) {
            return ['total_time' => '00:00:00'];
        } else {
            return $result;
        }
    }
}