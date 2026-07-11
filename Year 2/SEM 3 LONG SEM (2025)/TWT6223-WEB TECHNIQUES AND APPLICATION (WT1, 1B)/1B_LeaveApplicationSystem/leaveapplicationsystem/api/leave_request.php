<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

class LeaveRequest {
    private $conn;
    private $table_name = "leave_requests";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new leave request
    public function create($data) {
        try {
            // Check leave balance
            if (!$this->checkLeaveBalance($data['user_id'], $data['type_id'], $data['total_days'])) {
                return array(
                    "status" => "error",
                    "message" => "Insufficient leave balance"
                );
            }

            // Check for overlapping leaves
            if ($this->checkOverlappingLeaves($data['user_id'], $data['start_date'], $data['end_date'])) {
                return array(
                    "status" => "error",
                    "message" => "You already have a leave request for these dates"
                );
            }

            $query = "INSERT INTO " . $this->table_name . "
                    (user_id, type_id, start_date, end_date, total_days, reason)
                    VALUES
                    (:user_id, :type_id, :start_date, :end_date, :total_days, :reason)";

            $stmt = $this->conn->prepare($query);

            // Sanitize input
            $user_id = htmlspecialchars(strip_tags($data['user_id']));
            $type_id = htmlspecialchars(strip_tags($data['type_id']));
            $start_date = htmlspecialchars(strip_tags($data['start_date']));
            $end_date = htmlspecialchars(strip_tags($data['end_date']));
            $total_days = htmlspecialchars(strip_tags($data['total_days']));
            $reason = htmlspecialchars(strip_tags($data['reason']));

            // Bind values
            $stmt->bindParam(":user_id", $user_id);
            $stmt->bindParam(":type_id", $type_id);
            $stmt->bindParam(":start_date", $start_date);
            $stmt->bindParam(":end_date", $end_date);
            $stmt->bindParam(":total_days", $total_days);
            $stmt->bindParam(":reason", $reason);

            if ($stmt->execute()) {
                $request_id = $this->conn->lastInsertId();
                
                // Create notification
                $this->createNotification($user_id, "Leave Request Submitted", 
                    "Your leave request has been submitted and is pending approval.");

                return array(
                    "status" => "success",
                    "message" => "Leave request created successfully",
                    "request_id" => $request_id
                );
            }

            return array(
                "status" => "error",
                "message" => "Unable to create leave request"
            );

        } catch(PDOException $e) {
            return array(
                "status" => "error",
                "message" => $e->getMessage()
            );
        }
    }

    // Get leave requests for a user
    public function getUserRequests($user_id) {
        try {
            $query = "SELECT lr.*, lt.name as leave_type
                     FROM " . $this->table_name . " lr
                     JOIN leave_types lt ON lr.type_id = lt.type_id
                     WHERE lr.user_id = :user_id
                     ORDER BY lr.created_at DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();

            return $stmt->fetchAll();

        } catch(PDOException $e) {
            return array(
                "status" => "error",
                "message" => $e->getMessage()
            );
        }
    }

    // Check leave balance
    private function checkLeaveBalance($user_id, $type_id, $days) {
        try {
            $query = "SELECT (total_days - used_days) as available_days
                     FROM leave_balances
                     WHERE user_id = :user_id
                     AND type_id = :type_id
                     AND year = YEAR(CURRENT_DATE())";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->bindParam(":type_id", $type_id);
            $stmt->execute();

            $result = $stmt->fetch();
            return $result && $result['available_days'] >= $days;

        } catch(PDOException $e) {
            return false;
        }
    }

    // Check for overlapping leaves
    private function checkOverlappingLeaves($user_id, $start_date, $end_date) {
        try {
            $query = "SELECT COUNT(*) as count
                     FROM " . $this->table_name . "
                     WHERE user_id = :user_id
                     AND status != 'cancelled'
                     AND (
                         (start_date <= :end_date AND end_date >= :start_date)
                         OR (start_date BETWEEN :start_date AND :end_date)
                         OR (end_date BETWEEN :start_date AND :end_date)
                     )";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->bindParam(":start_date", $start_date);
            $stmt->bindParam(":end_date", $end_date);
            $stmt->execute();

            $result = $stmt->fetch();
            return $result['count'] > 0;

        } catch(PDOException $e) {
            return false;
        }
    }

    // Create notification
    private function createNotification($user_id, $title, $message) {
        try {
            $query = "INSERT INTO notifications (user_id, title, message)
                     VALUES (:user_id, :title, :message)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->bindParam(":title", $title);
            $stmt->bindParam(":message", $message);
            $stmt->execute();

        } catch(PDOException $e) {
            // Log error but don't stop the process
            error_log("Failed to create notification: " . $e->getMessage());
        }
    }
}

// Handle API requests
$database = new Database();
$db = $database->getConnection();
$leaveRequest = new LeaveRequest($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $result = $leaveRequest->create($data);
        echo json_encode($result);
        break;

    case 'GET':
        if (isset($_GET['user_id'])) {
            $result = $leaveRequest->getUserRequests($_GET['user_id']);
            echo json_encode($result);
        } else {
            echo json_encode(array(
                "status" => "error",
                "message" => "User ID is required"
            ));
        }
        break;

    default:
        echo json_encode(array(
            "status" => "error",
            "message" => "Method not allowed"
        ));
        break;
}
?> 