# Безопасность

Безопасность играет огромную роль, когда речь идет о веб-приложениях. Вам нужно убедиться, что ваше приложение защищено, и данные ваших пользователей в безопасности. Flight предоставляет ряд функций, которые помогут вам обезопасить ваши веб-приложения.

## Заголовки

HTTP-заголовки - один из самых простых способов защиты ваших веб-приложений. Вы можете использовать заголовки, чтобы предотвратить кликджекинг, XSS и другие атаки. Есть несколько способов добавить эти заголовки в ваше приложение.

Два отличных веб-сайта для проверки безопасности ваших заголовков - [securityheaders.com](https://securityheaders.com/) и [observatory.mozilla.org](https://observatory.mozilla.org/).

### Добавление вручную

Вы можете вручную добавить эти заголовки, используя метод `header` объекта `Flight\Response`.
```php
// Установите заголовок X-Frame-Options, чтобы предотвратить кликджекинг
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Установите заголовок Content-Security-Policy, чтобы предотвратить XSS
// Примечание: этот заголовок может быть очень сложным, поэтому вам стоит
//  посмотреть примеры в интернете для вашего приложения
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Установите заголовок X-XSS-Protection, чтобы предотвратить XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Установите заголовок X-Content-Type-Options, чтобы предотвратить подбор MIME
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Установите заголовок Referrer-Policy, чтобы контролировать, сколько информации о реферере отправляется
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Установите заголовок Strict-Transport-Security, чтобы принудительно использовать HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Установите заголовок Permissions-Policy, чтобы контролировать, какие функции и API могут быть использованы
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Эти заголовки можно добавить в начало ваших файлов `bootstrap.php` или `index.php`.

### Добавление как Фильтр

Вы также можете добавить их в фильтр/хук следующим образом:

```php
// Добавьте заголовки в фильтр
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
// FYI, этот пустой групповой элемент служит глобальным промежуточным программным обеспечением для
// всех маршрутов. Конечно, вы можете сделать то же самое и просто добавить
// это только для определенных маршрутов.
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// дополнительные маршруты
}, [ new SecurityHeadersMiddleware() ]);
```

## Межсайтовая фальсификация запросов (CSRF)

Межсайтовая фальсификация запросов (CSRF) - это тип атаки, при которой злоумышленный сайт может заставить браузер пользователя отправить запрос на ваш сайт. Это может использоваться для выполнения действий на вашем сайте без ведома пользователя. Flight не предоставляет встроенный механизм защиты от CSRF, но вы легко можете реализовать свой собственный с помощью промежуточного программного обеспечения.

### Настройка

Сначала вам нужно сгенерировать токен CSRF и сохранить его в сеансе пользователя. Вы затем можете использовать этот токен в ваших формах и проверять его при отправке формы.

```php
// Сгенерируйте токен CSRF и сохраните его в сеансе пользователя
// (предполагая, что вы создали объект сеанса и присоединили его к Flight)
// Вам нужно сгенерировать только один токен на сеанс (чтобы он работал 
// на протяжении нескольких вкладок и запросов одного и того же пользователя)
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
// Примечание: Представление было настроено с Latte в качестве системы представления
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
// Этот промежуточный слой проверяет, является ли запрос POST-запросом, и если да, он проверяет, действителен ли токен CSRF
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// захватывает токен csrf из значений формы
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Недопустимый токен CSRF');
		}
	}
});
```

Или вы можете использовать класс Middleware:

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
	// дополнительные маршруты
}, [ new CsrfMiddleware() ]);
```

## Межсайтовый скриптинг (XSS)

Межсайтовый скриптинг (XSS) - это тип атаки, при которой злоумышленный сайт может внедрить код на ваш сайт. Большинство таких возможностей появляются из значений форм, которые ваши конечные пользователи будут заполнять. Вы **никогда** не должны доверять данным ваших пользователей! Всегда предполагайте, что все они - лучшие хакеры в мире. Они могут внедрить вредоносный JavaScript или HTML на вашу страницу. Этот код может использоваться для кражи информации у ваших пользователей или выполнения действий на вашем сайте. Используя класс представления Flight, вы легко можете экранировать вывод, чтобы предотвратить атаки XSS.

```php
// Допустим, пользователь хитрый и пытается использовать это как свое имя
$name = '<script>alert("XSS")</script>';

// Это эскейпит вывод
Flight::view()->set('name', $name);
// Это выведет: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Если вы используете что-то вроде Latte в качестве вашего класса представления, он также автоматически экранирует это.
Flight::view()->render('template', ['name' => $name]);
```

## SQL-инъекция

SQL-инъекция - это тип атаки, при которой злоумышленник может внедрить SQL-код в вашу базу данных. Это может быть использовано для кражи информации из вашей базы данных или выполнения действий в вашей базе данных. Снова **никогда** не доверяйте вводу ваших пользователей! Всегда предполагайте, что они готовы к действиям. Вы можете использовать подготовленные операторы в ваших объектах `PDO`, чтобы предотвратить SQL-инъекцию.

```php
// Предположим, что у вас зарегистрирован Flight::db() как ваш объект PDO
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// Если вы используете класс PdoWrapper, это легко можно сделать в одну строку
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Вы можете сделать то же самое с объектом PDO с заполнителями ?
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// Просто обещайте, что никогда ЭТО не сделаете...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// потому что а что если $username = "' OR 1=1; -- "; 
// После построения запроса он будет выглядеть так
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// Выглядит странно, но это действительный запрос, который сработает. Фактически,
// это очень распространенная атака SQL-инъекцией, которая вернет всех пользователей.

```

## CORS

Пересечение Областей Ресурсов (CORS) - это механизм, который позволяет множеству ресурсов (например, шрифты, JavaScript и т. д.) на веб-странице запрашиваться с другого домена, отличного от домена, из которого ресурс был получен. Flight не имеет встроенной функциональности, но это легко решается с помощью промежуточного программного обеспечения или фильтров событий, аналогичных CSRF.

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
			$response->header('Access-Control-Allow-Credentials', 'true');
			$response->header('Access-Control-Max-Age', '86400');
		}

		if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
				$response->header(
					'Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS'
				);
			}
			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
				$response->header(
					"Access-Control-Allow-Headers",
					$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']
				);
			}
			$response->send();
			exit(0);
		}
	}

	private function allowOrigins(): void
	{
		// настройте свои разрешенные хосты здесь.
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
			$response->header("Access-Control-Allow-Origin", $_SERVER['HTTP_ORIGIN']);
		}
	}
}

// index.php или где у вас находятся ваши маршруты
Flight::route('/users', function() {
	$users = Flight::db()->fetchAll('SELECT * FROM users');
	Flight::json($users);
})->addMiddleware(new CorsMiddleware());
```

## Заключение

Безопасность - это важно, и важно убедиться, что ваши веб-приложения безопасны. Flight предоставляет ряд функций, которые помогут вам обезопасить ваши веб-приложения, но важно всегда быть бдительным и удостовериться, что вы делаете все возможное, чтобы защитить данные ваших пользователей. Всегда предполагайте худшее и никогда не доверяйте вводу ваших пользователей. Всегда экранируйте вывод и используйте подготовленные операторы для предотвращения SQL-инъекций. Всегда используйте промежуточное программное обеспечение, чтобы защитить ваши маршруты от атак CSRF и CORS. Если вы сделаете все это, вы будете на пути к созданию безопасных веб-приложений.