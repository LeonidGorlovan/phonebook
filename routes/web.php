<?php

use App\Core\Router;
use App\Controller\AuthController;
use App\Controller\ContactController;

$router = new Router();

$router->get('', ['controller' => AuthController::class, 'action' => 'login'], 'home');

$router->get('contacts', ['controller' => ContactController::class, 'action' => 'index'], 'contacts.index');
$router->get('contacts/{id}', ['controller' => ContactController::class, 'action' => 'view'], 'contacts.show');
$router->post('contacts', ['controller' => ContactController::class, 'action' => 'store'], 'contacts.store');
$router->delete('contacts/delete/{id}', ['controller' => ContactController::class, 'action' => 'delete'], 'contacts.delete');

$router->get('auth/login', ['controller' => AuthController::class, 'action' => 'login'], 'auth.login');
$router->post('auth/login', ['controller' => AuthController::class, 'action' => 'login']);
$router->get('auth/register', ['controller' => AuthController::class, 'action' => 'register'], 'auth.register');
$router->post('auth/register', ['controller' => AuthController::class, 'action' => 'register']);
$router->get('auth/logout', ['controller' => AuthController::class, 'action' => 'logout'], 'auth.logout');

return $router;