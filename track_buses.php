<?php
session_start();
// Security Check: Only allow 'student' role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') header("Location: ../login.php");
include '../db.php';

// Fetch logged-in user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Fetch ONLY active (started) rides
$query = "
    SELECT r.id, r.started_at, b.reg_number, d.name as dest_name, 
           CONCAT(u.first_name, ' ', u.last_name) as driver_name,
           d.start_map_coords, d.end_map_coords, r.last_map_coords
    FROM rides r
    JOIN users u ON r.driver_id = u.id
    JOIN buses b ON r.bus_id = b.id
    JOIN destinations d ON r.destination_id = d.id
    WHERE r.status = 'started'
";
$result = $conn->query($query);
$active_rides = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Buses - Student Portal</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <link rel="stylesheet" href="../assets/css/custom.css">
    
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3a0ca3;
            --accent-color: #4cc9f0;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --sidebar-width: 250px;
            --header-height: 70px;
        }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f5f7fb; margin: 0; padding: 0; }
        .admin-container { display: flex; min-height: 100vh; }
        
        /* Z-Index Fix for Modal */
        .admin-sidebar { width: var(--sidebar-width); background: linear-gradient(180deg, var(--primary-color), var(--secondary-color)); color: white; position: fixed; height: 100%; z-index: 100; }
        
        .sidebar-header { padding: 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header h3 { font-size: 1.4rem; font-weight: 600; margin: 0; }
        .sidebar-header p { opacity: 0.8; font-size: 0.9rem; margin: 5px 0 0; }
        .sidebar-menu { padding: 20px 0; }
        .menu-item { padding: 12px 20px; display: flex; align-items: center; color: white; text-decoration: none; transition: 0.3s; border-left: 4px solid transparent; }
        .menu-item:hover, .menu-item.active { background-color: rgba(255,255,255,0.1); border-left-color: var(--accent-color); color: white; }
        .menu-item i { margin-right: 12px; width: 25px; text-align: center; font-size: 1.1rem; }
        .admin-main { flex: 1; margin-left: var(--sidebar-width); }
        .admin-header { height: var(--header-height); background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; padding: 0 25px; position: sticky; top: 0; z-index: 99; }
        .header-left h1 { font-size: 1.5rem; margin: 0; font-weight: 600; color: var(--dark-color); }
        .dashboard-content { padding: 25px; }
        .content-card { background: white; border-radius: 10px; padding: 25px; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        
        #trackingMap { height: 500px; width: 100%; border-radius: 8px; }
        .user-avatar { width: 45px; height: 45px; border-radius: 50%; background: linear-gradient(45deg, var(--primary-color), var(--accent-color)); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; margin-right: 12px; }
        .logout-btn { background: #f44336; color: white; padding: 8px 16px; border-radius: 4px; text-decoration: none; display: inline-flex; align-items: center; }
        .logout-btn:hover { background: #d32f2f; color: white; }
        
        @media (max-width: 992px) {
            .admin-sidebar { width: 70px; }
            .sidebar-header h3, .sidebar-header p, .menu-item span { display: none; }
            .admin-sidebar:hover { width: var(--sidebar-width); }
            .admin-sidebar:hover .sidebar-header h3, .admin-sidebar:hover .sidebar-header p, .admin-sidebar:hover .menu-item span { display: block; }
            .admin-main { margin-left: 70px; }
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
                <a href="map.php" class="menu-item">
                    <i class="fas fa-map"></i>
                    <span>Route Map</span>
                </a>
                <a href="track_buses.php" class="menu-item active">
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
                    <h1>Track Active Buses</h1>
                </div>
                <div class="user-info d-flex align-items-center">
                    <div class="user-avatar"><?php echo strtoupper(substr($user['first_name'], 0, 1)); ?></div>
                    <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
                </div>
            </div>

            <div class="dashboard-content">
                <div class="content-card">
                    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                        <h4 class="m-0 text-primary"><i class="fas fa-bus-alt me-2"></i> Live Rides</h4>
                        <span class="badge bg-success"><?php echo count($active_rides); ?> Active Now</span>
                    </div>

                    <?php if (count($active_rides) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Bus Reg</th>
                                        <th>Driver</th>
                                        <th>Destination</th>
                                        <th>Started At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($active_rides as $ride): ?>
                                    <tr>
                                        <td><strong><?php echo $ride['reg_number']; ?></strong></td>
                                        <td><?php echo $ride['driver_name']; ?></td>
                                        <td><?php echo $ride['dest_name']; ?></td>
                                        <td><?php echo date('h:i A', strtotime($ride['started_at'])); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-primary btn-sm track-btn" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#trackModal"
                                                data-ride-id="<?php echo $ride['id']; ?>"
                                                data-driver="<?php echo $ride['driver_name']; ?>"
                                                data-start="<?php echo $ride['start_map_coords']; ?>"
                                                data-end="<?php echo $ride['end_map_coords']; ?>"
                                                data-current="<?php echo $ride['last_map_coords']; ?>">
                                                <i class="fas fa-map-marked-alt me-1"></i> Track Live
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center py-5">
                            <i class="fas fa-info-circle fa-3x mb-3 text-muted"></i>
                            <h5>No buses are currently running.</h5>
                            <p class="text-muted">Active rides will appear here automatically when drivers start their trips.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="trackModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-satellite-dish me-2"></i> Tracking: <span id="modalDriverName"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="trackingMap"></div>
                </div>
                <div class="modal-footer justify-content-between">
                    <div class="text-muted small">
                        <i class="fas fa-circle text-success"></i> Start &nbsp;
                        <i class="fas fa-circle text-danger"></i> End &nbsp;
                        <i class="fas fa-bus text-primary"></i> Current Location
                    </div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <script>
        var map, pollingInterval;
        var busMarker = null;

        var greenIcon = new L.Icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
        });
        var redIcon = new L.Icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
        });
        var blueIcon = new L.Icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
        });

        $(document).ready(function() {
            var myModalEl = document.getElementById('trackModal');
            myModalEl.addEventListener('shown.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var rideId = button.data('ride-id');
                var driverName = button.data('driver');
                var startCoords = button.data('start');
                var endCoords = button.data('end');
                var currentCoords = button.data('current');

                $('#modalDriverName').text(driverName);

                if (!map) {
                    map = L.map('trackingMap').setView([23.8103, 90.4125], 13);
                    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap'
                    }).addTo(map);
                }
                
                setTimeout(function(){ map.invalidateSize(); }, 200);

                map.eachLayer(function (layer) {
                    if (!!layer.toGeoJSON) {
                        map.removeLayer(layer);
                    }
                });

                if (startCoords && startCoords.toString().includes(',')) {
                    var s = startCoords.split(',');
                    L.marker([s[0], s[1]], {icon: greenIcon}).addTo(map).bindPopup("Start Point");
                }
                if (endCoords && endCoords.toString().includes(',')) {
                    var e = endCoords.split(',');
                    L.marker([e[0], e[1]], {icon: redIcon}).addTo(map).bindPopup("End Point");
                }

                busMarker = null;
                if (currentCoords && currentCoords.toString().includes(',')) {
                    var c = currentCoords.split(',');
                    busMarker = L.marker([c[0], c[1]], {icon: blueIcon}).addTo(map).bindPopup("Driver: " + driverName).openPopup();
                    map.setView([c[0], c[1]], 15);
                } else if (startCoords && startCoords.toString().includes(',')) {
                     var s = startCoords.split(',');
                     map.setView([s[0], s[1]], 14);
                }

                if(pollingInterval) clearInterval(pollingInterval);
                startPolling(rideId, driverName);
            });

            myModalEl.addEventListener('hidden.bs.modal', function () {
                if(pollingInterval) clearInterval(pollingInterval);
            });
        });

        function startPolling(rideId, driverName) {
            pollingInterval = setInterval(function() {
                $.post('../ajax/ride_actions.php', {
                    action: 'get_latest_coords',
                    ride_id: rideId
                }, function(response) {
                    try {
                        var data = JSON.parse(response);
                        if(data.success && data.status === 'started' && data.coords) {
                            var latlng = data.coords.split(',');
                            var newLat = parseFloat(latlng[0]);
                            var newLng = parseFloat(latlng[1]);

                            if (busMarker) {
                                busMarker.setLatLng([newLat, newLng]);
                            } else {
                                busMarker = L.marker([newLat, newLng], {icon: blueIcon}).addTo(map).bindPopup("Driver: " + driverName).openPopup();
                            }
                        } else if (data.status === 'ended' || data.status === 'cancelled') {
                            clearInterval(pollingInterval);
                            alert("Ride has ended or cancelled!");
                            location.reload();
                        }
                    } catch(e) { console.error(e); }
                });
            }, 3000);
        }
    </script>
</body>
</html>