<?php
require_once '../includes/config.php';
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

// Handle note deletion
if (isset($data['action']) && $data['action'] === 'delete' && isset($data['note_id'])) {
    $note_id = intval($data['note_id']);
    $stmt = $conn->prepare('DELETE FROM calendar_notes WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $note_id, $user_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Note deleted successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete note.']);
    }
    $stmt->close();
    exit;
}

// Handle note editing
if (isset($data['action']) && $data['action'] === 'edit' && isset($data['note_id']) && isset($data['note'])) {
    $note_id = intval($data['note_id']);
    $note = trim($data['note']);
    $stmt = $conn->prepare('UPDATE calendar_notes SET note = ? WHERE id = ? AND user_id = ?');
    $stmt->bind_param('sii', $note, $note_id, $user_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Note updated successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update note.']);
    }
    $stmt->close();
    exit;
}

$date = $data['date'] ?? '';
$note = trim($data['note'] ?? '');

if (!$date || !$note) {
    echo json_encode(['status' => 'error', 'message' => 'Date and note are required.']);
    exit;
}

$stmt = $conn->prepare('INSERT INTO calendar_notes (user_id, note_date, note) VALUES (?, ?, ?)');
$stmt->bind_param('iss', $user_id, $date, $note);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Note added successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error.']);
}
$stmt->close(); 