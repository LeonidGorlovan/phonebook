<?php

namespace App\Core;

use Exception;

class Router
{
    private array $routes = [];
    private array $namedRoutes = [];
    private ?string $currentRoute = null;
    private array $currentParams = [];
    private static ?Router $instance = null;

    public static function getInstance(): Router
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Добавляет маршрут для любого HTTP-метода
     *
     * @param string $method HTTP метод (GET, POST и т.д.)
     * @param string $route URL шаблон маршрута
     * @param array $params Параметры контроллера и действия
     * @param string|null $name Необязательное имя маршрута для обратной маршрутизации
     * @return void
     */
    public function addRoute(string $method, string $route, array $params = [], ?string $name = null): void
    {
        // Конвертируем маршрут в регулярное выражение
        $route = preg_replace('/\//', '\\/', $route);
        $route = preg_replace('/\{([a-z]+)}/', '(?P<\1>[^\/]+)', $route);
        $pattern = '/^' . $route . '$/i';

        // Сохраняем HTTP-метод в параметрах
        $params['method'] = strtoupper($method);

        // Добавляем метод к ключу для уникальности
        $routeKey = $pattern . '_' . strtoupper($method);

        // Сохраняем маршрут с уникальным ключом
        $this->routes[$routeKey] = [
            'pattern' => $pattern,
            'params' => $params
        ];

        if ($name) {
            $this->namedRoutes[$name] = $routeKey;
        }
    }

    /**
     * Добавляет GET-маршрут
     *
     * @param string $route URL шаблон маршрута
     * @param array $params Параметры контроллера и действия
     * @param string|null $name Необязательное имя маршрута
     * @return void
     */
    public function get(string $route, array $params = [], ?string $name = null): void
    {
        $this->addRoute('GET', $route, $params, $name);
    }

    /**
     * Добавляет POST-маршрут
     *
     * @param string $route URL шаблон маршрута
     * @param array $params Параметры контроллера и действия
     * @param string|null $name Необязательное имя маршрута
     * @return void
     */
    public function post(string $route, array $params = [], ?string $name = null): void
    {
        $this->addRoute('POST', $route, $params, $name);
    }

    /**
     * Добавляет PUT-маршрут
     *
     * @param string $route URL шаблон маршрута
     * @param array $params Параметры контроллера и действия
     * @param string|null $name Необязательное имя маршрута
     * @return void
     */
    public function put(string $route, array $params = [], ?string $name = null): void
    {
        $this->addRoute('PUT', $route, $params, $name);
    }

    /**
     * Добавляет DELETE-маршрут
     *
     * @param string $route URL шаблон маршрута
     * @param array $params Параметры контроллера и действия
     * @param string|null $name Необязательное имя маршрута
     * @return void
     */
    public function delete(string $route, array $params = [], ?string $name = null): void
    {
        $this->addRoute('DELETE', $route, $params, $name);
    }

    /**
     * Сопоставляет URL с маршрутами в таблице маршрутизации
     *
     * @param string $url URL маршрута
     * @param string $method HTTP-метод запроса
     * @return array|false Параметры маршрута, если маршрут найден, false в противном случае
     */
    public function match(string $url, string $method = 'GET'): array|false
    {
        $method = strtoupper($method);

        foreach ($this->routes as $routeKey => $routeData) {
            $pattern = $routeData['pattern'];
            $params = $routeData['params'];

            // Проверяем соответствие HTTP-метода
            if ($params['method'] !== $method) {
                continue;
            }

            if (preg_match($pattern, $url, $matches)) {
                foreach ($matches as $key => $match) {
                    if (is_string($key)) {
                        $params[$key] = $match;
                    }
                }
                $this->currentRoute = $routeKey;
                $this->currentParams = $params;
                return $params;
            }
        }

        return false;
    }

    /**
     * Генерирует URL для именованного маршрута
     *
     * @param string $name Имя маршрута
     * @param array $params Параметры для URL
     *
     * @return string Сгенерированный URL
     * @throws Exception
     */
    public function generateUrl(string $name, array $params = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new Exception("Маршрут с именем '$name' не найден");
        }

        $routePattern = $this->namedRoutes[$name];
        $route = $this->extractRouteFromPattern($routePattern);

        // Заменяем именованные параметры значениями
        foreach ($params as $paramName => $paramValue) {
            $route = str_replace("{{$paramName}}", $paramValue, $route);
        }

        // Проверяем, что все параметры были заменены
        if (preg_match('/{([a-z]+)}/', $route, $matches)) {
            throw new Exception("Отсутствует параметр '{$matches[1]}' для маршрута '{$name}'");
        }

        return $route;
    }

    /**
     * Извлекает исходный маршрут из шаблона регулярного выражения
     *
     * @param string $pattern Шаблон регулярного выражения
     * @return string Исходный маршрут
     */
    private function extractRouteFromPattern(string $pattern): string
    {
        // Удаляем HTTP метод из ключа, если он присутствует
        if (str_contains($pattern, '_')) {
            $pattern = explode('_', $pattern)[0];
        }

        // Извлекаем имена параметров из регулярного выражения
        preg_match_all('/\(\?P<([a-z0-9_]+)>/', $pattern, $matches);
        $paramNames = $matches[1] ?? [];

        // Удаляем начало и конец регулярного выражения и экранирование слешей
        $route = preg_replace('/^\/\^|\$\/i$/', '', $pattern);
        $route = str_replace('\\/', '/', $route);

        // Заменяем каждую именованную группу на {имя}
        foreach ($paramNames as $name) {
            $route = preg_replace('/\(\?P<' . $name . '>[^)]+\)/', '{' . $name . '}', $route);
        }

        return $route;
    }

    /**
     * Получает текущий маршрут
     *
     * @return string|null
     */
    public function getCurrentRoute(): ?string
    {
        return $this->currentRoute;
    }

    /**
     * Получает параметры текущего маршрута
     *
     * @return array
     */
    public function getCurrentParams(): array
    {
        return $this->currentParams;
    }

    /**
     * Получает controller и action из текущих параметров
     *
     * @return array Массив с controller и action
     * @throws Exception Если controller или action не определены
     */
    public function getControllerAction(): array
    {
        if (empty($this->currentParams['controller'])) {
            throw new Exception("Контроллер не определен для текущего маршрута");
        }

        if (empty($this->currentParams['action'])) {
            throw new Exception("Действие не определено для текущего маршрута");
        }

        return [
            'controller' => $this->currentParams['controller'],
            'action' => $this->currentParams['action']
        ];
    }
}