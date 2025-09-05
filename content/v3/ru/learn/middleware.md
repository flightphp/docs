# Маршрут Middleware

Flight поддерживает middleware для маршрутов и групп маршрутов. Middleware — это функция, которая выполняется перед (или после) обратным вызовом маршрута. Это отличный способ добавить проверки аутентификации API в ваш код или убедиться, что пользователь имеет разрешение на доступ к маршруту.

## Основное Middleware

Вот базовый пример:

```php
// Если вы предоставляете только анонимную функцию, она будет выполнена перед обратным вызовом маршрута.
// Здесь нет функций middleware "after", кроме классов (см. ниже)
Flight::route('/path', function() { echo ' Here I am!'; })->addMiddleware(function() {
	echo 'Middleware first!';
});

Flight::start();

// Это выведет "Middleware first! Here I am!"
```

Есть несколько очень важных замечаний о middleware, которые вы должны знать перед использованием:
- Функции middleware выполняются в порядке их добавления к маршруту. Выполнение аналогично тому, как [Slim Framework обрабатывает это](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - Befores выполняются в порядке добавления, а Afters — в обратном порядке.
- Если ваша функция middleware возвращает false, все выполнение останавливается и генерируется ошибка 403 Forbidden. Вероятно, вы захотите обработать это более изящно с помощью `Flight::redirect()` или подобного.
- Если вам нужны параметры из вашего маршрута, они будут переданы в виде единого массива вашей функции middleware. (`function($params) { ... }` или `public function before($params) {}`). Причина в том, что вы можете структурировать свои параметры в группы, и в некоторых из этих групп параметры могут появляться в другом порядке, что сломает функцию middleware из-за ссылки на неправильный параметр. Таким образом, вы можете обращаться к ним по имени, а не по позиции.
- Если вы передаете только имя middleware, оно автоматически будет выполнено с помощью [контейнера внедрения зависимостей](dependency-injection-container), и middleware будет выполнено с необходимыми параметрами. Если контейнер внедрения зависимостей не зарегистрирован, будет передан экземпляр `flight\Engine` в `__construct()`.

## Классы Middleware

Middleware также можно зарегистрировать как класс. Если вам нужна функциональность "after", вы **должны** использовать класс.

```php
class MyMiddleware {
	public function before($params) {
		echo 'Middleware first!';
	}

	public function after($params) {
		echo 'Middleware last!';
	}
}

$MyMiddleware = new MyMiddleware();
Flight::route('/path', function() { echo ' Here I am! '; })->addMiddleware($MyMiddleware); // также ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// Это отобразит "Middleware first! Here I am! Middleware last!"
```

## Обработка ошибок Middleware

Предположим, у вас есть middleware для аутентификации, и вы хотите перенаправить пользователя на страницу входа, если он не аутентифицирован. У вас есть несколько вариантов:

1. Вы можете вернуть false из функции middleware, и Flight автоматически вернет ошибку 403 Forbidden, но без настройки.
1. Вы можете перенаправить пользователя на страницу входа с помощью `Flight::redirect()`.
1. Вы можете создать пользовательскую ошибку внутри middleware и остановить выполнение маршрута.

### Базовый пример

Вот простой пример с return false:
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			return false;
		}

		// поскольку это true, всё просто продолжается
	}
}
```

### Пример перенаправления

Вот пример перенаправления пользователя на страницу входа:
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			Flight::redirect('/login');
			exit;
		}
	}
}
```

### Пример пользовательской ошибки

Предположим, вам нужно сгенерировать ошибку в формате JSON, потому что вы строите API. Вы можете сделать это так:
```php
class MyMiddleware {
	public function before($params) {
		$authorization = Flight::request()->headers['Authorization'];
		if(empty($authorization)) {
			Flight::jsonHalt(['error' => 'You must be logged in to access this page.'], 403);
			// или
			Flight::json(['error' => 'You must be logged in to access this page.'], 403);
			exit;
			// или
			Flight::halt(403, json_encode(['error' => 'You must be logged in to access this page.']);
		}
	}
}
```

## Группировка Middleware

Вы можете добавить группу маршрутов, и каждый маршрут в этой группе будет иметь одно и то же middleware. Это полезно, если вам нужно сгруппировать несколько маршрутов, например, с middleware Auth для проверки API-ключа в заголовке.

```php

// добавлено в конце метода group
Flight::group('/api', function() {

	// Этот "пустой" маршрут фактически совпадает с /api
	Flight::route('', function() { echo 'api'; }, false, 'api');
	// Это совпадает с /api/users
    Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// Это совпадает с /api/users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```

Если вы хотите применить глобальное middleware ко всем вашим маршрутам, вы можете добавить "пустую" группу:

```php

// добавлено в конце метода group
Flight::group('', function() {

	// Это всё ещё /users
	Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// А это всё ещё /users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ ApiAuthMiddleware::class ]); // или [ new ApiAuthMiddleware() ], то же самое
```