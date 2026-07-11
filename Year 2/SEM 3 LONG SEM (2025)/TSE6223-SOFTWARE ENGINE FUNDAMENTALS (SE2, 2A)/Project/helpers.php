<?php
// Flash messages
function setFlash($key, $message) {
    $_SESSION['flash'][$key] = $message;
}
function getFlash($key) {
    if (isset($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}

// Redirect helper
function redirect($url) {
    header('Location: ' . $url);
    exit;
}