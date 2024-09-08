# Маршрутизація

> **Примітка:** Хочете дізнатися більше про маршрутизацію? Перегляньте сторінку ["чому фреймворк?"](/learn/why-frameworks) для більш детального пояснення.

Основна маршрутизація в Flight відбувається шляхом зіставлення шаблону URL з функцією зворотного виклику або масивом класу та методу.

```php
Flight::route('/', function(){
    echo 'привіт світ!';
});
```

> Маршрути зіставляються в порядку, в якому вони визначені. Перший маршрут, який відповідає запиту, буде викликаний.

### Функції/Колбеки
В якості колбеку можна використовувати будь-який об'єкт, який можна викликати. Тож ви можете використовувати звичайну функцію:

```php
function hello() {
    echo 'привіт світ!';
}

Flight::route('/', 'hello');
```

### Класи
Ви також можете використовувати статичний метод класу:

```php
class Greeting {
    public static function hello() {
        echo 'привіт світ!';
    }
}

Flight::route('/', [ 'Greeting', 'hello' ]);
```

Або, створивши об'єкт спочатку, а потім викликавши метод:

```php

// Greeting.php
class Greeting
{
    public function __construct() {
        $this->name = 'Джон Доу';
    }

    public function hello() {
        echo "Привіт, {$this->name}!";
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', [ $greeting, 'hello' ]);
// Ви також можете зробити це без попереднього створення об'єкта
// Примітка: Аргументи не будуть впроваджені в конструктор
Flight::route('/', [ 'Greeting', 'hello' ]);
// Крім того, ви можете використовувати цей коротший синтаксис
Flight::route('/', 'Greeting->hello');
// або
Flight::route('/', Greeting::class.'->hello');
```

#### Впровадження залежностей через DIC (Контейнер для впровадження залежностей)
Якщо ви хочете використовувати впровадження залежностей через контейнер (PSR-11, PHP-DI, Dice тощо), єдиний тип маршрутів, де це доступно, - це або безпосереднє створення об'єкта самостійно та використання контейнера для створення вашого об'єкта, або ви можете використовувати рядки для визначення класу та методу для виклику. Ви можете перейти на сторінку [Впровадження залежностей](/learn/extending) для отримання додаткової інформації.

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
		echo "Привіт, світ! Моє ім'я {$name}!";
	}
}

// index.php

// Налаштуйте контейнер з будь-якими параметрами, які вам потрібні
// Див. сторінку Впровадження залежностей для отримання додаткової інформації про PSR-11
$dice = new \Dice\Dice();

// Не забудьте переназначити змінну '$dice = '!!!!!
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

// Маршрути, як зазвичай
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// або
Flight::route('/hello/@id', 'Greeting->hello');
// або
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

## Метод маршрутизації

За замовчуванням шаблони маршрутів порівнюються з усіма методами запиту. Ви можете реагувати 
на конкретні методи, поставивши ідентифікатор перед URL.

```php
Flight::route('GET /', function () {
  echo 'Я отримав GET запит.';
});

Flight::route('POST /', function () {
  echo 'Я отримав POST запит.';
});

// Ви не можете використовувати Flight::get() для маршрутів, оскільки це метод 
//    для отримання змінних, а не для створення маршруту.
// Flight::post('/', function() { /* код */ });
// Flight::patch('/', function() { /* код */ });
// Flight::put('/', function() { /* код */ });
// Flight::delete('/', function() { /* код */ });
```

Ви також можете мапувати кілька методів на один колбек, використовуючи роздільник `|`:

```php
Flight::route('GET|POST /', function () {
  echo 'Я отримав або GET, або POST запит.';
});
```

Крім того, ви можете отримати об’єкт Router, в якому є допоміжні методи для використання:

```php

$router = Flight::router();

// мапує всі методи
$router->map('/', function() {
	echo 'привіт світ!';
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

Ви можете використовувати регулярні вирази у ваших маршрутах:

```php
Flight::route('/user/[0-9]+', function () {
  // Це буде відповідати /user/1234
});
```

Хоча цей метод доступний, рекомендується використовувати названі параметри або 
названі параметри з регулярними виразами, оскільки вони більш читабельні та прості у підтримці.

## Названі параметри

Ви можете вказати названі параметри у ваших маршрутах, які будуть передані 
вашій функції зворотного виклику.

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "привіт, $name ($id)!";
});
```

Ви також можете включити регулярні вирази з вашими названими параметрами, використовуючи 
роздільник `:`:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // Це буде відповідати /bob/123
  // Але не відповідатиме /bob/12345
});
```

> **Примітка:** Із групами відповідності regex `()` з названими параметрами не підтримується. :'\(

## Необов'язкові параметри

Ви можете вказати названі параметри, які є необов'язковими для відповідності, обгортаючи 
сегменти в дужки.

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

Будь-які необов'язкові параметри, які не були відповідні, будуть передані як `NULL`.

## Дикий символ

Відповідність відбувається лише на поодиноких сегментах URL. Якщо ви хочете відповідати кількома 
сегментами, ви можете використовувати дикий символ `*`.

```php
Flight::route('/blog/*', function () {
  // Це буде відповідати /blog/2000/02/01
});
```

Щоб маршрутизувати всі запити до одного колбеку, ви можете зробити так:

```php
Flight::route('*', function () {
  // Виконати щось
});
```

## Передача

Ви можете передати виконання на наступний відповідний маршрут, повернувши `true` з 
вашої функції зворотного виклику.

```php
Flight::route('/user/@name', function (string $name) {
  // Перевірте якусь умову
  if ($name !== "Боб") {
    // Продовжте до наступного маршруту
    return true;
  }
});

