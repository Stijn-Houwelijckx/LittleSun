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
            $stmt = $pdo->prepare("SELECT id FROM taskTypes WHERE id = :id");
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