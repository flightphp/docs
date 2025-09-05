# Маршрутизація

> **Note:** Хочете дізнатися більше про маршрутизацію? Перегляньте сторінку ["why a framework?"](/learn/why-frameworks) для більш детального пояснення.

Базова маршрутизація в Flight виконується шляхом зіставлення шаблону URL з функцією зворотного виклику або масивом класу і методу.

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

> Маршрути зіставляються в порядку їх визначення. Перший маршрут, який відповідає запиту, буде викликаний.

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

Або шляхом створення об'єкта спочатку і потім виклику методу:

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
// Note: Аргументи не будуть ін'єктовані в конструктор
Flight::route('/', [ 'Greeting', 'hello' ]);
// Крім того, ви можете використовувати цей коротший синтаксис
Flight::route('/', 'Greeting->hello');
// або
Flight::route('/', Greeting::class.'->hello');
```

#### Ін'єкція залежностей через контейнер (Dependency Injection Container)
Якщо ви хочете використовувати ін'єкцію залежностей через контейнер (PSR-11, PHP-DI, Dice тощо), єдиний тип маршрутів, де це доступно, — це безпосередньо створення об'єкта самостійно або використання рядків для визначення класу і методу для виклику. Ви можете перейти на сторінку [Dependency Injection](/learn/extending) для отримання більшої інформації.

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
// Дивіться сторінку Dependency Injection для більшої інформації про PSR-11
$dice = new \Dice\Dice();

// Не забудьте перепризначити змінну з '$dice = '!!!!!
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

// Маршрути як зазвичай
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// або
Flight::route('/hello/@id', 'Greeting->hello');
// або
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

## Маршрутизація за методами

За замовчуванням, шаблони маршрутів зіставляються з усіма методами запитів. Ви можете відповідати на конкретні методи, розміщуючи ідентифікатор перед URL.

```php
Flight::route('GET /', function () {
  echo 'I received a GET request.';
});

Flight::route('POST /', function () {
  echo 'I received a POST request.';
});

// Ви не можете використовувати Flight::get() для маршрутів, оскільки це метод 
//    для отримання змінних, а не створення маршруту.
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

Ви можете використовувати регулярні вирази у своїх маршрутах:

```php
Flight::route('/user/[0-9]+', function () {
  // Це відповідатиме /user/1234
});
```

Хоча цей метод доступний, рекомендується використовувати іменовані параметри або іменовані параметри з регулярними виразами, оскільки вони більш читабельні і легші у підтримці.

## Іменовані параметри

Ви можете вказати іменовані параметри у своїх маршрутах, які будуть передані функції зворотного виклику. **Це більше для читабельності маршруту, ніж для чогось іншого. Будь ласка, перегляньте розділ нижче щодо важливої застереження.**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "hello, $name ($id)!";
});
```

Ви також можете включити регулярні вирази з іменованими параметрами, використовуючи роздільник `:`:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // Це відповідатиме /bob/123
  // Але не відповідатиме /bob/12345
});
```

> **Note:** Підтримка груп регулярних виразів `()` з позиційними параметрами не підтримується. :'\(

### Важлива застереження

Хоча у прикладі вище здається, що `@name` безпосередньо пов'язаний зі змінною `$name`, це не так. Порядок параметрів у функції зворотного виклику визначає, що буде передано їй. Отже, якщо ви зміните порядок параметрів у функції зворотного виклику, змінні також будуть змінені. Ось приклад:

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "hello, $name ($id)!";
});
```

І якщо ви перейдете за таким URL: `/bob/123`, виведення буде `hello, 123 (bob)!`. Будьте обережні при налаштуванні своїх маршрутів і функцій зворотного виклику.

## Опціональні параметри

Ви можете вказати іменовані параметри, які є опціональними для зіставлення, обгорнувши сегменти в дужки.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // Це відповідатиме таким URL:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

Будь-які опціональні параметри, які не зіставляються, будуть передані як `NULL`.

## Шаблони

Зіставлення виконується лише для окремих сегментів URL. Якщо ви хочете зіставити кілька сегментів, ви можете використовувати шаблон `*`.

```php
Flight::route('/blog/*', function () {
  // Це відповідатиме /blog/2000/02/01
});
```

Щоб маршрутизувати всі запити на одну функцію зворотного виклику, ви можете зробити:

```php
Flight::route('*', function () {
  // Виконайте щось
});
```

## Передача

Ви можете передати виконання наступному зіставленому маршруту, повернувши `true` з функції зворотного виклику.

```php
Flight::route('/user/@name', function (string $name) {
  // Перевірте якусь умову
  if ($name !== "Bob") {
    // Продовжіть до наступного маршруту
    return true;
  }
});

