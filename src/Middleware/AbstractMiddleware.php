<?php

namespace App\Middleware;

abstract class AbstractMiddleware implements MiddlewareInterface
{
    protected ?MiddlewareInterface $next = null;
    
    /**
     * Set the next middleware in the chain
     * 
     * @param MiddlewareInterface $middleware
     * @return MiddlewareInterface
     */
    public function setNext(MiddlewareInterface $middleware): MiddlewareInterface
    {
        $this->next = $middleware;
        return $middleware;
    }
    
    /**
     * Call the next middleware in the chain
     * 
     * @return void
     */
    protected function processNext(): void
    {
        if ($this->next !== null) {
            $this->next->process();
        }
    }
}