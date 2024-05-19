<?php
class CalendarItem
{
    private $event_date;
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
            $this->start_time = explode(' - ', reset($selectedTimeslots))[0];
            $this->end_time = explode(' - ', end($selectedTimeslots))[1];

            $stmt = $pdo->prepare("INSERT INTO calendar (user_id, task_id, event_date, event_location, start_time, end_time) VALUES (:user_id, :task_id, :event_date, :event_location, :start_time, :end_time)");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':task_id', $task_id);
            $stmt->bindParam(':event_date', $this->event_date);
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
            $stmt = $pdo->prepare("SELECT DISTINCT calendar.*, tasktypes.task
            FROM calendar, users, tasktypes
            WHERE tasktypes.id = calendar.task_id
            AND calendar.user_id = :user_id
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
    
    public static function getPlannedWorkTimeByUserIdAndDate(PDO $pdo, $user_id, $date)
    {
        try {
            $stmt = $pdo->prepare("
                SELECT SEC_TO_TIME(SUM(TIMESTAMPDIFF(SECOND, start_time, end_time))) AS total_time
                FROM calendar
                WHERE user_id = :user_id
                AND event_date = :date
            ");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':date', $date);
            $stmt->execute();
            $hours = $stmt->fetch(PDO::FETCH_ASSOC);
            return $hours ?: [];
        } catch (PDOException $e) {
            error_log('Database error in getPlannedWorkHoursByUserIdAndDate(): ' . $e->getMessage());
            throw new Exception('Database error: Unable to retrieve hours');
        }
    }

    public static function getDistinctYearsByLocation(PDO $pdo, $location_id)
    {
        try {
            $stmt = $pdo->prepare("SELECT DISTINCT YEAR(event_date) AS year FROM calendar WHERE event_location = :location_id ORDER BY year ASC");
            $stmt->bindParam(':location_id', $location_id);
            $stmt->execute();
            $years = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $years ?: [];
        } catch (PDOException $e) {
            error_log('Database error in getDistinctYears(): ' . $e->getMessage());
            throw new Exception('Database error: Unable to retrieve years');
        }
    }

    public static function getDistinctMonthsByLocation(PDO $pdo, $year, $location_id)
    {
        try {
            $stmt = $pdo->prepare("SELECT DISTINCT LPAD(MONTH(event_date), 2, '0') AS month_number, MONTHNAME(event_date) AS month_name FROM calendar WHERE YEAR(event_date) = :year AND event_location = :location_id ORDER BY month_number ASC");
            $stmt->bindParam(':year', $year);
            $stmt->bindParam(':location_id', $location_id);
            $stmt->execute();
            $months = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $months ?: [];
        } catch (PDOException $e) {
            error_log('Database error in getDistinctMonths(): ' . $e->getMessage());
            throw new Exception('Database error: Unable to retrieve months calendar items');
        }
    }
    
    public static function getCalenderItemsByUserIdBetweenDates(PDO $pdo, $user_id, $start_date, $end_date)
    {
        try {
            $stmt = $pdo->prepare("SELECT * FROM calendar WHERE user_id = :user_id AND event_date BETWEEN :start_date AND :end_date ORDER BY event_date ASC");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':start_date', $start_date);
            $stmt->bindParam(':end_date', $end_date);
            $stmt->execute();
            $calendarItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $calendarItems ?: [];
        } catch (PDOException $e) {
            error_log('Database error in getCalenderItemsByUserIdBetweenDates(): ' . $e->getMessage());
            throw new Exception('Database error: Unable to retrieve calendar items');
        }
    }
}
