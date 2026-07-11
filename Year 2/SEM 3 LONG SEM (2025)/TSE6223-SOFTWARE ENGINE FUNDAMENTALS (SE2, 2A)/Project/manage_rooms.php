<?php
require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/controllers/RoomController.php';
checkAuth();
checkRole('admin');
$ctrl = new RoomController($pdo);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ctrl->addRoom($_POST['type'], $_POST['capacity']);
    header('Location: manage_rooms.php'); exit;
}
$rooms = $ctrl->listRooms();
?>
<!DOCTYPE html><html><body>
<h1>Manage Rooms</h1>
<form method="post">
    <input name="type" placeholder="Type" required />
    <input name="capacity" type="number" placeholder="Capacity" required />
    <button>Add Room</button>
</form>
<table>
    <tr><th>ID</th><th>Type</th><th>Capacity</th><th>Status</th></tr>
    <?php foreach($rooms as $room): ?>
    <tr>
        <td><?= $room['id'] ?></td>
        <td><?= htmlspecialchars($room['type']) ?></td>
        <td><?= $room['capacity'] ?></td>
        <td><?= $room['status'] ?></td>
    </tr>
    <?php endforeach; ?>
</table>
</body></html>