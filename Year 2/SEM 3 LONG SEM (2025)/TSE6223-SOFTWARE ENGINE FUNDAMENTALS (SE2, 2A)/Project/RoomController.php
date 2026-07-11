<?php
require_once __DIR__ . '/../policy.php';
class RoomController {
    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    public function listRooms() {
        $stmt = $this->pdo->query("SELECT * FROM rooms");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function addRoom($type, $capacity) {
        $stmt = $this->pdo->prepare("INSERT INTO rooms (type, capacity) VALUES (?, ?)");
        return $stmt->execute([$type, $capacity]);
    }
    public function updateStatus($id, $status) {
        $stmt = $this->pdo->prepare("UPDATE rooms SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
}