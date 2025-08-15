<?php
require_once __DIR__.'/../common/config.php';
echo json_encode(['user'=> $_SESSION['user'] ?? null, 'csrf'=> $_SESSION['csrf'] ?? null]);
