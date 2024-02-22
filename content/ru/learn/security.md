# Безопасность

Безопасность - это серьезный вопрос, когда речь идет о веб-приложениях. Вы хотите быть уверены, что ваше приложение защищено, и данные ваших пользователей в безопасности. Flight предоставляет ряд функций, чтобы помочь вам обеспечить безопасность вашего веб-приложения.

## Заголовки

HTTP-заголовки - один из простейших способов обеспечить безопасность ваших веб-приложений. Вы можете использовать заголовки, чтобы предотвратить кликджекинг, XSS и другие атаки. Существует несколько способов добавления этих заголовков в ваше приложение.

Два отличных веб-сайта, на которых можно проверить безопасность ваших заголовков, это [securityheaders.com](https://securityheaders.com/) и [observatory.mozilla.org](https://observatory.mozilla.org/).

### Добавить вручную

Вы можете вручную добавить эти заголовки, используя метод `header` объекта `Flight\Response`.
```php
// Установите заголовок X-Frame-Options, чтобы предотвратить кликджекинг
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Установите заголовок Content-Security-Policy, чтобы предотвратить XSS
// Примечание: этот заголовок может быть очень сложным, поэтому вам понадобится
//  примеры из интернета для вашего приложения
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Установите заголовок X-XSS-Protection, чтобы предотвратить XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Установите заголовок X-Content-Type-Options, чтобы предотвратить распознавание MIME
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Установите заголовок Referrer-Policy, чтобы контролировать отправку информации о referrer
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Установите заголовок Strict-Transport-Security, чтобы принудить HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Установите заголовок Permissions-Policy, чтобы контролировать, какие функции и API могут использоваться
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Эти заголовки могут быть добавлены в начало ваших файлов `bootstrap.php` или `index.php`.

### Добавить как Фильтр

Вы также можете добавить их с помощью фильтра/хука, как показано ниже: 

```php
// Добавьте заголовки в фильтре
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

### Добавить как Промежуточное ПО

Вы также можете добавить их как класс промежуточного ПО. Это хороший способ держать ваш код чистым и организованным.

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

// index.php или где у вас ваши маршруты
// Прим. Эта пустая группа строки действует как глобальное промежуточное ПО для
// всех маршрутов. Конечно, вы можете сделать то же самое и добавить этот блок только к конкретным маршрутам.
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// другие маршруты
}, [ new SecurityHeadersMiddleware() ]);
```

## Межсайтовая Подделка Запроса (CSRF)

Межсайтовая Подделка Запроса (CSRF) - это тип атаки, при котором злоумышленный сайт может заставить браузер пользователя отправить запрос на ваш сайт. Это можно использовать для выполнения действий на вашем сайте без ведома пользователя. Flight не предоставляет встроенного механизма защиты от CSRF, но вы можете легко реализовать свой собственный с помощью промежуточного ПО.

### Настройка

Сначала вам нужно сгенерировать токен CSRF и сохранить его в сессии пользователя. Затем вы можете использовать этот токен в ваших формах и проверять его при отправке формы.

```php
// Сгенерировать токен CSRF и сохранить его в сессии пользователя
// (предполагая, что вы создали объект сеанса и привязали его к Flight)
// Вам нужно сгенерировать всего один токен на сеанс (чтобы это работало
// в разных вкладках и запросах для одного и того же пользователя)
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

#### Используя Latte

Вы также можете установить пользовательскую функцию для вывода токена CSRF в ваших шаблонах Latte.

```php
// Установите пользовательскую функцию для вывода токена CSRF
// Примечание: Предположим, что Просмотр настроен с Latte как видовым движком
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

### Проверьте Токен CSRF

Вы можете проверить токен CSRF, используя событийные фильтры:

```php
// Это промежуточное ПО проверяет, является ли запрос POST-запросом, и если да, проверяет, действителен ли токен CSRF
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

Или вы можете использовать класс промежуточного ПО:

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

// index.php или где у вас ваши маршруты
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// другие маршруты
}, [ new CsrfMiddleware() ]);
```

## Межсайтовый Скриптинг (XSS)

Межсайтовый Скриптинг (XSS) - это тип атаки, при котором злоумышленный сайт может внедрить код в ваш сайт. Большинство из этих возможностей появляются из значений форм, которые заполняют ваши конечные пользователи. Вы **никогда** не должны доверять вводу от ваших пользователей! Всегда считайте, что все они - лучшие хакеры в мире. Они могут внедрить злоумышленный JavaScript или HTML на вашу страницу. Этот код может быть использован для кражи информации от ваших пользователей или выполнения действий на вашем сайте. Используя класс представления Flight, вы можете легко экранировать вывод, чтобы предотвратить атаки XSS.

```php
// Предположим, что пользователь хитрый и пытается использовать это как свое имя
$name = '<script>alert("XSS")</script>';