Flight::route('/user/*', function () {
  // Це буде викликано
});
```

## Псевдоніми маршрутів

Ви можете призначити псевдонім маршруту, щоб URL можна було динамічно генерувати пізніше у вашому коді (наприклад, у шаблоні).

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');

// пізніше десь у коді
Flight::getUrl('user_view', [ 'id' => 5 ]); // поверне '/users/5'
```

Це особливо корисно, якщо ваш URL зміниться. У прикладі вище, припустимо, що users було перенесено до `/admin/users/@id`.
З псевдонімами на місці, вам не потрібно змінювати будь-де посилання на псевдонім, оскільки псевдонім тепер поверне `/admin/users/5`, як у прикладі вище.

Псевдоніми маршрутів також працюють у групах:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});


// пізніше десь у коді
Flight::getUrl('user_view', [ 'id' => 5 ]); // поверне '/users/5'
```

## Інформація про маршрути

Якщо ви хочете перевірити інформацію про зіставлений маршрут, є 2 способи. Ви можете використовувати властивість `executedRoute` або запитати об'єкт маршруту, щоб він був переданий вашій функції зворотного виклику, передаючи `true` як третій параметр у методі маршруту. Об'єкт маршруту завжди буде останнім параметром, переданим вашій функції зворотного виклику.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Масив HTTP-методів, з якими зіставлено
  $route->methods;

  // Масив іменованих параметрів
  $route->params;

  // Зіставлений регулярний вираз
  $route->regex;

  // Містить вміст будь-якого '*' використаного в шаблоні URL
  $route->splat;

  // Показує шлях URL....якщо вам це дійсно потрібно
  $route->pattern;

  // Показує, який middleware призначено цьому
  $route->middleware;

  // Показує псевдонім, призначений цьому маршруту
  $route->alias;
}, true);
```

Або якщо ви хочете перевірити останній виконаний маршрут, ви можете зробити:

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

  // Містить вміст будь-якого '*' використаного в шаблоні URL
  $route->splat;

  // Показує шлях URL....якщо вам це дійсно потрібно
  $route->pattern;

  // Показує, який middleware призначено цьому
  $route->middleware;

  // Показує псевдонім, призначений цьому маршруту
  $route->alias;
});
```

> **Note:** Властивість `executedRoute` буде встановлена лише після виконання маршруту. Якщо ви спробуєте отримати до неї доступ до виконання маршруту, вона буде `NULL`. Ви також можете використовувати executedRoute у middleware!

## Групування маршрутів

Можливо, виникнуть випадки, коли ви хочете згрупувати пов'язані маршрути разом (наприклад, `/api/v1`). Ви можете зробити це, використовуючи метод `group`:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Це відповідатиме /api/v1/users
  });

  Flight::route('/posts', function () {
	// Це відповідатиме /api/v1/posts
  });
});
```

Ви навіть можете вкладати групи в групи:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() отримує змінні, це не встановлює маршрут! Дивіться контекст об'єкта нижче
	Flight::route('GET /users', function () {
	  // Це відповідатиме GET /api/v1/users
	});

	Flight::post('/posts', function () {
	  // Це відповідатиме POST /api/v1/posts
	});

	Flight::put('/posts/1', function () {
	  // Це відповідатиме PUT /api/v1/posts
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() отримує змінні, це не встановлює маршрут! Дивіться контекст об'єкта нижче
	Flight::route('GET /users', function () {
	  // Це відповідатиме GET /api/v2/users
	});
  });
});
```

### Групування з контекстом об'єкта

Ви все ще можете використовувати групування маршрутів з об'єктом `Engine` наступним чином:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // використовуйте змінну $router
  $router->get('/users', function () {
	// Це відповідатиме GET /api/v1/users
  });

  $router->post('/posts', function () {
	// Це відповідатиме POST /api/v1/posts
  });
});
```

