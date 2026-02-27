<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'driver') header("Location: ../login.php");
include '../db.php';

// Fetch user data for Header
$stmt_user = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt_user->bind_param("i", $_SESSION['user_id']);
$stmt_user->execute();
$user = $stmt_user->get_result()->fetch_assoc();

$destinations = $conn->query("SELECT * FROM destinations")->fetch_all(MYSQLI_ASSOC);

// Fetch current ride AND destination details (Coordinates)
$stmt = $conn->prepare("
    SELECT r.*, d.name as dest_name, d.start_map_coords, d.end_map_coords 
    FROM rides r 
    JOIN destinations d ON r.destination_id = d.id 
    WHERE r.driver_id = ? AND r.status IN ('pending', 'started')
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$current_ride = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Ride - Driver Portal</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <link rel="stylesheet" href="../assets/css/custom.css">
    
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
            cursor: pointer;
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

        /* Profile Button Style */
        .profile-btn-header {
            background-color: transparent;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 500;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            margin-right: 10px;
            cursor: pointer;
        }

        .profile-btn-header:hover {
            background-color: var(--primary-color);
            color: white;
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

        /* Profile Modal Styles */
        .profile-label {
            font-weight: 600;
            color: #666;
            margin-bottom: 5px;
        }
        .profile-value {
            font-size: 1.1rem;
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        /* Map Container */
        #map {
            height: 400px;
            width: 100%;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            margin-top: 20px;
            z-index: 1;
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
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-sidebar">
            <div class="sidebar-header">
                <h3>Driver Portal</h3>
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
                <a href="manage_ride.php" class="menu-item active">
                    <i class="fas fa-bus-alt"></i>
                    <span>Manage Ride</span>
                </a>
                <div class="menu-item" data-bs-toggle="modal" data-bs-target="#profileModal">
                    <i class="fas fa-user-circle"></i>
                    <span>View Profile</span>
                </div>
            </div>
        </div>
        
        <div class="admin-main">
            <div class="admin-header">
                <div class="header-left">
                    <h1>Manage Ride</h1>
                    <div class="welcome-text">Control your trip status</div>
                </div>
                
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                    </div>
                    <button class="profile-btn-header" data-bs-toggle="modal" data-bs-target="#profileModal">
                        <i class="fas fa-user me-2"></i> Profile
                    </button>
                    <a href="../logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
            
            <div class="dashboard-content">
                
                <div class="content-card">
                    <h2 class="section-header"><i class="fas fa-steering-wheel"></i> Ride Control Panel</h2>
                    
                    <?php if (!$current_ride): ?>
                    <form id="startRideForm" class="row g-3">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> No active ride detected. Start a new trip below.
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Trip Date</label>
                            <input type="date" name="trip_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Destination</label>
                            <select name="destination_id" id="destSelect" class="form-control" required>
                                <option value="">Select Destination</option>
                                <?php foreach ($destinations as $dest): ?>
                                    <option value="<?php echo $dest['id']; ?>"><?php echo $dest['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Time</label>
                            <select name="time_id" id="timeSelect" class="form-control" required>
                                <option value="">Select Time</option>
                            </select>
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-play"></i> Start Ride</button>
                        </div>
                    </form>
                    <?php else: ?>
                        <div class="alert alert-success border-success">
                            <h4 class="alert-heading"><i class="fas fa-bus"></i> Ride in Progress</h4>
                            <p>You are currently on an active trip to <strong><?php echo htmlspecialchars($current_ride['dest_name']); ?></strong>.</p>
                            <div id="locationStatus" class="small text-muted mb-2"><i class="fas fa-satellite-dish"></i> Initializing GPS...</div>
                            <hr>
                            <div class="row">
                                <div class="col-md-3"><strong>Date:</strong> <?php echo $current_ride['trip_date']; ?></div>
                                <div class="col-md-3"><strong>Destination:</strong> <?php echo htmlspecialchars($current_ride['dest_name']); ?></div>
                                <div class="col-md-3"><strong>Time ID:</strong> <?php echo $current_ride['time_id']; ?></div>
                                <div class="col-md-3"><strong>Status:</strong> <span class="badge bg-primary"><?php echo ucfirst($current_ride['status']); ?></span></div>
                            </div>
                            
                            <div id="map"></div>
                            
                        </div>
                        <div class="mt-4">
                            <button id="cancelRide" class="btn btn-warning me-2"><i class="fas fa-times-circle"></i> Cancel Ride</button>
                            <button id="endRide" class="btn btn-danger"><i class="fas fa-check-circle"></i> End Ride</button>
                        </div>
                    <?php endif; ?>
                    
                    <div id="rideAlert" class="mt-3"></div>
                </div>

                <div class="footer">
                    <p>University Transport System &copy; <?php echo date('Y'); ?> | Driver Portal</p>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="profileModalLabel"><i class="fas fa-id-card me-2"></i>My Driver Profile</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="user-avatar mx-auto" style="width: 80px; height: 80px; font-size: 2rem;">
                            <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                        </div>
                        <h4 class="mt-2"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h4>
                        <span class="badge bg-secondary">Role: <?php echo ucfirst($user['role']); ?></span>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="profile-label">Email Address</div>
                            <div class="profile-value"><?php echo htmlspecialchars($user['email']); ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="profile-label">Phone Number</div>
                            <div class="profile-value"><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="profile-label">Gender</div>
                            <div class="profile-value"><?php echo htmlspecialchars(ucfirst($user['gender'] ?? 'N/A')); ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="profile-label">Driving License</div>
                            <div class="profile-value"><?php echo htmlspecialchars($user['driving_license'] ?? 'N/A'); ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="profile-label">Experience</div>
                            <div class="profile-value"><?php echo htmlspecialchars($user['years_of_experience'] ?? '0'); ?> Years</div>
                        </div>
                        <div class="col-md-6">
                            <div class="profile-label">National ID (NID)</div>
                            <div class="profile-value"><?php echo htmlspecialchars($user['nid'] ?? 'N/A'); ?></div>
                        </div>
                    </div>

                    <div class="alert alert-warning mt-2 mb-0 d-flex align-items-center">
                        <i class="fas fa-exclamation-circle me-3 fa-2x"></i>
                        <div>
                            <strong>Need to update information?</strong><br>
                            Please contact the admin at <a href="mailto:admin@example.com" class="alert-link">admin@example.com</a>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
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

        // -----------------------------------------------------
        // FEATURE: LIVE LOCATION + MAP + COLORED MARKERS
        // -----------------------------------------------------
        <?php if ($current_ride): ?>
        
        // 1. Initialize Map
        // Default view (will be updated by coords)
        var map = L.map('map').setView([23.8103, 90.4125], 13); 
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        // 2. Define Colored Icons
        // Green for Start
        var greenIcon = new L.Icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
        });

        // Red for End
        var redIcon = new L.Icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
        });

        // Blue/Bus for Driver
        var blueIcon = new L.Icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
        });

        // 3. Plot Start and End Destinations from Database
        var startCoordsStr = "<?php echo $current_ride['start_map_coords']; ?>";
        var endCoordsStr = "<?php echo $current_ride['end_map_coords']; ?>";
        
        if(startCoordsStr && startCoordsStr.includes(',')) {
            var sc = startCoordsStr.split(',');
            L.marker([sc[0], sc[1]], {icon: greenIcon})
                .addTo(map)
                .bindPopup("<b>Start:</b> <?php echo htmlspecialchars($current_ride['dest_name']); ?> (Start Point)");
        }

        if(endCoordsStr && endCoordsStr.includes(',')) {
            var ec = endCoordsStr.split(',');
            L.marker([ec[0], ec[1]], {icon: redIcon})
                .addTo(map)
                .bindPopup("<b>End:</b> <?php echo htmlspecialchars($current_ride['dest_name']); ?> (End Point)");
        }

        // 4. Driver Live Location Logic
        var driverMarker = null;

        var locationInterval = setInterval(function() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    var lat = position.coords.latitude;
                    var lng = position.coords.longitude;
                    
                    // Update UI status
                    $('#locationStatus').html('<i class="fas fa-satellite-dish text-success"></i> Location updating live... (' + lat.toFixed(4) + ', ' + lng.toFixed(4) + ')');

                    // Map Logic for Driver
                    if (driverMarker) {
                        // Move existing marker
                        driverMarker.setLatLng([lat, lng]);
                    } else {
                        // Create new marker
                        driverMarker = L.marker([lat, lng], {icon: blueIcon}).addTo(map);
                        driverMarker.bindPopup("<b>You are here</b><br>Driver Location").openPopup();
                        // Center map on driver initially
                        map.setView([lat, lng], 14);
                    }

                    // Send coordinates to PHP for DB update
                    $.post('../ajax/ride_actions.php', {
                        action: 'update_location',
                        latitude: lat,
                        longitude: lng
                    }, function(data) {
                        // Optional: Log success
                    });
                }, function(error) {
                    console.error("Geolocation error: " + error.message);
                    $('#locationStatus').html('<i class="fas fa-exclamation-triangle text-warning"></i> Location access failed: ' + error.message);
                });
            } else {
                console.error("Geolocation is not supported by this browser.");
                $('#locationStatus').html('<i class="fas fa-times-circle text-danger"></i> Geolocation not supported.');
            }
        }, 5000); // 5000ms = 5 seconds
        <?php endif; ?>
    </script>
</body>
</html>