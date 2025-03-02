# Методы API фреймворка

Flight разработан для простоты использования и понимания. Ниже приведен полный набор методов для фреймворка. Он состоит из базовых методов, которые являются обычными статическими методами, и расширяемых методов, которые являются отображенными методами, которые могут быть отфильтрованы или переопределены.

## Базовые методы

Эти методы являются базовыми для фреймворка и не могут быть переопределены.

```php
Flight::map(строка $name, callable $callback, bool $pass_route = false) // Создает пользовательский метод фреймворка.
Flight::register(строка $name, строка $class, массив $params = [], ?callable $callback = null) // Регистрирует класс в метод фреймворка.
Flight::unregister(строка $name) // Отменяет регистрацию класса в методе фреймворка.
Flight::before(строка $name, callable $callback) // Добавляет фильтр до метода фреймворка.
Flight::after(строка $name, callable $callback) // Добавляет фильтр после метода фреймворка.
Flight::path(строка $path) // Добавляет путь для автозагрузки классов.
Flight::get(строка $key) // Получает переменную, установленную через Flight::set().
Flight::set(строка $key, смешанный $value) // Устанавливает переменную внутри движка Flight.
Flight::has(строка $key) // Проверяет, установлена ли переменная.
Flight::clear(массив|строка $key = []) // Очищает переменную.
Flight::init() // Инициализирует фреймворк к его настройкам по умолчанию.
Flight::app() // Получает экземпляр объекта приложения
Flight::request() // Получает экземпляр объекта запроса
Flight::response() // Получает экземпляр объекта ответа
Flight::router() // Получает экземпляр объекта маршрутизатора
Flight::view() // Получает экземпляр объекта представления
```

## Расширяемые методы

```php
Flight::start() // Запускает фреймворк.
Flight::stop() // Останавливает фреймворк и отправляет ответ.
Flight::halt(int $code = 200, строка $message = '') // Останавливает фреймворк с необязательным кодом состояния и сообщением.
Flight::route(строка $pattern, callable $callback, bool $pass_route = false, строка $alias = '') // Сопоставляет шаблон URL с обратным вызовом.
Flight::post(строка $pattern, callable $callback, bool $pass_route = false, строка $alias = '') // Сопоставляет шаблон URL для POST-запроса с обратным вызовом.
Flight::put(строка $pattern, callable $callback, bool $pass_route = false, строка $alias = '') // Сопоставляет шаблон URL для PUT-запроса с обратным вызовом.
Flight::patch(строка $pattern, callable $callback, bool $pass_route = false, строка $alias = '') // Сопоставляет шаблон URL для запроса PATCH с обратным вызовом.
Flight::delete(строка $pattern, callable $callback, bool $pass_route = false, строка $alias = '') // Сопоставляет шаблон URL для запроса DELETE с обратным вызовом.
Flight::group(строка $pattern, callable $callback) // Создает группировку для URL, шаблон должен быть строкой.
Flight::getUrl(строка $name, массив $params = []) // Генерирует URL на основе псевдонима маршрута.
Flight::redirect(строка $url, int $code) // Перенаправляет на другой URL.
Flight::download(строка $filePath) // Загружает файл.
Flight::render(строка $file, массив $data, ?строка $key = null) // Рендерит файл шаблона.
Flight::error(Throwable $error) // Отправляет ответ HTTP 500.
Flight::notFound() // Отправляет ответ HTTP 404.
Flight::etag(строка $id, строка $type = 'string') // Выполняет кэширование HTTP ETag.
Flight::lastModified(int $time) // Выполняет кэширование последней модификации HTTP.
Flight::json(смешанный $data, int $code = 200, bool $encode = true, строка $charset = 'utf8', int $option) // Отправляет ответ JSON.
Flight::jsonp(смешанный $data, строка $param = 'jsonp', int $code = 200, bool $encode = true, строка $charset = 'utf8', int $option) // Отправляет ответ JSONP.
Flight::jsonHalt(смешанный $data, int $code = 200, bool $encode = true, строка $charset = 'utf8', int $option) // Отправляет ответ JSON и останавливает фреймворк.
```

Любые пользовательские методы, добавленные с помощью `map` и `register`, также могут быть отфильтрованы. Примеры того, как сопоставить эти методы, см. в руководстве [Расширение Flight](/learn/extending).