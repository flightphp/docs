# Интеграция с WordPress: n0nag0n/wordpress-integration-for-flight-framework

Хотите использовать Flight PHP внутри вашего сайта WordPress? Этот плагин делает это очень простым! С `n0nag0n/wordpress-integration-for-flight-framework` вы можете запустить полноценное приложение Flight прямо рядом с вашей установкой WordPress — идеально для создания пользовательских API, микросервисов или даже полноценных приложений, не выходя из комфорта WordPress.

---

## Что он делает?

- **Безупречно интегрирует Flight PHP с WordPress**
- Направляет запросы либо на Flight, либо на WordPress в зависимости от шаблонов URL
- Организует ваш код с контроллерами, моделями и представлениями (MVC)
- Легко настраивает рекомендуемую структуру папок Flight
- Использует соединение с базой данных WordPress или свою собственную
- Тонко настраивает взаимодействие между Flight и WordPress
- Простой интерфейс администрирования для конфигурации

## Установка

1. Загрузите папку `flight-integration` в ваш каталог `/wp-content/plugins/`.
2. Активируйте плагин в админ-панели WordPress (меню Плагины).
3. Перейдите в **Настройки > Flight Framework**, чтобы настроить плагин.
4. Укажите путь к установке Flight (или используйте Composer для установки Flight).
5. Настройте путь к папке вашего приложения и создайте структуру папок (плагин может помочь с этим!).
6. Начните создавать ваше приложение Flight!

## Примеры использования

### Пример базового маршрута
В вашем файле `app/config/routes.php`:

```php
Flight::route('GET /api/hello', function() {
    Flight::json(['message' => 'Hello World!']);
});
```

### Пример контроллера

Создайте контроллер в `app/controllers/ApiController.php`:

```php
namespace app\controllers;

use Flight;

class ApiController {
    public function getUsers() {
        // Вы можете использовать функции WordPress внутри Flight!
        $users = get_users();
        $result = [];
        foreach($users as $user) {
            $result[] = [
                'id' => $user->ID,
                'name' => $user->display_name,
                'email' => $user->user_email
            ];
        }
        Flight::json($result);
    }
}
```

Затем, в вашем `routes.php`:

```php
Flight::route('GET /api/users', [app\controllers\ApiController::class, 'getUsers']);
```

## ЧАВО

**В: Мне нужно знать Flight, чтобы использовать этот плагин?**  
О: Да, это для разработчиков, которые хотят использовать Flight в WordPress. Рекомендуется базовое знание маршрутизации и обработки запросов Flight.

**В: Это замедлит мой сайт WordPress?**  
О: Нет! Плагин обрабатывает только запросы, которые соответствуют вашим маршрутам Flight. Все остальные запросы идут в WordPress как обычно.

**В: Могу ли я использовать функции WordPress в своем приложении Flight?**  
О: Абсолютно! У вас есть полный доступ ко всем функциям, хукам и глобальным переменным WordPress из ваших маршрутов и контроллеров Flight.

**В: Как создать пользовательские маршруты?**  
О: Определите свои маршруты в файле `config/routes.php` в папке вашего приложения. Посмотрите образец файла, созданный генератором структуры папок, для примеров.

## Журнал изменений

**1.0.0**  
Первоначальный релиз.

---

Для получения дополнительной информации посетите [GitHub repo](https://github.com/n0nag0n/wordpress-integration-for-flight-framework).