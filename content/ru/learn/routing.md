# Маршрутизация

> **Примечание:** Хотите узнать больше о маршрутизации? Ознакомьтесь с страницей ["почему фреймворк?"](/learn/why-frameworks) для более подробного объяснения.

Базовая маршрутизация в Flight осуществляется путем сопоставления шаблона URL с функцией обратного вызова или массивом класса и метода.

```php
Flight::route('/', function() {
    echo 'привет, мир!';
});
```

> Маршруты сопоставляются в порядке их определения. Первый сопоставленный маршрут будет вызван.

### Обратные вызовы/Функции
Функцией обратного вызова может быть любой объект, который можно вызвать. Так что вы можете использовать обычную функцию:

```php
function hello(){
    echo 'привет, мир!';
}

Flight::route('/', 'hello');
```

### Классы
Вы также можете использовать статический метод класса:

```php
class Greeting {
    public static function hello() {
        echo 'привет, мир!';
    }
}

Flight::route('/', [ 'Greeting','hello' ]);
```

Или создать объект сначала, а затем вызвать метод:

```php

// Greeting.php
class Greeting
{
    public function __construct() {
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "Привет, {$this->name}!";
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', [ $greeting, 'hello' ]);
// Также можно сделать это без создания объекта сначала
// Примечание: не будут переданы аргументы в конструктор
Flight::route('/', [ 'Greeting', 'hello' ]);
```

#### Внедрение зависимостей через DIC (контейнер внедрения зависимости)
Если вы хотите использовать внедрение зависимостей через контейнер (PSR-11, PHP-DI, Dice и т. д.), единственный тип маршрутов, где это возможно, это либо прямое создание объекта самостоятельно и использование контейнера для создания вашего объекта, либо вы можете использовать строки для определения класса и метода для вызова. Вы можете перейти на страницу [Внедрение зависимостей](/learn/extending) для получения дополнительной информации.

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
		// что-то делаем с $this->pdoWrapper
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "Привет, мир! Меня зовут {$name}!";
	}
}

// index.php

// Настройте контейнер с необходимыми параметрами
// Смотрите страницу Внедрение зависимостей для получения дополнительной информации о PSR-11
$dice = new \Dice\Dice();

// Не забудьте переопределить переменную с '$dice = '!!!!!
$dice = $dice->addRule('flight\database\PdoWrapper', [
	'shared' => true,
	'constructParams' => [ 
		'mysql:host=localhost;dbname=test', 
		'root',
		'password'
	]
]);

// Регистрация обработчика контейнера
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

## Маршрутизация методом

По умолчанию, шаблоны маршрутов сопоставляются со всеми методами запросов. Вы можете отвечать на конкретные методы, размещая идентификатор перед URL.

```php
Flight::route('GET /', function () {
  echo 'Я получил запрос метода GET.';
});

Flight::route('POST /', function () {
  echo 'Я получил запрос метода POST.';
});

// Невозможно использовать Flight::get() для маршрутов, поскольку это метод
//      для получения переменных, а не для создания маршрута.
// Flight::post('/', function() { /* код */ });
// Flight::patch('/', function() { /* код */ });
// Flight::put('/', function() { /* код */ });
// Flight::delete('/', function() { /* код */ });
```

Вы также можете отобразить несколько методов на один обратный вызов, используя разделитель `|`:

```php
Flight::route('GET|POST /', function () {
  echo 'Я получил запрос метода GET или POST.';
});
```

Кроме того, вы можете получить объект Router, который предлагает несколько вспомогательных методов для использования:

```php

$router = Flight::router();

// соотносит все методы
$router->map('/', function() {
	echo 'привет, мир!';
});

// Запрос GET
$router->get('/users', function() {
	echo 'пользователи';
});
// $router->post();
// $router->put();
// $router->delete();
// $router->patch();
```