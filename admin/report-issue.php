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



  <!-- View & Update Report Modal -->
  <div class="modal fade" id="ViewReportModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">Report Details</h5>
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
                <label>Order ID</label>
                <input type="text" id="r_order_id" class="form-control" readonly>
              </div>

              <div class="col-md-12 mb-2">
                <label>Issue</label>
                <input type="text" id="r_issue" class="form-control" readonly>
              </div>

              <div class="col-md-12 mb-2">
                <label>Message</label>
                <textarea id="r_message" rows="4" class="form-control" readonly></textarea>
              </div>

              <div class="col-md-6 mb-2">
                <label>Status</label>
                <select name="status" id="r_status" class="form-control">
                  <option value="pending">Pending</option>
                  <option value="in_progress">In Progress</option>
                  <option value="resolved">Resolved</option>
                </select>
              </div>

            </div>

          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">
              Close
            </button>
            <button type="submit" name="updateReport" class="btn btn-success">
              Update Status
            </button>
          </div>

        </form>

      </div>
    </div>
  </div>

  <!-- delete user -->
  <!-- User Modal -->
  <!-- Delete Report Modal -->
  <div class="modal fade" id="DeleteReportModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Delete Report</h5>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>
        <form action="codeLogic/report/delete/delete.php" method="POST">
          <div class="modal-body">
            <input type="hidden" name="delete_id" class="delete_report_id">
            <p>Are you sure you want to delete this report?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" name="deleteReport" class="btn btn-primary">Yes, Delete!</button>
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
            <li class="breadcrumb-item active">Report Issue</li>
          </ol>
        </div><!-- /.col -->
      </div><!-- /.row -->
    </div><!-- /.container-fluid -->
  </div>
  <!-- /.content-header -->

  <!-- Reports Content Wrapper -->
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <?php include('./message/message.php'); ?>
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Reports</h3>
          </div>
          <div class="card-body">
            <table id="reportsTable" class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>Sr</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Phone</th>
                  <th>Order ID</th>
                  <th>Issue</th>
                  <th>Message</th>
                  <th>Status</th>
                  <th>Created At</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $query = "SELECT * FROM report ORDER BY created_at DESC";
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
                      <td><?= htmlspecialchars($row['order_id']); ?></td>
                      <td><?= htmlspecialchars($row['issue']); ?></td>
                      <td><?= htmlspecialchars(mb_strimwidth($row['issue_message'], 0, 50, '...')); ?></td>
                      <td>
                        <?php
                        $status = $row['status'];
                        if ($status == 'pending') {
                          echo '<span class="badge badge-warning">Pending</span>';
                        } elseif ($status == 'in_progress') {
                          echo '<span class="badge badge-info">In Progress</span>';
                        } else {
                          echo '<span class="badge badge-success">Resolved</span>';
                        }
                        ?>
                      </td>
                      <td><?= date('d-m-Y H:i', strtotime($row['created_at'])); ?></td>
                      <td>
                        <button
                          type="button"
                          class="btn btn-sm btn-info viewReportBtn"
                          data-id="<?= $row['id']; ?>">
                          View
                        </button>
                        <button type="button" value="<?= $row['id']; ?>" class="btn btn-sm btn-danger deleteReportBtn">
                          Delete
                        </button>
                      </td>
                    </tr>
                  <?php
                  }
                } else {
                  ?>
                  <tr>
                    <td colspan="10" class="text-center">No Reports Found</td>
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
    $('.deleteReportBtn').click(function(e) {
      e.preventDefault();
      var report_id = $(this).val();
      $('.delete_report_id').val(report_id);
      $('#DeleteReportModal').modal('show');
    });

    // Optional: initialize DataTable
    $('#reportsTable').DataTable({
      "responsive": true,
      "autoWidth": false,
      "order": [
        [0, "desc"]
      ]
    });
  });
</script>


<script>
$(document).ready(function () {

  $('.viewReportBtn').click(function () {
    const reportId = $(this).data('id');

    $.ajax({
      url: 'codeLogic/report/fetch/fetch.php',
      type: 'POST',
      data: { id: reportId },
      dataType: 'json',
      success: function (res) {
        if (res.success) {
          $('#report_id').val(res.data.id);
          $('#r_name').val(res.data.name);
          $('#r_email').val(res.data.email);
          $('#r_phone').val(res.data.phone);
          $('#r_order_id').val(res.data.order_id);
          $('#r_issue').val(res.data.issue);
          $('#r_message').val(res.data.issue_message);
          $('#r_status').val(res.data.status);

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