// Это будет экранировать вывод
Flight::view()->set('name', $name);
// Это будет вывод: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Если вы используете что-то, например, Latte, зарегистрированное как ваш класс представления, оно также автоматически экранирует это.
Flight::view()->render('шаблон', ['name' => $name]);
```

## SQL-инъекция

SQL-инъекция - это тип атаки, при котором злоумышленный пользователь может внедрить SQL-код в вашу базу данных. Это можно использовать для кражи информации из вашей базы данных или выполнения действий в вашей базе данных. Снова вы **никогда** не должны доверять вводу от ваших пользователей! Всегда считайте, что они готовы на все. Вы можете использовать подготовленные выражения в ваших объектах `PDO`, чтобы предотвратить SQL-инъекцию.

```php
// Предположим, что у вас зарегистрировано Flight::db() как ваш объект PDO
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// Если вы используете класс PdoWrapper, это можно легко сделать в одной строке
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Вы можете сделать то же самое с объектом PDO с использованием местозаполнителей ?
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// Просто обещайте, что никогда НИКОГДА не будете делать что-то подобное...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// потому что что, если $username = "' OR 1=1; -- "; 
// После построения запроса он выглядит так
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// Это выглядит странно, но это хороший запрос, который сработает. Фактически,
// это очень распространенная атака SQL-инъекции, которая вернет всех пользователей.
```

## CORS

Совместное использование ресурсов с другого источника (CORS) - механизм, который позволяет запросить множество ресурсов (например, шрифты, JavaScript и т. д.) на веб-странице из другого домена вне домена, откуда происходит запуск ресурса. У Flight нет встроенной функциональности, но это легко решается с помощью промежуточного ПО или событийных фильтров, подобных CSRF.

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
		// настройте ваши разрешенные хосты здесь.
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

// index.php или где у вас ваши маршруты
Flight::route('/users', function() {
	$users = Flight::db()->fetchAll('SELECT * FROM users');
	Flight::json($users);
})->addMiddleware(new CorsMiddleware());
```

## Заключение

Безопасность имеет большое значение, и важно убедиться, что ваши веб-приложения защищены. Flight предоставляет ряд функций, чтобы помочь вам обеспечить безопасность ваших веб-приложений, но важно всегда быть бдительным и убедиться, что вы делаете все возможное, чтобы сохранить данные ваших пользователей в безопасности. Всегда предполагайте худшее и никогда не доверяйте вводу от ваших пользователей. Всегда экранируйте вывод и используйте подготовленные выражения, чтобы предотвратить SQL-инъекцию. Всегда используйте промежуточное ПО для защиты ваших маршрутов от атак CSRF и CORS. Если вы сделаете все это, вы будете на# Безопасность

Безопасность - это серьезное дело, когда речь идет о веб-приложениях. Вы хотите убедиться, что ваше приложение защищено, и данные ваших пользователей в безопасности. Flight предоставляет ряд функций, чтобы помочь вам обеспечить безопасность вашего веб-приложения.

## Заголовки

HTTP-заголовки - один из самых простых способов обезопасить ваши веб-приложения. Вы можете использовать заголовки для предотвращения кликджекинга, XSS и других атак. Существует несколько способов добавления этих заголовков в ваше приложение.

Два отличных веб-сайта для проверки безопасности ваших заголовков: [securityheaders.com](https://securityheaders.com/) и [observatory.mozilla.org](https://observatory.mozilla.org/).

### Добавить вручную

Эти заголовки можно добавить вручную, используя метод `header` объекта `Flight\Response`.
```php
// Установить заголовок X-Frame-Options, чтобы предотвратить кликджекинг
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Установить заголовок Content-Security-Policy, чтобы предотвратить XSS
// Примечание: этот заголовок может быть очень сложным, поэтому вам нужно
//  ознакомиться с примерами в интернете для вашего приложения
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Установить заголовок X-XSS-Protection, чтобы предотвратить XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Установить заголовок X-Content-Type-Options, чтобы предотвратить небезопасное типизирование
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Установить заголовок Referrer-Policy, чтобы контролировать отправку информации о реферере
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Установить заголовок Strict-Transport-Security, чтобы принудительно использовать HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Установить заголовок Permissions-Policy, чтобы контролировать использование функций и API
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Эти заголовки могут быть добавлены в начало ваших файлов `bootstrap.php` или `index.php`.

### Добавить как Фильтр

Вы также можете добавить их в фильтре/хуке, как показано ниже: 

```php
// Добавить заголовки в фильтре
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

### Добавить как Промежуточное ПО

Вы также можете добавить их как класс промежуточного ПО. Это хороший способ сохранить ваш код чистым и организованным.

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

// index.php или где у вас маршруты
// Прим. Эта пустая группа действует как глобальное промежуточное ПО для
// всех маршрутов. Конечно, вы также можете добавить это только к определенным маршрутам.
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// дополнительные маршруты
}, [ new SecurityHeadersMiddleware() ]);
```

## Межсайтовая Подделка Запроса (CSRF)

Межсайтовая Подделка Запроса (CSRF) - это тип атаки, при которой злоумышленный сайт может заставить браузер пользователя отправить запрос на ваш сайт. Это можно использовать для выполнения действий на вашем сайте без ведома пользователя. Flight не предоставляет механизма защиты от CSRF "из коробки", но вы легко можете реализовать собственный с использованием промежуточного ПО.

### Настройка

Сначала вам нужно сгенерировать токен CSRF и сохранить его в сеансе пользователя. Затем вы можете использовать этот токен в ваших формах и проверять его при отправке формы.

```php
// Сгенерировать токен CSRF и сохранить его в сеансе пользователя
// (предполагая, что у вас есть объект сессии и он прикреплен к Flight)
// Вам нужно сгенерировать только один токен на сеанс (так, чтобы он работал
// в нескольких вкладках и запросах для одного пользователя)
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

