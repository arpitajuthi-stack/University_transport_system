<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') header("Location: ../login.php");
include '../db.php';

// Fetch user data for Header (Added for styling consistency)
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
    <title>Change Password - Student Portal</title>
    
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                <a href="change_password.php" class="menu-item active">
                    <i class="fas fa-key"></i>
                    <span>Change Password</span>
                </a>
            </div>
        </div>
        
        <div class="admin-main">
            <div class="admin-header">
                <div class="header-left">
                    <h1>Change Password</h1>
                    <div class="welcome-text">Secure your account</div>
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
                    <h2 class="section-header"><i class="fas fa-lock"></i> Update Password</h2>
                    
                    <div class="row">
                        <div class="col-md-8 col-lg-6">
                            <form id="changePasswordForm">
                                <div class="form-group mb-3">
                                    <label class="form-label">Current Password</label>
                                    <div class="input-group">
                                        <input type="password" name="current_pass" class="form-control" required>
                                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="form-label">New Password</label>
                                    <div class="input-group">
                                        <input type="password" name="new_pass" class="form-control" required>
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    </div>
                                </div>
                                <div class="form-group mb-4">
                                    <label class="form-label">Confirm New Password</label>
                                    <div class="input-group">
                                        <input type="password" name="confirm_new_pass" class="form-control" required>
                                        <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Change Password</button>
                            </form>
                            
                            <div id="changeAlert" class="mt-3"></div>
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