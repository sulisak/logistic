<?php
session_start();

// Security Guard: Authentication & Role Check
if (!isset($_SESSION['user_logged_in'])) {
    header('Location: login.php');
    exit;
}

if ($_SESSION['role'] === 'driver') {
    header('Location: driver.php');
    exit;
}

require_once 'config/db.php';

$msg = '';
$error = '';

// Handle Create Shipment Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_shipment'])) {
    $sender = trim($_POST['sender']);
    $receiver = trim($_POST['receiver']);
    $destination = trim($_POST['destination']);
    $weight_kg = !empty($_POST['weight_kg']) ? floatval($_POST['weight_kg']) : 0.00;
    $price = !empty($_POST['price']) ? floatval($_POST['price']) : 0.00;
    $status = $_POST['status'];

    if (!empty($sender) && !empty($receiver) && !empty($destination)) {
        $tracking = 'TRK' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
        
        $stmt = $pdo->prepare("INSERT INTO shipments (tracking_number, sender, receiver, destination, weight_kg, price, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$tracking, $sender, $receiver, $destination, $weight_kg, $price, $status])) {
            $msg = "Shipment created successfully! Tracking Number: <strong>" . htmlspecialchars($tracking) . "</strong>";
        } else {
            $error = "Failed to create shipment.";
        }
    } else {
        $error = "Sender, Receiver, and Destination are required.";
    }
}

// Handle Update Status Request (Backend Lock Check)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];

    // Verify package isn't already Delivered
    $chkStmt = $pdo->prepare("SELECT status FROM shipments WHERE id = ?");
    $chkStmt->execute([$id]);
    $current = $chkStmt->fetchColumn();

    if ($current === 'Delivered') {
        $error = "This shipment is already marked as Delivered and status cannot be changed.";
    } else {
        $stmt = $pdo->prepare("UPDATE shipments SET status = ? WHERE id = ?");
        if ($stmt->execute([$status, $id])) {
            $msg = "Shipment status updated successfully.";
        } else {
            $error = "Failed to update shipment status.";
        }
    }
}

// Handle Tracking ID & Keyword Search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search !== '') {
    $stmt = $pdo->prepare("SELECT * FROM shipments WHERE tracking_number LIKE ? OR sender LIKE ? OR receiver LIKE ? OR destination LIKE ? ORDER BY id DESC");
    $searchTerm = "%" . $search . "%";
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $shipments = $stmt->fetchAll();
} else {
    $shipments = $pdo->query("SELECT * FROM shipments ORDER BY id DESC")->fetchAll();
}

