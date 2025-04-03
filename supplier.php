<?php
require_once 'auth.php';
require_once 'config.php';
requireRole('admin');

// Handle supplier actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_supplier'])) {
        $name = $conn->real_escape_string($_POST['name']);
        $contact_person = $conn->real_escape_string($_POST['contact_person']);
        $email = $conn->real_escape_string($_POST['email']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $address = $conn->real_escape_string($_POST['address']);

        $stmt = $conn->prepare("INSERT INTO suppliers 
            (name, contact_person, email, phone, address) 
            VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $contact_person, $email, $phone, $address);
        $stmt->execute();
    } elseif (isset($_POST['delete_supplier'])) {
        $supplier_id = intval($_POST['supplier_id']);
        $conn->query("DELETE FROM suppliers WHERE supplier_id = $supplier_id");
    }
}

// Fetch suppliers
$suppliers = $conn->query("SELECT * FROM suppliers ORDER BY name");
?>

<?php include 'assets/admin_header.php'; ?>

<div class="container mt-4">
    <h2><i class="fas fa-truck"></i> Supplier Management</h2>
    
    <!-- Add Supplier Form -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-plus-circle"></i> Add New Supplier</h4>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label>Supplier Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label>Contact Person</label>
                        <input type="text" name="contact_person" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="col-12">
                        <label>Address</label>
                        <textarea name="address" class="form-control"></textarea>
                    </div>
                </div>
                <button type="submit" name="add_supplier" class="btn btn-primary mt-3">
                    <i class="fas fa-save"></i> Add Supplier
                </button>
            </form>
        </div>
    </div>

    <!-- Supplier List -->
    <div class="card">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0"><i class="fas fa-list"></i> Supplier List</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Contact Person</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($supplier = $suppliers->fetch_assoc()) { ?>
                            <tr>
                                <td><?= $supplier['name'] ?></td>
                                <td><?= $supplier['contact_person'] ?></td>
                                <td><?= $supplier['email'] ?></td>
                                <td><?= $supplier['phone'] ?></td>
                                <td>
                                    <button class="btn btn-sm btn-danger" 
                                        onclick="confirmDelete(<?= $supplier['supplier_id'] ?>)">
                                        <i class="fas fa-trash"></i> Delete
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
function confirmDelete(supplier_id) {
    if (confirm('Are you sure you want to delete this supplier?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `<input type="hidden" name="supplier_id" value="${supplier_id}">
                          <input type="hidden" name="delete_supplier">`;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include 'assets/admin_footer.php'; ?>