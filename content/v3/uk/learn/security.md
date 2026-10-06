# Безпека

## Огляд

Безпека — це велика справа, коли йдеться про вебзастосунки. Ви хочете переконатися, що ваш застосунок захищений, а дані ваших користувачів у безпеці. Flight надає низку функцій, які допоможуть вам захистити ваші вебзастосунки.

Офіційний [skeleton](https://github.com/flightphp/skeleton) також постачається з окремим **`SECURITY.md`** і middleware для заголовків безпеки, щоб [AI-інструменти для кодування](/learn/ai) (і люди) мали одне визначене місце для секретів, заголовків і правил XSS/SQL — окремо від загального стилю кодування в `AGENTS.md`.

## Розуміння

Існує низка поширених загроз безпеці, про які вам варто знати під час створення вебзастосунків. Деякі з найпоширеніших загроз включають:
- Міжсайтова підробка запитів (CSRF)
- Міжсайтовий скриптинг (XSS)
- SQL-ін'єкція
- Обмін ресурсами між різними джерелами (CORS)

[Шаблони](/learn/templates) допомагають із XSS, екрануючи виведення за замовчуванням (Twig і Latte роблять це; скористайтеся цією перевагою). [Сесії](/awesome-plugins/session) можуть допомогти із CSRF, зберігаючи CSRF-токен у сесії користувача, як описано нижче. Використання підготовлених запитів із PDO — або хелперів у [SimplePdo](/learn/simple-pdo) — допомагає запобігти SQL-ін'єкціям. CORS можна обробити простим хуком перед викликом `Flight::start()`.

Усі ці методи працюють разом, щоб допомогти захистити ваші вебзастосунки. Вивчення та розуміння найкращих практик безпеки завжди має бути на першому плані. Не просіть AI-асистента «вимкнути CSP» або послабити заголовки лише для того, щоб сторінка завантажилася, без розуміння компромісу.

## Базове використання

### Заголовки

HTTP-заголовки — один із найпростіших способів захистити ваші вебзастосунки. Ви можете використовувати заголовки, щоб запобігти clickjacking, XSS та іншим атакам. Існує кілька способів додати ці заголовки до вашого застосунку.

Два чудові сайти для перевірки безпеки ваших заголовків — [securityheaders.com](https://securityheaders.com/) і [observatory.mozilla.org](https://observatory.mozilla.org/). Після налаштування наведеного нижче коду ви можете легко перевірити, що ваші заголовки працюють, за допомогою цих двох сайтів.

Skeleton містить `App\Middleware\SecurityHeadersMiddleware` (CSP з nonce для кожного запиту, параметри frame, HSTS та інше). Віддавайте перевагу свідомому розширенню цього, а не вимкненню заголовків.

#### Додати вручну

Ви можете вручну додати ці заголовки за допомогою методу `header` об'єкта `Flight\Response`.
```php
// Встановіть заголовок X-Frame-Options, щоб запобігти clickjacking
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Встановіть заголовок Content-Security-Policy, щоб запобігти XSS
// Примітка: цей заголовок може бути дуже складним, тому вам варто
//  звернутися до прикладів в інтернеті для вашого застосунку
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Встановіть заголовок X-XSS-Protection, щоб запобігти XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Встановіть заголовок X-Content-Type-Options, щоб запобігти MIME sniffing
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Встановіть заголовок Referrer-Policy, щоб контролювати, скільки інформації про referrer надсилається
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Встановіть заголовок Strict-Transport-Security, щоб примусово використовувати HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Встановіть заголовок Permissions-Policy, щоб контролювати, які функції та API можна використовувати
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Їх можна додати на початку ваших файлів `routes.php` або `index.php`.

#### Додати як фільтр

Ви також можете додати їх у фільтрі/хуку, як показано нижче:

```php
// Додайте заголовки у фільтрі
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

#### Додати як middleware

Ви також можете додати їх як клас middleware, що забезпечує найбільшу гнучкість щодо того, до яких маршрутів це застосовувати. Загалом ці заголовки слід застосовувати до всіх HTML- і API-відповідей.

Шлях і простір імен у стилі skeleton (**регістр папки відповідає `App\Middleware`**):

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
		// Віддавайте перевагу CSP nonce з bootstrap, коли у вас є inline-скрипти (skeleton встановлює csp_nonce)
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

// app/config/routes.php — порожній рядок групи = глобальний middleware для всіх маршрутів
use App\Middleware\SecurityHeadersMiddleware;
use flight\net\Router;

$router->group('', function (Router $router) {
	$router->get('/users', [ \App\Controller\UserController::class, 'getUsers' ]);
	// більше маршрутів
}, [SecurityHeadersMiddleware::class]);
```

Старіші проєкти можуть усе ще використовувати `app/middlewares` і `app\middlewares`; це працює, якщо папки збігаються. Нові skeleton-застосунки використовують **`app/Middleware/`** і **`App\Middleware`**. Дивіться [Автозавантаження](/learn/autoloading).

### Міжсайтова підробка запитів (CSRF)

Міжсайтова підробка запитів (CSRF) — це тип атаки, коли шкідливий вебсайт може змусити браузер користувача надіслати запит на ваш вебсайт. Це можна використати для виконання дій на вашому вебсайті без відома користувача. Flight не надає вбудованого механізму захисту від CSRF, але ви можете легко реалізувати власний за допомогою middleware.

#### Налаштування

Спочатку вам потрібно згенерувати CSRF-токен і зберегти його в сесії користувача. Потім ви можете використовувати цей токен у своїх формах і перевіряти його під час надсилання форми. Ми використаємо плагін [flightphp/session](/awesome-plugins/session) для керування сесіями.

```php
// Згенеруйте CSRF-токен і збережіть його в сесії користувача
// (припускаючи, що ви створили об'єкт сесії та прикріпили його до Flight)
// дивіться документацію щодо сесій для отримання додаткової інформації
Flight::register('session', flight\Session::class);

// Вам потрібно згенерувати лише один токен на сесію (так він працює
// у кількох вкладках і запитах для того самого користувача)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

##### Використання стандартного шаблону PHP Flight

```html
<!-- Використовуйте CSRF-токен у своїй формі -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- інші поля форми -->
</form>
```

##### Використання Twig (типово для skeleton)

Зареєструйте функцію Twig або передавайте токен у кожне подання форми. Мінімальний приклад із глобальною змінною + полем форми:

```php
// Під час налаштування Twig (наприклад, services.php)
$twig->addGlobal('csrf_token', $app->session()->get('csrf_token'));
```

```html
{# app/views/form.twig #}
<form method="post">
	<input type="hidden" name="csrf_token" value="{{ csrf_token }}">
	{# інші поля #}
</form>
```

##### Використання Latte

Ви також можете встановити користувацьку функцію для виведення CSRF-токена у ваших шаблонах Latte.

```php

Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// інші конфігурації...

	// Встановіть користувацьку функцію для виведення CSRF-токена
	$latte->addFunction('csrf', function() {
		$csrfToken = Flight::session()->get('csrf_token');
		return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
	});

	$latte->render($finalPath, $data, $block);
});
```

І тепер у ваших шаблонах Latte ви можете використовувати функцію `csrf()` для виведення CSRF-токена.

```html
<form method="post">
	{csrf()}
	<!-- інші поля форми -->
</form>
```

#### Перевірка CSRF-токена

Ви можете перевірити CSRF-токен кількома методами.

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
	// більше маршрутів
}, [CsrfMiddleware::class]);
```

##### Фільтри подій

```php
// Цей middleware перевіряє, чи запит є POST-запитом, і якщо так, перевіряє, чи CSRF-токен дійсний
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// захопіть csrf-токен зі значень форми
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Invalid CSRF token');
			// або для JSON-відповіді
			Flight::jsonHalt(['error' => 'Invalid CSRF token'], 403);
		}
	}
});
```

### Міжсайтовий скриптинг (XSS)

Міжсайтовий скриптинг (XSS) — це тип атаки, коли шкідливе введення з форми може вставити код на ваш вебсайт. Більшість таких можливостей походить від значень форм, які заповнюють ваші кінцеві користувачі. Ви **ніколи** не повинні довіряти виведенню від ваших користувачів! Завжди припускайте, що всі вони — найкращі хакери у світі. Вони можуть вставити шкідливий JavaScript або HTML на вашу сторінку. Цей код можна використати для крадіжки інформації від ваших користувачів або виконання дій на вашому вебсайті. Використовуючи клас представлення Flight або рушій шаблонів, як-от [Twig](/awesome-plugins/twig) чи [Latte](/awesome-plugins/latte), ви можете легко екранувати виведення, щоб запобігти XSS-атакам.

```php
// Припустімо, користувач достатньо кмітливий і намагається використати це як своє ім'я
$name = '<script>alert("XSS")</script>';

