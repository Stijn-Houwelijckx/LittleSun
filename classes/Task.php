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

}
