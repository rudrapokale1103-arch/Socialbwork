<?php
require_once __DIR__.'/../common/config.php'; require_once __DIR__.'/../common/utils.php';
require_login();
$stmt=$pdo->prepare('SELECT sr.*, (SELECT reply FROM admin_replies ar WHERE ar.request_id=sr.id ORDER BY ar.id DESC LIMIT 1) AS last_reply
                     FROM support_requests sr WHERE sr.user_id=? ORDER BY sr.created_at DESC');
$stmt->execute([$_SESSION['user']['id']]);
echo json_encode($stmt->fetchAll());
