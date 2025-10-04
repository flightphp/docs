# Маршрутизація

## Огляд
Маршрутизація в Flight PHP відображає шаблони URL на функції зворотного виклику або методи класів, що забезпечує швидку та просту обробку запитів. Вона розроблена для мінімального навантаження, зручного використання для початківців та розширюваності без зовнішніх залежностей.

## Розуміння
Маршрутизація є основним механізмом, який з'єднує HTTP-запити з логікою вашого додатка в Flight. Визначаючи маршрути, ви вказуєте, як різні URL запускають конкретний код, чи то через функції, методи класів, чи дії контролера. Система маршрутизації Flight гнучка, підтримує базові шаблони, іменовані параметри, регулярні вирази та розширені функції, такі як ін'єкція залежностей та ресурсна маршрутизація. Цей підхід тримає ваш код організованим та легким у підтримці, залишаючись швидким і простим для початківців та розширюваним для просунутих користувачів.

> **Примітка:** Хочете дізнатися більше про маршрутизацію? Перегляньте сторінку ["why a framework?"](/learn/why-frameworks) для детальнішого пояснення.

## Базове використання

### Визначення простого маршруту
Базова маршрутизація в Flight виконується шляхом співставлення шаблону URL з функцією зворотного виклику або масивом класу та методу.

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

> Маршрути співставляються в порядку їх визначення. Перший маршрут, що співпадає з запитом, буде викликаний.

### Використання функцій як зворотних викликів
Зворотний виклик може бути будь-яким об'єктом, який є викличним. Отже, ви можете використовувати звичайну функцію:

```php
function hello() {
    echo 'hello world!';
}

Flight::route('/', 'hello');
```

### Використання класів та методів як контролера
Ви також можете використовувати метод (статичний чи ні) класу:

```php
class GreetingController {
    public function hello() {
        echo 'hello world!';
    }
}

Flight::route('/', [ 'GreetingController','hello' ]);
// or
Flight::route('/', [ GreetingController::class, 'hello' ]); // preferred method
// or
Flight::route('/', [ 'GreetingController::hello' ]);
// or 
Flight::route('/', [ 'GreetingController->hello' ]);
```

Або створюючи об'єкт спочатку, а потім викликаючи метод:

```php
use flight\Engine;

// GreetingController.php
class GreetingController
{
	protected Engine $app
    public function __construct(Engine $app) {
		$this->app = $app;
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "Hello, {$this->name}!";
    }
}

// index.php
$app = Flight::app();
$greeting = new GreetingController($app);

Flight::route('/', [ $greeting, 'hello' ]);
```

> **Примітка:** За замовчуванням, коли контролер викликається в рамках, клас `flight\Engine` завжди інжектується, якщо ви не вказуєте через [контейнер ін'єкції залежностей](/learn/dependency-injection-container)

### Маршрутизація, специфічна для методу

За замовчуванням шаблони маршрутів співставляються з усіма методами запитів. Ви можете реагувати на конкретні методи, розміщуючи ідентифікатор перед URL.

```php
Flight::route('GET /', function () {
  echo 'I received a GET request.';
});

Flight::route('POST /', function () {
  echo 'I received a POST request.';
});

// You cannot use Flight::get() for routes as that is a method 
//    to get variables, not create a route.
Flight::post('/', function() { /* code */ });
Flight::patch('/', function() { /* code */ });
Flight::put('/', function() { /* code */ });
Flight::delete('/', function() { /* code */ });
```

Ви також можете відображати кілька методів на один зворотний виклик, використовуючи роздільник `|`:

```php
Flight::route('GET|POST /', function () {
  echo 'I received either a GET or a POST request.';
});
```

### Спеціальна обробка запитів HEAD та OPTIONS

Flight надає вбудовану обробку для HTTP-запитів `HEAD` та `OPTIONS`:

#### Запити HEAD

- **Запити HEAD** обробляються так само, як запити `GET`, але Flight автоматично видаляє тіло відповіді перед відправкою її клієнту.
- Це означає, що ви можете визначити маршрут для `GET`, і запити HEAD до того самого URL повернуть лише заголовки (без вмісту), як очікується стандартами HTTP.

```php
Flight::route('GET /info', function() {
    echo 'This is some info!';
});
// A HEAD request to /info will return the same headers, but no body.
```

#### Запити OPTIONS

Запити `OPTIONS` автоматично обробляються Flight для будь-якого визначеного маршруту.
- Коли отримано запит OPTIONS, Flight відповідає статусом `204 No Content` та заголовком `Allow`, що перелічує всі підтримувані HTTP-методи для цього маршруту.
- Вам не потрібно визначати окремий маршрут для OPTIONS, якщо ви не хочете кастомну поведінку або модифікувати відповідь.

```php
// For a route defined as:
Flight::route('GET|POST /users', function() { /* ... */ });

// An OPTIONS request to /users will respond with:
//
// Status: 204 No Content
// Allow: GET, POST, HEAD, OPTIONS
```

### Використання об'єкта Router

Крім того, ви можете отримати об'єкт Router, який має деякі допоміжні методи для вашого використання:

```php

$router = Flight::router();

// maps all methods just like Flight::route()
$router->map('/', function() {
	echo 'hello world!';
});

// GET request
$router->get('/users', function() {
	echo 'users';
});
$router->post('/users', 			function() { /* code */});
$router->put('/users/update/@id', 	function() { /* code */});
$router->delete('/users/@id', 		function() { /* code */});
$router->patch('/users/@id', 		function() { /* code */});
```

### Регулярні вирази (Regex)
Ви можете використовувати регулярні вирази у ваших маршрутах:

```php
Flight::route('/user/[0-9]+', function () {
  // This will match /user/1234
});
```

Хоча цей метод доступний, рекомендується використовувати іменовані параметри або іменовані параметри з регулярними виразами, оскільки вони більш читабельні та легші у підтримці.

### Іменовані параметри
Ви можете вказувати іменовані параметри у ваших маршрутах, які будуть передані до вашої функції зворотного виклику. **Це більше для читабельності маршруту, ніж для чогось іншого. Будь ласка, дивіться розділ нижче щодо важливого застереження.**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "hello, $name ($id)!";
});
```

Ви також можете включати регулярні вирази з вашими іменованими параметрами, використовуючи роздільник `:`:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // This will match /bob/123
  // But will not match /bob/12345
});
```

