<?php

namespace App\Middleware;

interface MiddlewareInterface
{
    /**
     * Process the request and optionally delegate to the next middleware
     * 
     * @return void
     */
    public function process(): void;
}