# Маршрутизация

> **Примечание:** Хотите узнать больше о маршрутизации? Проверьте страницу ["почему фреймворк?"](/learn/why-frameworks) для более подробного объяснения.

Основная маршрутизация в Flight выполняется путем сопоставления шаблона URL с функцией обратного вызова или массивом класса и метода.

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

> Маршруты сопоставляются в порядке их определения. Первый маршрут, который соответствует запросу, будет вызван.

### Функции обратного вызова
Функция обратного вызова может быть любым вызываемым объектом. Таким образом, вы можете использовать обычную функцию:

```php
function hello() {
    echo 'hello world!';
}

Flight::route('/', 'hello');
```

### Классы
Вы также можете использовать статический метод класса:

```php
class Greeting {
    public static function hello() {
        echo 'hello world!';
    }
}

Flight::route('/', [ 'Greeting','hello' ]);
```

Или путем создания объекта сначала, а затем вызова метода:

```php
// Greeting.php
class Greeting
{
    public function __construct() {
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "Hello, {$this->name}!";
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', [ $greeting, 'hello' ]);
// Вы также можете сделать это без создания объекта сначала
// Примечание: никакие аргументы не будут внедрены в конструктор
Flight::route('/', [ 'Greeting', 'hello' ]);
// Кроме того, вы можете использовать этот более короткий синтаксис
Flight::route('/', 'Greeting->hello');
// или
Flight::route('/', Greeting::class.'->hello');
```

#### Внедрение зависимостей через DIC (Контейнер внедрения зависимостей)
Если вы хотите использовать внедрение зависимостей через контейнер (PSR-11, PHP-DI, Dice и т.д.), это доступно только для маршрутов, где вы напрямую создаете объект сами или используете строки для определения класса и метода. Вы можете перейти на страницу [Внедрение зависимостей](/learn/extending) для получения дополнительной информации.

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
		// выполните что-то с $this->pdoWrapper
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "Hello, world! My name is {$name}!";
	}
}

// index.php

// Настройте контейнер с необходимыми параметрами
// См. страницу Внедрение зависимостей для дополнительной информации о PSR-11
$dice = new \Dice\Dice();

// Не забудьте переприсвоить переменную с '$dice = '!!!!!
$dice = $dice->addRule('flight\database\PdoWrapper', [
	'shared' => true,
	'constructParams' => [ 
		'mysql:host=localhost;dbname=test', 
		'root',
		'password'
	]
]);

// Зарегистрируйте обработчик контейнера
Flight::registerContainerHandler(function($class, $params) use ($dice) {
	return $dice->create($class, $params);
});

// Маршруты как обычно
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// или
Flight::route('/hello/@id', 'Greeting->hello');
// или
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

## Маршрутизация по методам

По умолчанию, шаблоны маршрутов сопоставляются со всеми методами запросов. Вы можете отвечать на конкретные методы, разместив идентификатор перед URL.

```php
Flight::route('GET /', function () {
  echo 'I received a GET request.';
});

Flight::route('POST /', function () {
  echo 'I received a POST request.';
});

// Вы не можете использовать Flight::get() для маршрутов, так как это метод 
//    для получения переменных, а не создания маршрута.
// Flight::post('/', function() { /* code */ });
// Flight::patch('/', function() { /* code */ });
// Flight::put('/', function() { /* code */ });
// Flight::delete('/', function() { /* code */ });
```

Вы также можете сопоставить несколько методов с одной функцией обратного вызова, используя разделитель `|`:

```php
Flight::route('GET|POST /', function () {
  echo 'I received either a GET or a POST request.';
});
```

Кроме того, вы можете получить объект Router, который имеет некоторые вспомогательные методы для использования:

```php
$router = Flight::router();

// сопоставляет все методы
$router->map('/', function() {
	echo 'hello world!';
});

// GET запрос
$router->get('/users', function() {
	echo 'users';
});
// $router->post();
// $router->put();
// $router->delete();
// $router->patch();
```

## Регулярные выражения

Вы можете использовать регулярные выражения в своих маршрутах:

```php
Flight::route('/user/[0-9]+', function () {
  // Это будет соответствовать /user/1234
});
```

Хотя этот метод доступен, рекомендуется использовать именованные параметры или именованные параметры с регулярными выражениями, так как они более читаемы и проще в обслуживании.

## Именованные параметры

Вы можете указать именованные параметры в своих маршрутах, которые будут переданы вашей функции обратного вызова. **Это больше для читаемости маршрута, чем для чего-либо еще. Пожалуйста, ознакомьтесь с разделом ниже о важном замечании.**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "hello, $name ($id)!";
});
```

Вы также можете включить регулярные выражения с вашими именованными параметрами, используя разделитель `:`:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // Это будет соответствовать /bob/123
  // Но не будет соответствовать /bob/12345
});
```