// Це екранує виведення
Flight::view()->set('name', $name);
// Це виведе: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Twig (типово для skeleton) і Latte автоматично екранують за замовчуванням — віддавайте перевагу їм над сирим PHP echo
Flight::render('template', ['name' => $name]);
// Twig: {{ name }}  → екрановано
// Уникайте |raw / неекранованого виведення, якщо вміст не є повністю довіреним
```

### SQL-ін'єкція

SQL-ін'єкція — це тип атаки, коли зловмисний користувач може вставити SQL-код у вашу базу даних. Це можна використати для крадіжки інформації з вашої бази даних або виконання дій із вашою базою даних. Знову ж таки, ви **ніколи** не повинні довіряти введенню від ваших користувачів! Завжди припускайте, що вони прагнуть крові. Використовуйте підготовлені запити — хелпери [SimplePdo](/learn/simple-pdo) роблять це шляхом за замовчуванням.

```php
// Припускаючи, що у вас зареєстровано Flight::db() як SimplePdo (або SimplePdo інжектується в контролер)
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// SimplePdo (бажано) — однорядкові запити з прив'язаними параметрами
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Те саме з ?-заповнювачами
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);
```

У контролерах у стилі skeleton віддавайте перевагу ін'єкції `SimplePdo` через конструктор, а не `Flight::db()`, щоб тести й код, згенерований AI, залишалися узгодженими ([DIC](/learn/dependency-injection-container)).

#### Небезпечний приклад

Нижче показано, чому ми використовуємо підготовлені SQL-запити для захисту від наївних прикладів, як наведений нижче:

```php
// кінцевий користувач заповнює вебформу.
// як значення форми хакер вводить щось таке:
$username = "' OR 1=1; -- ";

