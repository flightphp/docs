# Безопасность

Безопасность - это серьезное дело, когда речь идёт о веб-приложениях. Вы хотите быть уверены, что ваше приложение защищено, и данные ваших пользователей в безопасности. Flight предоставляет ряд функций, чтобы помочь вам обеспечить безопасность ваших веб-приложений.

## Заголовки

HTTP-заголовки - один из самых простых способов обеспечить безопасность ваших веб-приложений. Вы можете использовать заголовки для предотвращения кликджекинга, XSS и других атак. Существует несколько способов добавить эти заголовки к вашему приложению.

Два отличных вебсайта, на которых можно проверить безопасность ваших заголовков, это [securityheaders.com](https://securityheaders.com/) и [observatory.mozilla.org](https://observatory.mozilla.org/).

### Добавить вручную

Вы можете вручную добавить эти заголовки, используя метод `header` объекта `Flight\Response`.
```php
// Установите заголовок X-Frame-Options, чтобы предотвратить кликджекинг
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Установите заголовок Content-Security-Policy, чтобы предотвратить XSS
// Примечание: этот заголовок может быть очень сложным, поэтому вам следует
//  проконсультироваться с примерами в интернете для вашего приложения
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Установите заголовок X-XSS-Protection, чтобы предотвратить XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Установите заголовок X-Content-Type-Options, чтобы предотвратить сниффинг MIME
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Установите заголовок Referrer-Policy, чтобы контролировать, сколько информации о реферере отправляется
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Установите заголовок Strict-Transport-Security, чтобы принудительно использовать HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Установите заголовок Permissions-Policy, чтобы контролировать, какие возможности и API можно использовать
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Эти заголовки могут быть добавлены в начало файлов `bootstrap.php` или `index.php`.

### Добавить как фильтр

Вы также можете добавить их в фильтр/хук, как показано ниже: 

```php
// Добавление заголовков в фильтр
Flight::before('start', function() {
	Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');
	Flight::response()->header("Content-Security-Policy", "default-src 'self'");
	Flight::response()->header('X-XSS-Protection', '1; mode=block');
	Flight::response()->header('X-Content-Type-Options', 'nosniff');
	Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');
	Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
	Flight::response()->header('Permissions-Policy', 'geolocation=()');
});
```

### Добавить как промежуточный слой

Вы также можете добавить их как класс промежуточного слоя. Это хороший способ держать ваш код чистым и организованным.

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
		Flight::response()->header('Permissions-Policy', 'geolocation=()');
	}
}

// index.php or wherever you have your routes
// FYI, this empty string group acts as a global middleware for
// all routes. Of course you could do the same thing and just add
// this only to specific routes.
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// more routes
}, [ new SecurityHeadersMiddleware() ]);
```


## Межсайтовая Подделка Запроса (CSRF)

Межсайтовая Подделка Запроса (CSRF) - это тип атаки, при которой злонамеренный сайт может заставить браузер пользователя отправить запрос на ваш сайт. Это может быть использовано для выполнения действий на вашем сайте без ведома пользователя. Flight не предоставляет встроенного механизма защиты от CSRF, но вы легко можете реализовать свой собственный, используя промежуточный слой.

### Настройка

Сначала вам нужно сгенерировать токен CSRF и сохранить его в сессии пользователя. Затем вы можете использовать этот токен в своих формах и проверять его при отправке формы.

```php
// Генерация токена CSRF и сохранение его в сессии пользователя
// (предполагая, что вы создали объект сессии и прикрепили его к Flight)
// Вам нужно сгенерировать только один токен на сессию (чтобы он работал 
// на разных вкладках и запросах для одного и того же пользователя)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

```html
<!-- Используйте токен CSRF в вашей форме -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- другие поля формы -->
</form>
```

#### Использование Latte

Вы также можете установить пользовательскую функцию для вывода токена CSRF в ваших шаблонах Latte.

```php
// Установите пользовательскую функцию для вывода токена CSRF
// Примечание: Представление настроено с использованием Latte как движка представлений
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

И теперь в ваших шаблонах Latte вы можете использовать функцию `csrf()` для вывода токена CSRF.

```html
<form method="post">
	{csrf()}
	<!-- другие поля формы -->
</form>
```

Коротко и просто, верно?

### Проверка токена CSRF

Вы можете проверить токен CSRF, используя фильтры событий:

```php
// Этот промежуточный слой проверяет, является ли запрос POST-запросом, и если да, проверяет, действителен ли токен CSRF
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// захватите токен csrf из значений формы
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Недопустимый токен CSRF');
		}
	}
});
```

Или вы можете использовать класс промежуточного слоя:

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
				Flight::halt(403, 'Недопустимый токен CSRF');
			}
		}
	}
}

