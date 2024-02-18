# Безопасность

Безопасность играет большую роль в веб-приложениях. Вы хотите убедиться, что ваше приложение защищено, и данные ваших пользователей в безопасности. Flight предоставляет ряд функций для помощи в обеспечении безопасности ваших веб-приложений.

## Заголовки

HTTP-заголовки - один из самых простых способов обеспечить безопасность ваших веб-приложений. Вы можете использовать заголовки для предотвращения clickjacking, XSS и других атак. Есть несколько способов добавить эти заголовки к вашему приложению.

### Добавить Вручную

Вы можете вручную добавить эти заголовки, используя метод `header` объекта `Flight\Response`.
```php
// Установите заголовок X-Frame-Options для предотвращения clickjacking
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Установите заголовок Content-Security-Policy для предотвращения XSS
// Примечание: этот заголовок может быть очень сложным, поэтому вам стоит
//  просмотреть примеры в интернете для вашего приложения
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Установите заголовок X-XSS-Protection для предотвращения XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Установите заголовок X-Content-Type-Options для предотвращения MIME-сниффинга
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Установите заголовок Referrer-Policy для контроля отправляемой информации о referer
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Установите заголовок Strict-Transport-Security для принудительного использования HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
```

Эти заголовки могут быть добавлены в начало ваших файлов `bootstrap.php` или `index.php`.

### Добавление как Фильтр

Вы также можете добавить их в фильтр/хук, как показано ниже:

```php
// Добавление заголовков в фильтре
Flight::before('start', function() {
	Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');
	Flight::response()->header("Content-Security-Policy", "default-src 'self'");
	Flight::response()->header('X-XSS-Protection', '1; mode=block');
	Flight::response()->header('X-Content-Type-Options', 'nosniff');
	Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');
	Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
});
```

### Добавление как Middleware

Вы также можете добавить их как класс middleware. Это хороший способ держать ваш код чистым и организованным.

```php
// app/middleware/SecurityHeadersMiddleware.php

namespace app\middleware;

class SecurityHeadersMiddleware
{
	public function before(array $params): void
	{
		Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');
		Flight::response()->header("Content-Security-Policy", "default-src 'self'");
		Flight::response()->header('X-XSS-Protection', '1; mode=block');
		Flight::response()->header('X-Content-Type-Options', 'nosniff');
		Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');
		Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
	}
}

// index.php или любое другое место, где у вас находятся ваши маршруты
// FYI, этот пустой групповой элемент действует как глобальный middleware для
// всех маршрутов. Конечно же, вы можете сделать то же самое и просто добавить
// это только для конкретных маршрутов.
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// другие маршруты
}, [ new SecurityHeadersMiddleware() ]);
```


## Межсайтовая Подделка Запроса (CSRF)

Межсайтовая Подделка Запроса (CSRF) - тип атаки, при котором злонамеренный сайт может заставить браузер пользователя отправить запрос на ваш сайт. Это можно использовать для выполнения действий на вашем сайте без ведома пользователя. Flight не предоставляет встроенного механизма защиты от CSRF, но вы легко можете реализовать свой собственный, используя middleware.

### Настройка

Сначала вам нужно сгенерировать токен CSRF и сохранить его в сессии пользователя. Затем вы можете использовать этот токен в ваших формах и проверять его при отправке формы.

```php
// Генерация токена CSRF и сохранение его в сессии пользователя
// (предполагая, что вы создали объект сессии и привязали его к Flight)
// Вам нужно сгенерировать только один токен на сессию (чтобы он работал
// на нескольких вкладках и запросах от одного и того же пользователя)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

```html
<!-- Использование токена CSRF в вашей форме -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- другие поля формы -->
</form>
```

#### Использование Latte

Вы также можете установить пользовательскую функцию для вывода токена CSRF в ваших шаблонах Latte.

```php
// Установка пользовательской функции для вывода токена CSRF
// Примечание: View настроен с использованием Latte в качестве движка представления
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

А теперь в ваших шаблонах Latte вы можете использовать функцию `csrf()` для вывода токена CSRF.

```html
<form method="post">
	{csrf()}
	<!-- другие поля формы -->
</form>
```

Кратко и просто, верно?

### Проверка токена CSRF

Вы можете проверить токен CSRF, используя фильтры событий:

```php
// Этот middleware проверяет, является ли запрос POST-запросом, и, если да, проверяет, валиден ли токен CSRF
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// захват токена csrf из значений формы
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Недействительный токен CSRF');
		}
	}
});
```

Или вы можете использовать класс middleware:

