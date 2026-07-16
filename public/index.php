<?php

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));

require ROOT_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Autoloader.php';

$autoloader = App\Core\Autoloader::boot(ROOT_PATH);

$app = new App\Core\Application(ROOT_PATH);

$app->run();
