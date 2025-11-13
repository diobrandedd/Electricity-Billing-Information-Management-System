<?php
/**
 * Kolas.ai Setup Wizard
 * Helps configure Kolas.ai integration for content moderation
 */

$page_title = 'Kolas.ai Setup';
require_once 'includes/header.php';

$step = $_GET['step'] ?? 1;
$error = '';
$success = '';

// Handle form submissions
if ($_POST) {
    if ($step == 2) {
        // Save configuration
        $config = [
            'client_id' => $_POST['client_id'] ?? '',
            'client_secret' => $_POST['client_secret'] ?? '',
            'project_id' => $_POST['project_id'] ?? '',
            'base_url' => 'https://api.kolas.ai',
            'enabled' => true,
            'timeout' => 10
        ];
        
        $config_content = "<?php\nreturn " . var_export($config, true) . ";\n?>";
        
        if (file_put_contents('config/kolas_config.php', $config_content)) {
            $success = 'Kolas.ai configuration saved successfully!';
            $step = 3;
        } else {
            $error = 'Failed to save configuration. Please check file permissions.';
        }
    }
}

?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-robot me-2"></i>Kolas.ai Setup Wizard
                    </h5>
                </div>
                <div class="card-body">
                    
                    <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Progress Steps -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="progress-steps">
                                <div class="step <?php echo $step >= 1 ? 'active' : ''; ?>">
                                    <span class="step-number">1</span>
                                    <span class="step-title">Introduction</span>
                                </div>
                                <div class="step <?php echo $step >= 2 ? 'active' : ''; ?>">
                                    <span class="step-number">2</span>
                                    <span class="step-title">Configuration</span>
                                </div>
                                <div class="step <?php echo $step >= 3 ? 'active' : ''; ?>">
                                    <span class="step-number">3</span>
                                    <span class="step-title">Complete</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($step == 1): ?>
                    <!-- Step 1: Introduction -->
                    <div class="text-center">
                        <i class="fas fa-robot fa-4x text-primary mb-4"></i>
                        <h3>Welcome to Kolas.ai Integration</h3>
                        <p class="lead">Kolas.ai provides advanced AI-powered content moderation for your chat system.</p>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h5><i class="fas fa-shield-alt text-success me-2"></i>Benefits</h5>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Advanced AI detection</li>
                                <li><i class="fas fa-check text-success me-2"></i>Context-aware analysis</li>
                                <li><i class="fas fa-check text-success me-2"></i>Multiple language support</li>
                                <li><i class="fas fa-check text-success me-2"></i>Real-time processing</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="fas fa-cogs text-info me-2"></i>What You Need</h5>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-info-circle text-info me-2"></i>Kolas.ai account</li>
                                <li><i class="fas fa-info-circle text-info me-2"></i>Client ID & Secret</li>
                                <li><i class="fas fa-info-circle text-info me-2"></i>Project ID</li>
                                <li><i class="fas fa-info-circle text-info me-2"></i>Internet connection</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="?step=2" class="btn btn-primary btn-lg">
                            <i class="fas fa-arrow-right me-2"></i>Get Started
                        </a>
                    </div>
                    
                    <?php elseif ($step == 2): ?>
                    <!-- Step 2: Configuration -->
                    <form method="POST">
                        <h4><i class="fas fa-cog me-2"></i>Configure Kolas.ai</h4>
                        <p>Enter your Kolas.ai credentials below:</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="client_id" class="form-label">Client ID</label>
                                    <input type="text" class="form-control" id="client_id" name="client_id" 
                                           placeholder="Your Kolas.ai Client ID" required>
                                    <div class="form-text">Found in your Kolas.ai account settings</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="client_secret" class="form-label">Client Secret</label>
                                    <input type="password" class="form-control" id="client_secret" name="client_secret" 
                                           placeholder="Your Kolas.ai Client Secret" required>
                                    <div class="form-text">Keep this secure and private</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="project_id" class="form-label">Project ID</label>
                            <input type="text" class="form-control" id="project_id" name="project_id" 
                                   placeholder="Your Kolas.ai Project ID" required>
                            <div class="form-text">Create a project for message classification in Kolas.ai</div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Need help?</strong> 
                            <a href="https://kolas.ai/documentation" target="_blank" class="alert-link">
                                View Kolas.ai Documentation
                            </a>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Save Configuration
                            </button>
                        </div>
                    </form>
                    
                    <?php elseif ($step == 3): ?>
                    <!-- Step 3: Complete -->
                    <div class="text-center">
                        <i class="fas fa-check-circle fa-4x text-success mb-4"></i>
                        <h3>Setup Complete!</h3>
                        <p class="lead">Kolas.ai is now integrated with your content moderation system.</p>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body text-center">
                                    <i class="fas fa-comments fa-2x text-primary mb-3"></i>
                                    <h5>Test the System</h5>
                                    <p>Try sending a message with inappropriate content to test the AI detection.</p>
                                    <a href="customer/home.php" class="btn btn-outline-primary">Test Chat</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body text-center">
                                    <i class="fas fa-chart-bar fa-2x text-info mb-3"></i>
                                    <h5>Monitor Results</h5>
                                    <p>View moderation logs and statistics in the admin dashboard.</p>
                                    <a href="admin/content_moderation.php" class="btn btn-outline-info">View Logs</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="dashboard.php" class="btn btn-success btn-lg">
                            <i class="fas fa-home me-2"></i>Go to Dashboard
                        </a>
                    </div>
                    <?php endif; ?>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.progress-steps {
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 2rem 0;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin: 0 2rem;
    opacity: 0.5;
    transition: opacity 0.3s ease;
}

.step.active {
    opacity: 1;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.step.active .step-number {
    background: var(--primary-color);
    color: white;
}

.step-title {
    font-size: 0.9rem;
    font-weight: 500;
}
</style>

<?php require_once 'includes/footer.php'; ?>
