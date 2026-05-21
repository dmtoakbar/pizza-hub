<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('./authentication/authentication.php');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants/constants.php';

require_once __DIR__ . '/../vendor/autoload.php';

use Ramsey\Uuid\Uuid;

include('include/header.php');
include('include/topbar.php');
include('include/sidebar.php');

/* =========================================================
   ADD COUPON
========================================================= */

if (isset($_POST['addCoupon'])) {

    $id = Uuid::uuid4()->toString();

    $code = trim($_POST['code']);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    $offer_type = $_POST['offer_type'];
    $discount_type = $_POST['discount_type'];

    $discount_value = $_POST['discount_value'];
    $max_discount_amount = $_POST['max_discount_amount'];

    $min_order_amount = $_POST['min_order_amount'];

    $usage_limit = $_POST['usage_limit'];
    $usage_per_user = $_POST['usage_per_user'];

    $is_new_user_only = isset($_POST['is_new_user_only']) ? 1 : 0;

    $badge_text = trim($_POST['badge_text']);

    $background_color = trim($_POST['background_color']);

    $button_text = trim($_POST['button_text']);

    $start_date = !empty($_POST['start_date'])
        ? $_POST['start_date']
        : null;

    $end_date = !empty($_POST['end_date'])
        ? $_POST['end_date']
        : null;

    $status = $_POST['status'];

    $stmt = $conn->prepare("
        INSERT INTO coupons (
            id,
            code,
            title,
            description,
            offer_type,
            discount_type,
            discount_value,
            max_discount_amount,
            min_order_amount,
            usage_limit,
            usage_per_user,
            is_new_user_only,
            badge_text,
            background_color,
            button_text,
            start_date,
            end_date,
            status
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )
    ");

    $stmt->bind_param(
        "ssssssdddiiisssssi",
        $id,
        $code,
        $title,
        $description,
        $offer_type,
        $discount_type,
        $discount_value,
        $max_discount_amount,
        $min_order_amount,
        $usage_limit,
        $usage_per_user,
        $is_new_user_only,
        $badge_text,
        $background_color,
        $button_text,
        $start_date,
        $end_date,
        $status
    );

    if ($stmt->execute()) {
        $_SESSION['status'] = "Coupon added successfully!";
    } else {
        $_SESSION['status'] = "Failed to add coupon!";
    }
}

/* =========================================================
   UPDATE COUPON
========================================================= */

if (isset($_POST['updateCoupon'])) {

    $id = $_POST['coupon_id'];

    $code = trim($_POST['code']);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    $offer_type = $_POST['offer_type'];
    $discount_type = $_POST['discount_type'];

    $discount_value = $_POST['discount_value'];
    $max_discount_amount = $_POST['max_discount_amount'];

    $min_order_amount = $_POST['min_order_amount'];

    $usage_limit = $_POST['usage_limit'];
    $usage_per_user = $_POST['usage_per_user'];

    $is_new_user_only = isset($_POST['is_new_user_only']) ? 1 : 0;

    $badge_text = trim($_POST['badge_text']);

    $background_color = trim($_POST['background_color']);

    $button_text = trim($_POST['button_text']);

    $start_date = !empty($_POST['start_date'])
        ? $_POST['start_date']
        : null;

    $end_date = !empty($_POST['end_date'])
        ? $_POST['end_date']
        : null;

    $status = $_POST['status'];

    $stmt = $conn->prepare("
        UPDATE coupons SET
            code=?,
            title=?,
            description=?,
            offer_type=?,
            discount_type=?,
            discount_value=?,
            max_discount_amount=?,
            min_order_amount=?,
            usage_limit=?,
            usage_per_user=?,
            is_new_user_only=?,
            badge_text=?,
            background_color=?,
            button_text=?,
            start_date=?,
            end_date=?,
            status=?
        WHERE id=?
    ");

    $stmt->bind_param(
        "sssssdddiiisssssis",
        $code,
        $title,
        $description,
        $offer_type,
        $discount_type,
        $discount_value,
        $max_discount_amount,
        $min_order_amount,
        $usage_limit,
        $usage_per_user,
        $is_new_user_only,
        $badge_text,
        $background_color,
        $button_text,
        $start_date,
        $end_date,
        $status,
        $id
    );

    if ($stmt->execute()) {
        $_SESSION['status'] = "Coupon updated successfully!";
    } else {
        $_SESSION['status'] = "Failed to update coupon!";
    }
}

/* =========================================================
   DELETE COUPON
========================================================= */

if (isset($_POST['deleteCoupon'])) {

    $id = $_POST['delete_id'];

    $stmt = $conn->prepare("
        DELETE FROM coupons
        WHERE id=?
    ");

    $stmt->bind_param("s", $id);

    if ($stmt->execute()) {
        $_SESSION['status'] = "Coupon deleted successfully!";
    } else {
        $_SESSION['status'] = "Failed to delete coupon!";
    }
}

/* =========================================================
   TOGGLE STATUS
========================================================= */

if (isset($_POST['toggleStatus'])) {

    $id = $_POST['coupon_id'];

    $status = $_POST['status'] == 1 ? 0 : 1;

    $stmt = $conn->prepare("
        UPDATE coupons
        SET status=?
        WHERE id=?
    ");

    $stmt->bind_param("is", $status, $id);
    $stmt->execute();
}
?>

<div class="content-wrapper">

    <!-- =====================================================
       ADD COUPON MODAL
    ====================================================== -->

    <div class="modal fade" id="AddCouponModal">

        <div class="modal-dialog modal-lg">

            <div class="modal-content">

                <div class="modal-header">
                    <h5>Add Coupon</h5>

                    <button class="close"
                        data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <form method="POST">

                    <div class="modal-body">

                        <div class="row">

                            <div class="col-md-6">
                                <label>Coupon Code</label>

                                <input type="text"
                                    name="code"
                                    class="form-control"
                                    required>
                            </div>

                            <div class="col-md-6">
                                <label>Title</label>

                                <input type="text"
                                    name="title"
                                    class="form-control"
                                    required>
                            </div>

                            <div class="col-md-12 mt-3">
                                <label>Description</label>

                                <textarea
                                    name="description"
                                    class="form-control"></textarea>
                            </div>

                            <div class="col-md-6 mt-3">
                                <label>Offer Type</label>

                                <select
                                    name="offer_type"
                                    class="form-control">

                                    <option value="all">
                                        All
                                    </option>

                                    <option value="bank_offer">
                                        Bank Offer
                                    </option>

                                    <option value="combo_offer">
                                        Combo Offer
                                    </option>

                                    <option value="free_delivery">
                                        Free Delivery
                                    </option>

                                    <option value="new_user">
                                        New User
                                    </option>

                                </select>
                            </div>

                            <div class="col-md-6 mt-3">
                                <label>Discount Type</label>

                                <select
                                    name="discount_type"
                                    class="form-control">

                                    <option value="percentage">
                                        Percentage
                                    </option>

                                    <option value="flat">
                                        Flat
                                    </option>

                                    <option value="free_delivery">
                                        Free Delivery
                                    </option>

                                </select>
                            </div>

                            <div class="col-md-4 mt-3">
                                <label>Discount Value</label>

                                <input type="number"
                                    step="0.01"
                                    name="discount_value"
                                    class="form-control">
                            </div>

                            <div class="col-md-4 mt-3">
                                <label>Max Discount</label>

                                <input type="number"
                                    step="0.01"
                                    name="max_discount_amount"
                                    class="form-control">
                            </div>

                            <div class="col-md-4 mt-3">
                                <label>Minimum Order</label>

                                <input type="number"
                                    step="0.01"
                                    name="min_order_amount"
                                    class="form-control">
                            </div>

                            <div class="col-md-4 mt-3">
                                <label>Usage Limit</label>

                                <input type="number"
                                    name="usage_limit"
                                    class="form-control">
                            </div>

                            <div class="col-md-4 mt-3">
                                <label>Usage Per User</label>

                                <input type="number"
                                    name="usage_per_user"
                                    value="1"
                                    class="form-control">
                            </div>

                            <div class="col-md-4 mt-3">
                                <label>Status</label>

                                <select
                                    name="status"
                                    class="form-control">

                                    <option value="1">
                                        Active
                                    </option>

                                    <option value="0">
                                        Disabled
                                    </option>

                                </select>
                            </div>

                            <div class="col-md-6 mt-3">
                                <label>Badge Text</label>

                                <input type="text"
                                    name="badge_text"
                                    class="form-control">
                            </div>

                            <div class="col-md-6 mt-3">
                                <label>Button Text</label>

                                <input type="text"
                                    name="button_text"
                                    value="Apply"
                                    class="form-control">
                            </div>

                            <div class="col-md-6 mt-3">
                                <label>Background Color</label>

                                <input type="color"
                                    name="background_color"
                                    value="#F7F1EB"
                                    class="form-control">
                            </div>

                            <div class="col-md-6 mt-3">
                                <label>
                                    New User Only
                                </label>

                                <div>
                                    <input type="checkbox"
                                        name="is_new_user_only">
                                </div>
                            </div>

                            <div class="col-md-6 mt-3">
                                <label>Start Date</label>

                                <input type="datetime-local"
                                    name="start_date"
                                    class="form-control">
                            </div>

                            <div class="col-md-6 mt-3">
                                <label>End Date</label>

                                <input type="datetime-local"
                                    name="end_date"
                                    class="form-control">
                            </div>

                        </div>

                    </div>

                    <div class="modal-footer">

                        <button type="button"
                            class="btn btn-secondary"
                            data-dismiss="modal">
                            Close
                        </button>

                        <button type="submit"
                            name="addCoupon"
                            class="btn btn-primary">
                            Add Coupon
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

    <!-- =====================================================
       PAGE HEADER
    ====================================================== -->

    <div class="content-header">
        <div class="container-fluid">

            <h1 class="m-0">
                Coupons / Offers
            </h1>

        </div>
    </div>

    <!-- =====================================================
       TABLE
    ====================================================== -->

    <div class="container">

        <?php include('./message/message.php'); ?>

        <div class="card">

            <div class="card-header">

                <h3 class="card-title">
                    Coupons
                </h3>

                <button
                    class="btn btn-primary btn-sm float-right"
                    data-toggle="modal"
                    data-target="#AddCouponModal">

                    Add Coupon

                </button>

            </div>

            <div class="card-body">

                <table
                    id="example1"
                    class="table table-bordered table-striped">

                    <thead>

                        <tr>
                            <th>Sr</th>
                            <th>Code</th>
                            <th>Title</th>
                            <th>Offer Type</th>
                            <th>Discount</th>
                            <th>Min Order</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php
                        $query = "
                            SELECT *
                            FROM coupons
                            ORDER BY created_at DESC
                        ";

                        $run = mysqli_query($conn, $query);

                        $n = 0;

                        while ($row = mysqli_fetch_assoc($run)):

                            $n++;
                        ?>

                            <tr>

                                <td><?= $n ?></td>

                                <td>
                                    <?= htmlspecialchars($row['code']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['title']) ?>
                                </td>

                                <td>
                                    <?= $row['offer_type'] ?>
                                </td>

                                <td>

                                    <?php
                                    if ($row['discount_type'] == 'percentage') {

                                        echo $row['discount_value'] . '%';
                                    } else if (
                                        $row['discount_type']
                                        == 'flat'
                                    ) {

                                        echo '$' .
                                            $row['discount_value'];
                                    } else {

                                        echo 'Free Delivery';
                                    }
                                    ?>

                                </td>

                                <td>
                                    $<?= $row['min_order_amount'] ?>
                                </td>

                                <td>

                                    <?php if ($row['status'] == 1): ?>

                                        <span class="badge badge-success">
                                            Active
                                        </span>

                                    <?php else: ?>

                                        <span class="badge badge-danger">
                                            Disabled
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>

                                    <form method="POST"
                                        style="display:inline-block;">

                                        <input type="hidden"
                                            name="coupon_id"
                                            value="<?= $row['id'] ?>">

                                        <input type="hidden"
                                            name="status"
                                            value="<?= $row['status'] ?>">

                                        <button
                                            type="submit"
                                            name="toggleStatus"
                                            class="btn btn-warning btn-sm">

                                            <?= $row['status'] == 1
                                                ? 'Disable'
                                                : 'Enable' ?>

                                        </button>

                                    </form>

                                    <button
                                        class="btn btn-danger btn-sm deleteBtn"
                                        value="<?= $row['id'] ?>">

                                        Delete

                                    </button>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

<!-- =====================================================
   DELETE MODAL
===================================================== -->

<div class="modal fade"
    id="DeleteCouponModal">

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">
                <h5>Delete Coupon</h5>

                <button class="close"
                    data-dismiss="modal">

                    <span>&times;</span>

                </button>
            </div>

            <form method="POST">

                <div class="modal-body">

                    <input type="hidden"
                        name="delete_id"
                        class="delete_coupon_id">

                    <p>
                        Are you sure you want to delete this coupon?
                    </p>

                </div>

                <div class="modal-footer">

                    <button type="button"
                        class="btn btn-secondary"
                        data-dismiss="modal">

                        Close

                    </button>

                    <button type="submit"
                        name="deleteCoupon"
                        class="btn btn-danger">

                        Delete

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<?php include('include/script.php'); ?>

<script>
    $(document).ready(function() {

        $('.deleteBtn').click(function() {

            $('.delete_coupon_id')
                .val($(this).val());

            $('#DeleteCouponModal')
                .modal('show');
        });

    });
</script>

<?php include('include/footer.php'); ?>