> **Примітка:** Співставлення груп regex `()` з позиційними параметрами не підтримується. Ex: `:'\(`

#### Важливе застереження

Хоча в прикладі вище здається, що `@name` безпосередньо пов'язаний з змінною `$name`, це не так. Порядок параметрів у функції зворотного виклику визначає, що передається до неї. Якщо ви поміняєте порядок параметрів у функції зворотного виклику, змінні також поміняються. Ось приклад:

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "hello, $name ($id)!";
});
```

І якщо ви перейдете за наступним URL: `/bob/123`, вивід буде `hello, 123 (bob)!`. 
_Будьте обережні_ при налаштуванні ваших маршрутів та функцій зворотного виклику!

### Необов'язкові параметри
Ви можете вказувати іменовані параметри, які є необов'язковими для співставлення, обгортаючи сегменти в дужки.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // This will match the following URLS:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

Будь-які необов'язкові параметри, які не співпадають, будуть передані як `NULL`.

### Дикий символ у маршрутизації
Співставлення виконується лише для окремих сегментів URL. Якщо ви хочете співставити кілька сегментів, ви можете використовувати дикий символ `*`.

```php
Flight::route('/blog/*', function () {
  // This will match /blog/2000/02/01
});
```

Щоб маршрутизувати всі запити до одного зворотного виклику, ви можете зробити:

```php
Flight::route('*', function () {
  // Do something
});
```

### Обробник 404 Not Found

За замовчуванням, якщо URL не знайдено, Flight надішле просту та звичайну відповідь `HTTP 404 Not Found`.
Якщо ви хочете мати більш кастомізовану відповідь 404, ви можете [відобразити](/learn/extending) свій власний метод `notFound`:

```php
Flight::map('notFound', function() {
	$url = Flight::request()->url;

	// You could also use Flight::render() with a custom template.
    $output = <<<HTML
		<h1>My Custom 404 Not Found</h1>
		<h3>The page you have requested {$url} could not be found.</h3>
		HTML;

	$this->response()
		->clearBody()
		->status(404)
		->write($output)
		->send();
});
```

### Обробник Method Not Found

За замовчуванням, якщо URL знайдено, але метод не дозволено, Flight надішле просту та звичайну відповідь `HTTP 405 Method Not Allowed` (Ex: Method Not Allowed. Allowed Methods are: GET, POST). Він також включить заголовок `Allow` з дозволеними методами для цього URL.

Якщо ви хочете мати більш кастомізовану відповідь 405, ви можете [відобразити](/learn/extending) свій власний метод `methodNotFound`:

```php
use flight\net\Route;

