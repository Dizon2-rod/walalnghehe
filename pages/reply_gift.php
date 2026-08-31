<?php
require_once __DIR__ . '/../includes/helpers.php'; require_login();
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_validate($_POST['csrf_token'] ?? null)) { http_response_code(419); echo json_encode(['ok'=>false,'message'=>'Invalid request.']); exit; }
$id = $_POST['id'] ?? ''; $message = trim((string)($_POST['message'] ?? '')); $reaction = (string)($_POST['reaction'] ?? '');
try { $oid = mongo_object_id($id); } catch (Throwable $e) { $oid = null; }
if (!$oid || $message === '' || !in_array($reaction, ['❤️','🥺','🥰','💍'], true)) { http_response_code(400); echo json_encode(['ok'=>false,'message'=>'Please choose a reaction and write a reply.']); exit; }
col_gifts()->updateOne(['_id'=>$oid], ['$set'=>['recipient_reply'=>['message'=>mb_substr($message, 0, 2000), 'reaction'=>$reaction, 'replied_at'=>mongo_utc_now()]]]);
echo json_encode(['ok'=>true,'message'=>'Your reply is safely tucked into my heart.']);
