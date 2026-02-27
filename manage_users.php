<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') header("Location: ../login.php");
include '../db.php';

// Fetch user data for the Header (Added for Dashboard Styling)
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Dashboard</title>
    
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    
    <style>
        /* --- DASHBOARD STYLES START --- */
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3a0ca3;
            --accent-color: #4cc9f0;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #4CAF50;
            --warning-color: #ff9800;
            --danger-color: #f44336;
            --sidebar-width: 250px;
            --header-height: 70px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fb;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .admin-sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 0;
            box-shadow: 3px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 100;
            position: fixed;
            height: 100%;
        }
        
        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-header h3 {
            font-weight: 600;
            margin: 0;
            font-size: 1.4rem;
        }
        
        .sidebar-header p {
            opacity: 0.8;
            font-size: 0.9rem;
            margin: 5px 0 0;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .menu-item {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }
        
        .menu-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 4px solid var(--accent-color);
            color: white;
            text-decoration: none;
        }
        
        .menu-item.active {
            background-color: rgba(255, 255, 255, 0.15);
            border-left: 4px solid var(--accent-color);
        }
        
        .menu-item i {
            margin-right: 12px;
            font-size: 1.1rem;
            width: 25px;
            text-align: center;
        }
        
        /* Main Content Area */
        .admin-main {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 0;
        }
        
        .admin-header {
            height: var(--header-height);
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 25px;
            position: sticky;
            top: 0;
            z-index: 99;
        }
        
        .header-left h1 {
            font-size: 1.5rem;
            color: var(--dark-color);
            margin: 0;
            font-weight: 600;
        }
        
        .welcome-text {
            font-size: 0.95rem;
            color: #666;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 12px;
            font-size: 1.2rem;
        }
        
        .logout-btn {
            background-color: var(--danger-color);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 500;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        
        .logout-btn:hover {
            background-color: #d32f2f;
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(244, 67, 54, 0.3);
        }
        
        .logout-btn i {
            margin-right: 6px;
        }
        
        /* Dashboard Content */
        .dashboard-content {
            padding: 25px;
        }

        /* Content Cards */
        .content-card {
            background-color: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }
        
        .section-header {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            color: var(--primary-color);
        }

        .footer {
            text-align: center;
            padding: 20px;
            color: #777;
            font-size: 0.9rem;
            border-top: 1px solid #eee;
            margin-top: 30px;
        }
        
        /* Responsive adjustments */
        @media (max-width: 992px) {
            .admin-sidebar {
                width: 70px;
                overflow: hidden;
            }
            
            .admin-sidebar:hover {
                width: var(--sidebar-width);
            }
            
            .sidebar-header h3, .sidebar-header p, .menu-item span {
                display: none;
            }
            
            .admin-sidebar:hover .sidebar-header h3,
            .admin-sidebar:hover .sidebar-header p,
            .admin-sidebar:hover .menu-item span {
                display: block;
            }
            
            .admin-main {
                margin-left: 70px;
            }
            
            .admin-sidebar:hover .menu-item i {
                margin-right: 12px;
            }
        }
        
        @media (max-width: 768px) {
            .admin-sidebar {
                display: none;
            }
            
            .admin-main {
                margin-left: 0;
            }
            
            .admin-header {
                padding: 0 15px;
                flex-direction: column;
                height: auto;
                padding: 15px;
            }
            
            .header-left {
                margin-bottom: 15px;
                text-align: center;
            }
        }
        /* --- DASHBOARD STYLES END --- */

        /* Existing Modal Fix Styles */
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
    <div class="admin-container">
        <div class="admin-sidebar">
            <div class="sidebar-header">
                <h3>Admin Portal</h3>
                <p>University Transport</p>
            </div>
            
            <div class="sidebar-menu">
                <a href="dashboard.php" class="menu-item">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="map.php" class="menu-item">
                    <i class="fas fa-map"></i>
                    <span>Route Map</span>
                </a>
                <a href="track_buses.php" class="menu-item">
                    <i class="fas fa-satellite-dish"></i>
                    <span>Track Buses</span>
                </a>
                <a href="manage_users.php" class="menu-item active">
                    <i class="fas fa-users"></i>
                    <span>Manage Users</span>
                </a>
                <a href="manage_destinations.php" class="menu-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Manage Destinations</span>
                </a>
                <a href="manage_buses.php" class="menu-item">
                    <i class="fas fa-bus"></i>
                    <span>Manage Buses</span>
                </a>
                <a href="manage_payments.php" class="menu-item">
                    <i class="fas fa-credit-card"></i>
                    <span>Manage Payments</span>
                </a>
                <a href="manage_rides.php" class="menu-item">
                    <i class="fas fa-route"></i>
                    <span>Manage Rides</span>
                </a>
            </div>
        </div>
        
        <div class="admin-main">
            <div class="admin-header">
                <div class="header-left">
                    <h1>Manage Users</h1>
                    <div class="welcome-text">Administration Panel</div>
                </div>
                
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                    </div>
                    <a href="../logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
            
            <div class="dashboard-content">
                
                <div class="content-card">
                    <h2 class="section-header"><i class="fas fa-user-plus"></i> Add New User</h2>
                    <form id="addUserForm" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Role</label>
                            <select name="role" id="addRoleSelect" class="form-control" required>
                                <option value="">Select Role</option>
                                <option value="admin">Admin</option>
                                <option value="student">Student</option>
                                <option value="driver">Driver</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <div class="col-12">
                            <div id="studentFields" style="display:none;" class="p-3 bg-light rounded border">
                                <h5 class="mb-3 text-primary">Student Details</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Student ID</label>
                                        <input type="text" name="student_id" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Gender</label>
                                        <select name="gender" class="form-control">
                                            <option value="">Select</option>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="driverFields" style="display:none;" class="p-3 bg-light rounded border">
                                <h5 class="mb-3 text-primary">Driver Details</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Driving License</label>
                                        <input type="text" name="driving_license" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">NID</label>
                                        <input type="text" name="nid" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Years of Experience</label>
                                        <input type="number" name="years_of_experience" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Add User</button>
                        </div>
                    </form>
                </div>

                <div class="content-card">
                    <h3 class="section-header"><i class="fas fa-users-cog"></i> User List</h3>
                    <div id="userList"></div>
                </div>

                <div class="footer">
                    <p>University Transport System &copy; <?php echo date('Y'); ?> | Admin Dashboard</p>
                </div>

            </div>
        </div>
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
                
                // Added table classes for styling
                var html = '<h4 class="mt-4 mb-3 text-secondary">Admins</h4><div class="table-responsive"><table id="adminsTable" class="table table-hover table-bordered"><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Actions</th></tr></thead><tbody>';
                admins.forEach(function(user) {
                    html += buildUserRow(user, false, false);
                });
                html += '</tbody></table></div>';
                
                html += '<h4 class="mt-5 mb-3 text-secondary">Students</h4><div class="table-responsive"><table id="studentsTable" class="table table-hover table-bordered"><thead><tr><th>ID</th><th>Name</th><th>Student ID</th><th>Email</th><th>Phone</th><th>Gender</th><th>Role</th><th>Actions</th></tr></thead><tbody>';
                students.forEach(function(user) {
                    html += buildUserRow(user, true, false);
                });
                html += '</tbody></table></div>';
                
                html += '<h4 class="mt-5 mb-3 text-secondary">Drivers</h4><div class="table-responsive"><table id="driversTable" class="table table-hover table-bordered"><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Driving License</th><th>NID</th><th>Years of Experience</th><th>Role</th><th>Actions</th></tr></thead><tbody>';
                drivers.forEach(function(user) {
                    html += buildUserRow(user, false, true);
                });
                html += '</tbody></table></div>';
                
                if ($.fn.DataTable.isDataTable('#adminsTable')) {
                    $('#adminsTable').DataTable().destroy();
                }
                if ($.fn.DataTable.isDataTable('#studentsTable')) {
                    $('#studentsTable').DataTable().destroy();
                }
                if ($.fn.DataTable.isDataTable('#driversTable')) {
                    $('#driversTable').DataTable().destroy();
                }
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
            // Updated buttons with icons and smaller size
            row += '<td><span class="badge bg-secondary">' + user.role + '</span></td><td><button class="btn btn-sm btn-info editUser" data-id="' + user.id + '" data-first_name="' + user.first_name + '" data-last_name="' + user.last_name + '" data-student_id="' + (user.student_id || '') + '" data-email="' + user.email + '" data-phone="' + user.phone + '" data-gender="' + (user.gender || '') + '" data-role="' + user.role + '" data-driving_license="' + (user.driving_license || '') + '" data-nid="' + (user.nid || '') + '" data-years_of_experience="' + (user.years_of_experience || '') + '"><i class="fas fa-edit"></i> Edit</button> <button class="btn btn-sm btn-warning changePassword" data-id="' + user.id + '"><i class="fas fa-key"></i> Pwd</button> <button class="btn btn-sm btn-danger deleteUser" data-id="' + user.id + '"><i class="fas fa-trash"></i> Delete</button></td></tr>';
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
                                    <div class="form-group mb-3">
                                        <label class="form-label">First Name</label>
                                        <input type="text" name="first_name" value="${firstName}" class="form-control" required>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-label">Last Name</label>
                                        <input type="text" name="last_name" value="${lastName}" class="form-control" required>
                                    </div>
                                    <div id="editStudentFields" style="${role === 'student' ? '' : 'display:none;'}">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Student ID</label>
                                            <input type="text" name="student_id" value="${studentId}" class="form-control">
                                        </div>
                                        <div class="form-group mb-3">
                                            <label class="form-label">Gender</label>
                                            <select name="gender" class="form-control">
                                                <option value="male" ${gender == 'male' ? 'selected' : ''}>Male</option>
                                                <option value="female" ${gender == 'female' ? 'selected' : ''}>Female</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div id="editDriverFields" style="${role === 'driver' ? '' : 'display:none;'}">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Driving License</label>
                                            <input type="text" name="driving_license" value="${drivingLicense}" class="form-control">
                                        </div>
                                        <div class="form-group mb-3">
                                            <label class="form-label">NID</label>
                                            <input type="text" name="nid" value="${nid}" class="form-control">
                                        </div>
                                        <div class="form-group mb-3">
                                            <label class="form-label">Years of Experience</label>
                                            <input type="number" name="years_of_experience" value="${yearsOfExperience}" class="form-control">
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" value="${email}" class="form-control" required>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-label">Phone</label>
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
                                    <div class="form-group mb-3">
                                        <label class="form-label">New Password</label>
                                        <input type="password" name="new_password" class="form-control" required>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-label">Confirm New Password</label>
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