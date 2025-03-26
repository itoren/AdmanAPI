<?php

namespace Adman;

use InvalidArgumentException;

class AdmanAPI
{
    private string $url = "https://adman.com/api/";
    private string $login;
    private string $mdpass;

    public function __construct(string $login, string $mdpass)
    {
        $this->login = $login;
        $this->mdpass = $mdpass;
    }

    /**
     * Отправка POST-запроса к API.
     *
     * @param string $method Метод API
     * @param array $data Данные для отправки
     * @return array Ответ сервера в формате массива
     */
    private function sendRequest(string $method, array $data = []): array
    {
        $data['login'] = $this->login;
        $data['mdpass'] = $this->mdpass;

        $ch = curl_init($this->url . $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['req' => json_encode($data)]));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        if ($error = curl_error($ch)) {
            throw new InvalidArgumentException("CURL error: $error");
        }
        curl_close($ch);

        $result = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException("JSON decode error: " . json_last_error_msg());
        }

        if ($result['status'] === 'error') {
            throw new InvalidArgumentException("API error: {$result['error']}");
        }

        return $result;
    }

    // Базовые методы

    /**
     * Получить баланс аккаунта.
     *
     * @return float Баланс аккаунта
     */
    public function getBalance(): float
    {
        $response = $this->sendRequest('account/balance');
        return (float)$response['balance'];
    }

    /**
     * Получить список тарифов.
     *
     * @param int|null $groupId Тип услуги (0 - все, 2 - VPS, 3 - Сервера, 4 - SSL, 5 - Хостинг)
     * @param int|null $tarifId Конкретный тариф
     * @return array Список тарифов
     */
    public function getTariffs(?int $groupId = null, ?int $tarifId = null): array
    {
        $params = [];
        if ($groupId !== null) {
            $params['filter']['group_id'] = $groupId;
        }
        if ($tarifId !== null) {
            $params['filter']['tarif_id'] = $tarifId;
        }

        $response = $this->sendRequest('order/tarifs', $params);
        return $response['data'] ?? [];
    }

    /**
     * Получить список имен параметров дополнительных услуг.
     *
     * @return array Список имен параметров
     */
    public function getAdditionalServiceNames(): array
    {
        return $this->sendRequest('order/names')['data'] ?? [];
    }

    /**
     * Получить список операционных систем для тарифа.
     *
     * @param int $tarifId Номер тарифа
     * @return array Список операционных систем
     */
    public function getOperatingSystems(int $tarifId): array
    {
        return $this->sendRequest('order/os', ['tarif_id' => $tarifId])['data'] ?? [];
    }

    /**
     * Создать новый заказ.
     *
     * @param int $tarif Номер тарифа
     * @param int $period Период оплаты (1 - месяц, 2 - два месяца и т.д.)
     * @param array $options Дополнительные параметры заказа
     * @return int Номер заказа
     */
    public function createOrder(int $tarif, int $period, array $options = []): int
    {
        $data = [
            'tarif' => $tarif,
            'period' => $period,
        ];
        $data = array_merge($data, $options);

        $response = $this->sendRequest('order/add', $data);
        return (int)$response['id'];
    }

    /**
     * Продлить существующий заказ.
     *
     * @param int $zakazId Номер заказа
     * @param int $period Период продления (1 - месяц, 2 - два месяца и т.д.)
     * @return array Ответ сервера
     */
    public function prolongOrder(int $zakazId, int $period): array
    {
        return $this->sendRequest('order/prolong', ['zakaz_id' => $zakazId, 'period' => $period]);
    }

    /**
     * Получить список заказов.
     *
     * @param array $filters Фильтры для списка заказов
     * @return array Список заказов
     */
    public function getOrderList(array $filters = []): array
    {
        return $this->sendRequest('order/list', ['filter' => $filters])['data'] ?? [];
    }

    /**
     * Получить информацию о заказе.
     *
     * @param int $zakazId Номер заказа
     * @return array Информация о заказе
     */
    public function getOrderInfo(int $zakazId): array
    {
        return $this->sendRequest('order/info', ['zakaz_id' => $zakazId]);
    }

    /**
     * Отключить услугу (удалить заказ).
     *
     * @param int $zakazId Номер заказа
     * @return array Ответ сервера
     */
    public function deleteOrder(int $zakazId): array
    {
        return $this->sendRequest('order/delete', ['zakaz_id' => $zakazId]);
    }

    /**
     * Перезагрузить виртуальный сервер.
     *
     * @param int $zakazId Номер заказа
     * @return array Ответ сервера
     */
    public function rebootVPS(int $zakazId): array
    {
        return $this->sendRequest('order/vpsreboot', ['zakaz_id' => $zakazId]);
    }

    /**
     * Получить нагрузку на виртуальные серверы.
     *
     * @return array Нагрузка на серверы
     */
    public function getVPSServersLoad(): array
    {
        return $this->sendRequest('order/vpsload')['vpsload'] ?? [];
    }

    // Методы для работы с доменами

    /**
     * Получить список доменов.
     *
     * @param array $filters Фильтры для списка доменов
     * @return array Список доменов
     */
    public function getDomainList(array $filters = []): array
    {
        return $this->sendRequest('domain/list', ['filter' => $filters])['data'] ?? [];
    }

    /**
     * Продлить домен.
     *
     * @param int $domainId Номер домена
     * @param int $period Период продления (1 - год, ... до 10 лет)
     * @return array Ответ сервера
     */
    public function prolongDomain(int $domainId, int $period): array
    {
        return $this->sendRequest('domain/prolong', ['domain_id' => $domainId, 'period' => $period]);
    }

    /**
     * Проверить доступность домена для регистрации.
     *
     * @param string $domain Домен без зоны
     * @param array $zones Зоны для проверки (например: ['.ru' => 1, '.com' => 1])
     * @return array Результат проверки
     */
    public function checkDomainAvailability(string $domain, array $zones): array
    {
        return $this->sendRequest('domain/check', ['domain' => $domain, 'zn' => $zones])['domains'] ?? [];
    }

    /**
     * Зарегистрировать домен.
     *
     * @param string $domain Домен
     * @param int $profileId Номер профиля
     * @return array Ответ сервера
     */
    public function registerDomain(string $domain, int $profileId): array
    {
        return $this->sendRequest('domain/add', ['domain' => $domain, 'profile_id' => $profileId]);
    }

    // Методы для работы с DNS

    /**
     * Получить список DNS-зон для домена.
     *
     * @param int $domainId Номер домена
     * @return array Список DNS-зон
     */
    public function getDNSZoneList(int $domainId): array
    {
        return $this->sendRequest('domain/zonelist', ['filter' => ['domain_id' => $domainId]])['data'] ?? [];
    }

    /**
     * Добавить запись DNS в зону домена.
     *
     * @param int $domainId Номер домена
     * @param string $type Тип записи (A, TXT, MX, CNAME)
     * @param string $subdomain Поддомен
     * @param string $rec Значение записи
     * @param int|null $prior Приоритет (для MX)
     * @return array Ответ сервера
     */
    public function addDNSRecord(int $domainId, string $type, string $subdomain, string $rec, ?int $prior = null): array
    {
        return $this->sendRequest('domain/zoneadd', [
            'domain_id' => $domainId,
            'type' => $type,
            'subdomain' => $subdomain,
            'rec' => $rec,
            'prior' => $prior,
        ]);
    }

    /**
     * Удалить запись DNS из зоны домена.
     *
     * @param int $domainId Номер домена
     * @param int $recId Номер записи
     * @return array Ответ сервера
     */
    public function deleteDNSRecord(int $domainId, int $recId): array
    {
        return $this->sendRequest('domain/zonedelete', ['domain_id' => $domainId, 'rec_id' => $recId]);
    }

    // Методы для работы с тикетами

    /**
     * Получить список тикетов.
     *
     * @param array $filters Фильтры для списка тикетов
     * @return array Список тикетов
     */
    public function getTicketList(array $filters = []): array
    {
        return $this->sendRequest('ticket/list', ['filter' => $filters])['data'] ?? [];
    }

    /**
     * Создать новый тикет или ответить в существующий.
     *
     * @param string $subject Тема сообщения
     * @param string $text Сообщение
     * @param int|null $ticketId Номер тикета (если ответ на существующий)
     * @return array Ответ сервера
     */
    public function createOrUpdateTicket(string $subject, string $text, ?int $ticketId = null): array
    {
        return $this->sendRequest('ticket/add', [
            'subject' => $subject,
            'text' => $text,
            'ticket_id' => $ticketId,
        ]);
    }
}

// Пример использования:
try {
    $api = new AdmanAPI('test@mail.com', 'qwert12345');

    // Получить баланс
    echo "Баланс: " . $api->getBalance() . "\n";

    // Получить список тарифов
    $tariffs = $api->getTariffs(2); // Например, получить тарифы для VPS
    print_r($tariffs);

    // Создать заказ
    $orderId = $api->createOrder(1, 1, ['os' => 1]); // Например, создать заказ на VPS с ОС #1
    echo "Новый заказ создан с ID: $orderId\n";

} catch (InvalidArgumentException $e) {
    echo "Ошибка: " . $e->getMessage();
}
