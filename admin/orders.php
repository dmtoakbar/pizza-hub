<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('./authentication/authentication.php');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants/constants.php';

include('include/header.php');
include('include/topbar.php');
include('include/sidebar.php');

/* =========================
   STATUS FILTER (SAFE)
========================= */
$status = $_GET['status'] ?? 'pending';

$allowedStatuses = [
    'pending',
    'accepted',
    'preparing',
    'ready',
    'out_for_delivery',
    'delivered',
    'cancelled'
];

if (!in_array($status, $allowedStatuses)) {
    $status = 'pending';
}

/* =========================
   FETCH ORDERS (PREPARED)
========================= */
$stmt = $conn->prepare("
    SELECT 
        id,
        username,
        phone,
        total_amount,
        status,
        created_at
    FROM orders
    WHERE status = ?
    ORDER BY created_at DESC
");
$stmt->bind_param("s", $status);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="content-wrapper">

    <!-- PAGE HEADER -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Orders</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active"><?= ucfirst(str_replace('_', ' ', $status)); ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- CONTENT -->
    <div class="container-fluid">
        <?php include('./message/message.php'); ?>

        <div class="card">
            <div class="card-header">
                <div class="d-flex flex-wrap gap-2">

                    <a href="orders.php?status=pending" class="btn btn-primary flex-fill">🆕 New</a>
                    <a href="orders.php?status=accepted" class="btn btn-info flex-fill">Accepted</a>
                    <a href="orders.php?status=preparing" class="btn btn-warning flex-fill">👨‍🍳 Preparing</a>
                    <a href="orders.php?status=ready" class="btn btn-secondary flex-fill">📦 Ready</a>
                    <a href="orders.php?status=out_for_delivery" class="btn btn-info flex-fill">🚴 Out for delivery</a>
                    <a href="orders.php?status=delivered" class="btn btn-success flex-fill">✅ Delivered</a>
                    <a href="orders.php?status=cancelled" class="btn btn-danger flex-fill">❌ Cancelled</a>

                </div>
            </div>

            <!-- TABLE -->
            <div class="card-body">
                <table id="example1" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php if ($result->num_rows > 0): ?>
                            <?php $i = 1; ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $i++; ?></td>
                                    <td><?= htmlspecialchars($row['id']); ?></td>
                                    <td><?= htmlspecialchars($row['username']); ?></td>
                                    <td><?= htmlspecialchars($row['phone']); ?></td>
                                    <td>$<?= number_format($row['total_amount'], 2); ?></td>
                                    <td>
                                        <span class="badge badge-dark">
                                            <?= ucfirst(str_replace('_', ' ', $row['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?= date('d M Y, h:i A', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <a href="order-detail.php?order_id=<?= urlencode($row['id']); ?>"
                                           class="btn btn-sm btn-info">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    No orders found
                                </td>
                            </tr>
                        <?php endif; ?>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$stmt->close();
include('include/script.php');
include('include/footer.php');
?>

<script>
    // auto refresh every 30 sec
    setTimeout(() => {
        location.reload();
    }, 30000);
</script>
