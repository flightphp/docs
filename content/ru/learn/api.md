# Методы API Framework

Flight разработан для простоты использования и понимания. Ниже приведен полный
набор методов для фреймворка. Он состоит из основных методов, которые являются обычными
статическими методами, и расширяемых методов, которые являются отображенными методами, которые можно фильтровать
или переопределять.

## Основные методы

Эти методы являются основными для фреймворка и не могут быть переопределены.

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // Создает пользовательский метод фреймворка.
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // Регистрирует класс для метода фреймворка.
Flight::unregister(string $name) // Отменяет регистрацию класса для метода фреймворка.
Flight::before(string $name, callable $callback) // Добавляет фильтр перед методом фреймворка.
Flight::after(string $name, callable $callback) // Добавляет фильтр после метода фреймворка.
Flight::path(string $path) // Добавляет путь для автозагрузки классов.
Flight::get(string $key) // Получает переменную.
Flight::set(string $key, mixed $value) // Устанавливает переменную.
Flight::has(string $key) // Проверяет, установлена ли переменная.
Flight::clear(array|string $key = []) // Очищает переменную.
Flight::init() // Инициализирует фреймворк со стандартными настройками.
Flight::app() // Получает экземпляр объекта приложения
Flight::request() // Получает экземпляр объекта запроса
Flight::response() // Получает экземпляр объекта ответа
Flight::router() // Получает экземпляр объекта роутера
Flight::view() // Получает экземпляр объекта представления
```

## Расширяемые методы

```php
Flight::start() // Запускает фреймворк.
Flight::stop() // Останавливает фреймворк и отправляет ответ.
Flight::halt(int $code = 200, string $message = '') // Останавливает фреймворк с необязательным кодом состояния и сообщением.
Flight::route(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Сопоставляет шаблон URL с обратным вызовом.
Flight::post(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Сопоставляет шаблон URL POST-запроса с обратным вызовом.
Flight::put(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Сопоставляет шаблон URL PUT-запроса с обратным вызовом.
Flight::patch(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Сопоставляет шаблон URL PATCH-запроса с обратным вызовом.
Flight::delete(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Сопоставляет шаблон URL DELETE-запроса с обратным вызовом.
Flight::group(string $pattern, callable $callback) // Создает группировку для URL, шаблон должен быть строкой.
Flight::getUrl(string $name, array $params = []) // Генерирует URL на основе псевдонима маршрута.
Flight::redirect(string $url, int $code) // Перенаправляет на другой URL.
Flight::render(string $file, array $data, ?string $key = null) // Визуализирует файл шаблона.
Flight::error(Throwable $error) // Отправляет HTTP 500 ответ.
Flight::notFound() // Отправляет HTTP 404 ответ.
Flight::etag(string $id, string $type = 'string') // Выполняет кэширование HTTP ETag.
Flight::lastModified(int $time) // Выполняет кэширование последнего изменения HTTP.
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Отправляет JSON-ответ.
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Отправляет JSONP-ответ.
```

Любые пользовательские методы, добавленные с помощью `map` и `register`, также могут быть отфильтрованы.