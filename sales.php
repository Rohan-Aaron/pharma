<?php
require_once 'auth.php';
require_once 'config.php';

// Handle sale actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_sale'])) {
        $customer_name = $conn->real_escape_string($_POST['customer_name']);
        $payment_method = $conn->real_escape_string($_POST['payment_method']);
        $total_amount = 0;

        // Insert sale
        $stmt = $conn->prepare("INSERT INTO sales 
            (user_id, customer_name, payment_method) 
            VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $_SESSION['user_id'], $customer_name, $payment_method);
        $stmt->execute();
        $sale_id = $stmt->insert_id;

        // Insert sale items
        foreach ($_POST['products'] as $product) {
            $product_id = intval($product['product_id']);
            $quantity = intval($product['quantity']);
            $unit_price = floatval($product['unit_price']);

            $stmt = $conn->prepare("INSERT INTO sale_items 
                (sale_id, product_id, quantity, unit_price) 
                VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiid", $sale_id, $product_id, $quantity, $unit_price);
            $stmt->execute();

            $total_amount += $quantity * $unit_price;
        }

        // Update total amount
        $conn->query("UPDATE sales SET total_amount = $total_amount 
                      WHERE sale_id = $sale_id");

        $_SESSION['success'] = "Sale recorded successfully!";
        header("Location: sales.php");
        exit();
    }
}

// Fetch data
$sales = $conn->query("
    SELECT s.*, u.full_name AS user_name
    FROM sales s
    JOIN users u ON s.user_id = u.user_id
    ORDER BY s.sale_date DESC
");

$products = $conn->query("SELECT * FROM products WHERE deleted_at IS NULL");
?>

<?php include 'assets/admin_header.php'; ?>

<div class="container mt-4">
    <h2><i class="fas fa-cash-register"></i> Manage Sales</h2>
    
    <!-- Add Sale Form -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-plus-circle"></i> Add New Sale</h4>
        </div>
        <div class="card-body">
            <form method="POST" id="saleForm">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label>Customer Name</label>
                        <input type="text" name="customer_name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label>Payment Method</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="mobile_money">Mobile Money</option>
                        </select>
                    </div>
                </div>

                <!-- Sale Items -->
                <div class="mt-4">
                    <h5>Sale Items</h5>
                    <div id="saleItems">
                        <div class="row g-3 item-row">
                            <div class="col-md-6">
                                <label>Product</label>
                                <select name="products[0][product_id]" class="form-select product-select" required>
                                    <?php while($product = $products->fetch_assoc()) { ?>
                                        <option value="<?= $product['product_id'] ?>"><?= $product['name'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Quantity</label>
                                <input type="number" name="products[0][quantity]" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label>Unit Price</label>
                                <input type="number" step="0.01" name="products[0][unit_price]" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary mt-2" id="addItem">
                        <i class="fas fa-plus"></i> Add Item
                    </button>
                </div>

                <button type="submit" name="add_sale" class="btn btn-primary mt-3">
                    <i class="fas fa-save"></i> Save Sale
                </button>
            </form>
        </div>
    </div>

    <!-- Sale List -->
    <div class="card">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0"><i class="fas fa-list"></i> Sales History</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Total Amount</th>
                            <th>Payment Method</th>
                            <th>Recorded By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($sale = $sales->fetch_assoc()) { ?>
                            <tr>
                                <td><?= date('d M Y H:i', strtotime($sale['sale_date'])) ?></td>
                                <td><?= $sale['customer_name'] ?></td>
                                <td><?= number_format($sale['total_amount'], 2) ?></td>
                                <td><?= ucfirst($sale['payment_method']) ?></td>
                                <td><?= $sale['user_name'] ?></td>
                                <td>
                                    <a href="view_sale.php?id=<?= $sale['sale_id'] ?>" class="btn btn-sm btn-info">
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
    document.getElementById('saleItems').appendChild(newRow);
    itemCount++;
});
</script>

<?php include 'assets/admin_footer.php'; ?>