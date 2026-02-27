<?php
include '../db.php';
session_start();
$action = $_POST['action'] ?? '';
if ($action == 'buy_ticket') {
    if (!isset($_SESSION['user_id'])) exit(json_encode(['success' => false]));
    $female_only = $_POST['female_only'] ?? 0;
    $stmt = $conn->prepare("SELECT ba.bus_id FROM bus_assignments ba JOIN buses b ON ba.bus_id = b.id WHERE ba.destination_id = ? AND ba.time_id = ? AND b.is_female_only = ?");
    $stmt->bind_param("iii", $_POST['destination_id'], $_POST['time_id'], $female_only);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $bus_id = $row['bus_id'];
    } else {
        echo json_encode(['success' => false, 'message' => 'No bus available for selected options']);
        exit();
    }
    // Check seats availability for that date
    $stmt = $conn->prepare("SELECT SUM(seats) as booked FROM tickets WHERE bus_id = ? AND time_id = ? AND destination_id = ? AND trip_date = ?");
    $stmt->bind_param("iiis", $bus_id, $_POST['time_id'], $_POST['destination_id'], $_POST['trip_date']);
    $stmt->execute();
    $booked = $stmt->get_result()->fetch_assoc()['booked'] ?? 0;
    $stmt = $conn->prepare("SELECT seats FROM buses WHERE id = ?");
    $stmt->bind_param("i", $bus_id);
    $stmt->execute();
    $total_seats = $stmt->get_result()->fetch_assoc()['seats'];
    if ($booked + $_POST['seats'] > $total_seats) {
        echo json_encode(['success' => false, 'message' => 'Not enough seats available']);
        exit();
    }
    $stmt = $conn->prepare("INSERT INTO tickets (student_id, destination_id, time_id, bus_id, seats, female_only, payment_method, trip_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiiisss", $_SESSION['user_id'], $_POST['destination_id'], $_POST['time_id'], $bus_id, $_POST['seats'], $female_only, $_POST['payment_method'], $_POST['trip_date']);
    if ($stmt->execute()) {
        $ticket_id = $stmt->insert_id;
        echo json_encode(['success' => true, 'ticket_id' => $ticket_id, 'payment_method' => $_POST['payment_method']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ticket purchase failed']);
    }
}
?>