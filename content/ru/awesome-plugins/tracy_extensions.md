Tracy Панель расширений
=====

Это набор расширений, который делает работу с Flight немного более насыщенной.

- Flight - Анализ всех переменных Flight.
- Database - Анализ всех запросов, выполненных на странице (если вы правильно инициировали соединение с базой данных)
- Request - Анализ всех переменных `$_SERVER` и изучение всех глобальных данных (`$_GET`, `$_POST`, `$_FILES`)
- Session - Анализ всех переменных `$_SESSION`, если сеансы активны.

Это Панель

![Панель Flight](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

И каждая панель отображает очень полезную информацию о вашем приложении!

![Данные Flight](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![База данных Flight](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Запрос Flight](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Установка
-------
Запустите `composer require flightphp/tracy-extensions --dev` и вперед!

Настройка
-------
Вам нужно сделать очень мало настроек, чтобы начать работу. Вам нужно инициировать отладчик Tracy перед использованием этого [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// код запуска
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Вам может потребоваться указать свою среду с помощью Debugger::enable(Debugger::DEVELOPMENT)

// если вы используете соединения с базой данных в своем приложении, существует
// необходимая обертка PDO для использования ТОЛЬКО В РАЗРАБОТКЕ (пожалуйста, не используйте в продакшене!)
// Она имеет те же параметры, что и обычное соединение PDO
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// или если вы присоединяете это к Flight framework
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// теперь при каждом запросе будет захвачено время, запрос и параметры

// Это соединяет точки
if(Debugger::$showBar === true) {
	// Это должно быть false, иначе Tracy не сможет отобразиться :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// ещё код

Flight::start();
```

## Дополнительная конфигурация

### Данные сеанса
Если у вас есть пользовательский обработчик сеанса (например, ghostff/session), вы можете передать любой массив данных сеанса Tracy и он автоматически их выведет для вас. Вы передаете его с ключом `session_data` во второй параметр конструктора `TracyExtensionLoader`.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// Это должно быть false, иначе Tracy не сможет отобразиться :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// маршруты и прочее...

Flight::start();
```

### Latte

Если у вас установлен Latte в вашем проекте, вы можете использовать панель Latte для анализа ваших шаблонов. Вы можете передать экземпляр Latte в конструктор `TracyExtensionLoader` с ключом `latte` во втором параметре.

```php

use Latte\Engine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', Engine::class, [], function($latte) {
	$latte->setTempDirectory(__DIR__ . '/temp');

	// здесь вы добавляете Панель Latte к Tracy
	$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
});

if(Debugger::$showBar === true) {
	// Это должно быть false, иначе Tracy не сможет отобразиться :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}
