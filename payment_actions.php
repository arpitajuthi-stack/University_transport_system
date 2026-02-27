<?php
include '../db.php';

$action = $_POST['action'] ?? '';

if ($action == 'complete_payment') {
    $stmt = $conn->prepare("UPDATE tickets SET payment_status = 'paid' WHERE id = ?");
    $stmt->bind_param("i", $_POST['ticket_id']);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Payment completed (simulated)']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Payment update failed']);
    }
}
?>