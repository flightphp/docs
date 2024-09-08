# Методи API фреймворку

Flight розроблений для того, щоб бути простим у використанні та розумінні. Наступне є повним
набором методів для фреймворку. Він складається з основних методів, які є звичайними
статичними методами, та розширювальних методів, які є картованими методами, що можуть бути відфільтровані
або перевизначені.

## Основні методи

Ці методи є основними для фреймворку і не можуть бути перевизначені.

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // Створює користувацький метод фреймворку.
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // Реєструє клас для методу фреймворку.
Flight::unregister(string $name) // Знедоступлює клас для методу фреймворку.
Flight::before(string $name, callable $callback) // Додає фільтр перед методом фреймворку.
Flight::after(string $name, callable $callback) // Додає фільтр після методу фреймворку.
Flight::path(string $path) // Додає шлях для автоматичного завантаження класів.
Flight::get(string $key) // Отримує змінну, встановлену Flight::set().
Flight::set(string $key, mixed $value) // Встановлює змінну в рамках системи Flight.
Flight::has(string $key) // Перевіряє, чи встановлено змінну.
Flight::clear(array|string $key = []) // Очищає змінну.
Flight::init() // Ініціалізує фреймворк до його стандартних налаштувань.
Flight::app() // Отримує екземпляр об'єкта застосунку
Flight::request() // Отримує екземпляр об'єкта запиту
Flight::response() // Отримує екземпляр об'єкта відповіді
Flight::router() // Отримує екземпляр об'єкта маршрутизатора
Flight::view() // Отримує екземпляр об'єкта представлення
```

## Розширювальні методи

```php
Flight::start() // Запускає фреймворк.
Flight::stop() // Зупиняє фреймворк і надсилає відповідь.
Flight::halt(int $code = 200, string $message = '') // Зупиняє фреймворк з допоміжним кодом статусу та повідомленням.
Flight::route(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Відображає шаблон URL на колбек.
Flight::post(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Відображає шаблон URL запиту POST на колбек.
Flight::put(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Відображає шаблон URL запиту PUT на колбек.
Flight::patch(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Відображає шаблон URL запиту PATCH на колбек.
Flight::delete(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Відображає шаблон URL запиту DELETE на колбек.
Flight::group(string $pattern, callable $callback) // Створює групування для URL, шаблон має бути рядком.
Flight::getUrl(string $name, array $params = []) // Генерує URL на основі псевдоніму маршруту.
Flight::redirect(string $url, int $code) // Перенаправляє на інший URL.
Flight::download(string $filePath) // Завантажує файл.
Flight::render(string $file, array $data, ?string $key = null) // Відображає файл шаблону.
Flight::error(Throwable $error) // Надсилає HTTP-відповідь 500.
Flight::notFound() // Надсилає HTTP-відповідь 404.
Flight::etag(string $id, string $type = 'string') // Виконує кешування HTTP ETag.
Flight::lastModified(int $time) // Виконує кешування HTTP для останнього модифікованого часу.
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Надсилає JSON-відповідь.
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Надсилає JSONP-відповідь.
Flight::jsonHalt(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Надсилає JSON-відповідь і зупиняє фреймворк.
```

Будь-які користувацькі методи, додані за допомогою `map` та `register`, також можуть бути відфільтровані. Для прикладів, як картувати ці методи, дивіться посібник [Розширення Flight](/learn/extending).