<?php
require_once 'auth.php';
require_once 'config.php';

// Ensure only admins can access
requireRole('admin');

// Handle category operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        $name = $conn->real_escape_string($_POST['name']);
        
        // Check for existing category
        $check = $conn->query("SELECT id FROM categories WHERE name = '$name'");
        if ($check->num_rows > 0) {
            $_SESSION['error'] = "Category name already exists!";
        } else {
            $conn->query("INSERT INTO categories (name) VALUES ('$name')");
            $_SESSION['message'] = "Category added successfully!";
        }
        header("Location: manage_categories.php");
        exit();

    } elseif (isset($_POST['edit_category'])) {
        $id = (int)$_POST['id'];
        $name = $conn->real_escape_string($_POST['name']);
        
        // Check for existing category excluding current one
        $check = $conn->query("SELECT id FROM categories WHERE name = '$name' AND id != $id");
        if ($check->num_rows > 0) {
            $_SESSION['error'] = "Category name already exists!";
        } else {
            $conn->query("UPDATE categories SET name = '$name' WHERE id = $id");
            $_SESSION['message'] = "Category updated successfully!";
        }
        header("Location: manage_categories.php");
        exit();
    }
}

if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    $conn->query("DELETE FROM categories WHERE id = $id");
    $_SESSION['message'] = "Category deleted successfully!";
    header("Location: manage_categories.php");
    exit();
}

// Fetch categories
$categories = $conn->query("SELECT * FROM categories ORDER BY category_id")->fetch_all(MYSQLI_ASSOC);
?>

    <?php include 'assets/admin_header.php'; ?>

    <div class="container mt-4">
        <h1 class="mb-4">Manage Categories</h1>

        <!-- Messages -->
        <?php if (isset($_SESSION['message'])) : ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= $_SESSION['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])) : ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Add Category Button -->
        <button class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fas fa-plus-circle"></i> Add Category
        </button>

        <!-- Categories Table -->
        <div class="card shadow">
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Category Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $index=>$cat) : ?>
                            <tr>
                                <td><?=$index+1 ?></td>
                                <td><?= htmlspecialchars($cat['name']) ?></td>
                                <td class="d-flex gap-4">
                                    <button class="btn btn-sm btn-warning me-2" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editModal<?= $cat['category_id'] ?>">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-sm btn-danger" 
                                            onclick="confirmDelete(<?= $cat['category_id'] ?>)">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Category Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="add_category" class="btn btn-primary">
                            Add Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modals -->
    <?php foreach ($categories as $cat) : ?>
    <div class="modal fade" id="editModal<?= $cat['id'] ?>">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Category Name</label>
                            <input type="text" class="form-control" name="name" 
                                   value="<?= htmlspecialchars($cat['name']) ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="edit_category" class="btn btn-primary">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Delete Confirmation Script -->
    <script>
    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this category?\n\nNote: Deleting categories will not affect existing products.')) {
            window.location.href = `manage_categories.php?delete_id=${id}`;
        }
    }
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>