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

/* ================= FETCH PRODUCT ================= */
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
$stmt->bind_param("s", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('Product not found');
}

$product = $result->fetch_assoc();

/* ================= FETCH CATEGORIES ================= */
$categories = mysqli_query($conn, "SELECT id, name FROM categories WHERE status = 1");
?>

<div class="content-wrapper">

    <!-- ================= HEADER ================= -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Edit Product</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Edit Product</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= CONTENT ================= -->
    <div class="container">
        <?php include('./message/message.php'); ?>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit Product</h3>
                <a href="products.php" class="btn btn-danger btn-sm float-right">Back</a>
            </div>

            <div class="card-body">
                <div class="row justify-content-center">
                    <div class="col-md-8">

                        <form action="codeLogic/product/update/update.php"
                              method="POST"
                              enctype="multipart/form-data">

                            <!-- REQUIRED -->
                            <input type="hidden" name="id" value="<?= htmlspecialchars($product['id']); ?>">
                            <input type="hidden" name="old_image" value="<?= htmlspecialchars($product['image']); ?>">

                            <!-- CATEGORY -->
                            <div class="form-group">
                                <label>Category</label>
                                <select name="category_id" class="form-control" required>
                                    <option value="">-- Select Category --</option>
                                    <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                                        <option value="<?= $cat['id']; ?>"
                                            <?= $cat['id'] === $product['category_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <!-- NAME -->
                            <div class="form-group">
                                <label>Product Name</label>
                                <input type="text"
                                       name="name"
                                       class="form-control"
                                       value="<?= htmlspecialchars($product['name']); ?>"
                                       required>
                            </div>

                            <!-- DESCRIPTION -->
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description"
                                          class="form-control"
                                          rows="3"><?= htmlspecialchars($product['description']); ?></textarea>
                            </div>

                            <!-- PRICE -->
                            <div class="form-group">
                                <label>Price (₹)</label>
                                <input type="number"
                                       step="0.01"
                                       name="price"
                                       class="form-control"
                                       value="<?= htmlspecialchars($product['price']); ?>"
                                       required>
                            </div>

                            <!-- DISCOUNT PRICE -->
                            <div class="form-group">
                                <label>Discount Price (₹)</label>
                                <input type="number"
                                       step="0.01"
                                       name="discount_price"
                                       class="form-control"
                                       value="<?= htmlspecialchars($product['discount_price']); ?>">
                            </div>

                            <!-- FLAGS -->
                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox"
                                           name="is_popular"
                                           class="form-check-input"
                                           <?= $product['is_popular'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label">Popular</label>
                                </div>

                                <div class="form-check">
                                    <input type="checkbox"
                                           name="is_featured"
                                           class="form-check-input"
                                           <?= $product['is_featured'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label">Featured</label>
                                </div>

                                <div class="form-check">
                                    <input type="checkbox"
                                           name="status"
                                           class="form-check-input"
                                           <?= $product['status'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label">Active</label>
                                </div>
                            </div>

                            <!-- IMAGE -->
                            <div class="form-group">
                                <label>Change Image</label>
                                <input type="file" name="image" class="form-control">
                                <br>
                                <img src="<?= $mediaPath . ltrim($product['image'], '/'); ?>"
                                     width="120"
                                     height="90"
                                     style="object-fit:cover;border-radius:6px;">
                            </div>

                            <!-- SUBMIT -->
                            <div class="text-right">
                                <button type="submit"
                                        name="updateProduct"
                                        class="btn btn-info">
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

<?php
include('include/script.php');
include('include/footer.php');
?>
