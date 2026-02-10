<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('./authentication/authentication.php');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants/constants.php';

// /* HOME BANNER LOGIC */
include('codeLogic/homeBanner/add/add.php');
include('codeLogic/homeBanner/edit/edit.php');
include('codeLogic/homeBanner/delete/delete.php');

include('include/header.php');
include('include/topbar.php');
include('include/sidebar.php');
?>

<div class="content-wrapper">

    <!-- ================= ADD HOME BANNER MODAL ================= -->
    <div class="modal fade" id="AddBannerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Add Home Banner</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">

                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Subtitle</label>
                            <input type="text" name="subtitle" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Discount Text</label>
                            <input type="text" name="discount_text" class="form-control" placeholder="UP TO 70% OFF">
                        </div>

                        <div class="form-group">
                            <label>Valid Till</label>
                            <input type="date" name="valid_till" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="0">
                        </div>

                        <div class="form-group">
                            <label>Banner Image</label>
                            <input type="file" name="image" class="form-control" required>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" name="addHomeBanner" class="btn btn-primary">Add</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- ================= UPDATE HOME BANNER MODAL ================= -->
    <div class="modal fade" id="UpdateBannerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Update Home Banner</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <form method="POST" enctype="multipart/form-data">

                    <input type="hidden" name="banner_id" id="edit_id">
                    <input type="hidden" name="old_image" id="edit_old_image">

                    <div class="modal-body">

                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" name="title" id="edit_title" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Subtitle</label>
                            <input type="text" name="subtitle" id="edit_subtitle" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Discount Text</label>
                            <input type="text" name="discount_text" id="edit_discount_text" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Valid Till</label>
                            <input type="date" name="valid_till" id="edit_valid_till" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Sort Order</label>
                            <input type="number" name="sort_order" id="edit_sort_order" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Change Image</label>
                            <input type="file" name="image" class="form-control">
                            <small class="text-muted">Leave empty to keep existing image</small>
                        </div>

                        <div class="form-group">
                            <label>Current Image</label><br>
                            <img id="edit_image" width="120" style="border-radius:6px;">
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" name="updateHomeBanner" class="btn btn-success">Update</button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <!-- ================= DELETE HOME BANNER MODAL ================= -->
    <div class="modal fade" id="DeleteBannerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Delete Home Banner</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="delete_id" class="delete_banner_id">
                        <p>Are you sure you want to delete this banner?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" name="deleteHomeBanner" class="btn btn-danger">Yes, Delete!</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- ================= HEADER ================= -->
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Home Banners</h1>
        </div>
    </div>

    <!-- ================= TABLE ================= -->
    <div class="container">
        <?php include('./message/message.php'); ?>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Banner List</h3>
                <button class="btn btn-primary btn-sm float-right"
                    data-toggle="modal"
                    data-target="#AddBannerModal">
                    Add Banner
                </button>
            </div>

            <div class="card-body">
                <table id="example1" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Sr</th>
                            <th>Title</th>
                            <th>Discount</th>
                            <th>Valid Till</th>
                            <th>Sort</th>
                            <th>Image</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $query = "SELECT * FROM home_banners ORDER BY sort_order ASC, created_at DESC";
                        $run = mysqli_query($conn, $query);
                        $n = 0;

                        while ($row = mysqli_fetch_assoc($run)):
                            $n++;
                        ?>
                            <tr>
                                <td><?= $n ?></td>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td><?= htmlspecialchars($row['discount_text']) ?></td>
                                <td><?= htmlspecialchars($row['valid_till']) ?></td>
                                <td><?= (int)$row['sort_order'] ?></td>
                                <td>
                                    <img src="<?= $mediaPath . $row['image'] ?>"
                                        width="80"
                                        height="50"
                                        style="object-fit:cover;">
                                </td>
                                <td>
                                    <button
                                        class="btn btn-sm btn-info editBtn"
                                        data-id="<?= $row['id'] ?>"
                                        data-title="<?= htmlspecialchars($row['title']) ?>"
                                        data-subtitle="<?= htmlspecialchars($row['subtitle']) ?>"
                                        data-discount="<?= htmlspecialchars($row['discount_text']) ?>"
                                        data-valid="<?= $row['valid_till'] ?>"
                                        data-sort="<?= $row['sort_order'] ?>"
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
            $('.delete_banner_id').val($(this).val());
            $('#DeleteBannerModal').modal('show');
        });

        $('.editBtn').click(function() {

            $('#edit_id').val($(this).data('id'));
            $('#edit_title').val($(this).data('title'));
            $('#edit_subtitle').val($(this).data('subtitle'));
            $('#edit_discount_text').val($(this).data('discount'));
            $('#edit_valid_till').val($(this).data('valid'));
            $('#edit_sort_order').val($(this).data('sort'));

            const imagePath = $(this).data('image');
            $('#edit_old_image').val(imagePath);
            $('#edit_image').attr('src', '<?= $mediaPath ?>' + imagePath);

            $('#UpdateBannerModal').modal('show');
        });

    });
</script>

<?php include('include/footer.php'); ?>