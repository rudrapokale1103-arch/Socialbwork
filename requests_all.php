<?php
require_once __DIR__.'/../common/config.php'; require_once __DIR__.'/../common/utils.php';
require_admin();
$page=max(1, intval($_GET['page'] ?? 1)); $size=max(1, min(50, intval($_GET['size'] ?? 10)));
$offset=($page-1)*$size;
$type=$_GET['type']??null; $status=$_GET['status']??null; $q=trim($_GET['q']??'');
$sql='FROM support_requests sr LEFT JOIN users u ON u.id=sr.user_id LEFT JOIN businesses b ON b.id=sr.business_id WHERE 1=1';
$args=[];
if($type && in_array($type,['content','strategy','account_handling'])){ $sql.=' AND sr.type=?'; $args[]=$type; }
if($status && in_array($status,['open','in_progress','answered','closed'])){ $sql.=' AND sr.status=?'; $args[]=$status; }
if($q!==''){ $sql.=' AND (sr.message LIKE ? OR u.email LIKE ? OR b.name LIKE ?)'; $args[]="%$q%"; $args[]="%$q%"; $args[]="%$q%"; }
$stmt=$pdo->prepare('SELECT COUNT(*) AS c '.$sql); $stmt->execute($args); $total=$stmt->fetch()['c'] ?? 0;
$stmt=$pdo->prepare('SELECT sr.*, u.email, b.name AS business_name '.$sql.' ORDER BY sr.created_at DESC LIMIT '.$size.' OFFSET '.$offset); $stmt->execute($args); $rows=$stmt->fetchAll();
echo json_encode(['items'=>$rows,'page'=>$page,'size'=>$size,'total'=>$total]);
