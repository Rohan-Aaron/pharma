<?php
require_once 'auth.php';
require_once 'config.php';

$expiring_soon = $conn->query("
    SELECT p.*, c.name AS category_name,
           DATEDIFF(p.expiry_date, CURDATE()) AS days_remaining
    FROM products p
    JOIN categories c ON p.category_id = c.category_id
    WHERE p.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 60 DAY)
    AND p.deleted_at IS NULL
    ORDER BY p.expiry_date ASC
");
?>

<?php include 'assets/admin_header.php'; ?>

<div class="container mt-4">
    <h2 class="text-warning mb-4">
        <i class="fas fa-clock"></i> Expiry Date Alerts
    </h2>
    
    <div class="card shadow">
        <div class="card-header bg-warning text-dark">
            <h4 class="mb-0"><i class="fas fa-hourglass-half"></i> Expiring Soon (Next 60 Days)</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-warning">
                        <tr>
                            <th>Medicine Name</th>
                            <th>Expiry Date</th>
                            <th>Days Remaining</th>
                            <th>Current Stock</th>
                            <th>Location</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($item = $expiring_soon->fetch_assoc()) : ?>
                        <tr class="<?= $item['days_remaining'] < 30 ? 'table-danger' : 'table-warning' ?>">
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td><?= date('d M Y', strtotime($item['expiry_date'])) ?></td>
                            <td><?= $item['days_remaining'] ?></td>
                            <td><?= $item['stock_quantity'] ?></td>
                            <td>Rack <?= $item['rack_number'] ?>, Shelf <?= $item['shelf_number'] ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'assets/admin_footer.php'; ?>