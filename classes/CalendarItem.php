<?php
class CalendarItem
{
    private $event_date;
    private $event_title;
    private $event_description;
    private $event_location;
    private $start_time;
    private $end_time;
    private $created_at;


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
     * Get the value of event_title
     */ 
    public function getEvent_title()
    {
        return $this->event_title;
    }

    /**
     * Set the value of event_title
     *
     * @return  self
     */ 
    public function setEvent_title($event_title)
    {
        $this->event_title = $event_title;

        return $this;
    }

    /**
     * Get the value of event_description
     */ 
    public function getEvent_description()
    {
        return $this->event_description;
    }

    /**
     * Set the value of event_description
     *
     * @return  self
     */ 
    public function setEvent_description($event_description)
    {
        $this->event_description = $event_description;

        return $this;
    }

    /**
     * Get the value of event_location
     */ 
    public function getEvent_location()
    {
        return $this->event_location;
    }

    /**
     * Set the value of event_location
     *
     * @return  self
     */ 
    public function setEvent_location($event_location)
    {
        $this->event_location = $event_location;

        return $this;
    }

    /**
     * Get the value of created_at
     */ 
    public function getCreated_at()
    {
        return $this->created_at;
    }

    /**
     * Set the value of created_at
     *
     * @return  self
     */ 
    public function setCreated_at($created_at)
    {
        $this->created_at = $created_at;

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

    public function addCalendarItem(PDO $pdo, $user_id, $task_id, $selectedTimeslots): int|bool
    {
        try {
            // Selecteer het eerste en laatste geselecteerde timeslot als start- en eindtijd
            $this->start_time = reset($selectedTimeslots);
            $this->end_time = explode(' - ', end($selectedTimeslots))[1]; // Het tweede deel is het eindtijdgedeelte

            $stmt = $pdo->prepare("INSERT INTO calendar (user_id, task_id, event_date, event_title, event_description, event_location, start_time, end_time) VALUES (:user_id, :task_id, :event_date, :event_title, :event_description, :event_location, :start_time, :end_time)");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':task_id', $task_id);
            $stmt->bindParam(':event_date', $this->event_date);
            $stmt->bindParam(':event_title', $this->event_title);
            $stmt->bindParam(':event_description', $this->event_description);
            $stmt->bindParam(':event_location', $this->event_location);
            $stmt->bindParam(':start_time', $this->start_time);
            $stmt->bindParam(':end_time', $this->end_time);
    
            // Execute and return id of the new user
            if ($stmt->execute()) {
                return $pdo->lastInsertId();
            } else {
                return false;
            }
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return false;
        }
    }
     

    public static function getAllCalenderItems(PDO $pdo, $location_id)
    {
        try {
            $stmt = $pdo->prepare("SELECT DISTINCT calendar.*, tasktypes.task
            FROM calendar, users, user_locations, tasktypes
            WHERE tasktypes.id = calendar.task_id
            AND calendar.user_id = users.id
            AND users.id = user_locations.user_id
            AND users.typeOfUser = 'employee'
            AND user_locations.location_id = :location_id
            ORDER BY calendar.start_time ASC
            ");
            $stmt->bindParam(':location_id', $location_id);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $users ?: [];
        } catch (PDOException $e) {
            error_log('Database error in getUsers(): ' . $e->getMessage());
            throw new Exception('Database error: Unable to retrieve users');
        }
    }

    public static function getMyCalendarAsEmployee(PDO $pdo, $user_id){
        try {
            $stmt = $pdo->prepare("SELECT DISTINCT calendar.* 
            FROM calendar, users 
            WHERE calendar.user_id = :user_id
            ");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $users ?: [];
        } catch (PDOException $e) {
            error_log('Database error in getUsers(): ' . $e->getMessage());
            throw new Exception('Database error: Unable to retrieve users');
        }
    }

    public static function getAllUsersByTaskTypeAndEventDate(PDO $pdo, $selectedTask){
        try {
            $stmt = $pdo->prepare("
                SELECT DISTINCT users.firstname, users.lastname 
                FROM user_tasks, tasktypes, users 
                WHERE user_tasks.user_id = users.id 
                AND user_tasks.task_id = tasktypes.id 
                AND tasktypes.id = :selectedTask 
                AND users.typeOfUser = 'employee'");
            
            $stmt->bindParam(':selectedTask', $selectedTask);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $users ?: [];
        } catch (PDOException $e) {
            error_log('Database error in getAllUsersByTaskTypeAndEventDate(): ' . $e->getMessage());
            throw new Exception('Database error: Unable to retrieve users');
        }
    }
    
}
