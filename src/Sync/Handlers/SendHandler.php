<?php

declare(strict_types=1);

namespace Sync\Handlers;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sync\Actions\ContactsActions;
use Sync\Service\AuthService;
use Unisender\ApiWrapper\UnisenderApi;


class SendHandler implements RequestHandlerInterface
{
    public AuthService $authService;

    public ContactsActions $contactsActions;

    public function __construct()
    {
        $this->authService = new AuthService(
            'a479d7c9-36a5-45a4-b9de-da4f12248b72',
            '81Ij4pKXuwMH6qKMgeQy6w7FD21AW7g4f9U8fuLN3oQ7KZxpbxJYjW8TitTbVrJX',
            'https://f226-2a00-1370-8184-21ac-dc6-4c30-8d6d-8b78.ngrok-free.app/auth'
        );
        $this->contactsActions = new ContactsActions();
    }

    /**
     * Отправка контактов и их мэйлов в сервис юнисендер
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->authService->tokenCheck($request->getQueryParams());


        return new JsonResponse([
            $this->contactsActions->sendContacts($this->authService)
        ]);
    }
}