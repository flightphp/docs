# Маршрутизація

> **Порада:** Хочете дізнатися більше про маршрутизацію? Перегляньте сторінку ["why a framework?"](/learn/why-frameworks) для більш детального пояснення.

Основна маршрутизація в Flight здійснюється шляхом зіставлення шаблону URL з функцією зворотного виклику або масивом класу та методу.

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

> Шляхи маршрутизації обробляються в порядку їх визначення. Перший шлях, який відповідає запиту, буде викликаний.

### Функції зворотного виклику
Функція зворотного виклику може бути будь-яким об'єктом, який можна викликати. Отже, ви можете використовувати звичайну функцію:

```php
function hello() {
    echo 'hello world!';
}

Flight::route('/', 'hello');
```

### Класи
Ви також можете використовувати статичний метод класу:

```php
class Greeting {
    public static function hello() {
        echo 'hello world!';
    }
}

Flight::route('/', [ 'Greeting','hello' ]);
```

Або шляхом створення об'єкта спочатку, а потім виклику методу:

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
// Ви також можете зробити це без створення об'єкта спочатку
// Порада: Аргументи не будуть інжектовані в конструктор
Flight::route('/', [ 'Greeting', 'hello' ]);
// Крім того, ви можете використовувати цей коротший синтаксис
Flight::route('/', 'Greeting->hello');
// або
Flight::route('/', Greeting::class.'->hello');
```

#### Ін'єкція залежностей через DIC (Контейнер ін'єкції залежностей)
Якщо ви хочете використовувати ін'єкцію залежностей через контейнер (PSR-11, PHP-DI, Dice тощо), єдиний тип шляхів, де це доступно, — це або безпосереднє створення об'єкта самостійно та використання контейнера для створення вашого об'єкта, або використання рядків для визначення класу та методу для виклику. Ви можете перейти на сторінку [Dependency Injection](/learn/extending) для отримання додаткової інформації.

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
		// виконайте щось з $this->pdoWrapper
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "Hello, world! My name is {$name}!";
	}
}

// index.php

// Налаштуйте контейнер з необхідними параметрами
// Дивіться сторінку Dependency Injection для отримання додаткової інформації про PSR-11
$dice = new \Dice\Dice();

// Не забудьте переприсвоїти змінну з '$dice = '!!!!!
$dice = $dice->addRule('flight\database\PdoWrapper', [
	'shared' => true,
	'constructParams' => [ 
		'mysql:host=localhost;dbname=test', 
		'root',
		'password'
	]
]);

// Зареєструйте обробник контейнера
Flight::registerContainerHandler(function($class, $params) use ($dice) {
	return $dice->create($class, $params);
});

// Шляхи як зазвичай
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// або
Flight::route('/hello/@id', 'Greeting->hello');
// або
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

## Маршрутизація за методами

За замовчуванням, шаблони шляхів маршрутизації зіставляються з усіма методами запитів. Ви можете відповідати на конкретні методи, розміщуючи ідентифікатор перед URL.

```php
Flight::route('GET /', function () {
  echo 'I received a GET request.';
});

Flight::route('POST /', function () {
  echo 'I received a POST request.';
});

// Ви не можете використовувати Flight::get() для шляхів, оскільки це метод 
//    для отримання змінних, а не створення шляху.
// Flight::post('/', function() { /* code */ });
// Flight::patch('/', function() { /* code */ });
// Flight::put('/', function() { /* code */ });
// Flight::delete('/', function() { /* code */ });
```

Ви також можете зіставити кілька методів з однією функцією зворотного виклику, використовуючи роздільник `|`:

```php
Flight::route('GET|POST /', function () {
  echo 'I received either a GET or a POST request.';
});
```

Крім того, ви можете отримати об'єкт Router, який має деякі допоміжні методи для використання:

```php
$router = Flight::router();

// зіставляє всі методи
$router->map('/', function() {
	echo 'hello world!';
});

// GET запит
$router->get('/users', function() {
	echo 'users';
});
// $router->post();
// $router->put();
// $router->delete();
// $router->patch();
```

## Регулярні вирази

Ви можете використовувати регулярні вирази у ваших шляхах:

```php
Flight::route('/user/[0-9]+', function () {
  // Це буде відповідати /user/1234
});
```

Хоча цей метод доступний, рекомендується використовувати іменовані параметри або іменовані параметри з регулярними виразами, оскільки вони більш читабельні та легші у підтримці.

## Іменовані параметри

Ви можете вказати іменовані параметри у ваших шляхах, які будуть передані до вашої функції зворотного виклику. **Це в основному для читабельності шляху. Будь ласка, перегляньте розділ нижче щодо важливої обережності.**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "hello, $name ($id)!";
});
```

