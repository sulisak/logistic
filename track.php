<?php
require_once 'config/db.php';

$shipment = null;
$error = '';
$search_tn = isset($_GET['tracking_number']) ? trim($_GET['tracking_number']) : '';

if ($search_tn !== '') {
    $stmt = $pdo->prepare("SELECT * FROM shipments WHERE tracking_number = ?");
    $stmt->execute([$search_tn]);
    $shipment = $stmt->fetch();

    if (!$shipment) {
        $error = "No package found with tracking number: <strong>" . htmlspecialchars($search_tn) . "</strong>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Public Package Tracker - Logistics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <!-- Public Navigation Header -->
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="track.php">📦 Public Package Tracker</a>
            <a href="login.php" class="btn btn-outline-light btn-sm">Staff Login</a>
        </div>
    </nav>

    <div class="container py-5" style="max-width: 650px;">

        <!-- Tracker Search Card -->
        <div class="card shadow border-0 mb-4">
            <div class="card-body p-4 text-center">
                <h4 class="fw-bold mb-2 text-primary">Track Your Package</h4>
                <p class="text-muted small mb-4">Enter your tracking number below to view your real-time status.</p>

                <form method="GET" action="track.php" class="d-flex gap-2">
                    <input type="text" name="tracking_number" class="form-control form-control-lg text-center fw-bold"
                        placeholder="e.g. TRK12345678" value="<?php echo htmlspecialchars($search_tn); ?>" required>
                    <button type="submit" class="btn btn-primary btn-lg px-4">Track</button>
                </form>
            </div>
        </div>

        <!-- Error Notification -->
        <?php if (!empty($error)): ?>
        <div class="alert alert-danger shadow-sm text-center py-3"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Read-Only Results Display -->
        <?php if ($shipment): ?>
        <?php $total_item_amount = $shipment['weight_kg'] * $shipment['price']; ?>
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-primary text-white p-3 d-flex justify-content-between align-items-center">
                <div>
                    <small class="d-block text-white-50">Tracking Number</small>
                    <span class="fs-5 fw-bold"><?php echo htmlspecialchars($shipment['tracking_number']); ?></span>
                </div>
                <?php
                    $badge = 'bg-secondary';
                    if ($shipment['status'] === 'Pending') $badge = 'bg-warning text-dark';
                    elseif ($shipment['status'] === 'In Transit') $badge = 'bg-info text-dark';
                    elseif ($shipment['status'] === 'Out for Delivery') $badge = 'bg-primary';
                    elseif ($shipment['status'] === 'Delivered') $badge = 'bg-success';
                ?>
                <span
                    class="badge <?php echo $badge; ?> fs-6 px-3 py-2"><?php echo htmlspecialchars($shipment['status']); ?></span>
            </div>

            <div class="card-body p-4">
                <!-- Visual Progress Bar -->
                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted mb-2">Delivery Status</label>
                    <div class="progress" style="height: 10px;">
                        <?php
                            $progress = 10;
                            if ($shipment['status'] === 'Pending') $progress = 25;
                            elseif ($shipment['status'] === 'In Transit') $progress = 55;
                            elseif ($shipment['status'] === 'Out for Delivery') $progress = 80;
                            elseif ($shipment['status'] === 'Delivered') $progress = 100;
                        ?>
                        <div class="progress-bar <?php echo ($shipment['status'] === 'Delivered') ? 'bg-success' : 'bg-primary'; ?> progress-bar-striped progress-bar-animated"
                            role="progressbar" style="width: <?php echo $progress; ?>%;"></div>
                    </div>
                </div>

                <!-- Shipment Route Information -->
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <div class="p-2 bg-light rounded border">
                            <small class="text-muted d-block">Sender</small>
                            <strong><?php echo htmlspecialchars($shipment['sender']); ?></strong>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 bg-light rounded border">
                            <small class="text-muted d-block">Receiver</small>
                            <strong><?php echo htmlspecialchars($shipment['receiver']); ?></strong>
                        </div>
                    </div>
                </div>

                <div class="bg-light p-3 rounded border mb-3">
                    📍 <strong>Destination:</strong> <span
                        class="text-dark fw-bold"><?php echo htmlspecialchars($shipment['destination']); ?></span>
                </div>

                <!-- Read-Only Calculations -->
                <div class="row g-2 text-center">
                    <div class="col-4">
                        <div class="bg-white p-2 rounded border">
                            <small class="text-muted d-block">Weight</small>
                            <strong><?php echo number_format($shipment['weight_kg'], 2); ?> kg</strong>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-white p-2 rounded border">
                            <small class="text-muted d-block">Rate</small>
                            <strong>$<?php echo number_format($shipment['price'], 2); ?></strong>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-white p-2 rounded border">
                            <small class="text-muted d-block">Total Amount</small>
                            <strong class="text-success">$<?php echo number_format($total_item_amount, 2); ?></strong>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-3 pt-2 border-top">
                    <small class="text-muted">Last System Update:
                        <?php echo htmlspecialchars($shipment['updated_at']); ?></small>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>
</body>

</html>