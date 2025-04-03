<?php
require_once 'auth.php';
require_once 'config.php';

// Fetch key metrics
$total_sales = $conn->query("SELECT SUM(total_amount) AS total FROM sales")->fetch_assoc()['total'] ?? 0;
$total_purchases = $conn->query("SELECT SUM(total_amount) AS total FROM purchases")->fetch_assoc()['total'] ?? 0;
$total_products = $conn->query("SELECT COUNT(*) AS total FROM products WHERE deleted_at IS NULL")->fetch_assoc()['total'] ?? 0;
$low_stock_count = $conn->query("SELECT COUNT(*) AS total FROM products WHERE stock < 50 AND deleted_at IS NULL")->fetch_assoc()['total'] ?? 0;

// Fetch recent sales
$recent_sales = $conn->query("
    SELECT s.*, u.full_name AS user_name
    FROM sales s
    JOIN users u ON s.user_id = u.user_id
    ORDER BY s.sale_date DESC
    LIMIT 5
");

// Fetch recent purchases
$recent_purchases = $conn->query("
    SELECT p.*, s.name AS supplier_name, u.full_name AS user_name
    FROM purchases p
    JOIN suppliers s ON p.supplier_id = s.supplier_id
    JOIN users u ON p.user_id = u.user_id
    ORDER BY p.purchase_date DESC
    LIMIT 5
");
?>

<?php include 'assets/admin_header.php'; ?>

<div class="container mt-4">
    <h2><i class="fas fa-tachometer-alt"></i> Dashboard</h2>
    
    <!-- Key Metrics -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-dollar-sign"></i> Total Sales</h5>
                    <p class="card-text h4"><?= number_format($total_sales, 2) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-shopping-cart"></i> Total Purchases</h5>
                    <p class="card-text h4"><?= number_format($total_purchases, 2) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-pills"></i> Total Products</h5>
                    <p class="card-text h4"><?= $total_products ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-exclamation-triangle"></i> Low Stock Items</h5>
                    <p class="card-text h4"><?= $low_stock_count ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-receipt"></i> Recent Sales</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Recorded By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($sale = $recent_sales->fetch_assoc()) { ?>
                                    <tr>
                                        <td><?= date('d M Y H:i', strtotime($sale['sale_date'])) ?></td>
                                        <td><?= $sale['customer_name'] ?></td>
                                        <td><?= number_format($sale['total_amount'], 2) ?></td>
                                        <td><?= $sale['user_name'] ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-truck"></i> Recent Purchases</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Supplier</th>
                                    <th>Amount</th>
                                    <th>Recorded By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($purchase = $recent_purchases->fetch_assoc()) { ?>
                                    <tr>
                                        <td><?= date('d M Y', strtotime($purchase['purchase_date'])) ?></td>
                                        <td><?= $purchase['supplier_name'] ?></td>
                                        <td><?= number_format($purchase['total_amount'], 2) ?></td>
                                        <td><?= $purchase['user_name'] ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-link"></i> Quick Links</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2 d-md-flex">
                        <a href="manage_products.php" class="btn btn-primary me-md-2">
                            <i class="fas fa-pills"></i> Manage Products
                        </a>
                        <a href="sales.php" class="btn btn-success me-md-2">
                            <i class="fas fa-cash-register"></i> Record Sale
                        </a>
                        <a href="purchases.php" class="btn btn-warning me-md-2">
                            <i class="fas fa-shopping-cart"></i> Record Purchase
                        </a>
                        <a href="low_stock.php" class="btn btn-danger">
                            <i class="fas fa-exclamation-triangle"></i> Low Stock Alerts
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'assets/admin_footer.php'; ?>