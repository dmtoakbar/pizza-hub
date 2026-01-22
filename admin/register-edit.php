<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include('./authentication/authentication.php');
require_once __DIR__ . '/../config/database.php';
include('codeLogic/auth/update/register-edit.php');
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
                        <li class="breadcrumb-item active">Edit - Registered Admin</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Edit - Registered Admin
                        </h3>
                        <a href="register.php" class="btn btn-danger btn-sm float-right">Back</a>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <form method="POST">
                                    <div class="modal-body">
                                        <?php
                                        if (isset($_GET['user_id'])) {
                                            $user_id = $_GET['user_id'];
                                            $query = "SELECT * FROM admins WHERE id = '$user_id' LIMIT 1";
                                            $query_run = mysqli_query($conn, $query);

                                            if (mysqli_num_rows($query_run) > 0) {
                                                $row = mysqli_fetch_assoc($query_run);
                                        ?>
                                                <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">

                                                <div class="form-group">
                                                    <label>Name</label>
                                                    <input type="text" value="<?php echo $row['name']; ?>" name="name" class="form-control" placeholder="Name" required>
                                                </div>

                                                <div class="form-group">
                                                    <label>Phone Number</label>
                                                    <input type="text" value="<?php echo $row['phone']; ?>" name="phone" class="form-control" placeholder="Phone Number" required>
                                                </div>

                                                <div class="form-group">
                                                    <label>Email Id (cannot be changed)</label>
                                                    <input type="email" value="<?php echo $row['email']; ?>" class="form-control" disabled>
                                                </div>

                                                <div class="form-group">
                                                    <label>Password (leave blank to keep current)</label>
                                                    <input type="password" name="password" class="form-control" placeholder="Password">
                                                </div>

                                                <div class="form-group mt-2">
                                                    <label>Role</label>
                                                    <select name="role" class="form-control" required>
                                                        <option value="admin" <?= $row['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                                        <option value="editor" <?= $row['role'] == 'editor' ? 'selected' : '' ?>>Editor</option>
                                                        <option value="reader" <?= $row['role'] == 'reader' ? 'selected' : '' ?>>Reader</option>
                                                    </select>
                                                </div>

                                                <div class="form-group">
                                                    <label>Status</label>
                                                    <select name="is_active" class="form-control" required>
                                                        <option value="1" <?= $row['is_active'] ? 'selected' : '' ?>>Active</option>
                                                        <option value="0" <?= !$row['is_active'] ? 'selected' : '' ?>>Inactive</option>
                                                    </select>
                                                </div>
                                        <?php
                                            } else {
                                                echo "<h4>NO Record Found!</h4>";
                                            }
                                        }
                                        ?>

                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" name="updateUser" class="btn btn-info">Update</button>
                                    </div>
                                </form>
                            </div>
                        </div>
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

<?php
include('include/footer.php');
?>