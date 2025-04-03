<?php
// Database connection
$host = "localhost";
$user = "root";
$password = "";
$dbname = "pharmacy_db";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Insert sample users
$conn->query("INSERT IGNORE INTO users (username, password, full_name, role) VALUES
    ('grash', '" . password_hash('grash@456', PASSWORD_BCRYPT) . "', 'Grashmitha', 'admin'),
    ('aaru', '" . password_hash('aaru@123', PASSWORD_BCRYPT) . "', 'Aarlyn', 'pharmacist'),
    ('pinku', '" . password_hash('pinku@789', PASSWORD_BCRYPT) . "', 'Priyanka', 'cashier')");
echo "Inserted users<br>";

// Insert categories
$conn->query("INSERT IGNORE INTO categories (name) VALUES
    ('Prescription Drugs'),
    ('Over-the-Counter'),
    ('Medical Supplies'),
    ('Vitamins & Supplements')");
echo "Inserted categories<br>";

// Insert suppliers
$conn->query("INSERT IGNORE INTO suppliers (name, contact_person, email, phone) VALUES
    ('MediCorp Ltd', 'David Wilson', 'sales@medicorp.com', '+1-800-555-1234'),
    ('PharmaDistro Inc', 'Emily Brown', 'info@pharmadistro.com', '+1-800-555-5678')");
echo "Inserted suppliers<br>";

// Insert products
$conn->query("INSERT IGNORE INTO products (name, category_id, sku, cost_price, selling_price, stock, expiry_date, rack_number, shelf_number) VALUES
    ('Paracetamol 500mg', 2, 'PANADOL-500', 0.50, 1.99, 150, '2025-12-31', 'A', '3'),
    ('Amoxicillin 500mg', 1, 'AMOXI-500', 1.20, 4.99, 50, '2024-06-30', 'B', '1'),
    ('Vitamin C 1000mg', 4, 'VITC-1000', 0.80, 3.49, 200, '2026-03-31', 'C', '2'),
    ('Blood Pressure Monitor', 3, 'BPM-3000', 25.00, 59.99, 30, '2030-01-01', 'D', '4')");
echo "Inserted products<br>";

// Insert purchases
$conn->query("INSERT INTO purchases (supplier_id, user_id, purchase_date, invoice_number) VALUES
    (1, 1, '2024-01-15', 'INV-001'),
    (2, 1, '2024-01-16', 'INV-002')");
echo "Inserted purchases<br>";

// Insert purchase items
$conn->query("INSERT INTO purchase_items (purchase_id, product_id, quantity, unit_cost, batch_no, expiry_date) VALUES
    (1, 1, 200, 0.45, 'BATCH-001', '2025-12-31'),
    (1, 3, 300, 0.75, 'BATCH-003', '2026-03-31'),
    (2, 2, 100, 1.10, 'BATCH-002', '2024-06-30')");
echo "Inserted purchase items<br>";

// Insert sales
$conn->query("INSERT INTO sales (user_id, sale_date, total_amount, payment_method, customer_name) VALUES
    (3, NOW(), 23.94, 'cash', 'Walk-in Customer'),
    (3, NOW(), 9.98, 'card', 'John Smith')");
echo "Inserted sales<br>";

// Insert sale items
$conn->query("INSERT INTO sale_items (sale_id, product_id, quantity, unit_price) VALUES
    (1, 1, 6, 1.99),  -- 6 x Paracetamol
    (1, 3, 2, 3.49),  -- 2 x Vitamin C
    (2, 2, 2, 4.99)"); // 2 x Amoxicillin
echo "Inserted sale items<br>";

// Insert notifications
$conn->query("INSERT INTO notifications (user_id, type, message) VALUES
    (1, 'expiry', 'Amoxicillin 500mg (BATCH-002) expires in 90 days'),
    (1, 'stock', 'Blood Pressure Monitor stock is low (25 units remaining)')");
echo "Inserted notifications<br>";

// Display sample data tables
function showTable($conn, $table, $columns) {
    echo "<h3>$table</h3>";
    $result = $conn->query("SELECT $columns FROM $table");
    if ($result->num_rows > 0) {
        echo "<table class='table table-bordered'><tr>";
        while ($field = $result->fetch_field()) {
            echo "<th>{$field->name}</th>";
        }
        echo "</tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>$value</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pharmacy Sample Data</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; }
        table { margin-bottom: 30px; }
        th { background-color: #f8f9fa; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Pharmacy Sample Data Loaded</h1>
        
        <?php
        showTable($conn, 'users', 'user_id, username, full_name, role');
        showTable($conn, 'categories', 'category_id, name');
        showTable($conn, 'suppliers', 'supplier_id, name, contact_person, phone');
        showTable($conn, 'products', 'product_id, name, stock, expiry_date, rack_number, shelf_number');
        showTable($conn, 'purchases', 'purchase_id, purchase_date, invoice_number');
        showTable($conn, 'purchase_items', 'purchase_item_id, product_id, quantity, batch_no, expiry_date');
        showTable($conn, 'sales', 'sale_id, sale_date, total_amount, payment_method');
        showTable($conn, 'sale_items', 'sale_item_id, product_id, quantity');
        showTable($conn, 'notifications', 'notification_id, message, created_at');
        showTable($conn, 'inventory_transactions', 'transaction_id, product_id, quantity_change, transaction_type');
        ?>

        <div class="alert alert-success">
            <h4>Test Credentials:</h4>
            <ul>
                <li><strong>Admin:</strong> admin / admin123</li>
                <li><strong>Pharmacist:</strong> pharma1 / pharma123</li>
                <li><strong>Cashier:</strong> cashier1 / cashier123</li>
            </ul>
        </div>

        <a href="login.php" class="btn btn-primary">Go to Login Page</a>
    </div>
</body>
</html>

<?php $conn->close(); ?>