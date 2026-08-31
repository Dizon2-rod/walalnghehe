<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/supabase_client.php';
require_admin();
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_validate($_POST['csrf_token'] ?? null)) { http_response_code(419); echo json_encode(['ok'=>false,'message'=>'Invalid request.']); exit; }
try {
    $client = new \SupabaseClient(); $action = (string)($_POST['action'] ?? '');
    if ($action === 'reset') {
        foreach ($client->select('pets', ['select'=>'id']) as $pet) $client->update('pets', ['id'=>'eq.' . $pet['id']], ['hunger'=>100,'hygiene'=>100,'happiness'=>100,'energy'=>100,'mood'=>'happy','exp'=>0,'level'=>1]);
        echo json_encode(['ok'=>true,'message'=>'All guardians are rested and happy.']); exit;
    }
    if ($action !== 'save') throw new InvalidArgumentException('Unsupported admin action.');
    $petId = trim((string)($_POST['pet_id'] ?? '')); $values = [];
    foreach (['hunger','hygiene','happiness','energy'] as $stat) { $value = filter_var($_POST[$stat] ?? null, FILTER_VALIDATE_INT); if ($value === false || $value < 0 || $value > 100) throw new InvalidArgumentException('Stats must be whole numbers from 0 to 100.'); $values[$stat] = $value; }
    $values['mood'] = min($values['hunger'], $values['hygiene'], $values['happiness'], $values['energy']) > 80 ? 'ecstatic' : (min($values['hunger'], $values['hygiene']) < 30 ? 'hungry' : 'happy');
    $saved = $client->update('pets', ['id'=>'eq.' . $petId], $values);
    echo json_encode(['ok'=>true,'pet'=>$saved[0] ?? $values,'message'=>'Pet stats updated.']);
} catch (Throwable $error) { http_response_code($error instanceof InvalidArgumentException ? 400 : 500); echo json_encode(['ok'=>false,'message'=>$error->getMessage()]); }
