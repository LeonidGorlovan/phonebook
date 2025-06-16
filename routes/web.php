<?php

use App\Core\Router;
use App\Controller\AuthController;
use App\Controller\ContactController;

$router = Router::getInstance();

$router->get('', ['controller' => AuthController::class, 'action' => 'login'], 'home');

$router->get('contacts', ['controller' => ContactController::class, 'action' => 'index'], 'contacts.index');
$router->get('contacts/{id}', ['controller' => ContactController::class, 'action' => 'show'], 'contacts.show');
$router->post('contacts', ['controller' => ContactController::class, 'action' => 'store'], 'contacts.store');
$router->get('contacts/edit/{id}', ['controller' => ContactController::class, 'action' => 'edit'], 'contacts.edit');
$router->put('contacts/{id}', ['controller' => ContactController::class, 'action' => 'update'], 'contacts.update');
$router->delete('contacts/delete/{id}', ['controller' => ContactController::class, 'action' => 'delete'], 'contacts.delete');

$router->get('auth/login', ['controller' => AuthController::class, 'action' => 'login'], 'auth.login');
$router->post('auth/login', ['controller' => AuthController::class, 'action' => 'login'], 'auth.login.send');
$router->get('auth/register', ['controller' => AuthController::class, 'action' => 'register'], 'auth.register');
$router->post('auth/register', ['controller' => AuthController::class, 'action' => 'register'], 'auth.register.send');
$router->get('auth/logout', ['controller' => AuthController::class, 'action' => 'logout'], 'auth.logout');

return $router;