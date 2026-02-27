<?php
include '../db.php';
session_start();
$action = $_POST['action'] ?? '';

if ($action == 'start_ride') {
    if (!isset($_SESSION['user_id'])) exit(json_encode(['success' => false]));
    $stmt = $conn->prepare("SELECT bus_id FROM bus_assignments WHERE destination_id = ? AND time_id = ?");
    $stmt->bind_param("ii", $_POST['destination_id'], $_POST['time_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $bus_id = $row['bus_id'];
    } else {
        echo json_encode(['success' => false, 'message' => 'No bus assigned to this destination and time']);
        exit();
    }
    $stmt = $conn->prepare("SELECT id FROM rides WHERE driver_id = ? AND trip_date = ? AND status IN ('pending', 'started')");
    $stmt->bind_param("is", $_SESSION['user_id'], $_POST['trip_date']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'You have an active ride for this date']);
        exit();
    }
    $stmt = $conn->prepare("INSERT INTO rides (driver_id, bus_id, destination_id, time_id, status, started_at, trip_date) VALUES (?, ?, ?, ?, 'started', NOW(), ?)");
    $stmt->bind_param("iiiis", $_SESSION['user_id'], $bus_id, $_POST['destination_id'], $_POST['time_id'], $_POST['trip_date']);
    echo json_encode(['success' => $stmt->execute()]);

} elseif ($action == 'cancel_ride') {
    $stmt = $conn->prepare("UPDATE rides SET status = 'cancelled' WHERE driver_id = ? AND status = 'started'");
    $stmt->bind_param("i", $_SESSION['user_id']);
    echo json_encode(['success' => $stmt->execute()]);

} elseif ($action == 'end_ride') {
    $stmt = $conn->prepare("UPDATE rides SET status = 'ended', ended_at = NOW() WHERE driver_id = ? AND status = 'started'");
    $stmt->bind_param("i", $_SESSION['user_id']);
    echo json_encode(['success' => $stmt->execute()]);

} elseif ($action == 'get_rides') {
    if ($_SESSION['role'] != 'admin') exit();
    $result = $conn->query("SELECT r.id, r.driver_id, r.bus_id, r.destination_id, r.time_id, r.status, r.started_at, r.ended_at, r.trip_date, CONCAT(u.first_name, ' ', u.last_name) as driver_name, b.reg_number as bus_reg, d.name as destination_name, bt.time FROM rides r JOIN users u ON r.driver_id = u.id JOIN buses b ON r.bus_id = b.id JOIN destinations d ON r.destination_id = d.id JOIN bus_times bt ON r.time_id = bt.id");
    $rides = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($rides);

} elseif ($action == 'delete_ride') {
    if ($_SESSION['role'] != 'admin') exit();
    $stmt = $conn->prepare("DELETE FROM rides WHERE id = ?");
    $stmt->bind_param("i", $_POST['id']);
    echo json_encode(['success' => $stmt->execute()]);

} elseif ($action == 'update_location') {
    if (!isset($_SESSION['user_id'])) exit(json_encode(['success' => false]));
    $latitude = $_POST['latitude'] ?? '';
    $longitude = $_POST['longitude'] ?? '';
    if ($latitude && $longitude) {
        $coords = $latitude . ',' . $longitude;
        $stmt = $conn->prepare("UPDATE rides SET last_map_coords = ? WHERE driver_id = ? AND status = 'started'");
        $stmt->bind_param("si", $coords, $_SESSION['user_id']);
        echo json_encode(['success' => $stmt->execute()]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid coordinates']);
    }

} elseif ($action == 'get_latest_coords') {
    // --- UPDATED: Allow both Admin AND Student to track ---
    if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'student')) {
        exit(json_encode(['success' => false]));
    }
    
    $ride_id = $_POST['ride_id'] ?? 0;
    
    $stmt = $conn->prepare("SELECT last_map_coords, status FROM rides WHERE id = ?");
    $stmt->bind_param("i", $ride_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode(['success' => true, 'coords' => $row['last_map_coords'], 'status' => $row['status']]);
    } else {
        echo json_encode(['success' => false]);
    }
}
?>