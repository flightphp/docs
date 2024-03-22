# Tracy Расширения Панели для Flight

Это набор расширений, который делает работу с Flight более удобной.

- Flight - Анализ всех переменных Flight.
- Database - Анализ всех запросов, выполненных на странице (если вы правильно инициировали соединение с базой данных)
- Request - Анализ всех переменных `$_SERVER` и изучение всех глобальных данных (`$_GET`, `$_POST`, `$_FILES`)
- Session - Анализ всех переменных `$_SESSION`, если сеансы активны.

Это Панель

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

И каждая панель отображает очень полезную информацию о вашем приложении!

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Установка
-------
Выполните `composer require flightphp/tracy-extensions --dev` и вперед!

Конфигурация
-------
Вам нужно сделать очень мало для запуска этого. Вам нужно инициировать отладчик Tracy перед использованием [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// код инициализации
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Возможно вам потребуется указать ваше окружение с помощью Debugger::enable(Debugger::DEVELOPMENT)

// если вы используете подключения к базе данных в своем приложении, есть 
// обязательная обертка PDO для использования ТОЛЬКО В РАЗРАБОТКЕ (не в продакшене, пожалуйста!)
// Она имеет те же параметры, что и обычное соединение PDO
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// или если присоединить это к Flight framework
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// теперь при выполнении запроса он будет записывать время, запрос и параметры

// Это соединяет точки
if(Debugger::$showBar === true) {
	// Это должно быть false, иначе Tracy не сможет отобразить :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// больше кода

Flight::start();
```
## Дополнительная Конфигурация

### Данные Сеансов
Если у вас есть собственный обработчик сеансов (например, ghostff/session), вы можете передать любой массив данных сеанса в Tracy, и он автоматически выведет его для вас. Вы передаете его с ключом `session_data` во втором параметре конструктора `TracyExtensionLoader`.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// Это должно быть false, иначе Tracy не сможет отобразить :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// маршруты и другие вещи...

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
	// Это должно быть false, иначе Tracy не сможет отобразить :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}
