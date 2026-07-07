<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Config;
use App\Core\Router;
use App\Core\Session;

Config::load(__DIR__ . '/../config/config.php');

Session::start();

$router = new Router();

require __DIR__ . '/../routes/web.php';

$router->dispatch();
