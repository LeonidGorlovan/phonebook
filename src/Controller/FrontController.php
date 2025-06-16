<?php

namespace App\Controller;

use App\Core\Router;
use Exception;

class FrontController
{
    private Router $router;

    public function __construct()
    {
        $this->router = require __DIR__ . '/../../routes/web.php';
    }

    public function dispatch(): void
    {
        // Старт сессии
        session_start();

        // Получаем путь из URL
        $path = $this->getRequestPath();
        $method = $_SERVER['REQUEST_METHOD'];

        // Ищем соответствующий маршрут
        $route = $this->router->match($path, $method);

        if ($route) {
            try {
                $controllerClass = $route['controller'];
                $action = $route['action'];

                // Инициализация контроллера и вызов действия
                $controller = new $controllerClass();

                // Собираем параметры для метода контроллера (исключая controller и action)
                $params = array_diff_key($route, ['controller' => 1, 'action' => 1, 'method' => 1]);

                // Преобразуем ассоциативный массив в индексированный
                $parameters = array_values($params);

                call_user_func_array([$controller, $action], $parameters);

            } catch (Exception $e) {
                // Обработка ошибки
                echo 'Ошибка: ' . $e->getMessage();
            }
        } else {
            // Обработка 404
            header("HTTP/1.0 404 Not Found");
            echo '404 - Страница не найдена';
        }
    }

    private function getRequestPath(): string
    {
        $path = $_SERVER['PATH_INFO'] ?? '';

        if (empty($path)) {
            $path = $_SERVER['REQUEST_URI'] ?? '/';

            // Удаляем базовый путь, если он есть
            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            if ($basePath !== '/' && $basePath !== '\\') {
                $path = str_replace($basePath, '', $path);
            }
        }

        // Удаление параметров запроса
        $position = strpos($path, '?');
        if ($position !== false) {
            $path = substr($path, 0, $position);
        }

        // Удаление начального и конечного слеша
        $path = trim($path, '/');

        return $path;
    }
}