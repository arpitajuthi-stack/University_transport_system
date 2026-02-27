# University Transport System Project

Below, I'll provide a complete, functional project for a University Transport System based on your requirements. This is built using **Bootstrap 5** for responsive UI, **jQuery** for DOM manipulation and AJAX, **AJAX** for asynchronous operations (e.g., form submissions without page reload), **PHP 8+** for backend logic, and **MySQL** for the database.

**Assumptions and Notes:**
- The system assumes a single starting location: North South University (NSU).
- Users are differentiated by roles: `student`, `driver`, `admin` (stored in the database).
- For simplicity, "online payment" is simulated (no real gateway integration; it just marks as paid). Cash is handled as "pay on board."
- Female-only option: Only shown to female students, and buses can be marked as female-only.
- Security: Basic validation and password hashing (using `password_hash`). In production, add more (e.g., CSRF, prepared statements everywhere).
- Maps: Google Maps coordinates are stored but not integrated with actual maps (you can add Google Maps API later).
- Sessions are used for authentication.
- Error handling is basic; expand as needed.
- This is a monolithic project; no frameworks like Laravel for simplicity.
- Test on a local server like XAMPP/WAMP with PHP and MySQL.

## Step 1: Database Setup
Create a MySQL database named `university_transport`.

Run the following SQL to create tables:

```sql
CREATE DATABASE university_transport;
USE university_transport;

-- Users Table (for students, drivers, admins)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    student_id VARCHAR(20) DEFAULT NULL, -- For students only
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15),
    gender ENUM('male', 'female') DEFAULT NULL, -- For students
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'driver', 'admin') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Destinations Table
CREATE TABLE destinations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    distance DECIMAL(5,2) NOT NULL, -- in km
    fare DECIMAL(10,2) NOT NULL,
    map_coords VARCHAR(50) NOT NULL, -- e.g., '23.8151,90.4251'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bus Times Table (times for each destination)
CREATE TABLE bus_times (
    id INT AUTO_INCREMENT PRIMARY KEY,
    destination_id INT NOT NULL,
    time TIME NOT NULL, -- e.g., '08:00:00'
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE
);

-- Buses Table
CREATE TABLE buses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reg_number VARCHAR(20) UNIQUE NOT NULL,
    seats INT NOT NULL,
    is_female_only BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bus Assignments (bus to destination and time)
CREATE TABLE bus_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bus_id INT NOT NULL,
    destination_id INT NOT NULL,
    time_id INT NOT NULL,
    FOREIGN KEY (bus_id) REFERENCES buses(id) ON DELETE CASCADE,
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE,
    FOREIGN KEY (time_id) REFERENCES bus_times(id) ON DELETE CASCADE
);

-- Tickets Table
CREATE TABLE tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    destination_id INT NOT NULL,
    time_id INT NOT NULL,
    bus_id INT NOT NULL,
    seats INT NOT NULL,
    female_only BOOLEAN DEFAULT FALSE,
    payment_method ENUM('online', 'cash') NOT NULL,
    payment_status ENUM('pending', 'paid') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (destination_id) REFERENCES destinations(id),
    FOREIGN KEY (time_id) REFERENCES bus_times(id),
    FOREIGN KEY (bus_id) REFERENCES buses(id)
);

-- Rides Table (for drivers to manage rides)
CREATE TABLE rides (
    id INT AUTO_INCREMENT PRIMARY KEY,
    driver_id INT NOT NULL,
    bus_id INT NOT NULL,
    destination_id INT NOT NULL,
    time_id INT NOT NULL,
    status ENUM('pending', 'started', 'cancelled', 'ended') DEFAULT 'pending',
    started_at TIMESTAMP NULL,
    ended_at TIMESTAMP NULL,
    FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (bus_id) REFERENCES buses(id),
    FOREIGN KEY (destination_id) REFERENCES destinations(id),
    FOREIGN KEY (time_id) REFERENCES bus_times(id)
);

-- Payment Options Table (admin sets global options)
CREATE TABLE payment_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL -- e.g., 'online', 'cash'
);

-- Insert default payment options
INSERT INTO payment_options (name) VALUES ('online'), ('cash');

-- Insert an admin for testing (password: admin123 hashed)
INSERT INTO users (first_name, last_name, email, password, role) 
VALUES ('Admin', 'User', 'admin@example.com', '$2y$10$K.Iw6z5fQJ4k0bX8y7Z.ue8zPqL4bXz7fG0pO9jL2h3k4m5n6o7p8', 'admin'); -- Example hash for 'admin123'; generate your own with password_hash('admin123', PASSWORD_DEFAULT) in PHP

ALTER TABLE tickets ADD trip_date DATE NULL;
UPDATE tickets SET trip_date = CURDATE() WHERE trip_date IS NULL;
ALTER TABLE tickets MODIFY trip_date DATE NOT NULL;

ALTER TABLE rides ADD trip_date DATE NULL;
UPDATE rides SET trip_date = CURDATE() WHERE trip_date IS NULL;
ALTER TABLE rides MODIFY trip_date DATE NOT NULL;

ALTER TABLE destinations
ADD COLUMN start_destination VARCHAR(100) NOT NULL DEFAULT '',
ADD COLUMN end_destination VARCHAR(100) NOT NULL DEFAULT '',
ADD COLUMN end_map_coords VARCHAR(50) NOT NULL DEFAULT '',
CHANGE COLUMN map_coords start_map_coords VARCHAR(50) NOT NULL;
```

Note: The hashed password above is an example. In PHP, run `echo password_hash('admin123', PASSWORD_DEFAULT);` to get a real one and replace it.

## Step 2: File Structure
Organize files in a root folder, e.g., `university-transport-system/`.

```
university-transport-system/
├── index.php                  // Home/Login page
├── signup.php                 // Student signup
├── login.php                  // General login (redirects based on role)
├── logout.php                 // Logout
├── db.php                     // Database connection (include in all PHP files)
├── assets/
│   ├── css/
│   │   └── bootstrap.min.css  // Download from https://getbootstrap.com/
│   │   └── custom.css         // Custom styles
│   ├── js/
│   │   └── jquery.min.js      // Download from https://jquery.com/
│   │   └── bootstrap.bundle.min.js  // Bootstrap JS
│   │   └── custom.js          // Custom jQuery/AJAX scripts
├── student/
│   ├── dashboard.php          // Student home
│   ├── edit_profile.php       // Edit profile
│   ├── change_password.php    // Change password
│   ├── buy_ticket.php         // Select dest, time, seat, payment
│   ├── my_tickets.php         // View tickets
├── driver/
│   ├── dashboard.php          // Driver home
│   ├── manage_ride.php        // Select dest, time, start/cancel/end
├── admin/
│   ├── dashboard.php          // Admin home
│   ├── manage_users.php       // Add/edit/delete users
│   ├── manage_destinations.php// Add/edit/delete destinations, set times
│   ├── manage_buses.php       // Add buses, assign to dest/time
│   ├── manage_payments.php    // Set payment options
│   ├── manage_rides.php    // view ride histories
├── ajax/
│   ├── user_actions.php       // AJAX for signup, login, edit, etc.
│   ├── destination_actions.php// AJAX for destinations
│   ├── bus_actions.php        // AJAX for buses and assignments
│   ├── ticket_actions.php     // AJAX for buying tickets
│   ├── ride_actions.php       // AJAX for driver rides
│   ├── payment_actions.php    // AJAX for payments (simulated)
```

Download Bootstrap 5 CSS/JS and jQuery 3+ and place them in `assets/css` and `assets/js`.

## Step 3: Codes for Each File

### db.php (Database Connection)
```php
<?php
$servername = "localhost";
$username = "root"; // Change if needed
$password = ""; // Change if needed
$dbname = "university_transport";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// For prepared statements, use $conn
?>
```

### assets/css/custom.css
```css
body { font-family: Arial, sans-serif; }
.container { max-width: 1200px; }
.form-group { margin-bottom: 15px; }
.alert { margin-top: 10px; }
```

### assets/js/custom.js
```javascript
// General AJAX handler function
function ajaxRequest(url, data, successCallback, errorCallback) {
    $.ajax({
        url: url,
        type: 'POST',
        data: data,
        success: successCallback,
        error: errorCallback || function() { alert('Error occurred'); }
    });
}

// Base ready function; page-specific handlers are in individual pages
$(document).ready(function() {
    // Common code if needed
});
```

### index.php (Home/Login Redirect)
```php
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
```

### signup.php (Student Signup)
```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Signup</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
</head>
<body>
    <div class="container">
        <h2>Student Signup</h2>
        <form id="signupForm">
            <div class="form-group">
                <label>First Name</label>
                <input type="text" name="first_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Last Name</label>
                <input type="text" name="last_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Student ID</label>
                <input type="text" name="student_id" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Gender</label>
                <select name="gender" class="form-control" required>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Signup</button>
        </form>
        <div id="signupAlert"></div>
    </div>
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/custom.js"></script>
    <script>
        $('#signupForm').submit(function(e) {
            e.preventDefault();
            var data = $(this).serialize() + '&action=signup';
            ajaxRequest('ajax/user_actions.php', data, function(response) {
                response = JSON.parse(response);
                if (response.success) {
                    window.location.href = 'login.php';
                } else {
                    $('#signupAlert').html('<div class="alert alert-danger">' + response.message + '</div>');
                }
            });
        });
    </script>
</body>
</html>
```

