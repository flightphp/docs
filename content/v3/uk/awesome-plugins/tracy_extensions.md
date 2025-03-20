Tracy Flight Panel Extensions
=====

Це набір розширень для покращення роботи з Flight.

- Flight - Аналізувати всі змінні Flight.
- Database - Аналізувати всі запити, які виконувалися на сторінці (якщо ви правильно ініціювали підключення до бази даних)
- Request - Аналізувати всі змінні `$_SERVER` та перевіряти всі глобальні дані (`$_GET`, `$_POST`, `$_FILES`)
- Session - Аналізувати всі змінні `$_SESSION`, якщо сесії активні.

Це панель

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

І кожна панель відображає дуже корисну інформацію про ваш додаток!

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Натисніть [тут](https://github.com/flightphp/tracy-extensions), щоб переглянути код.

Встановлення
-------
Запустіть `composer require flightphp/tracy-extensions --dev`, і ви на правильному шляху!

Налаштування
-------
Існує дуже мало налаштувань, які потрібно зробити, щоб це запрацювало. Вам потрібно ініціювати відлагоджувач Tracy перед використанням цього [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// код для старту
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Можливо, вам потрібно вказати своє середовище з Debugger::enable(Debugger::DEVELOPMENT)

// якщо ви використовуєте підключення до бази даних у вашому додатку, є 
// необхідна обгортка PDO, яку потрібно використовувати ТІЛЬКИ В РОЗРОБЦІ (не в продакшені, будь ласка!)
// Вона має такі ж параметри, як і звичайне підключення PDO
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// або якщо ви приєднаєте це до фреймворку Flight
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// тепер щоразу, коли ви робите запит, він буде захоплювати час, запит і параметри

// Це з'єднує всі елементи
if(Debugger::$showBar === true) {
	// Це має бути false, інакше Tracy не зможе його відобразити :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// більше коду

Flight::start();
```

## Додаткове налаштування

### Дані сесії
Якщо у вас є власний обробник сесій (такий як ghostff/session), ви можете передати будь-який масив даних сесії до Tracy, і вона автоматично виведе його для вас. Ви передаєте це з ключем `session_data` у другому параметрі конструктора `TracyExtensionLoader`.

```php

use Ghostff\Session\Session;
// або використовуйте flight\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// Це має бути false, інакше Tracy не зможе його відобразити :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// маршрути та інші речі...

Flight::start();
```

### Latte

Якщо у вас є Latte, встановлений у вашому проекті, ви можете використовувати панель Latte для аналізу ваших шаблонів. Ви можете передати екземпляр Latte до конструктора `TracyExtensionLoader` з ключем `latte` у другому параметрі.

```php

use Latte\Engine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', Engine::class, [], function($latte) {
	$latte->setTempDirectory(__DIR__ . '/temp');

	// це місце, де ви додаєте панель Latte до Tracy
	$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
});

if(Debugger::$showBar === true) {
	// Це має бути false, інакше Tracy не зможе його відобразити :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}
```