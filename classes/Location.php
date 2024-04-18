<?php
class Location
{
    private $image;
    private $name;
    private $city;
    private $country;

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

    /**
     * Get the value of city
     */ 
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set the value of city
     *
     * @return  self
     */ 
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get the value of country
     */ 
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set the value of country
     *
     * @return  self
     */ 
    public function setCountry($country)
    {
        $this->country = $country;

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

    public static function addLocation(PDO $pdo, $image, $name, $city, $country)
    {
        try {
            $stmt = $pdo->prepare("INSERT INTO locations (image, name, city, country) VALUES (:image, :name, :city, :country)");
            $stmt->bindParam(':image', $image);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':city', $city);
            $stmt->bindParam(':country', $country);
            
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
}