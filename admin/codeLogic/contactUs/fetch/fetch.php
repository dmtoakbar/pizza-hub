<?php
require_once __DIR__ . '/../../../../config/database.php';

$id = $_POST['id'] ?? '';

$query = "SELECT * FROM contact_us WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    echo json_encode([
        'success' => true,
        'data' => $result->fetch_assoc()
    ]);
} else {
    echo json_encode([
        'success' => false
    ]);
}
