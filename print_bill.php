<?php
session_start();

if (!isset($_SESSION['user_logged_in'])) {
    header('Location: login.php');
    exit;
}

require_once 'config/db.php';

$tracking = isset($_GET['tracking']) ? trim($_GET['tracking']) : '';

if (empty($tracking)) {
    die("Invalid tracking number.");
}

$stmt = $pdo->prepare("SELECT * FROM shipments WHERE tracking_number = ?");
$stmt->execute([$tracking]);
$shipment = $stmt->fetch();

if (!$shipment) {
    die("Shipment not found.");
}

$total_amount = $shipment['weight_kg'] * $shipment['price'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Shipment Bill - <?php echo htmlspecialchars($shipment['tracking_number']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    @media print {
        .no-print {
            display: none !important;
        }

        body {
            background: white !important;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
        }
    }
    </style>
</head>

<body class="bg-white py-5" onload="window.print()">

    <div class="container" style="max-width: 600px;">
        <div class="card border p-4 shadow-sm">
            <div class="text-center mb-4">
                <h3 class="fw-bold">📦 Logistics Express</h3>
                <p class="text-muted mb-0">Official Shipment Receipt & Bill</p>
                <hr>
            </div>

            <div class="row mb-3">
                <div class="col-6">
                    <span class="text-muted small">Tracking Number:</span>
                    <h5 class="fw-bold text-primary"><?php echo htmlspecialchars($shipment['tracking_number']); ?></h5>
                </div>
                <div class="col-6 text-end">
                    <span class="text-muted small">Date:</span>
                    <p class="mb-0 fw-semibold"><?php echo date('Y-m-d H:i'); ?></p>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-6">
                    <h6 class="fw-bold text-secondary">Sender Details</h6>
                    <p class="mb-0"><?php echo htmlspecialchars($shipment['sender']); ?></p>
                </div>
                <div class="col-6">
                    <h6 class="fw-bold text-secondary">Receiver Details</h6>
                    <p class="mb-0"><?php echo htmlspecialchars($shipment['receiver']); ?></p>
                    <p class="text-muted small mb-0">Destination:
                        <?php echo htmlspecialchars($shipment['destination']); ?></p>
                </div>
            </div>

            <table class="table table-bordered mb-4">
                <thead class="table-light">
                    <tr>
                        <th>Description</th>
                        <th class="text-center">Weight</th>
                        <th class="text-end">Rate</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Standard Cargo Shipment</td>
                        <td class="text-center"><?php echo number_format($shipment['weight_kg'], 2); ?> kg</td>
                        <td class="text-end">$<?php echo number_format($shipment['price'], 2); ?></td>
                        <td class="text-end fw-bold">$<?php echo number_format($total_amount, 2); ?></td>
                    </tr>
                </tbody>
            </table>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <span class="text-muted">Current Status:</span>
                <span class="badge bg-secondary"><?php echo htmlspecialchars($shipment['status']); ?></span>
            </div>

            <div class="text-center text-muted small mt-4">
                <p>Thank you for shipping with Logistics Express!<br>Track your package anytime using your tracking
                    number.</p>
            </div>

            <div class="text-center no-print mt-4">
                <button onclick="window.print()" class="btn btn-primary">Print Again</button>
                <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>
    </div>

</body>

</html>