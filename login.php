<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - University Transport System</title>
    
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    
    <style>
        /* --- THEME STYLES (MATCHING DASHBOARD) --- */
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3a0ca3;
            --accent-color: #4cc9f0;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #4CAF50;
            --danger-color: #f44336;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fb; /* Same background as dashboard */
            color: #333;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        .login-card {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            border-top: 5px solid var(--primary-color);
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h2 {
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 5px;
        }

        .login-header p {
            color: #777;
            font-size: 0.9rem;
        }

        .form-control {
            border-radius: 6px;
            padding: 10px 15px;
            border: 1px solid #ddd;
        }

        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
            border-color: var(--primary-color);
        }

        .form-label {
            font-weight: 500;
            font-size: 0.9rem;
            color: #555;
            margin-bottom: 8px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 12px;
            font-weight: 600;
            width: 100%;
            border-radius: 6px;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(67, 97, 238, 0.3);
        }

        .signup-link {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
        }

        .signup-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }

        .signup-link a:hover {
            text-decoration: underline;
        }

        .input-group-text {
            background-color: #f8f9fa;
            border-color: #ddd;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container d-flex justify-content-center">
        <div class="login-card">
            <div class="login-header">
                <div style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 10px;">
                    <i class="fas fa-bus-alt"></i>
                </div>
                <h2>Welcome Back</h2>
                <p>Login to Transport Management System</p>
            </div>

            <form id="loginForm">
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
            
            <div id="loginAlert" class="mt-3"></div>
            
            <div class="signup-link">
                Don't have an account? <a href="signup.php">Signup as Student</a>
            </div>
        </div>
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
                    $('#loginAlert').html('<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-exclamation-circle"></i> ' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                }
            });
        });
    </script>
</body>
</html>