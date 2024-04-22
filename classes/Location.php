<?php
class Location
{
    private $image;
    private $name;

        /**
     * Get the value of image
     */ 
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set the value of image
     *
     * @return  self
     */ 
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get the value of name
     */ 
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */ 
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public static function getAll(PDO $pdo)
    {
        try {
            $query = "SELECT * FROM locations WHERE status = 1";
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return null;
        }
    }

    public static function getLocationById(PDO $pdo, $id)
    {
        try {
            $stmt = $pdo->prepare("SELECT * FROM locations WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return null;
        }
    }

    public static function addLocation(PDO $pdo, $image, $name)
    {
        try {
            $stmt = $pdo->prepare("INSERT INTO locations (image, name) VALUES (:image, :name)");
            $stmt->bindParam(':image', $image);
            $stmt->bindParam(':name', $name);
            
            $stmt->execute();
            
            return true;
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return false;
        }
    }   
    
    public static function deleteLocation(PDO $pdo, $id)
    {
        try {
            $stmt = $pdo->prepare("UPDATE locations SET status = 0 WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            throw new Exception('Database error: Unable to update read status');
        }
    }

    public function updateLocation(PDO $pdo, $id)
    {
        try {
            $stmt = $pdo->prepare("UPDATE locations SET name = :name WHERE id = :id");

            $stmt->bindParam(':name', $this->name);
            $stmt->bindParam(':id', $id);
            
            // Return true if the SQL query is successfully executed, otherwise return false
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
}