Flight::map('methodNotFound', function(Route $route) {
	$url = Flight::request()->url;
	$methods = implode(', ', $route->methods);

	// You could also use Flight::render() with a custom template.
	$output = <<<HTML
		<h1>My Custom 405 Method Not Allowed</h1>
		<h3>The method you have requested for {$url} is not allowed.</h3>
		<p>Allowed Methods are: {$methods}</p>
		HTML;

	$this->response()
		->clearBody()
		->status(405)
		->setHeader('Allow', $methods)
		->write($output)
		->send();
});
```

## Розширене використання

### Ін'єкція залежностей у маршрутах
Якщо ви хочете використовувати ін'єкцію залежностей через контейнер (PSR-11, PHP-DI, Dice тощо), єдиний тип маршрутів, де це доступно, — це безпосереднє створення об'єкта самостійно та використання контейнера для створення вашого об'єкта, або ви можете використовувати рядки для визначення класу та методу для виклику. Ви можете перейти на сторінку [Dependency Injection](/learn/dependency-injection-container) для отримання додаткової інформації. 

Ось швидкий приклад:

```php

use flight\database\PdoWrapper;

// Greeting.php
class Greeting
{
	protected PdoWrapper $pdoWrapper;
	public function __construct(PdoWrapper $pdoWrapper) {
		$this->pdoWrapper = $pdoWrapper;
	}

	public function hello(int $id) {
		// do something with $this->pdoWrapper
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "Hello, world! My name is {$name}!";
	}
}

// index.php

// Setup the container with whatever params you need
// See the Dependency Injection page for more information on PSR-11
$dice = new \Dice\Dice();

// Don't forget to reassign the variable with '$dice = '!!!!!
$dice = $dice->addRule('flight\database\PdoWrapper', [
	'shared' => true,
	'constructParams' => [ 
		'mysql:host=localhost;dbname=test', 
		'root',
		'password'
	]
]);

// Register the container handler
Flight::registerContainerHandler(function($class, $params) use ($dice) {
	return $dice->create($class, $params);
});

// Routes like normal
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// or
Flight::route('/hello/@id', 'Greeting->hello');
// or
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

### Передача виконання наступному маршруту
<span class="badge bg-warning">Deprecated</span>
Ви можете передати виконання наступному співставленому маршруту, повертаючи `true` з вашої функції зворотного виклику.

```php
Flight::route('/user/@name', function (string $name) {
  // Check some condition
  if ($name !== "Bob") {
    // Continue to next route
    return true;
  }
});

Flight::route('/user/*', function () {
  // This will get called
});
```

Тепер рекомендується використовувати [middleware](/learn/middleware) для обробки складних випадків, як цей.

### Псевдоніми маршрутів
Призначаючи псевдонім маршруту, ви можете пізніше динамічно викликати цей псевдонім у вашому додатку, щоб згенерувати його пізніше у вашому коді (наприклад: посилання в HTML-шаблоні або генерація URL для перенаправлення).

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
// or 
Flight::route('/users/@id', function($id) { echo 'user:'.$id; })->setAlias('user_view');

// later in code somewhere
class UserController {
	public function update() {

		// code to save user...
		$id = $user['id']; // 5 for example

		$redirectUrl = Flight::getUrl('user_view', [ 'id' => $id ]); // will return '/users/5'
		Flight::redirect($redirectUrl);
	}
}

```

Це особливо корисно, якщо ваш URL змінюється. У прикладі вище, припустимо, що users переміщено до `/admin/users/@id` замість.
З псевдонімами на місці для маршруту, вам більше не потрібно шукати всі старі URL у вашому коді та змінювати їх, оскільки псевдонім тепер поверне `/admin/users/5`, як у прикладі вище.

Псевдоніми маршрутів все ще працюють у групах:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
	// or
	Flight::route('/@id', function($id) { echo 'user:'.$id; })->setAlias('user_view');
});
```

### Перевірка інформації про маршрут
Якщо ви хочете перевірити інформацію про співставлений маршрут, є 2 способи це зробити:

1. Ви можете використовувати властивість `executedRoute` на об'єкті `Flight::router()`.
2. Ви можете запросити об'єкт маршруту для передачі до вашого зворотного виклику, передаючи `true` як третій параметр у методі маршруту. Об'єкт маршруту завжди буде останнім параметром, переданим до вашої функції зворотного виклику.

#### `executedRoute`
```php
Flight::route('/', function() {
  $route = Flight::router()->executedRoute;
  // Do something with $route
  // Array of HTTP methods matched against
  $route->methods;

  // Array of named parameters
  $route->params;

  // Matching regular expression
  $route->regex;

  // Contains the contents of any '*' used in the URL pattern
  $route->splat;

  // Shows the url path....if you really need it
  $route->pattern;

  // Shows what middleware is assigned to this
  $route->middleware;

  // Shows the alias assigned to this route
  $route->alias;
});
```

