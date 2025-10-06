# Маршрутизация

## Обзор
Маршрутизация в Flight PHP сопоставляет шаблоны URL с функциями обратного вызова или методами классов, обеспечивая быстрый и простой обработку запросов. Она разработана для минимальных накладных расходов, удобства для начинающих и расширяемости без внешних зависимостей.

## Понимание
Маршрутизация — это основной механизм, который соединяет HTTP-запросы с логикой вашего приложения в Flight. Определяя маршруты, вы указываете, как разные URL запускают конкретный код, будь то через функции, методы классов или действия контроллера. Система маршрутизации Flight гибкая, поддерживает базовые шаблоны, именованные параметры, регулярные выражения и продвинутые функции, такие как внедрение зависимостей и ресурсная маршрутизация. Этот подход сохраняет ваш код организованным и легким в обслуживании, оставаясь быстрым и простым для начинающих и расширяемым для продвинутых пользователей.

> **Примечание:** Хотите узнать больше о маршрутизации? Посмотрите страницу ["почему фреймворк?"](/learn/why-frameworks) для более подробного объяснения.

## Базовое использование

### Определение простого маршрута
Базовая маршрутизация в Flight выполняется путем сопоставления шаблона URL с функцией обратного вызова или массивом класса и метода.

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

> Маршруты сопоставляются в порядке их определения. Первый маршрут, соответствующий запросу, будет вызван.

### Использование функций в качестве обратных вызовов
Обратный вызов может быть любым объектом, который можно вызвать. Таким образом, вы можете использовать обычную функцию:

```php
function hello() {
    echo 'hello world!';
}

Flight::route('/', 'hello');
```

### Использование классов и методов в качестве контроллера
Вы также можете использовать метод (статический или нет) класса:

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

Или создав объект сначала, а затем вызвав метод:

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

> **Примечание:** По умолчанию, когда контроллер вызывается в рамках фреймворка, класс `flight\Engine` всегда внедряется, если вы не укажете это через [контейнер внедрения зависимостей](/learn/dependency-injection-container)

### Маршрутизация, специфичная для метода

По умолчанию шаблоны маршрутов сопоставляются со всеми методами запросов. Вы можете отвечать на конкретные методы, размещая идентификатор перед URL.

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

Вы также можете сопоставить несколько методов с одним обратным вызовом, используя разделитель `|`:

```php
Flight::route('GET|POST /', function () {
  echo 'I received either a GET or a POST request.';
});
```

### Особая обработка запросов HEAD и OPTIONS

Flight предоставляет встроенную обработку HTTP-запросов `HEAD` и `OPTIONS`:

#### Запросы HEAD

- **Запросы HEAD** обрабатываются так же, как запросы `GET`, но Flight автоматически удаляет тело ответа перед отправкой клиенту.
- Это означает, что вы можете определить маршрут для `GET`, и запросы HEAD к тому же URL вернут только заголовки (без содержимого), как ожидается по стандартам HTTP.

```php
Flight::route('GET /info', function() {
    echo 'This is some info!';
});
// A HEAD request to /info will return the same headers, but no body.
```

#### Запросы OPTIONS

Запросы `OPTIONS` автоматически обрабатываются Flight для любого определенного маршрута.
- Когда получен запрос OPTIONS, Flight отвечает статусом `204 No Content` и заголовком `Allow`, перечисляющим все поддерживаемые HTTP-методы для этого маршрута.
- Вам не нужно определять отдельный маршрут для OPTIONS.

```php
// For a route defined as:
Flight::route('GET|POST /users', function() { /* ... */ });

// An OPTIONS request to /users will respond with:
//
// Status: 204 No Content
// Allow: GET, POST, HEAD, OPTIONS
```

### Использование объекта Router

Кроме того, вы можете получить объект Router, который имеет некоторые вспомогательные методы для вашего использования:

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

### Регулярные выражения (Regex)
Вы можете использовать регулярные выражения в своих маршрутах:

```php
Flight::route('/user/[0-9]+', function () {
  // This will match /user/1234
});
```

Хотя этот метод доступен, рекомендуется использовать именованные параметры или именованные параметры с регулярными выражениями, поскольку они более читаемы и легче в обслуживании.

### Именованные параметры
Вы можете указать именованные параметры в своих маршрутах, которые будут переданы в вашу функцию обратного вызова. **Это больше для читаемости маршрута, чем для чего-либо другого. Пожалуйста, см. раздел ниже о важном предупреждении.**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "hello, $name ($id)!";
});
```

Вы также можете включить регулярные выражения с вашими именованными параметрами, используя разделитель `:`:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // This will match /bob/123
  // But will not match /bob/12345
});
```

> **Примечание:** Сопоставление групп regex `()` с позиционными параметрами не поддерживается. Ex: `:'\(`

