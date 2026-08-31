<?php
require_once __DIR__ . '/../includes/helpers.php'; require_login();
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_validate($_POST['csrf_token'] ?? null)) { http_response_code(419); echo json_encode(['ok'=>false,'message'=>'Invalid request.']); exit; }
$id = $_POST['id'] ?? ''; $couponId = (int)($_POST['coupon_id'] ?? 0);
try { $oid = mongo_object_id($id); } catch (Throwable $e) { $oid = null; }
if (!$oid || !$couponId) { http_response_code(400); echo json_encode(['ok'=>false,'message'=>'Invalid coupon.']); exit; }
$gift = col_gifts()->findOne(['_id' => $oid]);
$coupons = (array)($gift['coupons'] ?? []); $found = false;
foreach ($coupons as &$coupon) { if ((int)($coupon['id'] ?? 0) === $couponId) { if (!empty($coupon['is_redeemed'])) { echo json_encode(['ok'=>false,'message'=>'Coupon already redeemed.']); exit; } $coupon['is_redeemed'] = true; $found = true; break; } }
unset($coupon);
if (!$found) { http_response_code(404); echo json_encode(['ok'=>false,'message'=>'Coupon not found.']); exit; }
$result = col_gifts()->updateOne(['_id'=>$oid], ['$set'=>['coupons'=>$coupons]]);
echo json_encode(['ok'=>$result->getModifiedCount() > 0,'message'=>$result->getModifiedCount() > 0 ? 'Coupon redeemed with love.' : 'Coupon already redeemed.']);