Flight::route('/user/*', function () {
  // Це буде викликано
});
```

## Псевдоніми маршруту

Ви можете присвоїти псевдонім маршруту, щоб URL можна було динамічно генерувати пізніше у вашому коді (наприклад, як шаблон).

```php
Flight::route('/users/@id', function($id) { echo 'користувач:'.$id; }, false, 'user_view');

// пізніше в коді десь
Flight::getUrl('user_view', [ 'id' => 5 ]); // поверне '/users/5'
```

Це особливо корисно, якщо ваш URL випадково змінюється. У наведеному вище прикладі, скажімо, що користувачі були переміщені до `/admin/users/@id` замість цього. 
З псевдонімами ви не повинні змінювати місце, де ви посилаєтеся на псевдонім, оскільки псевдонім тепер поверне `/admin/users/5`, як у 
вищезгаданому прикладі.

Псевдонім маршруту також працює в групах:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'користувач:'.$id; }, false, 'user_view');
});

// пізніше в коді десь
Flight::getUrl('user_view', [ 'id' => 5 ]); // поверне '/users/5'
```

## Інформація про маршрут

Якщо ви хочете перевірити інформацію про відповідний маршрут, ви можете запросити 
об'єкт маршруту, щоб передати його вашій функції зворотного виклику, передавши `true` як третій параметр у 
методі маршруту. Об'єкт маршруту завжди буде останнім параметром, переданим вашій 
функції зворотного виклику.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Масив методів HTTP, що відповідають
  $route->methods;

  // Масив названих параметрів
  $route->params;

  // Відповідний регулярний вираз
  $route->regex;

  // Містить вміст будь-якого '*' використаного в шаблоні URL
  $route->splat;

  // Показує шлях URL.... якщо вам це дійсно потрібно
  $route->pattern;

  // Показує, яке середовище призначено цьому
  $route->middleware;

  // Показує псевдонім, призначений цьому маршруту
  $route->alias;
}, true);
```

## Групування маршруту

Можуть бути ситуації, коли ви хочете згрупувати пов'язані маршрути (як-от `/api/v1`).
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

Ви навіть можете вкладати групи груп.

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() отримує змінні, він не встановлює маршрут! Див. контекст об'єкта нижче
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

	// Flight::get() отримує змінні, він не встановлює маршрут! Див. контекст об'єкта нижче
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

  // використовуйте змінну $router
  $router->get('/users', function () {
	// Відповідає GET /api/v1/users
  });

  $router->post('/posts', function () {
	// Відповідає POST /api/v1/posts
  });
});
```

## Потокова передача

Тепер ви можете передавати відповіді клієнту, використовуючи метод `streamWithHeaders()`. 
Це корисно для відправлення великих файлів, тривалих процесів або генерації великих відповідей. 
Потокова передача маршруту обробляється трохи інакше, ніж звичайний маршрут.

> **Примітка:** Потокові відповіді доступні лише в тому випадку, якщо ви маєте [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) встановлений на false.

### Потокова передача з ручними заголовками

Ви можете потоково передавати відповідь клієнту, використовуючи метод `stream()` на маршруті. Якщо ви 
це робите, ви повинні вручну встановити всі методи, перш ніж щось виводити клієнту.
Це робиться за допомогою функції php `header()` або методу `Flight::response()->setRealHeader()`.

```php
Flight::route('/@filename', function($filename) {

	// звісно, вам необхідно очистити шлях і т.д.
	$fileNameSafe = basename($filename);

	// Якщо у вас є додаткові заголовки для встановлення тут після виконання маршруту
	// ви повинні визначити їх перед будь-яким виводом.
	// Вони повинні бути всі сировинним викликом функції header() або 
	// викликом Flight::response()->setRealHeader()
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// або
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$fileNameSafe.'"');

	$fileData = file_get_contents('/some/path/to/files/'.$fileNameSafe);

	// Обробка помилок і т.д.
	if(empty($fileData)) {
		Flight::halt(404, 'Файл не знайдено');
	}

	// вручну встановлюйте довжину вмісту, якщо хочете
	header('Content-Length: '.filesize($filename));

	// Потоково передайте дані клієнту
	echo $fileData;

// Це магічний рядок тут
})->stream();
```

### Потокова передача з заголовками

Ви також можете використовувати метод `streamWithHeaders()`, щоб встановити заголовки перед початком потокової передачі.

```php
Flight::route('/stream-users', function() {

	// ви можете додати будь-які додаткові заголовки, які хочете
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

		// Це необхідно для відправки даних клієнту
		ob_flush();
	}
	echo '}';

// Це так ви встановите заголовки перед початком потокової передачі.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// необов'язковий статус-код, за замовчуванням 200
	'status' => 200
]);
```