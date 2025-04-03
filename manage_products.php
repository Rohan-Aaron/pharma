<?php
require_once 'auth.php';
require_once 'config.php';
requireRole('admin');

// Handle product actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        $name = $conn->real_escape_string($_POST['name']);
        $category_id = intval($_POST['category_id']);
        $sku = $conn->real_escape_string($_POST['sku']);
        $cost_price = floatval($_POST['cost_price']);
        $selling_price = floatval($_POST['selling_price']);
        $stock = intval($_POST['stock']);
        $expiry = $_POST['expiry_date'];
        $rack = $conn->real_escape_string($_POST['rack']);
        $shelf = $conn->real_escape_string($_POST['shelf']);

        $stmt = $conn->prepare("INSERT INTO products 
            (name, category_id, sku, cost_price, selling_price, stock_quantity, 
             expiry_date, rack_number, shelf_number) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sisddiss", $name, $category_id, $sku, $cost_price, 
                        $selling_price, $stock, $expiry, $rack, $shelf);
        $stmt->execute();
    }
}

// Fetch data with corrected column name
$products = $conn->query("
    SELECT p.*, c.name AS category_name 
    FROM products p
    JOIN categories c ON p.category_id = c.category_id
    WHERE p.deleted_at IS NULL
");

$categories = $conn->query("SELECT * FROM categories");
?>

<?php include 'assets/admin_header.php'; ?>

<div class="container mt-4">
    <h2><i class="fas fa-pills"></i> Manage Pharmaceutical Products</h2>
    
    <!-- Add Product Card -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-plus-circle"></i> Add New Medicine</h4>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label>Medicine Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label>Category</label>
                        <select name="category_id" class="form-select" required>
                            <?php while($cat = $categories->fetch_assoc()) { ?>
                                <option value="<?= $cat['category_id'] ?>"><?= $cat['name'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>SKU Code</label>
                        <input type="text" name="sku" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label>Cost Price</label>
                        <input type="number" step="0.01" name="cost_price" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label>Selling Price</label>
                        <input type="number" step="0.01" name="selling_price" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label>Initial Stock</label>
                        <input type="number" name="stock" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label>Expiry Date</label>
                        <input type="date" name="expiry_date" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label>Rack Number</label>
                        <input type="text" name="rack" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label>Shelf Number</label>
                        <input type="text" name="shelf" class="form-control" required>
                    </div>
                </div>
                <button type="submit" name="add_product" class="btn btn-primary mt-3">
                    <i class="fas fa-save"></i> Add Medicine
                </button>
            </form>
        </div>
    </div>

    <!-- Product List -->
    <div class="card">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0"><i class="fas fa-capsules"></i> Current Inventory</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Medicine Name</th>
                            <th>Category</th>
                            <th>SKU</th>
                            <th>Stock</th>
                            <th>Expiry</th>
                            <th>Location</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($product = $products->fetch_assoc()) { ?>
                            <tr>
                                <td><?= htmlspecialchars($product['name']) ?></td>
                                <td><?= $product['category_name'] ?></td>
                                <td><?= $product['sku'] ?></td>
                                <td class="<?= $product['stock'] < 50 ? 'text-danger fw-bold' : '' ?>">
                                    <?= $product['stock'] ?>
                                </td>
                                <td><?= date('M Y', strtotime($product['expiry_date'])) ?></td>
                                <td>Rack <?= $product['rack_number'] ?> / Shelf <?= $product['shelf_number'] ?></td>
                                <td>
                                    <a href="edit_product.php?id=<?= $product['product_id'] ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-sm btn-danger" 
                                        onclick="confirmDelete(<?= $product['product_id'] ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
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
function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this medicine?\n\nThis will not affect existing sales records.')) {
        window.location.href = 'delete_product.php?id=' + id;
    }
}
</script>

<?php include 'assets/admin_footer.php'; ?>