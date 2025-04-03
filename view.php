<?php
require_once 'config.php';
require_once 'auth.php';
include 'assets/admin_header.php'; ?>

<div class="container table-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Medicine Inventory</h2>
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="add_medicine.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Medicine
            </a>
        <?php endif; ?>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Medicine Name</th>
                        <th>Generic Name</th>
                        <th>Dosage</th>
                        <th>Form</th>
                        <th>Stock</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        // Get medicines with inventory details
                        $stmt = $conn->prepare("
                                SELECT m.*, 
                                    SUM(i.quantity) AS total_stock,
                                    COUNT(i.inventory_id) AS batch_count,
                                    MIN(i.expiry_date) AS earliest_expiry
                                FROM medications m
                                LEFT JOIN inventory i ON m.medication_id = i.medication_id
                                WHERE m.is_active = 1
                                GROUP BY m.medication_id
                                ORDER BY m.drug_name
                            ");
                        $stmt->execute();
                        $result = $stmt->get_result();
                        if ($result->num_rows === 0) {
                            echo "<tr><td colspan='7' class='text-center'>No medicines found</td></tr>";
                        }else{
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td>" . htmlspecialchars($row['drug_name']) . "</td>
                                    <td>" . htmlspecialchars($row['generic_name']) . "</td>
                                    <td>" . htmlspecialchars($row['dosage']) . "</td>
                                    <td>" . htmlspecialchars($row['form']) . "</td>
                                    <td>
                                        " . number_format($row['total_stock']) . "<br>
                                        <small class='text-muted'>{$row['batch_count']} batches</small>
                                    </td>
                                    <td>
                                        <span class='badge bg-success'>
                                            ₹" . number_format($row['selling_price'], 2) . "
                                        </span>
                                    </td>
                                    <td class='action-btns'>";

                            if ($_SESSION['role'] === 'admin') {
                                echo "<a href='edit_medicine.php?id={$row['medication_id']}' 
                                           class='btn btn-sm btn-warning'>
                                            <i class='fas fa-edit'></i>
                                        </a>
                                        <button class='btn btn-sm btn-danger delete-btn' 
                                                data-id='{$row['medication_id']}'>
                                            <i class='fas fa-trash'></i>
                                        </button>";
                            }

                            echo "<a href='medicine_details.php?id={$row['medication_id']}' 
                                       class='btn btn-sm btn-info'>
                                        <i class='fas fa-info-circle'></i>
                                    </a>
                                    </td>
                                </tr>";
                        }
                    }
                    } catch (Exception $e) {
                        echo "<tr><td colspan='7' class='text-danger text-center'>
                                    Error loading data: " . htmlspecialchars($e->getMessage()) . "
                                  </td></tr>";
                    }
                    
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this medicine? All inventory records will be archived.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="main.js"></script>
</body>

</html>