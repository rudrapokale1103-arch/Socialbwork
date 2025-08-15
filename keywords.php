<?php
require_once __DIR__.'/common/config.php';
$q = strtolower(trim($_GET['q'] ?? ''));
if($q===''){ http_response_code(400); echo json_encode(['error'=>'Missing q']); exit; }
$stmt=$pdo->prepare('SELECT * FROM keyword_cache WHERE query=?'); $stmt->execute([$q]);
$row=$stmt->fetch();
if($row){
  echo json_encode(['query'=>$q,'suggestions'=>json_decode($row['suggestions'],true),'trend'=>json_decode($row['trend'],true),'cached'=>true]); exit;
}
$map=['cafe'=>['coffee shop','latte art','local cafe','best coffee','cafe near me'],'salon'=>['hair stylist','salon offers','hair spa','bridal makeup','men haircut'],'gym'=>['fitness training','personal trainer','weight loss tips','gym near me','workout plan']];
$sg=$map[$q] ?? ["%s deals","%s tips","best %s","%s near me","%s ideas"];
$sg=array_map(fn($s)=>sprintf($s,$q), $sg); array_unshift($sg,$q); $sg=array_values(array_unique($sg));
$trend=[]; for($i=0;$i<12;$i++){ $trend[] = rand(20,100); }
$pdo->prepare('INSERT INTO keyword_cache (query,suggestions,trend) VALUES (?,?,?) ON DUPLICATE KEY UPDATE suggestions=VALUES(suggestions), trend=VALUES(trend)')->execute([$q,json_encode(array_slice($sg,0,10)),json_encode($trend)]);
echo json_encode(['query'=>$q,'suggestions'=>array_slice($sg,0,10),'trend'=>$trend,'cached'=>false]);