> **Примечание:** Сопоставление групп регулярных выражений `()` с позиционными параметрами не поддерживается. :'\(

### Важное замечание

Хотя в примере выше кажется, что `@name` напрямую связано с переменной `$name`, это не так. Порядок параметров в функции обратного вызова определяет, что передается в нее. Таким образом, если вы переключите порядок параметров в функции обратного вызова, переменные также переключатся. Вот пример:

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "hello, $name ($id)!";
});
```

И если вы перейдете по следующему URL: `/bob/123`, вывод будет `hello, 123 (bob)!.` Будьте осторожны при настройке маршрутов и функций обратного вызова.

## Необязательные параметры

Вы можете указать именованные параметры, которые являются необязательными для сопоставления, обернув сегменты в скобки.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // Это будет соответствовать следующим URL:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

Любые необязательные параметры, которые не сопоставлены, будут переданы как `NULL`.

## Шаблоны

Сопоставление выполняется только для отдельных сегментов URL. Если вы хотите сопоставить несколько сегментов, вы можете использовать шаблон `*`.

```php
Flight::route('/blog/*', function () {
  // Это будет соответствовать /blog/2000/02/01
});
```

Чтобы маршрутизировать все запросы к одной функции обратного вызова, вы можете сделать:

```php
Flight::route('*', function () {
  // Выполните что-то
});
```

## Передача

Вы можете передать выполнение следующему сопоставленному маршруту, вернув `true` из вашей функции обратного вызова.

```php
Flight::route('/user/@name', function (string $name) {
  // Проверьте некоторое условие
  if ($name !== "Bob") {
    // Продолжите к следующему маршруту
    return true;
  }
});

Flight::route('/user/*', function () {
  // Это будет вызвано
});
```

## Псевдонимы маршрутов

Вы можете назначить псевдоним маршруту, чтобы URL мог динамически генерироваться позже в вашем коде (например, в шаблоне).

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');

// позже в коде где-то
Flight::getUrl('user_view', [ 'id' => 5 ]); // вернет '/users/5'
```

Это особенно полезно, если ваш URL изменится. В примере выше, предположим, что users перемещен в `/admin/users/@id` вместо этого. С псевдонимами на месте, вам не нужно изменять места, где вы ссылаетесь на псевдоним, потому что псевдоним теперь вернет `/admin/users/5`, как в примере выше.

Псевдонимы маршрутов также работают в группах:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});


// позже в коде где-то
Flight::getUrl('user_view', [ 'id' => 5 ]); // вернет '/users/5'
```

## Информация о маршруте

Если вы хотите проверить информацию о сопоставленном маршруте, есть 2 способа. Вы можете использовать свойство `executedRoute` или запросить объект маршрута, чтобы он был передан вашей функции обратного вызова, передавая `true` в качестве третьего параметра в методе маршрута. Объект маршрута всегда будет последним параметром, переданным вашей функции обратного вызова.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Массив HTTP-методов, с которыми сопоставлено
  $route->methods;

  // Массив именованных параметров
  $route->params;

  // Сопоставляющее регулярное выражение
  $route->regex;

  // Содержит содержимое любого '*' использованного в шаблоне URL
  $route->splat;

  // Показывает путь URL....если вам это действительно нужно
  $route->pattern;

  // Показывает, какое промежуточное ПО назначено этому
  $route->middleware;

  // Показывает псевдоним, назначенный этому маршруту
  $route->alias;
}, true);
```

Или, если вы хотите проверить последний выполненный маршрут, вы можете сделать:

```php
Flight::route('/', function() {
  $route = Flight::router()->executedRoute;
  // Сделайте что-то с $route
  // Массив HTTP-методов, с которыми сопоставлено
  $route->methods;

  // Массив именованных параметров
  $route->params;

  // Сопоставляющее регулярное выражение
  $route->regex;

  // Содержит содержимое любого '*' использованного в шаблоне URL
  $route->splat;

  // Показывает путь URL....если вам это действительно нужно
  $route->pattern;

  // Показывает, какое промежуточное ПО назначено этому
  $route->middleware;

  // Показывает псевдоним, назначенный этому маршруту
  $route->alias;
});
```

> **Примечание:** Свойство `executedRoute` будет установлено только после выполнения маршрута. Если вы попытаетесь получить к нему доступ до выполнения маршрута, оно будет `NULL`. Вы также можете использовать executedRoute в промежуточном ПО!

## Группировка маршрутов