```php
// app/middleware/CsrfMiddleware.php

namespace app\middleware;

class CsrfMiddleware
{
	public function before(array $params): void
	{
		if(Flight::request()->method == 'POST') {
			$token = Flight::request()->data->csrf_token;
			if($token !== Flight::session()->get('csrf_token')) {
				Flight::halt(403, 'Недействительный токен CSRF');
			}
		}
	}
}

// index.php или любое другое место, где у вас находятся ваши маршруты
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// другие маршруты
}, [ new CsrfMiddleware() ]);
```


## Межсайтовые Сценарии (XSS)

Межсайтовые Сценарии (XSS) - тип атаки, при котором злонамеренный сайт может внедрить код на вашем сайте. Большинство таких возможностей появляются из значений форм, которые заполняют ваши пользователи. Вы **никогда не** должны доверять выводу от ваших пользователей! Всегда считайте, что все они лучшие хакеры в мире. Они могут внедрять зловредный JavaScript или HTML на вашу страницу. Этот код может использоваться для кражи информации у ваших пользователей или выполнения действий на вашем сайте. Используя класс представления Flight, вы легко можете экранировать вывод, чтобы предотвратить атаки XSS.

```php
// Предположим, что пользователь умный и пытается использовать это в качестве своего имени
$name = '<script>alert("XSS")</script>';

// Это будет экранировать вывод
Flight::view()->set('name', $name);
// Это выведет: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Если вы используете что-то подобное Latte, зарегистрированное как ваш класс представления, это также будет авто-экранировать это.
Flight::view()->render('шаблон', ['name' => $name]);
```

## SQL-инъекции

SQL-инъекция - тип атаки, при котором злонамеренный пользователь может внедрить SQL-код в вашу базу данных. Это может использоваться для кражи информации из вашей базы данных или выполнения действий над вашей базой данных. Опять же, **никогда** не доверяйте вводу от ваших пользователей! Всегда считайте, что они нацелены на разрушение. Вы можете использовать подготовленные выражения в ваших объектах `PDO`, чтобы предотвратить SQL-инъекции.

```php
// Предположим, что у вас зарегистрирован Flight::db() как ваш объект PDO
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// Если вы используете класс PdoWrapper, это можно легко сделать в одну строку
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Вы можете сделать то же самое с объектом PDO с знаками вопроса?
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// Просто пообещайте, что никогда НИКОГДА не будете делать что-то подобное этому...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// потому что что, если $username = "' OR 1=1; -- "; 
// После построения запроса он выглядит так
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// Это выглядит странно, но это действующий запрос, который сработает. Фактически,
// это очень распространенная атака SQL-инъекции, которая вернет все пользователи.
```

## CORS

Обмен Ресурсами с Кросс-Доменным Согласием (CORS) - это механизм, позволяющий многим ресурсам (например, шрифты, JavaScript и т. д.) на веб-странице быть запрошенными с другого домена вне домена, откуда ресурс происходит. В Flight отсутствует встроенная функциональность, но это легко можно обработать с помощью посредников или фильтров событий, аналогично CSRF.

```php
// app/middleware/CorsMiddleware.php

namespace app\middleware;

class CorsMiddleware
{
	public function before(array $params): void
	{
		$response = Flight::response();
		if (isset($_SERVER['HTTP_ORIGIN'])) {
			$this->allowOrigins();
			$response->header('Access-Control-Allow-Credentials: true');
			$response->header('Access-Control-Max-Age: 86400');
		}

		if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
				$response->header(
					'Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS'
				);
			}
			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
				$response->header(
					"Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}"
				);
			}
			$response->send();
			exit(0);
		}
	}

	private function allowOrigins(): void
	{
		// настройте ваш разрешенные хосты здесь.
		$allowed = [
			'capacitor://localhost',
			'ionic://localhost',
			'http://localhost',
			'http://localhost:4200',
			'http://localhost:8080',
			'http://localhost:8100',
		];

		if (in_array($_SERVER['HTTP_ORIGIN'], $allowed)) {
			$response = Flight::response();
			$response->header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
		}
	}
}

// index.php или любое другое место, где у вас находятся ваши маршруты
Flight::route('/users', function() {
	$users = Flight::db()->fetchAll('SELECT * FROM users');
	Flight::json($users);
})->addMiddleware(new CorsMiddleware());
```

## Заключение

Безопасность играет большую роль, и важно убедиться, что ваши веб-приложения защищены. Flight предоставляет ряд функций для помощи в обеспечении безопасности ваших веб-приложений, но важно всегда быть бдительным и убедиться, что вы делаете все возможное, чтобы держать данные ваших пользователей в безопасности. Всегда предполагайте худшее и никогда не доверяйте вводу от ваших пользователей. Всегда экранируйте вывод и используйте подготовленные выражения, чтобы предотвратить SQL-инъекции. Всегда используйте посредники для защиты ваших маршрутов от атак CSRF и CORS. Если вы выполните все эти действия, вы будете на правильном пути к созданию безопасных веб-приложений.