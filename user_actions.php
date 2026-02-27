<?php
include '../db.php';
session_start();
$action = $_POST['action'] ?? '';
if ($action == 'signup') {
    // Validate inputs (basic)
    if ($_POST['password'] != $_POST['confirm_password']) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
        exit();
    }
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $_POST['email']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        exit();
    }
    $hashed_pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, student_id, email, phone, gender, password, role) VALUES (?, ?, ?, ?, ?, ?, ?, 'student')");
    $stmt->bind_param("sssssss", $_POST['first_name'], $_POST['last_name'], $_POST['student_id'], $_POST['email'], $_POST['phone'], $_POST['gender'], $hashed_pass);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Signup failed']);
    }
} elseif ($action == 'login') {
    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $_POST['email']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        if (password_verify($_POST['password'], $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['role'] = $row['role'];
            $redirect = $row['role'] == 'student' ? 'student/dashboard.php' : ($row['role'] == 'driver' ? 'driver/dashboard.php' : 'admin/dashboard.php');
            echo json_encode(['success' => true, 'redirect' => $redirect]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid password']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Email not found']);
    }
} elseif ($action == 'edit_profile') {
    if (!isset($_SESSION['user_id'])) exit(json_encode(['success' => false]));
    $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, student_id = ?, email = ?, phone = ?, gender = ? WHERE id = ?");
    $stmt->bind_param("ssssssi", $_POST['first_name'], $_POST['last_name'], $_POST['student_id'], $_POST['email'], $_POST['phone'], $_POST['gender'], $_SESSION['user_id']);
    echo json_encode(['success' => $stmt->execute()]);
} elseif ($action == 'change_password') {
    if (!isset($_SESSION['user_id'])) exit(json_encode(['success' => false]));
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if (password_verify($_POST['current_pass'], $row['password'])) {
        if ($_POST['new_pass'] != $_POST['confirm_new_pass']) {
            echo json_encode(['success' => false, 'message' => 'New passwords do not match']);
            exit();
        }
        $hashed = password_hash($_POST['new_pass'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed, $_SESSION['user_id']);
        echo json_encode(['success' => $stmt->execute()]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Current password incorrect']);
    }
} elseif ($action == 'add_user') { // Admin only
    if ($_SESSION['role'] != 'admin') exit();
    $role = $_POST['role'];
    $hashed_pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $student_id = ($role == 'student') ? ($_POST['student_id'] ?? null) : null;
    $gender = ($role == 'student') ? ($_POST['gender'] ?? null) : null;
    $driving_license = ($role == 'driver') ? ($_POST['driving_license'] ?? null) : null;
    $nid = ($role == 'driver') ? ($_POST['nid'] ?? null) : null;
    $years_of_experience = ($role == 'driver') ? ($_POST['years_of_experience'] ?? null) : null;
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, student_id, email, phone, gender, password, role, driving_license, nid, years_of_experience) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssssi", $_POST['first_name'], $_POST['last_name'], $student_id, $_POST['email'], $_POST['phone'], $gender, $hashed_pass, $role, $driving_license, $nid, $years_of_experience);
    echo json_encode(['success' => $stmt->execute()]);
} elseif ($action == 'edit_user') { // Admin
    if ($_SESSION['role'] != 'admin') exit();
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->bind_param("i", $_POST['user_id']);
    $stmt->execute();
    $role = $stmt->get_result()->fetch_assoc()['role'];
    $student_id = ($role == 'student') ? ($_POST['student_id'] ?? null) : null;
    $gender = ($role == 'student') ? ($_POST['gender'] ?? null) : null;
    $driving_license = ($role == 'driver') ? ($_POST['driving_license'] ?? null) : null;
    $nid = ($role == 'driver') ? ($_POST['nid'] ?? null) : null;
    $years_of_experience = ($role == 'driver') ? ($_POST['years_of_experience'] ?? null) : null;
    $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, student_id = ?, email = ?, phone = ?, gender = ?, driving_license = ?, nid = ?, years_of_experience = ? WHERE id = ?");
    $stmt->bind_param("ssssssssii", $_POST['first_name'], $_POST['last_name'], $student_id, $_POST['email'], $_POST['phone'], $gender, $driving_license, $nid, $years_of_experience, $_POST['user_id']);
    echo json_encode(['success' => $stmt->execute()]);
} elseif ($action == 'delete_user') { // Admin
    if ($_SESSION['role'] != 'admin') exit();
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $_POST['user_id']);
    echo json_encode(['success' => $stmt->execute()]);
} elseif ($action == 'admin_change_password') { // Admin only
    if ($_SESSION['role'] != 'admin') exit();
    if ($_POST['new_password'] != $_POST['confirm_new_password']) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
        exit();
    }
    $hashed = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed, $_POST['user_id']);
    echo json_encode(['success' => $stmt->execute()]);
} elseif ($action == 'get_users') {
    if ($_SESSION['role'] != 'admin') exit();
    $result = $conn->query("SELECT id, first_name, last_name, student_id, email, phone, gender, role, driving_license, nid, years_of_experience FROM users");
    $users = [];
    while ($row = $result->fetch_assoc()) $users[] = $row;
    echo json_encode($users);
}
?>