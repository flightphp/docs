# Безопасность

Безопасность является важным аспектом веб-приложений. Вы хотите убедиться, что ваше приложение безопасно, и что данные ваших пользователей находятся в безопасности. Flight предоставляет ряд функций, которые помогут вам обезопасить ваши веб-приложения.

## Заголовки

HTTP-заголовки — один из самых простых способов защитить ваши веб-приложения. Вы можете использовать заголовки, чтобы предотвратить кликджекинг, XSS и другие атаки. Есть несколько способов добавить эти заголовки в ваше приложение.

Два отличных веб-сайта для проверки безопасности ваших заголовков — это [securityheaders.com](https://securityheaders.com/) и [observatory.mozilla.org](https://observatory.mozilla.org/).

### Добавить вручную

Вы можете вручную добавить эти заголовки, используя метод `header` объекта `Flight\Response`.
```php
// Установите заголовок X-Frame-Options для предотвращения кликджекинга
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Установите заголовок Content-Security-Policy для предотвращения XSS
// Примечание: этот заголовок может быть очень сложным, поэтому вам стоит
//  посмотреть примеры в интернете для вашего приложения
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Установите заголовок X-XSS-Protection для предотвращения XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Установите заголовок X-Content-Type-Options для предотвращения MIME sniffing
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Установите заголовок Referrer-Policy для контроля над тем, сколько информации передается
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Установите заголовок Strict-Transport-Security для принудительного использования HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Установите заголовок Permissions-Policy для контроля над тем, какие функции и API могут быть использованы
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Эти заголовки можно добавить в начало ваших файлов `bootstrap.php` или `index.php`.

### Добавить как фильтр

Вы также можете добавить их в фильтр/хуки, как показано ниже:

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

### Добавить как промежуточное ПО

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

// index.php или где угодно, где у вас есть ваши маршруты
// К вашему сведению, эта пустая строка группируется как глобальное промежуточное ПО для
// всех маршрутов. Конечно, вы можете сделать то же самое и добавить
// это только для конкретных маршрутов.
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// больше маршрутов
}, [ new SecurityHeadersMiddleware() ]);
```

## Подделка межсайтовых запросов (CSRF)

Подделка межсайтовых запросов (CSRF) — это тип атаки, при которой вредоносный веб-сайт может заставить браузер пользователя отправить запрос на ваш веб-сайт. Это может использоваться для выполнения действий на вашем сайте без ведома пользователя. Flight не предоставляет встроенного механизма защиты от CSRF, но вы можете легко реализовать свой собственный с помощью промежуточного ПО. 

### Настройка

Сначала вам нужно сгенерировать токен CSRF и сохранить его в сеансе пользователя. Вы можете использовать этот токен в ваших формах и проверять его, когда форма отправлена.

```php
// Сгенерируйте токен CSRF и сохраните его в сеансе пользователя
// (предполагая, что вы создали объект сеанса и прикрепили его к Flight)
// смотрите документацию по сеансам для получения дополнительной информации
Flight::register('session', \Ghostff\Session\Session::class);

// Вам нужно сгенерировать только один токен на сеанс (чтобы он работал 
// на нескольких вкладках и запросах для одного и того же пользователя)
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
// Примечание: Вид был сконфигурирован с Latte как движок представления
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

И теперь в ваших шаблонах Latte вы можете использовать функцию `csrf()`, чтобы вывести токен CSRF.

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
// Этот промежуточное ПО проверяет, является ли запрос POST-запросом, и если да, проверяет, действителен ли токен CSRF
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// захватите токен CSRF из значений формы
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Недействительный токен CSRF');
			// или для JSON ответа
			Flight::jsonHalt(['error' => 'Недействительный токен CSRF'], 403);
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
				Flight::halt(403, 'Недействительный токен CSRF');
			}
		}
	}
}

// index.php или где угодно, где у вас есть ваши маршруты
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// больше маршрутов
}, [ new CsrfMiddleware() ]);
```

## Межсайтовый скриптинг (XSS)

Межсайтовый скриптинг (XSS) — это тип атаки, при которой злонамеренный веб-сайт может внедрить код на ваш сайт. Большинство этих возможностей возникают из значений формы, которые заполняют ваши конечные пользователи. Вы **никогда** не должны доверять выводам от ваших пользователей! Всегда предполагает, что все они — лучшие хакеры в мире. Они могут внедрить вредоносный JavaScript или HTML на вашу страницу. Этот код может использоваться для кражи информации у ваших пользователей или выполнения действий на вашем веб-сайте. Используя класс представления Flight, вы можете легко экранировать вывод, чтобы предотвратить атаки XSS.

```php
// Предположим, что пользователь умен и пытается использовать это как свое имя
$name = '<script>alert("XSS")</script>';

// Это экранирует вывод
Flight::view()->set('name', $name);
// Это выведет: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Если вы используете что-то вроде Latte, зарегистрированное как ваш класс представления,
// это также будет автоматически экранировано.
Flight::view()->render('template', ['name' => $name]);
```

## SQL-инъекция

SQL-инъекция — это тип атаки, при которой злонамеренный пользователь может внедрить SQL-код в вашу базу данных. Это может использоваться для кражи информации из вашей базы данных или выполнения действий с вашей базой данных. Опять же, вы **никогда** не должны доверять входным данным от ваших пользователей! Всегда предполагает, что они преследуют свои цели. Вы можете использовать подготовленные выражения в ваших объектах `PDO`, чтобы предотвратить SQL-инъекции.

```php
// Предполагая, что у вас зарегистрирован Flight::db() как ваш объект PDO
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// Если вы используете класс PdoWrapper, это можно легко сделать в одной строке
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Вы можете сделать то же самое с объектом PDO с ? плейсхолдерами
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// Просто пообещайте, что вы никогда не будете делать что-то подобное...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// потому что что, если $username = "' OR 1=1; -- "; 
// После того, как запрос будет построен, он будет выглядеть так
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// Это выглядит странно, но это действительный запрос, который сработает. На самом деле,
// это очень распространенная атака SQL-инъекции, которая вернет всех пользователей.
```

## CORS

Обмен ресурсами между разными источниками (CORS) — это механизм, который позволяет запрашивать многие ресурсы (например, шрифты, JavaScript и т. д.) на веб-странице с другого домена, отличного от домена, с которого этот ресурс был получен. Flight не имеет встроенной функциональности, но это можно легко обработать с помощью хука, который будет вызван перед тем, как будет вызван метод `Flight::start()`.

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
		// настройте здесь ваши разрешенные хосты.
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

// index.php или где угодно, где у вас есть ваши маршруты
$CorsUtil = new CorsUtil();

// Это нужно выполнить до начала работы.
Flight::before('start', [ $CorsUtil, 'setupCors' ]);
```

## Обработка ошибок
Скрывайте чувствительную информацию об ошибках в производственной среде, чтобы избежать утечки данных злоумышленникам.

```php
// В вашем bootstrap.php или index.php

// в flightphp/skeleton это находится в app/config/config.php
$environment = ENVIRONMENT;
if ($environment === 'production') {
    ini_set('display_errors', 0); // Отключить отображение ошибок
    ini_set('log_errors', 1);     // Записывать ошибки
    ini_set('error_log', '/path/to/error.log');
}

// В ваших маршрутах или контроллерах
// Используйте Flight::halt() для контролируемых ответов на ошибки
Flight::halt(403, 'Доступ запрещен');
```

## Санитария ввода
Никогда не доверяйте вводу от пользователя. Убирайте нежелательные данные перед обработкой, чтобы предотвратить внедрение вредоносных данных.

```php
// Предположим, что есть запрос $_POST с $_POST['input'] и $_POST['email']

// Санировать строковый ввод
$clean_input = filter_var(Flight::request()->data->input, FILTER_SANITIZE_STRING);
// Санировать электронную почту
$clean_email = filter_var(Flight::request()->data->email, FILTER_SANITIZE_EMAIL);
```

## Хэширование паролей
Храните пароли в безопасности и аккуратно их проверяйте, используя встроенные функции PHP.

```php
$password = Flight::request()->data->password;
// Хэшировать пароль при хранении (например, во время регистрации)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Проверить пароль (например, во время входа в систему)
if (password_verify($password, $stored_hash)) {
    // Пароль совпадает
}
```

## Ограничение частоты
Защитите себя от атак грубой силы, ограничив частоту запросов с помощью кеша.

```php
// Предполагая, что у вас установлен и зарегистрирован flightphp/cache
// Использование flightphp/cache в промежуточном ПО
Flight::before('start', function() {
    $cache = Flight::cache();
    $ip = Flight::request()->ip;
    $key = "rate_limit_{$ip}";
    $attempts = (int) $cache->retrieve($key);
    
    if ($attempts >= 10) {
        Flight::halt(429, 'Слишком много запросов');
    }
    
    $cache->set($key, $attempts + 1, 60); // Сбросить через 60 секунд
});
```

## Заключение

Безопасность является важным аспектом, и важно убедиться, что ваши веб-приложения безопасны. Flight предоставляет ряд функций, которые помогут вам защитить ваши веб-приложения, но важно всегда быть бдительным и убедиться, что вы делаете все возможное, чтобы защитить данные своих пользователей. Всегда предполагайте худшее и никогда не доверяйте вводу от ваших пользователей. Всегда экранируйте вывод и используйте подготовленные выражения, чтобы предотвратить SQL-инъекции. Всегда используйте промежуточное ПО для защиты ваших маршрутов от атак CSRF и CORS. Если вы сделаете все это, вы будете на пути к созданию безопасных веб-приложений.