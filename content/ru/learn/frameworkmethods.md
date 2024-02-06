## Методы фреймворка

Flight разработан для удобства использования и понимания. Ниже приведен полный
набор методов для фреймворка. Он состоит из основных методов, которые являются
обычными статическими методами, и расширяемых методов, которые являются отображенными методами,
которые могут быть отфильтрованы или переопределены.

## Основные Методы

```php
Flight::map(строка $name, callable $callback, bool $pass_route = false) // Создает пользовательский метод фреймворка.
Flight::register(строка $name, строка $class, массив $params = [], ?callable $callback = null) // Регистрирует класс для метода фреймворка.
Flight::before(строка $name, callable $callback) // Добавляет фильтр перед методом фреймворка.
Flight::after(строка $name, callable $callback) // Добавляет фильтр после метода фреймворка.
Flight::path(строка $path) // Добавляет путь для автозагрузки классов.
Flight::get(строка $key) // Получает переменную.
Flight::set(строка $key, смешанный $value) // Устанавливает переменную.
Flight::has(строка $key) // Проверяет, установлена ли переменная.
Flight::clear(массив|строка $key = []) // Очищает переменную.
Flight::init() // Инициализирует фреймворк к его настройкам по умолчанию.
Flight::app() // Получает экземпляр объекта приложения.
```

## Расширяемые Методы

```php
Flight::start() // Запускает фреймворк.
Flight::stop() // Останавливает фреймворк и отправляет ответ.
Flight::halt(int $code = 200, строка $message = '') // Останавливает фреймворк с необязательным кодом состояния и сообщением.
Flight::route(строка $pattern, callable $callback, bool $pass_route = false) // Сопоставляет шаблон URL с обратным вызовом.
Flight::group(строка $pattern, callable $callback) // Создает группировку для URL, шаблон должен быть строкой.
Flight::redirect(строка $url, int $code) // Перенаправляет на другой URL.
Flight::render(строка $file, массив $data, ?строка $key = null) // Рендерит файл шаблона.
Flight::error(Throwable $error) // Отправляет ответ HTTP 500.
Flight::notFound() // Отправляет ответ HTTP 404.
Flight::etag(строка $id, строка $type = 'string') // Выполняет кэширование HTTP ETag.
Flight::lastModified(int $time) // Выполняет кэширование HTTP Last-Modified.
Flight::json(смешанный $data, int $code = 200, bool $encode = true, строка $charset = 'utf8', int $option) // Отправляет ответ JSON.
Flight::jsonp(смешанный $data, строка $param = 'jsonp', int $code = 200, bool $encode = true, строка $charset = 'utf8', int $option) // Отправляет ответ JSONP.
```

Любые пользовательские методы, добавленные с помощью `map` и `register`, также могут быть отфильтрованы.