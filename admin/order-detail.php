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

$orderId = $_GET['order_id'] ?? '';
$order = getOrder($conn, $orderId);

if (!$order) {
    die("Order not found");
}

$items = getOrderItems($conn, $orderId);

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
?>

<div class="content-wrapper">
    <div class="container-fluid">

        <!-- ================= HEADER ================= -->
        <?php
        include('./message/message.php');
        ?>
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
                <h4 class="text-success">₹<?= number_format($order['total_amount'], 2); ?></h4>
                <span class="badge badge-<?= $order['payment_status'] == 'paid' ? 'success' : 'danger'; ?>">
                    <?= strtoupper($order['payment_status']); ?>
                </span>
            </div>
        </div>

        <!-- ================= CUSTOMER + CONTROLS ================= -->
        <div class="row">

            <!-- CUSTOMER INFO -->
            <div class="col-md-6">
                <div class="card card-outline card-primary">
                    <div class="card-header"><strong>Customer Details</strong></div>
                    <div class="card-body">
                        <p><strong>Name:</strong> <?= htmlspecialchars($order['username']); ?></p>
                        <p><strong>Phone:</strong> <?= $order['phone']; ?></p>
                        <p><strong>Email:</strong> <?= $order['email']; ?></p>
                        <p><strong>Address:</strong><br>
                            <?= nl2br(htmlspecialchars($order['address'])); ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- ORDER CONTROLS -->
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
                            <option value="unpaid" <?= $order['payment_status'] == 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
                            <option value="paid" <?= $order['payment_status'] == 'paid' ? 'selected' : '' ?>>Paid</option>
                        </select>
                    </div>
                </div>
            </div>

        </div>

        <!-- ================= ORDER ITEMS TABLE ================= -->
        <div class="card mt-3">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    🍕 Ordered Items
                    <span class="badge badge-primary ml-2">
                        <?= $items->num_rows; ?> Items
                    </span>
                </h5>
            </div>

            <div class="card-body p-0">
                <table class="table table-hover table-striped mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Pizza</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Extras</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php
                        $i = 1;
                        $grandTotal = 0;
                        while ($item = $items->fetch_assoc()):
                            $itemTotal = $item['product_price'] * $item['quantity'];
                            $extraTotal = 0;
                        ?>
                            <tr>
                                <td><?= $i++; ?></td>

                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?= $item['product_image']; ?>"
                                            width="60"
                                            height="50"
                                            class="rounded mr-2"
                                            style="object-fit:cover">
                                        <strong><?= htmlspecialchars($item['product_name']); ?></strong>
                                    </div>
                                </td>

                                <td>₹<?= number_format($item['product_price'], 2); ?></td>

                                <td>
                                    <span class="badge badge-info">
                                        <?= $item['quantity']; ?>
                                    </span>
                                </td>

                                <td>
                                    <?php
                                    $extras = getItemExtras($conn, $item['id']);
                                    if ($extras->num_rows === 0) {
                                        echo '<span class="text-muted">—</span>';
                                    }
                                    while ($ex = $extras->fetch_assoc()):
                                        $extraTotal += $ex['extra_price'];
                                    ?>
                                        <span class="badge badge-light border mr-1">
                                            <?= $ex['extra_name']; ?> (+₹<?= $ex['extra_price']; ?>)
                                        </span>
                                    <?php endwhile; ?>
                                </td>

                                <td>
                                    <strong>
                                        ₹<?= number_format($itemTotal + $extraTotal, 2); ?>
                                    </strong>
                                </td>
                            </tr>

                            <?php $grandTotal += ($itemTotal + $extraTotal); ?>
                        <?php endwhile; ?>

                    </tbody>
                </table>
            </div>

            <div class="card-footer text-right">
                <h5>
                    Grand Total:
                    <span class="text-success">
                        ₹<?= number_format($grandTotal, 2); ?>
                    </span>
                </h5>
            </div>
        </div>

    </div>
</div>

<?php
include('include/script.php');
?>

<script>
    $(document).ready(function() {


        $('#orderStatus').on('change', function() {

            $.post(
                'codeLogic/orders/updateOrderStatus/update-order-status.php', {
                    id: '<?= $orderId ?>',
                    status: $(this).val()
                },
                function(res) {
                    const r = JSON.parse(res);
                    if (r.success) {

                        location.reload();

                    } else {


                    }
                }
            ).fail(() => {


            });
        });

    });

    $('#paymentStatus').on('change', function() {
        $.post(
            'codeLogic/orders/updatePaymentStatus/update-payment-status.php', {
                id: '<?= $orderId ?>',
                payment_status: $(this).val()
            },
            function(res) {
                const r = JSON.parse(res);
                if (r.success) {
                    location.reload();
                } else {

                }
            }
        ).fail(() => {

        });
    });
</script>


<?php include('include/footer.php'); ?>