### login.php (General Login)
```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <form id="loginForm">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        <div id="loginAlert"></div>
        <a href="signup.php">Signup as Student</a>
    </div>
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/custom.js"></script>
    <script>
        $('#loginForm').submit(function(e) {
            e.preventDefault();
            var data = $(this).serialize() + '&action=login';
            ajaxRequest('ajax/user_actions.php', data, function(response) {
                response = JSON.parse(response);
                if (response.success) {
                    window.location.href = response.redirect;
                } else {
                    $('#loginAlert').html('<div class="alert alert-danger">' + response.message + '</div>');
                }
            });
        });
    </script>
</body>
</html>
```

### logout.php
```php
<?php
session_start();
session_destroy();
header("Location: login.php");
exit();
?>
```

### ajax/user_actions.php (Handles User-Related AJAX)
```php
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
```

### student/dashboard.php
```php
<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') header("Location: ../login.php");
include '../db.php';
// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
</head>
<body>
    <div class="container">
        <h2>Welcome, <?php echo $user['first_name']; ?></h2>
        <a href="edit_profile.php">Edit Profile</a> | <a href="change_password.php">Change Password</a> | <a href="buy_ticket.php">Buy Ticket</a> | <a href="my_tickets.php">My Tickets</a> | <a href="../logout.php">Logout</a>
    </div>
    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```

### student/edit_profile.php
```php
<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') header("Location: ../login.php");
include '../db.php';
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
</head>
<body>
    <div class="container">
        <h2>Edit Profile</h2>
        <form id="editProfileForm">
            <div class="form-group">
                <label>First Name</label>
                <input type="text" name="first_name" value="<?php echo $user['first_name']; ?>" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Last Name</label>
                <input type="text" name="last_name" value="<?php echo $user['last_name']; ?>" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Student ID</label>
                <input type="text" name="student_id" value="<?php echo $user['student_id']; ?>" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo $user['email']; ?>" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" value="<?php echo $user['phone']; ?>" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Gender</label>
                <select name="gender" class="form-control" required>
                    <option value="male" <?php if ($user['gender'] == 'male') echo 'selected'; ?>>Male</option>
                    <option value="female" <?php if ($user['gender'] == 'female') echo 'selected'; ?>>Female</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
        </form>
        <div id="editAlert"></div>
        <a href="dashboard.php">Back</a>
    </div>
    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/custom.js"></script>
    <script>
        $('#editProfileForm').submit(function(e) {
            e.preventDefault();
            var data = $(this).serialize() + '&action=edit_profile';
            ajaxRequest('../ajax/user_actions.php', data, function(response) {
                response = JSON.parse(response);
                if (response.success) {
                    alert('Profile updated');
                    window.location.href = 'dashboard.php';
                } else {
                    $('#editAlert').html('<div class="alert alert-danger">Error</div>');
                }
            });
        });
    </script>
</body>
</html>
```

### student/change_password.php
```php
<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') header("Location: ../login.php");
include '../db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
</head>
<body>
    <div class="container">
        <h2>Change Password</h2>
        <form id="changePasswordForm">
            <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="current_pass" class="form-control" required>
            </div>
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_pass" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_new_pass" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Change</button>
        </form>
        <div id="changeAlert"></div>
        <a href="dashboard.php">Back</a>
    </div>
    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/custom.js"></script>
    <script>
        $('#changePasswordForm').submit(function(e) {
            e.preventDefault();
            var data = $(this).serialize() + '&action=change_password';
            ajaxRequest('../ajax/user_actions.php', data, function(response) {
                response = JSON.parse(response);
                if (response.success) {
                    alert('Password changed');
                    window.location.href = 'dashboard.php';
                } else {
                    $('#changeAlert').html('<div class="alert alert-danger">' + (response.message || 'Error') + '</div>');
                }
            });
        });
    </script>
</body>
</html>
```

### student/buy_ticket.php
```php
<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') header("Location: ../login.php");
include '../db.php';
// Fetch destinations
$destinations = $conn->query("SELECT * FROM destinations")->fetch_all(MYSQLI_ASSOC);
// Fetch payment options
$payments = $conn->query("SELECT * FROM payment_options")->fetch_all(MYSQLI_ASSOC);
// Get gender
$stmt = $conn->prepare("SELECT gender FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$gender = $stmt->get_result()->fetch_assoc()['gender'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Buy Ticket</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
</head>
<body>
    <div class="container">
        <h2>Buy Ticket</h2>
        <form id="buyTicketForm">
            <div class="form-group">
                <label>Trip Date</label>
                <input type="date" name="trip_date" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="form-group">
                <label>Destination</label>
                <select name="destination_id" id="destSelect" class="form-control" required>
                    <option value="">Select Destination</option>
                    <?php foreach ($destinations as $dest): ?>
                        <option value="<?php echo $dest['id']; ?>"><?php echo $dest['name']; ?> (Fare: <?php echo $dest['fare']; ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Time</label>
                <select name="time_id" id="timeSelect" class="form-control" required>
                    <option value="">Select Time</option>
                </select> <!-- Populate via AJAX -->
            </div>
            <div class="form-group">
                <label>Seats</label>
                <input type="number" name="seats" min="1" class="form-control" required>
            </div>
            <?php if ($gender == 'female'): ?>
            <div class="form-group">
                <label>Female Only</label>
                <select name="female_only" class="form-control">
                    <option value="0">No</option>
                    <option value="1">Yes</option>
                </select>
            </div>
            <?php endif; ?>
            <div class="form-group">
                <label>Payment</label>
                <select name="payment_method" class="form-control" required>
                    <option value="">Select Payment</option>
                    <?php foreach ($payments as $pay): ?>
                        <option value="<?php echo $pay['name']; ?>"><?php echo ucfirst($pay['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Buy</button>
        </form>
        <div id="ticketAlert"></div>
        <a href="dashboard.php">Back</a>
    </div>
    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/custom.js"></script>
    <script>
        $('#destSelect').change(function() {
            var destId = $(this).val();
            if (destId) {
                ajaxRequest('../ajax/destination_actions.php', {action: 'get_times', destination_id: destId}, function(response) {
                    response = JSON.parse(response);
                    $('#timeSelect').html('<option value="">Select Time</option>');
                    response.forEach(function(time) {
                        $('#timeSelect').append('<option value="' + time.id + '">' + time.time + '</option>');
                    });
                });
            }
        });
        $('#buyTicketForm').submit(function(e) {
            e.preventDefault();
            var data = $(this).serialize() + '&action=buy_ticket';
            ajaxRequest('../ajax/ticket_actions.php', data, function(response) {
                response = JSON.parse(response);
                if (response.success) {
                    if (response.payment_method == 'online') {
                        ajaxRequest('../ajax/payment_actions.php', {action: 'complete_payment', ticket_id: response.ticket_id}, function(payResp) {
                            payResp = JSON.parse(payResp);
                            alert(payResp.message);
                        });
                    } else {
                        alert('Ticket bought. Pay cash on board.');
                    }
                    window.location.href = 'my_tickets.php';
                } else {
                    $('#ticketAlert').html('<div class="alert alert-danger">' + (response.message || 'Error') + '</div>');
                }
            });
        });
    </script>
</body>
</html>
```

### student/my_tickets.php
```php
<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') header("Location: ../login.php");
include '../db.php';
$stmt = $conn->prepare("SELECT t.*, d.name as dest, bt.time as time, r.status as ride_status FROM tickets t JOIN destinations d ON t.destination_id = d.id JOIN bus_times bt ON t.time_id = bt.id LEFT JOIN rides r ON t.bus_id = r.bus_id AND t.destination_id = r.destination_id AND t.time_id = r.time_id AND t.trip_date = r.trip_date WHERE student_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$tickets = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Tickets</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
</head>
<body>
    <div class="container">
        <h2>My Tickets</h2>
        <table id="ticketsTable" class="table">
            <thead>
                <tr><th>Trip Date</th><th>Destination</th><th>Time</th><th>Seats</th><th>Payment</th><th>Status</th><th>Ride Status</th></tr>
            </thead>
            <tbody>
                <?php if (empty($tickets)): ?>
                    <tr><td colspan="7">No tickets yet</td></tr>
                <?php else: ?>
                    <?php foreach ($tickets as $ticket): ?>
                        <tr>
                            <td><?php echo $ticket['trip_date']; ?></td>
                            <td><?php echo $ticket['dest']; ?></td>
                            <td><?php echo $ticket['time']; ?></td>
                            <td><?php echo $ticket['seats']; ?></td>
                            <td><?php echo $ticket['payment_method']; ?></td>
                            <td><?php echo $ticket['payment_status']; ?></td>
                            <td><?php echo $ticket['ride_status'] ?? 'Not Started'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <a href="dashboard.php">Back</a>
    </div>
    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#ticketsTable').DataTable();
        });
    </script>
</body>
</html>
```

### ajax/ticket_actions.php
```php
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
```

