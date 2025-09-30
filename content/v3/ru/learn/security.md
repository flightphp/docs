# Безопасность

## Обзор

Безопасность имеет большое значение для веб-приложений. Вы хотите убедиться, что ваше приложение защищено и данные ваших пользователей в безопасности. Flight предоставляет ряд функций, которые помогут вам обезопасить ваши веб-приложения.

## Понимание

Существует ряд распространенных угроз безопасности, о которых вы должны знать при создании веб-приложений. Некоторые из наиболее распространенных угроз включают:
- Межсайтовый запрос подделки (CSRF)
- Межсайтовый скриптинг (XSS)
- Инъекция SQL
- Кросс-доменное совместное использование ресурсов (CORS)

[Шаблоны](/learn/templates) помогают с XSS, экранируя вывод по умолчанию, чтобы вам не приходилось об этом помнить. [Сессии](/awesome-plugins/session) могут помочь с CSRF, храня токен CSRF в сессии пользователя, как описано ниже. Использование подготовленных запросов с PDO может помочь предотвратить атаки инъекции SQL (или использование удобных методов в классе [PdoWrapper](/learn/pdo-wrapper)). CORS можно обрабатывать с помощью простого хука перед вызовом `Flight::start()`.

Все эти методы работают вместе, чтобы помочь сохранить ваши веб-приложения в безопасности. Всегда держите в уме изучение и понимание лучших практик безопасности.

## Основное использование

### Заголовки

HTTP-заголовки — один из самых простых способов обезопасить ваши веб-приложения. Вы можете использовать заголовки для предотвращения clickjacking, XSS и других атак. Существует несколько способов добавить эти заголовки в ваше приложение.

Два отличных сайта для проверки безопасности ваших заголовков — [securityheaders.com](https://securityheaders.com/) и [observatory.mozilla.org](https://observatory.mozilla.org/). После настройки кода ниже вы можете легко проверить, что ваши заголовки работают, с помощью этих двух сайтов.

#### Добавление вручную

Вы можете вручную добавить эти заголовки, используя метод `header` объекта `Flight\Response`.
```php
// Установка заголовка X-Frame-Options для предотвращения clickjacking
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Установка заголовка Content-Security-Policy для предотвращения XSS
// Примечание: этот заголовок может быть очень сложным, поэтому вы захотите
//  проконсультироваться с примерами в интернете для вашего приложения
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Установка заголовка X-XSS-Protection для предотвращения XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Установка заголовка X-Content-Type-Options для предотвращения MIME sniffing
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Установка заголовка Referrer-Policy для контроля количества отправляемой информации о реферере
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Установка заголовка Strict-Transport-Security для принудительного использования HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Установка заголовка Permissions-Policy для контроля используемых функций и API
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Эти заголовки можно добавить в начало ваших файлов `routes.php` или `index.php`.

#### Добавление как фильтр

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

#### Добавление как middleware

Вы также можете добавить их как класс middleware, что обеспечивает наибольшую гибкость для выбора маршрутов, к которым это применяется. В общем случае эти заголовки должны применяться ко всем HTML- и API-ответам.

```php
// app/middlewares/SecurityHeadersMiddleware.php

namespace app\middlewares;

use flight\Engine;

class SecurityHeadersMiddleware
{
	protected Engine $app;

	public function __construct(Engine $app)
	{
		$this->app = $app;
	}

	public function before(array $params): void
	{
		$response = $this->app->response();
		$response->header('X-Frame-Options', 'SAMEORIGIN');
		$response->header("Content-Security-Policy", "default-src 'self'");
		$response->header('X-XSS-Protection', '1; mode=block');
		$response->header('X-Content-Type-Options', 'nosniff');
		$response->header('Referrer-Policy', 'no-referrer-when-downgrade');
		$response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
		$response->header('Permissions-Policy', 'geolocation=()');
	}
}

// index.php или где у вас есть маршруты
// К сведению, эта пустая строка группы действует как глобальный middleware для
// всех маршрутов. Конечно, вы можете сделать то же самое и добавить
// это только к конкретным маршрутам.
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// больше маршрутов
}, [ SecurityHeadersMiddleware::class ]);
```

### Межсайтовый запрос подделки (CSRF)

Межсайтовый запрос подделки (CSRF) — это тип атаки, при которой вредоносный сайт может заставить браузер пользователя отправить запрос на ваш сайт. Это может быть использовано для выполнения действий на вашем сайте без ведома пользователя. Flight не предоставляет встроенный механизм защиты от CSRF, но вы можете легко реализовать свой собственный, используя middleware.

#### Настройка

Сначала вам нужно сгенерировать токен CSRF и сохранить его в сессии пользователя. Затем вы можете использовать этот токен в ваших формах и проверить его при отправке формы. Мы будем использовать плагин [flightphp/session](/awesome-plugins/session) для управления сессиями.

```php
// Генерация токена CSRF и сохранение его в сессии пользователя
// (предполагая, что вы создали объект сессии и прикрепили его к Flight)
// см. документацию по сессиям для получения дополнительной информации
Flight::register('session', flight\Session::class);