$sql = "SELECT * FROM users WHERE username = '$username' LIMIT 5";
$users = Flight::db()->fetchAll($sql);
// Після побудови запиту він виглядає так
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5

// Це виглядає дивно, але це дійсний запит, який працюватиме. Насправді,
// це дуже поширена атака SQL-ін'єкції, яка поверне всіх користувачів.

var_dump($users); // це виведе всіх користувачів у базі даних, а не лише одного користувача з цим іменем
```

### Секрети та конфігурація

- Розміщуйте секрети в **`.env`** (або в реальному середовищі), а не в закомічених зразках `config.php`.
- Правило skeleton: літеральні значення за замовчуванням у `config.php`; об'єднуйте env під час bootstrap; **не** читайте `$_ENV` всередині контролерів — натомість інжектуйте конфігурацію. Дивіться [Конфігурація](/learn/configuration).
- Ніколи не комітьте API-ключі, паролі до БД або ключі шифрування сесій. Спрямовуйте AI-інструменти до **`SECURITY.md`**, щоб вони не вигадували небезпечні скорочення.

### Валідація callback JSONP

Якщо ви використовуєте метод `Flight::jsonp()`, зверніть увагу, що Flight перевіряє ім'я параметра callback JSONP відповідно до строгого regex-allowlist (`/^[A-Za-z_$][\w$.]{0,127}$/`). Будь-яке ім'я callback, яке не відповідає цьому шаблону, змусить Flight викинути виняток, запобігаючи ін'єкції довільного JavaScript через шкідливе значення callback.

Ця валідація вбудована і не потребує додаткової конфігурації, але про неї варто знати під час налагодження неочікуваних помилок від JSONP-ендпоінтів.

### CORS

Обмін ресурсами між різними джерелами (CORS) — це механізм, який дозволяє багатьом ресурсам (наприклад, шрифтам, JavaScript тощо) на вебсторінці бути запитаними з іншого домену, відмінного від домену, з якого походить ресурс. Flight не має вбудованої функціональності, але це можна легко обробити хуком, який виконується перед викликом методу `Flight::start()`.

```php
// app/Utils/CorsUtil.php  (skeleton: папка Utils у PascalCase → App\Utils)

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
		// налаштуйте тут дозволені хости.
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

