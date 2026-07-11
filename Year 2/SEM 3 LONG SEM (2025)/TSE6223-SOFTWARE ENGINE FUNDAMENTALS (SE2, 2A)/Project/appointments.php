<?php
require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/controllers/BookingController.php';
checkAuth();
checkRole('student');
$ctrl = new BookingController($pdo);
$bookings = $ctrl->listBookings('student', $_SESSION['user']['id']);
?>
<!DOCTYPE html><html><body>
<h1>Your Bookings</h1>
<table>
    <tr><th>Room</th><th>Date</th><th>Status</th></tr>
    <?php foreach($bookings as $b): ?>
    <tr>
        <td><?= htmlspecialchars($b['type']) ?></td>
        <td><?= $b['date'] ?></td>
        <td><?= $b['status'] ?></td>
    </tr>
    <?php endforeach; ?>
</table>
</body></html>