Могут быть случаи, когда вы хотите сгруппировать связанные маршруты вместе (например, `/api/v1`). Вы можете сделать это, используя метод `group`:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Сопоставляется с /api/v1/users
  });

  Flight::route('/posts', function () {
	// Сопоставляется с /api/v1/posts
  });
});
```

Вы даже можете вкладывать группы в группы:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() получает переменные, это не устанавливает маршрут! См. контекст объекта ниже
	Flight::route('GET /users', function () {
	  // Сопоставляется с GET /api/v1/users
	});

	Flight::post('/posts', function () {
	  // Сопоставляется с POST /api/v1/posts
	});

	Flight::put('/posts/1', function () {
	  // Сопоставляется с PUT /api/v1/posts
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() получает переменные, это не устанавливает маршрут! См. контекст объекта ниже
	Flight::route('GET /users', function () {
	  // Сопоставляется с GET /api/v2/users
	});
  });
});
```

### Группировка с контекстом объекта

Вы все еще можете использовать группировку маршрутов с объектом `Engine` следующим образом:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // используйте переменную $router
  $router->get('/users', function () {
	// Сопоставляется с GET /api/v1/users
  });

  $router->post('/posts', function () {
	// Сопоставляется с POST /api/v1/posts
  });
});
```

### Группировка с промежуточным ПО

Вы также можете назначить промежуточное ПО группе маршрутов:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Сопоставляется с /api/v1/users
  });
}, [ MyAuthMiddleware::class ]); // или [ new MyAuthMiddleware() ], если вы хотите использовать экземпляр
```

См. дополнительные детали на странице [группирование промежуточного ПО](/learn/middleware#grouping-middleware).

## Маршрутизация ресурсов

Вы можете создать набор маршрутов для ресурса, используя метод `resource`. Это создаст набор маршрутов для ресурса, following RESTful conventions.

Чтобы создать ресурс, сделайте следующее:

```php
Flight::resource('/users', UsersController::class);
```

И что произойдет в фоновом режиме, это создаст следующие маршруты:

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

И ваш контроллер будет выглядеть так:

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

### Настройка маршрутов ресурсов

Есть несколько опций для настройки маршрутов ресурсов.

#### Базовый псевдоним

Вы можете настроить `aliasBase`. По умолчанию псевдоним — это последняя часть указанного URL. Например, `/users/` приведет к `aliasBase` равному `users`. Когда эти маршруты создаются, псевдонимы — `users.index`, `users.create` и т.д. Если вы хотите изменить псевдоним, установите `aliasBase` на желаемое значение.

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);
```

#### Только и кроме

Вы также можете указать, какие маршруты вы хотите создать, используя опции `only` и `except`.

```php
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);
```

```php
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);
```

Это, по сути, опции белого и черного списков, чтобы вы могли указать, какие маршруты вы хотите создать.

#### Промежуточное ПО

Вы также можете указать промежуточное ПО для выполнения на каждом из маршрутов, созданных методом `resource`.

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);
```

## Стриминг

Теперь вы можете стримить ответы клиенту, используя метод `streamWithHeaders()`. Это полезно для отправки больших файлов, длительных процессов или генерации больших ответов. Стриминг маршрута обрабатывается немного иначе, чем обычный маршрут.

> **Примечание:** Стриминговые ответы доступны только если у вас [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) установлено как false.

### Стриминг с ручными заголовками

Вы можете стримить ответ клиенту, используя метод `stream()` на маршруте. Если вы это сделаете, вы должны установить все методы вручную перед выводом чего-либо клиенту. Это делается с помощью функции `header()` php или метода `Flight::response()->setRealHeader()`.

```php
Flight::route('/@filename', function($filename) {

	// очевидно, вы бы очистили путь и т.д.
	$fileNameSafe = basename($filename);

	// Если у вас есть дополнительные заголовки для установки здесь после выполнения маршрута
	// вы должны определить их перед тем, как что-либо выведено.
	// Они должны быть raw вызовом функции header() или
	// вызовом Flight::response()->setRealHeader()
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// или
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$fileNameSafe.'"');

	$fileData = file_get_contents('/some/path/to/files/'.$fileNameSafe);

	// Обработка ошибок и т.д.
	if(empty($fileData)) {
		Flight::halt(404, 'File not found');
	}

	// вручную установите длину содержимого, если хотите
	header('Content-Length: '.filesize($filename));

	// Стриминг данных клиенту
	echo $fileData;

// Это магическая строка здесь
})->stream();
```

### Стриминг с заголовками

Вы также можете использовать метод `streamWithHeaders()` для установки заголовков перед началом стриминга.

```php
Flight::route('/stream-users', function() {

	// вы можете добавить любые дополнительные заголовки, которые хотите здесь
	// вы просто должны использовать header() или Flight::response()->setRealHeader()

	// как бы вы не получали свои данные, просто как пример...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// Это требуется для отправки данных клиенту
		ob_flush();
	}
	echo '}';

// Это как вы установите заголовки перед началом стриминга.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// необязательный код статуса, по умолчанию 200
	'status' => 200
]);
```