### ajax/payment_actions.php (Simulated)
```php
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
```

### driver/dashboard.php
```php
<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'driver') header("Location: ../login.php");
include '../db.php';
// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
// Fetch rides
$stmt = $conn->prepare("SELECT r.*, d.name as dest, bt.time as time FROM rides r JOIN destinations d ON r.destination_id = d.id JOIN bus_times bt ON r.time_id = bt.id WHERE driver_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$rides = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Driver Dashboard</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
</head>
<body>
    <div class="container">
        <h2>Welcome, <?php echo $user['first_name']; ?></h2>
        <a href="manage_ride.php">Manage Ride</a> | <a href="../logout.php">Logout</a>
        <h3>My Rides</h3>
        <table id="ridesTable" class="table">
            <thead>
                <tr><th>Trip Date</th><th>Destination</th><th>Time</th><th>Status</th><th>Started At</th><th>Ended At</th></tr>
            </thead>
            <tbody>
                <?php if (empty($rides)): ?>
                    <tr><td colspan="6">No rides yet</td></tr>
                <?php else: ?>
                    <?php foreach ($rides as $ride): ?>
                        <tr>
                            <td><?php echo $ride['trip_date']; ?></td>
                            <td><?php echo $ride['dest']; ?></td>
                            <td><?php echo $ride['time']; ?></td>
                            <td><?php echo $ride['status']; ?></td>
                            <td><?php echo $ride['started_at'] ?? ''; ?></td>
                            <td><?php echo $ride['ended_at'] ?? ''; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#ridesTable').DataTable();
        });
    </script>
</body>
</html>
```

### driver/manage_ride.php
```php
<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'driver') header("Location: ../login.php");
include '../db.php';
$destinations = $conn->query("SELECT * FROM destinations")->fetch_all(MYSQLI_ASSOC);
// Fetch current ride if any
$stmt = $conn->prepare("SELECT * FROM rides WHERE driver_id = ? AND status IN ('pending', 'started')");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$current_ride = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Ride</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
</head>
<body>
    <div class="container">
        <h2>Manage Ride</h2>
        <?php if (!$current_ride): ?>
        <form id="startRideForm">
            <div class="form-group">
                <label>Trip Date</label>
                <input type="date" name="trip_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="form-group">
                <label>Destination</label>
                <select name="destination_id" id="destSelect" class="form-control" required>
                    <option value="">Select Destination</option>
                    <?php foreach ($destinations as $dest): ?>
                        <option value="<?php echo $dest['id']; ?>"><?php echo $dest['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Time</label>
                <select name="time_id" id="timeSelect" class="form-control" required>
                    <option value="">Select Time</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Start Ride</button>
        </form>
        <?php else: ?>
            <p>Current Ride: Date <?php echo $current_ride['trip_date']; ?>, Destination ID <?php echo $current_ride['destination_id']; ?> at Time ID <?php echo $current_ride['time_id']; ?> - Status: <?php echo $current_ride['status']; ?></p>
            <button id="cancelRide" class="btn btn-warning">Cancel Ride</button>
            <button id="endRide" class="btn btn-danger">End Ride</button>
        <?php endif; ?>
        <div id="rideAlert"></div>
        <a href="dashboard.php">Back</a>
    </div>
    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/custom.js"></script>
    <script>
        $('#destSelect').change(function() {
            var destId = $(this).val();
            if (destId) {
                ajaxRequest('../ajax/destination_actions.php', {action: 'get_times', destination_id: destId}, function(response) {
                    response = JSON.parse(response);
                    $('#timeSelect').html('<option value="">Select Time</option>');
                    response.forEach(function(time) {
                        $('#timeSelect').append('<option value="' + time.id + '">' + time.time + '</option>');
                    });
                });
            }
        });
        $('#startRideForm').submit(function(e) {
            e.preventDefault();
            var data = $(this).serialize() + '&action=start_ride';
            ajaxRequest('../ajax/ride_actions.php', data, function(response) {
                response = JSON.parse(response);
                if (response.success) {
                    location.reload();
                } else {
                    $('#rideAlert').html('<div class="alert alert-danger">' + (response.message || 'Error') + '</div>');
                }
            });
        });
        $('#cancelRide').click(function() {
            if (confirm('Cancel ride?')) {
                ajaxRequest('../ajax/ride_actions.php', {action: 'cancel_ride'}, function(response) {
                    response = JSON.parse(response);
                    if (response.success) location.reload();
                });
            }
        });
        $('#endRide').click(function() {
            if (confirm('End ride?')) {
                ajaxRequest('../ajax/ride_actions.php', {action: 'end_ride'}, function(response) {
                    response = JSON.parse(response);
                    if (response.success) location.reload();
                });
            }
        });
    </script>
</body>
</html>
```

### ajax/ride_actions.php
```php
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
}
?>
```

### admin/dashboard.php
```php
<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') header("Location: ../login.php");
include '../db.php';
// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
</head>
<body>
    <div class="container">
        <h2>Welcome, <?php echo $user['first_name']; ?></h2>
        <a href="manage_users.php">Manage Users</a> | <a href="manage_destinations.php">Manage Destinations</a> | <a href="manage_buses.php">Manage Buses</a> | <a href="manage_payments.php">Manage Payments</a> | <a href="../logout.php">Logout</a>
    </div>
    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```

