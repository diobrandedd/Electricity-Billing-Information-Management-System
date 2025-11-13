<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['customer_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_bills':
        // Get customer's recent bills (match schema fields)
        $bills = fetchAll(
            "SELECT
                b.bill_id,
                b.bill_number,
                b.billing_period_start,
                b.billing_period_end,
                b.due_date,
                b.total_amount,
                b.status
            FROM bills b
            WHERE b.customer_id = ?
            ORDER BY b.due_date DESC, b.billing_period_end DESC
            LIMIT 10",
            [$_SESSION['customer_id']]
        );
        
        echo json_encode(['success' => true, 'bills' => $bills]);
        break;
        
    case 'get_bill_details':
        $bill_id = (int) ($_GET['bill_id'] ?? 0);
        if ($bill_id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid bill ID']);
            exit;
        }
        
        // Get detailed bill information
        $bill = fetchOne(
            "SELECT 
                b.*,
                c.first_name,
                c.last_name,
                c.account_number,
                c.address,
                c.contact_number,
                mr.reading_date,
                mr.previous_reading,
                mr.current_reading,
                mr.consumption,
                u.full_name AS reader_name
            FROM bills b
            JOIN customers c ON b.customer_id = c.customer_id
            LEFT JOIN meter_readings mr ON b.reading_id = mr.reading_id
            LEFT JOIN users u ON mr.meter_reader_id = u.user_id
            WHERE b.bill_id = ? AND b.customer_id = ?",
            [$bill_id, $_SESSION['customer_id']]
        );
        
        if (!$bill) {
            http_response_code(404);
            echo json_encode(['error' => 'Bill not found']);
            exit;
        }
        
        // Get payment history for this bill
        $payments = fetchAll(
            "SELECT payment_date, amount_paid, payment_method, or_number
             FROM payments 
             WHERE bill_id = ? 
             ORDER BY payment_date DESC",
            [$bill_id]
        );
        
        echo json_encode([
            'success' => true, 
            'bill' => $bill,
            'payments' => $payments
        ]);
        break;
        
    case 'generate_pdf':
        $bill_id = (int) ($_GET['bill_id'] ?? 0);
        if ($bill_id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid bill ID']);
            exit;
        }
        
        // Verify bill belongs to customer
        $bill = fetchOne(
            "SELECT b.*, c.first_name, c.last_name, c.account_number, c.address
             FROM bills b
             JOIN customers c ON b.customer_id = c.customer_id
             WHERE b.bill_id = ? AND b.customer_id = ?",
            [$bill_id, $_SESSION['customer_id']]
        );
        
        if (!$bill) {
            http_response_code(404);
            echo json_encode(['error' => 'Bill not found']);
            exit;
        }
        
        // Generate PDF (basic HTML for now)
        $pdf_path = generateBillPDF($bill);
        
        if ($pdf_path) {
            echo json_encode([
                'success' => true, 
                'pdf_url' => $pdf_path,
                'filename' => 'Bill_' . $bill['account_number'] . '_' . date('Y-m', strtotime($bill['billing_period_end'])) . '.pdf'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to generate PDF']);
        }
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}

function generateBillPDF($bill) {
    // Simple HTML to PDF generation
    $html = generateBillHTML($bill);
    
    // Create PDF directory if it doesn't exist
    $pdf_dir = __DIR__ . '/../pdfs/';
    if (!is_dir($pdf_dir)) {
        mkdir($pdf_dir, 0755, true);
    }
    
    $filename = 'bill_' . $bill['bill_id'] . '_' . time() . '.pdf';
    $filepath = $pdf_dir . $filename;
    
    // For now, we'll create an HTML file that can be printed as PDF
    // In production, you'd use a library like TCPDF or mPDF
    file_put_contents($filepath, $html);
    
    return 'pdfs/' . $filename;
}

function generateBillHTML($bill) {
    $period = date('F j, Y', strtotime($bill['billing_period_start'])) . ' - ' . date('F j, Y', strtotime($bill['billing_period_end']));
    $due_date = date('F j, Y', strtotime($bill['due_date']));
    $total = (float)$bill['total_amount'];
    $vat = (float)$bill['vat'];
    $energy = max($total - $vat, 0);
    
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <title>Electricity Bill - {$bill['account_number']}</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { text-align: center; margin-bottom: 30px; }
            .company-name { font-size: 24px; font-weight: bold; color: #FF9A00; }
            .bill-title { font-size: 18px; margin: 10px 0; }
            .bill-info { display: flex; justify-content: space-between; margin-bottom: 20px; }
            .customer-info, .bill-details { width: 48%; }
            .section-title { font-weight: bold; margin-bottom: 10px; color: #333; }
            .bill-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            .bill-table th, .bill-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            .bill-table th { background-color: #f2f2f2; }
            .total { font-weight: bold; font-size: 16px; }
            .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class='header'>
            <div class='company-name'>SOCOTECO II</div>
            <div class='bill-title'>ELECTRICITY BILL</div>
        </div>
        
        <div class='bill-info'>
            <div class='customer-info'>
                <div class='section-title'>Customer Information</div>
                <p><strong>Account Number:</strong> {$bill['account_number']}</p>
                <p><strong>Name:</strong> {$bill['first_name']} {$bill['last_name']}</p>
                <p><strong>Address:</strong> {$bill['address']}</p>
            </div>
            <div class='bill-details'>
                <div class='section-title'>Bill Details</div>
                <p><strong>Bill Number:</strong> {$bill['bill_number']}</p>
                <p><strong>Billing Period:</strong> {$period}</p>
                <p><strong>Due Date:</strong> {$due_date}</p>
                <p><strong>Status:</strong> {$bill['status']}</p>
            </div>
        </div>
        
        <table class='bill-table'>
            <tr>
                <th>Description</th>
                <th>Amount</th>
            </tr>
            <tr>
                <td>Energy Charges (Consumption: {$bill['consumption']} kWh)</td>
                <td>₱" . number_format($energy, 2) . "</td>
            </tr>
            <tr>
                <td>VAT</td>
                <td>₱" . number_format($vat, 2) . "</td>
            </tr>
            <tr class='total'>
                <td>Total Amount Due</td>
                <td>₱" . number_format($total, 2) . "</td>
            </tr>
        </table>
        
        <div class='footer'>
            <p>Thank you for choosing SOCOTECO II</p>
            <p>For inquiries, please contact our customer service</p>
        </div>
    </body>
    </html>";
}
?>