// Вам нужно генерировать только один токен на сессию (чтобы он работал 
// в нескольких вкладках и запросах для одного пользователя)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

##### Использование стандартного шаблона PHP Flight

```html
<!-- Использование токена CSRF в вашей форме -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- другие поля формы -->
</form>
```

##### Использование Latte

Вы также можете установить пользовательскую функцию для вывода токена CSRF в ваших шаблонах Latte.

```php

Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// другие конфигурации...

	// Установка пользовательской функции для вывода токена CSRF
	$latte->addFunction('csrf', function() {
		$csrfToken = Flight::session()->get('csrf_token');
		return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
	});

	$latte->render($finalPath, $data, $block);
});
```

И теперь в ваших шаблонах Latte вы можете использовать функцию `csrf()` для вывода токена CSRF.

```html
<form method="post">
	{csrf()}
	<!-- другие поля формы -->
</form>
```

#### Проверка токена CSRF

Вы можете проверить токен CSRF с помощью нескольких методов.

##### Middleware

```php
// app/middlewares/CsrfMiddleware.php

namespace app\middleware;

use flight\Engine;

class CsrfMiddleware
{
	protected Engine $app;

	public function __construct(Engine $app)
	{
		$this->app = $app;
	}

	public function before(array $params): void
	{
		if($this->app->request()->method == 'POST') {
			$token = $this->app->request()->data->csrf_token;
			if($token !== $this->app->session()->get('csrf_token')) {
				$this->app->halt(403, 'Invalid CSRF token');
			}
		}
	}
}

// index.php или где у вас есть маршруты
use app\middlewares\CsrfMiddleware;

Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// больше маршрутов
}, [ CsrfMiddleware::class ]);
```

##### Фильтры событий

```php
// Этот middleware проверяет, является ли запрос POST-запросом, и если да, проверяет, действителен ли токен CSRF
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// захват токена CSRF из значений формы
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Invalid CSRF token');
			// или для JSON-ответа
			Flight::jsonHalt(['error' => 'Invalid CSRF token'], 403);
		}
	}
});
```

### Межсайтовый скриптинг (XSS)

Межсайтовый скриптинг (XSS) — это тип атаки, при которой вредоносный ввод формы может внедрить код в ваш сайт. Большинство таких возможностей возникает из значений форм, которые заполнят конечные пользователи. Вы **никогда** не должны доверять выводу от ваших пользователей! Всегда предполагайте, что все они — лучшие хакеры в мире. Они могут внедрить вредоносный JavaScript или HTML в вашу страницу. Этот код может быть использован для кражи информации от ваших пользователей или выполнения действий на вашем сайте. Используя класс view Flight или другой движок шаблонов, такой как [Latte](/awesome-plugins/latte), вы можете легко экранировать вывод для предотвращения атак XSS.

```php
// Предположим, пользователь хитрый и пытается использовать это как свое имя
$name = '<script>alert("XSS")</script>';

// Это экранирует вывод
Flight::view()->set('name', $name);
// Это выведет: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Если вы используете что-то вроде Latte, зарегистрированное как ваш класс view, оно также автоматически экранирует это.
Flight::view()->render('template', ['name' => $name]);
```

### Инъекция SQL

Инъекция SQL — это тип атаки, при которой вредоносный пользователь может внедрить SQL-код в вашу базу данных. Это может быть использовано для кражи информации из вашей базы данных или выполнения действий в вашей базе данных. Снова вы **никогда** не должны доверять вводу от ваших пользователей! Всегда предполагайте, что они жаждут крови. Вы можете использовать подготовленные запросы в ваших объектах `PDO`, чтобы предотвратить инъекцию SQL.

```php
// Предполагая, что у вас зарегистрирован Flight::db() как ваш объект PDO
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// Если вы используете класс PdoWrapper, это можно легко сделать в одну строку
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Вы можете сделать то же самое с объектом PDO с плейсхолдерами ?
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);
```

#### Небезопасный пример

Ниже показано, почему мы используем подготовленные SQL-запросы для защиты от невинных примеров, таких как ниже:

```php
// конечный пользователь заполняет веб-форму.
// для значения формы хакер вводит что-то вроде этого:
$username = "' OR 1=1; -- ";

$sql = "SELECT * FROM users WHERE username = '$username' LIMIT 5";
$users = Flight::db()->fetchAll($sql);
// После построения запроса он выглядит так
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5

// Это выглядит странно, но это действительный запрос, который сработает. На самом деле,
// это очень распространенная атака инъекции SQL, которая вернет всех пользователей.

var_dump($users); // это выведет всех пользователей в базе данных, а не только одного с конкретным именем пользователя
```