#### Важное предупреждение

Хотя в примере выше кажется, что `@name` напрямую связан с переменной `$name`, это не так. Порядок параметров в функции обратного вызова определяет, что передается в нее. Если вы поменяете порядок параметров в функции обратного вызова, переменные также поменяются. Вот пример:

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "hello, $name ($id)!";
});
```

И если вы перейдете по следующему URL: `/bob/123`, вывод будет `hello, 123 (bob)!`. 
_Пожалуйста, будьте осторожны_ при настройке ваших маршрутов и функций обратного вызова!

### Необязательные параметры
Вы можете указать именованные параметры, которые являются необязательными для сопоставления, обернув сегменты в скобки.

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

Любые необязательные параметры, которые не сопоставлены, будут переданы как `NULL`.

### Маршрутизация с подстановочным знаком
Сопоставление выполняется только для отдельных сегментов URL. Если вы хотите сопоставить несколько сегментов, вы можете использовать подстановочный знак `*`.

```php
Flight::route('/blog/*', function () {
  // This will match /blog/2000/02/01
});
```

Чтобы маршрутизировать все запросы к одному обратному вызову, вы можете сделать:

```php
Flight::route('*', function () {
  // Do something
});
```

### Обработчик 404 Not Found

По умолчанию, если URL не найден, Flight отправит простой и обычный ответ `HTTP 404 Not Found`.
Если вы хотите иметь более кастомный ответ 404, вы можете [сопоставить](/learn/extending) свой собственный метод `notFound`:

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

### Обработчик Method Not Found

По умолчанию, если URL найден, но метод не разрешен, Flight отправит простой и обычный ответ `HTTP 405 Method Not Allowed` (Ex: Method Not Allowed. Allowed Methods are: GET, POST). Он также включит заголовок `Allow` с разрешенными методами для этого URL.

Если вы хотите иметь более кастомный ответ 405, вы можете [сопоставить](/learn/extending) свой собственный метод `methodNotFound`:

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

## Продвинутое использование

### Внедрение зависимостей в маршрутах
Если вы хотите использовать внедрение зависимостей через контейнер (PSR-11, PHP-DI, Dice и т.д.), единственный тип маршрутов, где это доступно, — это либо прямое создание объекта самостоятельно с использованием контейнера для создания вашего объекта, либо вы можете использовать строки для определения класса и метода для вызова. Вы можете перейти на страницу [Внедрение зависимостей](/learn/dependency-injection-container) для получения дополнительной информации. 

Вот быстрый пример:

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

### Передача выполнения следующему маршруту
<span class="badge bg-warning">Устарело</span>
Вы можете передать выполнение следующему соответствующему маршруту, вернув `true` из вашей функции обратного вызова.

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

Теперь рекомендуется использовать [middleware](/learn/middleware) для обработки сложных случаев вроде этого.

### Псевдонимы маршрутов
Присваивая псевдоним маршруту, вы можете позже динамически вызывать этот псевдоним в вашем приложении для генерации позже в вашем коде (например: ссылка в HTML-шаблоне или генерация URL для перенаправления).

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

Это особенно полезно, если ваш URL изменится. В приведенном выше примере предположим, что пользователи перемещены в `/admin/users/@id` вместо.
С псевдонимами на месте для маршрута вам больше не нужно искать все старые URL в вашем коде и изменять их, потому что псевдоним теперь вернет `/admin/users/5`, как в примере выше.

Псевдонимы маршрутов все еще работают в группах:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
	// or
	Flight::route('/@id', function($id) { echo 'user:'.$id; })->setAlias('user_view');
});
```

### Просмотр информации о маршруте
Если вы хотите просмотреть информацию о соответствующем маршруте, есть 2 способа это сделать:

1. Вы можете использовать свойство `executedRoute` на объекте `Flight::router()`.
2. Вы можете запросить передачу объекта маршрута в ваш обратный вызов, передав `true` в качестве третьего параметра в методе маршрута. Объект маршрута всегда будет последним параметром, переданным в вашу функцию обратного вызова.

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

> **Примечание:** Свойство `executedRoute` будет установлено только после выполнения маршрута. Если вы попытаетесь получить к нему доступ до выполнения маршрута, оно будет `NULL`. Вы также можете использовать executedRoute в [middleware](/learn/middleware)!

#### Передача `true` в определение маршрута
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

### Группировка маршрутов и Middleware
Могут быть случаи, когда вы хотите сгруппировать связанные маршруты вместе (например, `/api/v1`).
Вы можете сделать это, используя метод `group`:

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

Вы даже можете вкладывать группы в группы:

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

#### Группировка с контекстом объекта

Вы все еще можете использовать группировку маршрутов с объектом `Engine` следующим образом:

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

> **Примечание:** Это предпочтительный метод определения маршрутов и групп с объектом `$router`.