// index.php or wherever you have your routes
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// more routes
}, [ new CsrfMiddleware() ]);
```

## Межсайтовый Скриптинг (XSS)

Межсайтовый Скриптинг (XSS) - это тип атаки, при которой злонамеренный сайт может внедрить код на ваш сайт. Большинство таких возможностей появляются из значений форм, которые пользователи будут заполнять. Вы **никогда** не должны доверять выводу от ваших пользователей! Всегда считайте, что все они лучшие хакеры в мире. Они могут внедрить вредоносный JavaScript или HTML на вашу страницу. Этот код может быть использован для кражи информации у ваших пользователей или для выполнения действий на вашем сайте. Используя класс представления Flight, вы можете легко экранировать вывод, чтобы предотвратить атаки XSS.

```php
// Предположим, что пользователь умный и пытается использовать это в качестве своего имени
$name = '<script>alert("XSS")</script>';

// Это избавит вывод
Flight::view()->set('name', $name);
// Это выведет: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Если вы используете что-то вроде Latte, зарегистрированного в качестве вашего класса представления, это также будет автоматически экранироваться.
Flight::view()->render('template', ['name' => $name]);
```

## SQL-инъекция

SQL-инъекция - это тип атаки, при которой злонамеренный пользователь может внедрить SQL-код в вашу базу данных. Это может использоваться для кражи информации из вашей базы данных или для выполнения действий в вашей базе данных. Опять же вы **никогда** не должны доверять вводу от ваших пользователей! Всегда считайте, что они нацелены на кровь. Вы можете использовать подготовленные операторы в ваших объектах `PDO`, чтобы предотвратить SQL-инъекции.

```php
// Предположим, что у вас зарегистрировано Flight::db() как ваш объект PDO
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// Если вы используете класс PdoWrapper, это можно легко сделать в одну строку
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Вы можете сделать то же самое с объектом PDO с использованием заполнителей ?
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// Просто обещайте, что вы никогда НИКОГДА не будете делать что-то подобное этому...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// потому что вдруг $username = "' OR 1=1; -- "; 
// После формирования запроса он будет выглядеть так
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// Выглядит странно, но это допустимый запрос, который будет работать. Фактически,
// это очень распространенная атака SQL-инъекции, которая вернет всех пользователей.
```

## CORS

Обмен ресурсами между разными источниками (CORS) - это механизм, который позволяет запрашивать множество ресурсов (например, шрифты, JavaScript и т. д.) на веб-странице из другого домена, отличного от домена, из которого ресурс начался. Flight не имеет встроенной функциональности, но это легко можно обработать с помощью хука для запуска перед вызовом метода `Flight::start()`.

```php
// app/utils/CorsUtil.php

namespace app\utils;

class CorsUtil
{
	public function set(array $params): void
	{
		$request = Flight::request();
		$response = Flight::response();
		if ($request->getVar('HTTP_ORIGIN') !== '') {
			$this->allowOrigins();
			$response->header('Access-Control-Allow-Credentials', 'true');
			$response->header('Access-Control-Max-Age', '86400');
		}

		if ($request->method === 'OPTIONS') {
			if ($request->getVar('HTTP_ACCESS_CONTROL_REQUEST_METHOD') !== '') {
				$response->header(
					'Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS, HEAD'
				);
			}
			if ($request->getVar('HTTP_ACCESS_CONTROL_REQUEST_HEADERS') !== '') {
				$response->header(
					"Access-Control-Allow-Headers",
					$request->getVar('HTTP_ACCESS_CONTROL_REQUEST_HEADERS')
				);
			}

			$response->status(200);
			$response->send();
			exit;
		}
	}

	private function allowOrigins(): void
	{
		// настройте разрешенные хосты здесь.
		$allowed = [
			'capacitor://localhost',
			'ionic://localhost',
			'http://localhost',
			'http://localhost:4200',
			'http://localhost:8080',
			'http://localhost:8100',
		];

		$request = Flight::request();

		if (in_array($request->getVar('HTTP_ORIGIN'), $allowed, true) === true) {
			$response = Flight::response();
			$response->header("Access-Control-Allow-Origin", $request->getVar('HTTP_ORIGIN'));
		}
	}
}

// index.php or wherever you have your routes
$CorsUtil = new CorsUtil();
Flight::before('start', [ $CorsUtil, 'setupCors' ]);

```

## Заключение

Безопасность - это серьезное дело, и важно убедиться, что ваши веб-приложения защищены. Flight предоставляет ряд функций, чтобы помочь вам обеспечить безопасность ваших веб-приложений, но важно всегда быть бдительным и делать все возможное, чтобы сохранить данные ваших пользователей в безопасности. Всегда предполагайте худшее и никогда не доверяйте вводу от ваших пользователей. Всегда экранируйте вывод и используйте подготовленные операторы для предотвращения SQL-инъекций. Всегда используйте промежуточные слои для защиты ваших маршрутов от атак CSRF и CORS. Если вы сделаете все эти вещи, вы будете на верном пути к созданию безопасных веб-приложений.