<?php
require_once __DIR__ . '/../src/auth.php';
checkAuth();
if ($_SESSION['user']['role']==='admin') {
    header('Location: admin/dashboard.php');
} else {
    header('Location: student/dashboard.php');
}
exit;