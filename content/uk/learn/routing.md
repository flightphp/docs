# Маршрутизація

> **Примітка:** Хочете дізнатися більше про маршрутизацію? Перегляньте сторінку ["чому фреймворк?"](/learn/why-frameworks) для більш детального пояснення.

Основна маршрутизація у Flight здійснюється шляхом зіставлення шаблону URL з функцією зворотного виклику або масивом класу та методу.

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

> Маршрути зіставляються в порядку, в якому вони визначені. Перший маршрут, який співпадає з запитом, буде викликаний.

### Функції зворотного виклику
Функція зворотного виклику може бути будь-яким об'єктом, який можна викликати. Тож ви можете використовувати звичайну функцію:

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

Або спочатку створивши об'єкт, а потім викликавши метод:

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
// Ви також можете зробити це без попереднього створення об'єкта
// Примітка: жодних аргументів не буде впроваджено в конструктор
Flight::route('/', [ 'Greeting', 'hello' ]);
// Крім того, ви можете використовувати цей коротший синтаксис
Flight::route('/', 'Greeting->hello');
// або
Flight::route('/', Greeting::class.'->hello');
```

#### Впровадження залежностей через DIC (Контейнер впровадження залежностей)
Якщо ви хочете використовувати впровадження залежностей через контейнер (PSR-11, PHP-DI, Dice тощо), єдиний тип маршрутів, де це доступно, - це або безпосереднє створення об'єкта самостійно та використання контейнера для створення вашого об'єкта, або ви можете використовувати рядки, щоб визначити клас і метод для виклику. Ви можете перейти на сторінку [Впровадження залежностей](/learn/extending) для отримання додаткової інформації.

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
		// зробіть щось з $this->pdoWrapper
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "Hello, world! My name is {$name}!";
	}
}

// index.php

// Налаштуйте контейнер з будь-якими параметрами, які вам потрібні
// Дивіться сторінку Впровадження залежностей для отримання додаткової інформації про PSR-11
$dice = new \Dice\Dice();

// Не забудьте перезаписати змінну з '$dice = '!!!!!
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

## Маршрутизація за методом

За замовчуванням шаблони маршрутів зіставляються з усіма методами запиту. Ви можете реагувати на конкретні методи, розмістивши ідентифікатор перед URL.

```php
Flight::route('GET /', function () {
  echo 'Я отримав GET запит.';
});

Flight::route('POST /', function () {
  echo 'Я отримав POST запит.';
});

// Ви не можете використовувати Flight::get() для маршрутів, оскільки це метод 
//    для отримання змінних, а не створення маршруту.
// Flight::post('/', function() { /* код */ });
// Flight::patch('/', function() { /* код */ });
// Flight::put('/', function() { /* код */ });
// Flight::delete('/', function() { /* код */ });
```

Ви також можете відобразити кілька методів на один зворотний виклик, використовуючи роздільник `|`:

```php
Flight::route('GET|POST /', function () {
  echo 'Я отримав або GET, або POST запит.';
});
```

Крім того, ви можете отримати об'єкт Router, який має кілька допоміжних методів для вас:

```php

$router = Flight::router();

// відображає всі методи
$router->map('/', function() {
	echo 'hello world!';
});

// GET запит
$router->get('/users', function() {
	echo 'користувачі';
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
  // Це буде відповідати /user/1234
});
```

Хоча цей метод доступний, рекомендується використовувати іменовані параметри або 
іменовані параметри з регулярними виразами, оскільки вони більш читабельні та легкі для обслуговування.

## Іменовані параметри

Ви можете вказати іменовані параметри у ваших маршрутах, які будуть передані вашій функції зворотного виклику. **Це більше для читабельності маршруту, ніж для чогось іншого. Будь ласка, перегляньте розділ нижче про важливу застереження.**

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

> **Примітка:** Співпадіння груп регекс `()` з позиційними параметрами не підтримується. :'\(

### Важлива застереження

Хоча у наведеному прикладі здається, що `@name` безпосередньо пов'язаний зі змінною `$name`, це не так. Порядок параметрів у функції зворотного виклику визначає, що їй передається. Тож якщо ви зміните порядок параметрів у функції зворотного виклику, змінні також будуть змінені. Ось приклад:

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "hello, $name ($id)!";
});
```