// bootstrap / routes — запускається перед start
$app = Flight::app();
$cors = new \App\Utils\CorsUtil($app);
$app->before('start', [ $cors, 'set' ]);
```

### Посилення конфігурації Flight

Flight відкриває кілька налаштувань рушія, які мають прямий вплив на безпеку. Правильне встановлення цих параметрів — один із найпростіших способів посилити захист вашого застосунку.

#### `flight.allow_method_override`

За замовчуванням Flight дозволяє клієнтам перевизначати HTTP-метод запиту за допомогою або заголовка `X-HTTP-Method-Override`, або поля `_method` у тілі POST. Хоча це зручно для HTML-форм, які можуть надсилати лише `GET`/`POST`, це може бути небезпечно, якщо ви цього не очікуєте — зловмисник міг би підробити `DELETE` або `PUT`-запити через звичайну форму.

Якщо ваш застосунок не покладається на цю поведінку (наприклад, ви створюєте API, який використовують сучасні клієнти або JavaScript-фронтенди, що можуть надсилати будь-який HTTP-дієслово), вам слід її вимкнути:

```php
// У вашому index.php або bootstrap-файлі, перед Flight::start()
Flight::set('flight.allow_method_override', false);
```

Значення за замовчуванням — `true` для зворотної сумісності, але **встановлення його в `false` наполегливо рекомендується** для будь-якого застосунку, який явно не потребує функції override.

#### `flight.debug`

Flight має налаштування `flight.debug`, яке контролює, чи відображається в браузері детальна інформація про помилку (повідомлення винятку, код і повний стек викликів), коли виникає необроблений виняток. Типове значення — `false`, що означає, що показується лише загальне повідомлення `500 Internal Server Error` — жодні внутрішні деталі не розкриваються клієнту.

Ніколи не вмикайте це на продакшн-сервері. Використовуйте це лише локально або в staging-середовищі:

```php
// Безпечно лише для локальної розробки — НІКОЛИ в продакшені
Flight::set('flight.debug', true);
```

Коли `flight.debug` має значення `false` (типово), ви все ще можете перехоплювати помилки, увімкнувши `flight.log_errors`:

```php
// Логуйте помилки на сервері, не розкриваючи їх клієнту
Flight::set('flight.debug', false);
Flight::set('flight.log_errors', true);
```

#### `flight.views.restrict_to_path`

Вбудований клас `View` у Flight із задоволенням підключить шаблон з абсолютного шляху або з відносної назви, яка виходить за межі `flight.views.path` (наприклад, через `../`). Це навмисно для застосунків, які свідомо спільно використовують шаблони між папками, але це також ризик обходу шляху, якщо назва шаблону колись походить із недовіреного введення.

`flight.views.restrict_to_path` **вимкнено за замовчуванням**, щоб наявні застосунки продовжували працювати. Увімкніть його, якщо у вас немає задокументованої причини цього не робити:

```php
// У вашому index.php або bootstrap-файлі, перед Flight::start()
Flight::set('flight.views.restrict_to_path', true);
```

Engine застосовує це налаштування до `View::$restrictToPath`, коли створюється представлення (той самий шаблон, що й `flight.views.path` і `flight.views.extension`). Коли це увімкнено:

- `render()` і `fetch()` підключають лише файли, чий реальний шлях розташований усередині налаштованої папки views (символічні посилання, які вказують назовні, також відхиляються).
- `exists()` повертає `false` для тих самих шляхів замість викидання винятку.
- `getTemplate()` сам по собі не змінюється — він усе ще повертає шляхи так, як робив це завжди.
- Заблокований файл викидає `Template file is outside the views path.` Відсутній файл усе ще викидає наявне повідомлення `Template file not found: ...`.

Якщо ви використовуєте Twig або Latte з їхніми власними завантажувачами файлової системи, спрямованими на вашу папку views, ці рушії вже містять шаблони в межах цього кореня. Усе одно увімкніть це для нативного `View` у Flight, щоб будь-який код, який викликає `Flight::view()->render()` / `fetch()`, отримував такий самий захист. Офіційний [skeleton](https://github.com/flightphp/skeleton) вмикає це в bootstrap.

#### Рекомендована продакшн-конфігурація

```php
// index.php або застосовано з конфігурації застосунку / bootstrap
Flight::set('flight.allow_method_override', false);
Flight::set('flight.debug', false);
Flight::set('flight.log_errors', true);
Flight::set('flight.views.restrict_to_path', true);
```

### Обробка помилок
Приховуйте чутливі деталі помилок у продакшені, щоб уникнути витоку інформації до зловмисників. У продакшені логуйте помилки замість їх відображення з `display_errors`, встановленим у `0`.

```php
// У вашому bootstrap.php або index.php

// додайте це до вашого app/config/config.php
$environment = ENVIRONMENT;
if ($environment === 'production') {
    ini_set('display_errors', 0); // Вимкнути відображення помилок
    ini_set('log_errors', 1);     // Натомість логувати помилки
    ini_set('error_log', '/path/to/error.log');
}

