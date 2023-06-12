<?php

declare(strict_types=1);

namespace Sync\Handlers;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sync\Actions\ContactsActions;
use Sync\Service\AuthService;


class ContactsHandler implements RequestHandlerInterface
{
    /**
     * @var AuthService
     */
    public AuthService $authService;

    /**
     * @var ContactsActions
     */
    public ContactsActions $contactsActions;


    public function __construct()
    {
        $this->authService = new AuthService(
            'a479d7c9-36a5-45a4-b9de-da4f12248b72',
            '81Ij4pKXuwMH6qKMgeQy6w7FD21AW7g4f9U8fuLN3oQ7KZxpbxJYjW8TitTbVrJX',
            'https://b307-2a00-1370-8184-21ac-6152-a51a-2ae7-70b1.ngrok-free.app/auth'
        );
        $this->contactsActions = new ContactsActions();
    }

    /**
     * Вывод всех контактов с их мэйлами
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {

        $this->authService->tokenCheck((int)$request->getQueryParams());

        return new JsonResponse([
            $this->contactsActions->getContacts($this->authService)
        ]);
    }
}
