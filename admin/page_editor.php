<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('./authentication/authentication.php');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';
include('include/header.php');
include('include/topbar.php');
include('include/sidebar.php');

use Ramsey\Uuid\Uuid;

/* =========================
   GET SLUG FROM URL
========================= */

$slug = $_GET['slug'] ?? null;

if (!$slug) {
    die('Invalid page');
}

$saved = false;

/* =========================
   DEFAULT TITLES & CONTENT
========================= */
$defaultPages = [
    'refund-policy' => [
        'title' => 'Refund Policy',
        'content' => '<h1>Refund Policy</h1><p>This is the refund policy.</p>'
    ],
    'privacy-policy' => [
        'title' => 'Privacy Policy',
        'content' => '<h1>Privacy Policy</h1><p>Your privacy matters to us.</p>'
    ],
    'terms-conditions' => [
        'title' => 'Terms & Conditions',
        'content' => '<h1>Terms & Conditions</h1><p>Please read carefully.</p>'
    ],
    'shipping-policy' => [
        'title' => 'Shipping Policy',
        'content' => '<h1>Shipping Policy</h1><p>Shipping information.</p>'
    ],
    'cancellation-policy' => [
        'title' => 'Cancellation Policy',
        'content' => '<h1>Cancellation Policy</h1><p>Cancellation rules.</p>'
    ],
    'about-us' => [
        'title' => 'About Us',
        'content' => '<h1>About Us</h1><p>Company information.</p>'
    ],
    'faq' => [
        'title' => 'FAQ',
        'content' => '<h1>FAQ</h1><p>Frequently asked questions.</p>'
    ],
];

/* =========================
   FETCH PAGE
========================= */
$stmt = $conn->prepare(
    "SELECT title, content FROM static_pages WHERE slug = ? LIMIT 1"
);
$stmt->bind_param("s", $slug);
$stmt->execute();
$page = $stmt->get_result()->fetch_assoc();

/* =========================
   AUTO-CREATE IF NOT EXISTS
========================= */
if (!$page) {
    $pageId  = Uuid::uuid4()->toString();
    $title   = $defaultPages[$slug]['title'] ?? ucwords(str_replace('-', ' ', $slug));
    $content = $defaultPages[$slug]['content'] ?? '<h1>' . $title . '</h1><p>Content coming soon.</p>';

    $insert = $conn->prepare("
        INSERT INTO static_pages (id, slug, title, content, status)
        VALUES (?, ?, ?, ?, 1)
    ");
    $insert->bind_param("ssss", $pageId, $slug, $title, $content);
    $insert->execute();
} else {
    $title   = $page['title'];
    $content = $page['content'];
}

/* =========================
   SAVE UPDATE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $newContent = $_POST['content'];

    $update = $conn->prepare(
        "UPDATE static_pages SET content = ?, updated_at = NOW() WHERE slug = ?"
    );
    $update->bind_param("ss", $newContent, $slug);
    $update->execute();

    $content = $newContent;
    $saved = true;
}
?>

<div class="content-wrapper">

    <!-- Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="fas fa-file-alt mr-2"></i>
                        <?= htmlspecialchars($title) ?>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active"><?= htmlspecialchars($title) ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">

                <!-- Editor -->
                <div class="col-lg-7">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-edit"></i> Edit Content
                            </h3>
                        </div>

                        <div class="card-body">
                            <?php if ($saved): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i>
                                    Page updated successfully
                                </div>
                            <?php endif; ?>

                            <form method="POST">
                                <div class="form-group">
                                    <label>HTML Content</label>
                                    <textarea
                                        name="content"
                                        rows="18"
                                        class="form-control"
                                        style="font-family: monospace;"><?= htmlspecialchars($content) ?></textarea>
                                </div>

                                <button type="submit" name="save" class="btn btn-primary float-right">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Preview -->
                <div class="col-lg-5">
                    <div class="card card-secondary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-eye"></i> Live Preview
                            </h3>
                        </div>

                        <div class="card-body" style="max-height:600px; overflow-y:auto;">
                            <?= $content ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

</div>

<?php include('include/script.php'); ?>
<?php include('include/footer.php'); ?>