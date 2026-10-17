# Безопасность

## Обзор

Безопасность — это очень важно для веб-приложений. Вы должны убедиться, что ваше приложение защищено, а данные пользователей находятся в безопасности. Flight предоставляет ряд функций, которые помогут вам защитить ваши веб-приложения.

Официальный [скелет](https://github.com/flightphp/skeleton) также включает выделенный **`SECURITY.md`** и middleware для заголовков безопасности, чтобы [AI-инструменты для написания кода](/learn/ai) (и люди) имели специальное место для секретов, заголовков и правил XSS/SQL — отдельно от общего стиля кода в `AGENTS.md`.

## Понимание

Существует ряд распространённых угроз безопасности, о которых следует знать при создании веб-приложений. Некоторые из наиболее распространённых угроз включают:
- Межсайтовая подделка запроса (CSRF)
- Межсайтовый скриптинг (XSS)
- SQL-инъекция
- Межсайтовое совместное использование ресурсов (CORS)

[Шаблоны](/learn/templates) помогают с XSS, экранируя вывод по умолчанию (Twig и Latte делают это; используйте это преимущество). [Сессии](/awesome-plugins/session) могут помочь с CSRF, сохраняя CSRF-токен в сессии пользователя, как описано ниже. Использование подготовленных выражений с PDO — или помощников [SimplePdo](/learn/simple-pdo) — помогает предотвратить SQL-инъекции. CORS можно обработать с помощью простого хука перед вызовом `Flight::start()`.

Все эти методы работают вместе, чтобы помочь сохранить ваши веб-приложения в безопасности. Всегда следует помнить о необходимости изучать и понимать лучшие практики безопасности. Не просите AI-ассистента «отключить CSP» или ослабить заголовки только для того, чтобы страница загрузилась, не понимая компромиссов.

## Базовое использование

### Заголовки

HTTP-заголовки — один из самых простых способов защитить ваши веб-приложения. Вы можете использовать заголовки для предотвращения кликджекинга, XSS и других атак. Существует несколько способов добавить эти заголовки в ваше приложение.

Два отличных сайта для проверки безопасности ваших заголовков: [securityheaders.com](https://securityheaders.com/) и [observatory.mozilla.org](https://observatory.mozilla.org/). После настройки приведённого ниже кода вы легко сможете проверить, что ваши заголовки работают, с помощью этих двух сайтов.

Скелет включает `App\Middleware\SecurityHeadersMiddleware` (CSP с nonce для каждого запроса, параметры frame, HSTS и другое). Предпочтительно осознанно расширять его, а не отключать заголовки.

#### Добавление вручную

Вы можете вручную добавить эти заголовки, используя метод `header` объекта `Flight\Response`.
```php
// Устанавливаем заголовок X-Frame-Options для предотвращения кликджекинга
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Устанавливаем заголовок Content-Security-Policy для предотвращения XSS
// Примечание: этот заголовок может стать очень сложным, поэтому вам стоит
//  обратиться к примерам в интернете для вашего приложения
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Устанавливаем заголовок X-XSS-Protection для предотвращения XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Устанавливаем заголовок X-Content-Type-Options для предотвращения MIME-сниффинга
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Устанавливаем заголовок Referrer-Policy для управления объёмом передаваемой информации о реферере
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Устанавливаем заголовок Strict-Transport-Security для принудительного использования HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Устанавливаем заголовок Permissions-Policy для управления функциями и API
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Их можно добавить в начало ваших файлов `routes.php` или `index.php`.

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

#### Добавление в качестве middleware

Вы также можете добавить их как класс middleware, что даёт наибольшую гибкость в выборе маршрутов, к которым это применяется. В целом эти заголовки должны применяться ко всем HTML- и API-ответам.

Путь и пространство имён в стиле скелета (**регистр папок соответствует `App\Middleware`**):

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
		// Предпочтительно использовать CSP nonce из bootstrap, если у вас есть встроенные скрипты (скелет устанавливает csp_nonce)
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

// app/config/routes.php — пустая строка в group = глобальный middleware для всех маршрутов
use App\Middleware\SecurityHeadersMiddleware;
use flight\net\Router;

$router->group('', function (Router $router) {
	$router->get('/users', [ \App\Controller\UserController::class, 'getUsers' ]);
	// остальные маршруты
}, [SecurityHeadersMiddleware::class]);
```

В старых проектах всё ещё может использоваться `app/middlewares` и `app\middlewares`; это работает, если папки совпадают. Новые приложения-скелеты используют **`app/Middleware/`** и **`App\Middleware`**. См. [Автозагрузка](/learn/autoloading).

### Межсайтовая подделка запроса (CSRF)

Межсайтовая подделка запроса (CSRF) — это тип атаки, при которой вредоносный сайт может заставить браузер пользователя отправить запрос на ваш сайт. Это может быть использовано для выполнения действий на вашем сайте без ведома пользователя. Flight не предоставляет встроенного механизма защиты от CSRF, но вы можете легко реализовать собственный с помощью middleware.

#### Настройка

Сначала необходимо сгенерировать CSRF-токен и сохранить его в сессии пользователя. Затем вы можете использовать этот токен в своих формах и проверять его при отправке формы. Мы будем использовать плагин [flightphp/session](/awesome-plugins/session) для управления сессиями.

```php
// Генерируем CSRF-токен и сохраняем его в сессии пользователя
// (предполагается, что вы создали объект сессии и прикрепили его к Flight)
// см. документацию по сессиям для получения дополнительной информации
Flight::register('session', flight\Session::class);

// Вам нужно сгенерировать только один токен на сессию (так он будет работать
// на нескольких вкладках и запросах для одного и того же пользователя)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

##### Использование стандартного шаблона PHP Flight

```html
<!-- Используем CSRF-токен в вашей форме -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- остальные поля формы -->
</form>
```

##### Использование Twig (по умолчанию в скелете)

Зарегистрируйте функцию Twig или передавайте токен в каждое представление формы. Минимальный пример с глобальной переменной и полем формы:

```php
// При настройке Twig (например, services.php)
$twig->addGlobal('csrf_token', $app->session()->get('csrf_token'));
```

```html
{# app/views/form.twig #}
<form method="post">
	<input type="hidden" name="csrf_token" value="{{ csrf_token }}">
	{# остальные поля #}
</form>
```

##### Использование Latte

Вы также можете настроить собственную функцию для вывода CSRF-токена в ваших шаблонах Latte.

```php

Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// другие настройки...

	// Устанавливаем собственную функцию для вывода CSRF-токена
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
	<!-- остальные поля формы -->
</form>
```

#### Проверка CSRF-токена

Вы можете проверить CSRF-токен несколькими способами.

##### Middleware

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
	// остальные маршруты
}, [CsrfMiddleware::class]);
```

##### Событийные фильтры

```php
// Этот middleware проверяет, является ли запрос POST-запросом, и если да, проверяет действительность CSRF-токена
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

Межсайтовый скриптинг (XSS) — это тип атаки, при которой вредоносный ввод в форме может внедрить код на ваш сайт. Большинство таких возможностей исходят из значений форм, которые заполняют конечные пользователи. Вы **никогда** не должны доверять выводу от ваших пользователей! Всегда предполагайте, что все они — лучшие хакеры в мире. Они могут внедрить вредоносный JavaScript или HTML на вашу страницу. Этот код может быть использован для кражи информации у ваших пользователей или выполнения действий на вашем сайте. Используя класс представления Flight или шаблонизатор, такой как [Twig](/awesome-plugins/twig) или [Latte](/awesome-plugins/latte), вы можете легко экранировать вывод для предотвращения XSS-атак.

```php
// Предположим, что пользователь умный и пытается использовать это в качестве своего имени
$name = '<script>alert("XSS")</script>';

// Это экранирует вывод
Flight::view()->set('name', $name);
// Это выведет: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Twig (по умолчанию в скелете) и Latte автоматически экранируют вывод — предпочтительнее использовать их, а не сырой PHP echo
Flight::render('template', ['name' => $name]);
// Twig: {{ name }}  → экранировано
// Избегайте |raw / неэкранированный вывод, если контент не полностью доверенный
```

### SQL-инъекция

SQL-инъекция — это тип атаки, при которой вредоносный пользователь может внедрить SQL-код в вашу базу данных. Это может быть использовано для кражи информации из вашей базы данных или выполнения действий с базой данных. Опять же, вы **никогда** не должны доверять вводу от ваших пользователей! Всегда предполагайте, что они жаждут крови. Используйте подготовленные выражения — помощники [SimplePdo](/learn/simple-pdo) делают это путём по умолчанию.

```php
// Предполагается, что Flight::db() зарегистрирован как SimplePdo (или SimplePdo внедрён в контроллер)
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// SimplePdo (предпочтительно) — однострочники со связанными параметрами
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Та же идея с плейсхолдерами ?
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);
```

В контроллерах в стиле скелета предпочтительно внедрение `SimplePdo` через конструктор, а не `Flight::db()`, чтобы тесты и AI-сгенерированный код оставались согласованными ([DIC](/learn/dependency-injection-container)).

#### Небезопасный пример

Ниже показано, почему мы используем SQL-подготовленные выражения для защиты от таких невинных примеров:

```php
// конечный пользователь заполняет веб-форму.
// в поле формы хакер вводит что-то вроде этого:
$username = "' OR 1=1; -- ";

$sql = "SELECT * FROM users WHERE username = '$username' LIMIT 5";
$users = Flight::db()->fetchAll($sql);
// После построения запроса он выглядит так
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5

// Это выглядит странно, но это допустимый запрос, который будет работать. На самом деле,
// это очень распространённая SQL-инъекция, которая вернёт всех пользователей.

var_dump($users); // этот код выведет всех пользователей в базе данных, а не только одного с указанным именем
```

### Секреты и конфигурация

- Помещайте секреты в **`.env`** (или в реальное окружение), а не в примеры `config.php`, которые попадают в репозиторий.
- Правило скелета: литеральные значения по умолчанию в `config.php`; объединяйте с окружением