Ви також можете включити регулярні вирази з вашими іменованими параметрами, використовуючи роздільник `:`:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // Це буде відповідати /bob/123
  // Але не буде відповідати /bob/12345
});
```

> **Порада:** Підтримка груп регулярних виразів `()` з позиційними параметрами не підтримується. :'\(

### Важлива обережність

Хоча у прикладі вище здається, що `@name` безпосередньо пов'язаний зі змінною `$name`, це не так. Порядок параметрів у функції зворотного виклику визначає, що буде передано до неї. Отже, якщо ви зміните порядок параметрів у функції зворотного виклику, змінні також будуть змінені. Ось приклад:

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "hello, $name ($id)!";
});
```

І якщо ви перейдете за таким URL: `/bob/123`, виведення буде `hello, 123 (bob)!`. Будьте обережні при налаштуванні ваших шляхів та функцій зворотного виклику.

## Опціональні параметри

Ви можете вказати іменовані параметри, які є опціональними для зіставлення, обгортаючи сегменти в дужки.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // Це буде відповідати таким URL:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

Будь-які опціональні параметри, які не зіставлені, будуть передані як `NULL`.

## Шаблони з підстановками

Зіставлення виконується лише для окремих сегментів URL. Якщо ви хочете зіставити кілька сегментів, ви можете використовувати шаблон `*`.

```php
Flight::route('/blog/*', function () {
  // Це буде відповідати /blog/2000/02/01
});
```

Щоб маршрутизувати всі запити до однієї функції зворотного виклику, ви можете зробити:

```php
Flight::route('*', function () {
  // Виконайте щось
});
```

## Передача

Ви можете передати виконання до наступного зіставленого шляху, повернувши `true` з вашої функції зворотного виклику.

```php
Flight::route('/user/@name', function (string $name) {
  // Перевірте якусь умову
  if ($name !== "Bob") {
    // Продовжіть до наступного шляху
    return true;
  }
});

Flight::route('/user/*', function () {
  // Це буде викликано
});
```

## Псевдоніми шляхів

Ви можете призначити псевдонім шляху, щоб URL можна було динамічно генерувати пізніше у вашому коді (наприклад, у шаблоні).

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');

// пізніше десь у коді
Flight::getUrl('user_view', [ 'id' => 5 ]); // поверне '/users/5'
```

Це особливо корисно, якщо ваш URL зміниться. У прикладі вище, припустімо, що users було перенесено до `/admin/users/@id`.
З псевдонімами на місці, вам не потрібно змінювати будь-де посилання на псевдонім, оскільки псевдонім тепер поверне `/admin/users/5`, як у прикладі вище.

Псевдоніми шляхів також працюють у групах:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});


// пізніше десь у коді
Flight::getUrl('user_view', [ 'id' => 5 ]); // поверне '/users/5'
```

## Інформація про шляхи

Якщо ви хочете перевірити інформацію про зіставлений шлях, є 2 способи. Ви можете використовувати властивість `executedRoute` або запитати об'єкт шляху, щоб він був переданий до вашої функції зворотного виклику, передаючи `true` як третій параметр у методі шляху. Об'єкт шляху завжди буде останнім параметром, переданим до вашої функції зворотного виклику.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Масив HTTP-методів, з якими зіставлено
  $route->methods;

  // Масив іменованих параметрів
  $route->params;

  // Зіставлений регулярний вираз
  $route->regex;

  // Містить вміст будь-якого '*' у шаблоні URL
  $route->splat;

  // Показує шлях URL....якщо вам це дійсно потрібно
  $route->pattern;

  // Показує, який middleware призначено цьому
  $route->middleware;

  // Показує псевдонім, призначений цьому шляху
  $route->alias;
}, true);
```

Або якщо ви хочете перевірити останній виконаний шлях, ви можете зробити:

```php
Flight::route('/', function() {
  $route = Flight::router()->executedRoute;
  // Виконайте щось з $route
  // Масив HTTP-методів, з якими зіставлено
  $route->methods;

  // Масив іменованих параметрів
  $route->params;

  // Зіставлений регулярний вираз
  $route->regex;

  // Містить вміст будь-якого '*' у шаблоні URL
  $route->splat;

  // Показує шлях URL....якщо вам це дійсно потрібно
  $route->pattern;

  // Показує, який middleware призначено цьому
  $route->middleware;

  // Показує псевдонім, призначений цьому шляху
  $route->alias;
});
```

> **Порада:** Властивість `executedRoute` буде встановлена лише після виконання шляху. Якщо ви спробуєте отримати до неї доступ до виконання шляху, вона буде `NULL`. Ви також можете використовувати executedRoute у middleware!

## Групування шляхів

Можливо, будуть випадки, коли ви захочете згрупувати пов'язані шляхи разом (наприклад, `/api/v1`). Ви можете зробити це, використовуючи метод `group`:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Відповідає /api/v1/users
  });

  Flight::route('/posts', function () {
	// Відповідає /api/v1/posts
  });
});
```

