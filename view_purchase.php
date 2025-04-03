<?php
require_once 'auth.php';
require_once 'config.php';

if (!isset($_GET['id'])) {
    header("Location: purchases.php");
    exit();
}

$purchase_id = intval($_GET['id']);

// Fetch purchase details
$purchase = $conn->query("
    SELECT p.*, s.name AS supplier_name, u.full_name AS user_name
    FROM purchases p
    JOIN suppliers s ON p.supplier_id = s.supplier_id
    JOIN users u ON p.user_id = u.user_id
    WHERE p.purchase_id = $purchase_id
")->fetch_assoc();

if (!$purchase) {
    $_SESSION['error'] = "Purchase not found!";
    header("Location: purchases.php");
    exit();
}

// Fetch purchase items
$items = $conn->query("
    SELECT pi.*, pr.name AS product_name
    FROM purchase_items pi
    JOIN products pr ON pi.product_id = pr.product_id
    WHERE pi.purchase_id = $purchase_id
");
?>

<?php include 'assets/admin_header.php'; ?>

<div class="container mt-4">
    <h2><i class="fas fa-file-invoice"></i> Purchase Details</h2>
    
    <!-- Purchase Summary -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Purchase #<?= $purchase['purchase_id'] ?></h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <p><strong>Supplier:</strong> <?= $purchase['supplier_name'] ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>Invoice Number:</strong> <?= $purchase['invoice_number'] ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>Purchase Date:</strong> <?= date('d M Y', strtotime($purchase['purchase_date'])) ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>Total Amount:</strong> <?= number_format($purchase['total_amount'], 2) ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>Recorded By:</strong> <?= $purchase['user_name'] ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Purchase Items -->
    <div class="card">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0"><i class="fas fa-list"></i> Purchased Items</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Unit Cost</th>
                            <th>Total Cost</th>
                            <th>Batch No</th>
                            <th>Expiry Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($item = $items->fetch_assoc()) { ?>
                            <tr>
                                <td><?= $item['product_name'] ?></td>
                                <td><?= $item['quantity'] ?></td>
                                <td><?= number_format($item['unit_cost'], 2) ?></td>
                                <td><?= number_format($item['quantity'] * $item['unit_cost'], 2) ?></td>
                                <td><?= $item['batch_no'] ?></td>
                                <td><?= date('M Y', strtotime($item['expiry_date'])) ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <a href="purchases.php" class="btn btn-secondary mt-3">
        <i class="fas fa-arrow-left"></i> Back to Purchases
    </a>
</div>

<?php include 'assets/admin_footer.php'; ?>