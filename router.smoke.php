<?php
declare(strict_types=1);

chdir(__DIR__);
header('X-Smoke-Router: 1');
require __DIR__ . '/public/router.php';
