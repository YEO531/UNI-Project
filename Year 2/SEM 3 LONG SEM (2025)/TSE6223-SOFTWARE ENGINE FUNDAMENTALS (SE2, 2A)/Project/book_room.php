<?php
require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/controllers/BookingController.php';
checkAuth();
checkRole('student');
$bookingCtrl = new BookingController($pdo);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ok = $bookingCtrl->bookRoom(
        $_SESSION['user']['id'],
        $_POST['room_id'],
        $_POST['date']
    );
    header('Location: appointments.php'); exit;
}
?>
<!DOCTYPE html><html><body>
<h1>Book Room</h1>
<form method="post">
    <input type="hidden" name="room_id" value="<?= $_GET['room_id'] ?>" />
    <label>Date: <input type="date" name="date" required></label>
    <button>Submit</button>
</form>
</body></html>