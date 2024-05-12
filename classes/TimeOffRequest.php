<?php
class TimeOffRequest {
    private $start_date;
    private $end_date;
    private $reason;
    private $description;
    private $status;

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

    /**
     * Get the value of description
     */ 
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the value of description
     *
     * @return  self
     */ 
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the value of status
     */ 
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the value of status
     *
     * @return  self
     */ 
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    public function submitRequest(PDO $pdo, $user_id)
    {
        try {
            $stmt = $pdo->prepare("INSERT INTO time_off_requests (user_id, start_date, end_date, reason, description) VALUES (:user_id, :start_date, :end_date, :reason, :description)");
            $stmt->bindParam(':start_date', $this->start_date);
            $stmt->bindParam(':end_date', $this->end_date);
            $stmt->bindParam(':reason', $this->reason);
            $stmt->bindParam(':description', $this->description);
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

    public static function getRequestsByUserId(PDO $pdo, $user_id)
    {
        try {
            $stmt = $pdo->prepare("SELECT * FROM time_off_requests WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return false;
        }
    }

    public static function getTimeOffDatesByUserIdAndDate (PDO $pdo, $user_id, $date)
    {
        try {
            $stmt = $pdo->prepare("SELECT start_date, end_date FROM time_off_requests WHERE user_id = :user_id AND (DATE(start_date) = :date1 OR DATE(end_date) = :date2) AND status = 'Approved'");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':date1', $date);
            $stmt->bindParam(':date2', $date);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // If there is a result, return the result, otherwise return false
            return $result ?: false;
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return false;
        }
    }

    public static function updateRequestStatus(PDO $pdo, $request_id, $status, $Comment)
    {
        try {
            $stmt = $pdo->prepare("UPDATE time_off_requests SET status = :status, managerComment = :comment WHERE id = :request_id");
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':request_id', $request_id);
            $stmt->bindParam(':comment', $Comment);
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

    public static function getAllPendingRequests(PDO $pdo, $location_id)
    {
        try {
            $stmt = $pdo->prepare("SELECT time_off_requests.*, users.firstname, users.lastname FROM time_off_requests, user_locations, users WHERE time_off_requests.status = 'Pending' AND time_off_requests.user_id = user_locations.user_id AND time_off_requests.user_id = users.id AND user_locations.location_id = :location_id");
            $stmt->bindParam(':location_id', $location_id);
            $stmt->execute();
            
            // Return all pending requests or false if there are no pending requests
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: false;
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return false;
        }
    }

    public static function numberOfRequests(PDO $pdo){
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) AS row_count FROM time_off_requests WHERE status = 'Pending'");
            $stmt->execute();
            
            // Return all pending requests or false if there are no pending requests
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return false;
        }
    }
}