> **Примітка:** Властивість `executedRoute` буде встановлена лише після виконання маршруту. Якщо ви спробуєте отримати до неї доступ до виконання маршруту, вона буде `NULL`. Ви також можете використовувати executedRoute у [middleware](/learn/middleware)!

#### Передача `true` до визначення маршруту
```php
Flight::route('/', function(\flight\net\Route $route) {
  // Array of HTTP methods matched against
  $route->methods;

  // Array of named parameters
  $route->params;

  // Matching regular expression
  $route->regex;

  // Contains the contents of any '*' used in the URL pattern
  $route->splat;

  // Shows the url path....if you really need it
  $route->pattern;

  // Shows what middleware is assigned to this
  $route->middleware;

  // Shows the alias assigned to this route
  $route->alias;
}, true);// <-- This true parameter is what makes that happen
```

### Групування маршрутів та Middleware
Можуть бути випадки, коли ви хочете групувати пов'язані маршрути разом (наприклад, `/api/v1`).
Ви можете зробити це, використовуючи метод `group`:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Matches /api/v1/users
  });

  Flight::route('/posts', function () {
	// Matches /api/v1/posts
  });
});
```

Ви навіть можете вкладати групи в групи:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() gets variables, it doesn't set a route! See object context below
	Flight::route('GET /users', function () {
	  // Matches GET /api/v1/users
	});

	Flight::post('/posts', function () {
	  // Matches POST /api/v1/posts
	});

	Flight::put('/posts/1', function () {
	  // Matches PUT /api/v1/posts
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() gets variables, it doesn't set a route! See object context below
	Flight::route('GET /users', function () {
	  // Matches GET /api/v2/users
	});
  });
});
```

#### Групування з контекстом об'єкта

Ви все ще можете використовувати групування маршрутів з об'єктом `Engine` наступним чином:

```php
$app = Flight::app();

$app->group('/api/v1', function (Router $router) {

  // user the $router variable
  $router->get('/users', function () {
	// Matches GET /api/v1/users
  });

  $router->post('/posts', function () {
	// Matches POST /api/v1/posts
  });
});
```

> **Примітка:** Це перевага метод визначення маршрутів та груп з об'єктом `$router`.

#### Групування з Middleware

Ви також можете призначати middleware групі маршрутів:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Matches /api/v1/users
  });
}, [ MyAuthMiddleware::class ]); // or [ new MyAuthMiddleware() ] if you want to use an instance
```

Дивіться більше деталей на сторінці [group middleware](/learn/middleware#grouping-middleware).

### Ресурсна маршрутизація
Ви можете створити набір маршрутів для ресурсу, використовуючи метод `resource`. Це створить набір маршрутів для ресурсу, що слідує конвенціям RESTful.

Щоб створити ресурс, зробіть наступне:

```php
Flight::resource('/users', UsersController::class);
```

І те, що станеться в тлі, — це створення наступних маршрутів:

```php
[
      'index' => 'GET /users',
      'create' => 'GET /users/create',
      'store' => 'POST /users',
      'show' => 'GET /users/@id',
      'edit' => 'GET /users/@id/edit',
      'update' => 'PUT /users/@id',
      'destroy' => 'DELETE /users/@id'
]
```

І ваш контролер використовуватиме наступні методи:

```php
class UsersController
{
    public function index(): void
    {
    }

    public function show(string $id): void
    {
    }

    public function create(): void
    {
    }

    public function store(): void
    {
    }

    public function edit(string $id): void
    {
    }

    public function update(string $id): void
    {
    }

    public function destroy(string $id): void
    {
    }
}
```

> **Примітка**: Ви можете переглянути новододаноти маршрути з `runway`, запустивши `php runway routes`.

#### Налаштування ресурсів маршрутів

Є кілька опцій для налаштування ресурсів маршрутів.

##### Базовий псевдонім

Ви можете налаштувати `aliasBase`. За замовчуванням псевдонім — це остання частина вказаного URL.
Наприклад, `/users/` призведе до `aliasBase` як `users`. Коли ці маршрути створюються,
псевдоніми — `users.index`, `users.create` тощо. Якщо ви хочете змінити псевдонім, встановіть `aliasBase`
на значення, яке ви хочете.

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);
```

##### Only та Except

Ви також можете вказати, які маршрути ви хочете створити, використовуючи опції `only` та `except`.

```php
// Whitelist only these methods and blacklist the rest
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);
```

```php
// Blacklist only these methods and whitelist the rest
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);
```

