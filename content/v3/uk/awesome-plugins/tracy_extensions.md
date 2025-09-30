Розширення панелі Tracy Flight
=====

Це набір розширень, які роблять роботу з Flight трохи багатшою.

- Flight - Аналізує всі змінні Flight.
- Database - Аналізує всі запити, які виконувалися на сторінці (якщо ви правильно ініціалізували з'єднання з базою даних)
- Request - Аналізує всі змінні `$_SERVER` та перевіряє всі глобальні навантаження (`$_GET`, `$_POST`, `$_FILES`)
- Session - Аналізує всі змінні `$_SESSION`, якщо сесії активні.

Це панель

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

І кожна панель відображає дуже корисну інформацію про ваш додаток!

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Клікніть [тут](https://github.com/flightphp/tracy-extensions), щоб переглянути код.

Встановлення
-------
Виконайте `composer require flightphp/tracy-extensions --dev`, і ви готові!

Конфігурація
-------
Потрібно виконати дуже мало налаштувань, щоб запустити це. Вам потрібно ініціалізувати налагоджувач Tracy перед використанням [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// bootstrap code
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// You may need to specify your environment with Debugger::enable(Debugger::DEVELOPMENT)

// if you use database connections in your app, there is a 
// required PDO wrapper to use ONLY IN DEVELOPMENT (not production please!)
// It has the same parameters as a regular PDO connection
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// or if you attach this to the Flight framework
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// now whenever you make a query it will capture the time, query, and parameters

// This connects the dots
if(Debugger::$showBar === true) {
	// This needs to be false or Tracy can't actually render :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// more code

Flight::start();
```

## Додаткова конфігурація

### Дані сесії
Якщо у вас є власний обробник сесій (наприклад, ghostff/session), ви можете передати будь-який масив даних сесії до Tracy, і він автоматично виведе його для вас. Ви передаєте його за допомогою ключа `session_data` у другому параметрі конструктора `TracyExtensionLoader`.

```php

use Ghostff\Session\Session;
// or use flight\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// This needs to be false or Tracy can't actually render :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// routes and other things...

Flight::start();
```

### Latte

_Для цього розділу потрібен PHP 8.1+._

Якщо у вашому проекті встановлено Latte, Tracy має нативну інтеграцію з Latte для аналізу ваших шаблонів. Ви просто реєструєте розширення з вашим екземпляром Latte.

```php

require 'vendor/autoload.php';

$app = Flight::app();

$app->map('render', function($template, $data, $block = null) {
	$latte = new Latte\Engine;

	// other configurations...

	// only add the extension if Tracy Debug Bar is enabled
	if(Debugger::$showBar === true) {
		// this is where you add the Latte Panel to Tracy
		$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
	}

	$latte->render($template, $data, $block);
});
```