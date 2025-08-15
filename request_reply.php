<?php
require_once __DIR__.'/../common/config.php'; require_once __DIR__.'/../common/utils.php';
require_admin(); require_csrf();
$d=body_json(); $req_id=intval($d['request_id']??0); $reply=trim($d['reply']??''); $status=$d['status']??null;
if($req_id<=0 || $reply==='') json_error('Invalid',422);
$stmt=$pdo->prepare('SELECT user_id FROM support_requests WHERE id=?'); $stmt->execute([$req_id]); $req=$stmt->fetch();
if(!$req) json_error('Request not found',404);
$pdo->prepare('INSERT INTO admin_replies (request_id, admin_id, reply) VALUES (?,?,?)')->execute([$req_id,$_SESSION['user']['id'],$reply]);
if($status && in_array($status,['open','in_progress','answered','closed'])){
  $pdo->prepare('UPDATE support_requests SET status=? WHERE id=?')->execute([$status,$req_id]);
} else {
  $pdo->prepare("UPDATE support_requests SET status='answered' WHERE id=?")->execute([$req_id]);
}
notify_user($pdo,$req['user_id'],'Your support request has a new reply.');
echo json_encode(['ok'=>true]);
