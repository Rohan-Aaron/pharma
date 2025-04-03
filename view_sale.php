<?php
require_once 'auth.php';
require_once 'config.php';

if (!isset($_GET['id'])) {
    header("Location: sales.php");
    exit();
}

$sale_id = intval($_GET['id']);

// Fetch sale details
$sale = $conn->query("
    SELECT s.*, u.full_name AS user_name
    FROM sales s
    JOIN users u ON s.user_id = u.user_id
    WHERE s.sale_id = $sale_id
")->fetch_assoc();

if (!$sale) {
    $_SESSION['error'] = "Sale not found!";
    header("Location: sales.php");
    exit();
}

// Fetch sale items
$items = $conn->query("
    SELECT si.*, pr.name AS product_name
    FROM sale_items si
    JOIN products pr ON si.product_id = pr.product_id
    WHERE si.sale_id = $sale_id
");
?>

<?php include 'assets/admin_header.php'; ?>

<div class="container mt-4">
    <h2><i class="fas fa-receipt"></i> Sale Details</h2>
    
    <!-- Sale Summary -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Sale #<?= $sale['sale_id'] ?></h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <p><strong>Customer:</strong> <?= $sale['customer_name'] ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>Payment Method:</strong> <?= ucfirst($sale['payment_method']) ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>Sale Date:</strong> <?= date('d M Y H:i', strtotime($sale['sale_date'])) ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>Total Amount:</strong> <?= number_format($sale['total_amount'], 2) ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>Recorded By:</strong> <?= $sale['user_name'] ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sale Items -->
    <div class="card">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0"><i class="fas fa-list"></i> Sold Items</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($item = $items->fetch_assoc()) { ?>
                            <tr>
                                <td><?= $item['product_name'] ?></td>
                                <td><?= $item['quantity'] ?></td>
                                <td><?= number_format($item['unit_price'], 2) ?></td>
                                <td><?= number_format($item['quantity'] * $item['unit_price'], 2) ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <a href="sales.php" class="btn btn-secondary mt-3">
        <i class="fas fa-arrow-left"></i> Back to Sales
    </a>
</div>

<?php include 'assets/admin_footer.php'; ?>