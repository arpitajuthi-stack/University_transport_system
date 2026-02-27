<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Signup - University Transport System</title>
    
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    
    <style>
        /* --- THEME STYLES (MATCHING LOGIN & DASHBOARD) --- */
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
            background-color: #f5f7fb;
            color: #333;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px 0; /* Add padding for vertical scrolling on small screens */
        }

        .auth-card {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            padding: 40px;
            width: 100%;
            max-width: 700px; /* Wider than login to accommodate 2 columns */
            border-top: 5px solid var(--primary-color);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .auth-header h2 {
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 5px;
        }

        .auth-header p {
            color: #777;
            font-size: 0.9rem;
        }

        .form-control, .form-select {
            border-radius: 6px;
            padding: 10px 15px;
            border: 1px solid #ddd;
        }

        .form-control:focus, .form-select:focus {
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

        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
        }

        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
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
        <div class="auth-card">
            <div class="auth-header">
                <div style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 10px;">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <h2>Student Registration</h2>
                <p>Create your account to access transport services</p>
            </div>

            <form id="signupForm" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">First Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" name="first_name" class="form-control" required placeholder="First Name">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Last Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" name="last_name" class="form-control" required placeholder="Last Name">
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Student ID</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                        <input type="text" name="student_id" class="form-control" required placeholder="e.g. 2023001">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Gender</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-venus-mars"></i></span>
                        <select name="gender" class="form-select" required>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" name="email" class="form-control" required placeholder="student@example.com">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone Number</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                        <input type="text" name="phone" class="form-control" required placeholder="01XXXXXXXXX">
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" class="form-control" required placeholder="Password">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Confirm Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                        <input type="password" name="confirm_password" class="form-control" required placeholder="Confirm Password">
                    </div>
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Sign Up
                    </button>
                </div>
            </form>
            
            <div id="signupAlert" class="mt-3"></div>
            
            <div class="login-link">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </div>
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
                    // Success alert before redirect
                    $('#signupAlert').html('<div class="alert alert-success"><i class="fas fa-check-circle"></i> Signup successful! Redirecting...</div>');
                    setTimeout(function() {
                        window.location.href = 'login.php';
                    }, 1500);
                } else {
                    $('#signupAlert').html('<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-exclamation-circle"></i> ' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                }
            });
        });
    </script>
</body>
</html>