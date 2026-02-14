<?php
// Compatibility stub to serve legacy installer when running `php artisan serve` from laravel/.
// Delegates to the root public/install.php without loading Laravel.
$root = dirname(__DIR__, 2); // repo root
chdir($root);
require $root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'install.php';
