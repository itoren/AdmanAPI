# AdmanAPI
API Class PHP adman.com

Конструктор : Принимает логин и ключ API для аутентификации. <br>
Метод sendRequest : Общий метод для отправки запросов к API. Включает обработку ошибок и проверку статуса ответа.<br>

# Методы API :<br>
# Основные методы:<br>
<b>getBalance:</b> Получение текущего баланса аккаунта.<br>
<b>getTariffs:</b> Получение списка тарифов с возможностью фильтрации по типу услуги или конкретному тарифу.<br>
<b>getOperatingSystems:</b> Получение списка доступных операционных систем для указанного тарифа.<br>
<b>createOrder:</b> Создание нового заказа с указанием тарифа, периода оплаты и дополнительных параметров.<br>
<b>prolongOrder:</b> Продление существующего заказа на указанный период.<br>
<b>getOrderList:</b> Получение списка заказов с возможностью применения фильтров.<br>
# Методы управления заказами :<br>
<b>getOrderInfo:</b> Получение детальной информации о конкретном заказе.<br>
<b>deleteOrder:</b> Удаление заказа (отключение услуги).<br>
<b>rebootServer, shutdownServer, startServer:</b> Контроль состояния сервера (перезагрузка, выключение, включение).<br>
<b>changeOperatingSystem:</b> Изменение операционной системы на сервере.<br>
# Методы получения информации :<br>
<b>getOrderActions:</b> Список доступных действий для конкретного заказа.<br>
<b>getOrderStatuses:</b> Список возможных статусов заказов.<br>
<b>getPaymentHistory:</b> История платежей по аккаунту.<br>
<b>getAccountInfo:</b> Информация об аккаунте пользователя.<br>

Обработка ошибок : Все методы используют общий метод sendRequest, который проверяет статус ответа и бросает исключение при возникновении ошибки.<br>
PHP класс полностью покрывает все основные методы API Adman, описанные в документации. Вы можете использовать его для управления заказами, получать информацию об аккаунте и выполнять различные действия с серверами.
