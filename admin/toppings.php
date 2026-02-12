<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('./authentication/authentication.php');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants/constants.php';
include('codeLogic/extraToppings/add/add.php');
include('codeLogic/extraToppings/edit/edit.php');
include('codeLogic/extraToppings/delete/delete.php');
include('include/header.php');
include('include/topbar.php');
include('include/sidebar.php');
?>

<div class="content-wrapper">

  <!-- ================= ADD TOPPING MODAL ================= -->
  <div class="modal fade" id="AddToppingModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">Add Extra Topping</h5>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>

        <form method="POST"
          enctype="multipart/form-data">

          <div class="modal-body">

            <div class="form-group">
              <label>Topping Name</label>
              <input type="text" name="name" class="form-control" required>
            </div>

            <div class="form-group">
              <label>Price ($)</label>
              <input type="number" step="0.01" name="price" class="form-control" required>
            </div>

            <div class="form-group">
              <label>Topping Image</label>
              <input type="file" name="image" class="form-control" required>
            </div>

          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" name="addTopping" class="btn btn-primary">Add</button>
          </div>

        </form>

      </div>
    </div>
  </div>

  <!-- ================= UPDATE TOPPING MODAL ================= -->
  <div class="modal fade" id="UpdateToppingModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">Update Topping</h5>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>

        <form method="POST"
          enctype="multipart/form-data">

          <!-- REQUIRED -->
          <input type="hidden" name="topping_id" id="edit_id">
          <input type="hidden" name="old_image" id="edit_old_image">

          <div class="modal-body">

            <div class="form-group">
              <label>Name</label>
              <input type="text" name="name" id="edit_name" class="form-control" required>
            </div>

            <div class="form-group">
              <label>Price ($)</label>
              <input type="number" step="0.01" name="price" id="edit_price" class="form-control" required>
            </div>

            <div class="form-group">
              <label>Change Image</label>
              <!-- ✅ THIS MUST BE image -->
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
            <button type="submit" name="updateTopping" class="btn btn-success">Update</button>
          </div>

        </form>


      </div>
    </div>
  </div>

  <!-- ================= DELETE MODAL ================= -->
  <div class="modal fade" id="DeleteToppingModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">Delete Topping</h5>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>

        <form method="POST">
          <div class="modal-body">
            <input type="hidden" name="delete_id" class="delete_topping_id">
            <p>Are you sure you want to delete this topping?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" name="deleteTopping" class="btn btn-danger">Yes, Delete!</button>
          </div>
        </form>

      </div>
    </div>
  </div>

  <!-- ================= HEADER ================= -->
  <div class="content-header">
    <div class="container-fluid">
      <h1 class="m-0">Extra Toppings</h1>
    </div>
  </div>

  <!-- ================= TABLE ================= -->
  <div class="container">
    <?php include('./message/message.php'); ?>

    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Toppings</h3>
        <button class="btn btn-primary btn-sm float-right"
          data-toggle="modal"
          data-target="#AddToppingModal">
          Add Topping
        </button>
      </div>

      <div class="card-body">
        <table id="example1" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>Sr</th>
              <th>Name</th>
              <th>Price</th>
              <th>Image</th>
              <th>Action</th>
            </tr>
          </thead>

          <tbody>
            <?php
            $query = "SELECT * FROM extra_toppings ORDER BY created_at DESC";
            $run = mysqli_query($conn, $query);
            $n = 0;

            while ($row = mysqli_fetch_assoc($run)):
              $n++;
            ?>
              <tr>
                <td><?= $n ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td>$<?= number_format($row['price'], 2) ?></td>
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
                    data-price="<?= $row['price'] ?>"
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
      $('.delete_topping_id').val($(this).val());
      $('#DeleteToppingModal').modal('show');
    });

    $('.editBtn').click(function() {

      $('#edit_id').val($(this).data('id'));
      $('#edit_name').val($(this).data('name'));
      $('#edit_price').val($(this).data('price'));

      const imagePath = $(this).data('image');

      $('#edit_old_image').val(imagePath);
      $('#edit_image').attr('src', '<?= $mediaPath ?>' + imagePath);

      $('#UpdateToppingModal').modal('show');
    });


  });
</script>

<?php include('include/footer.php'); ?>