<?php
function json_error($msg,$code=400){ http_response_code($code); echo json_encode(['error'=>$msg]); exit; }
function body_json(){ $raw=file_get_contents('php://input'); $d=json_decode($raw,true); return is_array($d)?$d:[]; }
function require_login(){ if(!isset($_SESSION['user'])) json_error('Unauthorized',401); }
function require_admin(){ require_login(); if($_SESSION['user']['role']!=='admin') json_error('Forbidden',403); }
function notify_user($pdo,$user_id,$message){ $stmt=$pdo->prepare('INSERT INTO notifications (user_id,message) VALUES (?,?)'); $stmt->execute([$user_id,$message]); }
