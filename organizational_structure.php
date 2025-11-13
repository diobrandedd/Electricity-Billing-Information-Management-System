<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizational Structure - SOCOTECO II</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo url('css/socoteco-theme.css'); ?>" rel="stylesheet">
    <style>
        :root {
            --primary-orange: #FF9A00;
            --secondary-yellow: #FFD93D;
            --dark-blue: #1e3a8a;
            --light-gray: #f8f9fa;
        }
        
        body {
            background-color: var(--light-gray);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--primary-orange), var(--secondary-yellow));
            padding: 60px 0;
            color: white;
        }
        
        .content-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .content-card .card-header {
            background: linear-gradient(135deg, var(--primary-orange), var(--secondary-yellow));
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 20px;
        }
        
        .btn-primary {
            background-color: var(--primary-orange);
            border-color: var(--primary-orange);
        }
        
        .btn-primary:hover {
            background-color: #e68900;
            border-color: #e68900;
        }
    </style>
</head>
<body>
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light" style="background-color: var(--primary-orange)">
        <div class="container">
            <a class="navbar-brand" href="users/userindex.php">
                <img src="img/socotecoLogo.png" alt="SOCOTECO II" class="d-inline-block align-text-top" style="max-height: 60px;">
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text text-white">
                    <i class="fas fa-phone me-2"></i>(083) 553-5848 to 50
                </span>
            </div>
        </div>
    </nav>

    <!-- Main Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light" style="background-color: var(--secondary-yellow)">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="users/userindex.php"><i class="fas fa-home me-1"></i>Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-building me-1"></i>My Coop
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="company_profile.php"><i class="fas fa-info-circle me-2"></i>Company Profile</a></li>
                            <li><a class="dropdown-item" href="vision_mission.php"><i class="fas fa-eye me-2"></i>Vision, Mission, & Values</a></li>
                            <li><a class="dropdown-item" href="board_directors.php"><i class="fas fa-users me-2"></i>Board of Directors</a></li>
                            <li><a class="dropdown-item" href="management_team.php"><i class="fas fa-user-tie me-2"></i>Management Team</a></li>
                            <li><a class="dropdown-item" href="organizational_structure.php"><i class="fas fa-sitemap me-2"></i>Organizational Structure</a></li>
                            <li><a class="dropdown-item" href="coop_activities.php"><i class="fas fa-chart-bar me-2"></i>Coop Activities</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-newspaper me-1"></i>News & Updates
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="news_updates.php"><i class="fas fa-newspaper me-2"></i>Latest News</a></li>
                            <li><a class="dropdown-item" href="coop_activities.php#community"><i class="fas fa-heart me-2"></i>Community Programs (CSR Activities)</a></li>
                            <li><a class="dropdown-item" href="coop_activities.php#supply-demand"><i class="fas fa-chart-line me-2"></i>Daily Supply and Load Demand</a></li>
                            <li><a class="dropdown-item" href="coop_activities.php#generation-breakdown"><i class="fas fa-cogs me-2"></i>Breakdown of Generation Charge</a></li>
                            <li><a class="dropdown-item" href="coop_activities.php#rates"><i class="fas fa-percentage me-2"></i>Effective Rates</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-headset me-1"></i>Customer Service
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="customer_service.php"><i class="fas fa-headset me-2"></i>Customer Service Home</a></li>
                            <li><a class="dropdown-item" href="customer_service.php#contact"><i class="fas fa-phone me-2"></i>Contact Information</a></li>
                            <li><a class="dropdown-item" href="customer_service.php#hours"><i class="fas fa-clock me-2"></i>Operating Hours (Teller)</a></li>
                            <li><a class="dropdown-item" href="customer_service.php#application"><i class="fas fa-search me-2"></i>Electric Service Application Status</a></li>
                            <li><a class="dropdown-item" href="customer_service.php#sms"><i class="fas fa-sms me-2"></i>Register to SMS Broadcast System</a></li>
                            <li><a class="dropdown-item" href="customer_service.php#support"><i class="fas fa-life-ring me-2"></i>Support Channels / Resources</a></li>
                            <li><a class="dropdown-item" href="customer_service.php#complaint"><i class="fas fa-exclamation-triangle me-2"></i>Complaint Form</a></li>
                            <li><a class="dropdown-item" href="customer_service.php#calculator"><i class="fas fa-calculator me-2"></i>Estimated Bill Calculator</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cogs me-1"></i>Services
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="services.php"><i class="fas fa-cogs me-2"></i>Services Home</a></li>
                            <li><a class="dropdown-item" href="services.php#government"><i class="fas fa-building me-2"></i>Government Subsidized Projects</a></li>
                            <li><a class="dropdown-item" href="services.php#renewable"><i class="fas fa-leaf me-2"></i>Renewable Energy Projects</a></li>
                            <li><a class="dropdown-item" href="services.php#suppliers"><i class="fas fa-industry me-2"></i>Power Suppliers</a></li>
                            <li><a class="dropdown-item" href="services.php#payment"><i class="fas fa-credit-card me-2"></i>Payment Channels</a></li>
                            <li><a class="dropdown-item" href="services.php#electricians"><i class="fas fa-tools me-2"></i>Accredited Electricians</a></li>
                            <li><a class="dropdown-item" href="services.php#downloads"><i class="fas fa-download me-2"></i>Get Files (Downloads)</a></li>
                        </ul>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="job_opportunity.php"><i class="fas fa-briefcase me-1"></i>Job Opportunity</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php"><i class="fas fa-user me-1"></i>Members Portal</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-4">Organizational Structure</h1>
                    <p class="lead mb-4">Understanding SOCOTECO II's organizational framework and leadership hierarchy.</p>
                </div>
                <div class="col-lg-4 text-center">
                    <i class="fas fa-sitemap" style="font-size: 8rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container py-5">

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0"><i class="fas fa-sitemap me-2"></i>Organizational Structure</h3>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-12">
                            <p class="lead">The Organizational Structure information will be updated with the official details from the SOCOTECO II website.</p>
                        </div>
                    </div>
                    
                    <!-- Organizational Chart -->
                    <div class="row mb-5">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="mb-0">Organizational Chart</h4>
                                </div>
                                <div class="card-body">
                                    <div class="org-chart">
                                        <!-- Board of Directors -->
                                        <div class="text-center mb-4">
                                            <div class="org-level-1">
                                                <div class="org-box bg-primary text-white">
                                                    <h5>Board of Directors</h5>
                                                    <small>Policy Making Body</small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- General Manager -->
                                        <div class="text-center mb-4">
                                            <div class="org-level-2">
                                                <div class="org-box bg-success text-white">
                                                    <h5>General Manager</h5>
                                                    <small>Chief Executive Officer</small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Management Team -->
                                        <div class="row mb-4">
                                            <div class="col-md-3 mb-3">
                                                <div class="org-box bg-info text-white">
                                                    <h6>Assistant General Manager</h6>
                                                    <small>Operations</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="org-box bg-warning text-dark">
                                                    <h6>Finance Manager</h6>
                                                    <small>Financial Management</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="org-box bg-danger text-white">
                                                    <h6>Engineering Manager</h6>
                                                    <small>Technical Operations</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="org-box bg-secondary text-white">
                                                    <h6>HR Manager</h6>
                                                    <small>Human Resources</small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Departments -->
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <div class="org-box bg-light border">
                                                    <h6>Customer Service</h6>
                                                    <small>Billing & Collections</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="org-box bg-light border">
                                                    <h6>Operations</h6>
                                                    <small>Line Maintenance</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="org-box bg-light border">
                                                    <h6>Administration</h6>
                                                    <small>Support Services</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Department Details -->
                    <div class="row">
                        <div class="col-lg-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">Executive Level</h5>
                                </div>
                                <div class="card-body">
                                    <h6>Board of Directors</h6>
                                    <ul>
                                        <li>Policy formulation and strategic planning</li>
                                        <li>Governance and oversight</li>
                                        <li>Financial accountability</li>
                                        <li>Member representation</li>
                                    </ul>
                                    
                                    <h6>General Manager</h6>
                                    <ul>
                                        <li>Overall management and operations</li>
                                        <li>Strategic implementation</li>
                                        <li>External relations</li>
                                        <li>Performance monitoring</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">Management Level</h5>
                                </div>
                                <div class="card-body">
                                    <h6>Assistant General Manager</h6>
                                    <ul>
                                        <li>Operations coordination</li>
                                        <li>Administrative support</li>
                                        <li>Performance management</li>
                                        <li>Process improvement</li>
                                    </ul>
                                    
                                    <h6>Department Managers</h6>
                                    <ul>
                                        <li>Finance: Financial planning and control</li>
                                        <li>Engineering: Technical operations</li>
                                        <li>Customer Service: Member relations</li>
                                        <li>HR: Human resource management</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">Operations Level</h5>
                                </div>
                                <div class="card-body">
                                    <h6>Customer Service Department</h6>
                                    <ul>
                                        <li>Billing and collections</li>
                                        <li>Customer support</li>
                                        <li>Account management</li>
                                        <li>Payment processing</li>
                                    </ul>
                                    
                                    <h6>Engineering Department</h6>
                                    <ul>
                                        <li>Line maintenance</li>
                                        <li>System operations</li>
                                        <li>Technical support</li>
                                        <li>Infrastructure development</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="mb-0">Support Level</h5>
                                </div>
                                <div class="card-body">
                                    <h6>Administration Department</h6>
                                    <ul>
                                        <li>General administration</li>
                                        <li>Records management</li>
                                        <li>Facilities maintenance</li>
                                        <li>Procurement</li>
                                    </ul>
                                    
                                    <h6>Finance Department</h6>
                                    <ul>
                                        <li>Accounting and bookkeeping</li>
                                        <li>Budget preparation</li>
                                        <li>Financial reporting</li>
                                        <li>Audit coordination</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.org-chart {
    font-family: Arial, sans-serif;
}