// У ваших маршрутах або контролерах
// Використовуйте Flight::halt() для контрольованих відповідей із помилками
Flight::halt(403, 'Access denied');
```

### Очищення вхідних даних
Ніколи не довіряйте введенню користувача. Очищуйте його за допомогою [filter_var](https://www.php.net/manual/en/function.filter-var.php) перед обробкою, щоб запобігти проникненню шкідливих даних. Віддавайте перевагу читанню введення через `$app->request()` (або `Flight::request()`), а не через сирі `$_GET` / `$_POST` у коді застосунку.

```php

// Припустімо, є $_POST-запит із $_POST['input'] і $_POST['email']

// Очистіть рядкове введення
$clean_input = filter_var(Flight::request()->data->input, FILTER_SANITIZE_STRING);
// Очистіть email
$clean_email = filter_var(Flight::request()->data->email, FILTER_SANITIZE_EMAIL);
```

### Хешування паролів
Зберігайте паролі безпечно та перевіряйте їх надійно за допомогою вбудованих функцій PHP, як-от [password_hash](https://www.php.net/manual/en/function.password-hash.php) і [password_verify](https://www.php.net/manual/en/function.password-verify.php). Паролі ніколи не слід зберігати у відкритому тексті, а також не слід шифрувати оборотними методами. Хешування гарантує, що навіть якщо вашу базу даних скомпрометовано, справжні паролі залишаться захищеними.

```php
$password = Flight::request()->data->password;
// Хешуйте пароль під час збереження (наприклад, під час реєстрації)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Перевірте пароль (наприклад, під час входу)
if (password_verify($password, $stored_hash)) {
    // Пароль збігається
}
```

### Обмеження частоти запитів
Захищайтеся від атак brute force або атак типу denial-of-service, обмежуючи частоту запитів за допомогою кешу.

```php
// Припускаючи, що у вас встановлено й зареєстровано flightphp/cache
// Використання flightphp/cache у фільтрі
Flight::before('start', function() {
    $cache = Flight::cache();
    $ip = Flight::request()->ip;
    $key = "rate_limit_{$ip}";
    $attempts = (int) $cache->retrieve($key);
    
    if ($attempts >= 10) {
        Flight::halt(429, 'Too many requests');
    }
    
    $cache->set($key, $attempts + 1, 60); // Скинути через 60 секунд
});
```

## Дивіться також
- [Сесії](/awesome-plugins/session) - Як безпечно керувати сесіями користувачів.
- [Шаблони](/learn/templates) - Автоматичне екранування Twig/Latte та XSS.
- [SimplePdo](/learn/simple-pdo) - Хелпери бази даних із підготовленими запитами.
- [PdoWrapper](/learn/pdo-wrapper) - Застаріло; використовуйте SimplePdo для нового коду.
- [Middleware](/learn/middleware) - Як використовувати middleware для спрощення процесу додавання заголовків безпеки.
- [Конфігурація](/learn/configuration) - `.env` проти літеральної конфігурації, продакшн-прапорці.
- [AI та досвід розробника](/learn/ai) - Тримайте політику безпеки в `SECURITY.md` для агентів.
- [Відповіді](/learn/responses) - Як налаштовувати HTTP-відповіді із безпечними заголовками.
- [Запити](/learn/requests) - Як обробляти й очищувати введення користувача.
- [filter_var](https://www.php.net/manual/en/function.filter-var.php) - PHP-функція для очищення вхідних даних.
- [password_hash](https://www.php.net/manual/en/function.password-hash.php) - PHP-функція для безпечного хешування паролів.
- [password_verify](https://www.php.net/manual/en/function.password-verify.php) - PHP-функція для перевірки хешованих паролів.

## Усунення несправностей
- Зверніться до розділу «Дивіться також» вище для отримання інформації щодо усунення несправностей, пов'язаних із проблемами компонентів Flight Framework.
- Якщо CSP блокує ваші скрипти, додайте nonce (шаблон skeleton) або додайте конкретні джерела до allowlist — не встановлюйте `script-src *` без плану.

## Журнал змін
- Docs – Skeleton `App\Middleware`, нотатки Twig CSRF/XSS, SimplePdo, секрети/`.env` і `SECURITY.md` для проєктів, дружніх до AI.
- Docs – Задокументовано `flight.views.restrict_to_path` у розділі посилення конфігурації Flight (opt-in обмеження шляху для нативних views).
- v3.18.1 - Додано розділ посилення конфігурації Flight, що охоплює `flight.allow_method_override`, `flight.debug` і валідацію callback JSONP.
- v3.1.0 - Додано розділи про CORS, обробку помилок, очищення вхідних даних, хешування паролів і обмеження частоти запитів.
- v2.0 - Додано екранування для типових views, щоб запобігти XSS.