# Безопасность

Безопасность - это серьезное дело, когда речь идет о веб-приложениях. Вы хотите быть уверены, что ваше приложение безопасно и данные ваших пользователей защищены. Flight предоставляет ряд функций, чтобы помочь вам обеспечить безопасность ваших веб-приложений.

## Заголовки (Headers)

HTTP заголовки - один из самых простых способов защитить ваши веб-приложения. Вы можете использовать заголовки, чтобы предотвратить clickjacking, XSS и другие атаки. Есть несколько способов, как можно добавить эти заголовки к вашему приложению.

Два отличных сайта, на которых можно проверить безопасность ваших заголовков, это [securityheaders.com](https://securityheaders.com/) и [observatory.mozilla.org](https://observatory.mozilla.org/).

### Добавление вручную

Вы можете вручную добавить эти заголовки, используя метод 'header' объекта `Flight\Response`.
```php
// Установить заголовок X-Frame-Options для предотвращения clickjacking
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Установить заголовок Content-Security-Policy для предотвращения XSS
// Примечание: этот заголовок может быть очень сложным, так что вам понадобится
// обращаться к примерам в интернете для вашего приложения
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Установить заголовок X-XSS-Protection для предотвращения XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Установить заголовок X-Content-Type-Options для предотвращения MIME sniffing
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Установить заголовок Referrer-Policy для управления количеством отправляемой информации обратного адреса
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Установить заголовок Strict-Transport-Security для принудительного использования HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Установить заголовок Permissions-Policy для управления используемыми функциями и API
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Эти заголовки могут быть добавлены в начало ваших файлов `bootstrap.php` или `index.php`.

### Добавление как Фильтр

Вы также можете добавить их в фильтр/хук, как показано ниже:

```php
// Добавить заголовки в фильтр
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

### Добавление как Middleware

Вы также можете добавить их как класс Middleware. Это хороший способ держать свой код чистым и организованным.

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

// index.php или где у вас находятся ваши маршруты
//FYI, эта группа пустых строк действует как глобальное промежуточное ПО для всех маршрутов. Конечно же, вы могли бы сделать то же самое и просто добавить это только к определенным маршрутам.
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers']);
	// более маршрутов
}, [ new SecurityHeadersMiddleware() ]);
```


## Межсайтовая Подделка Запроса (CSRF)

Межсайтовая подделка запроса (CSRF) - это тип атаки, при которой злоумышленный сайт может заставить браузер пользователя отправить запрос на ваш сайт. Это может использоваться для выполнения действий на вашем сайте без ведома пользователя. Flight не предоставляет встроенного механизма защиты от CSRF, но вы легко можете реализовать свой собственный, используя промежуточное программное обеспечение.

### Настройка

Сначала вам необходимо сгенерировать токен CSRF и сохранить его в сессии пользователя. Затем вы можете использовать этот токен в ваших формах и проверять его при отправке формы.

```php
// Сгенерировать токен CSRF и сохранить его в сессии пользователя
// (предполагая, что вы создали объект сеанса и присоединили его к Flight)
// см. документацию по сессиям для получения дополнительной информации
Flight::register('session', \Ghostff\Session\Session::class);

// Вам нужно сгенерировать только один токен на сеанс (чтобы он работал 
// в нескольких вкладках и запросах для одного и того же пользователя)
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
// Установить пользовательскую функцию для вывода токена CSRF
// Примечание: Вид был настроен с движком представления Latte
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
// Это промежуточное программное обеспечение проверяет, является ли запрос запросом POST, и если да, проверяет, является ли токен CSRF допустимым
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// получаем токен CSRF из значений формы
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Недопустимый токен CSRF');
			// или для JSON-ответа
			Flight::jsonHalt(['error' => 'Недопустимый токен CSRF'], 403);
		}
	}
});
```

Или вы можете использовать класс промежуточного программного обеспечения:

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

// index.php или где у вас находятся ваши маршруты
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// более маршрутов
}, [ new CsrfMiddleware() ]);
```

## Межсайтовая Вставка Скриптов (XSS)

Межсайтовая вставка скриптов (XSS) - это тип атаки, при которой злоумышленный сайт может внедрить код на вашем сайте. Большинство таких возможностей происходят из значений форм, которые вводят ваши конечные пользователи. Никогда не доверяйте выводу от ваших пользователей! Всегда предполагайте, что все они - лучшие хакеры в мире. Они могут внедрить вредоносный JavaScript или HTML на вашу страницу. Этот код может использоваться для кражи информации от ваших пользователей или выполнения действий на вашем сайте. Используя класс представлений Flight, вы легко можете экранировать вывод для предотвращения атак XSS.

