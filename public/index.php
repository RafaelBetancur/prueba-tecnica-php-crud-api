<?php
require __DIR__ . '/../vendor/autoload.php';

// Configuración inicial
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$config = include __DIR__ . '/../config/database.php';

// Determinar si es una petición API
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$isApiRequest = strpos($requestUri, '/api/') === 0;

// Configurar headers según el tipo de petición
if ($isApiRequest) {
    header("Content-Type: application/json");
    header("Access-Control-Allow-Origin: http://localhost:8080");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
}

try {
    // Conexión a la base de datos
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']}",
        $config['username'],
        $config['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $controller = new \App\Controllers\ApiResultController($pdo);
    
    $requestMethod = $_SERVER['REQUEST_METHOD'];

    // Manejo de rutas
    switch (true) {
        // Ruta principal - servir HTML
        case $requestMethod === 'GET' && $requestUri === '/':
            include __DIR__ . '/index.html';
            break;
            
        // Endpoints API
        case $isApiRequest:
            switch (true) {
                case $requestMethod === 'GET' && $requestUri === '/api/results':
                    echo json_encode($controller->getAllResults());
                    break;
                    
                case $requestMethod === 'GET' && preg_match('/^\/api\/results\/(\d+)$/', $requestUri, $matches):
                    $result = $controller->getResult((int)$matches[1]);
                    if ($result) {
                        echo json_encode($result);
                    } else {
                        http_response_code(404);
                        echo json_encode(['error' => 'Not found']);
                    }
                    break;
                    
                case $requestMethod === 'POST' && $requestUri === '/api/results':
                    $data = json_decode(file_get_contents('php://input'), true);
                    http_response_code(201);
                    echo json_encode($controller->createResult($data));
                    break;
                    
                case $requestMethod === 'PUT' && preg_match('/^\/api\/results\/(\d+)$/', $requestUri, $matches):
                    $data = json_decode(file_get_contents('php://input'), true);
                    $result = $controller->updateResult((int)$matches[1], $data);
                    if ($result) {
                        echo json_encode($result);
                    } else {
                        http_response_code(404);
                        echo json_encode(['error' => 'Not found']);
                    }
                    break;
                    
                case $requestMethod === 'DELETE' && preg_match('/^\/api\/results\/(\d+)$/', $requestUri, $matches):
                    $controller->deleteResult((int)$matches[1]);
                    http_response_code(204);
                    break;
                    
                case $requestMethod === 'POST' && $requestUri === '/api/initialize':
                    echo json_encode($controller->initializeData());
                    break;
                    
                case $requestMethod === 'POST' && $requestUri === '/api/improve':
                    echo json_encode($controller->improveAllBadResults());
                    break;
                    
                case $requestMethod === 'GET' && $requestUri === '/api/logs':
                    echo json_encode($controller->getExecutionLogs());
                    break;
                    
                case $requestMethod === 'DELETE' && $requestUri === '/api/results/all':
                    echo json_encode($controller->deleteAllResults());
                    break;
                    
                default:
                    http_response_code(404);
                    echo json_encode(['error' => 'API endpoint not found']);
            }
            break;
            
        // Cualquier otra ruta - servir HTML
        default:
            include __DIR__ . '/index.html';
    }
    
} catch (PDOException $e) {
    $errorResponse = ['error' => 'Database error: ' . $e->getMessage()];
    if ($isApiRequest) {
        http_response_code(500);
        echo json_encode($errorResponse);
    } else {
        die('<h1>Error</h1><pre>' . htmlspecialchars($errorResponse['error']) . '</pre>');
    }
} catch (Exception $e) {
    $errorResponse = ['error' => $e->getMessage()];
    if ($isApiRequest) {
        http_response_code(500);
        echo json_encode($errorResponse);
    } else {
        die('<h1>Error</h1><pre>' . htmlspecialchars($errorResponse['error']) . '</pre>');
    }
}