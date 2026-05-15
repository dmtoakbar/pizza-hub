<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('./authentication/authentication.php');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants/constants.php';

/* PROMO SLIDER LOGIC */
include('codeLogic/homePromoBanner/add/add.php');
include('codeLogic/homePromoBanner/edit/edit.php');
include('codeLogic/homePromoBanner/delete/delete.php');

include('include/header.php');
include('include/topbar.php');
include('include/sidebar.php');
?>

<div class="content-wrapper">

    <!-- ================= ADD MODAL ================= -->
    <div class="modal fade" id="AddPromoSliderModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Add Promo Slider Banner</h5>
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
                            <label>Button Text</label>
                            <input type="text" name="button_text" class="form-control" placeholder="Order Now">
                        </div>

                        <div class="form-group">
                            <label>Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="0">
                        </div>

                        <div class="form-group">
                            <label>Start Date</label>
                            <input type="datetime-local" name="start_date" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>End Date</label>
                            <input type="datetime-local" name="end_date" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Banner Image</label>
                            <input type="file" name="image" class="form-control" required>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" name="addPromoSlider" class="btn btn-primary">Add Banner</button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <!-- ================= UPDATE MODAL ================= -->
    <div class="modal fade" id="UpdatePromoSliderModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Update Promo Slider Banner</h5>
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
                            <label>Button Text</label>
                            <input type="text" name="button_text" id="edit_button_text" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Sort Order</label>
                            <input type="number" name="sort_order" id="edit_sort_order" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Start Date</label>
                            <input type="datetime-local" name="start_date" id="edit_start_date" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>End Date</label>
                            <input type="datetime-local" name="end_date" id="edit_end_date" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Change Image</label>
                            <input type="file" name="image" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Current Image</label><br>
                            <img id="edit_image" width="120" style="border-radius:8px;">
                        </div>


                        <div class="form-group">
                            <label>Status</label>

                            <select name="status" id="edit_status" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" name="updatePromoSlider" class="btn btn-success">Update</button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <!-- ================= DELETE MODAL ================= -->
    <div class="modal fade" id="DeletePromoSliderModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Delete Promo Slider Banner</h5>
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
                        <button type="submit" name="deletePromoSlider" class="btn btn-danger">Delete</button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <!-- ================= PAGE HEADER ================= -->
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Promo Slider Banners</h1>
        </div>
    </div>

    <!-- ================= TABLE ================= -->
    <div class="container">

        <?php include('./message/message.php'); ?>

        <div class="card">

            <div class="card-header">
                <h3 class="card-title">Promo Slider List</h3>

                <button class="btn btn-primary btn-sm float-right"
                    data-toggle="modal"
                    data-target="#AddPromoSliderModal">
                    Add Promo Banner
                </button>
            </div>

            <div class="card-body">

                <table id="example1" class="table table-bordered table-striped">

                    <thead>
                        <tr>
                            <th>Sr</th>
                            <th>Title</th>
                            <th>Button</th>
                            <th>Sort</th>
                            <th>Image</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php
                        $query = "SELECT * FROM promo_slider_banners 
                                  ORDER BY sort_order ASC, created_at DESC";

                        $run = mysqli_query($conn, $query);

                        $n = 0;

                        while ($row = mysqli_fetch_assoc($run)):
                            $n++;
                        ?>

                            <tr>

                                <td><?= $n ?></td>

                                <td><?= htmlspecialchars($row['title']) ?></td>

                                <td><?= htmlspecialchars($row['button_text']) ?></td>

                                <td><?= (int)$row['sort_order'] ?></td>

                                <td>
                                    <img src="<?= $mediaPath . $row['image'] ?>"
                                        width="90"
                                        height="55"
                                        style="object-fit:cover;border-radius:8px;">
                                </td>

                                <td>

                                    <button
                                        class="btn btn-sm btn-info editBtn"

                                        data-id="<?= $row['id'] ?>"
                                        data-title="<?= htmlspecialchars($row['title']) ?>"
                                        data-subtitle="<?= htmlspecialchars($row['subtitle']) ?>"
                                        data-button="<?= htmlspecialchars($row['button_text']) ?>"
                                        data-sort="<?= $row['sort_order'] ?>"
                                        data-start="<?= $row['start_date'] ?>"
                                        data-end="<?= $row['end_date'] ?>"
                                        data-status="<?= $row['status'] ?>"
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
            $('#DeletePromoSliderModal').modal('show');
        });

        $('.editBtn').click(function() {

            $('#edit_id').val($(this).data('id'));
            $('#edit_title').val($(this).data('title'));
            $('#edit_subtitle').val($(this).data('subtitle'));
            $('#edit_button_text').val($(this).data('button'));
            $('#edit_sort_order').val($(this).data('sort'));
            $('#edit_start_date').val($(this).data('start'));
            $('#edit_end_date').val($(this).data('end'));
            $('#edit_status').val($(this).data('status'));
            const imagePath = $(this).data('image');

            $('#edit_old_image').val(imagePath);

            $('#edit_image').attr(
                'src',
                '<?= $mediaPath ?>' + imagePath
            );

            $('#UpdatePromoSliderModal').modal('show');
        });

    });
</script>

<?php include('include/footer.php'); ?>