<?php
class User
{
    private $firstname;
    private $lastname;
    private $typeOfUser;
    private $email;
    private $location_id;
    private string $password;
    private $profileImg;

    /**
     * Get the value of firstname
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set the value of firstname
     *
     * @return  self
     */
    public function setFirstname($firstname)
    {
        if (empty(trim($firstname))) {
            throw new Exception("Voornaam is verplicht.");
        }

        $reValid = '/^(?!.*\s\s)[A-Za-z]+([-\' ][A-Za-z]+)*$/';

        if (!preg_match($reValid, $firstname)) {
            throw new Exception("Voornaam is niet geldig.");
        }

        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get the value of lastname
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set the value of lastname
     *
     * @return  self
     */
    public function setLastname($lastname)
    {
        if (empty(trim($lastname))) {
            throw new Exception("Achternaam is verplicht.");
        }

        $reValid = '/^(?!.*\s\s)[A-Za-z]+([-\' ][A-Za-z]+)*$/';

        if (!preg_match($reValid, $lastname)) {
            throw new Exception("Achternaam is niet geldig.");
        }

        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get the value of typeOfUser
     */ 
    public function getTypeOfUser()
    {
        return $this->typeOfUser;
    }

    /**
     * Set the value of typeOfUser
     *
     * @return  self
     */ 
    public function setTypeOfUser($typeOfUser)
    {
        $this->typeOfUser = $typeOfUser;

        return $this;
    }

    /**
     * Get the value of email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @return  self
     */
    public function setEmail($email)
    {
        if (empty(trim($email))) {
            throw new Exception("Email is verplicht.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email is niet geldig.");
        }

        $this->email = $email;

        return $this;
    }
        /**
     * Get the value of location_id
     */ 
    public function getLocation_id()
    {
        return $this->location_id;
    }

    /**
     * Set the value of location_id
     *
     * @return  self
     */ 
    public function setLocation_id($location_id)
    {
        $this->location_id = $location_id;

        return $this;
    }

    /**
     * Get the value of password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set the value of password
     *
     * @return  self
     */

         /**
     * Get the value of profileImg
     */ 
    public function getProfileImg()
    {
        return $this->profileImg;
    }

    /**
     * Set the value of profileImg
     *
     * @return  self
     */ 
    public function setProfileImg($profileImg)
    {
        $this->profileImg = $profileImg;

        return $this;
    }

    public function setPassword($password)
    {
        if (empty(trim($password))) {
            throw new Exception("You must fill in a password.");
        }

        if (strlen($password) < 8) {
            throw new Exception("Password should be at least 8 characters long.");
        }

        $reValid = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*])[a-zA-Z\d!@#$%^&*]{8,}$/';
        if (!preg_match($reValid, $password)) {
            throw new Exception("Password must contain at least 1 capital, 1 lowercase letter, 1 number and 1 special character (!@#$%^&*).");
        }

        $this->password = password_hash($password, PASSWORD_DEFAULT);
        return $this;
    }

    public function addUser(PDO $pdo, $typeOfUser): int|bool
    {
        try {
            $stmt = $pdo->prepare("INSERT INTO users (firstname, lastname, typeOfUser, email, password) VALUES (:firstname, :lastname, :typeOfUser, :email, :password)");
            $stmt->bindParam(':firstname', $this->firstname);
            $stmt->bindParam(':lastname', $this->lastname);
            $stmt->bindParam(':typeOfUser', $typeOfUser);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':password', $this->password);

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

    public function addToLocation(PDO $pdo, $user_id)
    {
        try {
            $stmt = $pdo->prepare("INSERT INTO user_locations (user_id, location_id) VALUES (:user_id, :location_id)");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':location_id', $this->location_id);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log('Database error in addToLocation(): ' . $e->getMessage());
            throw new Exception('Database error: Unable to add user to location', 0, $e);
        }
    }

    public static function getUserByEmail(PDO $pdo, string $email)
    {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND status = 1");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return null;
        }
    }

    public static function getUserById(PDO $pdo, $id)
    {
        try {
            if ($id == 0) {
                $stmt = $pdo->prepare("SELECT users.*, user_locations.location_id
                FROM users, user_locations
                WHERE users.id = 1
                  AND users.status = 1
                  AND users.id = user_locations.user_id;
                ");
            } else {
                $stmt = $pdo->prepare("SELECT users.*, user_locations.location_id
                FROM users, user_locations
                WHERE users.id = :id
                  AND users.status = 1
                  AND users.id = user_locations.user_id;
                ");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return null;
        }
    }

    public function updateUser(PDO $pdo, $user_id, $typeOfUser): bool
    {
        try {
            $stmt = $pdo->prepare("UPDATE users SET firstname = :firstname, lastname = :lastname, typeOfUser = :typeOfUser, email = :email WHERE id = :user_id");
            $stmt->bindParam(':firstname', $this->firstname);
            $stmt->bindParam(':lastname', $this->lastname);
            $stmt->bindParam(':typeOfUser', $typeOfUser);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':user_id', $user_id);


            // Controleer of de SQL-instructie met succes is uitgevoerd
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

    public static function deleteUser(PDO $pdo, $id)
    {
        try {
            $stmt = $pdo->prepare("UPDATE users SET status = 0 WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log('Database error in deleteUser(): ' . $e->getMessage());
            throw new Exception('Database error: Unable to update read status');
        }
    }

    public static function getAll(PDO $pdo)
    {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE status = 1");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $users ?: [];
        } catch (PDOException $e) {
            error_log('Database error in getUsers(): ' . $e->getMessage());
            throw new Exception('Database error: Unable to retrieve users');
        }
    }

    public function updateProfileImg(PDO $pdo, $user_id): bool
    {
        try {
            $stmt = $pdo->prepare("UPDATE users SET profileImg = :profileImg WHERE id = :user_id");
            $stmt->bindParam(':profileImg', $this->profileImg);
            $stmt->bindParam(':user_id', $user_id);

            // Controleer of de SQL-instructie met succes is uitgevoerd
            if ($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log('Error: ' . $e->getMessage());
            return false;
        }
    }

    public static function updateTypeOfUser(PDO $pdo, $user_id, $typeOfUser): bool
    {
        try {
            $stmt = $pdo->prepare("UPDATE users SET typeOfUser = :typeOfUser WHERE id = :user_id");
            $stmt->bindParam(':typeOfUser', $typeOfUser);
            $stmt->bindParam(':user_id', $user_id);

            // Controleer of de SQL-instructie met succes is uitgevoerd
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

    public function updatePassword(PDO $pdo, $user_id): bool
    {
        try {
            $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :user_id");
            $stmt->bindParam(':password', $this->password);
            $stmt->bindParam(':user_id', $user_id);

            // Controleer of de SQL-instructie met succes is uitgevoerd
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
