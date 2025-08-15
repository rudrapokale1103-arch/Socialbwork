<?php
require_once __DIR__.'/../common/config.php'; require_once __DIR__.'/../common/utils.php';
rate_limit('pwreset_apply', 10, 60); require_csrf();
$d=body_json(); $token=$d['token']??''; $pass=$d['password']??'';
if(strlen($token)<10 || strlen($pass)<6) json_error('Invalid',422);
$stmt=$pdo->prepare('SELECT * FROM password_resets WHERE token=? AND used=0 AND expires_at>NOW()'); $stmt->execute([$token]); $row=$stmt->fetch();
if(!$row) json_error('Token invalid or expired',400);
$hash=password_hash($pass, PASSWORD_BCRYPT);
$pdo->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([$hash,$row['user_id']]);
$pdo->prepare('UPDATE password_resets SET used=1 WHERE id=?')->execute([$row['id']]);
echo json_encode(['ok'=>true]);
