<?php
class BookingController {
    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    public function bookRoom($studentId, $roomId, $date) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO bookings (student_id, room_id, date) VALUES (?, ?, ?)"
        );
        return $stmt->execute([$studentId, $roomId, $date]);
    }
    public function listBookings($role, $userId) {
        if ($role === 'student') {
            $stmt = $this->pdo->prepare(
                "SELECT b.*, r.type FROM bookings b JOIN rooms r ON b.room_id=r.id WHERE b.student_id = ?"
            );
            $stmt->execute([$userId]);
        } else {
            $stmt = $this->pdo->query(
                "SELECT b.*, u.name AS student_name, r.type FROM bookings b
                 JOIN users u ON b.student_id=u.id
                 JOIN rooms r ON b.room_id=r.id"
            );
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function changeStatus($bookingId, $status) {
        $stmt = $this->pdo->prepare(
            "UPDATE bookings SET status=? WHERE id=?"
        );
        return $stmt->execute([$status, $bookingId]);
    }
}