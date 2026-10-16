<?php
declare(strict_types=1);

$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPassword = getenv('DB_PASSWORD') ?: '';
$dbName = getenv('DB_NAME') ?: 'saep_db';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($dbHost, $dbUser, $dbPassword, $dbName);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $exception) {
    http_response_code(500);
    exit('Não foi possível conectar ao banco. Confira as configurações em config.php.');
}