Ви навіть можете вкладати групи в групи:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() отримує змінні, це не встановлює шлях! Дивіться контекст об'єкта нижче
	Flight::route('GET /users', function () {
	  // Відповідає GET /api/v1/users
	});

	Flight::post('/posts', function () {
	  // Відповідає POST /api/v1/posts
	});

	Flight::put('/posts/1', function () {
	  // Відповідає PUT /api/v1/posts
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() отримує змінні, це не встановлює шлях! Дивіться контекст об'єкта нижче
	Flight::route('GET /users', function () {
	  // Відповідає GET /api/v2/users
	});
  });
});
```

### Групування з контекстом об'єкта

Ви все ще можете використовувати групування шляхів з об'єктом `Engine` таким чином:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // використовуйте змінну $router
  $router->get('/users', function () {
	// Відповідає GET /api/v1/users
  });

  $router->post('/posts', function () {
	// Відповідає POST /api/v1/posts
  });
});
```

### Групування з middleware

Ви також можете призначити middleware групі шляхів:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Відповідає /api/v1/users
  });
}, [ MyAuthMiddleware::class ]); // або [ new MyAuthMiddleware() ], якщо ви хочете використовувати екземпляр
```

Дивіться більше деталей на сторінці [group middleware](/learn/middleware#grouping-middleware).

## Маршрутизація ресурсів

Ви можете створити набір шляхів для ресурсу, використовуючи метод `resource`. Це створить набір шляхів для ресурсу, який слідує RESTful-конвенціям.

Щоб створити ресурс, зробіть наступне:

```php
Flight::resource('/users', UsersController::class);
```

І що станеться у фоновому режимі, це створить такі шляхи:

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

І ваш контролер буде виглядати так:

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

> **Порада**: Ви можете переглянути newly додані шляхи з `runway`, запустивши `php runway routes`.

### Налаштування шляхів ресурсів

Є кілька опцій для налаштування шляхів ресурсів.

#### База псевдонімів

Ви можете налаштувати `aliasBase`. За замовчуванням псевдонім — це остання частина вказаного URL.
Наприклад, `/users/` призведе до `aliasBase` як `users`. Коли ці шляхи створюються,
псевдоніми — `users.index`, `users.create` тощо. Якщо ви хочете змінити псевдонім, встановіть `aliasBase`
до значення, яке ви хочете.

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);
```

#### Only і Except

Ви також можете вказати, які шляхи ви хочете створити, використовуючи опції `only` та `except`.

```php
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);
```

```php
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);
```

Це basically опції білого та чорного списків, щоб ви могли вказати, які шляхи ви хочете створити.

#### Middleware

Ви також можете вказати middleware, яке буде виконуватися на кожному з шляхів, створених методом `resource`.

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);
```

## Стриминг

Тепер ви можете стрімити відповіді клієнту, використовуючи метод `streamWithHeaders()`. 
Це корисно для надсилання великих файлів, тривалих процесів або генерації великих відповідей. 
Стриминг шляху обробляється трохи інакше, ніж звичайний шлях.

> **Порада:** Стримингові відповіді доступні лише якщо у вас [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) встановлено як false.

### Стриминг з ручними заголовками

Ви можете стрімити відповідь клієнту, використовуючи метод `stream()` на шляху. Якщо ви 
зробите це, ви повинні встановити всі методи вручну перед тим, як вивести щось клієнту.
Це робиться за допомогою функції `header()` php або методу `Flight::response()->setRealHeader()`.

```php
Flight::route('/@filename', function($filename) {

	// очевидно, ви б посанітизували шлях і все таке.
	$fileNameSafe = basename($filename);

	// Якщо у вас є додаткові заголовки для встановлення тут після виконання шляху
	// ви повинні визначити їх перед тим, як щось буде виведено.
	// Вони повинні бути сирим викликом функції header() або 
	// викликом Flight::response()->setRealHeader()
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// або
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$fileNameSafe.'"');

	$filePath = '/some/path/to/files/'.$fileNameSafe;

	if (!is_readable($filePath)) {
		Flight::halt(404, 'File not found');
	}

	// вручну встановіть довжину вмісту, якщо ви хочете
	header('Content-Length: '.filesize($filePath));

	// Стрімуйте файл клієнту під час його читання
	readfile($filePath);

// Це магічна лінія тут
})->stream();
```

### Стриминг з заголовками

Ви також можете використовувати метод `streamWithHeaders()` для встановлення заголовків перед початком стрімингу.

```php
Flight::route('/stream-users', function() {

	// ви можете додати будь-які додаткові заголовки, які ви хочете тут
	// ви просто повинні використовувати header() або Flight::response()->setRealHeader()

	// однак ви отримуєте дані, просто як приклад...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// Це вимагається для надсилання даних клієнту
		ob_flush();
	}
	echo '}';

// Це як ви встановите заголовки перед початком стрімингу.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// необов'язковий код статусу, за замовчуванням 200
	'status' => 200
]);
```