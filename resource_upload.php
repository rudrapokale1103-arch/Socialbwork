<?php
require_once __DIR__.'/../common/config.php'; require_once __DIR__.'/../common/utils.php';
require_admin();
if(strpos($_SERVER['CONTENT_TYPE']??'','multipart/form-data')!==false){
  $title=$_POST['title']??''; $desc=$_POST['description']??null; $cat=$_POST['category']??'tips';
  if($title===''||!in_array($cat,['tips','template','guide','seo'])) json_error('Invalid',422);
  $file_url=null;
  if(isset($_FILES['file']) && $_FILES['file']['error']===UPLOAD_ERR_OK){
    @mkdir(UPLOAD_DIR,0777,true);
    $ext=pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION);
    $safe=preg_replace('/[^a-zA-Z0-9._-]/','_',$ext);
    $name='res_'.time().'_'.bin2hex(random_bytes(4)).'.'.$safe;
    $dest=UPLOAD_DIR.'/'.$name; move_uploaded_file($_FILES['file']['tmp_name'],$dest);
    $file_url='uploads/'.$name;
  }
  $pdo->prepare('INSERT INTO resources (title,description,file_url,category) VALUES (?,?,?,?)')->execute([$title,$desc,$file_url,$cat]);
  echo json_encode(['ok'=>true,'file_url'=>$file_url]); exit;
} else {
  $d=body_json(); $title=trim($d['title']??''); $desc=$d['description']??null; $cat=$d['category']??'tips'; $file_url=$d['file_url']??null;
  if($title===''||!in_array($cat,['tips','template','guide','seo'])) json_error('Invalid',422);
  $pdo->prepare('INSERT INTO resources (title,description,file_url,category) VALUES (?,?,?,?)')->execute([$title,$desc,$file_url,$cat]);
  echo json_encode(['ok'=>true]);
}