.org-box {
    padding: 15px;
    border-radius: 8px;
    margin: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.org-level-1 .org-box {
    font-size: 1.2em;
    padding: 20px;
}

.org-level-2 .org-box {
    font-size: 1.1em;
    padding: 18px;
}

.org-box h5, .org-box h6 {
    margin: 0;
    font-weight: bold;
}

.org-box small {
    font-size: 0.9em;
    opacity: 0.8;
}
</style>

    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5 class="fw-bold mb-3">SOCOTECO II</h5>
                    <p>South Cotabato II Electric Cooperative, Inc.</p>
                    <p>Jose Catolico Avenue, Brgy Lagao, General Santos City, 9500</p>
                </div>
                <div class="col-lg-4 mb-4">
                    <h5 class="fw-bold mb-3">Contact Info</h5>
                    <p><i class="fas fa-phone me-2"></i>(083) 553-5848 to 50</p>
                    <p><i class="fas fa-mobile-alt me-2"></i>09177205365 / 09124094971</p>
                </div>
                <div class="col-lg-4 mb-4">
                    <h5 class="fw-bold mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="company_profile.php" class="text-white-50 text-decoration-none">Company Profile</a></li>
                        <li><a href="vision_mission.php" class="text-white-50 text-decoration-none">Vision, Mission & Values</a></li>
                        <li><a href="board_directors.php" class="text-white-50 text-decoration-none">Board of Directors</a></li>
                        <li><a href="management_team.php" class="text-white-50 text-decoration-none">Management Team</a></li>
                        <li><a href="organizational_structure.php" class="text-white-50 text-decoration-none">Organizational Structure</a></li>
                        <li><a href="coop_activities.php" class="text-white-50 text-decoration-none">Coop Activities</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="row">
                <div class="col-12 text-center">
                    <p class="mb-0">&copy; 2024 SOCOTECO II Official Website. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
