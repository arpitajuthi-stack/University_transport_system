<?php
include '../db.php';
session_start();
if ($_SESSION['role'] != 'admin') exit();
$action = $_POST['action'] ?? '';
if ($action == 'add_bus') {
    $stmt = $conn->prepare("SELECT id FROM buses WHERE reg_number = ?");
    $stmt->bind_param("s", $_POST['reg_number']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Registration number already exists']);
        exit();
    }
    $is_female_only = intval($_POST['is_female_only'] ?? 0); // Ensure int
    $stmt = $conn->prepare("INSERT INTO buses (reg_number, seats, is_female_only) VALUES (?, ?, ?)");
    $stmt->bind_param("sii", $_POST['reg_number'], $_POST['seats'], $is_female_only);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }
} elseif ($action == 'edit_bus') {
    $is_female_only = intval($_POST['is_female_only'] ?? 0);
    $stmt = $conn->prepare("UPDATE buses SET reg_number = ?, seats = ?, is_female_only = ? WHERE id = ?");
    $stmt->bind_param("siii", $_POST['reg_number'], $_POST['seats'], $is_female_only, $_POST['id']);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }
} elseif ($action == 'delete_bus') {
    $bus_id = $_POST['id'];
    // Delete dependent tickets
    $stmt = $conn->prepare("DELETE FROM tickets WHERE bus_id = ?");
    $stmt->bind_param("i", $bus_id);
    $stmt->execute();
    // Delete dependent rides
    $stmt = $conn->prepare("DELETE FROM rides WHERE bus_id = ?");
    $stmt->bind_param("i", $bus_id);
    $stmt->execute();
    // Then delete bus (assignments will cascade delete due to ON DELETE CASCADE)
    $stmt = $conn->prepare("DELETE FROM buses WHERE id = ?");
    $stmt->bind_param("i", $bus_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }
} elseif ($action == 'assign_bus') {
    $stmt = $conn->prepare("SELECT id FROM bus_assignments WHERE bus_id = ? AND destination_id = ? AND time_id = ?");
    $stmt->bind_param("iii", $_POST['bus_id'], $_POST['destination_id'], $_POST['time_id']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Assignment already exists for this bus, destination, and time']);
        exit();
    }
    $stmt = $conn->prepare("INSERT INTO bus_assignments (bus_id, destination_id, time_id) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $_POST['bus_id'], $_POST['destination_id'], $_POST['time_id']);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }
} elseif ($action == 'edit_assignment') {
    // Check if the new combination already exists (excluding the current id)
    $stmt = $conn->prepare("SELECT id FROM bus_assignments WHERE bus_id = ? AND destination_id = ? AND time_id = ? AND id != ?");
    $stmt->bind_param("iiii", $_POST['bus_id'], $_POST['destination_id'], $_POST['time_id'], $_POST['id']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Assignment already exists for this bus, destination, and time']);
        exit();
    }
    $stmt = $conn->prepare("UPDATE bus_assignments SET bus_id = ?, destination_id = ?, time_id = ? WHERE id = ?");
    $stmt->bind_param("iiii", $_POST['bus_id'], $_POST['destination_id'], $_POST['time_id'], $_POST['id']);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }
} elseif ($action == 'delete_assignment') {
    $stmt = $conn->prepare("DELETE FROM bus_assignments WHERE id = ?");
    $stmt->bind_param("i", $_POST['id']);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }
} elseif ($action == 'get_buses') {
    $result = $conn->query("SELECT * FROM buses");
    $buses = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($buses);
} elseif ($action == 'get_assignments') {
    $result = $conn->query("SELECT ba.id, ba.bus_id, ba.destination_id, ba.time_id, b.reg_number, d.name as destination_name, bt.time FROM bus_assignments ba JOIN buses b ON ba.bus_id = b.id JOIN destinations d ON ba.destination_id = d.id JOIN bus_times bt ON ba.time_id = bt.id");
    $assignments = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($assignments);
}
?>