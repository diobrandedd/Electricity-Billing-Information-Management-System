# SOCOTECO II Billing Management System

> **Important Notice**: This system is **NOT** intended to replace SOCOTECO II's current billing system. Instead, it serves as a reference implementation showcasing unique features and modern approaches that SOCOTECO II could potentially integrate into their existing system. This is a demonstration project highlighting innovative features such as AI-powered content moderation, priority queue management, real-time chat support, and automated SMS notifications.

A comprehensive billing management system designed specifically for Philippine electric cooperatives, featuring customer management, meter reading tracking, automated billing, payment processing, and detailed reporting.

## Network Architecture
For the recommended network topology (DMZ, App, DB, Admin VPN, Integration, SMS, Ops) see: [`docs/network-diagram.md`](docs/network-diagram.md)

## Unique Features

This system includes several unique features that differentiate it from standard billing systems:

### 💬 Customer Support Chat System (with AI Chat Filtering Moderation)
- **Real-time Chat Interface**: Direct communication between customers and support staff
- **AI-Powered Content Moderation**: Kolas.ai integration automatically filters inappropriate content
- **Admin Chat Dashboard**: Centralized interface for managing multiple customer conversations
- **Message History**: Complete chat logs for customer service reference
- **Unread Message Notifications**: Real-time alerts for new customer messages

### 📝 Feedback Board
- **Customer Feedback System**: Integrated feedback collection and management
- **Category-based Organization**: Organize feedback by type (complaints, suggestions, inquiries)
- **Admin Response System**: Reply to customer feedback directly from the admin panel
- **Status Tracking**: Track feedback status (new, in-progress, resolved)
- **Feedback Analytics**: View feedback statistics and trends

### 📊 Monthly Usage Analytics
- **Customer Usage Dashboard**: Visual analytics for individual customer consumption patterns
- **Monthly Trends**: Track electricity usage over time with interactive charts
- **Comparative Analysis**: Compare usage across different billing periods
- **Consumption Insights**: Identify usage patterns and anomalies
- **Data Visualization**: Interactive charts and graphs for better understanding

### 🎫 Priority Queueing System
- **Digital Queue Management**: Replace physical queue tickets with digital priority numbers
- **Category-based Queuing**: Separate queues for different service types
- **Real-time Display**: Public display screens showing current queue status
- **Admin Queue Control**: Manage queue, call customers, and track service times
- **Queue Statistics**: Monitor queue performance and customer wait times

### 📱 SMS Notification System
- **Automated Bill Reminders**: Automatically sends SMS 3 days before bill due date
- **Overdue Notifications**: Automatically sends SMS 7 days after due date
- **Disconnection Notices**: Automatically sends SMS 30 days after due date
- **Manual SMS Option**: Admin buttons available for testing or manual sending
- **Bulk Notifications**: Send notifications to multiple customers simultaneously

---

## Standard Features

The system also includes standard billing management features:

- **User Management**: Role-based access control (Admin, Cashier, Meter Reader, Customer)
- **Customer Management**: Customer profiles, categories, and account management
- **Meter Reading**: Manual entry and consumption calculation
- **Billing & Invoicing**: Automated bill generation with detailed breakdowns
- **Payment Processing**: Payment recording, receipt generation, and balance tracking
- **Reports**: Collection reports, aging reports, and revenue analytics
- **Modern UI**: Responsive design with Bootstrap 5 and interactive charts

## System Requirements

### Server Requirements
- **PHP**: Version 7.4 or higher
- **MySQL**: Version 5.7 or higher
- **Web Server**: Apache or Nginx
- **Extensions**: PDO, PDO_MySQL, JSON, Session

### Recommended Environment
- **XAMPP**: For local development (Windows/Mac/Linux)
- **WAMP**: For Windows development
- **LAMP**: For Linux production servers

## Installation Guide

### Step 1: Download and Setup
1. Download the system files to your web server directory
2. For XAMPP: Place files in `C:\xampp\htdocs\socobillSys\` (or your preferred folder name)
3. For WAMP: Place files in `C:\wamp64\www\socobillSys\` (or your preferred folder name)
4. For LAMP: Place files in `/var/www/html/socobillSys/` (or your web root directory)

### Step 2: Database Setup
1. Start your web server (Apache) and database (MySQL)
2. Open phpMyAdmin: `http://localhost/phpmyadmin`
3. Create a new database named `socoteco_billing`
4. Import the database:
   - Go to the "Import" tab
   - **Choose the file `socoteco_billing.sql`** (located in the root directory)
   - Click "Go" to import
   - This will create all necessary tables and initial data

