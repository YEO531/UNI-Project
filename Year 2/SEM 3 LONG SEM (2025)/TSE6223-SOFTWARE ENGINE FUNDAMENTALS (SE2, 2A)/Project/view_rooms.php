<?php
require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/controllers/RoomController.php';
checkAuth();
checkRole('student');
$controller = new RoomController($pdo);
$rooms = $controller->listRooms();
?>
<!DOCTYPE html><html><body>
<h1>Available Rooms</h1>
<table>
    <tr><th>Type</th><th>Capacity</th><th>Status</th><th>Action</th></tr>
    <?php foreach($rooms as $room): ?>
    <tr>
        <td><?= htmlspecialchars($room['type']) ?></td>
        <td><?= $room['capacity'] ?></td>
        <td><?= $room['status'] ?></td>
        <td>
            <?php if($room['status'] === 'available'): ?>
            <a href="book_room.php?room_id=<?= $room['id'] ?>">Book</a>
            <?php else: ?>
            &mdash;
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
</body></html>