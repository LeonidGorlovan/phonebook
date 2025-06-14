<?php

/**
 * Generate a URL from a named route
 * 
 * @param string $routeName Name of the route
 * @param array $params Parameters for the route
 * @return string Generated URL
 */
function url(string $routeName, array $params = []): string
{
    global $router;
    return '/' . $router->generateUrl($routeName, $params);
}