### Групування з Middleware

Ви також можете призначити middleware групі маршрутів:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Це відповідатиме /api/v1/users
  });
}, [ MyAuthMiddleware::class ]); // або [ new MyAuthMiddleware() ], якщо ви хочете використовувати екземпляр
```

Дивіться більше деталей на сторінці [group middleware](/learn/middleware#grouping-middleware).

## Маршрутизація ресурсів

Ви можете створити набір маршрутів для ресурсу, використовуючи метод `resource`. Це створить набір маршрутів для ресурсу, який слідує RESTful-конвенціям.

Щоб створити ресурс, зробіть наступне:

```php
Flight::resource('/users', UsersController::class);
```

І що станеться у фоновому режимі, це створить такі маршрути:

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

> **Note**: Ви можете переглянути newly додані маршрути з `runway`, запустивши `php runway routes`.

### Налаштування маршрутів ресурсів

Є кілька опцій для налаштування маршрутів ресурсів.

#### Базовий псевдонім

Ви можете налаштувати `aliasBase`. За замовчуванням псевдонім — це остання частина вказаного URL.
Наприклад, `/users/` призведе до `aliasBase` як `users`. Коли ці маршрути створюються,
псевдоніми — `users.index`, `users.create` тощо. Якщо ви хочете змінити псевдонім, встановіть `aliasBase`
на значення, яке ви хочете.

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);
```

#### Only і Except

Ви також можете вказати, які маршрути ви хочете створити, використовуючи опції `only` і `except`.

```php
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);
```

```php
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);
```

Це базово опції білого і чорного списків, щоб ви могли вказати, які маршрути ви хочете створити.

#### Middleware

Ви також можете вказати middleware, яке буде виконуватися для кожного з маршрутів, створених методом `resource`.

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);
```

## Стрімінг

Тепер ви можете стрімингувати відповіді клієнту, використовуючи метод `streamWithHeaders()`. 
Це корисно для надсилання великих файлів, тривалих процесів або генерації великих відповідей. 
Стрімінг маршруту обробляється трохи інакше, ніж звичайний маршрут.

> **Note:** Стрімінг відповідей доступний лише якщо у вас [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) встановлено як false.

### Стрімінг з ручними заголовками

Ви можете стрімингувати відповідь клієнту, використовуючи метод `stream()` на маршруті. Якщо ви 
зробите це, ви повинні встановити всі методи вручну перед тим, як вивести щось клієнту.
Це робиться за допомогою функції `header()` php або методу `Flight::response()->setRealHeader()`.

```php
Flight::route('/@filename', function($filename) {

	// очевидно, ви б очистили шлях і таке інше.
	$fileNameSafe = basename($filename);

	// Якщо у вас є додаткові заголовки для встановлення тут після виконання маршруту
	// ви повинні визначити їх перед тим, як щось буде виведено.
	// Вони повинні бути сирим викликом функції header() або 
	// викликом Flight::response()->setRealHeader()
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// або
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$fileNameSafe.'"');

	$fileData = file_get_contents('/some/path/to/files/'.$fileNameSafe);

	// Ловлення помилок і таке інше
	if(empty($fileData)) {
		Flight::halt(404, 'File not found');
	}

	// вручну встановіть довжину вмісту, якщо ви хочете
	header('Content-Length: '.filesize($filename));

	// Стрімінг даних клієнту
	echo $fileData;

// Це магічна лінія тут
})->stream();
```

### Стрімінг з заголовками

Ви також можете використовувати метод `streamWithHeaders()` для встановлення заголовків перед початком стрімінгу.

```php
Flight::route('/stream-users', function() {

	// ви можете додати будь-які додаткові заголовки, які ви хочете тут
	// ви просто повинні використовувати header() або Flight::response()->setRealHeader()

	// однак ви витягуєте свої дані, просто як приклад...
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

// Це як ви встановите заголовки перед початком стрімінгу.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// опційний код статусу, за замовчуванням 200
	'status' => 200
]);
```