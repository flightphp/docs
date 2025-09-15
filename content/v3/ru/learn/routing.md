# Маршрутизация

> **Примечание:** Хотите узнать больше о маршрутизации? Посетите страницу ["why a framework?"](/learn/why-frameworks) для более подробного объяснения.

Базовая маршрутизация в Flight выполняется путём сопоставления шаблона URL с функцией-обработчиком или массивом, содержащим класс и метод.

```php
Flight::route('/', function(){
    echo 'hello world!';  // Выводит 'hello world!'
});
```

> Маршруты сопоставляются в порядке их определения. Первый подходящий маршрут будет вызван.

### Обработчики/Функции
Обработчик может быть любым вызываемым объектом. Таким образом, вы можете использовать обычную функцию:

```php
function hello() {
    echo 'hello world!';  // Выводит 'hello world!'
}

Flight::route('/', 'hello');
```

### Классы
Вы также можете использовать статический метод класса:

```php
class Greeting {
    public static function hello() {
        echo 'hello world!';  // Выводит 'hello world!'
    }
}

Flight::route('/', [ 'Greeting','hello' ]);
```

Или создав объект сначала, а затем вызвав метод:

```php
// Greeting.php
class Greeting
{
    public function __construct() {
        $this->name = 'John Doe';  // Устанавливает имя
    }

    public function hello() {
        echo "Hello, {$this->name}!";  // Выводит приветствие
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', [ $greeting, 'hello' ]);
// You also can do this without creating the object first
// Note: No args will be injected into the constructor  // Примечание: Аргументы не будут внедрены в конструктор
Flight::route('/', [ 'Greeting', 'hello' ]);
// Additionally you can use this shorter syntax  // Кроме того, вы можете использовать этот более короткий синтаксис
Flight::route('/', 'Greeting->hello');
// or  // или
Flight::route('/', Greeting::class.'->hello');
```

#### Внедрение зависимостей через DIC (Контейнер внедрения зависимостей)
Если вы хотите использовать внедрение зависимостей через контейнер (PSR-11, PHP-DI, Dice и т.д.), это доступно только для маршрутов, где вы создаёте объект самостоятельно или определяете класс и метод строкой. Подробнее см. на странице [Dependency Injection](/learn/extending).

Вот быстрый пример:

```php
use flight\database\PdoWrapper;

// Greeting.php
class Greeting
{
	protected PdoWrapper $pdoWrapper;
	public function __construct(PdoWrapper $pdoWrapper) {
		$this->pdoWrapper = $pdoWrapper;  // Присваивает обёртку PDO
	}

	public function hello(int $id) {
		// Выполняет какие-то действия с $this->pdoWrapper  // Выполняет действия с обёрткой PDO
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "Hello, world! My name is {$name}!";  // Выводит приветствие
	}
}

// index.php

// Setup the container with whatever params you need  // Настраивает контейнер с необходимыми параметрами
// See the Dependency Injection page for more information on PSR-11  // См. страницу Dependency Injection для информации о PSR-11
$dice = new \Dice\Dice();

// Don't forget to reassign the variable with '$dice = '!!!!!  // Не забудьте переприсвоить переменную с '$dice = '
$dice = $dice->addRule('flight\database\PdoWrapper', [
	'shared' => true,
	'constructParams' => [ 
		'mysql:host=localhost;dbname=test', 
		'root',
		'password'
	]
]);

// Register the container handler  // Регистрирует обработчик контейнера
Flight::registerContainerHandler(function($class, $params) use ($dice) {
	return $dice->create($class, $params);
});

// Routes like normal  // Маршруты как обычно
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// or  // или
Flight::route('/hello/@id', 'Greeting->hello');
// or  // или
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

## Маршрутизация по методам

По умолчанию, шаблоны маршрутов сопоставляются со всеми методами запросов. Вы можете отвечать на конкретные методы, указав идентификатор перед URL.

```php
Flight::route('GET /', function () {
  echo 'I received a GET request.';  // Выводит сообщение о получении GET-запроса
});

Flight::route('POST /', function () {
  echo 'I received a POST request.';  // Выводит сообщение о получении POST-запроса
});

// You cannot use Flight::get() for routes as that is a method   // Вы не можете использовать Flight::get() для маршрутов, так как это метод
//    to get variables, not create a route.                     // для получения переменных, а не создания маршрута
// Flight::post('/', function() { /* code */ });
// Flight::patch('/', function() { /* code */ });
// Flight::put('/', function() { /* code */ });
// Flight::delete('/', function() { /* code */ });
```

Вы также можете сопоставить несколько методов с одним обработчиком, используя разделитель `|`:

```php
Flight::route('GET|POST /', function () {
  echo 'I received either a GET or a POST request.';  // Выводит сообщение о получении GET или POST запроса
});
```

Кроме того, вы можете получить объект Router, который имеет вспомогательные методы:

```php
$router = Flight::router();

