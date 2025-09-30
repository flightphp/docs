# Расширение

## Обзор

Flight разработан как расширяемая платформа. Фреймворк поставляется с набором
стандартных методов и компонентов, но позволяет вам отображать свои собственные методы,
регистрировать свои собственные классы или даже переопределять существующие классы и методы.

## Понимание

Есть 2 способа расширить функциональность Flight:

1. Отображение методов — это используется для создания простых пользовательских методов, которые вы можете вызывать
   из любого места в вашем приложении. Они обычно используются для утилитарных функций,
   которые вы хотите вызывать из любого места в вашем коде.
2. Регистрация классов — это используется для регистрации ваших собственных классов в Flight. Это
   обычно используется для классов, которые имеют зависимости или требуют конфигурации.

Вы также можете переопределять существующие методы фреймворка, чтобы изменить их поведение по умолчанию, чтобы лучше
соответствовать потребностям вашего проекта.

> Если вы ищете DIC (Dependency Injection Container), перейдите на страницу
[Dependency Injection Container](/learn/dependency-injection-container).

## Основное использование

### Переопределение методов фреймворка

Flight позволяет вам переопределять его стандартную функциональность в соответствии с вашими потребностями,
без необходимости изменять какой-либо код. Вы можете просмотреть все методы, которые можно переопределить, [ниже](#mappable-framework-methods).

Например, когда Flight не может сопоставить URL с маршрутом, он вызывает метод `notFound`,
который отправляет общий ответ `HTTP 404`. Вы можете переопределить это поведение,
используя метод `map`:

```php
Flight::map('notFound', function() {
  // Отображение пользовательской страницы 404
  include 'errors/404.html';
});
```

Flight также позволяет заменить основные компоненты фреймворка.
Например, вы можете заменить стандартный класс Router на свой собственный пользовательский класс:

```php
// создание вашего пользовательского класса Router
class MyRouter extends \flight\net\Router {
	// переопределение методов здесь
	// например, сокращение для GET-запросов, чтобы удалить
	// функцию передачи маршрута
	public function get($pattern, $callback, $alias = '') {
		return parent::get($pattern, $callback, false, $alias);
	}
}

// Регистрация вашего пользовательского класса
Flight::register('router', MyRouter::class);

// Когда Flight загружает экземпляр Router, он загрузит ваш класс
$myRouter = Flight::router();
$myRouter->get('/hello', function() {
  echo "Hello World!";
}, 'hello_alias');
```

Однако методы фреймворка, такие как `map` и `register`, нельзя переопределять. Вы получите
ошибку, если попытаетесь это сделать (см. [ниже](#mappable-framework-methods) для списка методов).

### Отображаемые методы фреймворка

Ниже приведен полный набор методов для фреймворка. Он состоит из основных методов,
которые являются обычными статическими методами, и расширяемых методов, которые являются отображенными методами, которые можно
фильтровать или переопределять.

#### Основные методы

Эти методы являются основными для фреймворка и не могут быть переопределены.

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // Создает пользовательский метод фреймворка.
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // Регистрирует класс для метода фреймворка.
Flight::unregister(string $name) // Отменяет регистрацию класса для метода фреймворка.
Flight::before(string $name, callable $callback) // Добавляет фильтр перед методом фреймворка.
Flight::after(string $name, callable $callback) // Добавляет фильтр после метода фреймворка.
Flight::path(string $path) // Добавляет путь для автозагрузки классов.
Flight::get(string $key) // Получает переменную, установленную Flight::set().
Flight::set(string $key, mixed $value) // Устанавливает переменную внутри движка Flight.
Flight::has(string $key) // Проверяет, установлена ли переменная.
Flight::clear(array|string $key = []) // Очищает переменную.
Flight::init() // Инициализирует фреймворк с настройками по умолчанию.
Flight::app() // Получает экземпляр объекта приложения
Flight::request() // Получает экземпляр объекта запроса
Flight::response() // Получает экземпляр объекта ответа
Flight::router() // Получает экземпляр объекта маршрутизатора
Flight::view() // Получает экземпляр объекта представления
```

#### Расширяемые методы

```php
Flight::start() // Запускает фреймворк.
Flight::stop() // Останавливает фреймворк и отправляет ответ.
Flight::halt(int $code = 200, string $message = '') // Останавливает фреймворк с опциональным кодом статуса и сообщением.
Flight::route(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Отображает шаблон URL на callback.
Flight::post(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Отображает шаблон URL POST-запроса на callback.
Flight::put(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Отображает шаблон URL PUT-запроса на callback.
Flight::patch(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Отображает шаблон URL PATCH-запроса на callback.
Flight::delete(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Отображает шаблон URL DELETE-запроса на callback.
Flight::group(string $pattern, callable $callback) // Создает группировку для URL, шаблон должен быть строкой.
Flight::getUrl(string $name, array $params = []) // Генерирует URL на основе псевдонима маршрута.
Flight::redirect(string $url, int $code) // Перенаправляет на другой URL.
Flight::download(string $filePath) // Скачивает файл.
Flight::render(string $file, array $data, ?string $key = null) // Рендерит файл шаблона.
Flight::error(Throwable $error) // Отправляет ответ HTTP 500.
Flight::notFound() // Отправляет ответ HTTP 404.
Flight::etag(string $id, string $type = 'string') // Выполняет кэширование HTTP ETag.
Flight::lastModified(int $time) // Выполняет кэширование HTTP последнего изменения.
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Отправляет JSON-ответ.
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Отправляет JSONP-ответ.
Flight::jsonHalt(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Отправляет JSON-ответ и останавливает фреймворк.
Flight::onEvent(string $event, callable $callback) // Регистрирует слушатель события.
Flight::triggerEvent(string $event, ...$args) // Запускает событие.
```

Любые пользовательские методы, добавленные с помощью `map` и `register`, также могут быть отфильтрованы. Для примеров того, как фильтровать эти методы, см. руководство [Filtering Methods](/learn/filtering).

#### Расширяемые классы фреймворка

Есть несколько классов, функциональность которых вы можете переопределить, расширив их и
регистрируя свой собственный класс. Эти классы:

```php
Flight::app() // Класс приложения — расширьте класс flight\Engine
Flight::request() // Класс запроса — расширьте класс flight\net\Request
Flight::response() // Класс ответа — расширьте класс flight\net\Response
Flight::router() // Класс маршрутизатора — расширьте класс flight\net\Router
Flight::view() // Класс представления — расширьте класс flight\template\View
Flight::eventDispatcher() // Класс диспетчера событий — расширьте класс flight\core\Dispatcher
```

### Отображение пользовательских методов

Чтобы отобразить свой собственный простой пользовательский метод, вы используете функцию `map`:

```php
// Отображение вашего метода
Flight::map('hello', function (string $name) {
  echo "hello $name!";
});

// Вызов вашего пользовательского метода
Flight::hello('Bob');
```

Хотя возможно создавать простые пользовательские методы, рекомендуется просто создавать
стандартные функции в PHP. Это обеспечивает автодополнение в IDE и легче читается.
Эквивалент приведенного выше кода будет:

```php
function hello(string $name) {
  echo "hello $name!";
}

hello('Bob');
```

Это используется чаще, когда вам нужно передавать переменные в ваш метод, чтобы получить ожидаемое
значение. Использование метода `register()`, как ниже, больше подходит для передачи конфигурации,
а затем вызова вашего предварительно настроенного класса.

### Регистрация пользовательских классов

Чтобы зарегистрировать свой собственный класс и настроить его, вы используете функцию `register`. Преимущество этого над map() заключается в том, что вы можете повторно использовать тот же класс при вызове этой функции (это будет полезно с `Flight::db()`, чтобы делить один и тот же экземпляр).

```php
// Регистрация вашего класса
Flight::register('user', User::class);

// Получение экземпляра вашего класса
$user = Flight::user();
```

Метод register также позволяет передавать параметры конструктору вашего класса.
Таким образом, когда вы загружаете свой пользовательский класс, он будет предварительно инициализирован.
Вы можете определить параметры конструктора, передав дополнительный массив.
Вот пример загрузки соединения с базой данных:

```php
// Регистрация класса с параметрами конструктора
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// Получение экземпляра вашего класса
// Это создаст объект с заданными параметрами
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// и если вам понадобится это позже в вашем коде, вы просто вызываете тот же метод снова
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

Если вы передадите дополнительный параметр callback, он будет выполнен сразу
после создания класса. Это позволяет выполнить любые процедуры настройки для вашего
нового объекта. Функция callback принимает один параметр — экземпляр нового объекта.

```php
// Callback будет передан объект, который был создан
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

По умолчанию каждый раз, когда вы загружаете свой класс, вы получите общий экземпляр.
Чтобы получить новый экземпляр класса, просто передайте `false` в качестве параметра:

```php
// Общий экземпляр класса
$shared = Flight::db();

// Новый экземпляр класса
$new = Flight::db(false);
```

> **Примечание:** Помните, что отображенные методы имеют приоритет над зарегистрированными классами. Если вы
объявите оба с одним и тем же именем, будет вызван только отображенный метод.

### Примеры

Вот несколько примеров того, как вы можете расширить Flight функциональностью, которая не встроена в ядро.

#### Логирование

Flight не имеет встроенной системы логирования, однако очень легко
использовать библиотеку логирования с Flight. Вот пример с использованием
библиотеки Monolog:

```php
// services.php

// Регистрация логгера с Flight
Flight::register('log', Monolog\Logger::class, [ 'name' ], function(Monolog\Logger $log) {
    $log->pushHandler(new Monolog\Handler\StreamHandler('path/to/your.log', Monolog\Logger::WARNING));
});
```

Теперь, когда он зарегистрирован, вы можете использовать его в своем приложении:

```php
// В вашем контроллере или маршруте
Flight::log()->warning('This is a warning message');
```

Это запишет сообщение в указанный вами файл лога. Что, если вы хотите записать что-то, когда происходит
ошибка? Вы можете использовать метод `error`:

```php
// В вашем контроллере или маршруте
Flight::map('error', function(Throwable $ex) {
	Flight::log()->error($ex->getMessage());
	// Отображение вашей пользовательской страницы ошибки
	include 'errors/500.html';
});
```

Вы также можете создать базовую систему APM (Application Performance Monitoring),
используя методы `before` и `after`:

```php
// В вашем файле services.php

Flight::before('start', function() {
	Flight::set('start_time', microtime(true));
});

Flight::after('start', function() {
	$end = microtime(true);
	$start = Flight::get('start_time');
	Flight::log()->info('Request '.Flight::request()->url.' took ' . round($end - $start, 4) . ' seconds');

	// Вы также можете добавить заголовки запроса или ответа
	// для их логирования (будьте осторожны, поскольку это будет много 
	// данных, если у вас много запросов)
	Flight::log()->info('Request Headers: ' . json_encode(Flight::request()->headers));
	Flight::log()->info('Response Headers: ' . json_encode(Flight::response()->headers));
});
```

#### Кэширование

Flight не имеет встроенной системы кэширования, однако очень легко
использовать библиотеку кэширования с Flight. Вот пример с использованием
библиотеки [PHP File Cache](/awesome-plugins/php_file_cache):

```php
// services.php

// Регистрация кэша с Flight
Flight::register('cache', \flight\Cache::class, [ __DIR__ . '/../cache/' ], function(\flight\Cache $cache) {
    $cache->setDevMode(ENVIRONMENT === 'development');
});
```

Теперь, когда он зарегистрирован, вы можете использовать его в своем приложении:

```php
// В вашем контроллере или маршруте
$data = Flight::cache()->get('my_cache_key');
if (empty($data)) {
	// Выполните некоторую обработку, чтобы получить данные
	$data = [ 'some' => 'data' ];
	Flight::cache()->set('my_cache_key', $data, 3600); // кэшировать на 1 час
}
```

#### Легкая инстанциация объектов DIC

Если вы используете DIC (Dependency Injection Container) в своем приложении,
вы можете использовать Flight, чтобы помочь вам инстанцировать ваши объекты. Вот пример с использованием
библиотеки [Dice](https://github.com/level-2/Dice):

```php
// services.php

// создание нового контейнера
$container = new \Dice\Dice;
// не забудьте переприсвоить его самому себе, как ниже!
$container = $container->addRule('PDO', [
	// shared означает, что тот же объект будет возвращен каждый раз
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// теперь мы можем создать отображаемый метод для создания любого объекта. 
Flight::map('make', function($class, $params = []) use ($container) {
	return $container->create($class, $params);
});

// Это регистрирует обработчик контейнера, чтобы Flight знал, как использовать его для контроллеров/промежуточного ПО
Flight::registerContainerHandler(function($class, $params) {
	Flight::make($class, $params);
});


// предположим, у нас есть следующий пример класса, который принимает объект PDO в конструкторе
class EmailCron {
	protected PDO $pdo;

	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
	}

	public function send() {
		// код, который отправляет email
	}
}

// И наконец, вы можете создавать объекты с использованием dependency injection
$emailCron = Flight::make(EmailCron::class);
$emailCron->send();
```

Круто, правда?

## См. также
- [Dependency Injection Container](/learn/dependency-injection-container) — Как использовать DIC с Flight.
- [File Cache](/awesome-plugins/php_file_cache) — Пример использования библиотеки кэширования с Flight.

## Устранение неполадок
- Помните, что отображенные методы имеют приоритет над зарегистрированными классами. Если вы объявите оба с одним и тем же именем, будет вызван только отображенный метод.

## Журнал изменений
- v2.0 — Первое издание.