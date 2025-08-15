<?php
require_once __DIR__.'/../common/config.php'; require_once __DIR__.'/../common/utils.php';
rate_limit('login', 20, 60); require_csrf();
$d = body_json();
$email = $d['email'] ?? ''; $pass = $d['password'] ?? '';
$stmt = $pdo->prepare('SELECT * FROM users WHERE email=?'); $stmt->execute([$email]);
$u = $stmt->fetch();
if(!$u || !password_verify($pass, $u['password_hash'])) json_error('Invalid credentials', 401);
$_SESSION['user'] = ['id'=>$u['id'],'email'=>$u['email'],'role'=>$u['role']];
echo json_encode(['ok'=>true,'user'=>$_SESSION['user']]);
