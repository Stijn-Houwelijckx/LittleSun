<?php
class WorkEntry {
    private $user_id;
    private $task_id;
    private $event_date;
    private $timePlanned;
    private $timeWorked;

        /**
     * Get the value of user_id
     */ 
    public function getUser_id()
    {
        return $this->user_id;
    }

    /**
     * Set the value of user_id
     *
     * @return  self
     */ 
    public function setUser_id($user_id)
    {
        $this->user_id = $user_id;

        return $this;
    }

    /**
     * Get the value of task_id
     */ 
    public function getTask_id()
    {
        return $this->task_id;
    }

    /**
     * Set the value of task_id
     *
     * @return  self
     */ 
    public function setTask_id($task_id)
    {
        $this->task_id = $task_id;

        return $this;
    }

    /**
     * Get the value of event_date
     */ 
    public function getEvent_date()
    {
        return $this->event_date;
    }

    /**
     * Set the value of event_date
     *
     * @return  self
     */ 
    public function setEvent_date($event_date)
    {
        $this->event_date = $event_date;

        return $this;
    }

    /**
     * Get the value of timePlanned
     */ 
    public function getTimePlanned()
    {
        return $this->timePlanned;
    }

    /**
     * Set the value of timePlanned
     *
     * @return  self
     */ 
    public function setTimePlanned($timePlanned)
    {
        $this->timePlanned = $timePlanned;

        return $this;
    }

    /**
     * Get the value of timeWorked
     */ 
    public function getTimeWorked()
    {
        return $this->timeWorked;
    }

    /**
     * Set the value of timeWorked
     *
     * @return  self
     */ 
    public function setTimeWorked($timeWorked)
    {
        $this->timeWorked = $timeWorked;

        return $this;
    }

    public function addWorkEntry(PDO $pdo)
    {
        try {
            $stmt = $pdo->prepare("INSERT INTO work_entries (user_id, task_id, event_date, time_planned) VALUES (:user_id, :task_id, :event_date, :time_planned)");
            $stmt->bindParam(':user_id', $this->user_id);
            $stmt->bindParam(':task_id', $this->task_id);
            $stmt->bindParam(':event_date', $this->event_date);
            $stmt->bindParam(':time_planned', $this->timePlanned);
            $stmt->execute();
            return $pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return false;
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return false;
        }
    }

    public function updateWorkEntryTimeWorked(PDO $pdo)
    {
        try {
            $stmt = $pdo->prepare("UPDATE work_entries SET time_worked = :time_worked WHERE user_id = :user_id AND task_id = :task_id AND event_date = :event_date");
            $stmt->bindParam(':user_id', $this->user_id);
            $stmt->bindParam(':task_id', $this->task_id);
            $stmt->bindParam(':event_date', $this->event_date);
            $stmt->bindParam(':time_worked', $this->timeWorked);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return false;
        }
    }

    public static function setWorkEntrySick(PDO $pdo, $user_id, $task_id, $event_date)
    {
        try {
            $stmt = $pdo->prepare("UPDATE work_entries SET is_sick = 1 WHERE user_id = :user_id AND task_id = :task_id AND event_date = :event_date");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':task_id', $task_id);
            $stmt->bindParam(':event_date', $event_date);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return false;
        }
    }
}