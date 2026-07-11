<?php
require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/controllers/BookingController.php';
checkAuth();
checkRole('admin');
$ctrl = new BookingController($pdo);
if (isset($_GET['action'])) {
    $ctrl->changeStatus($_GET['id'], $_GET['action']);
    header('Location: manage_bookings.php'); exit;
}
$bookings = $ctrl->listBookings('admin', null);
?>
<!DOCTYPE html><html><body>
<h1>Manage Bookings</h1>
<table>
    <tr><th>Student</th><th>Room</th><th>Date</th><th>Status</th><th>Actions</th></tr>
    <?php foreach($bookings as $b): ?>
    <tr>
        <td><?= htmlspecialchars($b['student_name']) ?></td>
        <td><?= htmlspecialchars($b['type']) ?></td>
        <td><?= $b['date'] ?></td>
        <td><?= $b['status'] ?></td>
        <td>
            <a href="?action=approved&id=<?= $b['id'] ?>">Approve</a> |
            <a href="?action=rejected&id=<?= $b['id'] ?>">Reject</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>