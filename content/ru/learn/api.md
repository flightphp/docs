# Методы API Фреймворка

Flight разработан таким образом, чтобы быть легким в использовании и понимании. Ниже приведен полный
набор методов фреймворка. Он состоит из основных методов, которые являются обычными
статическими методами, и расширяемых методов, которые являются сопоставленными методами, которые можно фильтровать
или переопределять.

## Основные Методы

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // Создает пользовательский метод фреймворка.
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // Регистрирует класс в методе фреймворка.
Flight::unregister(string $name) // Отменяет регистрацию класса в методе фреймворка.
Flight::before(string $name, callable $callback) // Добавляет фильтр перед методом фреймворка.
Flight::after(string $name, callable $callback) // Добавляет фильтр после метода фреймворка.
Flight::path(string $path) // Добавляет путь для автозагрузки классов.
Flight::get(string $key) // Получает переменную.
Flight::set(string $key, mixed $value) // Устанавливает переменную.
Flight::has(string $key) // Проверяет, установлена ли переменная.
Flight::clear(array|string $key = []) // Очищает переменную.
Flight::init() // Инициализирует фреймворк по умолчанию.
Flight::app() // Получает экземпляр объекта приложения.
Flight::request() // Получает экземпляр объекта запроса.
Flight::response() // Получает экземпляр объекта ответа.
Flight::router() // Получает экземпляр объекта маршрутизатора.
Flight::view() // Получает экземпляр объекта представления.
```

## Расширяемые Методы

```php
Flight::start() // Запускает фреймворк.
Flight::stop() // Останавливает фреймворк и отправляет ответ.
Flight::halt(int $code = 200, string $message = '') // Останавливает фреймворк с необязательным кодом состояния и сообщением.
Flight::route(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Сопоставляет шаблон URL с обратным вызовом.
Flight::post(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Сопоставляет шаблон URL POST-запроса с обратным вызовом.
Flight::put(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Сопоставляет шаблон URL PUT-запроса с обратным вызовом.
Flight::patch(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Сопоставляет шаблон URL PATCH-запроса с обратным вызовом.
Flight::delete(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Сопоставляет шаблон URL DELETE-запроса с обратным вызовом.
Flight::group(string $pattern, callable $callback) // Создает группировку для URL-адресов, шаблон должен быть строкой.
Flight::getUrl(string $name, array $params = []) // Генерирует URL на основе псевдонима маршрута.
Flight::redirect(string $url, int $code) // Перенаправляет на другой URL.
Flight::render(string $file, array $data, ?string $key = null) // Отображает файл шаблона.
Flight::error(Throwable $error) // Отправляет HTTP-ответ 500.
Flight::notFound() // Отправляет HTTP-ответ 404.
Flight::etag(string $id, string $type = 'string') // Выполняет кэширование HTTP ETag.
Flight::lastModified(int $time) // Выполняет кэширование последних изменений HTTP.
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Отправляет ответ JSON.
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Отправляет ответ JSONP.
```

Любые пользовательские методы, добавленные с помощью `map` и `register`, также могут быть отфильтрованы.