### Step 3: Configuration
The system requires configuration in multiple files. Follow these steps carefully:

#### 3.1: Database Configuration
1. Open `config/config.php`
2. Update the database connection constants:
   ```php
   define('DB_HOST', 'localhost');        // Your MySQL host
   define('DB_NAME', 'socoteco_billing'); // Database name
   define('DB_USER', 'root');             // Your MySQL username
   define('DB_PASS', 'your_password');    // Your MySQL password
   define('DB_CHARSET', 'utf8mb4');
   ```
3. Update the site URL:
   ```php
   define('SITE_URL', 'http://localhost/socobillSys'); // Change to match your setup
   ```
4. Set environment:
   ```php
   define('ENVIRONMENT', 'development'); // Change to 'production' when deploying
   ```

#### 3.2: System Configuration
The `config/database.php` file uses the constants from `config/config.php`, so you only need to update `config/config.php` for database settings.

### Step 4: Access the System
1. Open your web browser
2. Navigate to: `http://localhost/socobillSys/auth/login.php` (adjust URL based on your setup)
3. Use default admin credentials:
   - **Username**: `admin`
   - **Password**: `admin123`
   - **⚠️ Important**: Change this password immediately after first login!

### Step 5: Initial Setup
1. **Change Admin Password**: Go to Settings → User Management
2. **Configure System Settings**: Update company information and rates
3. **Add Customer Categories**: Verify or modify customer categories
4. **Create User Accounts**: Add cashier and meter reader accounts

---

## 📱 SMS Notification Setup

The system includes an **automated** SMS notification feature that sends bill reminders, overdue notices, and disconnection warnings automatically. The manual SMS buttons in the admin interface are provided for testing and on-demand sending only. Here's how to set it up:

### Option 1: Using Android SMS Gateway App (Recommended)

1. **Download the Android App**:
   - The APK file is located at: `sms/app-release.apk`
   - Transfer this file to an Android device
   - Install the app on the Android device

2. **Configure SMS Gateway**:
   - Open `sms/config/sms_config.php`
   - Update the SMS gateway credentials:
   ```php
   define('SMS_USERNAME', 'your_username');      // From SMS gateway service
   define('SMS_PASSWORD', 'your_password');     // From SMS gateway service
   define('SMS_SERVER_URL', 'https://api.sms-gate.app/3rdparty/v1');
   ```

3. **Configure the Android App**:
   - Open the SMS Gateway app on your Android device
   - Enter the server URL: `http://your-server-ip/sms/` (or your domain)
   - Enter your credentials
   - Start the SMS gateway service

4. **Test SMS Functionality**:
   - Go to the Bills page in the admin panel
   - Click on a bill's SMS dropdown
   - Send a test SMS to verify the connection

### Option 2: Using SMS Gateway API

If you prefer to use an SMS gateway API service:

1. **Get API Credentials**:
   - Sign up for an SMS gateway service (e.g., SMS Gateway API, Twilio, etc.)
   - Obtain your API credentials (username, password, API endpoint)

2. **Update Configuration**:
   - Edit `sms/config/sms_config.php`
   - Update all SMS-related constants:
   ```php
   define('SMS_USERNAME', 'your_api_username');
   define('SMS_PASSWORD', 'your_api_password');
   define('SMS_SERVER_URL', 'https://your-sms-gateway-api.com/endpoint');
   ```

3. **Configure Notification Settings**:
   ```php
   define('SMS_DUE_DATE_REMINDER_DAYS', 3);  // Days before due date
   define('SMS_OVERDUE_REMINDER_DAYS', 7);   // Days after due date
   define('SMS_DISCONNECTION_DAYS', 30);     // Days for disconnection notice
   ```

4. **Set Up Automated SMS (Required for Automatic Notifications)**:
   - **Important**: SMS notifications are automated and require cron jobs to be set up
   - The system will automatically send SMS for:
     - Due date reminders (3 days before)
     - Overdue reminders (7 days after due date)
     - Disconnection notices (30 days after due date)
   - See `sms/README.md` for detailed cron job setup instructions
   - Manual SMS buttons in the admin panel are for testing and on-demand sending only

### SMS Features
- **Automated Notifications**: The system automatically sends SMS notifications:
  - **Due Date Reminders**: 3 days before bill due date
  - **Overdue Reminders**: 7 days after due date
  - **Disconnection Notices**: 30 days after due date
- **Manual SMS Buttons**: Admin interface includes buttons for:
  - Testing SMS functionality
  - Manually sending notifications when needed
  - Sending individual or bulk SMS from the Bills page
