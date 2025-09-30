# Расширения панели Tracy для Flight

=====

Это набор расширений, чтобы сделать работу с Flight немного богаче.

- Flight - Анализ всех переменных Flight.
- Database - Анализ всех запросов, выполненных на странице (если вы правильно инициализируете подключение к базе данных)
- Request - Анализ всех переменных `$_SERVER` и осмотр всех глобальных полезных нагрузок (`$_GET`, `$_POST`, `$_FILES`)
- Session - Анализ всех переменных `$_SESSION`, если сессии активны.

Это панель

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

И каждая панель отображает очень полезную информацию о вашем приложении!

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Нажмите [здесь](https://github.com/flightphp/tracy-extensions), чтобы просмотреть код.

Установка
-------
Выполните `composer require flightphp/tracy-extensions --dev`, и вы на пути!

Конфигурация
-------
Вам нужно выполнить очень мало конфигурации, чтобы начать. Вы должны инициализировать отладчик Tracy перед использованием этого [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// bootstrap code
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Возможно, вам нужно указать вашу среду с помощью Debugger::enable(Debugger::DEVELOPMENT)

// если вы используете подключения к базе данных в вашем приложении, есть
// обязательная обертка PDO, которую нужно использовать ТОЛЬКО В РАЗРАБОТКЕ (не в продакшене, пожалуйста!)
// Она имеет те же параметры, что и обычное подключение PDO
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// или если вы подключаете это к фреймворку Flight
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// теперь каждый раз, когда вы выполняете запрос, он захватит время, запрос и параметры

// Это соединяет точки
if(Debugger::$showBar === true) {
	// Это должно быть false, иначе Tracy не сможет отобразиться :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// more code

Flight::start();
```

## Дополнительная конфигурация

### Данные сессии
Если у вас есть пользовательский обработчик сессий (например, ghostff/session), вы можете передать любой массив данных сессии в Tracy, и он автоматически выведет его для вас. Вы передаете его с помощью ключа `session_data` во втором параметре конструктора `TracyExtensionLoader`.

```php

use Ghostff\Session\Session;
// или используйте flight\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// Это должно быть false, иначе Tracy не сможет отобразиться :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// routes and other things...

Flight::start();
```

### Latte

_Требуется PHP 8.1+ для этого раздела._

Если у вас установлен Latte в вашем проекте, Tracy имеет нативную интеграцию с Latte для анализа ваших шаблонов. Вы просто регистрируете расширение с вашим экземпляром Latte.

```php

require 'vendor/autoload.php';

$app = Flight::app();

$app->map('render', function($template, $data, $block = null) {
	$latte = new Latte\Engine;

	// other configurations...

	// добавляйте расширение только если панель отладки Tracy включена
	if(Debugger::$showBar === true) {
		// здесь вы добавляете панель Latte в Tracy
		$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
	}

	$latte->render($template, $data, $block);
});
```