Це по суті опції білої та чорної списків, щоб ви могли вказати, які маршрути ви хочете створити.

##### Middleware

Ви також можете вказати middleware для виконання на кожному з маршрутів, створених методом `resource`.

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);
```

### Потокові відповіді

Ви тепер можете потокувати відповіді клієнту, використовуючи `stream()` або `streamWithHeaders()`. 
Це корисно для відправки великих файлів, довготривалих процесів або генерації великих відповідей. 
Потокування маршруту обробляється трохи інакше, ніж звичайний маршрут.

> **Примітка:** Потокові відповіді доступні лише якщо у вас встановлено [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) на `false`.

#### Поток з ручними заголовками

Ви можете потокувати відповідь клієнту, використовуючи метод `stream()` на маршруті. Якщо ви 
зробите це, ви мусите встановити всі заголовки вручну перед тим, як вивести щось клієнту.
Це робиться з функцією php `header()` або методом `Flight::response()->setRealHeader()`.

```php
Flight::route('/@filename', function($filename) {

	$response = Flight::response();

	// obviously you would sanitize the path and whatnot.
	$fileNameSafe = basename($filename);

	// If you have additional headers to set here after the route has executed
	// you must define them before anything is echoed out.
	// They must all be a raw call to the header() function or 
	// a call to Flight::response()->setRealHeader()
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// or
	$response->setRealHeader('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');

	$filePath = '/some/path/to/files/'.$fileNameSafe;

	if (!is_readable($filePath)) {
		Flight::halt(404, 'File not found');
	}

	// manually set the content length if you'd like
	header('Content-Length: '.filesize($filePath));
	// or
	$response->setRealHeader('Content-Length: '.filesize($filePath));

	// Stream the file to the client as it's read
	readfile($filePath);

// This is the magic line here
})->stream();
```

#### Поток з заголовками

Ви також можете використовувати метод `streamWithHeaders()` для встановлення заголовків перед початком потокування.

```php
Flight::route('/stream-users', function() {

	// you can add any additional headers you want here
	// you just must use header() or Flight::response()->setRealHeader()

	// however you pull your data, just as an example...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// This is required to send the data to the client
		ob_flush();
	}
	echo '}';

// This is how you'll set the headers before you start streaming.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// optional status code, defaults to 200
	'status' => 200
]);
```

## Дивіться також
- [Middleware](/learn/middleware) - Використання middleware з маршрутами для аутентифікації, логування тощо.
- [Dependency Injection](/learn/dependency-injection-container) - Спрощення створення та керування об'єктами в маршрутах.
- [Why a Framework?](/learn/why-frameworks) - Розуміння переваг використання фреймворку, як Flight.
- [Extending](/learn/extending) - Як розширити Flight своєю функціональністю, включаючи метод `notFound`.
- [php.net: preg_match](https://www.php.net/manual/en/function.preg-match.php) - Функція PHP для співставлення регулярних виразів.

## Усунення несправностей
- Параметри маршруту співставляються за порядком, а не за іменем. Переконайтеся, що порядок параметрів зворотного виклику відповідає визначенню маршруту.
- Використання `Flight::get()` не визначає маршрут; використовуйте `Flight::route('GET /...')` для маршрутизації або контекст об'єкта Router у групах (наприклад, `$router->get(...)`).
- Властивість executedRoute встановлюється лише після виконання маршруту; вона NULL перед виконанням.
- Потокування вимагає вимкнення функціональності буферизації виводу Flight (`flight.v2.output_buffering = false`).
- Для ін'єкції залежностей лише певні визначення маршрутів підтримують інстанціювання на основі контейнера.

### 404 Not Found або несподівана поведінка маршруту

Якщо ви бачите помилку 404 Not Found (але ви присягаєтеся своїм життям, що вона дійсно там і це не помилка друку), це насправді може бути проблема з тим, що ви повертаєте значення в ендпоінті маршруту замість просто виведення його. Причина для цього навмисна, але може заскочити деяких розробників.

```php
Flight::route('/hello', function(){
	// This might cause a 404 Not Found error
	return 'Hello World';
});

// What you probably want
Flight::route('/hello', function(){
	echo 'Hello World';
});
```

Причина для цього полягає в спеціальному механізмі, вбудованому в роутер, який обробляє повернений вивід як сигнал "перейти до наступного маршруту". 
Ви можете побачити поведінку, документовану в розділі [Routing](/learn/routing#passing).

## Журнал змін
- v3: Додано ресурсну маршрутизацію, псевдоніми маршрутів та підтримку потокування, групи маршрутів та підтримку middleware.
- v1: Більшість базових функцій доступні.