### admin/manage_users.php
```php
<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') header("Location: ../login.php");
include '../db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <style>
        .btn-close {
            box-sizing: content-box;
            width: 1em;
            height: 1em;
            padding: .25em .25em;
            color: #000;
            background: transparent url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23000'%3e%3cpath d='M.293.293a1 1 0 011.414 0L8 6.586 14.293.293a1 1 0 111.414 1.414L9.414 8l6.293 6.293a1 1 0 01-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 01-1.414-1.414L6.586 8 .293 1.707a1 1 0 010-1.414z'/%3e%3c/svg%3e") center/1em auto no-repeat;
            border: 0;
            border-radius: .25rem;
            opacity: .5;
        }
        .btn-close:hover {
            color: #000;
            text-decoration: none;
            opacity: .75;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Manage Users</h2>
        <form id="addUserForm">
            <div class="form-group">
                <label>First Name</label>
                <input type="text" name="first_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Last Name</label>
                <input type="text" name="last_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role" id="addRoleSelect" class="form-control" required>
                    <option value="">Select Role</option>
                    <option value="admin">Admin</option>
                    <option value="student">Student</option>
                    <option value="driver">Driver</option>
                </select>
            </div>
            <div id="studentFields" style="display:none;">
                <div class="form-group">
                    <label>Student ID</label>
                    <input type="text" name="student_id" class="form-control">
                </div>
                <div class="form-group">
                    <label>Gender</label>
                    <select name="gender" class="form-control">
                        <option value="">Select</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
            </div>
            <div id="driverFields" style="display:none;">
                <div class="form-group">
                    <label>Driving License</label>
                    <input type="text" name="driving_license" class="form-control">
                </div>
                <div class="form-group">
                    <label>NID</label>
                    <input type="text" name="nid" class="form-control">
                </div>
                <div class="form-group">
                    <label>Years of Experience</label>
                    <input type="number" name="years_of_experience" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Add User</button>
        </form>
        <div id="userList"></div> <a href="dashboard.php">Back</a>
    </div>
    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/custom.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script>
        // --- FIX START: Manual handler for closing dynamic modals ---
        $(document).on('click', '.btn-close, [data-bs-dismiss="modal"]', function() {
            $(this).closest('.modal').modal('hide');
        });
        // --- FIX END ---

        $('#addRoleSelect').change(function() {
            var role = $(this).val();
            $('#studentFields').hide();
            $('#driverFields').hide();
            if (role === 'student') {
                $('#studentFields').show();
            } else if (role === 'driver') {
                $('#driverFields').show();
            }
        });
        function loadUsers() {
            ajaxRequest('../ajax/user_actions.php', {action: 'get_users'}, function(response) {
                response = JSON.parse(response);
                var admins = response.filter(user => user.role === 'admin');
                var students = response.filter(user => user.role === 'student');
                var drivers = response.filter(user => user.role === 'driver');
                var html = '<h3>Admins</h3><table id="adminsTable" class="table"><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Actions</th></tr></thead><tbody>';
                admins.forEach(function(user) {
                    html += buildUserRow(user, false, false);
                });
                html += '</tbody></table>';
                html += '<h3>Students</h3><table id="studentsTable" class="table"><thead><tr><th>ID</th><th>Name</th><th>Student ID</th><th>Email</th><th>Phone</th><th>Gender</th><th>Role</th><th>Actions</th></tr></thead><tbody>';
                students.forEach(function(user) {
                    html += buildUserRow(user, true, false);
                });
                html += '</tbody></table>';
                html += '<h3>Drivers</h3><table id="driversTable" class="table"><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Driving License</th><th>NID</th><th>Years of Experience</th><th>Role</th><th>Actions</th></tr></thead><tbody>';
                drivers.forEach(function(user) {
                    html += buildUserRow(user, false, true);
                });
                html += '</tbody></table>';
                $('#userList').html(html);
                $('#adminsTable').DataTable();
                $('#studentsTable').DataTable();
                $('#driversTable').DataTable();
            });
        }
        function buildUserRow(user, showStudentFields, showDriverFields) {
            var row = '<tr><td>' + user.id + '</td><td>' + user.first_name + ' ' + user.last_name + '</td>';
            if (showStudentFields) {
                row += '<td>' + (user.student_id || '') + '</td>';
            }
            row += '<td>' + user.email + '</td><td>' + user.phone + '</td>';
            if (showStudentFields) {
                row += '<td>' + (user.gender || '') + '</td>';
            }
            if (showDriverFields) {
                row += '<td>' + (user.driving_license || '') + '</td><td>' + (user.nid || '') + '</td><td>' + (user.years_of_experience || '') + '</td>';
            }
            row += '<td>' + user.role + '</td><td><button class="btn btn-info editUser" data-id="' + user.id + '" data-first_name="' + user.first_name + '" data-last_name="' + user.last_name + '" data-student_id="' + (user.student_id || '') + '" data-email="' + user.email + '" data-phone="' + user.phone + '" data-gender="' + (user.gender || '') + '" data-role="' + user.role + '" data-driving_license="' + (user.driving_license || '') + '" data-nid="' + (user.nid || '') + '" data-years_of_experience="' + (user.years_of_experience || '') + '">Edit</button> <button class="btn btn-warning changePassword" data-id="' + user.id + '">Change Password</button> <button class="btn btn-danger deleteUser" data-id="' + user.id + '">Delete</button></td></tr>';
            return row;
        }
        loadUsers();
        $('#addUserForm').submit(function(e) {
            e.preventDefault();
            var data = $(this).serialize() + '&action=add_user';
            ajaxRequest('../ajax/user_actions.php', data, function(response) {
                response = JSON.parse(response);
                if (response.success) {
                    loadUsers();
                    $('#addUserForm')[0].reset();
                    $('#studentFields').hide();
                    $('#driverFields').hide();
                } else {
                    alert('Add failed');
                }
            });
        });
        $(document).on('click', '.deleteUser', function() {
            var userId = $(this).data('id');
            if (confirm('Delete user?')) {
                ajaxRequest('../ajax/user_actions.php', {action: 'delete_user', user_id: userId}, function(response) {
                    response = JSON.parse(response);
                    if (response.success) loadUsers();
                });
            }
        });
        $(document).on('click', '.editUser', function() {
            var userId = $(this).data('id');
            var firstName = $(this).data('first_name');
            var lastName = $(this).data('last_name');
            var studentId = $(this).data('student_id');
            var email = $(this).data('email');
            var phone = $(this).data('phone');
            var gender = $(this).data('gender');
            var role = $(this).data('role');
            var drivingLicense = $(this).data('driving_license');
            var nid = $(this).data('nid');
            var yearsOfExperience = $(this).data('years_of_experience');
            var editForm = `
                <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editModalLabel">Edit User</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="editUserForm">
                                    <input type="hidden" name="user_id" value="${userId}">
                                    <div class="form-group">
                                        <label>First Name</label>
                                        <input type="text" name="first_name" value="${firstName}" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Last Name</label>
                                        <input type="text" name="last_name" value="${lastName}" class="form-control" required>
                                    </div>
                                    <div id="editStudentFields" style="${role === 'student' ? '' : 'display:none;'}">
                                        <div class="form-group">
                                            <label>Student ID</label>
                                            <input type="text" name="student_id" value="${studentId}" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label>Gender</label>
                                            <select name="gender" class="form-control">
                                                <option value="male" ${gender == 'male' ? 'selected' : ''}>Male</option>
                                                <option value="female" ${gender == 'female' ? 'selected' : ''}>Female</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div id="editDriverFields" style="${role === 'driver' ? '' : 'display:none;'}">
                                        <div class="form-group">
                                            <label>Driving License</label>
                                            <input type="text" name="driving_license" value="${drivingLicense}" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label>NID</label>
                                            <input type="text" name="nid" value="${nid}" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label>Years of Experience</label>
                                            <input type="number" name="years_of_experience" value="${yearsOfExperience}" class="form-control">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" value="${email}" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Phone</label>
                                        <input type="text" name="phone" value="${phone}" class="form-control" required>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" form="editUserForm" class="btn btn-primary">Save Changes</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('body').append(editForm);
            $('#editModal').on('hidden.bs.modal', function () {
                $(this).remove();
            });
            $('#editModal').modal('show');
        });
        $(document).on('submit', '#editUserForm', function(e) {
            e.preventDefault();
            var data = $(this).serialize() + '&action=edit_user';
            ajaxRequest('../ajax/user_actions.php', data, function(response) {
                response = JSON.parse(response);
                if (response.success) {
                    loadUsers();
                    $('#editModal').modal('hide');
                } else {
                    alert('Edit failed');
                }
            });
        });
        $(document).on('click', '.changePassword', function() {
            var userId = $(this).data('id');
            var changeForm = `
                <div class="modal fade" id="changeModal" tabindex="-1" aria-labelledby="changeModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="changeModalLabel">Change Password</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="changePasswordForm">
                                    <input type="hidden" name="user_id" value="${userId}">
                                    <div class="form-group">
                                        <label>New Password</label>
                                        <input type="password" name="new_password" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Confirm New Password</label>
                                        <input type="password" name="confirm_new_password" class="form-control" required>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" form="changePasswordForm" class="btn btn-primary">Change Password</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('body').append(changeForm);
            $('#changeModal').on('hidden.bs.modal', function () {
                $(this).remove();
            });
            $('#changeModal').modal('show');
        });
        $(document).on('submit', '#changePasswordForm', function(e) {
            e.preventDefault();
            var data = $(this).serialize() + '&action=admin_change_password';
            ajaxRequest('../ajax/user_actions.php', data, function(response) {
                response = JSON.parse(response);
                if (response.success) {
                    alert('Password changed');
                    $('#changeModal').modal('hide');
                } else {
                    alert(response.message || 'Change failed');
                }
            });
        });
    </script>
</body>
</html>
```

### ajax/destination_actions.php
```php
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
```

### admin/manage_destinations.php
```php
<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') header("Location: ../login.php");
include '../db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Destinations</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .btn-close {
            box-sizing: content-box;
            width: 1em;
            height: 1em;
            padding: .25em .25em;
            color: #000;
            background: transparent url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23000'%3e%3cpath d='M.293.293a1 1 0 011.414 0L8 6.586 14.293.293a1 1 0 111.414 1.414L9.414 8l6.293 6.293a1 1 0 01-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 01-1.414-1.414L6.586 8 .293 1.707a1 1 0 010-1.414z'/%3e%3c/svg%3e") center/1em auto no-repeat;
            border: 0;
            border-radius: .25rem;
            opacity: .5;
        }
        .btn-close:hover {
            color: #000;
            text-decoration: none;
            opacity: .75;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Manage Destinations</h2>
        <form id="addDestForm">
            <div class="form-group">
                <label>Starting Destination</label>
                <input type="text" name="start_destination" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Start Map Coordinates (e.g., 23.8151,90.4251)</label>
                <div class="input-group">
                    <input type="text" id="start_map_coords" name="start_map_coords" class="form-control" required>
                    <span class="input-group-text" id="start_locateMe"><i class="bi bi-crosshair"></i></span>
                </div>
            </div>
            <div id="start_map" style="height: 300px;"></div>
            <div class="form-group">
                <label>End Destination</label>
                <input type="text" name="end_destination" class="form-control" required>
            </div>
            <div class="form-group">
                <label>End Map Coordinates (e.g., 23.8151,90.4251)</label>
                <div class="input-group">
                    <input type="text" id="end_map_coords" name="end_map_coords" class="form-control" required>
                    <span class="input-group-text" id="end_locateMe"><i class="bi bi-crosshair"></i></span>
                </div>
            </div>
            <div id="end_map" style="height: 300px;"></div>
            <div class="form-group">
                <label>Distance (km)</label>
                <input type="number" step="0.01" name="distance" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Fare</label>
                <input type="number" step="0.01" name="fare" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Destination</button>
        </form>
        <div id="destList"></div>
        <hr>
        <h3>Set Time for Destination</h3>
        <form id="setTimeForm">
            <div class="form-group">
                <label>Destination</label>
                <select name="destination_id" id="destTimeSelect" class="form-control" required>
                    <option value="">Select</option>
                </select>
            </div>
            <div class="form-group">
                <label>Time (HH:MM)</label>
                <input type="time" name="time" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Set Time</button>
        </form>
        <div id="timeList"></div>
        <a href="dashboard.php">Back</a>
    </div>
    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/custom.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    <script>
        // --- FIX START: Manual handler for closing dynamic modals ---
        $(document).on('click', '.btn-close, [data-bs-dismiss="modal"]', function() {
            $(this).closest('.modal').modal('hide');
        });
        // --- FIX END ---
        // Helper function to format coordinates to 4 decimal places
        function formatCoords(coordsStr) {
            if (!coordsStr) return '';
            var parts = coordsStr.split(',');
            if (parts.length === 2) {
                var lat = parseFloat(parts[0]);
                var lng = parseFloat(parts[1]);
                if (!isNaN(lat) && !isNaN(lng)) {
                    return lat.toFixed(4) + ',' + lng.toFixed(4);
                }
            }
            return coordsStr;
        }
        function loadDests() {
            ajaxRequest('../ajax/destination_actions.php', {action: 'get_destinations'}, function(response) {
                response = JSON.parse(response);
                var html = '<table id="destTable" class="table"><thead><tr><th>ID</th><th>Name</th><th>Start Dest</th><th>End Dest</th><th>Distance</th><th>Fare</th><th>Start Coords</th><th>End Coords</th><th>Actions</th></tr></thead><tbody>';
                response.forEach(function(dest) {
                    // Display formatted coordinates in the table, but keep original precision in data attributes
                    html += '<tr><td>' + dest.id + '</td><td>' + dest.name + '</td><td>' + dest.start_destination + '</td><td>' + dest.end_destination + '</td><td>' + dest.distance + '</td><td>' + dest.fare + '</td><td>' + formatCoords(dest.start_map_coords) + '</td><td>' + formatCoords(dest.end_map_coords) + '</td><td><button class="btn btn-info editDest" data-id="' + dest.id + '" data-start_destination="' + dest.start_destination + '" data-end_destination="' + dest.end_destination + '" data-distance="' + dest.distance + '" data-fare="' + dest.fare + '" data-start_map_coords="' + dest.start_map_coords + '" data-end_map_coords="' + dest.end_map_coords + '">Edit</button> <button class="btn btn-danger deleteDest" data-id="' + dest.id + '">Delete</button></td></tr>';
                });
                html += '</tbody></table>';
                if ($.fn.DataTable.isDataTable('#destTable')) {
                    $('#destTable').DataTable().destroy();
                }
                $('#destList').html(html);
                $('#destTable').DataTable();
                // Populate select for set time
                $('#destTimeSelect').html('<option value="">Select</option>');
                response.forEach(function(dest) {
                    $('#destTimeSelect').append('<option value="' + dest.id + '">' + dest.name + '</option>');
                });
            });
        }
        function loadTimes() {
            ajaxRequest('../ajax/destination_actions.php', {action: 'get_all_times'}, function(response) {
                response = JSON.parse(response);
                var html = '<table id="timesTable" class="table"><thead><tr><th>ID</th><th>Destination</th><th>Time</th><th>Actions</th></tr></thead><tbody>';
                response.forEach(function(time) {
                    html += '<tr><td>' + time.id + '</td><td>' + time.destination_name + '</td><td>' + time.time + '</td><td><button class="btn btn-info editTime" data-id="' + time.id + '" data-destination_id="' + time.destination_id + '" data-time="' + time.time + '">Edit</button> <button class="btn btn-danger deleteTime" data-id="' + time.id + '">Delete</button></td></tr>';
                });
                html += '</tbody></table>';
                if ($.fn.DataTable.isDataTable('#timesTable')) {
                    $('#timesTable').DataTable().destroy();
                }
                $('#timeList').html(html);
                $('#timesTable').DataTable();
            });
        }
        loadDests();
        loadTimes();
        // Settings for restricting search to Dhaka, Bangladesh
        // Viewbox covering approximate Dhaka area (minLon, minLat, maxLon, maxLat)
        var dhakaParams = {
            countrycodes: 'bd',
            viewbox: '90.3200,23.9000,90.5200,23.6600', // Approx bounds
            bounded: 1
        };
        // Initialize start map
        var start_map = L.map('start_map').setView([23.8151, 90.4251], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            detectRetina: true
        }).addTo(start_map);
        
        var start_marker;
        
        // Add Geocoder Search for Start Map (Dhaka restricted)
        L.Control.geocoder({
            defaultMarkGeocode: false,
            geocoder: L.Control.Geocoder.nominatim({
                geocodingQueryParams: dhakaParams
            })
        })
        .on('markgeocode', function(e) {
            var bbox = e.geocode.bbox;
            var poly = L.polygon([
                bbox.getSouthEast(),
                bbox.getNorthEast(),
                bbox.getNorthWest(),
                bbox.getSouthWest()
            ]);
            start_map.fitBounds(poly.getBounds());
            
            var latlng = e.geocode.center;
            if (start_marker) {
                start_marker.setLatLng(latlng);
            } else {
                start_marker = L.marker(latlng).addTo(start_map);
            }
            $('#start_map_coords').val(latlng.lat.toFixed(4) + ',' + latlng.lng.toFixed(4));
        })
        .addTo(start_map);
        start_map.on('click', function(e) {
            if (start_marker) {
                start_marker.setLatLng(e.latlng);
            } else {
                start_marker = L.marker(e.latlng).addTo(start_map);
            }
            // Fix to 4 decimal points
            $('#start_map_coords').val(e.latlng.lat.toFixed(4) + ',' + e.latlng.lng.toFixed(4));
        });
        $('#start_locateMe').click(function() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    var latlng = [position.coords.latitude, position.coords.longitude];
                    start_map.setView(latlng, 13);
                    if (start_marker) {
                        start_marker.setLatLng(latlng);
                    } else {
                        start_marker = L.marker(latlng).addTo(start_map);
                    }
                    // Fix to 4 decimal points
                    $('#start_map_coords').val(position.coords.latitude.toFixed(4) + ',' + position.coords.longitude.toFixed(4));
                }, function(error) {
                    alert('Geolocation error: ' + error.message);
                });
            } else {
                alert('Geolocation is not supported by this browser.');
            }
        });
        // Initialize end map
        var end_map = L.map('end_map').setView([23.8151, 90.4251], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            detectRetina: true
        }).addTo(end_map);
        
        var end_marker;
        
        // Add Geocoder Search for End Map (Dhaka restricted)
        L.Control.geocoder({
            defaultMarkGeocode: false,
            geocoder: L.Control.Geocoder.nominatim({
                geocodingQueryParams: dhakaParams
            })
        })
        .on('markgeocode', function(e) {
            var bbox = e.geocode.bbox;
            var poly = L.polygon([
                bbox.getSouthEast(),
                bbox.getNorthEast(),
                bbox.getNorthWest(),
                bbox.getSouthWest()
            ]);
            end_map.fitBounds(poly.getBounds());
            
            var latlng = e.geocode.center;
            if (end_marker) {
                end_marker.setLatLng(latlng);
            } else {
                end_marker = L.marker(latlng).addTo(end_map);
            }
            $('#end_map_coords').val(latlng.lat.toFixed(4) + ',' + latlng.lng.toFixed(4));
        })
        .addTo(end_map);
        end_map.on('click', function(e) {
            if (end_marker) {
                end_marker.setLatLng(e.latlng);
            } else {
                end_marker = L.marker(e.latlng).addTo(end_map);
            }
            // Fix to 4 decimal points
            $('#end_map_coords').val(e.latlng.lat.toFixed(4) + ',' + e.latlng.lng.toFixed(4));
        });
        $('#end_locateMe').click(function() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    var latlng = [position.coords.latitude, position.coords.longitude];
                    end_map.setView(latlng, 13);
                    if (end_marker) {
                        end_marker.setLatLng(latlng);
                    } else {
                        end_marker = L.marker(latlng).addTo(end_map);
                    }
                    // Fix to 4 decimal points
                    $('#end_map_coords').val(position.coords.latitude.toFixed(4) + ',' + position.coords.longitude.toFixed(4));
                }, function(error) {
                    alert('Geolocation error: ' + error.message);
                });
            } else {
                alert('Geolocation is not supported by this browser.');
            }
        });
        $('#addDestForm').submit(function(e) {
            e.preventDefault();
            var data = $(this).serialize() + '&action=add_destination';
            ajaxRequest('../ajax/destination_actions.php', data, function(response) {
                response = JSON.parse(response);
                if (response.success) {
                    loadDests();
                    $('#addDestForm')[0].reset();
                } else {
                    alert(response.message || 'Add failed');
                }
            });
        });
        $('#setTimeForm').submit(function(e) {
            e.preventDefault();
            var data = $(this).serialize() + '&action=set_time';
            ajaxRequest('../ajax/destination_actions.php', data, function(response) {
                response = JSON.parse(response);
                if (response.success) {
                    loadTimes();
                    $('#setTimeForm')[0].reset();
                } else {
                    alert(response.message || 'Set failed');
                }
            });
        });
        $(document).on('click', '.deleteDest', function() {
            var destId = $(this).data('id');
            if (confirm('Delete destination?')) {
                ajaxRequest('../ajax/destination_actions.php', {action: 'delete_destination', id: destId}, function(response) {
                    response = JSON.parse(response);
                    if (response.success) {
                        loadDests();
                        loadTimes();
                    }
                });
            }
        });
        $(document).on('click', '.editDest', function() {
            var destId = $(this).data('id');
            var start_destination = $(this).data('start_destination');
            var end_destination = $(this).data('end_destination');
            var distance = $(this).data('distance');
            var fare = $(this).data('fare');
            var start_map_coords = $(this).data('start_map_coords');
            var end_map_coords = $(this).data('end_map_coords');
            // Format coordinates for the edit form INPUTS only (visual)
            var display_start_coords = formatCoords(start_map_coords);
            var display_end_coords = formatCoords(end_map_coords);
            var editForm = `
                <form id="editDestForm">
                    <input type="hidden" name="id" value="${destId}">
                    <div class="form-group">
                        <label>Starting Destination</label>
                        <input type="text" name="start_destination" value="${start_destination}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Start Map Coordinates</label>
                        <div class="input-group">
                            <input type="text" id="edit_start_map_coords" name="start_map_coords" value="${display_start_coords}" class="form-control" required>
                            <span class="input-group-text" id="edit_start_locateMe"><i class="bi bi-crosshair"></i></span>
                        </div>
                    </div>
                    <div id="edit_start_map" style="height: 300px; width: 100%;"></div>
                    <div class="form-group">
                        <label>End Destination</label>
                        <input type="text" name="end_destination" value="${end_destination}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>End Map Coordinates</label>
                        <div class="input-group">
                            <input type="text" id="edit_end_map_coords" name="end_map_coords" value="${display_end_coords}" class="form-control" required>
                            <span class="input-group-text" id="edit_end_locateMe"><i class="bi bi-crosshair"></i></span>
                        </div>
                    </div>
                    <div id="edit_end_map" style="height: 300px; width: 100%;"></div>
                    <div class="form-group">
                        <label>Distance</label>
                        <input type="number" step="0.01" name="distance" value="${distance}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Fare</label>
                        <input type="number" step="0.01" name="fare" value="${fare}" class="form-control" required>
                    </div>
                </form>
            `;
            var modalHtml = `
                <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editModalLabel">Edit Destination</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                ${editForm}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" form="editDestForm" class="btn btn-primary">Save Changes</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('body').append(modalHtml);
            $('#editModal').on('hidden.bs.modal', function () {
                $(this).remove();
            });
            $('#editModal').on('shown.bs.modal', function () {
                // Initialize edit start map
                var edit_start_map = L.map('edit_start_map').setView([23.8151, 90.4251], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                    detectRetina: true
                }).addTo(edit_start_map);
                var edit_start_marker;
                if (start_map_coords) {
                    // Use original precision data attribute for map marker placement
                    var coords = start_map_coords.split(',');
                    if (coords.length === 2) {
                        var lat = parseFloat(coords[0].trim());
                        var lng = parseFloat(coords[1].trim());
                        if (!isNaN(lat) && !isNaN(lng)) {
                            var latlng = [lat, lng];
                            edit_start_map.setView(latlng, 13);
                            edit_start_marker = L.marker(latlng).addTo(edit_start_map);
                        }
                    }
                }
                // Add Geocoder for Edit Start Map (Dhaka restricted)
                L.Control.geocoder({
                    defaultMarkGeocode: false,
                    geocoder: L.Control.Geocoder.nominatim({
                        geocodingQueryParams: dhakaParams
                    })
                })
                .on('markgeocode', function(e) {
                    var bbox = e.geocode.bbox;
                    var poly = L.polygon([
                        bbox.getSouthEast(),
                        bbox.getNorthEast(),
                        bbox.getNorthWest(),
                        bbox.getSouthWest()
                    ]);
                    edit_start_map.fitBounds(poly.getBounds());
                    
                    var latlng = e.geocode.center;
                    if (edit_start_marker) {
                        edit_start_marker.setLatLng(latlng);
                    } else {
                        edit_start_marker = L.marker(latlng).addTo(edit_start_map);
                    }
                    $('#edit_start_map_coords').val(latlng.lat.toFixed(4) + ',' + latlng.lng.toFixed(4));
                })
                .addTo(edit_start_map);
                edit_start_map.on('click', function(e) {
                    if (edit_start_marker) {
                        edit_start_marker.setLatLng(e.latlng);
                    } else {
                        edit_start_marker = L.marker(e.latlng).addTo(edit_start_map);
                    }
                    // Fix to 4 decimal points
                    $('#edit_start_map_coords').val(e.latlng.lat.toFixed(4) + ',' + e.latlng.lng.toFixed(4));
                });
                $('#edit_start_locateMe').click(function() {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(function(position) {
                            var latlng = [position.coords.latitude, position.coords.longitude];
                            edit_start_map.setView(latlng, 13);
                            if (edit_start_marker) {
                                edit_start_marker.setLatLng(latlng);
                            } else {
                                edit_start_marker = L.marker(latlng).addTo(edit_start_map);
                            }
                            // Fix to 4 decimal points
                            $('#edit_start_map_coords').val(position.coords.latitude.toFixed(4) + ',' + position.coords.longitude.toFixed(4));
                        }, function(error) {
                            alert('Geolocation error: ' + error.message);
                        });
                    } else {
                        alert('Geolocation is not supported by this browser.');
                    }
                });
                edit_start_map.invalidateSize();
                
                // Initialize edit end map
                var edit_end_map = L.map('edit_end_map').setView([23.8151, 90.4251], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                    detectRetina: true
                }).addTo(edit_end_map);
                var edit_end_marker;
                if (end_map_coords) {
                    // Use original precision data attribute for map marker placement
                    var coords = end_map_coords.split(',');
                    if (coords.length === 2) {
                        var lat = parseFloat(coords[0].trim());
                        var lng = parseFloat(coords[1].trim());
                        if (!isNaN(lat) && !isNaN(lng)) {
                            var latlng = [lat, lng];
                            edit_end_map.setView(latlng, 13);
                            edit_end_marker = L.marker(latlng).addTo(edit_end_map);
                        }
                    }
                }
                // Add Geocoder for Edit End Map (Dhaka restricted)
                L.Control.geocoder({
                    defaultMarkGeocode: false,
                    geocoder: L.Control.Geocoder.nominatim({
                        geocodingQueryParams: dhakaParams
                    })
                })
                .on('markgeocode', function(e) {
                    var bbox = e.geocode.bbox;
                    var poly = L.polygon([
                        bbox.getSouthEast(),
                        bbox.getNorthEast(),
                        bbox.getNorthWest(),
                        bbox.getSouthWest()
                    ]);
                    edit_end_map.fitBounds(poly.getBounds());
                    
                    var latlng = e.geocode.center;
                    if (edit_end_marker) {
                        edit_end_marker.setLatLng(latlng);
                    } else {
                        edit_end_marker = L.marker(latlng).addTo(edit_end_map);
                    }
                    $('#edit_end_map_coords').val(latlng.lat.toFixed(4) + ',' + latlng.lng.toFixed(4));
                })
                .addTo(edit_end_map);
                edit_end_map.on('click', function(e) {
                    if (edit_end_marker) {
                        edit_end_marker.setLatLng(e.latlng);
                    } else {
                        edit_end_marker = L.marker(e.latlng).addTo(edit_end_map);
                    }
                    // Fix to 4 decimal points
                    $('#edit_end_map_coords').val(e.latlng.lat.toFixed(4) + ',' + e.latlng.lng.toFixed(4));
                });
                $('#edit_end_locateMe').click(function() {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(function(position) {
                            var latlng = [position.coords.latitude, position.coords.longitude];
                            edit_end_map.setView(latlng, 13);
                            if (edit_end_marker) {
                                edit_end_marker.setLatLng(latlng);
                            } else {
                                edit_end_marker = L.marker(latlng).addTo(edit_end_map);
                            }
                            // Fix to 4 decimal points
                            $('#edit_end_map_coords').val(position.coords.latitude.toFixed(4) + ',' + position.coords.longitude.toFixed(4));
                        }, function(error) {
                            alert('Geolocation error: ' + error.message);
                        });
                    } else {
                        alert('Geolocation is not supported by this browser.');
                    }
                });
                edit_end_map.invalidateSize();
            });
            $('#editModal').modal('show');
        });
        $(document).on('submit', '#editDestForm', function(e) {
            e.preventDefault();
            var data = $(this).serialize() + '&action=edit_destination';
            ajaxRequest('../ajax/destination_actions.php', data, function(response) {
                response = JSON.parse(response);
                if (response.success) {
                    loadDests();
                    $('#editModal').modal('hide');
                } else {
                    alert(response.message || 'Edit failed');
                }
            });
        });
        $(document).on('click', '.deleteTime', function() {
            var timeId = $(this).data('id');
            if (confirm('Delete time?')) {
                ajaxRequest('../ajax/destination_actions.php', {action: 'delete_time', id: timeId}, function(response) {
                    response = JSON.parse(response);
                    if (response.success) loadTimes();
                });
            }
        });
        $(document).on('click', '.editTime', function() {
            var timeId = $(this).data('id');
            var destId = $(this).data('destination_id');
            var timeVal = $(this).data('time');
            var editForm = `
                <form id="editTimeForm">
                    <input type="hidden" name="id" value="${timeId}">
                    <div class="form-group">
                        <label>Destination</label>
                        <select name="destination_id" id="editTimeDestSelect" class="form-control" required></select>
                    </div>
                    <div class="form-group">
                        <label>Time</label>
                        <input type="time" name="time" value="${timeVal}" class="form-control" required>
                    </div>
                </form>
            `;
            var modalHtml = `
                <div class="modal fade" id="editTimeModal" tabindex="-1" aria-labelledby="editTimeModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editTimeModalLabel">Edit Time</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                ${editForm}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" form="editTimeForm" class="btn btn-primary">Save Changes</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('body').append(modalHtml);
            $('#editTimeModal').on('hidden.bs.modal', function () {
                $(this).remove();
            });
            $('#editTimeModal').modal('show');
            // Populate dest select
            ajaxRequest('../ajax/destination_actions.php', {action: 'get_destinations'}, function(response) {
                response = JSON.parse(response);
                $('#editTimeDestSelect').html('<option value="">Select</option>');
                response.forEach(function(dest) {
                    var selected = dest.id == destId ? 'selected' : '';
                    $('#editTimeDestSelect').append('<option value="' + dest.id + '" ' + selected + '>' + dest.name + '</option>');
                });
            });
        });
        $(document).on('submit', '#editTimeForm', function(e) {
            e.preventDefault();
            var data = $(this).serialize() + '&action=edit_time';
            ajaxRequest('../ajax/destination_actions.php', data, function(response) {
                response = JSON.parse(response);
                if (response.success) {
                    loadTimes();
                    $('#editTimeModal').modal('hide');
                } else {
                    alert('Edit failed');
                }
            });
        });
    </script>
</body>
</html>
```

### ajax/bus_actions.php
```php
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
    echo json_encode(['success' => $stmt->execute()]);
} elseif ($action == 'edit_bus') {
    $is_female_only = intval($_POST['is_female_only'] ?? 0);
    $stmt = $conn->prepare("UPDATE buses SET reg_number = ?, seats = ?, is_female_only = ? WHERE id = ?");
    $stmt->bind_param("siii", $_POST['reg_number'], $_POST['seats'], $is_female_only, $_POST['id']);
    echo json_encode(['success' => $stmt->execute()]);
} elseif ($action == 'delete_bus') {
    $stmt = $conn->prepare("DELETE FROM buses WHERE id = ?");
    $stmt->bind_param("i", $_POST['id']);
    echo json_encode(['success' => $stmt->execute()]);
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
    echo json_encode(['success' => $stmt->execute()]);
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
    echo json_encode(['success' => $stmt->execute()]);
} elseif ($action == 'delete_assignment') {
    $stmt = $conn->prepare("DELETE FROM bus_assignments WHERE id = ?");
    $stmt->bind_param("i", $_POST['id']);
    echo json_encode(['success' => $stmt->execute()]);
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
```

### admin/manage_buses.php
```php
<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') header("Location: ../login.php");
include '../db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Buses</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <style>
        .btn-close {
            box-sizing: content-box;
            width: 1em;
            height: 1em;
            padding: .25em .25em;
            color: #000;
            background: transparent url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23000'%3e%3cpath d='M.293.293a1 1 0 011.414 0L8 6.586 14.293.293a1 1 0 111.414 1.414L9.414 8l6.293 6.293a1 1 0 01-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 01-1.414-1.414L6.586 8 .293 1.707a1 1 0 010-1.414z'/%3e%3c/svg%3e") center/1em auto no-repeat;
            border: 0;
            border-radius: .25rem;
            opacity: .5;
        }
        .btn-close:hover {
            color: #000;
            text-decoration: none;
            opacity: .75;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Manage Buses</h2>
        <form id="addBusForm">
            <div class="form-group">
                <label>Registration Number</label>
                <input type="text" name="reg_number" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Seats</label>
                <input type="number" name="seats" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Female Only</label>
                <select name="is_female_only" class="form-control">
                    <option value="0">No</option>
                    <option value="1">Yes</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Add Bus</button>
        </form>
        <div id="busList"></div>
        <hr>
        <h3>Assign Bus to Destination and Time</h3>
        <form id="assignBusForm">
            <div class="form-group">
                <label>Bus</label>
                <select name="bus_id" id="busSelect" class="form-control" required>
                    <option value="">Select</option>
                </select>
            </div>
            <div class="form-group">
                <label>Destination</label>
                <select name="destination_id" id="destAssignSelect" class="form-control" required>
                    <option value="">Select</option>
                </select>
            </div>
            <div class="form-group">
                <label>Time</label>
                <select name="time_id" id="timeAssignSelect" class="form-control" required>
                    <option value="">Select</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Assign</button>
        </form>
        <div id="assignmentList"></div>
        <a href="dashboard.php">Back</a>
    </div>
    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/custom.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script>
        // --- FIX START: Manual handler for closing dynamic modals ---
        $(document).on('click', '.btn-close, [data-bs-dismiss="modal"]', function() {
            $(this).closest('.modal').modal('hide');
        });
        // --- FIX END ---
        function loadBuses() {
            ajaxRequest('../ajax/bus_actions.php', {action: 'get_buses'}, function(response) {
                response = JSON.parse(response);
                var html = '<table id="busesTable" class="table"><thead><tr><th>ID</th><th>Reg Number</th><th>Seats</th><th>Female Only</th><th>Actions</th></tr></thead><tbody>';
                response.forEach(function(bus) {
                    html += '<tr><td>' + bus.id + '</td><td>' + bus.reg_number + '</td><td>' + bus.seats + '</td><td>' + (Number(bus.is_female_only) ? 'Yes' : 'No') + '</td><td><button class="btn btn-info editBus" data-id="' + bus.id + '" data-reg_number="' + bus.reg_number + '" data-seats="' + bus.seats + '" data-is_female_only="' + bus.is_female_only + '">Edit</button> <button class="btn btn-danger deleteBus" data-id="' + bus.id + '">Delete</button></td></tr>';
                });
                html += '</tbody></table>';
                if ($.fn.DataTable.isDataTable('#busesTable')) {
                    $('#busesTable').DataTable().destroy();
                }
                $('#busList').html(html);
                $('#busesTable').DataTable();
                // Populate bus select
                $('#busSelect').html('<option value="">Select</option>');
                response.forEach(function(bus) {
                    $('#busSelect').append('<option value="' + bus.id + '">' + bus.reg_number + '</option>');
                });
            });
        }
        function loadAssignments() {
            ajaxRequest('../ajax/bus_actions.php', {action: 'get_assignments'}, function(response) {
                response = JSON.parse(response);
                var html = '<table id="assignmentsTable" class="table"><thead><tr><th>ID</th><th>Bus Reg</th><th>Destination</th><th>Time</th><th>Actions</th></tr></thead><tbody>';
                response.forEach(function(assign) {
                    html += '<tr><td>' + assign.id + '</td><td>' + assign.reg_number + '</td><td>' + assign.destination_name + '</td><td>' + assign.time + '</td><td><button class="btn btn-info editAssign" data-id="' + assign.id + '" data-bus_id="' + assign.bus_id + '" data-destination_id="' + assign.destination_id + '" data-time_id="' + assign.time_id + '">Edit</button> <button class="btn btn-danger deleteAssign" data-id="' + assign.id + '">Delete</button></td></tr>';
                });
                html += '</tbody></table>';
                if ($.fn.DataTable.isDataTable('#assignmentsTable')) {
                    $('#assignmentsTable').DataTable().destroy();
                }
                $('#assignmentList').html(html);
                $('#assignmentsTable').DataTable();
            });
        }
        loadBuses();
        loadAssignments();
        // Load destinations for assign
        ajaxRequest('../ajax/destination_actions.php', {action: 'get_destinations'}, function(response) {
            response = JSON.parse(response);
            $('#destAssignSelect').html('<option value="">Select</option>');
            response.forEach(function(dest) {
                $('#destAssignSelect').append('<option value="' + dest.id + '">' + dest.name + '</option>');
            });
        });
        $('#destAssignSelect').change(function() {
            var destId = $(this).val();
            if (destId) {
                ajaxRequest('../ajax/destination_actions.php', {action: 'get_times', destination_id: destId}, function(response) {
                    response = JSON.parse(response);
                    $('#timeAssignSelect').html('<option value="">Select</option>');
                    response.forEach(function(time) {
                        $('#timeAssignSelect').append('<option value="' + time.id + '">' + time.time + '</option>');
                    });
                });
            }
        });
        $('#addBusForm').submit(function(e) {
            e.preventDefault();
            var data = $(this).serialize() + '&action=add_bus';
            ajaxRequest('../ajax/bus_actions.php', data, function(response) {
                response = JSON.parse(response);
                if (response.success) {
                    loadBuses();
                    $('#addBusForm')[0].reset();
                } else {
                    alert(response.message || 'Add failed');
                }
            });
        });
        $('#assignBusForm').submit(function(e) {
            e.preventDefault();
            var data = $(this).serialize() + '&action=assign_bus';
            ajaxRequest('../ajax/bus_actions.php', data, function(response) {
                response = JSON.parse(response);
                if (response.success) {
                    loadAssignments();
                    $('#assignBusForm')[0].reset();
                } else {
                    alert(response.message || 'Assign failed');
                }
            });
        });
        $(document).on('click', '.deleteBus', function() {
            var busId = $(this).data('id');
            if (confirm('Delete bus?')) {
                ajaxRequest('../ajax/bus_actions.php', {action: 'delete_bus', id: busId}, function(response) {
                    response = JSON.parse(response);
                    if (response.success) loadBuses();
                });
            }
        });
        $(document).on('click', '.editBus', function() {
            var busId = $(this).data('id');
            var regNumber = $(this).data('reg_number');
            var seats = $(this).data('seats');
            var isFemaleOnly = $(this).data('is_female_only');
            var editForm = `
                <form id="editBusForm">
                    <input type="hidden" name="id" value="${busId}">
                    <div class="form-group">
                        <label>Reg Number</label>
                        <input type="text" name="reg_number" value="${regNumber}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Seats</label>
                        <input type="number" name="seats" value="${seats}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Female Only</label>
                        <select name="is_female_only" class="form-control">
                            <option value="0" ${isFemaleOnly == 0 ? 'selected' : ''}>No</option>
                            <option value="1" ${isFemaleOnly == 1 ? 'selected' : ''}>Yes</option>
                        </select>
                    </div>
                </form>
            `;
            var modalHtml = `
                <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editModalLabel">Edit Bus</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                ${editForm}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" form="editBusForm" class="btn btn-primary">Save Changes</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('body').append(modalHtml);
            $('#editModal').on('hidden.bs.modal', function () {
                $(this).remove();
            });
            $('#editModal').modal('show');
        });
        $(document).on('submit', '#editBusForm', function(e) {
            e.preventDefault();
            var data = $(this).serialize() + '&action=edit_bus';
            ajaxRequest('../ajax/bus_actions.php', data, function(response) {
                response = JSON.parse(response);
                if (response.success) {
                    loadBuses();
                    $('#editModal').modal('hide');
                } else {
                    alert('Edit failed');
                }
            });
        });
        $(document).on('click', '.deleteAssign', function() {
            var assignId = $(this).data('id');
            if (confirm('Delete assignment?')) {
                ajaxRequest('../ajax/bus_actions.php', {action: 'delete_assignment', id: assignId}, function(response) {
                    response = JSON.parse(response);
                    if (response.success) loadAssignments();
                });
            }
        });
        $(document).on('click', '.editAssign', function() {
            var assignId = $(this).data('id');
            var busId = $(this).data('bus_id');
            var destId = $(this).data('destination_id');
            var timeId = $(this).data('time_id');
            // To edit, we can reload selects and set values
            // But for simplicity, assume we fetch all and set selected
            var editForm = `
                <form id="editAssignForm">
                    <input type="hidden" name="id" value="${assignId}">
                    <div class="form-group">
                        <label>Bus</label>
                        <select name="bus_id" id="editBusSelect" class="form-control" required></select>
                    </div>
                    <div class="form-group">
                        <label>Destination</label>
                        <select name="destination_id" id="editDestSelect" class="form-control" required></select>
                    </div>
                    <div class="form-group">
                        <label>Time</label>
                        <select name="time_id" id="editTimeSelect" class="form-control" required></select>
                    </div>
                </form>
            `;
            var modalHtml = `
                <div class="modal fade" id="editAssignModal" tabindex="-1" aria-labelledby="editAssignModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editAssignModalLabel">Edit Assignment</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                ${editForm}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" form="editAssignForm" class="btn btn-primary">Save Changes</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('body').append(modalHtml);
            $('#editAssignModal').on('hidden.bs.modal', function () {
                $(this).remove();
            });
            $('#editAssignModal').modal('show');
            // Populate selects
            ajaxRequest('../ajax/bus_actions.php', {action: 'get_buses'}, function(response) {
                response = JSON.parse(response);
                $('#editBusSelect').html('<option value="">Select</option>');
                response.forEach(function(bus) {
                    var selected = bus.id == busId ? 'selected' : '';
                    $('#editBusSelect').append('<option value="' + bus.id + '" ' + selected + '>' + bus.reg_number + '</option>');
                });
            });
            ajaxRequest('../ajax/destination_actions.php', {action: 'get_destinations'}, function(response) {
                response = JSON.parse(response);
                $('#editDestSelect').html('<option value="">Select</option>');
                response.forEach(function(dest) {
                    var selected = dest.id == destId ? 'selected' : '';
                    $('#editDestSelect').append('<option value="' + dest.id + '" ' + selected + '>' + dest.name + '</option>');
                });
            });
            // For time, need to load based on dest
            if (destId) {
                ajaxRequest('../ajax/destination_actions.php', {action: 'get_times', destination_id: destId}, function(response) {
                    response = JSON.parse(response);
                    $('#editTimeSelect').html('<option value="">Select</option>');
                    response.forEach(function(time) {
                        var selected = time.id == timeId ? 'selected' : '';
                        $('#editTimeSelect').append('<option value="' + time.id + '" ' + selected + '>' + time.time + '</option>');
                    });
                });
            }
            // Handle dest change for time
            $('#editDestSelect').change(function() {
                var destId = $(this).val();
                if (destId) {
                    ajaxRequest('../ajax/destination_actions.php', {action: 'get_times', destination_id: destId}, function(response) {
                        response = JSON.parse(response);
                        $('#editTimeSelect').html('<option value="">Select</option>');
                        response.forEach(function(time) {
                            $('#editTimeSelect').append('<option value="' + time.id + '">' + time.time + '</option>');
                        });
                    });
                }
            });
        });
        $(document).on('submit', '#editAssignForm', function(e) {
            e.preventDefault();
            var data = $(this).serialize() + '&action=edit_assignment';
            ajaxRequest('../ajax/bus_actions.php', data, function(response) {
                response = JSON.parse(response);
                if (response.success) {
                    loadAssignments();
                    $('#editAssignModal').modal('hide');
                } else {
                    alert('Edit failed');
                }
            });
        });
    </script>
</body>
</html>
```

### admin/manage_payments.php
```php
<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') header("Location: ../login.php");
include '../db.php';
$error = ''; // For displaying error
if (isset($_POST['action']) && $_POST['action'] == 'add_payment') {
    $stmt = $conn->prepare("SELECT id FROM payment_options WHERE name = ?");
    $stmt->bind_param("s", $_POST['name']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $error = 'Payment option name already exists';
    } else {
        $stmt = $conn->prepare("INSERT INTO payment_options (name) VALUES (?)");
        $stmt->bind_param("s", $_POST['name']);
        $stmt->execute();
    }
}
if (isset($_POST['action']) && $_POST['action'] == 'delete_payment') {
    $stmt = $conn->prepare("DELETE FROM payment_options WHERE id = ?");
    $stmt->bind_param("i", $_POST['id']);
    $stmt->execute();
}
$payments = $conn->query("SELECT * FROM payment_options")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Payment Options</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
</head>
<body>
    <div class="container">
        <h2>Manage Payment Options</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post">
            <input type="hidden" name="action" value="add_payment">
            <div class="form-group">
                <label>New Payment Option Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Add</button>
        </form>
        <table id="paymentsTable" class="table">
            <thead>
                <tr><th>ID</th><th>Name</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $pay): ?>
                    <tr>
                        <td><?php echo $pay['id']; ?></td>
                        <td><?php echo $pay['name']; ?></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="action" value="delete_payment">
                                <input type="hidden" name="id" value="<?php echo $pay['id']; ?>">
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Delete?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="dashboard.php">Back</a>
    </div>
    <script src="../assets/js/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#paymentsTable').DataTable();
        });
    </script>
</body>
</html>
```

### admin/manage_rides.php
```php
<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') header("Location: ../login.php");
include '../db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Rides</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
</head>
<body>
    <div class="container">
        <h2>Manage Rides</h2>
        <div id="rideList"></div>
        <a href="dashboard.php">Back</a>
    </div>
    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/custom.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script>
        function loadRides() {
            ajaxRequest('../ajax/ride_actions.php', {action: 'get_rides'}, function(response) {
                response = JSON.parse(response);
                var html = '<table id="ridesTable" class="table"><thead><tr><th>ID</th><th>Trip Date</th><th>Driver</th><th>Bus Reg</th><th>Destination</th><th>Time</th><th>Status</th><th>Started At</th><th>Ended At</th><th>Actions</th></tr></thead><tbody>';
                response.forEach(function(ride) {
                    html += '<tr><td>' + ride.id + '</td><td>' + ride.trip_date + '</td><td>' + ride.driver_name + '</td><td>' + ride.bus_reg + '</td><td>' + ride.destination_name + '</td><td>' + ride.time + '</td><td>' + ride.status + '</td><td>' + (ride.started_at || '') + '</td><td>' + (ride.ended_at || '') + '</td><td><button class="btn btn-danger deleteRide" data-id="' + ride.id + '">Delete</button></td></tr>';
                });
                html += '</tbody></table>';
                if ($.fn.DataTable.isDataTable('#ridesTable')) {
                    $('#ridesTable').DataTable().destroy();
                }
                $('#rideList').html(html);
                $('#ridesTable').DataTable();
            });
        }
        loadRides();
        $(document).on('click', '.deleteRide', function() {
            var rideId = $(this).data('id');
            if (confirm('Delete ride?')) {
                ajaxRequest('../ajax/ride_actions.php', {action: 'delete_ride', id: rideId}, function(response) {
                    response = JSON.parse(response);
                    if (response.success) loadRides();
                });
            }
        });
    </script>
</body>
</html>
```

This completes the project. To run: Set up XAMPP, create DB, place files, access via localhost. Test functionalities step by step. For production, add more security, error handling, and real payment integration.