// Calculate Revenue Summary
$total_revenue = 0;
foreach ($shipments as $s) {
    $total_revenue += ($s['weight_kg'] * $s['price']);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Control Panel - Logistics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">📦 Logistics Admin</a>
            <div class="d-flex align-items-center gap-3">
                <span class="text-light">User:
                    <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></span>
                <a href="track.php" target="_blank" class="btn btn-outline-info btn-sm">Public Tracker</a>
                <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">

        <?php if (!empty($msg)): ?>
        <div class="alert alert-success alert-dismissible fade show"><?php echo $msg; ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Summary Metrics Cards -->
        <div class="row mb-4">
            <div class="col-md-6 mb-2">
                <div class="card border-0 shadow-sm bg-primary text-white">
                    <div class="card-body p-3 d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-uppercase fw-bold text-white-50">Total Shipments Listed</small>
                            <h2 class="mb-0 fw-bold"><?php echo count($shipments); ?></h2>
                        </div>
                        <div class="fs-1">📦</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-2">
                <div class="card border-0 shadow-sm bg-success text-white">
                    <div class="card-body p-3 d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-uppercase fw-bold text-white-50">Total Revenue (Weight × Rate)</small>
                            <h2 class="mb-0 fw-bold">$<?php echo number_format($total_revenue, 2); ?></h2>
                        </div>
                        <div class="fs-1">💰</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Shipment Section -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white fw-bold">Create New Shipment</div>
            <div class="card-body">
                <form method="POST" action="index.php" class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Sender</label>
                        <input type="text" name="sender" class="form-control" placeholder="Sender Name" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Receiver</label>
                        <input type="text" name="receiver" class="form-control" placeholder="Receiver Name" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Destination</label>
                        <input type="text" name="destination" class="form-control" placeholder="Destination" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Weight (kg)</label>
                        <input type="number" step="0.01" name="weight_kg" class="form-control" placeholder="0.00">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label fw-semibold">Rate ($/kg)</label>
                        <input type="number" step="0.01" name="price" class="form-control" placeholder="0.00">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Initial Status</label>
                        <select name="status" class="form-select">
                            <option value="Pending">Pending</option>
                            <option value="In Transit">In Transit</option>
                            <option value="Out for Delivery">Out for Delivery</option>
                            <option value="Delivered">Delivered</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" name="create_shipment" class="btn btn-success fw-bold">Save & Generate
                            Tracking</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Active Shipments Table -->
        <div class="card shadow-sm">
            <div
                class="card-header bg-dark text-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold">Shipment Records</span>
                    <span class="badge bg-secondary"><?php echo count($shipments); ?> Found</span>
                </div>

                <form method="GET" action="index.php" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control form-control-sm" style="width: 250px;"
                        placeholder="Search Tracking #, Sender, Destination..."
                        value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-sm btn-primary">Search</button>
                    <?php if ($search !== ''): ?>
                    <a href="index.php" class="btn btn-sm btn-outline-light">Clear</a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Tracking #</th>
                                <th>Sender</th>
                                <th>Receiver</th>
                                <th>Destination</th>
                                <th>Weight</th>
                                <th>Rate ($/kg)</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($shipments) > 0): ?>
                            <?php foreach ($shipments as $s): ?>
                            <?php $total_item_amount = $s['weight_kg'] * $s['price']; ?>
                            <tr>
                                <td>
                                    <a href="track.php?tracking_number=<?php echo $s['tracking_number']; ?>"
                                        target="_blank" class="fw-bold text-decoration-none">
                                        <?php echo $s['tracking_number']; ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($s['sender']); ?></td>
                                <td><?php echo htmlspecialchars($s['receiver']); ?></td>
                                <td><?php echo htmlspecialchars($s['destination']); ?></td>
                                <td><strong><?php echo number_format($s['weight_kg'], 2); ?> kg</strong></td>
                                <td>$<?php echo number_format($s['price'], 2); ?></td>
                                <td><span
                                        class="text-success fw-bold">$<?php echo number_format($total_item_amount, 2); ?></span>
                                </td>
                                <td>
                                    <?php
                                    $badgeClass = 'bg-secondary';
                                    if ($s['status'] === 'Pending') $badgeClass = 'bg-warning text-dark';
                                    elseif ($s['status'] === 'In Transit') $badgeClass = 'bg-info text-dark';
                                    elseif ($s['status'] === 'Out for Delivery') $badgeClass = 'bg-primary';
                                    elseif ($s['status'] === 'Delivered') $badgeClass = 'bg-success';
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo $s['status']; ?></span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">


                                        <?php if ($s['status'] !== 'Delivered'): ?>
                                        <form method="POST"
                                            action="index.php<?php echo $search !== '' ? '?search=' . urlencode($search) : ''; ?>"
                                            class="d-flex gap-1 align-items-center">
                                            <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                                            <input type="hidden" name="update_status" value="1">
                                            <select name="status" class="form-select form-select-sm"
                                                style="width: auto;">
                                                <option value="Pending"
                                                    <?php echo $s['status']=='Pending'?'selected':''; ?>>Pending
                                                </option>
                                                <option value="In Transit"
                                                    <?php echo $s['status']=='In Transit'?'selected':''; ?>>In Transit
                                                </option>
                                                <option value="Out for Delivery"
                                                    <?php echo $s['status']=='Out for Delivery'?'selected':''; ?>>Out
                                                    for
                                                    Delivery</option>
                                                <option value="Delivered">Delivered</option>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                        </form>
                                        <?php endif; ?>

                                        <!-- Print Bill Button -->
                                        <a href="print_bill.php?tracking=<?php echo urlencode($s['tracking_number']); ?>"
                                            target="_blank" class="btn btn-sm btn-outline-secondary">
                                            🖨️ Print Bill
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">No shipments found.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</body>

</html>