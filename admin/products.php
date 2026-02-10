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

<div class="content-wrapper">

  <!-- ================= ADD PRODUCT MODAL ================= -->
  <div class="modal fade" id="AddProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">Add Product</h5>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>

        <form method="POST"
              action="codeLogic/product/add/add.php"
              enctype="multipart/form-data">

          <div class="modal-body">

            <!-- CATEGORY -->
            <div class="form-group">
              <label>Category</label>
              <select name="category_id" class="form-control" required>
                <option value="">-- Select Category --</option>
                <?php
                $catQuery = mysqli_query($conn, "SELECT id, name FROM categories WHERE status = 1");
                while ($cat = mysqli_fetch_assoc($catQuery)) {
                  echo "<option value='{$cat['id']}'>" . htmlspecialchars($cat['name']) . "</option>";
                }
                ?>
              </select>
            </div>

            <!-- NAME -->
            <div class="form-group">
              <label>Product Name</label>
              <input type="text" name="name" class="form-control" required>
            </div>

            <!-- DESCRIPTION -->
            <div class="form-group">
              <label>Description</label>
              <textarea name="description" class="form-control"></textarea>
            </div>

            <!-- PRICE -->
            <div class="form-group">
              <label>Price (₹)</label>
              <input type="number" step="0.01" name="price" class="form-control" required>
            </div>

            <!-- DISCOUNT -->
            <div class="form-group">
              <label>Discount Price (₹)</label>
              <input type="number" step="0.01" name="discount_price" class="form-control">
            </div>

            <!-- FLAGS -->
            <div class="form-group">
              <div class="form-check">
                <input type="checkbox" name="is_popular" class="form-check-input" id="popular">
                <label class="form-check-label" for="popular">Popular</label>
              </div>

              <div class="form-check">
                <input type="checkbox" name="is_featured" class="form-check-input" id="featured">
                <label class="form-check-label" for="featured">Featured</label>
              </div>

              <div class="form-check">
                <input type="checkbox" name="status" class="form-check-input" id="status" checked>
                <label class="form-check-label" for="status">Active</label>
              </div>
            </div>

            <!-- IMAGE -->
            <div class="form-group">
              <label>Product Image</label>
              <input type="file" name="image" class="form-control" required>
            </div>

          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" name="addProduct" class="btn btn-primary">Add Product</button>
          </div>

        </form>

      </div>
    </div>
  </div>

  <!-- ================= DELETE MODAL ================= -->
  <div class="modal fade" id="DeleteModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">Delete Product</h5>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>

        <form action="codeLogic/product/delete/delete.php" method="POST">
          <div class="modal-body">
            <input type="hidden" name="delete_id" class="delete_product_id">
            <p>Are you sure you want to delete this product?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" name="deleteProduct" class="btn btn-danger">Yes, Delete</button>
          </div>
        </form>

      </div>
    </div>
  </div>

  <!-- ================= HEADER ================= -->
  <div class="content-header">
    <div class="container-fluid">
      <h1 class="m-0">Products</h1>
    </div>
  </div>

  <!-- ================= TABLE ================= -->
  <div class="container">
    <?php include('./message/message.php'); ?>

    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Product List</h3>
        <button class="btn btn-primary btn-sm float-right"
                data-toggle="modal"
                data-target="#AddProductModal">
          Add Product
        </button>
      </div>

      <div class="card-body">
        <table id="example1" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>Sr</th>
              <th>Category</th>
              <th>Name</th>
              <th>Price</th>
              <th>Status</th>
              <th>Image</th>
              <th>Action</th>
            </tr>
          </thead>

          <tbody>
            <?php
            $query = "
              SELECT p.*, c.name AS category_name
              FROM products p
              JOIN categories c ON c.id = p.category_id
              ORDER BY p.created_at DESC
            ";

            $run = mysqli_query($conn, $query);
            $n = 0;

            while ($row = mysqli_fetch_assoc($run)):
              $n++;
            ?>
              <tr>
                <td><?= $n ?></td>
                <td><?= htmlspecialchars($row['category_name']) ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td>₹<?= number_format($row['price'], 2) ?></td>
                <td>
                  <?= $row['status'] ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>' ?>
                </td>
                <td>
                  <img src="<?= $mediaPath . $row['image'] ?>"
                       width="70"
                       height="55"
                       style="object-fit:cover;">
                </td>
                <td>
                  <a href="product-edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info">Edit</a>
                  <button class="btn btn-sm btn-danger deletebtn" value="<?= $row['id'] ?>">Delete</button>
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
$(document).ready(function () {
  $('.deletebtn').click(function () {
    $('.delete_product_id').val($(this).val());
    $('#DeleteModal').modal('show');
  });
});
</script>

<?php include('include/footer.php'); ?>
