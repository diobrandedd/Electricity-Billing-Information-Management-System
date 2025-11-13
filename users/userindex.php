<?php require_once __DIR__ . '/../config/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOCOTECO II - Customer Portal</title>
    <meta name="description" content="SOCOTECO II Customer Portal – view bills, make payments, get support, and see your electricity usage analytics.">
    <link rel="canonical" href="<?php echo url('users/userindex.php'); ?>">
    <meta property="og:title" content="SOCOTECO II - Customer Portal">
    <meta property="og:description" content="View bills, make payments, and see your usage analytics.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo url('users/userindex.php'); ?>">
    <meta property="og:image" content="<?php echo url('img/socotecoLogo.png'); ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="SOCOTECO II - Customer Portal">
    <meta name="twitter:description" content="Manage your account, bills, payments and support in one place.">
    <meta name="twitter:image" content="<?php echo url('img/socotecoLogo.png'); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="<?php echo url('css/socoteco-theme.css'); ?>" rel="stylesheet">
    <style>
        :root {
            --primary-orange: #FF9A00;
            --secondary-yellow: #FFD93D;
            --dark-blue: #1e3a8a;
            --light-gray: #f8f9fa;
        }
        
        .navbar-brand img {
            max-height: 60px;
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--primary-orange), var(--secondary-yellow));
            padding: 60px 0;
            color: white;
        }
        
        .service-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            height: 100%;
        }
        
        .service-card:hover {
            transform: translateY(-5px);
        }
        
        .service-icon {
            font-size: 3rem;
            color: var(--primary-orange);
            margin-bottom: 1rem;
        }
        .service-card .card-body { display: flex; flex-direction: column; }
        .service-card .card-body .btn { margin-top: auto; }
        
        .news-card {
            border-left: 4px solid var(--primary-orange);
            background: rgba(255,255,255,0.9);
            backdrop-filter: saturate(180%) blur(6px);
            -webkit-backdrop-filter: saturate(180%) blur(6px);
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border: 1px solid rgba(255,255,255,0.35);
        }
        
        .contact-info {
            background: rgba(255,255,255,0.85);
            backdrop-filter: saturate(180%) blur(6px);
            -webkit-backdrop-filter: saturate(180%) blur(6px);
            border-radius: 10px;
            padding: 20px;
            border: 1px solid rgba(255,255,255,0.35);
        }
        
        /* Page background image with 60% opacity */
        body {
            position: relative;
        }
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('../img/socotecobg.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0.9;
            z-index: -1;
            pointer-events: none;
        }
        
        .footer {
            background: var(--dark-blue);
            color: white;
            padding: 40px 0 20px;
        }
        /* Feedback list scroll area */
        .feedback-scroll {
            max-height: 380px;
            overflow-y: auto;
        }
        .reply-box { background: rgba(0,0,0,0.02); border-left: 3px solid var(--primary-orange); font-size: 0.9rem; color: #495057; }
        .truncate { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .reply-toggle { cursor: pointer; color: var(--primary-orange); font-weight: 600; }
        
        .btn-primary {
            background-color: var(--primary-orange);
            border-color: var(--primary-orange);
        }
        
        .btn-primary:hover {
            background-color: #e68900;
            border-color: #e68900;
        }
        
        .navbar-nav .nav-link {
            color: #333 !important;
            font-weight: 500;
            margin: 0 10px;
        }
        
        .navbar-nav .nav-link:hover {
            color: var(--primary-orange) !important;
        }
        .navbar.fixed-top, .fixed-top-2 { backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); }
        /* Sticky navbars (two-tier) */
        body { padding-top: 150px; }
        .navbar.fixed-top { z-index: 1040; box-shadow: 0 2px 6px rgba(0,0,0,0.06); }
        .fixed-top-2 { position: fixed; top: 80px; left: 0; right: 0; z-index: 1035; box-shadow: 0 2px 6px rgba(0,0,0,0.06); }
        @media (min-width: 992px) {
            body { padding-top: 130px; }
            .fixed-top-2 { top: 80px; }
        }
        @media (max-width: 991.98px) {
            body { padding-top: 120px; }
            .fixed-top-2 { top: 60px; }
        }
        
        /* Priority Number Modal Styles */
        .priority-number-display {
            background: linear-gradient(135deg, #ffc107, #ff8f00);
            border: 3px solid #ff8f00;
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
            animation: pulse-glow 2s infinite;
        }
        
        @keyframes pulse-glow {
            0% { box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3); }
            50% { box-shadow: 0 6px 20px rgba(255, 193, 7, 0.5); }
            100% { box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3); }
        }
        
        .priority-number-text {
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            font-weight: 900;
        }
        
        .category-card {
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0,0,0,0.2);
        }
        
        .category-card.selected {
            border-color: var(--primary-orange);
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
        }
        
        .category-icon {
            margin-bottom: 1rem;
        }

        /* Equalize carousel frame (square 460x460) */
        .carousel-equal { width: 100%; max-width: 460px; margin: 0 auto; }
        .carousel-equal .carousel-item { height: 460px; }
        .carousel-equal .carousel-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        /* Glass utility */
        .glass {
            background: rgba(255,255,255,0.9) !important;
            backdrop-filter: saturate(180%) blur(6px);
            -webkit-backdrop-filter: saturate(180%) blur(6px);
            border: 1px solid rgba(255,255,255,0.35);
        }

        /* Back to top */
        #backToTop {
            position: fixed;
            left: 16px;
            bottom: 20px;
            z-index: 1050;
            display: none;
            box-shadow: 0 6px 16px rgba(0,0,0,0.2);
            width: 48px; height: 48px;
            border-radius: 50% !important;
            padding: 0;
            align-items: center; justify-content: center;
        }
        #backToTop.show { display: inline-flex; }

        /* Skeleton loader */
        .skeleton {
            position: relative;
            overflow: hidden;
            background-color: #e9ecef;
            border-radius: 4px;
        }
        .skeleton::after {
            content: "";
            position: absolute;
            top: 0; left: -150px; height: 100%; width: 150px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.6), transparent);
            animation: shimmer 1.2s infinite;
        }
        @keyframes shimmer { 100% { transform: translateX(300%); } }
        
        .queue-status-card {
            transition: transform 0.2s ease;
        }
        
        .queue-status-card:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <a href="#mainContent" class="visually-hidden-focusable" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;">Skip to content</a>
    <!-- System Status Banner -->
    <?php $system_notice = getSystemSetting('system_notice', ''); if ($system_notice): ?>
    <div class="alert alert-warning text-center mb-0" role="status" aria-live="polite">
        <i class="fas fa-info-circle me-2" aria-hidden="true"></i><?php echo htmlspecialchars($system_notice); ?>
    </div>
    <?php endif; ?>
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top" style="background-color: var(--primary-orange)">
        <div class="container">
            <a class="navbar-brand" href="userindex.php">
                <img src="../img/socotecoLogo.png" alt="SOCOTECO II" class="d-inline-block align-text-top">
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text text-white">
                    <i class="fas fa-phone me-2"></i>(083) 553-5848 to 50
                </span>
            </div>
        </div>
    </nav>

    <!-- Main Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top-2" style="background-color: var(--secondary-yellow)">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="userindex.php"><i class="fas fa-home me-1"></i>Home</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <?php if (!empty($_SESSION['customer_id'])): ?>
                            <div class="dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['customer_name']); ?>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="../auth/customer_logout.php">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </a></li>
                                </ul>
                            </div>
                        <?php elseif (!empty($_SESSION['user_id']) && $_SESSION['role'] === 'admin'): ?>
                            <div class="dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-user-shield me-1"></i><?php echo htmlspecialchars($_SESSION['full_name']); ?> (Admin)
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="../dashboard.php">
                                        <i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard
                                    </a></li>
                                    <li><a class="dropdown-item" href="../feedback_management.php">
                                        <i class="fas fa-comments me-2"></i>Feedback Management
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="../auth/logout.php">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </a></li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <a class="nav-link" href="../auth/customer_login.php"><i class="fas fa-user me-1"></i>Members Portal</a>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">Hi!</h1>
                    <h2 class="h3 mb-4">How may we be of service to you today?</h2>
                    <p class="lead mb-4">Welcome to SOCOTECO II Customer Portal. Manage your electricity account, view bills, and access services online.</p>
                    
                </div>
                <div class="col-lg-6 text-center">
                    <i class="fas fa-bolt" aria-hidden="true" style="font-size: 8rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Back to top button -->
    <button id="backToTop" class="btn btn-primary rounded-circle align-items-center justify-content-center" aria-label="Back to top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Services Section -->
    <section class="py-5" id="mainContent" aria-label="Main content">
        <div class="container">
            <div class="row mb-5">
                <div class="col-12 text-center">
                    <h2 class="display-5 fw-bold text-dark">Our Services</h2>
                    <p class="lead text-muted">Everything you need for your electricity needs</p>
                </div>
            </div>
            <div class="row g-4 align-items-stretch">
                <div class="col-lg-3 col-md-6 d-flex">
                    <div class="card service-card w-100">
                        <div class="card-body d-flex flex-column text-center p-4">
                            <i class="fas fa-file-invoice-dollar service-icon"></i>
                            <h5 class="card-title">Bill Inquiry</h5>
                            <p class="card-text">View and download your electricity bills online</p>
                            <button class="btn btn-primary mt-auto" data-bs-toggle="modal" data-bs-target="#billInquiryModal">
                                <i class="fas fa-search me-2"></i>View Bills
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 d-flex">
                    <div class="card service-card w-100">
                        <div class="card-body d-flex flex-column text-center p-4">
                            <i class="fas fa-chart-line service-icon"></i>
                            <h5 class="card-title">Usage Analytics</h5>
                            <p class="card-text">See trends of your kWh and bills</p>
                            <a href="../customer/home.php#analytics" class="btn btn-primary mt-auto">Open Analytics</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="card service-card h-100">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-headset service-icon"></i>
                            <h5 class="card-title">Support</h5>
                            <p class="card-text">Get help and support for your concerns</p>
                            <a href="#" class="btn btn-primary mt-auto" data-bs-toggle="offcanvas" data-bs-target="#customerChatOffcanvas">Get Support</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 d-flex">
                    <div class="card service-card w-100">
                        <div class="card-body d-flex flex-column text-center p-4">
                            <i class="fas fa-ticket-alt service-icon"></i>
                            <h5 class="card-title">Priority Queue</h5>
                            <p class="card-text">Get a priority number for faster service</p>
                            <button type="button" class="btn btn-primary mt-auto" data-bs-toggle="modal" data-bs-target="#priorityModal">
                                Get Priority Number
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Carousel Section -->
    <section class="py-5" style="background-color: var(--light-gray)">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="card news-card">
                        <div class="card-body p-0">
                            <div id="homeCarousel" class="carousel slide carousel-equal" data-bs-ride="carousel" data-bs-interval="4000">
                                <div class="carousel-indicators">
                                    <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                                    <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                                    <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
                                    <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="3" aria-label="Slide 4"></button>
                                    <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="4" aria-label="Slide 5"></button>
                                    <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="5" aria-label="Slide 6"></button>
                                    <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="6" aria-label="Slide 7"></button>
                                </div>
                                <div class="carousel-inner">
                                    <div class="carousel-item active">
                                        <img src="../img/carousel1.png" class="d-block" alt="Carousel slide 1">
                                    </div>
                                    <div class="carousel-item">
                                        <img src="../img/carousel2.png" class="d-block" alt="Carousel slide 2">
                                    </div>
                                    <div class="carousel-item">
                                        <img src="../img/carousel3.png" class="d-block" alt="Carousel slide 3">
                                    </div>
                                    <div class="carousel-item">
                                        <img src="../img/carousel4.png" class="d-block" alt="Carousel slide 4">
                                    </div>
                                    <div class="carousel-item">
                                        <img src="../img/carousel5.png" class="d-block" alt="Carousel slide 5">
                                    </div>
                                    <div class="carousel-item">
                                        <img src="../img/carousel6.png" class="d-block" alt="Carousel slide 6">
                                    </div>
                                    <div class="carousel-item">
                                        <img src="../img/carousel7.png" class="d-block" alt="Carousel slide 7">
                                    </div>
                                </div>
                                <button class="carousel-control-prev" type="button" data-bs-target="#homeCarousel" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#homeCarousel" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Feedback Board Section -->
    <section class="py-5 glass">
        <div class="container">
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="display-6 fw-bold text-dark">Feedback Board</h2>
                    <p class="text-muted mb-0">
                        <?php if (!empty($_SESSION['customer_id'])): ?>
                            Welcome, <?php echo htmlspecialchars($_SESSION['customer_name']); ?>! Share your thoughts and help us improve our service.
                        <?php elseif (!empty($_SESSION['user_id']) && $_SESSION['role'] === 'admin'): ?>
                            Admin view: You can see customer feedback here. To reply, use the <a href="../feedback_management.php">Feedback Management</a> page.
                        <?php else: ?>
                            Share your thoughts and help us improve our service. Please log in to post feedback.
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Post Feedback</h5>
                            <?php if (!empty($_SESSION['customer_id'])): ?>
                                <form id="feedbackForm" class="d-flex flex-column gap-2">
                                    <select id="feedbackCategory" class="form-select" required>
                                        <option value="">Select Category</option>
                                        <option value="customer_support">Customer Support</option>
                                        <option value="service">Service</option>
                                        <option value="website">Website</option>
                                    </select>
                                    <textarea id="feedbackMessage" class="form-control" rows="4" placeholder="Write your feedback..." required></textarea>
                                    <div class="d-flex align-items-center gap-2">
                                        <button type="submit" class="btn btn-primary">Submit</button>
                                        <span id="feedbackStatus" class="text-muted small"></span>
                                    </div>
                                </form>
                            <?php elseif (!empty($_SESSION['user_id']) && $_SESSION['role'] === 'admin'): ?>
                                <div class="alert alert-info" role="alert">
                                    <i class="fas fa-info-circle me-2"></i>
                                    As an admin, you can view feedback here. To reply to feedback, please use the 
                                    <a href="../feedback_management.php" class="alert-link">Feedback Management</a> page.
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info" role="alert">
                                    <i class="fas fa-user me-2"></i>
                                    Please log in to your Member Portal to post feedback.
                                </div>
                                <a class="btn btn-primary" href="../auth/customer_login.php">
                                    <i class="fas fa-user me-1"></i> Log in to post
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Recent Feedback</h5>
                            
                            <!-- Category Tabs -->
                            <ul class="nav nav-tabs mb-3" id="categoryTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab" aria-controls="all" aria-selected="true">
                                        All <span class="badge bg-secondary ms-1" id="all-count">0</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="customer_support-tab" data-bs-toggle="tab" data-bs-target="#customer_support" type="button" role="tab" aria-controls="customer_support" aria-selected="false">
                                        Customer Support <span class="badge bg-secondary ms-1" id="customer_support-count">0</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="service-tab" data-bs-toggle="tab" data-bs-target="#service" type="button" role="tab" aria-controls="service" aria-selected="false">
                                        Service <span class="badge bg-secondary ms-1" id="service-count">0</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="website-tab" data-bs-toggle="tab" data-bs-target="#website" type="button" role="tab" aria-controls="website" aria-selected="false">
                                        Website <span class="badge bg-secondary ms-1" id="website-count">0</span>
                                    </button>
                                </li>
                            </ul>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="input-group" style="max-width: 300px;">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input id="searchBox" type="text" class="form-control" placeholder="Search feedback...">
                                </div>
                                <button id="refreshFeedback" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-sync-alt me-1"></i>Refresh
                                </button>
                            </div>
                            <div id="feedbackList" class="feedback-scroll d-flex flex-column gap-3"></div>
                            <div id="feedbackEmpty" class="text-muted text-center py-4" style="display:none;">No feedback yet. Be the first to share!</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Information -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <h3 class="fw-bold mb-4">Our Locations</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="contact-info mb-4">
                                <h5 class="fw-bold text-primary">Main Office</h5>
                                <p class="mb-1"><i class="fas fa-map-marker-alt me-2"></i>Socoteco II J. Catolico Avenue, Lagao, General Santos City</p>
                                <p class="mb-1"><i class="fas fa-phone me-2"></i>(083) 553-5848 to 50</p>
                                <p class="mb-0"><i class="fas fa-mobile-alt me-2"></i>09177205365 / 09124094971</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="fw-bold text-primary mb-3">Sub-Offices</h5>
                            <div class="row">
                                <div class="col-6">
                                    <p class="mb-1"><strong>Calumpang</strong><br>09639331803</p>
                                    <p class="mb-1"><strong>Polomolok</strong><br>09815059290</p>
                                    <p class="mb-1"><strong>Tupi</strong><br>09085663964</p>
                                </div>
                                <div class="col-6">
                                    <p class="mb-1"><strong>Alabel</strong><br>09977547974</p>
                                    <p class="mb-1"><strong>Malapatan</strong><br>09554488417</p>
                                    <p class="mb-1"><strong>Glan</strong><br>09752359732</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="contact-info">
                        <h5 class="fw-bold text-primary mb-3">Quick Actions</h5>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#billCalcModal">
                                <i class="fas fa-calculator me-2"></i>Bill Calculator
                            </button>
                            <a href="#" class="btn btn-outline-primary">
                                <i class="fas fa-file-alt me-2"></i>Download Forms
                            </a>
                            <a href="#" class="btn btn-outline-primary">
                                <i class="fas fa-question-circle me-2"></i>FAQ
                            </a>
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#outageReportModal">
                                <i class="fas fa-exclamation-triangle me-2"></i>Report Outage
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer" role="contentinfo">
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
            </div>
            <hr class="my-4">
            <div class="row">
                <div class="col-12 text-center">
                    
                </div>
            </div>
        </div>
    </footer>

    

    <!-- Priority Number Modal V2 -->
    <div class="modal fade" id="priorityModal" tabindex="-1" aria-labelledby="priorityModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="priorityModalLabel">
                        <i class="fas fa-ticket-alt me-2"></i>Priority Queue System V2
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Time Restrictions Check -->
                    <div id="time-restrictions" style="display: none;">
                        <div class="alert alert-warning text-center">
                            <h5><i class="fas fa-clock me-2"></i>Service Not Available</h5>
                            <p class="mb-0" id="time-restriction-message">Priority numbers can only be generated between 6:00 AM and 6:00 PM</p>
                        </div>
                    </div>
                    
                    <!-- Lunch Break Notice -->
                    <div id="lunch-break-notice" style="display: none;">
                        <div class="alert alert-info text-center">
                            <h5><i class="fas fa-utensils me-2"></i>Lunch Break</h5>
                            <p class="mb-0">Priority number generation is temporarily unavailable during lunch break (12:00 PM - 1:00 PM)</p>
                        </div>
                    </div>
                    
                    <!-- Queue Statistics -->
                    <div class="row mb-4" id="queue-stats">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white text-center">
                                <div class="card-body">
                                    <h5 id="payment-pending">0</h5>
                                    <small>Payment Queue</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-danger text-white text-center">
                                <div class="card-body">
                                    <h5 id="claims-pending">0</h5>
                                    <small>Claims Queue</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-info text-white text-center">
                                <div class="card-body">
                                    <h5 id="registration-pending">0</h5>
                                    <small>Registration Queue</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Status -->
                    <div id="customer-status">
                        <?php if (!empty($_SESSION['customer_id'])): ?>
                            <!-- Logged in customer -->
                            <div class="alert alert-info">
                                <h6><i class="fas fa-user me-2"></i>Welcome, <?php echo htmlspecialchars($_SESSION['customer_name']); ?>!</h6>
                                <p class="mb-0">Select a service category to get your priority number for today.</p>
                            </div>
                            
                            <!-- Category Selection -->
                            <div id="category-selection" style="display: none;">
                                <h6 class="mb-3"><i class="fas fa-list me-2"></i>Select Service Category</h6>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <div class="category-card card h-100 text-center p-3" data-category="payment">
                                            <div class="category-icon text-success">
                                                <i class="fas fa-credit-card fa-3x"></i>
                                            </div>
                                            <h5>Payment</h5>
                                            <p class="small text-muted">Bill payments and transactions</p>
                                            <div class="queue-info">
                                                <small class="text-muted">
                                                    Pending: <span id="payment-count">0</span><br>
                                                    Window 1
                                                </small>
                                            </div>
                                            <input type="radio" name="category" value="payment" class="d-none" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="category-card card h-100 text-center p-3" data-category="claims">
                                            <div class="category-icon text-danger">
                                                <i class="fas fa-file-alt fa-3x"></i>
                                            </div>
                                            <h5>Claims</h5>
                                            <p class="small text-muted">Service claims and complaints</p>
                                            <div class="queue-info">
                                                <small class="text-muted">
                                                    Pending: <span id="claims-count">0</span><br>
                                                    Window 2
                                                </small>
                                            </div>
                                            <input type="radio" name="category" value="claims" class="d-none" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="category-card card h-100 text-center p-3" data-category="registration">
                                            <div class="category-icon text-info">
                                                <i class="fas fa-user-plus fa-3x"></i>
                                            </div>
                                            <h5>Registration</h5>
                                            <p class="small text-muted">New customer registration</p>
                                            <div class="queue-info">
                                                <small class="text-muted">
                                                    Pending: <span id="registration-count">0</span><br>
                                                    Window 3
                                                </small>
                                            </div>
                                            <input type="radio" name="category" value="registration" class="d-none" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-center mt-4">
                                    <button type="button" class="btn btn-primary btn-lg px-5" id="generate-priority-btn">
                                        <i class="fas fa-ticket-alt me-2"></i>Generate Priority Number
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Existing Priority Number Display -->
                            <div id="existing-priority" style="display: none;">
                                <div class="alert alert-success">
                                    <h5><i class="fas fa-check-circle me-2"></i>You already have a priority number for today!</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Priority Number:</strong> <span class="badge bg-primary fs-5" id="existing-number">0</span></p>
                                            <p><strong>Category:</strong> <span id="existing-category">-</span></p>
                                            <p><strong>Window:</strong> <span id="existing-window">-</span></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Generated:</strong> <span id="existing-generated">-</span></p>
                                            <p><strong>Status:</strong> <span class="badge bg-warning" id="existing-status">-</span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        <?php else: ?>
                            <!-- Not logged in -->
                            <div class="alert alert-warning">
                                <h6><i class="fas fa-exclamation-triangle me-2"></i>Login Required</h6>
                                <p class="mb-3">You need to be logged in to get a priority number.</p>
                                <a href="../auth/customer_login.php" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login to Continue
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Service Information -->
                    <div class="mt-4 p-3 bg-light rounded">
                        <h6><i class="fas fa-info-circle me-2"></i>Service Information</h6>
                        <ul class="mb-0 small">
                            <li><strong>Service Hours:</strong> 6:00 AM - 6:00 PM</li>
                            <li><strong>Priority Calling:</strong> 7:00 AM - 6:00 PM</li>
                            <li><strong>Lunch Break:</strong> 12:00 PM - 1:00 PM</li>
                            <li><strong>Daily Capacity:</strong> 500 numbers per category</li>
                            <li><strong>Timer Interval:</strong> 5 minutes between calls</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-info" onclick="refreshQueueStatus()">
                        <i class="fas fa-sync-alt me-2"></i>Refresh Status
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bill Calculator Modal -->
    <div class="modal fade" id="billCalcModal" tabindex="-1" aria-labelledby="billCalcLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="billCalcLabel"><i class="fas fa-calculator me-2"></i>Bill Calculator</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Consumption (kWh)</label>
                        <input type="number" class="form-control" id="calcKwh" min="0" step="0.01" placeholder="e.g., 150">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rate per kWh (₱)</label>
                        <input type="number" class="form-control" id="calcRate" min="0" step="0.0001" value="<?php echo (float) getSystemSetting('generation_rate', '4.5000'); ?>">
                        <div class="form-text">Default uses your current generation rate.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">VAT (%)</label>
                        <input type="number" class="form-control" id="calcVat" min="0" step="0.01" value="<?php echo (float) getSystemSetting('vat_rate', '12'); ?>">
                    </div>
                    <div class="border rounded p-3">
                        <div class="d-flex justify-content-between"><span>Energy Charge:</span><strong id="calcEnergy">₱0.00</strong></div>
                        <div class="d-flex justify-content-between"><span>VAT:</span><strong id="calcVatAmt">₱0.00</strong></div>
                        <hr>
                        <div class="d-flex justify-content-between"><span>Total:</span><strong id="calcTotal" class="text-primary">₱0.00</strong></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="calcCompute">Compute</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Outage Report Modal -->
    <div class="modal fade" id="outageReportModal" tabindex="-1" aria-labelledby="outageReportLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="outageReportLabel"><i class="fas fa-exclamation-triangle me-2"></i>Report Outage</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Location or Account Number</label>
                        <input type="text" class="form-control" id="outageLocation" placeholder="e.g., Account # or address">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Details</label>
                        <textarea class="form-control" id="outageDetails" rows="3" placeholder="Brief description of the problem"></textarea>
                    </div>
                    <div class="form-text">We’ll route this to our support team.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="outageSubmit">Submit</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    // Back to top
    (function(){
        const btn = document.getElementById('backToTop');
        if (!btn) return;
        window.addEventListener('scroll', function(){
            if (window.scrollY > 300) btn.classList.add('show'); else btn.classList.remove('show');
        });
        btn.addEventListener('click', function(){ window.scrollTo({ top:0, behavior:'smooth' }); });
    })();

    // Bill calculator
    (function(){
        const fmt = (n) => '₱' + (Number(n)||0).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2});
        const compute = () => {
            const kwh = Number(document.getElementById('calcKwh').value || 0);
            const rate = Number(document.getElementById('calcRate').value || 0);
            const vatp = Number(document.getElementById('calcVat').value || 0);
            const energy = kwh * rate;
            const vatAmt = energy * (vatp/100);
            const total = energy + vatAmt;
            document.getElementById('calcEnergy').textContent = fmt(energy);
            document.getElementById('calcVatAmt').textContent = fmt(vatAmt);
            document.getElementById('calcTotal').textContent = fmt(total);
        };
        const btn = document.getElementById('calcCompute');
        if (btn) btn.addEventListener('click', compute);
    })();

    // Outage report submit
    (function(){
        const submit = document.getElementById('outageSubmit');
        if (!submit) return;
        submit.addEventListener('click', function(){
            const loc = (document.getElementById('outageLocation').value || '').trim();
            const det = (document.getElementById('outageDetails').value || '').trim();
            if (!loc || !det) {
                Swal.fire('Incomplete', 'Please provide your location/account and details.', 'warning');
                return;
            }
            // For now, just show confirmation; can be wired to an AJAX endpoint later
            Swal.fire('Submitted', 'Your outage report has been sent. Our team will review it shortly.', 'success');
            const modalEl = document.getElementById('outageReportModal');
            const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            modal.hide();
            document.getElementById('outageLocation').value = '';
            document.getElementById('outageDetails').value = '';
        });
    })();
    // Cookie banner removed per request

    (function(){
        const listEl = document.getElementById('feedbackList');
        const emptyEl = document.getElementById('feedbackEmpty');
        const refreshBtn = document.getElementById('refreshFeedback');
        const form = document.getElementById('feedbackForm');
        const categoryInput = document.getElementById('feedbackCategory');
        const messageInput = document.getElementById('feedbackMessage');
        const statusEl = document.getElementById('feedbackStatus');
        const searchBox = document.getElementById('searchBox');
        const categoryTabs = document.getElementById('categoryTabs');
        const endpoint = '../ajax/feedback.php';
        let lastId = 0;
        let lastReplyId = 0;
        let currentCategory = '';
        let searchQuery = '';
        const rendered = new Set();
        const replyRendered = new Map(); // feedback_id -> Set(reply_id)

        function formatTime(ts){
            try { return new Date(ts.replace(' ', 'T')).toLocaleString(); } catch(e){ return ts || ''; }
        }

        function createItemEl(f){
            const wrap = document.createElement('div');
            wrap.className = 'border rounded p-3';

            const nameRow = document.createElement('div');
            nameRow.className = 'd-flex justify-content-between align-items-center mb-1';
            
            const left = document.createElement('div');
            left.className = 'd-flex align-items-center gap-2';
            
            const name = document.createElement('strong');
            name.textContent = (f.customer_name || 'Member');
            
            // Category badge
            const categoryBadge = document.createElement('span');
            categoryBadge.className = 'badge bg-info';
            const categoryText = f.category ? f.category.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'General';
            categoryBadge.textContent = categoryText;
            
            left.appendChild(name);
            left.appendChild(categoryBadge);
            
            const time = document.createElement('small');
            time.className = 'text-muted';
            time.textContent = formatTime(f.created_at);
            
            nameRow.appendChild(left);
            nameRow.appendChild(time);

            const msg = document.createElement('div');
            msg.className = 'text-dark';
            msg.textContent = f.message || '';

            // Like / This helps button
            const actions = document.createElement('div');
            actions.className = 'd-flex align-items-center gap-2 mt-2';
            const likeBtn = document.createElement('button');
            likeBtn.type = 'button';
            likeBtn.className = 'btn btn-sm ' + (f.liked_by_me ? 'btn-success' : 'btn-outline-success');
            likeBtn.innerHTML = `<i class="fas fa-thumbs-up me-1"></i>${f.liked_by_me ? 'Helpful' : 'This helps'}`;
            const likeCount = document.createElement('span');
            likeCount.className = 'small text-secondary';
            likeCount.textContent = (f.like_count || 0) + ' found this helpful';

            likeBtn.addEventListener('click', function(){
                const body = new URLSearchParams({ action: 'toggle_like', feedback_id: f.feedback_id });
                fetch(endpoint, { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body })
                    .then(r=>r.json())
                    .then(data=>{
                        if (!data || !data.ok) return;
                        if (data.liked) {
                            likeBtn.classList.remove('btn-outline-success');
                            likeBtn.classList.add('btn-success');
                            likeBtn.innerHTML = '<i class="fas fa-thumbs-up me-1"></i>Helpful';
                        } else {
                            likeBtn.classList.add('btn-outline-success');
                            likeBtn.classList.remove('btn-success');
                            likeBtn.innerHTML = '<i class="fas fa-thumbs-up me-1"></i>This helps';
                        }
                        likeCount.textContent = (data.like_count || 0) + ' found this helpful';
                    })
                    .catch(()=>{});
            });

            wrap.appendChild(nameRow);
            wrap.appendChild(msg);
            actions.appendChild(likeBtn);
            actions.appendChild(likeCount);
            wrap.appendChild(actions);

            // Replies container
            const replies = document.createElement('div');
            replies.className = 'mt-3';
            replies.dataset.feedbackId = f.feedback_id;
            // Replies UI: collapsed if more than 1
            const repliesList = document.createElement('div');
            repliesList.className = 'd-flex flex-column gap-2';
            replies.appendChild(repliesList);
            const toggleWrap = document.createElement('div');
            toggleWrap.className = 'mt-2';
            const toggleBtn = document.createElement('button');
            toggleBtn.type = 'button';
            toggleBtn.className = 'btn btn-sm btn-outline-secondary';
            toggleBtn.textContent = 'Show replies';
            toggleWrap.appendChild(toggleBtn);
            replies.appendChild(toggleWrap);

            // State for collapse
            let repliesCount = 0;
            let expanded = false;
            toggleBtn.style.display = 'none';

            // Expose helpers on container
            repliesList.updateCount = function(){
                repliesCount = repliesList.children.length;
                if (repliesCount > 1) {
                    toggleBtn.style.display = '';
                    Array.from(repliesList.children).forEach((child, idx)=>{
                        child.style.display = expanded || idx === 0 ? '' : 'none';
                    });
                    toggleBtn.textContent = expanded ? 'Hide replies' : `Show ${repliesCount-1} more reply${repliesCount-1>1?'ies':'y'}`;
                } else {
                    toggleBtn.style.display = 'none';
                }
            };
            toggleBtn.addEventListener('click', function(){
                expanded = !expanded;
                repliesList.updateCount();
            });

            wrap.appendChild(replies);


            return wrap;
        }

        function createReplyEl(r){
            const box = document.createElement('div');
            box.className = 'reply-box border rounded p-2';
            const header = document.createElement('div');
            header.className = 'd-flex justify-content-between align-items-center mb-1';
            const who = document.createElement('strong');
            who.textContent = (r.admin_name || 'Admin');
            const time = document.createElement('small');
            time.className = 'text-muted';
            time.textContent = formatTime(r.created_at);
            header.appendChild(who);
            header.appendChild(time);

            const body = document.createElement('div');
            body.className = 'text-dark';
            body.textContent = r.message || '';

            box.appendChild(header);
            box.appendChild(body);
            return box;
        }

        function updateEmptyState(){
            emptyEl.style.display = listEl.children.length ? 'none' : 'block';
        }

        function renderList(items){
            if (!Array.isArray(items) || !items.length) { updateEmptyState(); return; }
            // Ensure items are in ascending id so newest ends up on top when inserting at firstChild
            items.sort((a,b) => (parseInt(a.feedback_id) - parseInt(b.feedback_id)));
            items.forEach(f => {
                const id = parseInt(f.feedback_id);
                if (rendered.has(id)) return; // avoid duplicates
                lastId = Math.max(lastId, id);
                rendered.add(id);
                listEl.insertBefore(createItemEl(f), listEl.firstChild);
            });
            updateEmptyState();
        }

        function renderRepliesMap(map){
            if (!map) return;
            Object.keys(map).forEach(fid => {
                const replies = map[fid] || [];
                renderReplies(replies);
            });
        }

        function renderReplies(replies){
            if (!Array.isArray(replies) || !replies.length) return;
            replies.forEach(r => {
                const fid = parseInt(r.feedback_id);
                const rid = parseInt(r.reply_id);
                let set = replyRendered.get(fid);
                if (!set) { set = new Set(); replyRendered.set(fid, set); }
                if (set.has(rid)) return;
                set.add(rid);
                lastReplyId = Math.max(lastReplyId, rid);
                const container = listEl.querySelector(`[data-feedback-id="${fid}"]`);
                if (container) {
                    const list = container.querySelector('div.d-flex.flex-column.gap-2');
                    const el = createReplyEl(r);
                    if (list) {
                        list.appendChild(el);
                        if (typeof list.updateCount === 'function') list.updateCount();
                    } else {
                        container.appendChild(el);
                    }
                }
            });
        }

        function fetchList(since = 0){
            let url = endpoint + `?action=list&since_id=${since}&since_reply_id=${lastReplyId}`;
            if (searchQuery) url += `&q=${encodeURIComponent(searchQuery)}`;
            if (currentCategory) url += `&category=${encodeURIComponent(currentCategory)}`;
            return fetch(url).then(r => r.json()).then(data => {
                renderList(data.feedback || []);
                renderRepliesMap(data.replies || {});
                if (Array.isArray(data.replies_new)) {
                    renderReplies(data.replies_new);
                }
            }).catch(() => {});
        }

        function updateCategoryCounts() {
            fetch(endpoint + '?action=category_counts')
                .then(r => r.json())
                .then(data => {
                    if (data && data.counts) {
                        const counts = data.counts;
                        document.getElementById('customer_support-count').textContent = counts.customer_support || 0;
                        document.getElementById('service-count').textContent = counts.service || 0;
                        document.getElementById('website-count').textContent = counts.website || 0;
                        document.getElementById('all-count').textContent = (counts.customer_support || 0) + (counts.service || 0) + (counts.website || 0);
                    }
                })
                .catch(() => {});
        }

        function switchCategory(category) {
            currentCategory = category;
            listEl.innerHTML = '';
            rendered.clear();
            replyRendered.clear();
            lastId = 0;
            lastReplyId = 0;
            fetchList(0);
        }

        // Category tab event listeners
        if (categoryTabs) {
            categoryTabs.addEventListener('click', function(e) {
                const tab = e.target.closest('button[data-bs-target]');
                if (!tab) return;
                
                const target = tab.getAttribute('data-bs-target');
                if (target === '#all') {
                    switchCategory('');
                } else if (target === '#customer_support') {
                    switchCategory('customer_support');
                } else if (target === '#service') {
                    switchCategory('service');
                } else if (target === '#website') {
                    switchCategory('website');
                }
            });
        }

        // Search functionality
        if (searchBox) {
            let searchTimeout;
            searchBox.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    searchQuery = searchBox.value.trim();
                    listEl.innerHTML = '';
                    rendered.clear();
                    replyRendered.clear();
                    lastId = 0;
                    lastReplyId = 0;
                    fetchList(0);
                }, 300);
            });
        }

        if (refreshBtn) refreshBtn.addEventListener('click', function(e){
            e.preventDefault();
            // Full refresh: clear list and re-fetch from scratch
            if (listEl) listEl.innerHTML = '';
            rendered.clear();
            replyRendered.clear();
            lastId = 0;
            lastReplyId = 0;
            updateCategoryCounts();
            fetchList(0);
        });

        if (form && messageInput && categoryInput) {
            form.addEventListener('submit', function(e){
                e.preventDefault();
                const category = (categoryInput.value || '').trim();
                const message = (messageInput.value || '').trim();
                if (!category) {
                    statusEl.textContent = 'Please select a category';
                    return;
                }
                if (!message) return;
                statusEl.textContent = 'Sending...';
                const body = new URLSearchParams({ action: 'create', category: category, message: message });
                fetch(endpoint, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body })
                    .then(async r => {
                        const text = await r.text();
                        try { return JSON.parse(text); } catch (e) { throw new Error(text || 'Invalid response'); }
                    })
                    .then(data => {
                        if (data && data.ok) {
                            // If server returned the item, render it; otherwise, fetch incrementally
                            if (data.feedback && data.feedback.feedback_id) {
                                const id = parseInt(data.feedback.feedback_id);
                                if (!rendered.has(id)) {
                                    rendered.add(id);
                                    lastId = Math.max(lastId, id);
                                    listEl.insertBefore(createItemEl(data.feedback), listEl.firstChild);
                                }
                            } else {
                                // fallback to fetch new items since lastId
                                fetchList(lastId);
                            }
                            categoryInput.value = '';
                            messageInput.value = '';
                            statusEl.textContent = 'Posted';
                            setTimeout(() => statusEl.textContent = '', 1500);
                            updateEmptyState();
                        } else {
                            statusEl.textContent = (data && data.error) ? data.error : 'Failed to post';
                        }
                    })
                    .catch((err) => { statusEl.textContent = err && err.message ? err.message : 'Network error'; })
            });
        }

        // Initial load and polling
        updateCategoryCounts();
        fetchList(0);
        setInterval(function(){
            // Always poll for new items; since_id ensures only new ones are returned
            updateCategoryCounts();
            fetchList(lastId || 0);
        }, 2000);
    })();

    // Priority Number System V2 JavaScript
    $(document).ready(function() {
        // Fix aria-hidden issue - ensure Bootstrap manages it correctly
        $('#priorityModal').on('show.bs.modal', function() {
            $(this).removeAttr('aria-hidden');
        });
        
        $('#priorityModal').on('hide.bs.modal', function() {
            $(this).attr('aria-hidden', 'true');
        });
        
        // Load initial queue status when modal is shown
        $('#priorityModal').on('shown.bs.modal', function() {
            refreshQueueStatus();
            checkExistingPriority();
            checkTimeRestrictions();
        });

        // Category selection
        $('.category-card').on('click', function() {
            // Remove selected class from all cards
            $('.category-card').removeClass('selected');
            
            // Add selected class to clicked card
            $(this).addClass('selected');
            
            // Check the radio button
            $(this).find('input[type="radio"]').prop('checked', true);
        });

        // Handle priority number generation
        $('#generate-priority-btn').on('click', function() {
            const selectedCategory = $('input[name="category"]:checked');
            if (!selectedCategory.length) {
                Swal.fire('Error', 'Please select a service category', 'error');
                return;
            }
            
            const category = selectedCategory.val();
            const customerId = '<?php echo $_SESSION['customer_id'] ?? ''; ?>';
            
            if (!customerId) {
                Swal.fire('Error', 'Please login first', 'error');
                return;
            }
            
            $.ajax({
                url: '../ajax/priority_user_v2.php',
                type: 'POST',
                data: {
                    action: 'generate_priority',
                    category: category,
                    customer_id: customerId
                },
                dataType: 'json',
                beforeSend: function() {
                    $('#generate-priority-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Generating...');
                },
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        Swal.fire({
                            title: 'Success!',
                            html: `
                                <div class="text-center">
                                    <h2 class="text-primary mb-3">${response.priority_number}</h2>
                                    <p><strong>Category:</strong> ${response.category}</p>
                                    <p><strong>Service Date:</strong> ${new Date(response.service_date).toLocaleDateString()}</p>
                                    <p><strong>Customer:</strong> ${response.customer_name || 'You'}</p>
                                </div>
                            `,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            // Hide category selection and show existing priority
                            $('#category-selection').hide();
                            $('#existing-priority').show();
                            
                            // Update existing priority display
                            $('#existing-number').text(response.priority_number);
                            $('#existing-category').text(response.category);
                            $('#existing-window').text(getWindowNumber(response.category));
                            $('#existing-status').text('Pending');
                            $('#existing-generated').text(new Date().toLocaleString());
                            
                            refreshQueueStatus();
                        });
                    } else {
                        Swal.fire('Error', response.error || 'An error occurred', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error, xhr.responseText);
                    let errorMsg = 'An error occurred while generating priority number';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.error) {
                            errorMsg = response.error;
                        }
                    } catch(e) {
                        // Use default error message
                    }
                    Swal.fire('Error', errorMsg, 'error');
                },
                complete: function() {
                    $('#generate-priority-btn').prop('disabled', false).html('<i class="fas fa-ticket-alt me-2"></i>Generate Priority Number');
                }
            });
        });
    });

    // Refresh queue status function
    function refreshQueueStatus() {
        $.ajax({
            url: '../ajax/priority_user_v2.php',
            type: 'GET',
            data: { action: 'get_queue_stats' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#payment-pending').text(response.stats.payment.pending);
                    $('#claims-pending').text(response.stats.claims.pending);
                    $('#registration-pending').text(response.stats.registration.pending);
                    $('#payment-count').text(response.stats.payment.pending);
                    $('#claims-count').text(response.stats.claims.pending);
                    $('#registration-count').text(response.stats.registration.pending);
                }
            },
            error: function() {
                console.log('Error updating queue status');
            }
        });
    }

    // Check time restrictions
    // TEMPORARILY DISABLED FOR TESTING - Remove comments to re-enable
    function checkTimeRestrictions() {
        // Always show the category selection for testing
        $('#time-restrictions').hide();
        $('#lunch-break-notice').hide();
        $('#queue-stats').show();
        $('#category-selection').show();
        
        /*
        const now = new Date();
        const currentHour = now.getHours();
        const currentMinute = now.getMinutes();
        const currentTime = currentHour + (currentMinute / 60);
        
        const lunchStart = 12; // 12:00 PM
        const lunchEnd = 13;   // 1:00 PM
        const serviceStart = 6; // 6:00 AM
        const serviceEnd = 18;  // 6:00 PM
        
        if (currentTime >= lunchStart && currentTime < lunchEnd) {
            // Lunch break
            $('#lunch-break-notice').show();
            $('#time-restrictions').hide();
            $('#queue-stats').hide();
            $('#category-selection').hide();
        } else if (currentTime < serviceStart || currentTime >= serviceEnd) {
            // Outside service hours
            $('#time-restrictions').show();
            $('#lunch-break-notice').hide();
            $('#queue-stats').hide();
            $('#category-selection').hide();
            $('#time-restriction-message').text('Priority numbers can only be generated between 6:00 AM and 6:00 PM');
        } else {
            // Service hours
            $('#time-restrictions').hide();
            $('#lunch-break-notice').hide();
            $('#queue-stats').show();
            $('#category-selection').show();
        }
        */
    }
    
    // Get window number for category
    function getWindowNumber(category) {
        const windows = {
            'payment': 'Window 1',
            'claims': 'Window 2',
            'registration': 'Window 3'
        };
        return windows[category] || 'Unknown';
    }

    // Check for existing priority number
    function checkExistingPriority() {
        <?php if (!empty($_SESSION['customer_id'])): ?>
        $.ajax({
            url: '../ajax/priority_user_v2.php',
            type: 'GET',
            data: { 
                action: 'check_existing_today',
                customer_id: <?php echo $_SESSION['customer_id']; ?>
            },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.has_existing) {
                    var latest = response.data;
                    if (latest.status === 'pending') {
                        // Show existing priority
                        $('#priority-form-section').hide();
                        $('#existing-priority').show();
                        
                        $('#existing-number').text(latest.priority_number);
                        $('#existing-date').text(new Date(latest.service_date).toLocaleDateString());
                        $('#existing-status').text(latest.status.charAt(0).toUpperCase() + latest.status.slice(1));
                        $('#existing-generated').text(new Date(latest.generated_at).toLocaleString());
                        
                        // Calculate estimated wait
                        var dayNumber = Math.ceil(latest.priority_number / 1000);
                        var positionInDay = ((latest.priority_number - 1) % 1000) + 1;
                        $('#existing-wait').text(`Day ${dayNumber}, Position ${positionInDay}`);
                    }
                }
            },
            error: function() {
                console.log('Error checking existing priority');
            }
        });
        <?php endif; ?>
    }
    </script>

    <!-- Bill Inquiry Modal -->
    <div class="modal fade" id="billInquiryModal" tabindex="-1" aria-labelledby="billInquiryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="billInquiryModalLabel">
                        <i class="fas fa-file-invoice-dollar me-2"></i>Bill Inquiry
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Loading State -->
                    <div id="billLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3">Loading your bills...</p>
                    </div>
                    
                    <!-- Bills List -->
                    <div id="billsList" style="display: none;">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6 class="mb-0">Recent Bills</h6>
                                <small class="text-muted">Click on a bill to view details</small>
                            </div>
                            <div class="col-md-6 text-end">
                                <button class="btn btn-outline-primary btn-sm" onclick="refreshBills()">
                                    <i class="fas fa-sync-alt me-1"></i>Refresh
                                </button>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Bill Date</th>
                                        <th>Due Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="billsTableBody">
                                    <!-- Skeleton rows -->
                                    <tr class="skeleton" style="height:48px;"><td colspan="5"></td></tr>
                                    <tr class="skeleton" style="height:48px;"><td colspan="5"></td></tr>
                                    <tr class="skeleton" style="height:48px;"><td colspan="5"></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Bill Details -->
                    <div id="billDetails" style="display: none;">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <button class="btn btn-outline-secondary" onclick="showBillsList()">
                                    <i class="fas fa-arrow-left me-1"></i>Back to Bills
                                </button>
                            </div>
                            <div class="col-md-6 text-end">
                                <button class="btn btn-success" onclick="downloadBill()">
                                    <i class="fas fa-download me-1"></i>Download PDF
                                </button>
                            </div>
                        </div>
                        
                        <div id="billDetailsContent">
                            <!-- Bill details will be loaded here -->
                        </div>
                    </div>
                    
                    <!-- Error State -->
                    <div id="billError" style="display: none;" class="text-center py-5">
                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                        <h5>Unable to Load Bills</h5>
                        <p class="text-muted">There was an error loading your bills. Please try again.</p>
                        <button class="btn btn-primary" onclick="loadBills()">
                            <i class="fas fa-retry me-1"></i>Try Again
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    let currentBillId = null;
    const isCustomerLoggedIn = <?php echo isset($_SESSION['customer_id']) ? 'true' : 'false'; ?>;
    
    // Load bills when modal opens
    $('#billInquiryModal').on('show.bs.modal', function () {
        if (!isCustomerLoggedIn) {
            requireLoginError();
            return;
        }
        loadBills();
    });
    
    function loadBills() {
        showLoading();
        
        $.ajax({
            url: '<?php echo url('ajax/bill_inquiry.php'); ?>',
            method: 'GET',
            data: { action: 'get_bills' },
            dataType: 'json',
            success: function(response) {
                if (response && response.success) {
                    displayBills(response.bills);
                } else {
                    showError('Unable to load your bills right now.');
                }
            },
            error: function(xhr) {
                if (xhr && xhr.status === 401) {
                    requireLoginError();
                } else {
                    showError('A network error occurred while loading bills.');
                }
            }
        });
    }
    
    function displayBills(bills) {
        const tbody = $('#billsTableBody');
        tbody.empty();
        
        if (bills.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="5" class="text-center py-4">
                        <i class="fas fa-inbox fa-2x text-muted mb-3"></i>
                        <p class="text-muted">No bills found</p>
                    </td>
                </tr>
            `);
        } else {
            bills.forEach(function(bill) {
                const billDate = `${new Date(bill.billing_period_start).toLocaleDateString()} - ${new Date(bill.billing_period_end).toLocaleDateString()}`;
                const dueDate = new Date(bill.due_date).toLocaleDateString();
                const amount = parseFloat(bill.total_amount).toLocaleString('en-PH', {
                    style: 'currency',
                    currency: 'PHP'
                });
                
                let statusBadge = '';
                if (bill.status === 'paid') {
                    statusBadge = '<span class="badge bg-success">Paid</span>';
                } else if (new Date(bill.due_date) < new Date()) {
                    statusBadge = '<span class="badge bg-danger">Overdue</span>';
                } else {
                    statusBadge = '<span class="badge bg-warning">Pending</span>';
                }
                
                tbody.append(`
                    <tr style="cursor: pointer;" onclick="viewBillDetails(${bill.bill_id})">
                        <td>${billDate}</td>
                        <td>${dueDate}</td>
                        <td>${amount}</td>
                        <td>${statusBadge}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation(); viewBillDetails(${bill.bill_id})">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });
        }
        
        showBillsList();
    }
    
    function viewBillDetails(billId) {
        currentBillId = billId;
        showLoading();
        
        $.ajax({
            url: '<?php echo url('ajax/bill_inquiry.php'); ?>',
            method: 'GET',
            data: { action: 'get_bill_details', bill_id: billId },
            dataType: 'json',
            success: function(response) {
                if (response && response.success) {
                    displayBillDetails(response.bill, response.payments);
                } else {
                    showError('Unable to load bill details.');
                }
            },
            error: function(xhr) {
                if (xhr && xhr.status === 401) {
                    requireLoginError();
                } else {
                    showError('A network error occurred while loading the bill.');
                }
            }
        });
    }
    
    function displayBillDetails(bill, payments) {
        const billDate = `${new Date(bill.billing_period_start).toLocaleDateString()} - ${new Date(bill.billing_period_end).toLocaleDateString()}`;
        const dueDate = new Date(bill.due_date).toLocaleDateString();
        const amount = parseFloat(bill.total_amount).toLocaleString('en-PH', {
            style: 'currency',
            currency: 'PHP'
        });
        const vat = parseFloat(bill.vat || (bill.total_amount * 0.12)).toLocaleString('en-PH', {
            style: 'currency',
            currency: 'PHP'
        });
        const total = parseFloat(bill.total_amount).toLocaleString('en-PH', {
            style: 'currency',
            currency: 'PHP'
        });
        
        let statusBadge = '';
        if (bill.status === 'paid') {
            statusBadge = '<span class="badge bg-success">Paid</span>';
        } else if (new Date(bill.due_date) < new Date()) {
            statusBadge = '<span class="badge bg-danger">Overdue</span>';
        } else {
            statusBadge = '<span class="badge bg-warning">Pending</span>';
        }
        
        const content = `
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-user me-2"></i>Customer Information</h6>
                        </div>
                        <div class="card-body">
                            <p><strong>Name:</strong> ${bill.first_name} ${bill.last_name}</p>
                            <p><strong>Account Number:</strong> ${bill.account_number}</p>
                            <p><strong>Address:</strong> ${bill.address}</p>
                            <p><strong>Contact:</strong> ${bill.contact_number}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Bill Information</h6>
                        </div>
                        <div class="card-body">
                            <p><strong>Bill Number:</strong> ${bill.bill_number}</p>
                            <p><strong>Bill Date:</strong> ${billDate}</p>
                            <p><strong>Due Date:</strong> ${dueDate}</p>
                            <p><strong>Status:</strong> ${statusBadge}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-tachometer-alt me-2"></i>Meter Reading</h6>
                        </div>
                        <div class="card-body">
                            <p><strong>Reading Date:</strong> ${bill.reading_date ? new Date(bill.reading_date).toLocaleDateString() : 'N/A'}</p>
                            <p><strong>Previous Reading:</strong> ${bill.previous_reading || 'N/A'} kWh</p>
                            <p><strong>Current Reading:</strong> ${bill.current_reading || 'N/A'} kWh</p>
                            <p><strong>Consumption:</strong> ${bill.consumption || 'N/A'} kWh</p>
                            <p><strong>Reader:</strong> ${bill.reader_name || 'N/A'}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-calculator me-2"></i>Billing Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <span>Energy Consumption:</span>
                                <span>${amount}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>VAT (12%):</span>
                                <span>${vat}</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold">
                                <span>Total Amount Due:</span>
                                <span class="text-primary">${total}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            ${payments.length > 0 ? `
            <div class="row mt-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-credit-card me-2"></i>Payment History</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Payment Date</th>
                                            <th>Amount</th>
                                            <th>Method</th>
                                            <th>Reference</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${payments.map(payment => `
                                            <tr>
                                                <td>${new Date(payment.payment_date).toLocaleDateString()}</td>
                                                <td>${parseFloat(payment.amount).toLocaleString('en-PH', {style: 'currency', currency: 'PHP'})}</td>
                                                <td>${payment.payment_method}</td>
                                                <td>${payment.reference_number || 'N/A'}</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            ` : ''}
        `;
        
        $('#billDetailsContent').html(content);
        showBillDetails();
    }
    
    function downloadBill() {
        if (!currentBillId) return;
        
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Generating...';
        btn.disabled = true;
        
        $.ajax({
            url: '<?php echo url('ajax/bill_inquiry.php'); ?>',
            method: 'GET',
            data: { action: 'generate_pdf', bill_id: currentBillId },
            dataType: 'json'
        }).done(function(response) {
            if (response && response.success) {
                const link = document.createElement('a');
                link.href = response.pdf_url;
                link.download = response.filename;
                link.click();
            } else {
                alert('Failed to generate PDF: ' + (response && response.error ? response.error : 'Unknown error'));
            }
        }).fail(function(xhr) {
            if (xhr && xhr.status === 401) {
                requireLoginError();
            } else {
                alert('Failed to generate PDF. Please try again.');
            }
        }).always(function() {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }
    
    function refreshBills() {
        loadBills();
    }
    
    function showBillsList() {
        $('#billLoading').hide();
        $('#billError').hide();
        $('#billDetails').hide();
        $('#billsList').show();
    }
    
    function showBillDetails() {
        $('#billLoading').hide();
        $('#billError').hide();
        $('#billsList').hide();
        $('#billDetails').show();
    }
    
    function showLoading() {
        $('#billLoading').show();
        $('#billError').hide();
        $('#billsList').hide();
        $('#billDetails').hide();
    }
    
    function showError(msg) {
        $('#billLoading').hide();
        if (msg) {
            $('#billError').html(`
                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                <h5>Unable to Load Bills</h5>
                <p class="text-muted">${msg}</p>
                <button class="btn btn-primary" onclick="loadBills()">
                    <i class=\"fas fa-sync-alt me-1\"></i>Try Again
                </button>
            `);
        }
        $('#billError').show();
        $('#billsList').hide();
        $('#billDetails').hide();
    }

    function requireLoginError() {
        $('#billLoading').hide();
        $('#billError').html(`
            <i class="fas fa-user-lock fa-3x text-warning mb-3"></i>
            <h5>Login Required</h5>
            <p class="text-muted">Please log in to view your bills.</p>
            <a class="btn btn-primary" href="<?php echo url('auth/customer_login.php'); ?>">
                <i class="fas fa-sign-in-alt me-1"></i>Go to Login
            </a>
        `).show();
        $('#billsList').hide();
        $('#billDetails').hide();
    }
    </script>

<?php include __DIR__ . '/../includes/user_chat_fab.php'; ?>
</body>
</html>