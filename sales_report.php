<?php
require_once 'auth.php';
require_once 'config.php';
requireRole('admin');

// Handle date filtering
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');

// Fetch sales data
$sales = $conn->query("
    SELECT s.*, u.full_name AS user_name
    FROM sales s
    JOIN users u ON s.user_id = u.user_id
    WHERE s.sale_date BETWEEN '$start_date' AND '$end_date 23:59:59'
    ORDER BY s.sale_date DESC
");

// Calculate totals
$total_sales = $conn->query("
    SELECT SUM(total_amount) AS total
    FROM sales
    WHERE sale_date BETWEEN '$start_date' AND '$end_date 23:59:59'
")->fetch_assoc()['total'] ?? 0;
?>

<?php include 'assets/admin_header.php'; ?>

<div class="container mt-4">
    <h2><i class="fas fa-chart-line"></i> Sales Report</h2>
    
    <!-- Date Filter -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-filter"></i> Filter Report</h4>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-5">
                    <label>Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>">
                </div>
                <div class="col-md-5">
                    <label>End Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>">
                </div>
                <div class="col-md-2">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Sales Summary -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0"><i class="fas fa-chart-bar"></i> Sales Summary</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <p><strong>Total Sales:</strong> <?= number_format($total_sales, 2) ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>Report Period:</strong> <?= date('d M Y', strtotime($start_date)) ?> - <?= date('d M Y', strtotime($end_date)) ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>Total Transactions:</strong> <?= $sales->num_rows ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales List -->
    <div class="card">
        <div class="card-header bg-info text-white">
            <h4 class="mb-0"><i class="fas fa-list"></i> Sales Details</h4>
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
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'assets/admin_footer.php'; ?>