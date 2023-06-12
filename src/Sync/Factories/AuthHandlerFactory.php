<?php

declare(strict_types=1);

namespace Sync\Factories;


use Sync\Handlers\AuthHandler;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthHandlerFactory
{

    /**
     * @param ContainerInterface $container
     * @return RequestHandlerInterface
     */
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new AuthHandler();
    }
}