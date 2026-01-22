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

if (!isset($_GET['id'])) {
    die('Product ID missing');
}

$product_id = $_GET['id'];

$query = "SELECT * FROM products WHERE id = ? LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('Product not found');
}

$product = $result->fetch_assoc();
?>

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
                        <li class="breadcrumb-item active">Edit - Product</li>
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
                        <h3 class="card-title">Edit - Product
                        </h3>
                        <a href="products.php" class="btn btn-danger btn-sm float-right">Back</a>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <form action="codeLogic/product/update/update.php" method="POST" enctype="multipart/form-data">
                                    <div class="modal-body">
                                        <input type="hidden" name="id" value="<?= htmlspecialchars($product['id']); ?>">
                                        <input type="hidden" name="old_image" value="<?= htmlspecialchars($product['image']); ?>">

                                        <div class="form-group">
                                            <label>Name</label>
                                            <input type="text" name="name" class="form-control"
                                                value="<?= htmlspecialchars($product['name']); ?>" required>
                                        </div>

                                        <div class="form-group">
                                            <label>Price in Rupees</label>
                                            <input type="number" step="0.01" name="price" class="form-control"
                                                value="<?= htmlspecialchars($product['price']); ?>" required>
                                        </div>

                                        <div class="form-group">
                                            <label>Tag</label>
                                            <input type="text" name="tag" class="form-control"
                                                value="<?= htmlspecialchars($product['tag']); ?>" required>
                                        </div>

                                        <div class="form-group">
                                            <label>Tag Description</label>
                                            <textarea name="tag_description" class="form-control" required><?= htmlspecialchars($product['tag_description']); ?></textarea>
                                        </div>

                                        <div class="form-group">
                                            <label>Product Image</label>
                                            <input type="file" name="image" class="form-control">
                                            <br>
                                            <img src="<?= $mediaPath . ltrim($product['image'], '/'); ?>"
                                                width="120" height="90" style="object-fit:cover;">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" name="updateProduct" class="btn btn-info">
                                            Update Product
                                        </button>
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



<?php
include('include/footer.php');
include('include/script.php');
?>