# Розширення

## Огляд

Flight розроблений як розширюваний фреймворк. Фреймворк постачається з набором
типових методів та компонентів, але дозволяє вам відображати власні методи,
реєструвати власні класи або навіть перевизначати існуючі класи та методи.

## Розуміння

Існує 2 способи, якими ви можете розширити функціональність Flight:

1. Відображення методів - Це використовується для створення простих власних методів, які ви можете викликати
   з будь-якого місця у вашому додатку. Ці методи зазвичай використовуються для утилітарних функцій,
   які ви хочете викликати з будь-якого місця у вашому коді.
2. Реєстрація класів - Це використовується для реєстрації власних класів у Flight. Це
   зазвичай використовується для класів, які мають залежності або потребують конфігурації.

Ви також можете перевизначати існуючі методи фреймворку, щоб змінити їхню типову поведінку, щоб краще
відповідати потребам вашого проекту.

> Якщо ви шукаєте DIC (Dependency Injection Container), перейдіть до сторінки
[Dependency Injection Container](/learn/dependency-injection-container).

## Основне використання

### Перевизначення методів фреймворку

Flight дозволяє вам перевизначати свою типову функціональність, щоб відповідати вашим потребам,
без необхідності змінювати будь-який код. Ви можете переглянути всі методи, які можна перевизначити [нижче](#mappable-framework-methods).

Наприклад, коли Flight не може співставити URL з маршрутом, він викликає метод `notFound`,
який надсилає загальну відповідь `HTTP 404`. Ви можете перевизначити цю поведінку,
використовуючи метод `map`:

```php
Flight::map('notFound', function() {
  // Відображення власної сторінки 404
  include 'errors/404.html';
});
```

Flight також дозволяє вам замінити основні компоненти фреймворку.
Наприклад, ви можете замінити типовий клас Router на власний власний клас:

```php
// create your custom Router class
class MyRouter extends \flight\net\Router {
	// override methods here
	// for example a shortcut for GET requests to remove
	// the pass route feature
	public function get($pattern, $callback, $alias = '') {
		return parent::get($pattern, $callback, false, $alias);
	}
}

// Register your custom class
Flight::register('router', MyRouter::class);

// When Flight loads the Router instance, it will load your class
$myRouter = Flight::router();
$myRouter->get('/hello', function() {
  echo "Hello World!";
}, 'hello_alias');
```

Методи фреймворку, такі як `map` та `register`, однак не можуть бути перевизначені. Ви отримаєте
помилку, якщо спробуєте це зробити (знову ж таки див. [нижче](#mappable-framework-methods) для списку методів).

### Методи фреймворку, що можна відображати

Наступне є повним набором методів для фреймворку. Він складається з основних методів,
які є звичайними статичними методами, та розширюваних методів, які є відображеними методами, що можуть
бути відфільтровані або перевизначені.

#### Основні методи

Ці методи є основними для фреймворку і не можуть бути перевизначені.

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // Creates a custom framework method.
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // Registers a class to a framework method.
Flight::unregister(string $name) // Unregisters a class to a framework method.
Flight::before(string $name, callable $callback) // Adds a filter before a framework method.
Flight::after(string $name, callable $callback) // Adds a filter after a framework method.
Flight::path(string $path) // Adds a path for autoloading classes.
Flight::get(string $key) // Gets a variable set by Flight::set().
Flight::set(string $key, mixed $value) // Sets a variable within the Flight engine.
Flight::has(string $key) // Checks if a variable is set.
Flight::clear(array|string $key = []) // Clears a variable.
Flight::init() // Initializes the framework to its default settings.
Flight::app() // Gets the application object instance
Flight::request() // Gets the request object instance
Flight::response() // Gets the response object instance
Flight::router() // Gets the router object instance
Flight::view() // Gets the view object instance
```

#### Розширювані методи

```php
Flight::start() // Starts the framework.
Flight::stop() // Stops the framework and sends a response.
Flight::halt(int $code = 200, string $message = '') // Stop the framework with an optional status code and message.
Flight::route(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Maps a URL pattern to a callback.
Flight::post(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Maps a POST request URL pattern to a callback.
Flight::put(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Maps a PUT request URL pattern to a callback.
Flight::patch(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Maps a PATCH request URL pattern to a callback.
Flight::delete(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Maps a DELETE request URL pattern to a callback.
Flight::group(string $pattern, callable $callback) // Creates grouping for urls, pattern must be a string.
Flight::getUrl(string $name, array $params = []) // Generates a URL based on a route alias.
Flight::redirect(string $url, int $code) // Redirects to another URL.
Flight::download(string $filePath) // Downloads a file.
Flight::render(string $file, array $data, ?string $key = null) // Renders a template file.
Flight::error(Throwable $error) // Sends an HTTP 500 response.
Flight::notFound() // Sends an HTTP 404 response.
Flight::etag(string $id, string $type = 'string') // Performs ETag HTTP caching.
Flight::lastModified(int $time) // Performs last modified HTTP caching.
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Sends a JSON response.
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Sends a JSONP response.
Flight::jsonHalt(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Sends a JSON response and stops the framework.
Flight::onEvent(string $event, callable $callback) // Registers an event listener.
Flight::triggerEvent(string $event, ...$args) // Triggers an event.
```

Будь-які власні методи, додані з `map` та `register`, також можуть бути відфільтровані. Для прикладів, як фільтрувати ці методи, див. посібник [Filtering Methods](/learn/filtering).

#### Розширювані класи фреймворку

Існує кілька класів, функціональність яких ви можете перевизначити, розширюючи їх і
реєструючи власний клас. Ці класи є:

```php
Flight::app() // Application class - extend the flight\Engine class
Flight::request() // Request class - extend the flight\net\Request class
Flight::response() // Response class - extend the flight\net\Response class
Flight::router() // Router class - extend the flight\net\Router class
Flight::view() // View class - extend the flight\template\View class
Flight::eventDispatcher() // Event Dispatcher class - extend the flight\core\Dispatcher class
```

### Відображення власних методів

Щоб відобразити власний простий власний метод, ви використовуєте функцію `map`:

```php
// Map your method
Flight::map('hello', function (string $name) {
  echo "hello $name!";
});

// Call your custom method
Flight::hello('Bob');
```

Хоча можливо створювати прості власні методи, рекомендується просто створювати
стандартні функції в PHP. Це має автодоповнення в IDE та легше читати.
Еквівалент наведеного вище коду буде:

```php
function hello(string $name) {
  echo "hello $name!";
}

hello('Bob');
```

Це використовується більше, коли вам потрібно передавати змінні у ваш метод, щоб отримати очікуване
значення. Використання методу `register()` нижче більше для передачі конфігурації,
а потім виклику вашого попередньо налаштованого класу.

### Реєстрація власних класів

Щоб зареєструвати власний клас і налаштувати його, ви використовуєте функцію `register`. Перевага цього над map() полягає в тому, що ви можете повторно використовувати той самий клас, коли викликаєте цю функцію (було б корисно з `Flight::db()`, щоб ділитися тим самим екземпляром).

```php
// Register your class
Flight::register('user', User::class);

// Get an instance of your class
$user = Flight::user();
```

Метод register також дозволяє вам передавати параметри конструктору вашого класу.
Отже, коли ви завантажуєте ваш власний клас, він буде попередньо ініціалізований.
Ви можете визначити параметри конструктора, передавши додатковий масив.
Ось приклад завантаження з'єднання з базою даних:

```php
// Register class with constructor parameters
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// Get an instance of your class
// This will create an object with the defined parameters
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// and if you needed it later in your code, you just call the same method again
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

Якщо ви передасте додатковий параметр callback, він буде виконаний відразу
після конструкції класу. Це дозволяє вам виконувати будь-які процедури налаштування для вашого
нового об'єкта. Функція callback приймає один параметр, екземпляр нового об'єкта.

```php
// The callback will be passed the object that was constructed
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

За замовчуванням, кожного разу, коли ви завантажуєте ваш клас, ви отримаєте спільний екземпляр.
Щоб отримати новий екземпляр класу, просто передайте `false` як параметр:

```php
// Shared instance of the class
$shared = Flight::db();

// New instance of the class
$new = Flight::db(false);
```

> **Note:** Keep in mind that mapped methods have precedence over registered classes. If you
declare both using the same name, only the mapped method will be invoked.

### Приклади

Ось деякі приклади того, як ви можете розширити Flight функціональністю, яка не вбудована в ядро.

#### Логування

Flight не має вбудованої системи логування, однак, дуже легко
використовувати бібліотеку логування з Flight. Ось приклад використання бібліотеки
Monolog:

```php
// services.php

// Register the logger with Flight
Flight::register('log', Monolog\Logger::class, [ 'name' ], function(Monolog\Logger $log) {
    $log->pushHandler(new Monolog\Handler\StreamHandler('path/to/your.log', Monolog\Logger::WARNING));
});
```

Тепер, коли це зареєстровано, ви можете використовувати його у вашому додатку:

```php
// In your controller or route
Flight::log()->warning('This is a warning message');
```

Це запише повідомлення у файл логу, який ви вказали. А що, якщо ви хочете записати щось, коли виникає
помилка? Ви можете використовувати метод `error`:

```php
// In your controller or route
Flight::map('error', function(Throwable $ex) {
	Flight::log()->error($ex->getMessage());
	// Display your custom error page
	include 'errors/500.html';
});
```

Ви також можете створити базову систему APM (Application Performance Monitoring),
використовуючи методи `before` та `after`:

```php
// In your services.php file

Flight::before('start', function() {
	Flight::set('start_time', microtime(true));
});

Flight::after('start', function() {
	$end = microtime(true);
	$start = Flight::get('start_time');
	Flight::log()->info('Request '.Flight::request()->url.' took ' . round($end - $start, 4) . ' seconds');

	// You could also add your request or response headers
	// to log them as well (be careful as this would be a 
	// lot of data if you have a lot of requests)
	Flight::log()->info('Request Headers: ' . json_encode(Flight::request()->headers));
	Flight::log()->info('Response Headers: ' . json_encode(Flight::response()->headers));
});
```

#### Кешування

Flight не має вбудованої системи кешування, однак, дуже легко
використовувати бібліотеку кешування з Flight. Ось приклад використання
бібліотеки [PHP File Cache](/awesome-plugins/php_file_cache):

```php
// services.php

// Register the cache with Flight
Flight::register('cache', \flight\Cache::class, [ __DIR__ . '/../cache/' ], function(\flight\Cache $cache) {
    $cache->setDevMode(ENVIRONMENT === 'development');
});
```

Тепер, коли це зареєстровано, ви можете використовувати його у вашому додатку:

```php
// In your controller or route
$data = Flight::cache()->get('my_cache_key');
if (empty($data)) {
	// Do some processing to get the data
	$data = [ 'some' => 'data' ];
	Flight::cache()->set('my_cache_key', $data, 3600); // cache for 1 hour
}
```

#### Легке створення об'єктів DIC

Якщо ви використовуєте DIC (Dependency Injection Container) у вашому додатку,
ви можете використовувати Flight, щоб допомогти вам створювати ваші об'єкти. Ось приклад використання
бібліотеки [Dice](https://github.com/level-2/Dice):

```php
// services.php

// create a new container
$container = new \Dice\Dice;
// don't forget to reassign it to itself like below!
$container = $container->addRule('PDO', [
	// shared means that the same object will be returned each time
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// now we can create a mappable method to create any object. 
Flight::map('make', function($class, $params = []) use ($container) {
	return $container->create($class, $params);
});

// This registers the container handler so Flight knows to use it for controllers/middleware
Flight::registerContainerHandler(function($class, $params) {
	Flight::make($class, $params);
});


// lets say we have the following sample class that takes a PDO object in the constructor
class EmailCron {
	protected PDO $pdo;

	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
	}

	public function send() {
		// code that sends an email
	}
}

// And finally you can create objects using dependency injection
$emailCron = Flight::make(EmailCron::class);
$emailCron->send();
```

Snazzy right?

## Див. також
- [Dependency Injection Container](/learn/dependency-injection-container) - Як використовувати DIC з Flight.
- [File Cache](/awesome-plugins/php_file_cache) - Приклад використання бібліотеки кешування з Flight.

## Вирішення проблем
- Пам'ятайте, що відображені методи мають пріоритет над зареєстрованими класами. Якщо ви оголосите обидва з однаковим іменем, буде викликано лише відображений метод.

## Журнал змін
- v2.0 - Початковий реліз.