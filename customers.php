<?php
$page_title = 'Customer Management';
require_once 'includes/header.php';
requireRole(['admin', 'cashier']);

$action = $_GET['action'] ?? 'list';
$customer_id = $_GET['id'] ?? null;
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Invalid request. Please try again.';
    } else {
        if ($action == 'add' || $action == 'edit') {
            $account_number = sanitizeInput($_POST['account_number']);
            $meter_number = sanitizeInput($_POST['meter_number']);
            $first_name = sanitizeInput($_POST['first_name']);
            $last_name = sanitizeInput($_POST['last_name']);
            $middle_name = sanitizeInput($_POST['middle_name']);
            $address = sanitizeInput($_POST['address']);
            $barangay = sanitizeInput($_POST['barangay']);
            $municipality = sanitizeInput($_POST['municipality']);
            $province = sanitizeInput($_POST['province']);
            $contact_number = sanitizeInput($_POST['contact_number']);
            $email = sanitizeInput($_POST['email']);
            $category_id = (int)$_POST['category_id'];
            $connection_date = $_POST['connection_date'];
            
            // Validation
            if (empty($account_number) || empty($meter_number) || empty($first_name) || empty($last_name)) {
                $error = 'Please fill in all required fields.';
            } else {
                // Check for duplicate account number
                $check_sql = "SELECT customer_id FROM customers WHERE account_number = ?";
                if ($action == 'edit') {
                    $check_sql .= " AND customer_id != ?";
                    $check_params = [$account_number, $customer_id];
                } else {
                    $check_params = [$account_number];
                }
                
                $existing = fetchOne($check_sql, $check_params);
                
                if ($existing) {
                    $error = 'Account number already exists.';
                } else {
                    // Check for duplicate meter number
                    $check_sql = "SELECT customer_id FROM customers WHERE meter_number = ?";
                    if ($action == 'edit') {
                        $check_sql .= " AND customer_id != ?";
                        $check_params = [$meter_number, $customer_id];
                    } else {
                        $check_params = [$meter_number];
                    }
                    
                    $existing = fetchOne($check_sql, $check_params);
                    
                    if ($existing) {
                        $error = 'Meter number already exists.';
                    } else {
                        if ($action == 'add') {
                            $sql = "INSERT INTO customers (account_number, meter_number, first_name, last_name, middle_name, 
                                    address, barangay, municipality, province, contact_number, email, category_id, connection_date) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                            
                            executeQuery($sql, [
                                $account_number, $meter_number, $first_name, $last_name, $middle_name,
                                $address, $barangay, $municipality, $province, $contact_number, $email, $category_id, $connection_date
                            ]);
                            
                            logActivity('Customer created', 'customers', getLastInsertId());
                            $message = 'Customer added successfully.';
                            $action = 'list';
                        } else {
                            $sql = "UPDATE customers SET account_number = ?, meter_number = ?, first_name = ?, last_name = ?, 
                                    middle_name = ?, address = ?, barangay = ?, municipality = ?, province = ?, 
                                    contact_number = ?, email = ?, category_id = ?, connection_date = ? 
                                    WHERE customer_id = ?";
                            
                            executeQuery($sql, [
                                $account_number, $meter_number, $first_name, $last_name, $middle_name,
                                $address, $barangay, $municipality, $province, $contact_number, $email, $category_id, $connection_date, $customer_id
                            ]);
                            
                            logActivity('Customer updated', 'customers', $customer_id);
                            $message = 'Customer updated successfully.';
                            $action = 'list';
                        }
                    }
                }
            }
        }
    }
}

// Get customer categories
$categories = fetchAll("SELECT * FROM customer_categories ORDER BY category_name");

// Get customer data for editing
$customer = null;
if ($action == 'edit' && $customer_id) {
    $customer = fetchOne("SELECT * FROM customers WHERE customer_id = ?", [$customer_id]);
    if (!$customer) {
        $error = 'Customer not found.';
        $action = 'list';
    }
}

// Get customers list
if ($action == 'list') {
    $search = $_GET['search'] ?? '';
    $category_filter = $_GET['category'] ?? '';
    
    $sql = "SELECT c.*, cc.category_name 
            FROM customers c 
            JOIN customer_categories cc ON c.category_id = cc.category_id 
            WHERE c.is_active = 1";
    $params = [];
    
    if ($search) {
        $sql .= " AND (c.account_number LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ? OR c.meter_number LIKE ?)";
        $search_param = "%{$search}%";
        $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
    }
    
    if ($category_filter) {
        $sql .= " AND c.category_id = ?";
        $params[] = $category_filter;
    }
    
    $sql .= " ORDER BY c.last_name, c.first_name";
    
    $customers = fetchAll($sql, $params);
}
?>

