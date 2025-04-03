<?php
// Database connection parameters
$host = "localhost";
$user = "root";
$password = "";
$dbname = "pharmacy_db";

// Create connection
$conn = new mysqli($host, $user, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
if ($conn->query("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci") === TRUE) {
    echo "Database created successfully.<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select database
$conn->select_db($dbname);

// Create tables
$tables = [
    "CREATE TABLE IF NOT EXISTS users (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(255) NOT NULL,
        role ENUM('admin', 'pharmacist', 'cashier') DEFAULT 'cashier',
        last_login TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS categories (
        category_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) UNIQUE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS suppliers (
        supplier_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        contact_person VARCHAR(255),
        email VARCHAR(255),
        phone VARCHAR(20),
        address TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS products (
        product_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        category_id INT NOT NULL,
        sku VARCHAR(50) UNIQUE,
        cost_price DECIMAL(10,2) NOT NULL,
        selling_price DECIMAL(10,2) NOT NULL,
        stock INT DEFAULT 0,
        expiry_date DATE,
        rack_number VARCHAR(10),
        shelf_number VARCHAR(10),
        deleted_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(category_id)
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS purchases (
        purchase_id INT AUTO_INCREMENT PRIMARY KEY,
        supplier_id INT NOT NULL,
        user_id INT NOT NULL,
        purchase_date DATE NOT NULL,
        total_amount DECIMAL(10,2),
        invoice_number VARCHAR(50) UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id),
        FOREIGN KEY (user_id) REFERENCES users(user_id)
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS purchase_items (
        purchase_item_id INT AUTO_INCREMENT PRIMARY KEY,
        purchase_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        unit_cost DECIMAL(10,2) NOT NULL,
        batch_no VARCHAR(50),
        expiry_date DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (purchase_id) REFERENCES purchases(purchase_id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(product_id)
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS sales (
        sale_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        sale_date DATETIME NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        payment_method ENUM('cash', 'card', 'mobile_money') NOT NULL,
        customer_name VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id)
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS sale_items (
        sale_item_id INT AUTO_INCREMENT PRIMARY KEY,
        sale_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        unit_price DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (sale_id) REFERENCES sales(sale_id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(product_id)
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS notifications (
        notification_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        type ENUM('stock', 'expiry', 'system') NOT NULL,
        message TEXT NOT NULL,
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS inventory_transactions (
        transaction_id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        quantity_change INT NOT NULL,
        transaction_type ENUM('purchase', 'sale', 'adjustment') NOT NULL,
        reference_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(product_id)
    ) ENGINE=InnoDB"
];

foreach ($tables as $sql) {
    if ($conn->query($sql)) {
        echo "Table created successfully.<br>";
    } else {
        echo "Error creating table: " . $conn->error . "<br>";
    }
}

// Create indexes
$conn->query("CREATE INDEX idx_products_expiry ON products(expiry_date)");
$conn->query("CREATE INDEX idx_purchases_date ON purchases(purchase_date)");
$conn->query("CREATE INDEX idx_sales_date ON sales(sale_date)");

// Create triggers without DELIMITER commands
$triggers = [
    "CREATE TRIGGER after_purchase_item_insert
    AFTER INSERT ON purchase_items
    FOR EACH ROW
    BEGIN
        UPDATE products SET stock = stock + NEW.quantity
        WHERE product_id = NEW.product_id;
        
        INSERT INTO inventory_transactions (product_id, quantity_change, transaction_type, reference_id)
        VALUES (NEW.product_id, NEW.quantity, 'purchase', NEW.purchase_id);
    END",

    "CREATE TRIGGER after_sale_item_insert
    AFTER INSERT ON sale_items
    FOR EACH ROW
    BEGIN
        UPDATE products SET stock = stock - NEW.quantity
        WHERE product_id = NEW.product_id;
        
        INSERT INTO inventory_transactions (product_id, quantity_change, transaction_type, reference_id)
        VALUES (NEW.product_id, NEW.quantity, 'sale', NEW.sale_id);
    END",

    "CREATE TRIGGER after_purchase_item_delete
    AFTER DELETE ON purchase_items
    FOR EACH ROW
    BEGIN
        UPDATE products SET stock = stock - OLD.quantity
        WHERE product_id = OLD.product_id;
    END",

    "CREATE TRIGGER after_sale_item_delete
    AFTER DELETE ON sale_items
    FOR EACH ROW
    BEGIN
        UPDATE products SET stock = stock + OLD.quantity
        WHERE product_id = OLD.product_id;
    END"
];

foreach ($triggers as $trigger) {
    if ($conn->query($trigger)) {
        echo "Trigger created successfully.<br>";
    } else {
        echo "Error creating trigger: " . $conn->error . "<br>";
    }
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pharmacy Database Setup</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
    </style>
</head>
<body>
    <h1>Pharmacy Database Setup</h1>
    <p>Database structure initialized with all required tables:</p>
    <ul>
        <li>Users</li>
        <li>Categories</li>
        <li>Suppliers</li>
        <li>Products</li>
        <li>Purchases & Purchase Items</li>
        <li>Sales & Sale Items</li>
        <li>Notifications</li>
        <li>Inventory Transactions</li>
    </ul>
    <p><a href="data.php">Proceed to insert dummy data</a></p>
</body>
</html>