### CORS

Кросс-доменное совместное использование ресурсов (CORS) — это механизм, который позволяет запрашивать многие ресурсы (например, шрифты, JavaScript и т.д.) на веб-странице с другого домена, отличного от домена, с которого произошел ресурс. Flight не имеет встроенной функциональности, но это можно легко обработать с помощью хука, выполняемого перед методом `Flight::start()`.

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
		// настройте здесь разрешенные хосты.
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

// index.php или где у вас есть маршруты
$CorsUtil = new CorsUtil();

// Это должно выполняться перед запуском start.
Flight::before('start', [ $CorsUtil, 'setupCors' ]);
```

### Обработка ошибок
Скрывайте чувствительные детали ошибок в производстве, чтобы избежать утечки информации атакующим. В производстве логируйте ошибки вместо их отображения с `display_errors`, установленным в `0`.

```php
// В вашем bootstrap.php или index.php

// добавьте это в app/config/config.php
$environment = ENVIRONMENT;
if ($environment === 'production') {
    ini_set('display_errors', 0); // Отключить отображение ошибок
    ini_set('log_errors', 1);     // Логировать ошибки вместо этого
    ini_set('error_log', '/path/to/error.log');
}

// В ваших маршрутах или контроллерах
// Используйте Flight::halt() для контролируемых ответов на ошибки
Flight::halt(403, 'Access denied');
```

### Санитизация ввода
Никогда не доверяйте вводу пользователя. Санитизируйте его с помощью [filter_var](https://www.php.net/manual/en/function.filter-var.php) перед обработкой, чтобы предотвратить проникновение вредоносных данных.

```php

// Предположим, POST-запрос с $_POST['input'] и $_POST['email']

// Санитизация строкового ввода
$clean_input = filter_var(Flight::request()->data->input, FILTER_SANITIZE_STRING);
// Санитизация email
$clean_email = filter_var(Flight::request()->data->email, FILTER_SANITIZE_EMAIL);
```

### Хеширование паролей
Храните пароли безопасно и проверяйте их безопасно с помощью встроенных функций PHP, таких как [password_hash](https://www.php.net/manual/en/function.password-hash.php) и [password_verify](https://www.php.net/manual/en/function.password-verify.php). Пароли никогда не должны храниться в открытом виде, а также не должны шифроваться обратимыми методами. Хеширование обеспечивает, что даже если ваша база данных скомпрометирована, фактические пароли остаются защищенными.

```php
$password = Flight::request()->data->password;
// Хеширование пароля при хранении (например, во время регистрации)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Проверка пароля (например, во время входа)
if (password_verify($password, $stored_hash)) {
    // Пароль совпадает
}
```

### Ограничение скорости
Защищайте от атак brute force или атак отказа в обслуживании, ограничивая скорость запросов с помощью кэша.

```php
// Предполагая, что у вас установлен и зарегистрирован flightphp/cache
// Использование flightphp/cache в фильтре
Flight::before('start', function() {
    $cache = Flight::cache();
    $ip = Flight::request()->ip;
    $key = "rate_limit_{$ip}";
    $attempts = (int) $cache->retrieve($key);
    
    if ($attempts >= 10) {
        Flight::halt(429, 'Too many requests');
    }
    
    $cache->set($key, $attempts + 1, 60); // Сброс через 60 секунд
});
```

## См. также
- [Сессии](/awesome-plugins/session) - Как безопасно управлять сессиями пользователей.
- [Шаблоны](/learn/templates) - Использование шаблонов для автоматического экранирования вывода и предотвращения XSS.
- [PDO Wrapper](/learn/pdo-wrapper) - Упрощенные взаимодействия с базой данных с подготовленными запросами.
- [Middleware](/learn/middleware) - Как использовать middleware для упрощения процесса добавления заголовков безопасности.
- [Ответы](/learn/responses) - Как настраивать HTTP-ответы с безопасными заголовками.
- [Запросы](/learn/requests) - Как обрабатывать и санитизировать ввод пользователя.
- [filter_var](https://www.php.net/manual/en/function.filter-var.php) - Функция PHP для санитизации ввода.
- [password_hash](https://www.php.net/manual/en/function.password-hash.php) - Функция PHP для безопасного хеширования паролей.
- [password_verify](https://www.php.net/manual/en/function.password-verify.php) - Функция PHP для проверки хешированных паролей.

## Устранение неисправностей
- Обращайтесь к разделу "См. также" выше для информации по устранению неисправностей, связанной с проблемами компонентов фреймворка Flight.

## Журнал изменений
- v3.1.0 - Добавлены разделы о CORS, обработке ошибок, санитизации ввода, хешировании паролей и ограничении скорости.
- v2.0 - Добавлено экранирование для стандартных представлений для предотвращения XSS.