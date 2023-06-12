<?php

namespace Sync\Service;

use AmoCRM\Client\AmoCRMApiClient;
use Exception;
use League\OAuth2\Client\Token\AccessToken;
use Throwable;

/**
 * Class ApiService.
 *
 * @package Sync\Api
 */
class AuthService
{
    /** @var string Базовый домен авторизации. */
    private const TARGET_DOMAIN = 'kommo.com';

    /** @var string Файл хранения токенов. */
    private const TOKENS_FILE = './tokens.json';

    /** @var AmoCRMApiClient AmoCRM клиент. */
    public AmoCRMApiClient $apiClient;

    /**
     * ApiService constructor.
     */
    public function __construct($integrationId, $integrationSecretKey, $integrationRedirectUri)
    {
        $this->apiClient = new AmoCRMApiClient(
            $integrationId,
            $integrationSecretKey,
            $integrationRedirectUri
        );
    }

    /**
     * Проверка при авторизации аккаунта
     * @param int $queyParams id передаваемого аккаунта
     * @return string|void
     */
    public function authCheck($queyParams)
    {
        try {
            if (empty($queyParams)) {
                throw new Exception('Не передан id аккаунта');
            }
            if (file_exists(self::TOKENS_FILE)) {
                $accesses = json_decode(file_get_contents(self::TOKENS_FILE), true);
            }
            if (!empty($accesses[$queyParams['id']])) {
                throw new Exception('Такой аккаунт уже авторизован');
            }

        } catch (Throwable $e) {
            exit($e->getMessage());
        }
        return $this->auth($queyParams);
    }

    /**
     * Проверка авторизован ли пользователь
     * @param int $queyParams id аккаунта
     * @return void
     */
    public function tokenCheck($queyParams)
    {
        try {
            if (file_exists(self::TOKENS_FILE)) {
                $accesses = json_decode(file_get_contents(self::TOKENS_FILE), true);
            }
            if (empty($accesses)) {
                throw new Exception('Пройдите авторизацию');
            }
            if (empty($accesses[$queyParams['id']])) {
                throw new Exception('Неправильно передан id');
            }
            if ($this->readToken($queyParams['id'])->hasExpired()) {
                throw new Exception('Expired');
            }
            $this->apiClient->setAccessToken($this->readToken($queyParams['id']))
                ->setAccountBaseDomain($this->readToken($queyParams['id'])->getValues()['base_domain']);
        } catch (Throwable $e) {
            exit($e->getMessage());
        }

    }

    /**
     * Получение токена досутпа для аккаунта.
     *
     * @param array $queryParams Входные GET параметры.
     * @return string Имя авторизованного аккаунта.
     */
    public function auth(array $queryParams): string
    {
        session_start();

        /** Занесение системного идентификатора в сессию для реализации OAuth2.0. */
        if (!empty($queryParams['id'])) {
            $_SESSION['service_id'] = $queryParams['id'];
        }

        if (isset($queryParams['referer'])) {
            $this
                ->apiClient
                ->setAccountBaseDomain($queryParams['referer'])
                ->getOAuthClient()
                ->setBaseDomain($queryParams['referer']);
        }

        try {
            if (!isset($queryParams['code'])) {
                $state = bin2hex(random_bytes(16));
                $_SESSION['oauth2state'] = $state;
                if (isset($queryParams['button'])) {
                    echo $this
                        ->apiClient
                        ->getOAuthClient()
                        ->setBaseDomain(self::TARGET_DOMAIN)
                        ->getOAuthButton([
                            'title' => 'Установить интеграцию',
                            'compact' => true,
                            'class_name' => 'className',
                            'color' => 'default',
                            'error_callback' => 'handleOauthError',
                            'state' => $state,
                        ]);
                } else {
                    $authorizationUrl = $this
                        ->apiClient
                        ->getOAuthClient()
                        ->setBaseDomain(self::TARGET_DOMAIN)
                        ->getAuthorizeUrl([
                            'state' => $state,
                            'mode' => 'post_message',
                        ]);
                    header('Location: ' . $authorizationUrl);
                }
                die;
            } elseif (
                empty($queryParams['state']) ||
                empty($_SESSION['oauth2state']) ||
                ($queryParams['state'] !== $_SESSION['oauth2state'])
            ) {
                unset($_SESSION['oauth2state']);
                exit('Invalid state');
            }
        } catch (Throwable $e) {
            die($e->getMessage());
        }

        try {
            $accessToken = $this
                ->apiClient
                ->getOAuthClient()
                ->setBaseDomain($queryParams['referer'])
                ->getAccessTokenByCode($queryParams['code']);

            if (!$accessToken->hasExpired()) {
                $this->saveToken($_SESSION['service_id'], [
                    'base_domain' => $this->apiClient->getAccountBaseDomain(),
                    'access_token' => $accessToken->getToken(),
                    'refresh_token' => $accessToken->getRefreshToken(),
                    'expires' => $accessToken->getExpires(),
                ]);
            }
        } catch (Throwable $e) {
            die($e->getMessage());
        }

        session_abort();
        return $this
            ->apiClient
            ->getOAuthClient()
            ->getResourceOwner($accessToken)
            ->getName();
    }

    /**
     * Сохранение токена авторизации.
     *
     * @param int $serviceId Системный идентификатор аккаунта.
     * @param array $token Токен доступа Api.
     * @return void
     */
    private function saveToken(int $serviceId, array $token): void
    {
        $tokens = file_exists(self::TOKENS_FILE)
            ? json_decode(file_get_contents(self::TOKENS_FILE), true)
            : [];
        $tokens[$serviceId] = $token;
        file_put_contents(self::TOKENS_FILE, json_encode($tokens, JSON_PRETTY_PRINT));
    }

    /**
     * Получение токена из файла.
     *
     * @param int $serviceId Системный идентификатор аккаунта.
     * @return AccessToken
     */
    public function readToken(int $serviceId): AccessToken
    {
        try {
            if (!file_exists(self::TOKENS_FILE)) {
                throw new Exception('Tokens file not found.');
            }

            $accesses = json_decode(file_get_contents(self::TOKENS_FILE), true);
            if (empty($accesses[$serviceId])) {
                throw new Exception("Unknown account name \"$serviceId\".");
            }

            return new AccessToken($accesses[$serviceId]);
        } catch (Throwable $e) {
            exit($e->getMessage());
        }
    }
}