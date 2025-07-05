# Інтеграція з WordPress: n0nag0n/wordpress-integration-for-flight-framework

Хочете використовувати Flight PHP у своєму сайті WordPress? Цей плагін робить це легким! З `n0nag0n/wordpress-integration-for-flight-framework`, ви можете запускати повноцінний додаток Flight поруч з вашою інсталяцією WordPress — ідеально для створення власних API, мікросервісів або навіть повноцінних додатків, не покидаючи комфорту WordPress.

---

## Що він робить?

- **Безшовно інтегрує Flight PHP з WordPress**
- Маршрутизує запити до Flight або WordPress на основі шаблонів URL
- Організовує ваш код за допомогою контролерів, моделей і видів (MVC)
- Легко налаштовує рекомендовану структуру папок Flight
- Використовує з'єднання бази даних WordPress або ваше власне
- Тонко налаштовує взаємодію між Flight і WordPress
- Проста адміністративна панель для налаштування

## Встановлення

1. Завантажте папку `flight-integration` до вашого каталогу `/wp-content/plugins/`.
2. Активуйте плагін в адмінці WordPress (меню Плагіни).
3. Перейдіть до **Налаштувань > Flight Framework**, щоб налаштувати плагін.
4. Вкажіть шлях до вашої інсталяції Flight (або використовуйте Composer для встановлення Flight).
5. Налаштуйте шлях до папки вашого додатку та створіть структуру папок (плагін може допомогти з цим!).
6. Почніть створювати свій додаток Flight!

## Приклади використання

### Приклад базового маршруту
У вашому файлі `app/config/routes.php`:

```php
Flight::route('GET /api/hello', function() {
    Flight::json(['message' => 'Hello World!']);
});
```

### Приклад контролера

Створіть контролер у `app/controllers/ApiController.php`:

```php
namespace app\controllers;

use Flight;

class ApiController {
    public function getUsers() {
        // Ви можете використовувати функції WordPress у Flight!
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

Тоді, у вашому `routes.php`:

```php
Flight::route('GET /api/users', [app\controllers\ApiController::class, 'getUsers']);
```

## Поширені запитання

**Питання: Чи потрібно мені знати Flight для використання цього плагіна?**  
Відповідь: Так, це для розробників, які хочуть використовувати Flight у WordPress. Рекомендується базове знання маршрутизації та обробки запитів Flight.

**Питання: Чи це сповільнить мій сайт WordPress?**  
Відповідь: Ні! Плагін обробляє лише запити, які відповідають вашим маршрутам Flight. Усі інші запити йдуть до WordPress як звичайно.

**Питання: Чи можу я використовувати функції WordPress у своєму додатку Flight?**  
Відповідь: Звичайно! У вас є повний доступ до всіх функцій, хуків і глобальних змінних WordPress зсередини ваших маршрутів і контролерів Flight.

**Питання: Як створити власні маршрути?**  
Відповідь: Визначте ваші маршрути у файлі `config/routes.php` у папці вашого додатку. Дивіться зразковий файл, створений генератором структури папок, для прикладів.

## Журнал змін

**1.0.0**  
Початкова версія.

---

Для більшої інформації, перегляньте [репозиторій GitHub](https://github.com/n0nag0n/wordpress-integration-for-flight-framework).