#### Группировка с Middleware

Вы также можете назначить middleware группе маршрутов:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Matches /api/v1/users
  });
}, [ MyAuthMiddleware::class ]); // or [ new MyAuthMiddleware() ] if you want to use an instance
```

См. больше деталей на странице [групповой middleware](/learn/middleware#grouping-middleware).

### Ресурсная маршрутизация
Вы можете создать набор маршрутов для ресурса, используя метод `resource`. Это создаст набор маршрутов для ресурса, следующий RESTful-конвенциям.

Чтобы создать ресурс, сделайте следующее:

```php
Flight::resource('/users', UsersController::class);
```

И что произойдет в фоне — это создание следующих маршрутов:

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

И ваш контроллер будет использовать следующие методы:

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

> **Примечание**: Вы можете просмотреть newly added routes с помощью `runway`, запустив `php runway routes`.

#### Настройка ресурсных маршрутов

Есть несколько опций для настройки ресурсных маршрутов.

##### Базовый псевдоним

Вы можете настроить `aliasBase`. По умолчанию псевдоним — это последняя часть указанного URL.
Например, `/users/` приведет к `aliasBase` равному `users`. Когда эти маршруты создаются,
псевдонимы — `users.index`, `users.create` и т.д. Если вы хотите изменить псевдоним, установите `aliasBase`
в желаемое значение.

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);
```

##### Only и Except

Вы также можете указать, какие маршруты вы хотите создать, используя опции `only` и `except`.

```php
// Whitelist only these methods and blacklist the rest
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);
```

```php
// Blacklist only these methods and whitelist the rest
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);
```

Это по сути опции белого и черного списков, чтобы вы могли указать, какие маршруты вы хотите создать.

##### Middleware

Вы также можете указать middleware, которое будет выполняться на каждом из маршрутов, созданных методом `resource`.

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);
```

### Потоковые ответы

Теперь вы можете передавать потоковые ответы клиенту с помощью `stream()` или `streamWithHeaders()`. 
Это полезно для отправки больших файлов, длительных процессов или генерации больших ответов. 
Потоковый маршрут обрабатывается немного иначе, чем обычный маршрут.

> **Примечание:** Потоковые ответы доступны только если у вас установлен [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) в `false`.

#### Поток с ручными заголовками

Вы можете передать потоковый ответ клиенту, используя метод `stream()` на маршруте. Если вы 
делаете это, вы должны установить все заголовки вручную перед выводом чего-либо клиенту.
Это делается с помощью функции php `header()` или метода `Flight::response()->setRealHeader()`.

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

#### Поток с заголовками

Вы также можете использовать метод `streamWithHeaders()` для установки заголовков перед началом потоковой передачи.

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

## См. также
- [Middleware](/learn/middleware) - Использование middleware с маршрутами для аутентификации, логирования и т.д.
- [Dependency Injection](/learn/dependency-injection-container) - Упрощение создания и управления объектами в маршрутах.
- [Why a Framework?](/learn/why-frameworks) - Понимание преимуществ использования фреймворка вроде Flight.
- [Extending](/learn/extending) - Как расширить Flight своей функциональностью, включая метод `notFound`.
- [php.net: preg_match](https://www.php.net/manual/en/function.preg-match.php) - Функция PHP для сопоставления регулярных выражений.

## Устранение неисправностей
- Параметры маршрута сопоставляются по порядку, а не по имени. Убедитесь, что порядок параметров обратного вызова соответствует определению маршрута.
- Использование `Flight::get()` не определяет маршрут; используйте `Flight::route('GET /...')` для маршрутизации или контекст объекта Router в группах (например, `$router->get(...)`).
- Свойство executedRoute устанавливается только после выполнения маршрута; до выполнения оно NULL.
- Для потоковой передачи требуется отключить устаревшую функциональность буферизации вывода Flight (`flight.v2.output_buffering = false`).
- Для внедрения зависимостей только определенные определения маршрутов поддерживают создание на основе контейнера.

### 404 Not Found или неожиданное поведение маршрута

Если вы видите ошибку 404 Not Found (но вы клянетесь своей жизнью, что это действительно там и это не опечатка), это на самом деле может быть проблема 
с возвратом значения в вашем конечном маршруте вместо простого эха. Причина для этого намеренная, но может подкрасться к некоторым разработчикам.

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

Причина в том, что есть специальный механизм, встроенный в роутер, который обрабатывает возвращаемый вывод как сигнал "перейти к следующему маршруту". 
Вы можете увидеть поведение, документированное в разделе [Routing](/learn/routing#passing).

## Журнал изменений
- v3: Добавлена ресурсная маршрутизация, псевдонимы маршрутов, поддержка потоковой передачи, группы маршрутов и поддержка middleware.
- v1: Большинство базовых функций доступны.