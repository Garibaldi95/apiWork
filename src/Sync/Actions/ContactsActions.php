<?php

namespace Sync\Actions;

use AmoCRM\Filters\CurrenciesFilter;
use Laminas\Diactoros\Response\JsonResponse;
use Sync\Service\AuthService;
use Throwable;
use Unisender\ApiWrapper\UnisenderApi;


class ContactsActions
{
    public AuthService $authService;


    /**
     * Функция для получения контактов
     * @param AuthService $authService
     * @return array
     */
    public function getContacts($authService): array
    {
        try {
            $filter = new CurrenciesFilter();
            $filter->setLimit(50);
            $page = 1;
            $k = 1;
            do {
                $filter->setPage($page++);
                $contacts = $authService->apiClient->contacts()->get($filter);
                foreach ($contacts as $contact) {
                    $customFields = $contact->getCustomFieldsValues();
                    if (!empty($customFields)) {
                        $emailField = $customFields->getBy('fieldCode', 'EMAIL');
                        $result[$k]['name'] = $contact->getName();
                        foreach ($emailField->getValues() as $email) {
                            $result[$k]['email'][] = $email->getValue();
                        }
                    } else {
                        $result[$k]['name'] = $contact->getName();
                        $result[$k]['email'] = NULL;
                    }
                    $k++;
                }
            } while (!empty($contacts->getNextPageLink()));
        } catch (Throwable $e) {
        }

        return $result;
    }

    /**
     * Функция для отправки контактов в юнисендер
     * @param $authSevrice
     * @return string[]
     */
    public function sendContacts($authSevrice): array
    {
        $unisenderApi = new UnisenderApi('6gbt6sruewng9xg3ikfupp54jkm7jc4amfopanja');
        $contacts = $this->getContacts($authSevrice);
        foreach ($contacts as $contact) {
            foreach ((array)$contact['email'] as $email) {
                $unisenderApi->importContacts([
                    'field_names[0]' => 'email',
                    'field_names[1]' => 'Name',
                    'data[0][0]' => $email,
                    'data[0][1]' => $contact['name']]);
            }
        }
        return ["status" => "ok"];
    }
}
