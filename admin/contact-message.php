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


    <div class="modal fade" id="ViewReportModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Message Details</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <form action="codeLogic/report/update/update.php" method="POST">
                    <div class="modal-body">

                        <input type="hidden" name="report_id" id="report_id">

                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label>Name</label>
                                <input type="text" id="r_name" class="form-control" readonly>
                            </div>

                            <div class="col-md-6 mb-2">
                                <label>Email</label>
                                <input type="text" id="r_email" class="form-control" readonly>
                            </div>

                            <div class="col-md-6 mb-2">
                                <label>Phone</label>
                                <input type="text" id="r_phone" class="form-control" readonly>
                            </div>

                            <div class="col-md-6 mb-2">
                                <label>Subject</label>
                                <input type="text" id="r_subject" class="form-control" readonly>
                            </div>
                            <div class="col-md-12 mb-2">
                                <label>Message</label>
                                <textarea id="r_message" rows="4" class="form-control" readonly></textarea>
                            </div>



                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            Close
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>


    <!-- delete user -->
    <!-- User Modal -->
    <div class="modal fade" id="DeleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Delete Message</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="codeLogic/contactUs/delete/delete.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="delete_id" class="delete_user_id">
                        <p>Are you sure, you want to delete this data ?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" name="deleteMessage" class="btn btn-primary">Yes, Delete !</button>
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
                        <li class="breadcrumb-item active">Contact Messages</li>
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
                        <h3 class="card-title">Contact Messages
                        </h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Sr</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Subject</th>
                                    <th>Message</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                $query = "SELECT * FROM contact_us ORDER BY created_at DESC";
                                $query_run = mysqli_query($conn, $query);

                                if (mysqli_num_rows($query_run) > 0) {
                                    $n = 0;
                                    foreach ($query_run as $row) {
                                        $n++;
                                ?>
                                        <tr>
                                            <td><?= $n; ?></td>

                                            <td><?= htmlspecialchars($row['name']); ?></td>

                                            <td><?= htmlspecialchars($row['email']); ?></td>

                                            <td><?= htmlspecialchars($row['phone']); ?></td>

                                            <td>
                                                <?= htmlspecialchars(mb_strimwidth($row['subject'], 0, 40, '...')); ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars(mb_strimwidth($row['message'], 0, 40, '...')); ?>
                                            </td>

                                            <td>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-info viewReportBtn"
                                                    data-id="<?= $row['id']; ?>">
                                                    View
                                                </button>

                                                <button type="button"
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
                                        <td colspan="7" class="text-center">No Products Found</td>
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
</script>

<script>
    $(document).ready(function() {

        $('.viewReportBtn').click(function() {
            const reportId = $(this).data('id');

            $.ajax({
                url: 'codeLogic/contactUs/fetch/fetch.php',
                type: 'POST',
                data: {
                    id: reportId
                },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        $('#report_id').val(res.data.id);
                        $('#r_name').val(res.data.name);
                        $('#r_email').val(res.data.email);
                        $('#r_phone').val(res.data.phone);
                        $('#r_subject').val(res.data.subject);
                        $('#r_message').val(res.data.message);
                        $('#ViewReportModal').modal('show');
                    } else {
                        alert('Failed to load report');
                    }
                }
            });
        });

    });
</script>

<?php
include('include/footer.php');
?>