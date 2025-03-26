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

    // Существующие методы...

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
     * Перезагрузить сервер.
     *
     * @param int $zakazId Номер заказа
     * @return array Ответ сервера
     */
    public function rebootServer(int $zakazId): array
    {
        return $this->sendRequest('order/reboot', ['zakaz_id' => $zakazId]);
    }

    /**
     * Выключить сервер.
     *
     * @param int $zakazId Номер заказа
     * @return array Ответ сервера
     */
    public function shutdownServer(int $zakazId): array
    {
        return $this->sendRequest('order/shutdown', ['zakaz_id' => $zakazId]);
    }

    /**
     * Включить сервер.
     *
     * @param int $zakazId Номер заказа
     * @return array Ответ сервера
     */
    public function startServer(int $zakazId): array
    {
        return $this->sendRequest('order/start', ['zakaz_id' => $zakazId]);
    }

    /**
     * Изменить операционную систему на сервере.
     *
     * @param int $zakazId Номер заказа
     * @param int $osId ID новой операционной системы
     * @return array Ответ сервера
     */
    public function changeOperatingSystem(int $zakazId, int $osId): array
    {
        return $this->sendRequest('order/os_change', ['zakaz_id' => $zakazId, 'os_id' => $osId]);
    }

    /**
     * Получить список доступных действий для заказа.
     *
     * @param int $zakazId Номер заказа
     * @return array Список действий
     */
    public function getOrderActions(int $zakazId): array
    {
        return $this->sendRequest('order/actions', ['zakaz_id' => $zakazId]);
    }

    /**
     * Получить статусы заказов.
     *
     * @return array Список статусов
     */
    public function getOrderStatuses(): array
    {
        return $this->sendRequest('order/statuses');
    }

    /**
     * Получить историю платежей.
     *
     * @param array $filters Фильтры для списка платежей
     * @return array Список платежей
     */
    public function getPaymentHistory(array $filters = []): array
    {
        return $this->sendRequest('account/payments', ['filter' => $filters]);
    }

    /**
     * Получить информацию об аккаунте.
     *
     * @return array Информация об аккаунте
     */
    public function getAccountInfo(): array
    {
        return $this->sendRequest('account/info');
    }
}

// Пример использования:
try {
    $api = new AdmanAPI('test@mail.com', 'qwert12345');

    // Получить информацию о заказе
    $orderInfo = $api->getOrderInfo(12345);
    print_r($orderInfo);

    // Перезагрузить сервер
    $rebootResult = $api->rebootServer(12345);
    print_r($rebootResult);

    // Получить историю платежей
    $payments = $api->getPaymentHistory();
    print_r($payments);

    // Получить информацию об аккаунте
    $accountInfo = $api->getAccountInfo();
    print_r($accountInfo);

} catch (InvalidArgumentException $e) {
    echo "Ошибка: " . $e->getMessage();
}
