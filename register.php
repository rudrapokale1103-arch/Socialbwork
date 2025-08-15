<?php
require_once __DIR__.'/../common/config.php'; require_once __DIR__.'/../common/utils.php';
rate_limit('register', 10, 60); require_csrf();
$d = body_json();
$email = filter_var($d['email'] ?? '', FILTER_VALIDATE_EMAIL);
$pass  = $d['password'] ?? '';
if(!$email || strlen($pass) < 6) json_error('Invalid email or password (min 6 chars)', 422);
$stmt = $pdo->prepare('SELECT id FROM users WHERE email=?'); $stmt->execute([$email]);
if($stmt->fetch()) json_error('Email already exists', 409);
$hash = password_hash($pass, PASSWORD_BCRYPT);
$pdo->prepare('INSERT INTO users (email, password_hash) VALUES (?,?)')->execute([$email, $hash]);
$_SESSION['user'] = ['id'=>$pdo->lastInsertId(), 'email'=>$email, 'role'=>'user'];
echo json_encode(['ok'=>true, 'user'=>$_SESSION['user']]);
