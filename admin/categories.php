<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('./authentication/authentication.php');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants/constants.php';

/* CATEGORY LOGIC */
include('codeLogic/category/add/add.php');
include('codeLogic/category/edit/edit.php');
include('codeLogic/category/delete/delete.php');

include('include/header.php');
include('include/topbar.php');
include('include/sidebar.php');
?>

<div class="content-wrapper">

  <!-- ================= ADD CATEGORY MODAL ================= -->
  <div class="modal fade" id="AddCategoryModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">Add Category</h5>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>

        <form method="POST" enctype="multipart/form-data">
          <div class="modal-body">

            <div class="form-group">
              <label>Category Name</label>
              <input type="text" name="name" class="form-control" required>
            </div>

            <div class="form-group">
              <label>Category Image</label>
              <input type="file" name="image" class="form-control" required>
            </div>

          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" name="addCategory" class="btn btn-primary">Add</button>
          </div>
        </form>

      </div>
    </div>
  </div>

  <!-- ================= UPDATE CATEGORY MODAL ================= -->
  <div class="modal fade" id="UpdateCategoryModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">Update Category</h5>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>

        <form method="POST" enctype="multipart/form-data">

          <input type="hidden" name="category_id" id="edit_id">
          <input type="hidden" name="old_image" id="edit_old_image">

          <div class="modal-body">

            <div class="form-group">
              <label>Name</label>
              <input type="text" name="name" id="edit_name" class="form-control" required>
            </div>

            <div class="form-group">
              <label>Change Image</label>
              <input type="file" name="image" class="form-control">
              <small class="text-muted">Leave empty to keep existing image</small>
            </div>

            <div class="form-group">
              <label>Current Image</label><br>
              <img id="edit_image" width="80" style="border-radius:6px;">
            </div>

          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" name="updateCategory" class="btn btn-success">Update</button>
          </div>

        </form>

      </div>
    </div>
  </div>

  <!-- ================= DELETE CATEGORY MODAL ================= -->
  <div class="modal fade" id="DeleteCategoryModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">Delete Category</h5>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>

        <form method="POST">
          <div class="modal-body">
            <input type="hidden" name="delete_id" class="delete_category_id">
            <p>Are you sure you want to delete this category?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" name="deleteCategory" class="btn btn-danger">Yes, Delete!</button>
          </div>
        </form>

      </div>
    </div>
  </div>

  <!-- ================= HEADER ================= -->
  <div class="content-header">
    <div class="container-fluid">
      <h1 class="m-0">Categories</h1>
    </div>
  </div>

  <!-- ================= TABLE ================= -->
  <div class="container">
    <?php include('./message/message.php'); ?>

    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Category List</h3>
        <button class="btn btn-primary btn-sm float-right"
          data-toggle="modal"
          data-target="#AddCategoryModal">
          Add Category
        </button>
      </div>

      <div class="card-body">
        <table id="example1" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>Sr</th>
              <th>Name</th>
              <th>Image</th>
              <th>Action</th>
            </tr>
          </thead>

          <tbody>
            <?php
            $query = "SELECT * FROM categories ORDER BY created_at DESC";
            $run = mysqli_query($conn, $query);
            $n = 0;

            while ($row = mysqli_fetch_assoc($run)):
              $n++;
            ?>
              <tr>
                <td><?= $n ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td>
                  <img src="<?= $mediaPath . $row['image'] ?>"
                    width="60"
                    height="50"
                    style="object-fit:cover;">
                </td>
                <td>
                  <button
                    class="btn btn-sm btn-info editBtn"
                    data-id="<?= $row['id'] ?>"
                    data-name="<?= htmlspecialchars($row['name']) ?>"
                    data-image="<?= $row['image'] ?>">
                    Edit
                  </button>

                  <button
                    class="btn btn-sm btn-danger deleteBtn"
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

<?php include('include/script.php'); ?>

<script>
  $(document).ready(function() {

    $('.deleteBtn').click(function() {
      $('.delete_category_id').val($(this).val());
      $('#DeleteCategoryModal').modal('show');
    });

    $('.editBtn').click(function() {

      $('#edit_id').val($(this).data('id'));
      $('#edit_name').val($(this).data('name'));

      const imagePath = $(this).data('image');
      $('#edit_old_image').val(imagePath);
      $('#edit_image').attr('src', '<?= $mediaPath ?>' + imagePath);

      $('#UpdateCategoryModal').modal('show');
    });

  });
</script>

<?php include('include/footer.php'); ?>