// maps all methods  // Сопоставляет все методы
$router->map('/', function() {
	echo 'hello world!';  // Выводит 'hello world!'
});

// GET request  // GET-запрос
$router->get('/users', function() {
	echo 'users';  // Выводит 'users'
});
// $router->post();
// $router->put();
// $router->delete();
// $router->patch();
```

## Регулярные выражения

Вы можете использовать регулярные выражения в ваших маршрутах:

```php
Flight::route('/user/[0-9]+', function () {
  // This will match /user/1234  // Это будет соответствовать /user/1234
});
```

Хотя этот метод доступен, рекомендуется использовать именованные параметры или именованные параметры с регулярными выражениями, так как они более читаемы и удобны в обслуживании.

## Именованные параметры

Вы можете указать именованные параметры в ваших маршрутах, которые будут переданы в функцию-обработчик. **Это в основном для читаемости маршрута. Пожалуйста, ознакомьтесь с разделом ниже о важном замечании.**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "hello, $name ($id)!";  // Выводит приветствие
});
```

Вы также можете включить регулярные выражения с именованными параметрами, используя разделитель `:`:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // This will match /bob/123  // Это будет соответствовать /bob/123
  // But will not match /bob/12345  // Но не будет соответствовать /bob/12345
});
```

> **Примечание:** Сопоставление групп регулярных выражений `()` с позиционными параметрами не поддерживается. :'\(

### Важное замечание

Хотя в примере выше кажется, что `@name` напрямую связано с переменной `$name`, это не так. Порядок параметров в функции-обработчике определяет, что будет передано. Таким образом, если вы измените порядок параметров в функции-обработчике, переменные также изменятся. Вот пример:

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "hello, $name ($id)!";  // Выводит приветствие с изменённым порядком
});
```

И если вы перейдёте по URL: `/bob/123`, вывод будет `hello, 123 (bob)!`. Будьте осторожны при настройке маршрутов и функций-обработчиков.

## Опциональные параметры

Вы можете указать именованные параметры, которые являются опциональными для сопоставления, обернув сегменты в скобки.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // This will match the following URLS:  // Это будет соответствовать следующим URL:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

Любые опциональные параметры, которые не сопоставлены, будут переданы как `NULL`.

## Шаблоны (Wildcards)

Сопоставление выполняется только для отдельных сегментов URL. Если вы хотите сопоставить несколько сегментов, вы можете использовать шаблон `*`.

```php
Flight::route('/blog/*', function () {
  // This will match /blog/2000/02/01  // Это будет соответствовать /blog/2000/02/01
});
```

Чтобы маршрутизировать все запросы к одному обработчику, вы можете сделать:

```php
Flight::route('*', function () {
  // Do something  // Выполнить что-то
});
```

## Передача (Passing)

Вы можете передать выполнение следующему подходящему маршруту, вернув `true` из функции-обработчика.

```php
Flight::route('/user/@name', function (string $name) {
  // Check some condition  // Проверяет некоторое условие
  if ($name !== "Bob") {
    // Continue to next route  // Продолжает к следующему маршруту
    return true;
  }
});

Flight::route('/user/*', function () {
  // This will get called  // Это будет вызвано
});
```

## Псевдонимы маршрутов

Вы можете назначить псевдоним маршруту, чтобы URL мог динамически генерироваться позже в коде (например, в шаблоне).

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');  // Создаёт маршрут с псевдонимом

// later in code somewhere  // Позже в коде где-то
Flight::getUrl('user_view', [ 'id' => 5 ]); // will return '/users/5'  // Вернёт '/users/5'
```

Это особенно полезно, если URL изменится. В примере выше, предположим, что users перемещён в `/admin/users/@id`. С псевдонимами на месте, вам не нужно изменять ссылки на псевдоним, так как он теперь вернёт `/admin/users/5`, как в примере.

Псевдонимы маршрутов работают и в группах:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');  // Создаёт маршрут в группе
});


// later in code somewhere  // Позже в коде где-то
Flight::getUrl('user_view', [ 'id' => 5 ]); // will return '/users/5'  // Вернёт '/users/5'
```

## Информация о маршрутах

