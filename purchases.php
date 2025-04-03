<?php
require_once 'auth.php';
require_once 'config.php';
requireRole('admin');

// Handle purchase actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_purchase'])) {
        $supplier_id = intval($_POST['supplier_id']);
        $invoice_number = $conn->real_escape_string($_POST['invoice_number']);
        $purchase_date = $_POST['purchase_date'];
        $total_amount = 0;

        // Insert purchase
        $stmt = $conn->prepare("INSERT INTO purchases 
            (supplier_id, user_id, purchase_date, invoice_number) 
            VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $supplier_id, $_SESSION['user_id'], $purchase_date, $invoice_number);
        $stmt->execute();
        $purchase_id = $stmt->insert_id;

        // Insert purchase items
        foreach ($_POST['products'] as $product) {
            $product_id = intval($product['product_id']);
            $quantity = intval($product['quantity']);
            $unit_cost = floatval($product['unit_cost']);
            $expiry_date = $product['expiry_date'];
            $batch_no = $conn->real_escape_string($product['batch_no']);

            $stmt = $conn->prepare("INSERT INTO purchase_items 
                (purchase_id, product_id, quantity, unit_cost, expiry_date, batch_no) 
                VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iiidss", $purchase_id, $product_id, $quantity, 
                             $unit_cost, $expiry_date, $batch_no);
            $stmt->execute();

            $total_amount += $quantity * $unit_cost;
        }

        // Update total amount
        $conn->query("UPDATE purchases SET total_amount = $total_amount 
                      WHERE purchase_id = $purchase_id");

        $_SESSION['success'] = "Purchase recorded successfully!";
        header("Location: purchases.php");
        exit();
    }
}

// Fetch data
$purchases = $conn->query("
    SELECT p.*, s.name AS supplier_name, u.full_name AS user_name
    FROM purchases p
    JOIN suppliers s ON p.supplier_id = s.supplier_id
    JOIN users u ON p.user_id = u.user_id
    ORDER BY p.purchase_date DESC
");

$suppliers = $conn->query("SELECT * FROM suppliers");
$products = $conn->query("SELECT * FROM products WHERE deleted_at IS NULL");
?>

<?php include 'assets/admin_header.php'; ?>

<div class="container mt-4">
    <h2><i class="fas fa-shopping-cart"></i> Manage Purchases</h2>
    
    <!-- Add Purchase Form -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-plus-circle"></i> Add New Purchase</h4>
        </div>
        <div class="card-body">
            <form method="POST" id="purchaseForm">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label>Supplier</label>
                        <select name="supplier_id" class="form-select" required>
                            <?php while($supplier = $suppliers->fetch_assoc()) { ?>
                                <option value="<?= $supplier['supplier_id'] ?>"><?= $supplier['name'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Invoice Number</label>
                        <input type="text" name="invoice_number" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label>Purchase Date</label>
                        <input type="date" name="purchase_date" class="form-control" required>
                    </div>
                </div>

                <!-- Purchase Items -->
                <div class="mt-4">
                    <h5>Purchase Items</h5>
                    <div id="purchaseItems">
                        <div class="row g-3 item-row">
                            <div class="col-md-4">
                                <label>Product</label>
                                <select name="products[0][product_id]" class="form-select product-select" required>
                                    <?php while($product = $products->fetch_assoc()) { ?>
                                        <option value="<?= $product['product_id'] ?>"><?= $product['name'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Quantity</label>
                                <input type="number" name="products[0][quantity]" class="form-control" required>
                            </div>
                            <div class="col-md-2">
                                <label>Unit Cost</label>
                                <input type="number" step="0.01" name="products[0][unit_cost]" class="form-control" required>
                            </div>
                            <div class="col-md-2">
                                <label>Expiry Date</label>
                                <input type="date" name="products[0][expiry_date]" class="form-control" required>
                            </div>
                            <div class="col-md-2">
                                <label>Batch No</label>
                                <input type="text" name="products[0][batch_no]" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary mt-2" id="addItem">
                        <i class="fas fa-plus"></i> Add Item
                    </button>
                </div>

                <button type="submit" name="add_purchase" class="btn btn-primary mt-3">
                    <i class="fas fa-save"></i> Save Purchase
                </button>
            </form>
        </div>
    </div>

    <!-- Purchase List -->
    <div class="card">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0"><i class="fas fa-list"></i> Purchase History</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Invoice No</th>
                            <th>Supplier</th>
                            <th>Total Amount</th>
                            <th>Recorded By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($purchase = $purchases->fetch_assoc()) { ?>
                            <tr>
                                <td><?= date('d M Y', strtotime($purchase['purchase_date'])) ?></td>
                                <td><?= $purchase['invoice_number'] ?></td>
                                <td><?= $purchase['supplier_name'] ?></td>
                                <td><?= number_format($purchase['total_amount'], 2) ?></td>
                                <td><?= $purchase['user_name'] ?></td>
                                <td>
                                    <a href="view_purchase.php?id=<?= $purchase['purchase_id'] ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Add new item row
let itemCount = 1;
document.getElementById('addItem').addEventListener('click', () => {
    const newRow = document.querySelector('.item-row').cloneNode(true);
    newRow.innerHTML = newRow.innerHTML.replace(/\[0\]/g, `[${itemCount}]`);
    document.getElementById('purchaseItems').appendChild(newRow);
    itemCount++;
});
</script>

<?php include 'assets/admin_footer.php'; ?>