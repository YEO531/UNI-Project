<?php
session_start();
require_once __DIR__ . '/../config/config.php';

function login($email, $password) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'role' => $user['role'],
            'name' => $user['name']
        ];
        return true;
    }
    return false;
}

function register($name, $email, $phone, $password, $role = 'student') {
    global $pdo;
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare(
        "INSERT INTO users (name, email, phone, password_hash, role) VALUES (?, ?, ?, ?, ?)"
    );
    return $stmt->execute([$name, $email, $phone, $hash, $role]);
}

function checkAuth() {
    if (!isset($_SESSION['user'])) {
        header('Location: /public/login.php');
        exit;
    }
}

function checkRole($role) {
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== $role) {
        header('Location: /public/login.php');
        exit;
    }
}