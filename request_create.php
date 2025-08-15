<?php
require_once __DIR__.'/../common/config.php'; require_once __DIR__.'/../common/utils.php';
require_login(); require_csrf();
$d=body_json();
$type=$d['type']??''; $priority=$d['priority']??'low'; $message=trim($d['message']??'');
if(!in_array($type,['content','strategy','account_handling']) || !in_array($priority,['low','medium','high']) || $message==='') json_error('Invalid request',422);
$stmt=$pdo->prepare('SELECT id FROM businesses WHERE user_id=?'); $stmt->execute([$_SESSION['user']['id']]);
$biz=$stmt->fetch(); $biz_id=$biz['id']??null;
$pdo->prepare('INSERT INTO support_requests (user_id,business_id,type,priority,message) VALUES (?,?,?,?,?)')
    ->execute([$_SESSION['user']['id'],$biz_id,$type,$priority,$message]);
echo json_encode(['ok'=>true]);
