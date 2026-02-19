<?php

function importFullDatabase(mysqli $conn): array
{
    $resultData = [
        'ran' => false,
        'status' => 'skipped',
        'message' => '',
        'tables_created' => []
    ];

    // 1️⃣ Check if tables already exist
    $check = $conn->query("SHOW TABLES");
    if ($check && $check->num_rows > 0) {
        $resultData['message'] = 'Migration skipped. Tables already exist.';
        return $resultData;
    }

    // 2️⃣ SQL file path (IMPORTANT)
    $sqlFile = __DIR__ . '/pizza_hub.sql';

    if (!file_exists($sqlFile)) {
        $resultData['status'] = 'error';
        $resultData['message'] = 'pizza_hub.sql file not found.';
        return $resultData;
    }

    // 3️⃣ Read SQL file
    $sql = file_get_contents($sqlFile);

    if (!$sql) {
        $resultData['status'] = 'error';
        $resultData['message'] = 'SQL file is empty.';
        return $resultData;
    }

    // 4️⃣ Execute SQL
    if (!$conn->multi_query($sql)) {
        $resultData['status'] = 'error';
        $resultData['message'] = $conn->error;
        return $resultData;
    }

    // 5️⃣ Flush all results
    do {
        if ($conn->error) {
            $resultData['status'] = 'error';
            $resultData['message'] = $conn->error;
            return $resultData;
        }
    } while ($conn->more_results() && $conn->next_result());

    // 6️⃣ Get created tables
    $tablesResult = $conn->query("SHOW TABLES");
    while ($row = $tablesResult->fetch_array()) {
        $resultData['tables_created'][] = $row[0];
    }

    $resultData['ran'] = true;
    $resultData['status'] = 'success';
    $resultData['message'] = 'Database migrated successfully.';

    return $resultData;
}
