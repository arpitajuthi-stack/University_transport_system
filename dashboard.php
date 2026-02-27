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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Transport Management System</title>
    
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
    
    <style>
        /* --- DASHBOARD STYLES (MATCHING ADMIN THEME) --- */
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
        
        /* Welcome Section */
        .welcome-card {
            background: linear-gradient(135deg, var(--primary-color), #5a6ff0);
            color: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.2);
        }

        .welcome-card h2 {
            font-weight: 600;
            font-size: 1.8rem;
            margin-bottom: 10px;
        }

        .welcome-card p {
            opacity: 0.9;
            margin-bottom: 0;
            font-size: 1.1rem;
        }

        /* Content Cards (Student Specific Actions) */
        .content-card {
            background-color: white;
            border-radius: 10px;
            padding: 30px 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 0;
            height: 100%;
            transition: all 0.3s;
            border-top: 4px solid var(--primary-color);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .content-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        /* Specific borders for different actions */
        .content-card.ticket { border-top-color: var(--primary-color); }
        .content-card.history { border-top-color: var(--success-color); }
        .content-card.profile { border-top-color: var(--accent-color); }
        .content-card.security { border-top-color: var(--warning-color); }

        .action-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background-color: #f0f2ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 20px;
            color: var(--primary-color);
            transition: all 0.3s;
        }
        
        .content-card:hover .action-icon {
            background-color: var(--primary-color);
            color: white;
        }

        .content-card h5 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--dark-color);
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
                <a href="dashboard.php" class="menu-item active">
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
                    <h1>Student Dashboard</h1>
                    <div class="welcome-text">Welcome, <?php echo htmlspecialchars($user['first_name']); ?></div>
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
                
                <div class="welcome-card">
                    <h2>Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>!</h2>
                    <p>Manage your trips, view history, and update your profile from here.</p>
                </div>
                
                <div class="row g-4">
                    <div class="col-md-6 col-lg-3">
                        <div class="content-card ticket text-center">
                            <div class="action-icon">
                                <i class="fas fa-ticket-alt"></i>
                            </div>
                            <h5>Buy Ticket</h5>
                            <p class="text-muted small mb-3">Purchase tickets for upcoming trips</p>
                            <a href="buy_ticket.php" class="btn btn-primary btn-sm w-100">Go to Booking</a>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="content-card history text-center">
                            <div class="action-icon">
                                <i class="fas fa-history"></i>
                            </div>
                            <h5>My Tickets</h5>
                            <p class="text-muted small mb-3">View your active and past tickets</p>
                            <a href="my_tickets.php" class="btn btn-outline-primary btn-sm w-100">View History</a>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="content-card profile text-center">
                            <div class="action-icon">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <h5>Edit Profile</h5>
                            <p class="text-muted small mb-3">Update your personal information</p>
                            <a href="edit_profile.php" class="btn btn-outline-info btn-sm w-100">Update Profile</a>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="content-card security text-center">
                            <div class="action-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h5>Security</h5>
                            <p class="text-muted small mb-3">Change your account password</p>
                            <a href="change_password.php" class="btn btn-outline-warning btn-sm w-100">Change Password</a>
                        </div>
                    </div>
                </div>

                <div class="footer">
                    <p>University Transport System &copy; <?php echo date('Y'); ?> | Student Portal</p>
                </div>

            </div>
        </div>
    </div>

    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add active class to current menu item (Same logic as Admin Dashboard)
        $(document).ready(function() {
            // Get current page URL
            var url = window.location.pathname;
            var filename = url.substring(url.lastIndexOf('/') + 1);
            
            // Remove active class from all menu items
            $('.menu-item').removeClass('active');
            
            // Add active class to current menu item
            $('.menu-item').each(function() {
                var href = $(this).attr('href');
                if (href === filename) {
                    $(this).addClass('active');
                }
            });
            
            // Animate cards on load
            $('.content-card').each(function(i) {
                var $card = $(this);
                $card.css('opacity', '0');
                setTimeout(function() {
                    $card.css('opacity', '1').addClass('animate__animated animate__fadeInUp');
                    $card.animate({opacity: 1}, 300);
                }, i * 100);
            });
            
            // Simple animation for welcome card
            $('.welcome-card').hide().fadeIn(800);
        });
    </script>
</body>
</html>