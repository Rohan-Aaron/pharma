<?php
require_once 'auth.php';
require_once 'config.php';
requireRole('admin');

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_user'])) {
        $username = $conn->real_escape_string($_POST['username']);
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $full_name = $conn->real_escape_string($_POST['full_name']);
        $role = $conn->real_escape_string($_POST['role']);

        $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $password, $full_name, $role);
        $stmt->execute();
    } elseif (isset($_POST['delete_user'])) {
        $user_id = intval($_POST['user_id']);
        $conn->query("DELETE FROM users WHERE user_id = $user_id");
    }
}

// Fetch users
$users = $conn->query("SELECT * FROM users ORDER BY role, username");
?>

<?php include 'assets/admin_header.php'; ?>

<div class="container mt-4">
    <h2><i class="fas fa-users-cog"></i> User Management</h2>
    
    <!-- Add User Form -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-user-plus"></i> Add New User</h4>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label>Full Name</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label>Role</label>
                        <select name="role" class="form-select" required>
                            <option value="admin">Admin</option>
                            <option value="pharmacist">Pharmacist</option>
                            <option value="cashier">Cashier</option>
                        </select>
                    </div>
                </div>
                <button type="submit" name="add_user" class="btn btn-primary mt-3">
                    <i class="fas fa-save"></i> Add User
                </button>
            </form>
        </div>
    </div>

    <!-- User List -->
    <div class="card">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0"><i class="fas fa-users"></i> User List</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($user = $users->fetch_assoc()) { ?>
                            <tr>
                                <td><?= $user['username'] ?></td>
                                <td><?= $user['full_name'] ?></td>
                                <td><?= ucfirst($user['role']) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-danger" 
                                        onclick="confirmDelete(<?= $user['user_id'] ?>)">
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
function confirmDelete(user_id) {
    if (confirm('Are you sure you want to delete this user?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `<input type="hidden" name="user_id" value="${user_id}">
                          <input type="hidden" name="delete_user">`;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include 'assets/admin_footer.php'; ?>