<?php
class AppointmentController {
    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    public function request($studentId, $roomId, $datetime) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO appointments (student_id, room_id, date) VALUES (?, ?, ?)"
        );
        return $stmt->execute([$studentId, $roomId, $datetime]);
    }
    public function listAll($role, $userId) {
        if ($role === 'student') {
            $stmt = $this->pdo->prepare(
                "SELECT a.*, r.type FROM appointments a JOIN rooms r ON a.room_id=r.id WHERE a.student_id = ?"
            );
            $stmt->execute([$userId]);
        } else {
            $stmt = $this->pdo->query(
                "SELECT a.*, u.name AS student_name, r.type FROM appointments a
                 JOIN users u ON a.student_id=u.id
                 JOIN rooms r ON a.room_id=r.id"
            );
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function changeStatus($id, $status) {
        $stmt = $this->pdo->prepare(
            "UPDATE appointments SET status = ? WHERE id = ?"
        );
        return $stmt->execute([$status, $id]);
    }
}