```php
// Допустим, пользователь умный и пытается использовать это в качестве своего имени
$name = '<script>alert("XSS")</script>';

// Это экранирует вывод
Flight::view()->set('name', $name);
// Это выведет: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Если вы используете что-то вроде Latte, зарегистрированного как ваш класс представлений, он также автоматически будет экранировать это.
Flight::view()->render('template', ['name' => $name]);
```

## SQL Инъекция

SQL инъекция - это тип атаки, при которой злоумышленный пользователь может внедрить SQL-код в вашу базу данных. Это может быть использовано для кражи информации из вашей базы данных или выполнения действий над вашей базой данных. Опять же, никогда не доверяйте вводу от ваших пользователей! Всегда предполагайте, что они настроены враждебно. Вы можете использовать подготовленные операторы в ваших объектах `PDO`, чтобы предотвратить SQL инъекцию.

```php
// Предполагая, что у вас зарегистрирован Flight::db() как ваш объект PDO
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// Если вы используете класс PdoWrapper, это можно сделать легко в одной строке
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Вы можете сделать то же самое с объектом PDO с использованием плейсхолдеров ?
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// Просто пообещайте, что вы никогда, никогда НИКОГДА не сделаете что-то подобное...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// потому что что, если $username = "' OR 1=1; -- "; 
// После построения запроса он выглядит следующим образом
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// Это выглядит странно, но это действующий запрос, который будет работать. Фактически,
// это очень распространенная атака методом SQL инъекции, которая вернет все пользователей.  
```

## CORS

Обмен ресурсами между разными источниками (CORS) - это механизм, который позволяет запросить множество ресурсов (например, шрифты, JavaScript и т. д.) на веб-странице из другого домена, отличного от домена, из которого ресурс был запрошен. У Flight нет встроенной функциональности, но это легко можно обработать с помощью хука, который запускается перед вызовом метода `Flight::start()`.

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
		// настройте разрешенные хосты здесь
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

// index.php или где у вас находятся ваши маршруты
$CorsUtil = new CorsUtil();

// Это должно быть выполнено до вызова start.
Flight::before('start', [ $CorsUtil, 'setupCors' ]);
```

## Заключение

Безопасность - это серьезное дело, и важно, чтобы ваши веб-приложения были безопасны. Flight предоставляет ряд функций, чтобы помочь вам обеспечить безопасность ваших веб-приложений, но важно всегда быть бдительным и удостовериться, что вы делаете все возможное, чтобы сохранить безопасность данных ваших пользователей. Всегда предполагайте худшее и никогда не доверяйте вводу от ваши# Безопасность

Безопасность - это серьезное дело, когда речь идет о веб-приложениях. Вы хотите быть уверены, что ваше приложение безопасно и данные ваших пользователей защищены. Flight предоставляет ряд функций, чтобы помочь вам обеспечить безопасность ваших веб-приложений.

## Заголовки (Headers)

HTTP заголовки - один из самых простых способов защитить ваши веб-приложения. Вы можете использовать заголовки, чтобы предотвратить clickjacking, XSS и другие атаки. Есть несколько способов, как можно добавить эти заголовки к вашему приложению.

Два отличных сайта, на которых можно проверить безопасность ваших заголовков, это [securityheaders.com](https://securityheaders.com/) и [observatory.mozilla.org](https://observatory.mozilla.org/).

### Добавление вручную

Вы можете вручную добавить эти заголовки, используя метод 'header' объекта `Flight\Response`.
```php
// Установить заголовок X-Frame-Options для предотвращения clickjacking
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Установить заголовок Content-Security-Policy для предотвращения XSS
// Примечание: этот заголовок может быть очень сложным, так что вам понадобится
// обращаться к примерам в интернете для вашего приложения
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Установить заголовок X-XSS-Protection для предотвращения XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Установить заголовок X-Content-Type-Options для предотвращения MIME sniffing
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Установить заголовок Referrer-Policy для управления количеством отправляемой информации обратного адреса
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Установить заголовок Strict-Transport-Security для принудительного использования HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Установить заголовок Permissions-Policy для управления используемыми функциями и API
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Эти заголовки могут быть добавлены в начало ваших файлов `bootstrap.php` или `index.php`.

### Добавление как Фильтр

Вы также можете добавить их в фильтр/хук, как показано ниже:

```php
// Добавить заголовки в фильтр
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

### Добавление как Middleware

Вы также можете добавить их как класс Middleware. Это хороший способ держать свой код чистым и организованным.

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

// index.php или где у вас находятся ваши маршруты
//FYI, эта группа пустых строк действует как глобальное промежуточное ПО для всех маршрутов. Конечно же, вы могли бы сделать то же самое и просто добавить это только к определенным маршрутам.
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers']);
	// более маршрутов
}, [ new SecurityHeadersMiddleware() ]);
```


## Межсайтовая Подделка Запроса (CSRF)

