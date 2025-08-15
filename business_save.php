<?php
require_once __DIR__.'/../common/config.php'; require_once __DIR__.'/../common/utils.php';
require_login(); require_csrf();
$d = body_json();
$name = trim($d['name'] ?? '');
if ($name==='') json_error('Business name required', 422);
$stmt=$pdo->prepare('SELECT id FROM businesses WHERE user_id=?'); $stmt->execute([$_SESSION['user']['id']]);
$exists=$stmt->fetch();
if($exists){
  $pdo->prepare('UPDATE businesses SET name=?,category=?,location=?,description=?,website=?,instagram=?,facebook=?,twitter=?,youtube=?,tiktok=? WHERE user_id=?')
      ->execute([$name,$d['category']??null,$d['location']??null,$d['description']??null,$d['website']??null,$d['instagram']??null,$d['facebook']??null,$d['twitter']??null,$d['youtube']??null,$d['tiktok']??null,$_SESSION['user']['id']]);
} else {
  $pdo->prepare('INSERT INTO businesses (user_id,name,category,location,description,website,instagram,facebook,twitter,youtube,tiktok) VALUES (?,?,?,?,?,?,?,?,?,?,?)')
      ->execute([$_SESSION['user']['id'],$name,$d['category']??null,$d['location']??null,$d['description']??null,$d['website']??null,$d['instagram']??null,$d['facebook']??null,$d['twitter']??null,$d['youtube']??null,$d['tiktok']??null]);
}
echo json_encode(['ok'=>true]);