І якщо ви перейдете за наступною URL: `/bob/123`, вивід буде `hello, 123 (bob)!`. 
Будь ласка, будьте обережні, коли ви налаштовуєте свої маршрути та функції зворотного виклику.

## Необов'язкові параметри

Ви можете вказати іменовані параметри, які є необов'язковими для співпадіння, обертаючи сегменти в дужки.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // Це буде відповідати наступним URL:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

Будь-які необов'язкові параметри, які не були співпадіння, будуть передані як `NULL`.

## Вайлдкарди

Співпадіння здійснюється лише на окремих сегментах URL. Якщо вам потрібно співпадати з кількома сегментами, ви можете використовувати вайлдкард `*`.

```php
Flight::route('/blog/*', function () {
  // Це буде відповідати /blog/2000/02/01
});
```

Щоб направити всі запити до одного зворотного виклику, ви можете зробити так:

```php
Flight::route('*', function () {
  // Зробіть щось
});
```

## Передача

Ви можете передати виконання наступному співпадаючому маршруту, повернувши `true` з вашої функції зворотного виклику.

```php
Flight::route('/user/@name', function (string $name) {
  // Перевірте деяку умову
  if ($name !== "Bob") {
    // Продовжити до наступного маршруту
    return true;
  }
});

Flight::route('/user/*', function () {
  // Це буде викликано
});
```

## Псевдонім маршруту

Ви можете призначити псевдонім для маршруту, щоб URL можна було динамічно створити пізніше у вашому коді (наприклад, у шаблоні).

```php
Flight::route('/users/@id', function($id) { echo 'користувач:'.$id; }, false, 'user_view');

// пізніше в коді десь
Flight::getUrl('user_view', [ 'id' => 5 ]); // поверне '/users/5'
```

Це особливо корисно, якщо ваш URL змінюється. У наведеному прикладі, скажімо, що користувачів було переміщено на `/admin/users/@id`.
З псевдонімами вам не потрібно змінювати всюди, де ви посилаєтеся на псевдонім, оскільки він тепер поверне `/admin/users/5`, як у 
наведеному прикладі.

Псевдоніми маршруту також працюють у групах:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'користувач:'.$id; }, false, 'user_view');
});

// пізніше в коді десь
Flight::getUrl('user_view', [ 'id' => 5 ]); // поверне '/users/5'
```

## Інформація про маршрути

Якщо ви хочете перевірити інформацію про відповідний маршрут, ви можете запросити, щоб об'єкт маршруту був переданий вашій функції зворотного виклику, передавши `true` як третій параметр у методі маршруту. Об'єкт маршруту завжди буде останнім параметром, переданим вашій функції зворотного виклику.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Масив методів HTTP, з якими зіставлено
  $route->methods;

  // Масив іменованих параметрів
  $route->params;

  // Відповідний регулярний вираз
  $route->regex;

  // Містить вміст будь-якого '*' використаного у шаблоні URL
  $route->splat;

  // Показує шлях URL.... якщо вам це дійсно потрібно
  $route->pattern;

  // Показує, яке програмне забезпечення призначено цьому
  $route->middleware;

  // Показує псевдонім, призначений для цього маршруту
  $route->alias;
}, true);
```

## Групування маршрутів

Існують часи, коли вам потрібно згрупувати пов'язані маршрути разом (такі як `/api/v1`).
Ви можете зробити це, використовуючи метод `group`:

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

Ви навіть можете вкладати групи груп:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() отримує змінні, не налаштовує маршрут! Див. контекст об'єкта нижче
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

	// Flight::get() отримує змінні, не налаштовує маршрут! Див. контекст об'єкта нижче
	Flight::route('GET /users', function () {
	  // Відповідає GET /api/v2/users
	});
  });
});
```

### Групування з контекстом об'єкта

Ви все ще можете використовувати групування маршрутів з об'єктом `Engine` наступним чином:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // використання змінної $router
  $router->get('/users', function () {
	// Відповідає GET /api/v1/users
  });

  $router->post('/posts', function () {
	// Відповідає POST /api/v1/posts
  });
});
```