Межсайтовая подделка запроса (CSRF) - это тип атаки, при которой злоумышленный сайт может заставить браузер пользователя отправить запрос на ваш сайт. Это может использоваться для выполнения действий на вашем сайте без ведома пользователя. Flight не предоставляет встроенного механизма защиты от CSRF, но вы легко можете реализовать свой собственный, используя промежуточное программное обеспечение.

### Настройка

Сначала вам необходимо сгенерировать токен CSRF и сохранить его в сессии пользователя. Затем вы можете использовать этот токен в ваших формах и проверять его при отправке формы.

```php
// Сгенерировать токен CSRF и сохранить его в сессии пользователя
// (предполагая, что вы создали объект сеанса и присоединили его к Flight)
// см. документацию по сессиям для получения дополнительной информации
Flight::register('session', \Ghostff\Session\Session::class);

// Вам нужно сгенерировать только один токен на сеанс (чтобы он работал 
// в нескольких вкладках и запросах для одного и того же пользователя)
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
// Установить пользовательскую функцию для вывода токена CSRF
// Примечание: Вид был настроен с движком представления Latte
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
// Это промежуточное программное обеспечение проверяет, является ли запрос запросом POST, и если да, проверяет, является ли токен CSRF допустимым
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// получаем токен CSRF из значений формы
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Недопустимый токен CSRF');
			// или для JSON-ответа
			Flight::jsonHalt(['error' => 'Недопустимый токен CSRF'], 403);
		}
	}
});
```

Или вы можете использовать класс промежуточного программного обеспечения:

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

// index.php или где у вас находятся ваши маршруты
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers']);
	// более маршрутов
}, [ new CsrfMiddleware() ]);
```

## Межсайтовая Вставка Скриптов (XSS)

Межсайтовая вставка скриптов (XSS) - это тип атаки, при которой злоумышленный сайт может внедрить код на вашем сайте. Большинство таких возможностей происходят из значений форм, которые вводят ваши конечные пользователи. Никогда не доверяйте выводу от ваших пользователей! Всегда предполагайте, что все они - лучшие хакеры в мире. Они могут внедрить вредоносный JavaScript или HTML на вашу страницу. Этот код может быть использован для кражи информации от ваших пользователей или выполнения действий на вашем сайте. Используя класс представлений Flight, вы легко можете экранировать вывод для предотвращения атак XSS.

```php
// Допустим, пользователь умный и пытается использовать это в качестве своего имени
$name = '<script>alert("XSS")</script>';

// Это экранирует вывод
Flight::view()->set('name', $name);
// Это выведет: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Если вы используете что-то вроде Latte, зарегистрированного как ваш класс представлений, он также автоматически будет экранировать это.
Flight::view()->render('template', ['name' => $name]);
```

## SQL Инъекция

SQL инъекция - это тип атаки, при которой злоумышленный пользователь может внедрить SQL-код в вашу базу данных. Это может быть использовано для кражи информации из вашей базы данных или выполнения действий над вашей базой данных. Опять же, никогда не доверяйте вводу от ваших пользователей! Всегда предполагайте они нацелены на вас. Вы можете использовать подготовленные операторы в ваших объектах `PDO`, чтобы предотвратить SQL инъекцию.

```php
// Предполагая, что у вас зарегистрирован Flight::db() как ваш объект PDO
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// Если вы используете класс PdoWrapper, это можно сделать легко в одной строке
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Вы можете сделать то же самое с объектом PDO с использованием плейсхолдеров ?
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// Просто пообещайте, что вы никогда, никогда НИКОГДА не сделаете что-то подобное...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// потому что что, если $username = "' OR 1=1; -- "; 
// После построения запроса он выглядит следующим образом
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// Это выглядит странно, но это действующий запрос, который будет работать. Фактически,
// это очень распространенная атака методом SQL инъекции, которая вернет все пользователей.  
```

## CORS

Обмен ресурсами между разными источниками (CORS) - это механизм, который позволяет запросить множество ресурсов (например, шрифты, JavaScript и т. д.) на веб-странице из другого домена, отличного от домена, из которого ресурс был запрошен. У Flight нет встроенной функциональности, но это легко можно обработать с помощью хука, который запускается перед вызовом метода `Flight::start()`.

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
		// настройте разрешенные хосты здесь
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

// index.php или где у вас находятся ваши маршруты
$CorsUtil = new CorsUtil();

// Это должно быть выполнено до вызова start.
Flight::before('start', [ $CorsUtil, 'setupCors' ]);
```

## Заключение

Безопасность - это серьезное дело, и важно, чтобы ваши веб-приложения были безопасны. Flight предоставляет ряд функций, чтобы помочь вам обеспечить безопасность ваших веб-приложений, но важно всегда быть бдительным и удостовериться, что вы делаете все возможное, чтобы сохранить безопасность данных ваших пользователей. Всегда предполагайте худшее и никогда не доверяйте вводу от ваши