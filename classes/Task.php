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
            return $stmt->fetchColumn();
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

            // Return the ID of the inserted row
            return $pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return false;
        }
    }

    public static function linkTasksToUser(PDO $pdo, $user_id)
    {
        try {
            $stmt = $pdo->prepare("INSERT INTO user_tasks (user_id, task_id) SELECT :user_id, id FROM tasktypes WHERE status = 1");
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

    public static function addTaskToAllUsers(PDO $pdo, $task_id) {
        try {
            $stmt = $pdo->prepare("INSERT INTO user_tasks (user_id, task_id) SELECT id, :task_id FROM users WHERE typeOfUser = 'employee'");
            $stmt->bindParam(':task_id', $task_id);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            throw new Exception('Database error: Unable to update read status');
        }
    }

    public static function assignTaskToUser(PDO $pdo, $user_id, $task_id, $is_assigned){
        try {
            $stmt = $pdo->prepare("UPDATE user_tasks SET is_assigned = :is_assigned WHERE user_id = :user_id AND task_id = :task_id");
            $stmt->bindParam(':task_id', $task_id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':is_assigned', $is_assigned, PDO::PARAM_INT);

            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log('Database error in assignTaskToUser(): ' . $e->getMessage());
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

    public static function removeTaskTypeFromUsers(PDO $pdo, $task_id)
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

    public static function mytasks(PDO $pdo, $user_id){
        try {
            $stmt = $pdo->prepare("SELECT tasktypes.task FROM tasktypes, user_tasks WHERE tasktypes.id = user_tasks.task_id AND user_tasks.user_id = :user_id AND user_tasks.is_assigned = 1");
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $tasks ?: [];
        } catch (PDOException $e) {
            error_log('Database error in getAllTasks(): ' . $e->getMessage());
            throw new Exception('Database error: Unable to retrieve tasks', 0, $e);
        }
    }

    public static function getTasksByEmployeeId($pdo, $employee_id) {
        $stmt = $pdo->prepare("SELECT tasktypes.* FROM tasktypes, user_tasks WHERE user_tasks.user_id = :employee_id AND tasktypes.id = user_tasks.task_id AND user_tasks.is_assigned = 1");
        $stmt->bindParam(":employee_id", $employee_id);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public static function getTaskInfoById($pdo, $task_id) {
        $stmt = $pdo->prepare("SELECT * FROM tasktypes WHERE id = :task_id");
        $stmt->bindParam(":task_id", $task_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public static function getClosestTaskIdForUser($pdo, $user_id, $end_time)
    {
        // Fetch all tasks for the user
        $sql = "SELECT * FROM calendar WHERE user_id = :user_id AND event_date = :event_date ORDER BY start_time ASC";
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':user_id', $user_id);
        $event_date = $end_time->format('Y-m-d');
        $stmt->bindParam(':event_date', $event_date);

        $stmt->execute();
        $user_tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Initialize variables to keep track of closest task and time difference
        $closest_task_id = null;
        $closest_time_diff = PHP_INT_MAX;

        // Loop through each user task to find the closest one to the end time
        foreach ($user_tasks as $user_task) {
            // Get the task start and end times
            $task_start_time = new DateTime($user_task['start_time']);
            $task_end_time = new DateTime($user_task['end_time']);

            // Calculate the time difference between the end time and task start time
            $time_diff = abs($task_start_time->getTimestamp() - $task_end_time->getTimestamp());

            // Check if this task's start time is closer to the end time than the current closest task
            if ($time_diff < $closest_time_diff) {
                $closest_time_diff = $time_diff;
                $closest_task_id = $user_task['task_id'];
            }
        }

        return $closest_task_id;
    }
}