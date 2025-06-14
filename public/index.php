<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Core/functions.php';

use App\Controller\FrontController;
use App\Core\Router;

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create router instance
$router = new Router();

// Handle all requests through the front controller
$frontController = new FrontController();
$frontController->dispatch();