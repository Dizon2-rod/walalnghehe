<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/pet_service.php';
require_login();

header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_validate($_POST['csrf_token'] ?? null)) { http_response_code(419); echo json_encode(['ok' => false, 'message' => 'Invalid request.']); exit; }
try {
    $petId = trim((string)($_POST['pet_id'] ?? ''));
    $action = trim((string)($_POST['action'] ?? ''));
    $service = new \PetService();
    $pet = $service->act($petId, $action);
    echo json_encode(['ok' => true, 'pet' => $pet, 'action' => $action]);
} catch (Throwable $error) {
    http_response_code($error instanceof InvalidArgumentException ? 400 : 500);
    echo json_encode(['ok' => false, 'message' => $error->getMessage()]);
}
