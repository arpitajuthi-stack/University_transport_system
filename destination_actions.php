<?php
include '../db.php';
session_start();
$action = $_POST['action'] ?? '';
if ($_SESSION['role'] != 'admin' && $action != 'get_times') exit(); // Allow get_times for all authenticated
if ($action == 'add_destination') {
    $start_destination = $_POST['start_destination'];
    $end_destination = $_POST['end_destination'];
    $name = $start_destination . '-' . $end_destination;
    $stmt = $conn->prepare("SELECT id FROM destinations WHERE name = ?");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Destination name already exists']);
        exit();
    }
    $stmt = $conn->prepare("INSERT INTO destinations (name, start_destination, end_destination, distance, fare, start_map_coords, end_map_coords) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssddss", $name, $start_destination, $end_destination, $_POST['distance'], $_POST['fare'], $_POST['start_map_coords'], $_POST['end_map_coords']);
    echo json_encode(['success' => $stmt->execute()]);
} elseif ($action == 'edit_destination') {
    $start_destination = $_POST['start_destination'];
    $end_destination = $_POST['end_destination'];
    $name = $start_destination . '-' . $end_destination;
    $stmt = $conn->prepare("SELECT name FROM destinations WHERE id = ?");
    $stmt->bind_param("i", $_POST['id']);
    $stmt->execute();
    $current_name = $stmt->get_result()->fetch_assoc()['name'];
    if ($name != $current_name) {
        $stmt = $conn->prepare("SELECT id FROM destinations WHERE name = ? AND id != ?");
        $stmt->bind_param("si", $name, $_POST['id']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Destination name already exists']);
            exit();
        }
    }
    $stmt = $conn->prepare("UPDATE destinations SET name = ?, start_destination = ?, end_destination = ?, distance = ?, fare = ?, start_map_coords = ?, end_map_coords = ? WHERE id = ?");
    $stmt->bind_param("sssddssi", $name, $start_destination, $end_destination, $_POST['distance'], $_POST['fare'], $_POST['start_map_coords'], $_POST['end_map_coords'], $_POST['id']);
    echo json_encode(['success' => $stmt->execute()]);
} elseif ($action == 'delete_destination') {
    $stmt = $conn->prepare("DELETE FROM destinations WHERE id = ?");
    $stmt->bind_param("i", $_POST['id']);
    echo json_encode(['success' => $stmt->execute()]);
} elseif ($action == 'set_time') {
    $stmt = $conn->prepare("SELECT id FROM bus_times WHERE destination_id = ? AND time = ?");
    $stmt->bind_param("is", $_POST['destination_id'], $_POST['time']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Time already exists for this destination']);
        exit();
    }
    $stmt = $conn->prepare("INSERT INTO bus_times (destination_id, time) VALUES (?, ?)");
    $stmt->bind_param("is", $_POST['destination_id'], $_POST['time']);
    echo json_encode(['success' => $stmt->execute()]);
} elseif ($action == 'edit_time') {
    $stmt = $conn->prepare("UPDATE bus_times SET destination_id = ?, time = ? WHERE id = ?");
    $stmt->bind_param("isi", $_POST['destination_id'], $_POST['time'], $_POST['id']);
    echo json_encode(['success' => $stmt->execute()]);
} elseif ($action == 'delete_time') {
    $stmt = $conn->prepare("DELETE FROM bus_times WHERE id = ?");
    $stmt->bind_param("i", $_POST['id']);
    echo json_encode(['success' => $stmt->execute()]);
} elseif ($action == 'get_times') {
    $stmt = $conn->prepare("SELECT id, time FROM bus_times WHERE destination_id = ?");
    $stmt->bind_param("i", $_POST['destination_id']);
    $stmt->execute();
    $times = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode($times);
} elseif ($action == 'get_destinations') {
    $result = $conn->query("SELECT * FROM destinations");
    $dests = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($dests);
} elseif ($action == 'get_all_times') {
    $result = $conn->query("SELECT bt.id, bt.destination_id, bt.time, d.name as destination_name FROM bus_times bt JOIN destinations d ON bt.destination_id = d.id");
    $times = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($times);
}
?>