Если вы хотите осмотреть информацию о сопоставленном маршруте, есть 2 способа. Вы можете использовать свойство `executedRoute` или запросить объект маршрута, переданный в обработчик, указав `true` в качестве третьего параметра в методе маршрута. Объект маршрута всегда будет последним параметром, переданным в функцию-обработчик.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Array of HTTP methods matched against  // Массив HTTP-методов, с которыми сопоставлено
  $route->methods;

  // Array of named parameters  // Массив именованных параметров
  $route->params;

  // Matching regular expression  // Соответствующее регулярное выражение
  $route->regex;

  // Contains the contents of any '*' used in the URL pattern  // Содержит содержимое любого '*' в шаблоне URL
  $route->splat;

  // Shows the url path....if you really need it  // Показывает путь URL... если это действительно нужно
  $route->pattern;

  // Shows what middleware is assigned to this  // Показывает, какое промежуточное ПО назначено этому
  $route->middleware;

  // Shows the alias assigned to this route  // Показывает псевдоним, назначенный этому маршруту
  $route->alias;
}, true);
```

Или, если вы хотите осмотреть последний выполненный маршрут, вы можете сделать:

```php
Flight::route('/', function() {
  $route = Flight::router()->executedRoute;  // Получает последний выполненный маршрут
  // Do something with $route  // Выполняет что-то с $route
  // Array of HTTP methods matched against  // Массив HTTP-методов, с которыми сопоставлено
  $route->methods;

  // Array of named parameters  // Массив именованных параметров
  $route->params;

  // Matching regular expression  // Соответствующее регулярное выражение
  $route->regex;

  // Contains the contents of any '*' used in the URL pattern  // Содержит содержимое любого '*' в шаблоне URL
  $route->splat;

  // Shows the url path....if you really need it  // Показывает путь URL... если это действительно нужно
  $route->pattern;

  // Shows what middleware is assigned to this  // Показывает, какое промежуточное ПО назначено этому
  $route->middleware;

  // Shows the alias assigned to this route  // Показывает псевдоним, назначенный этому маршруту
  $route->alias;
});
```

> **Примечание:** Свойство `executedRoute` будет установлено только после выполнения маршрута. Если вы попытаетесь получить к нему доступ до выполнения маршрута, оно будет `NULL`. Вы также можете использовать executedRoute в промежуточном ПО!

## Группировка маршрутов

Иногда вы можете захотеть сгруппировать связанные маршруты вместе (например, `/api/v1`). Вы можете сделать это, используя метод `group`:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Matches /api/v1/users  // Сопоставляется с /api/v1/users
  });

  Flight::route('/posts', function () {
	// Matches /api/v1/posts  // Сопоставляется с /api/v1/posts
  });
});
```

Вы даже можете вкладывать группы в группы:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() gets variables, it doesn't set a route! See object context below  // Flight::get() получает переменные, не устанавливает маршрут! См. контекст объекта ниже
	Flight::route('GET /users', function () {
	  // Matches GET /api/v1/users  // Сопоставляется с GET /api/v1/users
	});

	Flight::post('/posts', function () {
	  // Matches POST /api/v1/posts  // Сопоставляется с POST /api/v1/posts
	});

	Flight::put('/posts/1', function () {
	  // Matches PUT /api/v1/posts  // Сопоставляется с PUT /api/v1/posts
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() gets variables, it doesn't set a route! See object context below  // Flight::get() получает переменные, не устанавливает маршрут! См. контекст объекта ниже
	Flight::route('GET /users', function () {
	  // Matches GET /api/v2/users  // Сопоставляется с GET /api/v2/users
	});
  });
});
```

### Группировка с контекстом объекта

Вы все еще можете использовать группировку маршрутов с объектом `Engine` следующим образом:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // user the $router variable  // Используйте переменную $router
  $router->get('/users', function () {
	// Matches GET /api/v1/users  // Сопоставляется с GET /api/v1/users
  });

  $router->post('/posts', function () {
	// Matches POST /api/v1/posts  // Сопоставляется с POST /api/v1/posts
  });
});
```

### Группировка с промежуточным ПО

