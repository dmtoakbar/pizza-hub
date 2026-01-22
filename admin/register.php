<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include('./authentication/authentication.php');
require_once __DIR__ . '/../config/database.php';
include('codeLogic/auth/register/register.php');
include('codeLogic/auth/delete/delete.php');
include('include/header.php');
include('include/topbar.php');
include('include/sidebar.php');

?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

  <!-- User Modal -->
  <div class="modal fade" id="AddUserModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">Add Admin</h5>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>

        <form method="POST" autocomplete="off">
          <div class="modal-body">

            <div class="form-group">
              <label>Name</label>
              <input type="text" name="name" class="form-control" required>
            </div>

            <div class="form-group">
              <label>Phone</label>
              <input type="text" name="phone" class="form-control" required>
            </div>

            <div class="form-group">
              <label>Email</label>
              <input type="email" name="email" class="form-control" required>
            </div>

            <div class="row">
              <div class="col-md-6">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
              </div>
            </div>

            <div class="form-group mt-2">
              <label>Role</label>
              <select name="role" class="form-control" required>
                <option value="admin">Admin</option>
                <option value="editor">Editor</option>
                <option value="reader">Reader</option>
              </select>
            </div>

            <div class="form-group">
              <label>Status</label>
              <select name="is_active" class="form-control">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
              </select>
            </div>

          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" name="addAdmin" class="btn btn-primary">Save</button>
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
            <li class="breadcrumb-item active">Registered Admins</li>
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
            <h3 class="card-title">Registered Admins
            </h3>
            <a href="" class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#AddUserModal">Add Admin</a>
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
                  <th>Role</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $query = "SELECT * FROM admins";
                $query_run = mysqli_query($conn, $query);
                if (mysqli_num_rows($query_run) > 0) {
                  $n = 0;
                  foreach ($query_run as $row) {
                    $n++;
                ?>
                    <tr>
                      <td><?php echo $n; ?></td>
                      <td><?php echo $row['name']; ?>
                      </td>
                      <td><?php echo $row['email']; ?></td>
                      <td><?php echo $row['phone']; ?></td>
                      <td><?= ucfirst(str_replace('_', ' ', $row['role'])) ?></td>
                      <td><?= $row['is_active'] ? 'Active' : 'Inactive' ?></td>
                      <td>
                        <a href="register-edit.php?user_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">Edit</a>

                        <button type="button" value="<?php echo $row['id']; ?>" class="btn btn-sm btn-danger deletebtn">Delete</button>
                      </td>
                    </tr>
                  <?php
                  }
                } else {
                  ?>
                  <tr>
                    <td>No Record Found</td>
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
  $(document).ready(function () {

    $('.deletebtn').on('click', function () {
        var userId = $(this).val();
        $('.delete_user_id').val(userId);
        $('#DeleteModal').modal('show');
    });

  });
</script>

<?php
include('include/footer.php');
?>
