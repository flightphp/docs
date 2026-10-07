# Безопасность

## Обзор

Безопасность — это очень важный аспект веб-приложений. Вы должны убедиться, что ваше приложение защищено, а данные ваших пользователей 
находятся в безопасности. Flight предоставляет ряд функций, которые помогут вам защитить ваши веб-приложения.

Официальный [скелетон](https://github.com/flightphp/skeleton) также поставляется с dedicated-файлом **`SECURITY.md`** и промежуточным ПО для security-заголовков, чтобы [AI-инструменты для кода](/learn/ai) (и люди) имели одно продуманное место для секретов, заголовков и правил XSS/SQL — отдельно от общего стиля кода в `AGENTS.md`.

## Понимание

Существует ряд распространённых угроз безопасности, о которых вам следует знать при создании веб-приложений. Некоторые из наиболее распространённых угроз включают:
- Подделка межсайтовых запросов (CSRF)
- Межсайтовый скриптинг (XSS)
- SQL-инъекции
- Обмен ресурсами между разными источниками (CORS)

[Шаблоны](/learn/templates) помогают с XSS, экранируя вывод по умолчанию (Twig и Latte делают это; используйте это преимущество). [Сессии](/awesome-plugins/session) могут помочь с CSRF, сохраняя CSRF-токен в сессии пользователя, как описано ниже. Использование подготовленных выражений с PDO — или помощников из [SimplePdo](/learn/simple-pdo) — помогает предотвратить SQL-инъекции. CORS можно обработать с помощью простого хука перед вызовом `Flight::start()`.

Все эти методы работают вместе, чтобы помочь сохранить ваши веб-приложения в безопасности. Изучение и понимание лучших практик безопасности всегда должно быть на переднем плане вашего мышления. Не просите AI-ассистента «отключить CSP» или ослабить заголовки только для того, чтобы страница загрузилась, не понимая компромиссов.

## Базовое использование

### Заголовки

HTTP-заголовки — один из самых простых способов защитить ваши веб-приложения. Вы можете использовать заголовки для предотвращения кликджекинга, XSS и других атак.
Существует несколько способов добавить эти заголовки в ваше приложение.

Два отличных сайта для проверки безопасности ваших заголовков — [securityheaders.com](https://securityheaders.com/) и 
[observatory.mozilla.org](https://observatory.mozilla.org/). После настройки приведённого ниже кода вы легко сможете проверить, что ваши заголовки работают, с помощью этих двух сайтов.

Скелетон включает `App\Middleware\SecurityHeadersMiddleware` (CSP с nonce для каждого запроса, frame options, HSTS и другое). Предпочитайте осознанное расширение этого класса вместо полного отключения заголовков.

#### Добавление вручную

Вы можете добавить эти заголовки вручную, используя метод `header` объекта `Flight\Response`.
```php
// Устанавливаем заголовок X-Frame-Options для предотвращения кликджекинга
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Устанавливаем заголовок Content-Security-Policy для предотвращения XSS
// Примечание: этот заголовок может стать очень сложным, поэтому вам стоит
//  посмотреть примеры в интернете для вашего приложения
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Устанавливаем заголовок X-XSS-Protection для предотвращения XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Устанавливаем заголовок X-Content-Type-Options для предотвращения MIME-сниффинга
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Устанавливаем заголовок Referrer-Policy для контроля объёма передаваемой referrer-информации
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Устанавливаем заголовок Strict-Transport-Security для принудительного использования HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Устанавливаем заголовок Permissions-Policy для контроля того, какие функции и API можно использовать
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Их можно добавить в начале ваших файлов `routes.php` или `index.php`.

#### Добавление в качестве фильтра

Вы также можете добавить их в фильтр/хук следующим образом:

```php
// Добавляем заголовки в фильтре
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

#### Добавление в качестве промежуточного ПО

Вы также можете добавить их в виде класса промежуточного ПО, что даёт наибольшую гибкость в выборе маршрутов, к которым это применяется. В целом эти заголовки должны применяться ко всем HTML- и API-ответам.

Путь и пространство имён в стиле скелетона (**регистр папки соответствует `App\Middleware`**):

```php
// app/Middleware/SecurityHeadersMiddleware.php

namespace App\Middleware;

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
		// Предпочитайте CSP nonce из bootstrap, если у вас есть инлайн-скрипты (скелетон задаёт csp_nonce)
		$nonce = $this->app->get('csp_nonce');
		$csp = $nonce
			? "default-src 'self'; script-src 'self' 'nonce-{$nonce}'; style-src 'self' 'nonce-{$nonce}'"
			: "default-src 'self'";

		$response->header('X-Frame-Options', 'SAMEORIGIN');
		$response->header('Content-Security-Policy', $csp);
		$response->header('X-XSS-Protection', '1; mode=block');
		$response->header('X-Content-Type-Options', 'nosniff');
		$response->header('Referrer-Policy', 'no-referrer-when-downgrade');
		$response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
		$response->header('Permissions-Policy', 'geolocation=()');
	}
}

// app/config/routes.php — группа с пустой строкой = глобальное промежуточное ПО для всех маршрутов
use App\Middleware\SecurityHeadersMiddleware;
use flight\net\Router;

$router->group('', function (Router $router) {
	$router->get('/users', [ \App\Controller\UserController::class, 'getUsers' ]);
	// дополнительные маршруты
}, [SecurityHeadersMiddleware::class]);
```

В более старых проектах всё ещё может использоваться `app/middlewares` и `app\middlewares`; это работает, если папки совпадают. В новых приложениях-скелетонах используется **`app/Middleware/`** и **`App\Middleware`**. См. [Автозагрузка](/learn/autoloading).

### Подделка межсайтовых запросов (CSRF)

Подделка межсайтовых запросов (CSRF) — это тип атаки, при которой вредоносный сайт может заставить браузер пользователя отправить запрос на ваш сайт.
Это можно использовать для выполнения действий на вашем сайте без ведома пользователя. Flight не предоставляет встроенного механизма защиты от CSRF,
но вы можете легко реализовать свой собственный с помощью промежуточного ПО.

#### Настройка

Сначала вам нужно сгенерировать CSRF-токен и сохранить его в сессии пользователя. Затем вы можете использовать этот токен в своих формах и проверять его при отправке формы. Мы будем использовать плагин [flightphp/session](/awesome-plugins/session) для управления сессиями.

```php
// Генерируем CSRF-токен и сохраняем его в сессии пользователя
// (предполагая, что вы создали объект сессии и прикрепили его к Flight)
// см. документацию по сессиям для получения дополнительной информации
Flight::register('session', flight\Session::class);

// Вам нужно генерировать только один токен за сессию (так он работает
// в нескольких вкладках и для нескольких запросов одного пользователя)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

##### Использование стандартного PHP-шаблона Flight

```html
<!-- Используем CSRF-токен в вашей форме -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- другие поля формы -->
</form>
```

##### Использование Twig (по умолчанию в скелетоне)

Зарегистрируйте функцию Twig или передавайте токен в каждое представление формы. Минимальный пример с глобальной переменной + полем формы:

```php
// При настройке Twig (например, services.php)
$twig->addGlobal('csrf_token', $app->session()->get('csrf_token'));
```

```html
{# app/views/form.twig #}
<form method="post">
	<input type="hidden" name="csrf_token" value="{{ csrf_token }}">
	{# другие поля #}
</form>
```

##### Использование Latte

Вы также можете задать пользовательскую функцию для вывода CSRF-токена в ваших шаблонах Latte.

```php

Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// другие настройки...

	// Устанавливаем пользовательскую функцию для вывода CSRF-токена
	$latte->addFunction('csrf', function() {
		$csrfToken = Flight::session()->get('csrf_token');
		return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
	});

	$latte->render($finalPath, $data, $block);
});
```

Теперь в ваших шаблонах Latte вы можете использовать функцию `csrf()` для вывода CSRF-токена.

```html
<form method="post">
	{csrf()}
	<!-- другие поля формы -->
</form>
```

#### Проверка CSRF-токена

Вы можете проверить CSRF-токен несколькими способами.

##### Промежуточное ПО

```php
// app/Middleware/CsrfMiddleware.php

namespace App\Middleware;

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

// routes.php
use App\Middleware\CsrfMiddleware;

$router->group('', function ($router) {
	$router->get('/users', [ \App\Controller\UserController::class, 'getUsers' ]);
	// дополнительные маршруты
}, [CsrfMiddleware::class]);
```

##### Событийные фильтры

```php
// Это промежуточное ПО проверяет, является ли запрос POST-запросом, и если да, проверяет, действителен ли CSRF-токен
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// получаем csrf-токен из значений формы
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

Межсайтовый скриптинг (XSS) — это тип атаки, при которой вредоносный ввод в форму может внедрить код на ваш сайт. Большинство таких возможностей исходят
из значений формы, которые заполняют ваши конечные пользователи. Вам **никогда** не следует доверять выводу от пользователей! Всегда предполагайте, что все они —
лучшие хакеры в мире. Они могут внедрить вредоносный JavaScript или HTML на вашу страницу. Этот код может быть использован для кражи информации у ваших
пользователей или для выполнения действий на вашем сайте. Используя класс представления Flight или шаблонизатор, такой как [Twig](/awesome-plugins/twig) или [Latte](/awesome-plugins/latte), вы можете легко экранировать вывод для предотвращения XSS-атак.

```php
// Предположим, что пользователь умён и пытается использовать это в качестве своего имени
$name = '<script>alert("XSS")</script>';

// Это экранирует вывод
Flight::view()->set('name', $name);
// Это выведет: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Twig (по умолчанию в скелетоне) и Latte автоматически экранируют по умолчанию — предпочитайте их прямому echo в PHP
Flight::render('template', ['name' => $name]);
// Twig: {{ name }}  → экранировано
// Избегайте |raw / не экранированного вывода, если контент полностью доверенный
```

### SQL-инъекции

SQL-инъекция — это тип атаки, при которой вредоносный пользователь может внедрить SQL-код в вашу базу данных. Это может быть использовано для кражи информации
из вашей базы данных или для выполнения действий с вашей базой данных. Опять же, вам **никогда** не следует доверять вводу от пользователей! Всегда предполагайте, что они
жаждут крови. Используйте подготовленные выражения — помощники [SimplePdo](/learn/simple-pdo) делают это путём по умолчанию.

```php
// Предполагая, что у вас зарегистрирован Flight::db() как SimplePdo (или внедрён SimplePdo в контроллер)
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// SimplePdo (предпочтительно) — однострочники со связанными параметрами
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Та же идея с плейсхолдерами ?
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);
```

В контроллерах в стиле скелетона предпочитайте внедрение `SimplePdo` через конструктор вместо `Flight::db()`, чтобы тесты и AI-сгенерированный код оставались согласованными ([DIC](/learn/dependency-injection-container)).

#### Небезопасный пример

Ниже показано, почему мы используем подготовленные SQL-выражения для защиты от безобидных на первый взгляд примеров:

```php
// конечный пользователь заполняет веб-форму.
// в качестве значения формы хакер вводит что-то вроде этого:
$username = "' OR 1=1; -- ";

$sql = "SELECT * FROM users WHERE username = '$username' LIMIT 5";
$users = Flight::db()->fetchAll($sql);
// После построения запроса это выглядит так
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5

// Выглядит странно, но это валидный запрос, который сработает. На самом деле,
// это очень распространённая SQL-инъекция, которая вернёт всех пользователей.

var_dump($users); // этот вызов выведет всех пользователей в базе данных, а не только одно имя пользователя
```

### Секреты и конфигурация

- Помещайте секреты в **`.env`** (или в реальное окружение), а не в закоммиченные примеры `config.php`.
- Правило скелетона: литеральные значения по умолчанию в `config.php`; объединяйте с env в bootstrap; **не** читайте `$_ENV` внутри контроллеров — вместо этого внедряйте конфигурацию. См. [Конфигурация](/learn/configuration).
- Никогда не коммитьте API-ключи, пароли БД или ключи шифрования сессий. Указывайте AI-инструментам на **`SECURITY.md`**, чтобы они не изобретали небезопасные обходные пути.

### Проверка JSONP-обратного вызова

Если вы используете метод `Flight::jsonp()`, имейте в виду, что Flight проверяет имя параметра обратного вызова JSONP на соответствие строгому разрешённому шаблону regex (`/^[A-Za-z_$][\w$.]{0,127}$/`). Любое имя обратного вызова, не соответствующее этому шаблону, приведёт к исключению, тем самым предотвращая внедрение произвольного JavaScript через вредоносное значение обратного вызова.

Эта проверка встроена и не требует дополнительной настройки, но о ней полезно знать при отладке неожиданных ошибок от JSONP-конечных точек.

### CORS

Обмен ресурсами между разными источниками (CORS) — это механизм, который позволяет запрашивать многие ресурсы (например, шрифты, JavaScript и т. д.) на веб-странице
с другого домена, отличного от того, с которого изначально был получен ресурс. Flight не имеет встроенной функциональности,
но это легко обрабатывается с помощью хука, который запускается перед вызовом метода `Flight::start()`.

```php
// app/Utils/CorsUtil.php  (скелетон: папка Utils в PascalCase → App\Utils)

namespace App\Utils;

use flight\Engine;

class CorsUtil
{
	protected Engine $app;

	public function __construct(Engine $app)
	{
		$this->app = $app;
	}

	public function set(array $params = []): void
	{
		$request = $this->app->request();
		$response = $this->app->response();
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
		// настройте здесь ваши разрешённые хосты.
		$allowed = [
			'capacitor://localhost',
			'ionic://localhost',
			'http://localhost',
			'http://localhost:4200',
			'http://localhost:8080',
			'http://localhost:8100',
		];

		$request = $this->app->request();

		if (in_array($request->getVar('HTTP_ORIGIN'), $allowed, true) === true) {
			$response = $this->app->response();
			$response->header("Access-Control-Allow-Origin", $request->getVar('HTTP_ORIGIN'));
		}
	}
}

// bootstrap / routes — выполняется перед start
$app = Flight::app();
$cors = new \App\Utils\CorsUtil($app);
$app->before('start', [ $cors, 'set' ]);
```

### Усиление конфигурации Flight

Flight предоставляет несколько настроек движка, которые имеют прямое отношение к безопасности. Правильная их установка — один из самых простых способов усилить ваше приложение.

#### `flight.allow_method_override`

По умолчанию Flight позволяет клиентам переопределять HTTP-метод запроса с помощью заголовка `X-HTTP-Method-Override` или поля `_method` в теле POST-запроса. Хотя это удобно для HTML-форм, которые могут отправлять только `GET`/`POST`, это может быть опасно, если вы этого не ожидаете — злоумышленник может подделать запросы `DELETE` или `PUT` через обычную форму.

Если ваше приложение не полагается на это поведение (например, вы создаёте API, потребляемое современными клиентами или JavaScript-фронтендами, которые могут отправлять любой HTTP-глагол), вам следует отключить его:

```php
// В вашем index.php или bootstrap-файле, перед Flight::start()
Flight::set('flight.allow_method_override', false);
```

Значение по умолчанию — `true` для обратной совместимости, но **настоятельно рекомендуется установить `false`** для любого приложения, которое явно не нуждается в функции переопределения.

#### `flight.debug`

У Flight есть настройка `flight.debug`, которая управляет отображением подробной информации об ошибках (сообщение исключения, код и полный стек вызовов) в браузере при возникновении необработанного исключения. По умолчанию `false`, что означает показ только общего сообщения `500 Internal Server Error` — никакие внутренние детали не утекают клиенту.

Никогда не включайте это на производственном сервере. Используйте это только локально или в промежуточной среде (staging):

```php
// Безопасно только для локальной разработки — НИКОГДА в продакшене
Flight::set('flight.debug', true);
```

Когда `flight.debug` равен `false` (по умолчанию), вы всё равно можете перехватывать ошибки, включив `flight.log_errors`:

```php
// Логируйте ошибки на стороне сервера, не раскрывая их клиенту
Flight::set('flight.debug', false);
Flight::set('flight.log_errors', true);
```

#### `flight.views.restrict_to_path`

Встроенный класс `View` Flight с удовольствием подключит шаблон по абсолютному пути или по относительному имени, которое выходит за пределы `flight.views.path` (например, с помощью `../`). Это сделано намеренно для приложений, которые осознанно используют общие шаблоны в разных папках, но это также риск path-traversal, если имя шаблона когда-либо приходит из недоверенного ввода.

`flight.views.restrict_to_path` **отключен по умолчанию**, чтобы существующие приложения продолжали работать. Включите его, если у вас нет документально подтверждённой причины не делать этого:

```php
// В вашем index.php или bootstrap-файле, перед Flight::start()
Flight::set('flight.views.restrict_to_path', true);
```

Движок применяет эту настройку к `View::$restrictToPath` при создании представления (тот же шаблон, что и `flight.views.path` и `flight.views.extension`). При включённой опции:

- Методы `render()` и `fetch()` подключают только те файлы, чей реальный путь находится внутри настроенной директории представлений (символические ссылки, ведущие наружу, тоже отклоняются).
- Метод `exists()` возвращает `false` для тех же путей вместо выбрасывания исключения.
- Метод `getTemplate()` сам по себе не меняется — он по-прежнему возвращает пути так же, как и раньше.
- Заблокированный файл вызывает исключение `Template file is outside the views path.` Отсутствующий файл по-прежнему вызывает существующее сообщение `Template file not found: ...`.

Если вы используете Twig или Latte с их собственными файловыми загрузчиками, указывающими на вашу директорию представлений, эти движки уже ограничивают шаблоны этим корнем. Всё равно включите эту настройку для нативного `View` Flight, чтобы любой код, вызывающий `Flight::view()->render()` / `fetch()`, получал такую же защиту. Официальный [скелетон](https://github.com/flightphp/skeleton) включает её в bootstrap.

#### Рекомендуемая производственная конфигурация

```php
// index.php или применяется из конфигурации приложения / bootstrap
Flight::set('flight.allow_method_override', false);
Flight::set('flight.debug', false);
Flight::set('flight.log_errors', true);
Flight::set('flight.views.restrict_to_path', true);
```

### Обработка ошибок
Скрывайте чувствительные детали ошибок в продакшене, чтобы не раскрывать информацию злоумышленникам. В продакшене логируйте ошибки вместо их отображения, установив `display_errors` в `0`.

```php
// В вашем bootstrap.php или index.php

// добавьте это в ваш app/config/config.php
$environment = ENVIRONMENT;
if ($environment === 'production') {
    ini_set('display_errors', 0); // Отключаем отображение ошибок
    ini_set('log_errors', 1);     // Вместо этого логируем ошибки
    ini_set('error_log', '/path/to/error.log');
}

// В ваших маршрутах или контроллерах
// Используйте Flight::halt() для контролируемых ответов об ошибках
Flight::halt(403, 'Access denied');
```

### Санитизация ввода
Никогда не доверяйте пользовательскому вводу. Санитизируйте его с помощью [filter_var](https://www.php.net/manual/en/function.filter-var.php) перед обработкой, чтобы предотвратить проникновение вредоносных данных. Предпочитайте чтение ввода через `$app->request()` (или `Flight::request()`), а не через сырые `$_GET` / `$_POST` в коде приложения.

```php

// Предположим, есть $_POST запрос с $_POST['input'] и $_POST['email']

// Санитизируем строковый ввод
$clean_input = filter_var(Flight::request()->data->input, FILTER_SANITIZE_STRING);
// Санитизируем email
$clean_email = filter_var(Flight::request()->data->email, FILTER_SANITIZE_EMAIL);
```

### Хеширование паролей

Храните пароли безопасно и проверяйте их надёжно с помощью встроенных функций PHP, таких как [password_hash](https://www.php.net/manual/en/function.password-hash.php) и [password_verify](https://www.php.net/manual/en/function.password-verify.php). Пароли никогда не должны храниться в открытом виде и не должны шифроваться обратимыми методами. Хеширование гарантирует, что даже если ваша база данных будет скомпрометирована, фактические пароли останутся защищёнными.

```php
$password = Flight::request()->data->password;
// Хешируем пароль при сохранении (например, при регистрации)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Проверяем пароль (например, при входе в систему)
if (password_verify($password, $stored_hash)) {
    // Пароль совпадает
}
```

### Ограничение частоты запросов

Защититесь от атак перебором или отказов в обслуживании, ограничивая частоту запросов с помощью кеша.

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
    
    $cache->set($key, $attempts + 1, 60); // Сбрасываем через 60 секунд
});
```

## См. также
- [Сессии](/awesome-plugins/session) — как безопасно управлять пользовательскими сессиями.
- [Шаблоны](/learn/templates) — Twig/Latte auto-escape и XSS.
- [SimplePdo](/learn/simple-pdo) — помощники для базы данных с подготовленными выражениями.
- [PdoWrapper](/learn/pdo-wrapper) — устарело; используйте SimplePdo для нового кода.
- [Промежуточное ПО](/learn/middleware) — как использовать промежуточное ПО для упрощения добавления заголовков безопасности.
- [Конфигурация](/learn/configuration) — `.env` против литеральной конфигурации, производственные флаги.
- [AI и опыт разработчика](/learn/ai) — сохраняйте политику безопасности в `SECURITY.md` для агентов.
- [Ответы](/learn/responses) — как настраивать HTTP-ответы с безопасными заголовками.
- [Запросы](/learn/requests) — как обрабатывать и санитизировать пользовательский ввод.
- [filter_var](https://www.php.net/manual/en/function.filter-var.php) — PHP-функция для санитизации ввода.
- [password_hash](https://www.php.net/manual/en/function.password-hash.php) — PHP-функция для безопасного хеширования паролей.
- [password_verify](https://www.php.net/manual/en/function.password-verify.php) — PHP-функция для проверки хешированных паролей.

## Устранение неполадок
- Обратитесь к разделу «См. также» выше для получения информации об устранении неполадок, связанных с компонентами фреймворка Flight.
- Если CSP блокирует ваши скрипты, добавьте nonce (паттерн скелетона) или добавьте конкретные источники в белый список — не устанавливайте `script-src *` без плана.

## Журнал изменений
- Документация — скелетон `App\Middleware`, заметки Twig CSRF/XSS, SimplePdo, секреты/`.env` и `SECURITY.md` для AI-дружественных проектов.
- Документация — описана настройка `flight.views.restrict_to_path` в разделе «Усиление конфигурации Flight» (опциональное ограничение пути для нативных представлений).
- v3.18.1 — добавлен раздел «Усиление конфигурации Flight», охватывающий `flight.allow_method_override`, `flight.debug` и проверку JSONP-обратного вызова.
- v3.1.0 — добавлены разделы о CORS, обработке ошибок, санитизации ввода, хешировании паролей и ограничении частоты запросов.
- v2.0 — добавлено экранирование для представлений по умолчанию для предотвращения XSS.