<?php
// Include auth.php for session checking and role-based access
require_once 'auth.php';

// Optional: Check for specific roles if needed
// Example: requireRole('admin'); // Uncomment if this page is admin-only
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="main.css" type="text/css">
</head>

<body>
    <!-- Hamburger Menu Toggle -->
    <button class="navbar-toggler" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="logo">
            <img src="MedLogo.png" alt="Pharmacy Logo" width="100" height="100">
        </div>
        <nav class="nav flex-column">
            <!-- Dashboard -->
            <a href="dashboard.php" class="nav-link">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>

            <!-- Inventory Management -->
            <div class="nav-section">
                <div class="nav-link" data-bs-toggle="collapse" href="#inventoryMenu">
                    <i class="fas fa-warehouse"></i> Inventory Management
                    <i class="fas fa-chevron-down float-end"></i>
                </div>
                <div class="collapse" id="inventoryMenu">
                    <a href="manage_products.php" class="nav-link sub-item">
                        <i class="fas fa-pills"></i> Products
                    </a>
                    <a href="manage_categories.php" class="nav-link sub-item">
                        <i class="fas fa-tags"></i> Categories
                    </a>
                    <a href="expiry_alerts.php" class="nav-link sub-item">
                        <i class="fas fa-exclamation-triangle"></i> Expiry Alerts
                    </a>
                    <a href="low_stock.php" class="nav-link sub-item">
                        <i class="fas fa-box-open"></i> Low Stock
                    </a>
                </div>
            </div>

            <!-- Transactions -->
            <div class="nav-section">
                <div class="nav-link" data-bs-toggle="collapse" href="#transactionsMenu">
                    <i class="fas fa-exchange-alt"></i> Transactions
                    <i class="fas fa-chevron-down float-end"></i>
                </div>
                <div class="collapse" id="transactionsMenu">
                    <a href="sales.php" class="nav-link sub-item">
                        <i class="fas fa-cash-register"></i> Sales
                    </a>
                    <a href="purchases.php" class="nav-link sub-item">
                        <i class="fas fa-shopping-basket"></i> Purchases
                    </a>
                    <a href="returns.php" class="nav-link sub-item">
                        <i class="fas fa-undo"></i> Returns
                    </a>
                </div>
            </div>

            <!-- Reporting -->
            <?php if ($_SESSION['role'] === 'admin') { ?>
                <div class="nav-section">
                    <div class="nav-link" data-bs-toggle="collapse" href="#reportsMenu">
                        <i class="fas fa-chart-bar"></i> Reporting
                        <i class="fas fa-chevron-down float-end"></i>
                    </div>
                    <div class="collapse" id="reportsMenu">
                        <a href="sales_report.php" class="nav-link sub-item">
                            <i class="fas fa-chart-line"></i> Sales Reports
                        </a>
                        <a href="purchase_report.php" class="nav-link sub-item">
                            <i class="fas fa-chart-pie"></i> Purchase Reports
                        </a>
                        <a href="inventory_report.php" class="nav-link sub-item">
                            <i class="fas fa-chart-area"></i> Inventory Analysis
                        </a>
                    </div>
                </div>
            <?php } ?>

            <!-- Administration -->
            <?php if ($_SESSION['role'] === 'admin') { ?>
                <div class="nav-section">
                    <div class="nav-link" data-bs-toggle="collapse" href="#adminMenu">
                        <i class="fas fa-cogs"></i> Administration
                        <i class="fas fa-chevron-down float-end"></i>
                    </div>
                    <div class="collapse" id="adminMenu">
                        <a href="manage_users.php" class="nav-link sub-item">
                            <i class="fas fa-users-cog"></i> User Management
                        </a>
                        <a href="supplier.php" class="nav-link sub-item">
                            <i class="fas fa-truck-moving"></i> Suppliers
                        </a>
                    </div>
                </div>
            <?php } ?>

            <!-- Logout -->
            <div class="nav-footer">
                <a href="logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">