#### Используя Latte

Вы также можете установить пользовательскую функцию для вывода токена CSRF в ваших шаблонах Latte.

```php
// Установите пользовательскую функцию для вывода токена CSRF
// Замечание: Предположим, что View настроен с Latte как видовым движком
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

Просто и понятно, верно?

### Проверка токена CSRF

Вы можете проверить токен CSRF с помощью фильтров событий:

```php
// Это промежуточное ПО проверяет, является ли запрос POST-запросом, и если да, проверяет, является ли токен CSRF действительным
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// получить токен csrf из данных формы
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Неверный токен CSRF');
		}
	}
});
```

Или вы можете использовать класс промежуточного ПО:

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
				Flight::halt(403, 'Неверный токен CSRF');
			}
		}
	}
}

// index.php или где у вас маршруты
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// дополнительные маршруты
}, [ new CsrfMiddleware() ]);
```

## Межсайтовый Сценарий (XSS)

Межсайтовый Сценарий (XSS) - это тип атаки, при котором злоумышленный сайт может внедрить код на вашем сайте. Большинство возможностей для этого происходят из значений форм, которые заполняют ваши конечные пользователи. Вы **никогда** не должны доверять выводу ваших пользователей! Всегда считайте, что все они - лучшие хакеры в мире. Они могут внедрить вредоносный JavaScript или HTML на вашу страницу. Этот код может использоваться для кражи информации от ваших пользователей или выполнения действий на вашем сайте. Используя класс представления Flight, вы легко можете экранировать вывод, чтобы предотвратить атаки XSS.

```php
// Предположим, что пользователь хитрый и пытается использовать это как свое имя
$name = '<script>alert("XSS")</script>';

// Это сбежит вывод
Flight::view()->set('name', $name);
// Это выведет: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Если вы используете что-то вроде Latte в качестве вашего класса представления, оно также автоматически сбегает это.
Flight::view()->render('шаблон', ['name' => $name]);
```

## SQL-Инъекция

SQL-Инъекция - это тип атаки, при котором злоумышленный пользователь может внедрить SQL-код в вашу базу данных. Это можно использовать для кражи информации из вашей базы данных или выполнения действий в вашей базе данных. Опять же, **никогда** не доверяйте вводу ваших пользователей! Всегда предполагайте, что они готовы на все. Вы можете использовать подготовленные выражения в ваших объектах `PDO` для предотвращения SQL-Инъекции.

```php
// Предположим, что у вас зарегистрировано Flight::db() как ваш объект PDO
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// Если вы используете класс PdoWrapper, это можно сделать очень просто
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// То же самое можно сделать с объектом PDO и местозаполнителями ?
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// Просто обещайте, что никогда НИКОГДА не сделаете что-то подобное...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// потому что что, если $username = "' OR 1=1; -- "; 
// После построения запроса он выглядит так
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// Это может выглядет странно, но это действительный запрос, который сработает. На самом деле,
// это очень распространенная атака SQL-Инъекции, которая вернет всех пользователей.
```

## CORS

Совместное использование ресурсов с другого источника (CORS) - механизм, позволяющий запрашивать множество ресурсов (например, шрифты, JavaScript и т. д.) на веб-странице с другого домена вне домена, откуда происходит загрузка ресурса. В Flight нет встроенной функциональности для этого, но это можно легко обработать с помощью промежуточного ПО или событийных фильтров, аналогично CSRF.

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
		// настройте ваши разрешенные хосты здесь.
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

// index.php или где у вас маршруты
Flight::route('/users', function() {
	$users = Flight::db()->fetchAll('SELECT * FROM users');
	Flight::json($users);
})->addMiddleware(new CorsMiddleware());
```

## Заключение

Безопасность очень важна, и важно убедиться, что ваши веб-приложения защищены. Flight предоставляет ряд функций для обеспечения безопасности вашего веб-приложения, но важно всегда быть бдительным и сделать все возможное, чтобы обеспечить безопасность данных ваших пользователей. Всегда предполагайте худшее и никогда не доверяйте вводу от ваших пользователей. Всегда экранируйте вывод и используйте подготовленные выражения, чтобы предотвратить SQL-Инъекцию. Всегда используйте промежуточное ПО для защиты ваших маршрутов от атак CSRF и CORS. Если вы сделаете все это, вы на правильном пути к созданию безопасных веб-приложений.