Вы также можете назначить промежуточное ПО группе маршрутов:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Matches /api/v1/users  // Сопоставляется с /api/v1/users
  });
}, [ MyAuthMiddleware::class ]); // or [ new MyAuthMiddleware() ] if you want to use an instance  // или [ new MyAuthMiddleware() ], если вы хотите использовать экземпляр
```

Подробнее см. на странице [group middleware](/learn/middleware#grouping-middleware).

## Маршрутизация ресурсов

Вы можете создать набор маршрутов для ресурса, используя метод `resource`. Это создаст набор маршрутов для ресурса, соответствующий RESTful-конвенциям.

Чтобы создать ресурс, сделайте следующее:

```php
Flight::resource('/users', UsersController::class);  // Создаёт ресурс
```

В фоне это создаст следующие маршруты:

```php
[
      'index' => 'GET ',
      'create' => 'GET /create',
      'store' => 'POST ',
      'show' => 'GET /@id',
      'edit' => 'GET /@id/edit',
      'update' => 'PUT /@id',
      'destroy' => 'DELETE /@id'
]
```

Ваш контроллер будет выглядеть так:

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

> **Примечание**: Вы можете просмотреть newly added routes with `runway` by running `php runway routes`.  // Вы можете просмотреть newly added routes with `runway` by running `php runway routes`.

### Настройка маршрутов ресурсов

Есть несколько опций для настройки маршрутов ресурсов.

#### Базовый псевдоним

Вы можете настроить `aliasBase`. По умолчанию псевдоним — это последняя часть указанного URL. Например, `/users/` приведёт к `aliasBase` как `users`. При создании этих маршрутов псевдонимы будут `users.index`, `users.create` и т.д. Если вы хотите изменить псевдоним, установите `aliasBase` в желаемое значение.

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);  // Устанавливает базовый псевдоним
```

#### Only и Except

Вы также можете указать, какие маршруты вы хотите создать, используя опции `only` и `except`.

```php
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);  // Только указанные маршруты
```

```php
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);  // Исключает указанные маршруты
```

Это опции белого и чёрного списков, чтобы указать, какие маршруты вы хотите создать.

#### Промежуточное ПО

Вы также можете указать промежуточное ПО для выполнения на каждом из маршрутов, созданных методом `resource`.

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);  // Назначает промежуточное ПО
```

## Стриминг

Теперь вы можете стримить ответы клиенту, используя метод `streamWithHeaders()`. Это полезно для отправки больших файлов, длительных процессов или генерации больших ответов. Стриминг маршрута обрабатывается немного иначе, чем обычный маршрут.

> **Примечание:** Стриминговые ответы доступны только если [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) установлено в false.

### Стриминг с ручными заголовками

Вы можете стримить ответ клиенту, используя метод `stream()` на маршруте. Если вы это сделаете, вы должны установить все заголовки вручную перед выводом чего-либо клиенту. Это делается с помощью функции `header()` PHP или метода `Flight::response()->setRealHeader()`.

```php
Flight::route('/@filename', function($filename) {

	// obviously you would sanitize the path and whatnot.  // очевидно, вы бы проверили путь и т.д.
	$fileNameSafe = basename($filename);

	// If you have additional headers to set here after the route has executed  // Если у вас есть дополнительные заголовки для установки после выполнения маршрута
	// you must define them before anything is echoed out.  // вы должны определить их перед выводом чего-либо.
	// They must all be a raw call to the header() function or   // Они должны быть прямым вызовом функции header() или
	// a call to Flight::response()->setRealHeader()  // вызовом Flight::response()->setRealHeader()
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// or  // или
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$fileNameSafe.'"');

	$filePath = '/some/path/to/files/'.$fileNameSafe;

	if (!is_readable($filePath)) {
		Flight::halt(404, 'File not found');  // Останавливает с ошибкой 404, если файл не найден
	}

	// manually set the content length if you'd like  // Ручная установка длины содержимого, если хотите
	header('Content-Length: '.filesize($filePath));

	// Stream the file to the client as it's read  // Стриминг файла клиенту по ходу чтения
	readfile($filePath);

// This is the magic line here  // Это магическая строка
})->stream();
```

### Стриминг с заголовками

Вы также можете использовать метод `streamWithHeaders()` для установки заголовков перед началом стриминга.

```php
Flight::route('/stream-users', function() {

	// you can add any additional headers you want here  // Вы можете добавить любые дополнительные заголовки здесь
	// you just must use header() or Flight::response()->setRealHeader()  // Вы просто должны использовать header() или Flight::response()->setRealHeader()

	// however you pull your data, just as an example...  // Как бы вы ни получали данные, вот пример...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// This is required to send the data to the client  // Это требуется для отправки данных клиенту
		ob_flush();
	}
	echo '}';

// This is how you'll set the headers before you start streaming.  // Так вы установите заголовки перед началом стриминга
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// optional status code, defaults to 200  // Опциональный код статуса, по умолчанию 200
	'status' => 200
]);
```