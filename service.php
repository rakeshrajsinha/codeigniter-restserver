<?php

header('Content-Type: application/json');

$DB_HOST = 'meeucart.mysql.database.azure.com';
$DB_USER = 'meeucart';
$DB_PASSWORD = 'meeucart@123';
$DB_NAME = 'meeucart';
$DB_PORT = 3306;

// Connect to MySQL
function getDbConnection() {
    global $DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME, $DB_PORT;
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME, $DB_PORT);

    if ($conn->connect_error) {
        die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
    }
    return $conn;
}

// Handle GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $procName = $_GET['proc_name'] ?? '';
    $params = json_decode($_GET['params'] ?? '{}', true);

    if (!$procName) {
        echo json_encode(['error' => 'Procedure name is required']);
        exit;
    }

    $conn = getDbConnection();
    $placeholders = implode(',', array_fill(0, count($params), '?'));
    $stmt = $conn->prepare("CALL $procName($placeholders)");
    if ($stmt) {
        $stmt->bind_param(str_repeat('s', count($params)), ...array_values($params));
        $stmt->execute();
        $result = $stmt->get_result();
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        $stmt->close();
    } else {
        echo json_encode(['error' => $conn->error]);
    }
    $conn->close();
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $procName = $input['procedure_name'] ?? '';
    $params = $input['parameters'] ?? [];

    if (!$procName) {
        echo json_encode(['error' => 'Procedure name is required']);
        exit;
    }

    $conn = getDbConnection();
    $placeholders = implode(',', array_fill(0, count($params), '?'));
    $stmt = $conn->prepare("CALL $procName($placeholders)");

    if ($stmt) {
        $stmt->bind_param(str_repeat('s', count($params)), ...array_values($params));
        $stmt->execute();
        echo json_encode(['message' => 'Stored procedure executed successfully']);
        $stmt->close();
    } else {
        echo json_encode(['error' => $conn->error]);
    }
    $conn->close();
}

?>

<!-- Save this as index.php and you’re ready to deploy! Let me know if you want me to guide you through Azure setup. 🚀 -->
