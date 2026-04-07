<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('./authentication/authentication.php');
require_once __DIR__ . '/../config/database.php';

// ================= HANDLE DELETE ACTION =================
if (isset($_POST['action']) && $_POST['action'] === 'delete_review' && isset($_POST['review_id'])) {
    header('Content-Type: application/json');
    $review_id = $_POST['review_id'];
    $response = ['success' => false, 'message' => ''];

    if ($review_id > 0) {
        $query = "DELETE FROM product_reviews WHERE id = $review_id";
        if (mysqli_query($conn, $query)) {
            $response['success'] = true;
            $response['message'] = 'Review deleted successfully.';
        } else {
            $response['message'] = 'Database error: ' . mysqli_error($conn);
        }
    } else {
        $response['message'] = 'Invalid review ID.';
    }
    echo json_encode($response);
    exit;
}

include('include/header.php');
include('include/topbar.php');
include('include/sidebar.php');
?>

<div class="content-wrapper">

    <!-- ================= VIEW REVIEW MODAL ================= -->
    <div class="modal fade" id="ViewReviewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Review Details</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label>User</label>
                            <input type="text" id="r_user" class="form-control" readonly>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label>Product</label>
                            <input type="text" id="r_product" class="form-control" readonly>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label>Rating</label>
                            <input type="text" id="r_rating" class="form-control" readonly>
                        </div>
                        <div class="col-md-12 mb-2">
                            <label>Review</label>
                            <textarea id="r_review" rows="4" class="form-control" readonly></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= APPROVE REVIEW MODAL ================= -->
    <div class="modal fade" id="ApproveReviewModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Review</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form action="codeLogic/reviews/approve/approve.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="review_id" id="approve_review_id">
                        <p>Are you sure you want to approve this review?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Approve</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ================= DELETE REVIEW MODAL ================= -->
    <div class="modal fade" id="DeleteReviewModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Review</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form action="codeLogic/reviews/delete/delete.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="review_id" id="delete_review_id">
                        <p>Are you sure you want to delete this review? This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ================= HEADER ================= -->
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0 text-dark">Product Reviews</h1>
        </div>
    </div>

    <!-- ================= TABLE ================= -->
    <div class="container">
        <?php include('./message/message.php'); ?>
        <div class="card">
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Product</th>
                            <th>Rating</th>
                            <th>Review</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "
                        SELECT pr.*, u.name as user_name, p.name as product_name
                        FROM product_reviews pr
                        LEFT JOIN users u ON u.id = pr.user_id
                        LEFT JOIN products p ON p.id = pr.product_id
                        ORDER BY pr.created_at DESC
                        ";
                        $result = mysqli_query($conn, $query);

                        if (mysqli_num_rows($result) > 0) {
                            $i = 1;
                            foreach ($result as $row) {
                        ?>
                                <tr>
                                    <td><?= $i++; ?></td>
                                    <td><?= htmlspecialchars($row['user_name']); ?></td>
                                    <td><?= htmlspecialchars($row['product_name']); ?></td>
                                    <td>
                                        <?php
                                        for ($s = 1; $s <= 5; $s++) {
                                            echo $s <= $row['rating'] ? "⭐" : "☆";
                                        }
                                        ?>
                                    </td>
                                    <td><?= htmlspecialchars(mb_strimwidth($row['review'], 0, 40, '...')); ?></td>
                                    <td>
                                        <?php if ($row['approval_status'] == 'approved') { ?>
                                            <span class="badge badge-success">Approved</span>
                                        <?php } else { ?>
                                            <span class="badge badge-warning">Pending</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-info btn-sm viewBtn"
                                            data-id="<?= $row['id']; ?>"
                                            data-user="<?= htmlspecialchars($row['user_name']) ?>"
                                            data-product="<?= htmlspecialchars($row['product_name']) ?>"
                                            data-rating="<?= htmlspecialchars($row['rating']) ?>"
                                            data-review="<?= htmlspecialchars($row['review']) ?>">
                                            View
                                        </button>

                                        <?php if ($row['approval_status'] == 'pending') { ?>
                                            <button class="btn btn-success btn-sm approveBtn"
                                                data-id="<?= $row['id']; ?>">
                                                Approve
                                            </button>
                                        <?php } ?>


                                        <button class="btn btn-danger btn-sm deleteBtn"
                                            data-id="<?= $row['id']; ?>">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                        <?php
                            }
                        } else {
                            echo "<tr><td colspan='7' class='text-center'>No Reviews Found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include('include/script.php'); ?>

<script>
    $(document).ready(function() {
        // ----- VIEW MODAL -----
        $('.viewBtn').click(function() {
            const $btn = $(this);
            $('#r_user').val($btn.data('user'));
            $('#r_product').val($btn.data('product'));
            $('#r_rating').val($btn.data('rating') + ' ⭐');
            $('#r_review').val($btn.data('review'));
            $('#ViewReviewModal').modal('show');
        });

        // ----- APPROVE MODAL TRIGGER -----
        $('.approveBtn').click(function() {
            const reviewId = $(this).data('id');
            $('#approve_review_id').val(reviewId);
            $('#ApproveReviewModal').modal('show');
        });


        // ----- DELETE MODAL TRIGGER -----
        $('.deleteBtn').click(function() {
            const reviewId = $(this).data('id');
            $('#delete_review_id').val(reviewId);
            $('#DeleteReviewModal').modal('show');
        });

    });
</script>

<?php include('include/footer.php'); ?>