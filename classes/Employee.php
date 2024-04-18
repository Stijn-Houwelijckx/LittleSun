<?php
class Employee extends User
{
    public static function getAllEmployees(PDO $pdo)
    {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE typeOfUser = 'employee' AND status = 1");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $users ?: [];
        } catch (PDOException $e) {
            error_log('Database error in getAllUsers(): ' . $e->getMessage());
            throw new Exception('Database error: Unable to retrieve users', 0, $e);
        }
    }
}