<?php
session_start();

// Security Guard: Authentication & Role Check
if (!isset($_SESSION['user_logged_in'])) {
    header('Location: login.php');
    exit;
}

if ($_SESSION['role'] === 'admin') {
    header('Location: index.php');
    exit;
}

require_once 'config/db.php';

$message = '';
$error = '';

// Handle Status Update Action (Backend Lock Check)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = $_POST['id'];
    $new_status = $_POST['status'];
    $tracking = $_POST['tracking_number'];

    // Check if package is already delivered
    $chkStmt = $pdo->prepare("SELECT status FROM shipments WHERE id = ?");
    $chkStmt->execute([$id]);
    $current = $chkStmt->fetchColumn();

    if ($current === 'Delivered') {
        $error = "Tracking <strong>" . htmlspecialchars($tracking) . "</strong> has already been delivered and cannot be modified.";
    } else {
        $stmt = $pdo->prepare("UPDATE shipments SET status = ? WHERE id = ?");
        if ($stmt->execute([$new_status, $id])) {
            $message = "Tracking <strong>" . htmlspecialchars($tracking) . "</strong> updated to <strong>" . htmlspecialchars($new_status) . "</strong>";
        } else {
            $error = "Failed to update shipment status.";
        }
    }
}

// Search Filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($search !== '') {
    $stmt = $pdo->prepare("SELECT * FROM shipments WHERE (tracking_number LIKE ? OR destination LIKE ? OR receiver LIKE ? OR sender LIKE ?) ORDER BY id DESC");
    $term = "%" . $search . "%";
    $stmt->execute([$term, $term, $term, $term]);
    $shipments = $stmt->fetchAll();
} else {
    $shipments = $pdo->query("SELECT * FROM shipments WHERE status != 'Delivered' ORDER BY id DESC")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Dispatch Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-3" style="max-width: 600px;">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-0 text-primary">🚚 Driver Dispatch</h4>
                <small class="text-muted">Driver:
                    <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></small>
            </div>
            <div class="d-flex gap-1">
                <a href="track.php" target="_blank" class="btn btn-outline-info btn-sm">Tracker</a>
                <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
            </div>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show py-2"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show py-2"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card shadow-sm mb-3">
            <div class="card-body p-2">
                <form method="GET" action="driver.php" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control"
                        placeholder="Search Tracking #, Destination..."
                        value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <?php if ($search !== ''): ?>
                    <a href="driver.php" class="btn btn-outline-secondary">Reset</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-bold mb-0 text-secondary">
                <?php echo ($search !== '') ? 'Search Results' : 'Active Deliveries'; ?>
                (<?php echo count($shipments); ?>)
            </h6>
        </div>

        <?php if (count($shipments) > 0): ?>
        <?php foreach ($shipments as $s): ?>
        <?php $total_item_amount = $s['weight_kg'] * $s['price']; ?>
        <div
            class="card shadow-sm mb-3 border-start border-4 <?php echo ($s['status'] === 'Delivered') ? 'border-success' : 'border-primary'; ?>">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="fw-bold text-primary fs-5"><?php echo $s['tracking_number']; ?></span>
                        <div class="text-muted small">Receiver:
                            <strong><?php echo htmlspecialchars($s['receiver']); ?></strong>
                        </div>
                    </div>
                    <?php
                            $badge = 'bg-secondary';
                            if ($s['status'] === 'Pending') $badge = 'bg-warning text-dark';
                            elseif ($s['status'] === 'In Transit') $badge = 'bg-info text-dark';
                            elseif ($s['status'] === 'Out for Delivery') $badge = 'bg-primary';
                            elseif ($s['status'] === 'Delivered') $badge = 'bg-success';
                        ?>
                    <span class="badge <?php echo $badge; ?> fs-6"><?php echo $s['status']; ?></span>
                </div>

                <div class="bg-light p-2 rounded mb-2 border">
                    📍 <strong>Destination:</strong> <span
                        class="text-dark fw-bold"><?php echo htmlspecialchars($s['destination']); ?></span>
                </div>

                <div class="row g-2 mb-3 text-center">
                    <div class="col-4">
                        <div class="bg-white p-2 rounded border small">
                            <span class="text-muted d-block">Weight</span>
                            <strong><?php echo number_format($s['weight_kg'], 2); ?> kg</strong>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-white p-2 rounded border small">
                            <span class="text-muted d-block">Rate</span>
                            <strong>$<?php echo number_format($s['price'], 2); ?></strong>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-white p-2 rounded border small">
                            <span class="text-muted d-block">Total Amount</span>
                            <strong class="text-success">$<?php echo number_format($total_item_amount, 2); ?></strong>
                        </div>
                    </div>
                </div>

                <!-- Lock Action Buttons if Status is Delivered -->
                <?php if ($s['status'] === 'Delivered'): ?>
                <button class="btn btn-sm btn-success w-100 fw-bold" disabled>
                    🔒 Delivered & Locked
                </button>
                <?php else: ?>
                <form method="POST"
                    action="driver.php<?php echo $search !== '' ? '?search=' . urlencode($search) : ''; ?>"
                    class="d-flex gap-2 flex-wrap">
                    <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                    <input type="hidden" name="tracking_number" value="<?php echo $s['tracking_number']; ?>">
                    <input type="hidden" name="update_status" value="1">

                    <button type="submit" name="status" value="In Transit"
                        class="btn btn-sm btn-outline-warning text-dark flex-fill"
                        <?php echo ($s['status'] === 'In Transit') ? 'disabled' : ''; ?>>
                        🚚 In Transit
                    </button>
                    <button type="submit" name="status" value="Out for Delivery"
                        class="btn btn-sm btn-outline-info text-dark flex-fill"
                        <?php echo ($s['status'] === 'Out for Delivery') ? 'disabled' : ''; ?>>
                        📍 Out for Delivery
                    </button>
                    <button type="submit" name="status" value="Delivered"
                        class="btn btn-sm btn-success flex-fill fw-bold">
                        ✅ Delivered
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <div class="card shadow-sm p-4 text-center text-muted">
            No active shipments found.
        </div>
        <?php endif; ?>

    </div>
</body>

</html>