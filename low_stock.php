<?php
require_once 'auth.php';
require_once 'config.php';

$low_stock = $conn->query("
    SELECT p.*, c.name AS category_name,
           DATEDIFF(p.expiry_date, CURDATE()) AS days_to_expire
    FROM products p
    JOIN categories c ON p.category_id = c.category_id
    WHERE p.stock < 50
    AND p.deleted_at IS NULL
    ORDER BY p.stock ASC
");
?>

<?php include 'assets/admin_header.php'; ?>

<div class="container mt-4">
    <h2 class="text-danger mb-4">
        <i class="fas fa-exclamation-triangle"></i> Low Stock Alerts
    </h2>
    
    <div class="row">
        <?php while($item = $low_stock->fetch_assoc()) : ?>
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-capsules"></i> <?= htmlspecialchars($item['name']) ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <p class="text-muted mb-1">Current Stock</p>
                            <h2 class="text-danger"><?= $item['stock'] ?></h2>
                        </div>
                        <div class="col-6">
                            <p class="text-muted mb-1">Expiry Status</p>
                            <h4 class="<?= $item['days_to_expire'] < 60 ? 'text-danger' : 'text-success' ?>">
                                <?= $item['days_to_expire'] ?> days
                            </h4>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <p class="mb-1"><i class="fas fa-map-marker-alt"></i> Location: 
                                Rack <?= $item['rack_number'] ?>, Shelf <?= $item['shelf_number'] ?>
                            </p>
                            <p class="mb-0"><i class="fas fa-tag"></i> Category: 
                                <?= $item['category_name'] ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="manage_products.php" class="btn btn-outline-danger">
                        <i class="fas fa-box-open"></i> Reorder Now
                    </a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<?php include 'assets/admin_footer.php'; ?>