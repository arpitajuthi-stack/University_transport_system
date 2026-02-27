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
    <title>Manage Destinations - Admin Dashboard</title>
    
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
    
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
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

        /* Existing Styles Preserved */
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
                <a href="manage_destinations.php" class="menu-item active">
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
                    <h1>Manage Destinations</h1>
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
                    <h2 class="section-header"><i class="fas fa-plus-circle"></i> Add New Destination</h2>
                    
                    <form id="addDestForm" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Starting Destination</label>
                            <input type="text" name="start_destination" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">End Destination</label>
                            <input type="text" name="end_destination" class="form-control" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Start Coordinates (Lat, Lng)</label>
                            <div class="input-group">
                                <input type="text" id="start_map_coords" name="start_map_coords" class="form-control" required placeholder="e.g. 23.8151,90.4251">
                                <span class="input-group-text" id="start_locateMe" style="cursor: pointer;"><i class="bi bi-crosshair"></i></span>
                            </div>
                            <div id="start_map" style="height: 300px; margin-top: 10px; border-radius: 5px; border: 1px solid #ddd;"></div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">End Coordinates (Lat, Lng)</label>
                            <div class="input-group">
                                <input type="text" id="end_map_coords" name="end_map_coords" class="form-control" required placeholder="e.g. 23.8151,90.4251">
                                <span class="input-group-text" id="end_locateMe" style="cursor: pointer;"><i class="bi bi-crosshair"></i></span>
                            </div>
                            <div id="end_map" style="height: 300px; margin-top: 10px; border-radius: 5px; border: 1px solid #ddd;"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Distance (km)</label>
                            <input type="number" step="0.01" name="distance" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fare</label>
                            <input type="number" step="0.01" name="fare" class="form-control" required>
                        </div>
                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Add Destination</button>
                        </div>
                    </form>
                    
                    <div class="mt-4">
                        <div id="destList"></div>
                    </div>
                </div>

                <div class="content-card">
                    <h3 class="section-header"><i class="fas fa-clock"></i> Set Time for Destination</h3>
                    <form id="setTimeForm" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Destination</label>
                            <select name="destination_id" id="destTimeSelect" class="form-control" required>
                                <option value="">Select</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Time (HH:MM)</label>
                            <input type="time" name="time" class="form-control" required>
                        </div>
                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-calendar-check"></i> Set Time</button>
                        </div>
                    </form>
                    
                    <div class="mt-4">
                        <div id="timeList"></div>
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
                var html = '<table id="destTable" class="table table-hover table-bordered"><thead><tr><th>ID</th><th>Name</th><th>Start Dest</th><th>End Dest</th><th>Distance</th><th>Fare</th><th>Start Coords</th><th>End Coords</th><th>Actions</th></tr></thead><tbody>';
                response.forEach(function(dest) {
                    // Display formatted coordinates in the table, but keep original precision in data attributes
                    html += '<tr><td>' + dest.id + '</td><td>' + dest.name + '</td><td>' + dest.start_destination + '</td><td>' + dest.end_destination + '</td><td>' + dest.distance + '</td><td>' + dest.fare + '</td><td>' + formatCoords(dest.start_map_coords) + '</td><td>' + formatCoords(dest.end_map_coords) + '</td><td><button class="btn btn-sm btn-info editDest" data-id="' + dest.id + '" data-start_destination="' + dest.start_destination + '" data-end_destination="' + dest.end_destination + '" data-distance="' + dest.distance + '" data-fare="' + dest.fare + '" data-start_map_coords="' + dest.start_map_coords + '" data-end_map_coords="' + dest.end_map_coords + '"><i class="fas fa-edit"></i> Edit</button> <button class="btn btn-sm btn-danger deleteDest" data-id="' + dest.id + '"><i class="fas fa-trash"></i> Delete</button></td></tr>';
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
                var html = '<table id="timesTable" class="table table-hover table-bordered"><thead><tr><th>ID</th><th>Destination</th><th>Time</th><th>Actions</th></tr></thead><tbody>';
                response.forEach(function(time) {
                    html += '<tr><td>' + time.id + '</td><td>' + time.destination_name + '</td><td>' + time.time + '</td><td><button class="btn btn-sm btn-info editTime" data-id="' + time.id + '" data-destination_id="' + time.destination_id + '" data-time="' + time.time + '"><i class="fas fa-edit"></i> Edit</button> <button class="btn btn-sm btn-danger deleteTime" data-id="' + time.id + '"><i class="fas fa-trash"></i> Delete</button></td></tr>';
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
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Starting Destination</label>
                            <input type="text" name="start_destination" value="${start_destination}" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Destination</label>
                            <input type="text" name="end_destination" value="${end_destination}" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Map Coordinates</label>
                            <div class="input-group">
                                <input type="text" id="edit_start_map_coords" name="start_map_coords" value="${display_start_coords}" class="form-control" required>
                                <span class="input-group-text" id="edit_start_locateMe" style="cursor: pointer;"><i class="bi bi-crosshair"></i></span>
                            </div>
                            <div id="edit_start_map" style="height: 300px; width: 100%; margin-top: 10px; border-radius: 4px; border: 1px solid #ddd;"></div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Map Coordinates</label>
                            <div class="input-group">
                                <input type="text" id="edit_end_map_coords" name="end_map_coords" value="${display_end_coords}" class="form-control" required>
                                <span class="input-group-text" id="edit_end_locateMe" style="cursor: pointer;"><i class="bi bi-crosshair"></i></span>
                            </div>
                            <div id="edit_end_map" style="height: 300px; width: 100%; margin-top: 10px; border-radius: 4px; border: 1px solid #ddd;"></div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Distance</label>
                            <input type="number" step="0.01" name="distance" value="${distance}" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fare</label>
                            <input type="number" step="0.01" name="fare" value="${fare}" class="form-control" required>
                        </div>
                    </div>
                </form>
            `;
            var modalHtml = `
                <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
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
                    <div class="form-group mb-3">
                        <label class="form-label">Destination</label>
                        <select name="destination_id" id="editTimeDestSelect" class="form-control" required></select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Time</label>
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