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
    <title>Manage Buses - Admin Dashboard</title>
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

        /* Content Cards (To style the forms/tables) */
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
                <a href="manage_users.php" class="menu-item">
                    <i class="fas fa-users"></i>
                    <span>Manage Users</span>
                </a>
                <a href="manage_destinations.php" class="menu-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Manage Destinations</span>
                </a>
                <a href="manage_buses.php" class="menu-item active">
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
                    <h1>Manage Buses</h1>
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
                    <h2 class="section-header"><i class="fas fa-plus-circle"></i> Register New Bus</h2>
                    <form id="addBusForm" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Registration Number</label>
                            <input type="text" name="reg_number" class="form-control" required placeholder="e.g. DHAKA-TA-1234">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Seats</label>
                            <input type="number" name="seats" class="form-control" required placeholder="e.g. 40">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Female Only</label>
                            <select name="is_female_only" class="form-control">
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                        </div>
                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Add Bus</button>
                        </div>
                    </form>
                    
                    <div class="mt-4">
                        <div id="busList"></div>
                    </div>
                </div>

                <div class="content-card">
                    <h3 class="section-header"><i class="fas fa-clock"></i> Assign Bus to Destination and Time</h3>
                    <form id="assignBusForm" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Bus</label>
                            <select name="bus_id" id="busSelect" class="form-control" required>
                                <option value="">Select</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Destination</label>
                            <select name="destination_id" id="destAssignSelect" class="form-control" required>
                                <option value="">Select</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Time</label>
                            <select name="time_id" id="timeAssignSelect" class="form-control" required>
                                <option value="">Select</option>
                            </select>
                        </div>
                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-link"></i> Assign</button>
                        </div>
                    </form>
                    
                    <div class="mt-4">
                        <div id="assignmentList"></div>
                    </div>
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
        function loadBuses() {
            ajaxRequest('../ajax/bus_actions.php', {action: 'get_buses'}, function(response) {
                response = JSON.parse(response);
                var html = '<table id="busesTable" class="table table-hover table-bordered"><thead><tr><th>ID</th><th>Reg Number</th><th>Seats</th><th>Female Only</th><th>Actions</th></tr></thead><tbody>';
                response.forEach(function(bus) {
                    html += '<tr><td>' + bus.id + '</td><td>' + bus.reg_number + '</td><td>' + bus.seats + '</td><td>' + (Number(bus.is_female_only) ? '<span class="badge bg-danger">Yes</span>' : '<span class="badge bg-success">No</span>') + '</td><td><button class="btn btn-sm btn-info editBus" data-id="' + bus.id + '" data-reg_number="' + bus.reg_number + '" data-seats="' + bus.seats + '" data-is_female_only="' + bus.is_female_only + '"><i class="fas fa-edit"></i> Edit</button> <button class="btn btn-sm btn-danger deleteBus" data-id="' + bus.id + '"><i class="fas fa-trash"></i> Delete</button></td></tr>';
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
                var html = '<table id="assignmentsTable" class="table table-hover table-bordered"><thead><tr><th>ID</th><th>Bus Reg</th><th>Destination</th><th>Time</th><th>Actions</th></tr></thead><tbody>';
                response.forEach(function(assign) {
                    html += '<tr><td>' + assign.id + '</td><td>' + assign.reg_number + '</td><td>' + assign.destination_name + '</td><td>' + assign.time + '</td><td><button class="btn btn-sm btn-info editAssign" data-id="' + assign.id + '" data-bus_id="' + assign.bus_id + '" data-destination_id="' + assign.destination_id + '" data-time_id="' + assign.time_id + '"><i class="fas fa-edit"></i> Edit</button> <button class="btn btn-sm btn-danger deleteAssign" data-id="' + assign.id + '"><i class="fas fa-trash"></i> Delete</button></td></tr>';
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
                    if (response.success) {
                        loadBuses();
                        loadAssignments(); // Reload assignments since bus deletion cascades
                    } else {
                        alert(response.message || 'Delete failed');
                    }
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
                    <div class="form-group mb-3">
                        <label class="form-label">Reg Number</label>
                        <input type="text" name="reg_number" value="${regNumber}" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Seats</label>
                        <input type="number" name="seats" value="${seats}" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Female Only</label>
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
                    alert(response.message || 'Edit failed');
                }
            });
        });
        $(document).on('click', '.deleteAssign', function() {
            var assignId = $(this).data('id');
            if (confirm('Delete assignment?')) {
                ajaxRequest('../ajax/bus_actions.php', {action: 'delete_assignment', id: assignId}, function(response) {
                    response = JSON.parse(response);
                    if (response.success) {
                        loadAssignments();
                    } else {
                        alert(response.message || 'Delete failed');
                    }
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
                    <div class="form-group mb-3">
                        <label class="form-label">Bus</label>
                        <select name="bus_id" id="editBusSelect" class="form-control" required></select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Destination</label>
                        <select name="destination_id" id="editDestSelect" class="form-control" required></select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Time</label>
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
    </script>
</body>
</html>