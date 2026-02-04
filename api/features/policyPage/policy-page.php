<?php
require_once __DIR__ . '/../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../config/database.php';

header('Content-Type: application/json');

function getStaticPages()
{

    global $conn;

    $slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

    if ($slug === '') {
        return [
            'success' => false,
            'message' => 'Slug is required'
        ];
    }

    /**
     * Fetch single page by slug
     */
    $stmt = $conn->prepare("
    SELECT
        id,
        slug,
        title,
        content,
        status,
        created_at,
        updated_at
    FROM static_pages
    WHERE slug = ? AND status = 1
    LIMIT 1
");

    $stmt->bind_param('s', $slug);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return [
            'success' => false,
            'message' => 'Page not found'
        ];
    }

    $page = $result->fetch_assoc();

    return [
        'success' => true,
        'data' => [
            'id' => $page['id'],
            'slug' => $page['slug'],
            'title' => $page['title'],
            'content' => $page['content'],
            'last_updated' => date('F Y', strtotime($page['updated_at']))
        ]
    ];
}