- **Note**: SMS notifications run automatically via cron jobs. Manual buttons are provided for testing and on-demand sending only.

---

## 🤖 Kolas AI API Setup

The system includes AI-powered content moderation using Kolas.ai. Here's how to configure it:

### Step 1: Get Kolas.ai Credentials

1. **Sign Up for Kolas.ai**:
   - Visit [Kolas.ai](https://kolas.ai) and create an account
   - Navigate to your account settings to get your credentials

2. **Create a Project**:
   - Create a new project in Kolas.ai for message classification
   - Note down your Project ID

### Step 2: Configure Kolas.ai

**Option A: Using the Setup Wizard (Recommended)**
1. Navigate to: `http://localhost/socobillSys/setup_kolas_ai.php`
2. Follow the setup wizard:
   - Enter your **Client ID**
   - Enter your **Client Secret**
   - Enter your **Project ID**
3. Click "Save Configuration"

**Option B: Manual Configuration**
1. Open `config/kolas_config.php`
2. Update the configuration:
   ```php
   return array (
     'client_id' => 'your_client_id',
     'client_secret' => 'your_client_secret',
     'project_id' => 'your_project_id',
     'base_url' => 'https://app.kolas.ai',
     'enabled' => true,
     'timeout' => 10,
   );
   ```

### Step 3: Test the Integration

1. Go to the customer chat interface
2. Try sending a message with inappropriate content
3. The system should automatically detect and moderate the content
4. Check the admin content moderation page to view moderation logs

### Kolas.ai Features
- **AI-Powered Content Moderation**: Automatically detects inappropriate content
- **Context-Aware Analysis**: Understands context, not just keywords
- **Multi-language Support**: Works with Filipino and English
- **Real-time Processing**: Instant content analysis

---

## 📄 Exported PDFs Location

Generated PDF files (bills, receipts, reports) are stored in the following location:

- **PDF Directory**: `pdfs/` (root directory)
- **Full Path Example**: `C:\xampp\htdocs\socobillSys\pdfs\`

### PDF File Naming Convention
- Bills: `bill_{bill_id}_{timestamp}.pdf`
- Receipts: `receipt_{payment_id}_{timestamp}.pdf`
- Reports: `report_{type}_{date}.pdf`

### Accessing PDFs
- PDFs are automatically generated when:
  - Viewing/printing bills
  - Processing payments
  - Generating reports
- PDFs can be accessed directly via URL: `http://localhost/socobillSys/pdfs/filename.pdf`

### Important Notes
- PDFs are temporary files and may be cleaned up periodically
- For production, consider implementing a PDF cleanup cron job
- Ensure the `pdfs/` directory has write permissions (755 or 777)

---

## 📋 Logs and Error Checking

The system maintains several log files for debugging and monitoring:

### Log File Locations

1. **Database Errors**: `logs/db_errors.log`
   - Contains database connection errors
   - Logs SQL query failures
   - Useful for debugging database issues

2. **SMS Logs**: `logs/sms_log.txt`
   - Records all SMS sending attempts
   - Includes success/failure status
   - Timestamps for each SMS operation

3. **PHP Errors** (Production): `logs/php_errors.log`
   - PHP errors and warnings (only in production mode)
   - Set in `config/config.php` when `ENVIRONMENT = 'production'`

### How to Check Logs

**For Development:**
- Errors are displayed directly in the browser
- Check browser console for JavaScript errors
- Enable error display in `config/config.php`:
  ```php
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
  ```

**For Production:**
- Errors are logged to files (not displayed)
- Check log files regularly:
  ```bash
  # View database errors
  tail -f logs/db_errors.log
  
  # View SMS logs
  tail -f logs/sms_log.txt
  
  # View PHP errors
  tail -f logs/php_errors.log
  ```

### Log File Permissions
- Ensure the `logs/` directory exists and has write permissions (755 or 777)
- The system will create log files automatically if the directory exists

### Troubleshooting with Logs

1. **Database Connection Issues**:
   - Check `logs/db_errors.log` for connection errors
   - Verify database credentials in `config/config.php`

2. **SMS Not Sending**:
   - Check `logs/sms_log.txt` for SMS gateway errors
   - Verify SMS credentials in `sms/config/sms_config.php`

3. **General Errors**:
   - Check `logs/php_errors.log` for PHP errors
   - Review error messages for specific issues

---

## ⚙️ Complete Configuration Guide

### System Configuration Files

The system uses multiple configuration files. Here's a complete guide:

#### 1. Main Configuration (`config/config.php`)

**Database Settings:**
```php
define('DB_HOST', 'localhost');           // MySQL host (usually 'localhost')
define('DB_NAME', 'socoteco_billing');     // Database name
define('DB_USER', 'root');                 // MySQL username
define('DB_PASS', 'your_password');        // MySQL password
define('DB_CHARSET', 'utf8mb4');           // Character set
```

**Site Configuration:**
```php
define('SITE_URL', 'http://localhost/socobillSys');  // Your site URL
define('SITE_NAME', 'SOCOTECO II Billing Management System');
define('ENVIRONMENT', 'development');  // 'development' or 'production'
```

**For Different Environments:**

**XAMPP (Windows):**
```php
define('SITE_URL', 'http://localhost/socobillSys');
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Usually empty for XAMPP
```

**WAMP (Windows):**
```php
define('SITE_URL', 'http://localhost/socobillSys');
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Usually empty for WAMP
```

**LAMP (Linux):**
```php
define('SITE_URL', 'http://your-domain.com/socobillSys');
define('DB_HOST', 'localhost');
define('DB_USER', 'your_mysql_user');
define('DB_PASS', 'your_mysql_password');
```

**Production Server:**
```php
define('SITE_URL', 'https://your-domain.com');
define('ENVIRONMENT', 'production');
define('DB_HOST', 'localhost');  // Or your database server IP
define('DB_USER', 'production_user');
define('DB_PASS', 'secure_password');
```

#### 2. Database Configuration (`config/database.php`)

This file automatically uses constants from `config/config.php`. No manual editing needed unless you want to customize connection options.

#### 3. SMS Configuration (`sms/config/sms_config.php`)

```php
define('SMS_USERNAME', 'your_sms_username');
define('SMS_PASSWORD', 'your_sms_password');
define('SMS_SERVER_URL', 'https://api.sms-gate.app/3rdparty/v1');
define('SMS_ENABLED', true);
```

#### 4. Kolas AI Configuration (`config/kolas_config.php`)

```php
return array (
  'client_id' => 'your_client_id',
  'client_secret' => 'your_client_secret',
  'project_id' => 'your_project_id',
  'base_url' => 'https://app.kolas.ai',
  'enabled' => true,
);
```

### Configuration Checklist

Before running the system, ensure:

- [ ] Database credentials are correct in `config/config.php`
- [ ] Site URL matches your installation path
- [ ] Database `socoteco_billing` exists and is imported from `socoteco_billing.sql`
- [ ] `logs/` directory exists and has write permissions
- [ ] `pdfs/` directory exists and has write permissions
- [ ] SMS credentials are configured (if using SMS features)
- [ ] Kolas.ai credentials are configured (if using AI moderation)
- [ ] Environment is set correctly (development/production)

### Common Configuration Issues

**Issue: "Database connection failed"**
- Solution: Check `DB_HOST`, `DB_USER`, `DB_PASS` in `config/config.php`
- Verify MySQL is running
- Ensure database `socoteco_billing` exists

**Issue: "Page not found" or "404 errors"**
- Solution: Update `SITE_URL` in `config/config.php` to match your installation path
- Check Apache/Nginx virtual host configuration

**Issue: "Permission denied" errors**
- Solution: Set proper file permissions:
  - Directories: `chmod 755` or `chmod 777`
  - Files: `chmod 644`
  - Logs directory: `chmod 777`

**Issue: "SMS not sending"**
- Solution: Verify SMS credentials in `sms/config/sms_config.php`
- Check `logs/sms_log.txt` for error messages
- Ensure SMS gateway service is accessible

---

## 🎯 Unique Features for SOCOTECO II

This system includes five unique features that SOCOTECO II could consider implementing:

1. **Customer Support Chat System (with AI Chat Filtering Moderation)**: Real-time customer support with AI-powered content moderation
2. **Feedback Board**: Integrated customer feedback collection and management system
3. **Monthly Usage Analytics**: Visual analytics dashboard for customer consumption patterns
4. **Priority Queueing System**: Digital queue management with real-time display and category support
5. **SMS Notification System**: Automated SMS notifications for due dates, overdues, and disconnection notices

## Default System Settings

### Customer Categories
- **Residential**: Base rate ₱8.50/kWh
- **Commercial**: Base rate ₱9.20/kWh
- **Industrial**: Base rate ₱8.80/kWh
- **Government**: Base rate ₱8.00/kWh

### Billing Rates (per kWh)
- **Generation Charge**: ₱4.50
- **Distribution Charge**: ₱1.20
- **Transmission Charge**: ₱0.80
- **System Loss Charge**: ₱0.50
- **VAT**: 12%

### System Configuration
- **Due Days**: 15 days from bill date
- **Penalty Rate**: 2% per month for overdue bills

## User Roles & Permissions

### 👑 Administrator
- Full system access
- User management
- System settings
- All reports and analytics
- Customer management
- Billing and payment processing

### 💰 Cashier
- Customer management
- Payment processing
- Bill viewing and printing
- Customer information access

### 📊 Meter Reader
- Meter reading entry
- Reading history viewing
- Customer information access

### 👤 Customer (Future Feature)
- View own bills
- Payment history
- Account information

## File Structure

```
socotecoSys/
├── auth/                    # Authentication files
│   ├── login.php
│   └── logout.php
├── config/                  # Configuration files
│   ├── config.php
│   └── database.php
├── database/                # Database files
│   └── schema.sql
├── includes/                # Common includes
│   ├── header.php
│   └── footer.php
├── ajax/                    # AJAX endpoints
│   ├── get_previous_reading.php
│   └── get_bill_details.php
├── dashboard.php            # Main dashboard
├── customers.php            # Customer management
├── customer_details.php     # Customer details view
├── meter_readings.php       # Meter reading management
├── bills.php                # Billing management
├── bill_details.php         # Bill details view
├── bill_print.php           # Bill printing
├── payments.php             # Payment management
├── payment_receipt.php      # Payment receipt printing
├── reports.php              # Reports and analytics
└── README.md               # This file
```

## Key Features Explained

### Automated Billing Process
1. **Meter Reading Entry**: Staff records monthly meter readings
2. **Bill Generation**: System automatically calculates consumption and charges
3. **Bill Distribution**: Bills are generated with unique bill numbers
4. **Payment Processing**: Cashiers process payments and generate receipts
5. **Status Updates**: Bill status automatically updates based on payments

### Rate Calculation
The system uses a tiered rate structure:
- **Base Rate**: Varies by customer category
- **Additional Charges**: Generation, Distribution, Transmission, System Loss
- **VAT**: 12% on total charges
- **Penalties**: 2% monthly for overdue bills

### Security Features
- **CSRF Protection**: All forms protected against cross-site request forgery
- **SQL Injection Prevention**: Prepared statements for all database queries
- **Session Management**: Secure session handling
- **Input Sanitization**: All user inputs are sanitized
- **Audit Trail**: Complete logging of all system activities

## Troubleshooting

### Common Issues

#### Database Connection Error
- Check if MySQL is running
- Verify database credentials in `config/database.php`
- Ensure database `socoteco_billing` exists

#### Login Issues
- Verify default credentials: admin/admin123
- Check if user account is active
- Clear browser cache and cookies

#### Permission Errors
- Ensure web server has write permissions to upload directories
- Check file permissions (755 for directories, 644 for files)

#### Bill Generation Issues
- Verify meter readings exist for the billing period
- Check if bill already exists for the reading
- Ensure customer is active

### Support
For technical support or feature requests, please contact the system developer (me).

## Future Enhancements

### Planned Features
- **Email Integration**: Email bill delivery and notifications
- **Online Payment Gateway**: Integration with payment processors
- **Mobile App**: Customer mobile application
- **API Integration**: RESTful API for third-party integrations
- **Advanced Analytics**: Machine learning for consumption prediction
- **Document Management**: Digital document storage and retrieval

### Customization Options
- **Multi-language Support**: Filipino and English language options
- **Custom Rate Structures**: Flexible rate configuration
- **Additional Report Types**: Custom report generation
- **Integration APIs**: Connect with existing systems

## License

This system is developed for SOCOTECO II and is proprietary software. Unauthorized distribution or modification is prohibited.

## Version History

### Version 1.0.0 (Current)
- Initial release
- Complete billing management system
- User authentication and role management
- Customer, billing, and payment modules
- Comprehensive reporting system
- Modern responsive UI

---

## 🙏 Credits

This system was developed as a demonstration project for **SOCOTECO II Electric Cooperative, Inc.**

**Developer/s**
- This system was developed solely by me

**About SOCOTECO II:**
- **Official Website**: [https://www.socoteco2.com/](https://www.socoteco2.com/)
- **Location**: Jose Catolico Avenue, Brgy. Lagao, General Santos City, 9500
- **Contact**: (083) 553-5848 to 50 | 09177205365 / 09124094971

This project showcases modern web development approaches and unique features that could potentially enhance SOCOTECO II's existing systems. It is not intended to replace any current systems but rather to demonstrate innovative solutions for electric cooperative management.

---

**Developed for SOCOTECO II Electric Cooperative**  
*Empowering Philippine electric cooperatives with modern billing solutions*