## Ресурсна маршрутизація

Ви можете створити набір маршрутів для ресурсу, використовуючи метод `resource`. Це створить
набір маршрутів для ресурсу, що дотримується RESTful конвенцій.

Щоб створити ресурс, виконайте наступні дії:

```php
Flight::resource('/users', UsersController::class);
```

І те, що відбудеться за лаштунками, це створить наступні маршрути:

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

А ваш контролер виглядатиме так:

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

> **Примітка**: Ви можете переглянути нові додані маршрути з `runway`, запустивши `php runway routes`.

### Налаштування маршрутів ресурсів

Існує кілька варіантів для налаштування маршрутів ресурсів.

#### Псевдонім бази

Ви можете налаштувати `aliasBase`. За замовчуванням псевдонім - це остання частина вказаного URL.
Наприклад, `/users/` призведе до `aliasBase` як `users`. Коли ці маршрути створюються,
псевдоніми є `users.index`, `users.create` тощо. Якщо ви хочете змінити псевдонім, задайте `aliasBase`
на бажане значення.

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);
```

#### Тільки і Винятки

Ви також можете вказати, які маршрути ви хочете створити, використовуючи параметри `only` та `except`.

```php
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);
```

```php
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);
```

Це в основному варіанти білого списку та чорного списку, щоб ви могли вказати, які маршрути ви хочете створити.

#### Програмне забезпечення

Ви також можете вказати програмне забезпечення, яке запускається на кожному з маршрутів, створених за допомогою методу `resource`.

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);
```

## Стрімінг

Тепер ви можете стрімити відповіді клієнту, використовуючи метод `streamWithHeaders()`. 
Це корисно для відправки великих файлів, довготривалих процесів або генерації великих відповідей. 
Стрімінг маршруту обробляється трохи інакше, ніж звичайний маршрут.

> **Примітка:** Відповіді стрімінгу доступні тільки якщо ви маєте [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) встановлений на false.

### Стрім з ручними заголовками

Ви можете стрімити відповідь клієнту, використовуючи метод `stream()` на маршруті. Якщо ви 
так робите, ви повинні налаштувати всі методи вручну перед тим, як вивести що-небудь клієнту.
Це робиться за допомогою функції `header()` php або методу `Flight::response()->setRealHeader()`.

```php
Flight::route('/@filename', function($filename) {

	// звісно, вам потрібно буде очистити шлях та інше.
	$fileNameSafe = basename($filename);

	// Якщо у вас є додаткові заголовки, які потрібно налаштувати тут після виконання маршруту,
	// ви повинні визначити їх перед тим, як щось виводити.
	// Вони повинні бути всі необробленим зверненням до функції header() або 
	// зверненням до Flight::response()->setRealHeader()
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// або
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$fileNameSafe.'"');

	$fileData = file_get_contents('/some/path/to/files/'.$fileNameSafe);

	// Обробка помилок та інше
	if(empty($fileData)) {
		Flight::halt(404, 'Файл не знайдено');
	}

	// вручну встановіть довжину вмісту, якщо хочете
	header('Content-Length: '.filesize($filename));

	// Стрімте дані до клієнта
	echo $fileData;

// Це чарівний рядок тут
})->stream();
```

### Стрім з заголовками

Ви також можете використовувати метод `streamWithHeaders()`, щоб встановити заголовки перед початком стрімінгу.

```php
Flight::route('/stream-users', function() {

	// ви можете додати будь-які додаткові заголовки, які хочете тут
	// просто потрібно використовувати header() або Flight::response()->setRealHeader()

	// однак, як би ви не отримували свої дані, просто для прикладу...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// Це обов'язково для відправки даних до клієнта
		ob_flush();
	}
	echo '}';

// Ось як ви встановите заголовки перед початком стрімінгу.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// необов'язковий статус-код, за замовчуванням 200
	'status' => 200
]);
```