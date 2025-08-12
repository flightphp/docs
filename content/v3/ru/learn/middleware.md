# Маршрутное промежуточное ПО

Flight поддерживает промежуточное ПО для маршрутов и групп маршрутов. Промежуточное ПО — это функция, которая выполняется перед (или после) обратного вызова маршрута. Это отличный способ добавить проверки аутентификации API в коде или убедиться, что пользователь имеет разрешение на доступ к маршруту.

## Основное промежуточное ПО

Вот базальный пример:

```php
// Если вы предоставляете только анонимную функцию, она будет выполнена перед обратным вызовом маршрута.
// нет функций промежуточного ПО "after", кроме классов (см. ниже)
Flight::route('/path', function() { echo ' Here I am!'; })->addMiddleware(function() {
	echo 'Middleware first!';
});

Flight::start();

// Это выведет "Middleware first! Here I am!"
```

Есть несколько очень важных замечаний о промежуточном ПО, которые вы должны знать перед использованием:
- Функции промежуточного ПО выполняются в порядке их добавления к маршруту. Выполнение аналогично тому, как это обрабатывается в [Slim Framework](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - Before выполняются в порядке добавления, а After — в обратном порядке.
- Если функция промежуточного ПО возвращает false, все выполнение останавливается и генерируется ошибка 403 Forbidden. Вероятно, вы захотите обработать это более изящно с помощью `Flight::redirect()` или подобного.
- Если вам нужны параметры из вашего маршрута, они будут переданы в виде единого массива в вашу функцию промежуточного ПО. (`function($params) { ... }` или `public function before($params) {}`). Причина в том, что вы можете структурировать свои параметры в группы, и в некоторых из этих групп параметры могут появляться в другом порядке, что сломает функцию промежуточного ПО при ссылке на неправильный параметр. Таким образом, вы можете обращаться к ним по имени, а не по позиции.
- Если вы передадите только имя промежуточного ПО, оно автоматически будет выполнено через [контейнер внедрения зависимостей](dependency-injection-container), и промежуточное ПО будет выполнено с необходимыми параметрами. Если контейнер внедрения зависимостей не зарегистрирован, будет передан экземпляр `flight\Engine` в `__construct()`.

## Классы промежуточного ПО

Промежуточное ПО также можно зарегистрировать как класс. Если вам нужна функциональность "after", вы **должны** использовать класс.

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

## Обработка ошибок промежуточного ПО

Допустим, у вас есть промежуточное ПО для аутентификации, и вы хотите перенаправить пользователя на страницу входа, если он не аутентифицирован. У вас есть несколько вариантов:

1. Вы можете вернуть false из функции промежуточного ПО, и Flight автоматически вернет ошибку 403 Forbidden, но без настройки.
1. Вы можете перенаправить пользователя на страницу входа с помощью `Flight::redirect()`.
1. Вы можете создать пользовательскую ошибку в промежуточном ПО и остановить выполнение маршрута.

### Базальный пример

Вот простой пример с возвратом false:
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			return false;
		}

		// поскольку это true, всё просто продолжает выполняться
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

Допустим, вам нужно генерировать ошибку в формате JSON, потому что вы создаете API. Вы можете сделать это так:
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

## Группировка промежуточного ПО

Вы можете добавить группу маршрутов, и каждое маршрут в этой группе будет иметь то же промежуточное ПО. Это полезно, если вам нужно сгруппировать несколько маршрутов, например, с промежуточным ПО Auth для проверки ключа API в заголовке.

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

Если вы хотите применить глобальное промежуточное ПО ко всем вашим маршрутам, вы можете добавить "пустую" группу:

```php
// добавлено в конце метода group
Flight::group('', function() {

	// Это всё равно /users
	Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// И это всё равно /users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ ApiAuthMiddleware::class ]); // или [ new ApiAuthMiddleware() ], то же самое
```