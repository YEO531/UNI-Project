<?php
require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/helpers.php';
checkAuth();
$user = $_SESSION['user'];
?>
<!DOCTYPE html><html><body>
<h1>Profile</h1>
<p>Name: <?= htmlspecialchars($user['name']) ?></p>
<p>Role: <?= htmlspecialchars($user['role']) ?></p>
<a href="logout.php">Logout</a>
</body></html>