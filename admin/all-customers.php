<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include('./authentication/authentication.php');
require_once __DIR__ . '/../config/database.php';
include('codeLogic/customer/delete/delete.php');
include('include/header.php');
include('include/topbar.php');
include('include/sidebar.php');

?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

    <!-- delete user -->
    <!-- User Modal -->
    <div class="modal fade" id="DeleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Delete User</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="delete_id" class="delete_user_id">
                        <p>Are you sure, you want to delete this data ?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" name="DeleteUserbtn" class="btn btn-primary">Yes, Delete !</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


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
                        <li class="breadcrumb-item active">Registered Customers</li>
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
                        <h3 class="card-title">Registered Customers
                        </h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone Number</th>
                                    <th>Address</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT * FROM users ORDER BY created_at DESC";
                                $query_run = mysqli_query($conn, $query);

                                if (mysqli_num_rows($query_run) > 0) {
                                    $n = 0;
                                    foreach ($query_run as $row) {
                                        $n++;
                                ?>
                                        <tr>
                                            <td><?= $n ?></td>
                                            <td><?= htmlspecialchars($row['name']) ?></td>
                                            <td><?= htmlspecialchars($row['email']) ?></td>
                                            <td><?= htmlspecialchars($row['phone']) ?></td>
                                            <td><?= htmlspecialchars(mb_strimwidth($row['address'], 0, 30, '...')) ?></td>
                                            <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                                            <td>
                                                <button
                                                    type="button"
                                                    value="<?= $row['id']; ?>"
                                                    class="btn btn-sm btn-danger deletebtn">
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                    <?php
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="7">No Record Found</td>
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

        $('.deletebtn').on('click', function() {
            var userId = $(this).val();
            $('.delete_user_id').val(userId);
            $('#DeleteModal').modal('show');
        });

    });
</script>

<?php
include('include/footer.php');
?>