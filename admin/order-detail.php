<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('./authentication/authentication.php');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants/constants.php';
include('codeLogic/orders/order-functions.php');

include('include/header.php');
include('include/topbar.php');
include('include/sidebar.php');

/* =========================
   LOAD ORDER
========================= */
$orderId = $_GET['order_id'] ?? '';
$order = getOrder($conn, $orderId);

if (!$order) {
    die('Order not found');
}

$items = getOrderItems($conn, $orderId);

/* =========================
   HELPERS
========================= */
$statuses = [
    'pending',
    'accepted',
    'preparing',
    'ready',
    'out_for_delivery',
    'delivered',
    'cancelled'
];

function statusBadge($status)
{
    return match ($status) {
        'pending' => 'secondary',
        'accepted' => 'info',
        'preparing' => 'warning',
        'ready' => 'primary',
        'out_for_delivery' => 'info',
        'delivered' => 'success',
        'cancelled' => 'danger',
        default => 'dark'
    };
}

function formatPizzaSize(string $size): string
{
    return match (strtoupper($size)) {
        'S' => 'Small',
        'M' => 'Medium',
        'L' => 'Large',
        default => ucfirst($size),
    };
}
?>

<div class="content-wrapper">
<div class="container-fluid">

<?php include('./message/message.php'); ?>

<!-- ================= HEADER ================= -->
<div class="row mb-3">
    <div class="col-md-8">
        <h3>
            🧾 Order #<?= substr($order['id'], 0, 8); ?>
            <span class="badge badge-<?= statusBadge($order['status']); ?>">
                <?= ucfirst(str_replace('_', ' ', $order['status'])); ?>
            </span>
        </h3>
        <small class="text-muted">
            Placed on <?= date('d M Y, h:i A', strtotime($order['created_at'])); ?>
        </small>
    </div>

    <div class="col-md-4 text-right">
        <h4 class="text-success">$<?= number_format($order['total_amount'], 2); ?></h4>
        <span class="badge badge-<?= $order['payment_status'] === 'paid' ? 'success' : 'danger'; ?>">
            <?= strtoupper($order['payment_status']); ?>
        </span>
    </div>
</div>

<!-- ================= CUSTOMER + CONTROLS ================= -->
<div class="row">

<div class="col-md-6">
    <div class="card card-outline card-primary">
        <div class="card-header"><strong>Customer Details</strong></div>
        <div class="card-body">
            <p><strong>Name:</strong> <?= htmlspecialchars($order['username']); ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($order['phone']); ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($order['email']); ?></p>
            <p><strong>Address:</strong><br>
                <?= nl2br(htmlspecialchars($order['address'])); ?>
            </p>
        </div>
    </div>
</div>

<div class="col-md-6">
    <div class="card card-outline card-info">
        <div class="card-header"><strong>Order Controls</strong></div>
        <div class="card-body">

            <label>Status</label>
            <select id="orderStatus" class="form-control mb-3">
                <?php foreach ($statuses as $s): ?>
                    <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>>
                        <?= ucfirst(str_replace('_', ' ', $s)); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Payment Status</label>
            <select id="paymentStatus" class="form-control">
                <option value="unpaid" <?= $order['payment_status'] === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
                <option value="paid" <?= $order['payment_status'] === 'paid' ? 'selected' : '' ?>>Paid</option>
            </select>

        </div>
    </div>
</div>

</div>

<!-- ================= ORDER ITEMS ================= -->
<div class="card mt-3">
<div class="card-header bg-light">
    <h5 class="mb-0">
        🍕 Ordered Items
        <span class="badge badge-primary ml-2"><?= $items->num_rows; ?></span>
    </h5>
</div>

<div class="card-body p-0">
<table class="table table-hover table-striped mb-0">
<thead class="thead-dark">
<tr>
    <th>#</th>
    <th>Pizza</th>
    <th>Base Price</th>
    <th>Discount</th>
    <th>Final Price</th>
    <th>Qty</th>
    <th>Extras</th>
    <th>Size</th>
    <th>Subtotal</th>
</tr>
</thead>
<tbody>

<?php
$i = 1;
$grandTotal = 0;

while ($item = $items->fetch_assoc()):
    $basePrice   = (float)$item['base_price'];
    $finalPrice = (float)$item['final_price'];
    $quantity   = (int)$item['quantity'];
    $discount   = (float)$item['discount_percentage'];

    $extras = getItemExtras($conn, $item['id']);
    $extraTotal = 0;
?>
<tr>
<td><?= $i++; ?></td>

<td>
    <div class="d-flex align-items-center">
        <img src="<?= $item['product_image']; ?>" width="60" height="50"
             class="rounded mr-2" style="object-fit:cover">
        <strong><?= htmlspecialchars($item['product_name']); ?></strong>
    </div>
</td>

<td>$<?= number_format($basePrice, 2); ?></td>

<td>
    <?= $discount > 0 ? $discount . '%' : '—'; ?>
</td>

<td>
    <strong>$<?= number_format($finalPrice, 2); ?></strong>
</td>

<td>
    <span class="badge badge-info"><?= $quantity; ?></span>
</td>

<td>
<?php
if ($extras->num_rows === 0) {
    echo '<span class="text-muted">—</span>';
}
while ($ex = $extras->fetch_assoc()):
    $extraTotal += (float)$ex['extra_price'];
?>
<span class="badge badge-light border mr-1">
    <?= $ex['extra_name']; ?>
    (+$<?= number_format($ex['extra_price'], 2); ?>)
</span>
<?php endwhile; ?>
</td>

<td>
<span class="badge badge-secondary">
    <?= formatPizzaSize($item['size']); ?>
</span>
</td>

<td>
<?php
$subTotal = ($finalPrice * $quantity) + $extraTotal;
$grandTotal += $subTotal;
?>
<strong>$<?= number_format($subTotal, 2); ?></strong>
</td>
</tr>

<?php endwhile; ?>

</tbody>
</table>
</div>

<div class="card-footer text-right">
<h5>
    Grand Total:
    <span class="text-success">
        $<?= number_format($grandTotal, 2); ?>
    </span>
</h5>
</div>

</div>

</div>
</div>

<?php include('include/script.php'); ?>

<script>
$('#orderStatus').on('change', function () {
    $.post(
        'codeLogic/orders/updateOrderStatus/update-order-status.php',
        { id: '<?= $orderId ?>', status: $(this).val() },
        res => JSON.parse(res).success && location.reload()
    );
});

$('#paymentStatus').on('change', function () {
    $.post(
        'codeLogic/orders/updatePaymentStatus/update-payment-status.php',
        { id: '<?= $orderId ?>', payment_status: $(this).val() },
        res => JSON.parse(res).success && location.reload()
    );
});
</script>

<?php include('include/footer.php'); ?>
