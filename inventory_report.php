<?php
require_once 'auth.php';
require_once 'config.php';
requireRole('admin');

// Fetch inventory data
$inventory = $conn->query("
    SELECT p.*, c.name AS category_name
    FROM products p
    JOIN categories c ON p.category_id = c.category_id
    WHERE p.deleted_at IS NULL
    ORDER BY p.stock ASC
");

// Calculate totals
$total_stock_value = $conn->query("
    SELECT SUM(stock * cost_price) AS total_value
    FROM products
    WHERE deleted_at IS NULL
")->fetch_assoc()['total_value'] ?? 0;
?>

<?php include 'assets/admin_header.php'; ?>

<div class="container mt-4">
    <h2><i class="fas fa-chart-area"></i> Inventory Report</h2>
    
    <!-- Inventory Summary -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-boxes"></i> Inventory Summary</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <p><strong>Total Stock Value:</strong> <?= number_format($total_stock_value, 2) ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>Total Products:</strong> <?= $inventory->num_rows ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory List -->
    <div class="card">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0"><i class="fas fa-list"></i> Inventory Details</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Stock</th>
                            <th>Cost Price</th>
                            <th>Stock Value</th>
                            <th>Expiry Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($item = $inventory->fetch_assoc()) { ?>
                            <tr>
                                <td><?= $item['name'] ?></td>
                                <td><?= $item['category_name'] ?></td>
                                <td class="<?= $item['stock'] < 50 ? 'text-danger fw-bold' : '' ?>">
                                    <?= $item['stock'] ?>
                                </td>
                                <td><?= number_format($item['cost_price'], 2) ?></td>
                                <td><?= number_format($item['stock'] * $item['cost_price'], 2) ?></td>
                                <td><?= date('M Y', strtotime($item['expiry_date'])) ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'assets/admin_footer.php'; ?>