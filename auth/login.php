<?php
require_once '../config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $sql = "SELECT user_id, username, password, full_name, role, is_active FROM users WHERE username = ? AND is_active = 1";
        $user = fetchOne($sql, [$username]);
        
        if ($user && password_verify($password, $user['password'])) {
            // Ensure no customer session conflicts when admin/staff logs in
            unset($_SESSION['customer_id'], $_SESSION['customer_account_number'], $_SESSION['customer_name']);
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            
            logActivity('User login', 'users', $user['user_id']);
            redirect('dashboard.php');
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo url('css/socoteco-theme.css'); ?>" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .split-container {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }
        
        .left-side {
            flex: 1.3;
            background: var(--socoteco-primary-gradient);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        
        .left-side::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .logo-section {
            text-align: center;
            color: white;
            z-index: 2;
            position: relative;
        }
        
        .main-logo {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background-image: url('<?php echo url('img/logo1.png'); ?>');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            border: 5px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            margin: 0 auto 30px;
            transition: all 0.3s ease;
        }
        
        .main-logo:hover {
            transform: scale(1.05);
            box-shadow: 0 25px 60px rgba(255, 255, 255, 0.4);
        }
        
        .logo-title {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 10px;
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }
        
        .logo-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            font-weight: 300;
        }
        
        .right-side {
            flex: 0.7;
            background: white;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }
        
        .red-divider {
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: var(--socoteco-red);
            box-shadow: 2px 0 10px rgba(220, 20, 60, 0.3);
        }
        
        .login-form-container {
            width: 100%;
            max-width: 400px;
            padding: 40px;
        }
        
        .login-title {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-title h2 {
            color: var(--socoteco-dark-blue);
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .login-title p {
            color: #6c757d;
            margin: 0;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--socoteco-orange);
            box-shadow: 0 0 0 0.2rem var(--shadow-primary);
        }
        
        .btn-login {
            background: var(--socoteco-primary-gradient);
            border: none;
            border-radius: 10px;
            padding: 15px;
            font-weight: 600;
            color: white;
            font-size: 16px;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-login:hover {
            background: var(--socoteco-secondary-gradient);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px var(--shadow-primary);
            color: white;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--socoteco-dark-blue);
            margin-bottom: 8px;
        }
        
        .form-label i {
            color: var(--socoteco-orange);
        }
        
        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            backdrop-filter: blur(5px);
        }
        
        .loading-container {
            text-align: center;
            color: white;
        }
        
        .loading-logo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background-image: url('<?php echo url('img/logo1.png'); ?>');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            border: 4px solid rgba(255, 255, 255, 0.3);
            margin: 0 auto 30px;
            animation: logoSpin 2s linear infinite;
            box-shadow: 0 15px 40px rgba(255, 255, 255, 0.2);
        }
        
        .loading-text {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 10px;
            color: var(--socoteco-yellow);
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
        }
        
        .loading-subtitle {
            font-size: 1rem;
            opacity: 0.8;
            margin-bottom: 20px;
        }
        
        .loading-dots {
            display: flex;
            justify-content: center;
            gap: 8px;
        }
        
        .loading-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--socoteco-orange);
            animation: dotPulse 1.5s ease-in-out infinite;
        }
        
        .loading-dot:nth-child(2) {
            animation-delay: 0.2s;
        }
        
        .loading-dot:nth-child(3) {
            animation-delay: 0.4s;
        }
        
        @keyframes logoSpin {
            0% { transform: rotate(0deg) scale(1); }
            50% { transform: rotate(180deg) scale(1.1); }
            100% { transform: rotate(360deg) scale(1); }
        }
        
        @keyframes dotPulse {
            0%, 100% { 
                transform: scale(1);
                opacity: 0.5;
            }
            50% { 
                transform: scale(1.2);
                opacity: 1;
            }
        }
        
        @media (max-width: 768px) {
            .split-container {
                flex-direction: column;
            }
            
            .left-side {
                min-height: 40vh;
            }
            
            .main-logo {
                width: 120px;
                height: 120px;
            }
            
            .logo-title {
                font-size: 2rem;
            }
            
            .red-divider {
                top: 0;
                left: 0;
                right: 0;
                bottom: auto;
                width: auto;
                height: 4px;
            }
        }
    </style>
</head>
<body>
    <div class="split-container">
        <!-- Left Side - Logo Section -->
        <div class="left-side">
            <div class="logo-section">
                <div class="main-logo"></div>
                <h1 class="logo-title">SOCOTECO II</h1>
                <p class="logo-subtitle">Billing Management System</p>
            </div>
        </div>
        
        <!-- Red Divider Line -->
        <div class="red-divider"></div>
        
        <!-- Right Side - Login Form -->
        <div class="right-side">
            <div class="login-form-container">
                <div class="login-title">
                    <h2>Welcome!</h2>
                    <p>Sign in to your account</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-4">
                        <label for="username" class="form-label">
                            <i class="fas fa-user me-2"></i>Username
                        </label>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-2"></i>Password
                        </label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-login" id="loginBtn">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </button>
                </form>
                
                <div class="text-center mt-4">
                    <small class="text-muted">
                        Default Admin: admin / admin123
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-container">
            <div class="loading-logo"></div>
            <div class="loading-text">SOCOTECO II</div>
            <div class="loading-subtitle">Signing you in...</div>
            <div class="loading-dots">
                <div class="loading-dot"></div>
                <div class="loading-dot"></div>
                <div class="loading-dot"></div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('loginBtn').addEventListener('click', function(e) {
            // Prevent default form submission
            e.preventDefault();
            
            // Show loading overlay
            document.getElementById('loadingOverlay').style.display = 'flex';
            
            // Show loading for 3 seconds before submitting
            setTimeout(function() {
                // Submit the form after 3 seconds
                document.querySelector('form').submit();
            }, 3000);
        });
        
        // Hide loading overlay if there's an error (page reloads with error)
        <?php if ($error): ?>
        document.getElementById('loadingOverlay').style.display = 'none';
        <?php endif; ?>
    </script>
</body>
</html>
