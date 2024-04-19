<?php
class Employee extends User
{
    public static function getAllEmployeesByLocation(PDO $pdo, $location_id)
    {
        try {
            $stmt = $pdo->prepare("SELECT users.* FROM users, user_locations WHERE user_locations.user_id = users.id AND user_locations.location_id = :location_id AND typeOfUser = 'employee'");
            $stmt->bindParam(':location_id', $location_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return null;
        }
    }
}