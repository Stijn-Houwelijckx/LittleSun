<?php
class Task {  
    private $task;

    public function getTask()
    {
        return $this->task;
    }
    public function setTask($task)
    {
        $this->task = $task;
        return $this;
    }

    public static function getTaskById(PDO $pdo, $id)
    {
        try {
            $stmt = $pdo->prepare("SELECT id, task FROM taskTypes WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return null;
        }
    }

    public function addTask(PDO $pdo)
    {
        try {
            $stmt = $pdo->prepare("INSERT INTO taskTypes (task) VALUES (:task)");
            $stmt->bindParam(':task', $this->task);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return false;
        }
    }

    public static function linkTasksToUser(PDO $pdo, $user_id)
    {
        try {
            $stmt = $pdo->prepare("INSERT INTO user_tasks (user_id, task_id) SELECT :user_id, id FROM tasktypes");
            $stmt->bindParam(':user_id', $user_id);

            // check if the query was successful
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

    public static function addTaskToUser(PDO $pdo, $user_id, $task_id){
        try {
            $stmt = $pdo->prepare("UPDATE user_tasks SET is_assigned = 1 WHERE user_id = :user_id AND task_id = :task_id");
            $stmt->bindParam(':task_id', $task_id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log('Database error in addTaskToUser(): ' . $e->getMessage());
            throw new Exception('Database error: Unable to update user task', 0, $e);
        }
    }
    
    public static function deleteTask(PDO $pdo, $id)
    {
        try {
            $stmt = $pdo->prepare("UPDATE taskTypes SET status = 0 WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            throw new Exception('Database error: Unable to update read status');
        }
    }

    public static function removeTaskTypesFromUsers(PDO $pdo, $task_id)
    {
        try {
            $stmt = $pdo->prepare("DELETE FROM user_tasks WHERE task_id = :task_id;");
            $stmt->bindParam(':task_id', $task_id);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            throw new Exception('Database error: Unable to update read status');
        }
    }

    public static function getAllTasks(PDO $pdo)
    {
        try {
            $stmt = $pdo->prepare("SELECT * FROM taskTypes WHERE status = 1");
            $stmt->execute();
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $tasks ?: [];
        } catch (PDOException $e) {
            error_log('Database error in getAllTasks(): ' . $e->getMessage());
            throw new Exception('Database error: Unable to retrieve tasks', 0, $e);
        }
    }
}