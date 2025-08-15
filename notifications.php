<?php
require_once __DIR__.'/../common/config.php'; require_once __DIR__.'/../common/utils.php';
require_login();
if($_SERVER['REQUEST_METHOD']==='POST'){ require_csrf(); $pdo->prepare('UPDATE notifications SET is_read=1 WHERE user_id=?')->execute([$_SESSION['user']['id']]); echo json_encode(['ok'=>true]); exit; }
$stmt=$pdo->prepare('SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 50'); $stmt->execute([$_SESSION['user']['id']]);
echo json_encode($stmt->fetchAll());
