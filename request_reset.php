<?php
require_once __DIR__.'/../common/config.php'; require_once __DIR__.'/../common/utils.php';
rate_limit('pwreset', 5, 60); require_csrf();
$d=body_json(); $email=filter_var($d['email']??'', FILTER_VALIDATE_EMAIL);
if(!$email) json_error('Invalid email',422);
$stmt=$pdo->prepare('SELECT id FROM users WHERE email=?'); $stmt->execute([$email]); $u=$stmt->fetch();
if(!$u){ echo json_encode(['ok'=>true]); exit; } // don't leak
$token=bin2hex(random_bytes(16)); $expires=(new DateTime('+30 minutes'))->format('Y-m-d H:i:s');
$pdo->prepare('INSERT INTO password_resets (user_id, token, expires_at) VALUES (?,?,?)')->execute([$u['id'],$token,$expires]);
// In production you would email the token link. For demo, return token:
echo json_encode(['ok'=>true,'demo_token'=>$token,'note'=>'In production, send via email.']);
