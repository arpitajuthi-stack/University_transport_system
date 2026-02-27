<?php
session_start();
// SECURITY: Check if user is logged in AND is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') header("Location: ../login.php");
include '../db.php';

// Fetch logged-in user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// --- MAP DATA LOGIC (Fetch Stops) ---
$unique_locations = [];

$query = "SELECT start_destination, start_map_coords, end_destination, end_map_coords FROM destinations";
$result = $conn->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Process Start Coordinate
        if (!empty($row['start_map_coords']) && strpos($row['start_map_coords'], ',') !== false) {
            $coords = trim($row['start_map_coords']);
            if (!isset($unique_locations[$coords])) {
                $unique_locations[$coords] = [
                    'name' => $row['start_destination'],
                    'coords' => $coords
                ];
            }
        }
        // Process End Coordinate
        if (!empty($row['end_map_coords']) && strpos($row['end_map_coords'], ',') !== false) {
            $coords = trim($row['end_map_coords']);
            if (!isset($unique_locations[$coords])) {
                $unique_locations[$coords] = [
                    'name' => $row['end_destination'],
                    'coords' => $coords
                ];
            }
        }
    }
}
// Re-index array for JSON output
$map_data = array_values($unique_locations);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Route Map - Student Portal</title>
    
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    
    <style>
        /* --- DASHBOARD STYLES (MATCHING STUDENT THEME) --- */
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

        /* --- MAP SPECIFIC STYLES --- */
        .map-wrapper {
            padding: 25px;
            height: calc(100vh - var(--header-height));
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
        }

        .map-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            flex: 1;
            display: flex;
            flex-direction: column;
            border-top: 4px solid var(--primary-color);
        }

        #map {
            flex: 1;
            width: 100%;
            border-radius: 8px;
            border: 1px solid #ddd;
            z-index: 1;
        }

        .map-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .legend {
            font-size: 0.9rem;
            background: #f8f9fa;
            padding: 8px 15px;
            border-radius: 20px;
            border: 1px solid #eee;
        }
        .legend span {
            display: inline-flex;
            align-items: center;
            margin-left: 15px;
        }
        .legend span:first-child { margin-left: 0; }
        
        .dot {
            height: 10px;
            width: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
        }
        .dot-blue { background-color: #2A81CB; }
        .dot-red { background-color: #FF0000; }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .admin-sidebar { width: 70px; overflow: hidden; }
            .admin-sidebar:hover { width: var(--sidebar-width); }
            .sidebar-header h3, .sidebar-header p, .menu-item span { display: none; }
            .admin-sidebar:hover .sidebar-header h3, .admin-sidebar:hover .sidebar-header p, .admin-sidebar:hover .menu-item span { display: block; }
            .admin-main { margin-left: 70px; }
        }
        
        @media (max-width: 768px) {
            .admin-sidebar { display: none; }
            .admin-main { margin-left: 0; }
            .admin-header { padding: 15px; flex-direction: column; height: auto; }
            .header-left { margin-bottom: 15px; text-align: center; }
            .map-controls { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-sidebar">
            <div class="sidebar-header">
                <h3>Student Portal</h3>
                <p>University Transport</p>
            </div>
            
            <div class="sidebar-menu">
                <a href="dashboard.php" class="menu-item">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="map.php" class="menu-item active">
                    <i class="fas fa-map-marked-alt"></i>
                    <span>Route Map</span>
                </a>
                <a href="track_buses.php" class="menu-item">
                    <i class="fas fa-satellite-dish"></i>
                    <span>Track Buses</span>
                </a>
                <a href="buy_ticket.php" class="menu-item">
                    <i class="fas fa-ticket-alt"></i>
                    <span>Buy Ticket</span>
                </a>
                <a href="my_tickets.php" class="menu-item">
                    <i class="fas fa-list-alt"></i>
                    <span>My Tickets</span>
                </a>
                <a href="edit_profile.php" class="menu-item">
                    <i class="fas fa-user-edit"></i>
                    <span>Edit Profile</span>
                </a>
                <a href="change_password.php" class="menu-item">
                    <i class="fas fa-key"></i>
                    <span>Change Password</span>
                </a>
            </div>
        </div>
        
        <div class="admin-main">
            <div class="admin-header">
                <div class="header-left">
                    <h1>Live Route Map</h1>
                    <div class="welcome-text">Find bus stops and track your location</div>
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
            
            <div class="map-wrapper">
                <div class="map-card">
                    <div class="map-controls">
                        <div>
                            <strong><i class="fas fa-info-circle text-primary"></i> Stops Available: </strong> <?php echo count($map_data); ?>
                        </div>
                        <div class="legend">
                            <span><span class="dot dot-blue"></span> Bus Stop</span>
                            <span><span class="dot dot-red"></span> Your Location</span>
                        </div>
                        <button class="btn btn-sm btn-primary" onclick="locateUser()">
                            <i class="fas fa-location-arrow"></i> Find Me
                        </button>
                    </div>
                    <div id="map"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        // 1. Initialize Map
        var map = L.map('map').setView([23.8103, 90.4125], 11);

        // 2. Add OpenStreetMap Tile Layer
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        // 3. Load PHP Data into JS
        var locations = <?php echo json_encode($map_data); ?>;
        var bounds = [];

        // 4. Loop through locations and add markers
        locations.forEach(function(loc) {
            var coords = loc.coords.split(',');
            if(coords.length === 2) {
                var lat = parseFloat(coords[0]);
                var lng = parseFloat(coords[1]);

                var marker = L.marker([lat, lng]).addTo(map);
                
                // Add simple popup for students
                marker.bindPopup(`
                    <div style="text-align:center">
                        <i class="fas fa-bus-alt" style="color:#4361ee; font-size:18px;"></i><br>
                        <strong>${loc.name}</strong>
                    </div>
                `);

                bounds.push([lat, lng]);
            }
        });

        // Auto-fit map to show all markers
        if (bounds.length > 0) {
            map.fitBounds(bounds);
        }

        // 5. User Geolocation Logic
        var userMarker = null;
        var userIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        function locateUser() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    var lat = position.coords.latitude;
                    var lng = position.coords.longitude;
                    var accuracy = position.coords.accuracy;

                    if (userMarker) map.removeLayer(userMarker);

                    userMarker = L.marker([lat, lng], {icon: userIcon}).addTo(map);
                    userMarker.bindPopup("<b>You are here</b>").openPopup();
                    
                    L.circle([lat, lng], {radius: accuracy, color: 'red', fillOpacity: 0.1}).addTo(map);
                    map.flyTo([lat, lng], 15);

                }, function(error) {
                    alert("Error getting location: " + error.message);
                });
            } else {
                alert("Geolocation is not supported by this browser.");
            }
        }
    </script>
</body>
</html>