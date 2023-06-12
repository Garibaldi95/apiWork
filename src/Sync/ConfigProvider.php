<?php

declare(strict_types=1);

namespace Sync;

use Sync\Factories\AuthHandlerFactory;
use Sync\Factories\ContactsHandlerFactory;
use Sync\Factories\SendHandlerFactory;
use Sync\Factories\TestHandlerFactory;
use Sync\Factories\SumHandlerFactory;
use Sync\Handlers\AuthHandler;
use Sync\Handlers\ContactsHandler;
use Sync\Handlers\SendHandler;
use Sync\Handlers\SumHandler;
use Sync\Handlers\TestHandler;


class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            'invokables' => [
            ],
            'factories' => [
                TestHandler::class => TestHandlerFactory::class,
                SumHandler::class => SumHandlerFactory::class,
                AuthHandler::class => AuthHandlerFactory::class,
                ContactsHandler::class => ContactsHandlerFactory::class,
                SendHandler::class => SendHandlerFactory::class,
            ],
        ];
    }
}
