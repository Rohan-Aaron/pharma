<?php
require_once 'config.php';
require_once 'auth.php';

// Only admin can add medicines
if ($_SESSION['role'] !== 'admin') {
    header("Location: unauthorized.php");
    exit();
}
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Generate a secure token
}
// Helper function to maintain form state
function selected($field, $value)
{
    return (isset($_POST[$field]) && $_POST[$field] === $value ? 'selected' : '');
}

$error = '';
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid CSRF token!";
    } else {
        // Sanitize and validate inputs
        $drug_name = trim($_POST['drug_name']);
        $dosage = trim($_POST['dosage']);
        $form = $_POST['form'];
        $manufacturer = trim($_POST['manufacturer']);

        // Validate required fields
        if (empty($drug_name) || empty($dosage) || empty($form)) {
            $error = "Drug name, dosage, and form are required fields!";
        } elseif (!preg_match('/^[\w\s-]+$/u', $drug_name)) {
            $error = "Invalid characters in drug name!";
        } else {
            try {
                // Insert into database using prepared statement
                $stmt = $conn->prepare("
                    INSERT INTO medications 
                    (drug_name, dosage, form, manufacturer)
                    VALUES (?, ?, ?, ?)
                ");

                $stmt->bind_param(
                    "ssss",
                    $drug_name,
                    $dosage,
                    $form,
                    $manufacturer
                );

                if ($stmt->execute()) {
                    $success = "Medicine added successfully!";
                    // Clear form fields
                    $_POST = array();
                } else {
                    $error = "Error saving medicine: " . $stmt->error;
                }

                $stmt->close();
            } catch (Exception $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}
?>

<?php include 'assets/admin_header.php'; ?>

<div class="container form-container">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Add New Medicine</h3>
        </div>

        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token'] ?>">

                <div class="row g-3">
                    <!-- Drug Name -->
                    <div class="col-md-6">
                        <label class="form-label required">Drug Name</label>
                        <input type="text" class="form-control" name="drug_name"
                            value="<?= htmlspecialchars($_POST['drug_name'] ?? '') ?>" required pattern="[\w\s-]+"
                            maxlength="100">
                        <div class="form-text">Brand name of the medicine</div>
                    </div>

                    <!-- Dosage -->
                    <div class="col-md-4">
                        <label class="form-label required">Dosage</label>
                        <input type="text" class="form-control" name="dosage"
                            value="<?= htmlspecialchars($_POST['dosage'] ?? '') ?>" required
                            pattern="\d+(\.\d+)?[a-zA-Z]*" maxlength="50">
                        <div class="form-text">Example: 500mg, 10ml</div>
                    </div>

                    <!-- Form -->
                    <div class="col-md-4">
                        <label class="form-label required">Form</label>
                        <select class="form-select" name="form" required>
                            <option value="">Select Form</option>
                            <option value="tablet" <?= selected('form', 'tablet') ?>>Tablet</option>
                            <option value="capsule" <?= selected('form', 'capsule') ?>>Capsule</option>
                            <option value="liquid" <?= selected('form', 'liquid') ?>>Liquid</option>
                            <option value="injection" <?= selected('form', 'injection') ?>>Injection</option>
                            <option value="cream" <?= selected('form', 'cream') ?>>Cream</option>
                        </select>
                    </div>

                    <!-- Manufacturer -->
                    <div class="col-md-4">
                        <label class="form-label">Manufacturer</label>
                        <input type="text" class="form-control" name="manufacturer"
                            value="<?= htmlspecialchars($_POST['manufacturer'] ?? '') ?>" maxlength="100">
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Medicine
                    </button>
                    <a href="view.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'assets/admin_footer.php'; ?>