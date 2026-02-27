<?php
session_start();
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'student') header("Location: student/dashboard.php");
    else if ($_SESSION['role'] == 'driver') header("Location: driver/dashboard.php");
    else if ($_SESSION['role'] == 'admin') header("Location: admin/dashboard.php");
} else {
    header("Location: login.php");
}
exit();
?>