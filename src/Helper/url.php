<?php

use App\Core\Router;

if (!function_exists('url')) {
    /**
     * Генерирует URL по имени маршрута
     *
     * @param string $routeName
     * @param array $params
     * @param bool $absolutePath
     * @return string
     */
    function url(string $routeName, array $params = [], bool $absolutePath = false): string
    {
        try {
            $router = Router::getInstance();
            $path = $router->generateUrl($routeName, $params);

            if (!empty($path) && $path[0] !== '/') {
                $path = '/' . $path;
            }

            if ($absolutePath) {
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                return $protocol . '://' . $host . $path;
            }

            return $path;
        } catch (Exception $e) {
            error_log('Ошибка генерации URL: ' . $e->getMessage());
            return '#';
        }
    }
}