# Методи API фреймворку

Flight розроблений для того, щоб бути простим у використанні та розумінні. Нижче наведено повний набір методів для фреймворку. Він складається з основних методів, які є регулярними статичними методами, а також розширювальних методів, які є відображеними методами, що можуть бути відфільтровані або перевизначені.

## Основні методи

Ці методи є основними для фреймворку і не можуть бути перевизначені.

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // Створює кастомний метод фреймворку.
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // Реєструє клас для методу фреймворку.
Flight::unregister(string $name) // Скасовує реєстрацію класу для методу фреймворку.
Flight::before(string $name, callable $callback) // Додає фільтр перед методом фреймворку.
Flight::after(string $name, callable $callback) // Додає фільтр після методу фреймворку.
Flight::path(string $path) // Додає шлях для автоматичного завантаження класів.
Flight::get(string $key) // Отримує змінну, встановлену Flight::set().
Flight::set(string $key, mixed $value) // Встановлює змінну в рамках движка Flight.
Flight::has(string $key) // Перевіряє, чи встановлено змінну.
Flight::clear(array|string $key = []) // Очищає змінну.
Flight::init() // Ініціалізує фреймворк з його стандартними налаштуваннями.
Flight::app() // Отримує екземпляр об'єкта програми.
Flight::request() // Отримує екземпляр об'єкта запиту.
Flight::response() // Отримує екземпляр об'єкта відповіді.
Flight::router() // Отримує екземпляр об'єкта маршрутизатора.
Flight::view() // Отримує екземпляр об'єкта перегляду.
```

## Розширювальні методи

```php
Flight::start() // Запускає фреймворк.
Flight::stop() // Зупиняє фреймворк і відправляє відповідь.
Flight::halt(int $code = 200, string $message = '') // Зупиняє фреймворк з необов'язковим кодом статусу та повідомленням.
Flight::route(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Відображає шаблон URL на колбек.
Flight::post(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Відображає шаблон URL запиту POST на колбек.
Flight::put(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Відображає шаблон URL запиту PUT на колбек.
Flight::patch(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Відображає шаблон URL запиту PATCH на колбек.
Flight::delete(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Відображає шаблон URL запиту DELETE на колбек.
Flight::group(string $pattern, callable $callback) // Створює групування для URL, шаблон має бути рядком.
Flight::getUrl(string $name, array $params = []) // Генерує URL на основі псевдоніму маршруту.
Flight::redirect(string $url, int $code) // Перенаправляє на інший URL.
Flight::download(string $filePath) // Завантажує файл.
Flight::render(string $file, array $data, ?string $key = null) // Відображає шаблонний файл.
Flight::error(Throwable $error) // Відправляє відповідь HTTP 500.
Flight::notFound() // Відправляє відповідь HTTP 404.
Flight::etag(string $id, string $type = 'string') // Виконує ETag HTTP кешування.
Flight::lastModified(int $time) // Виконує кешування HTTP останнього модифікованого.
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Відправляє відповідь JSON.
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Відправляє відповідь JSONP.
Flight::jsonHalt(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Відправляє відповідь JSON і зупиняє фреймворк.
Flight::onEvent(string $event, callable $callback) // Реєструє слухача подій.
Flight::triggerEvent(string $event, ...$args) // Запускає подію.
```

Будь-які кастомні методи, додані за допомогою `map` та `register`, також можуть бути відфільтровані. Для прикладів того, як відобразити ці методи, дивіться посібник [Розширення Flight](/learn/extending).