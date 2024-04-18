<?php
class Manager extends User
{
    public static function getAllUsers(PDO $pdo)
    {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE typeOfUser = 'user' AND status = 1");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $users ?: [];
        } catch (PDOException $e) {
            error_log('Database error in getAllUsers(): ' . $e->getMessage());
            throw new Exception('Database error: Unable to retrieve users', 0, $e);
        }
    }  
    
    
    public static function getUserById(PDO $pdo, $id)
    {
        try {
            if ($id == 0) {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE typeOfUser = 'user' AND status = 1 LIMIT 1");
            } else {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE typeOfUser = 'user' AND id = :id AND status = 1");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return null;
        }
    }
}