<?php if ($message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($action == 'list'): ?>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-users me-2"></i>Customer Management
            </h5>
            <a href="?action=add" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add Customer
            </a>
        </div>
        <div class="card-body">
            <!-- Search and Filter -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <form method="GET" class="d-flex">
                        <input type="hidden" name="action" value="list">
                        <input type="text" class="form-control me-2" name="search" 
                               placeholder="Search by name, account, or meter number..." 
                               value="<?php echo htmlspecialchars($search ?? ''); ?>">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                <div class="col-md-3">
                    <form method="GET" class="d-flex">
                        <input type="hidden" name="action" value="list">
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search ?? ''); ?>">
                        <select name="category" class="form-select me-2">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['category_id']; ?>" 
                                        <?php echo ($category_filter == $category['category_id']) ? 'selected' : ''; ?>>
                                    <?php echo $category['category_name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-outline-secondary">
                            <i class="fas fa-filter"></i>
                        </button>
                    </form>
                </div>
                <div class="col-md-3 text-end">
                    <span class="text-muted">Total: <?php echo count($customers); ?> customers</span>
                </div>
            </div>
            
            <!-- Customers Table -->
            <div class="table-responsive">
                <table class="table table-striped data-table">
                    <thead>
                        <tr>
                            <th>Account #</th>
                            <th>Name</th>
                            <th>Meter #</th>
                            <th>Category</th>
                            <th>Address</th>
                            <th>Contact</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td><strong><?php echo $customer['account_number']; ?></strong></td>
                            <td>
                                <?php echo $customer['last_name'] . ', ' . $customer['first_name']; ?>
                                <?php if ($customer['middle_name']): ?>
                                    <?php echo ' ' . $customer['middle_name']; ?>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $customer['meter_number']; ?></td>
                            <td>
                                <span class="badge bg-primary"><?php echo $customer['category_name']; ?></span>
                            </td>
                            <td>
                                <?php echo $customer['address'] . ', ' . $customer['barangay'] . ', ' . $customer['municipality']; ?>
                            </td>
                            <td>
                                <?php if ($customer['contact_number']): ?>
                                    <i class="fas fa-phone me-1"></i><?php echo $customer['contact_number']; ?><br>
                                <?php endif; ?>
                                <?php if ($customer['email']): ?>
                                    <i class="fas fa-envelope me-1"></i><?php echo $customer['email']; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="?action=edit&id=<?php echo $customer['customer_id']; ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?php echo url('customer_details.php?id=' . $customer['customer_id']); ?>" 
                                       class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php elseif ($action == 'add' || $action == 'edit'): ?>
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-user-<?php echo $action == 'add' ? 'plus' : 'edit'; ?> me-2"></i>
                <?php echo $action == 'add' ? 'Add New Customer' : 'Edit Customer'; ?>
            </h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="account_number" class="form-label">Account Number *</label>
                            <input type="text" class="form-control" id="account_number" name="account_number" 
                                   value="<?php echo htmlspecialchars($customer['account_number'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="meter_number" class="form-label">Meter Number *</label>
                            <input type="text" class="form-control" id="meter_number" name="meter_number" 
                                   value="<?php echo htmlspecialchars($customer['meter_number'] ?? ''); ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="first_name" class="form-label">First Name *</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" 
                                   value="<?php echo htmlspecialchars($customer['first_name'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last Name *</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" 
                                   value="<?php echo htmlspecialchars($customer['last_name'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="middle_name" class="form-label">Middle Name</label>
                            <input type="text" class="form-control" id="middle_name" name="middle_name" 
                                   value="<?php echo htmlspecialchars($customer['middle_name'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="address" class="form-label">Address *</label>
                    <textarea class="form-control" id="address" name="address" rows="2" required><?php echo htmlspecialchars($customer['address'] ?? ''); ?></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="barangay" class="form-label">Barangay *</label>
                            <input type="text" class="form-control" id="barangay" name="barangay" 
                                   value="<?php echo htmlspecialchars($customer['barangay'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="municipality" class="form-label">Municipality *</label>
                            <input type="text" class="form-control" id="municipality" name="municipality" 
                                   value="<?php echo htmlspecialchars($customer['municipality'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="province" class="form-label">Province</label>
                            <input type="text" class="form-control" id="province" name="province" 
                                   value="<?php echo htmlspecialchars($customer['province'] ?? 'South Cotabato'); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="contact_number" class="form-label">Contact Number</label>
                            <div class="input-group">
                                <span class="input-group-text">+63</span>
                                <input type="text" class="form-control" id="contact_number" name="contact_number" 
                                       placeholder="9XXXXXXXXX" maxlength="10"
                                       value="<?php echo htmlspecialchars($customer['contact_number'] ?? ''); ?>"
                                       oninput="formatPhilippinePhone(this)">
                            </div>
                            <div class="form-text">Enter 10-digit Philippine mobile number starting with 9</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($customer['email'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Customer Category *</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>" 
                                            <?php echo ($customer['category_id'] ?? '') == $category['category_id'] ? 'selected' : ''; ?>>
                                        <?php echo $category['category_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="connection_date" class="form-label">Connection Date *</label>
                            <input type="date" class="form-control" id="connection_date" name="connection_date" 
                                   value="<?php echo $customer['connection_date'] ?? date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end">
                    <a href="<?php echo url('customers.php'); ?>" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>
                        <?php echo $action == 'add' ? 'Add Customer' : 'Update Customer'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<script>
function formatPhilippinePhone(input) {
    // Remove all non-numeric characters
    let value = input.value.replace(/\D/g, '');
    
    // If it starts with 63, remove it
    if (value.startsWith('63')) {
        value = value.substring(2);
    }
    
    // If it starts with 0, remove it
    if (value.startsWith('0')) {
        value = value.substring(1);
    }
    
    // Limit to 10 digits
    value = value.substring(0, 10);
    
    // Update the input value
    input.value = value;
    
    // Validate Philippine mobile number format
    const isValid = value.length === 10 && value.startsWith('9');
    
    if (value.length > 0) {
        if (isValid) {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
        } else {
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
        }
    } else {
        input.classList.remove('is-valid', 'is-invalid');
    }
}

// Add validation on form submit
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const phoneInput = document.getElementById('contact_number');
            if (phoneInput && phoneInput.value) {
                const value = phoneInput.value;
                const isValid = value.length === 10 && value.startsWith('9');
                
                if (!isValid) {
                    e.preventDefault();
                    alert('Please enter a valid 10-digit Philippine mobile number starting with 9');
                    phoneInput.focus();
                    return false;
                }
            }
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
