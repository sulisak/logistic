<style>
.login-card {
    background-color: #ffffff;
    border: 2px solid #1a65de;
    /* Change to your preferred border color */
    border-radius: 8px;
}


.bg-light {
    --bs-bg-opacity: 1;
    background-color: rgb(39 209 108) !important;
}
</style>



<?php
session_start();
require_once 'config/db.php';

$error = '';

// Safe Session Redirect Check
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    $role = isset($_SESSION['role']) ? strtolower(trim($_SESSION['role'])) : '';
    
    if ($role === 'admin') {
        header('Location: index.php');
        exit;
    } elseif ($role === 'driver') {
        header('Location: driver.php');
        exit;
    } else {
        // Destroy corrupted session to prevent infinite redirect loops
        session_unset();
        session_destroy();
        session_start();
    }
}

// Authentication Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $hashed_password = md5($password);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $stmt->execute([$username, $hashed_password]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user_logged_in'] = true;
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = strtolower(trim($user['role']));

        if ($_SESSION['role'] === 'admin') {
            header('Location: index.php');
            exit;
        } elseif ($_SESSION['role'] === 'driver') {
            header('Location: driver.php');
            exit;
        } else {
            $error = "User role not recognized in database.";
        }
    } else {
        $error = "Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logistics Portal Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light d-flex align-items-center" style="min-height: 100vh;">
    <div class="container" style="max-width: 400px;">
        <!-- Changed border-0 to border and added border-primary (or use style="border: 2px solid #ffffff;") -->
        <div class="card shadow border border-primary">
            <div class="card-body p-4">
                <h4 class="card-title text-center mb-4 text-primary">Logistics Portal</h4>

                <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" action="login.php">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Username</label>
                        <input type="text" name="username" class="form-control" placeholder="admin or driver1" required
                            autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Sign In</button>
                </form>

                <div class="text-center mt-3">
                    <a href="track.php" class="text-decoration-none small text-secondary">Go to Public Package Tracker
                        →</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>