<?php
class Employee extends User
{
    public static function getAllEmployees(PDO $pdo)
    {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE typeOfUser = 'employee' AND status = 1");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return null;
        }
    }

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

    public static function getEmployeesByTaskForLocation(PDO $pdo, $task_id, $location_id)
    {
        try {
            $stmt = $pdo->prepare("SELECT users.* FROM users, user_tasks, user_locations WHERE user_tasks.user_id = users.id AND user_tasks.task_id = :task_id AND user_tasks.is_assigned = 1 AND user_locations.user_id = users.id AND user_locations.location_id = :location_id AND users.typeOfUser = 'employee'");
            $stmt->bindParam(':task_id', $task_id, PDO::PARAM_INT);
            $stmt->bindParam(':location_id', $location_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return null;
        }
    }

    public static function getEmployeesByAvailability (PDO $pdo, $date, $location_id)
    {
        try {
            $query = "
                    SELECT users.*
                    FROM users
                    WHERE users.id NOT IN (
                        SELECT DISTINCT time_off_requests.user_id
                        FROM time_off_requests
                        WHERE CONCAT(:date1, ' 09:00:00') BETWEEN time_off_requests.start_date AND time_off_requests.end_date
                        AND CONCAT(:date2, ' 20:00:00') BETWEEN time_off_requests.start_date AND time_off_requests.end_date
                        AND time_off_requests.status = 'Approved'
                    )
                    AND users.id IN (
                        SELECT DISTINCT user_locations.user_id
                        FROM user_locations
                        WHERE user_locations.location_id = :location_id
                    )
                    AND users.status = 1
                    AND users.typeOfUser = 'employee';
                    ";

            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':date1', $date);
            $stmt->bindParam(':date2', $date);
            $stmt->bindParam(':location_id', $location_id);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return null;
        }
    }
}

// 2024-05-17