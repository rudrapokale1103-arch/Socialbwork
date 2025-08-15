<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

// Load .env if present
$envPath = dirname(__DIR__) . '/.env';
if (file_exists($envPath)) {
  $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  foreach ($lines as $line) {
    if (str_starts_with(trim($line),'#')) continue;
    [$k,$v] = array_map('trim', explode('=', $line, 2));
    if (!isset($_ENV[$k])) $_ENV[$k]=$v;
    putenv("$k=$v");
  }
}

$DB_HOST = getenv('DB_HOST') ?: '127.0.0.1';
$DB_NAME = getenv('DB_NAME') ?: 'social_support';
$DB_USER = getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('DB_PASS') ?: '';
define('UPLOAD_DIR', __DIR__ . '/../public/uploads');

// CORS for demo (relax as needed)
if (!headers_sent()) {
  header('Access-Control-Allow-Origin: *');
  header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
  header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');
}
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

try {
  $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
  ]);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['error' => 'DB connection failed', 'detail' => $e->getMessage()]);
  exit;
}

// CSRF token helpers
if (!isset($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
function require_csrf() {
  $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
  if ($token !== ($_SESSION['csrf'] ?? '')) {
    http_response_code(419);
    echo json_encode(['error'=>'Invalid CSRF token']);
    exit;
  }
}

// Simple rate limiting by IP path (session-based demo)
function rate_limit($key, $limit=30, $window=60){
  $now=time();
  if(!isset($_SESSION['rl'])) $_SESSION['rl'] = [];
  $_SESSION['rl'] = array_filter($_SESSION['rl'], fn($v)=>$v['reset']>$now);
  $bucket = $_SESSION['rl'][$key] ?? ['count'=>0,'reset'=>$now+$window];
  if ($bucket['count'] >= $limit) {
    http_response_code(429);
    echo json_encode(['error'=>'Too many requests, slow down']);
    exit;
  }
  $bucket['count']++;
  $_SESSION['rl'][$key]=$bucket;
}
