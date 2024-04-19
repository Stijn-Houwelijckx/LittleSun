<?php
class CalendarItem
{
    private $event_date;
    private $event_title;
    private $event_description;
    private $event_location;
    private $created_at;
    private $updated_at;


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
     * Get the value of updated_at
     */ 
    public function getUpdated_at()
    {
        return $this->updated_at;
    }

    /**
     * Set the value of updated_at
     *
     * @return  self
     */ 
    public function setUpdated_at($updated_at)
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function addCalendarItem(PDO $pdo): int|bool
    {
        try {
            $stmt = $pdo->prepare("INSERT INTO calendar (event_date, event_title, event_description, event_location) VALUES (:event_date, :event_title, :event_description, :event_location)");
            $stmt->bindParam(':event_date', $this->event_date);
            $stmt->bindParam(':event_title', $this->event_title);
            $stmt->bindParam(':event_description', $this->event_description);
            $stmt->bindParam(':event_location', $this->event_location);

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
}
