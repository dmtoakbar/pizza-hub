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
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Dashboard</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Orders</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <?php
                include('./message/message.php');
                ?>
                <div class="card">
                    <div class="card-header">

                        <div class="d-flex flex-wrap mb-3 gap-3">

                            <a href="orders.php?status=pending"
                                class="btn btn-primary flex-fill">
                                🆕 New
                            </a>

                            <a href="orders.php?status=accepted"
                                class="btn btn-info text-dark flex-fill">
                               Accepted
                            </a>

                            <a href="orders.php?status=preparing"
                                class="btn btn-warning text-dark flex-fill">
                                👨‍🍳 Preparing
                            </a>

                            <a href="orders.php?status=ready"
                                class="btn btn-secondary flex-fill">
                                📦 Ready
                            </a>

                            <a href="orders.php?status=out_for_delivery"
                                class="btn btn-info text-dark flex-fill">
                                🚴 Out for delivery
                            </a>

                            <a href="orders.php?status=delivered"
                                class="btn btn-success flex-fill">
                                ✅ Delivered
                            </a>

                            <a href="orders.php?status=cancelled"
                                class="btn btn-danger flex-fill">
                                ❌ Cancelled
                            </a>

                        </div>


                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Sr</th>
                                    <th>Order ID</th>
                                    <th>Customer Name</th>
                                    <th>Phone</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php

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

                                $query = "SELECT * FROM orders WHERE status = '$status' ORDER BY created_at DESC";
                                $query_run = mysqli_query($conn, $query);

                                if (mysqli_num_rows($query_run) > 0) {
                                    $n = 0;
                                    foreach ($query_run as $row) {
                                        $n++;
                                ?>
                                        <tr>
                                            <td><?= $n; ?></td>
                                            <td><?= htmlspecialchars($row['id']); ?></td>
                                            <td><?= htmlspecialchars($row['username']); ?></td>
                                            <td><?= htmlspecialchars($row['phone']); ?></td>
                                            <td>₹<?= number_format($row['total_amount'], 2); ?></td>
                                            <td><?= ucfirst(str_replace('_', ' ', $row['status'])); ?></td>
                                            <td><?= $row['created_at']; ?></td>
                                            <td>
                                                <a href="order-detail.php?order_id=<?= $row['id']; ?>" class="btn btn-sm btn-info">View</a>

                                            </td>
                                        </tr>
                                    <?php
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No Orders Found</td>
                                    </tr>
                                <?php
                                }
                                ?>

                            </tbody>

                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>



</div>
</div>
<?php
include('include/script.php');
?>
<script>
    $(document).ready(function() {
        $('.deletebtn').click(function(e) {
            e.preventDefault();
            var user_id = $(this).val();
            //console.log(user_id);
            $('.delete_user_id').val(user_id);
            $('#DeleteModal').modal('show');
        });
    });

    setTimeout(function () {
        location.reload();
    }, 30000); 

</script>

<?php
include('include/footer.php');
?>