# Безпека

Безпека є важливою справою, коли мова йде про веб-додатки. Ви хочете переконатися, що ваш додаток є безпечним, а дані ваших користувачів - 
в безпеці. Flight надає ряд функцій, щоб допомогти вам захистити ваші веб-додатки.

## Заголовки

HTTP заголовки - один з найбільш простих способів захистити ваші веб-додатки. Ви можете використовувати заголовки, щоб запобігти кліковій 
атаці, XSS та іншим атакам. Існує кілька способів, якими ви можете додати ці заголовки до вашого додатку.

Два чудових веб-сайти для перевірки безпеки ваших заголовків - це [securityheaders.com](https://securityheaders.com/) та 
[observatory.mozilla.org](https://observatory.mozilla.org/).

### Додати вручну

Ви можете вручну додати ці заголовки, використовуючи метод `header` об'єкта `Flight\Response`.
```php
// Встановіть заголовок X-Frame-Options, щоб запобігти кліковій атаці
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Встановіть заголовок Content-Security-Policy, щоб запобігти XSS
// Зверніть увагу: цей заголовок може бути дуже складним, тому ви, напевно,
// повинні проконсультуватися з прикладами в Інтернеті для вашого додатку
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Встановіть заголовок X-XSS-Protection, щоб запобігти XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Встановіть заголовок X-Content-Type-Options, щоб запобігти MIME sniffing
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Встановіть заголовок Referrer-Policy, щоб контролювати, скільки інформації про реферера надсилається
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Встановіть заголовок Strict-Transport-Security, щоб примусити HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Встановіть заголовок Permissions-Policy, щоб контролювати, які функції та API можуть використовуватися
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Ці заголовки можна додати на початку ваших файлів `bootstrap.php` або `index.php`.

### Додати як фільтр

Ви також можете додати їх у фільтрі/хуку, як у наступному прикладі:

```php
// Додати заголовки у фільтрі
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

### Додати як проміжне програмне забезпечення

Ви також можете додати їх як клас проміжного програмного забезпечення. Це хороший спосіб зберегти ваш код чистим і організованим.

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

// index.php або де б ви не мали ваші маршрути
// Зверніть увагу, цей пустий рядок група діє як глобальне проміжне програмне забезпечення для
// всіх маршрутів. Звісно, ви могли б зробити те ж саме і просто додати
// це тільки до певних маршрутів.
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// інші маршрути
}, [ new SecurityHeadersMiddleware() ]);
```

## Підробка міжсайтових запитів (CSRF)

Підробка міжсайтових запитів (CSRF) - це тип атаки, коли зловмисний веб-сайт може змусити браузер користувача надіслати запит до вашого веб-сайту. 
Це може бути використано для виконання дій на вашому веб-сайті без відома користувача. Flight не надає вбудованого механізму захисту CSRF, 
але ви можете легко реалізувати свій власний, використовуючи проміжне програмне забезпечення.

### Налаштування

Спочатку вам потрібно згенерувати токен CSRF і зберегти його в сесії користувача. Потім ви можете використовувати цей токен у ваших формах і перевіряти його, коли 
форма подається.

```php
// Сгенеруйте токен CSRF і зберігайте його в сесії користувача
// (припускаючи, що ви створили об'єкт сесії та під'єднали його до Flight)
// дивіться документацію про сесії для отримання додаткової інформації
Flight::register('session', \Ghostff\Session\Session::class);

// Вам потрібно згенерувати лише один токен на сесію (щоб це працювало
// через кілька вкладок та запитів для одного й того ж користувача)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

```html
<!-- Використовуйте токен CSRF у своїй формі -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- інші поля форми -->
</form>
```

#### Використання Latte

Ви також можете налаштувати власну функцію для виводу токену CSRF у ваших шаблонах Latte.

```php
// Налаштуйте власну функцію для виводу токену CSRF
// Зверніть увагу: Вид було налаштовано на Latte як движок видання
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

І тепер у ваших шаблонах Latte ви можете використовувати функцію `csrf()`, щоб вивести токен CSRF.

```html
<form method="post">
	{csrf()}
	<!-- інші поля форми -->
</form>
```

Просто і зрозуміло, так?

### Перевірка токену CSRF

Ви можете перевірити токен CSRF, використовуючи фільтри подій:

```php
// Це проміжне програмне забезпечення перевіряє, чи є запит POST-запитом і, якщо так, перевіряє, чи є токен CSRF дійсним
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// захоплюємо токен csrf з полів форми
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Недійсний токен CSRF');
			// або для JSON-відповіді
			Flight::jsonHalt(['error' => 'Недійсний токен CSRF'], 403);
		}
	}
});
```

Або ви можете використовувати клас проміжного програмного забезпечення:

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
				Flight::halt(403, 'Недійсний токен CSRF');
			}
		}
	}
}

// index.php або де б ви не мали ваші маршрути
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// інші маршрути
}, [ new CsrfMiddleware() ]);
```

## Міжсайтове скриптування (XSS)

Міжсайтове скриптування (XSS) - це тип атаки, коли зловмисний веб-сайт може впровадити код у ваш веб-сайт. Більшість таких можливостей 
з'являється з значень форм, які заповнюватимуть ваші кінцеві користувачі. Ви повинні **ніколи** не довіряти виводу ваших користувачів! Завжди вважайте, 
що всі з них - найкращі хакери у світі. Вони можуть впровадити шкідливий JavaScript або HTML у вашу сторінку. Цей код може бути використаний для 
викрадення інформації від ваших користувачів або виконання дій на вашому веб-сайті. Використовуючи клас видів Flight, ви можете легко екранізувати 
вивід, щоб запобігти атакам XSS.

```php
// Припустимо, що користувач розумний і намагається використати це як своє ім'я
$name = '<script>alert("XSS")</script>';

// Це відсканує вивід
Flight::view()->set('name', $name);
// Це виведе: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Якщо ви використовуєте щось на кшталт Latte, зареєстрованого як ваш клас видання, 
// це також автоматично екранізує це.
Flight::view()->render('template', ['name' => $name]);
```

## SQL-ін'єкція

SQL-ін'єкція - це тип атаки, коли зловмисний користувач може впровадити SQL-код у вашу базу даних. Це може бути використано для 
викрадення інформації з вашої бази даних або виконання дій на вашій базі даних. Знову ви повинні **ніколи** не довіряти 
виходу ваших користувачів! Завжди вважайте, що вони намагаються заподіяти шкоду. Ви можете використовувати підготовлені запити у ваших 
об'єктах `PDO`, щоб запобігти SQL-ін'єкції.

```php
// Припустимо, ви зареєстрували Flight::db() як ваш об'єкт PDO
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// Якщо ви використовуєте клас PdoWrapper, це легко можна зробити в один рядок
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Ви можете зробити те ж саме з об'єктом PDO з ? заповнювачами
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// Обіцяйте, що ви ніколи НЕ зробите щось таке...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// адже що, якщо $username = "' OR 1=1; -- ";
// Після того, як запит будується, виглядає це
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// Це виглядає дивно, але це дійсний запит, який спрацює. Насправді,
// це дуже поширена SQL-ін'єкція, яка поверне усіх користувачів.
```

## CORS

Обмін ресурсами між різними джерелами (CORS) - це механізм, який дозволяє багатьом ресурсам (наприклад, шрифтам, JavaScript тощо) на веб-сторінці бути 
запитуваними з іншого домену, відмінного від домену, з якого ресурс походить. Flight не має вбудованого функціоналу, але це можна легко 
обробити з допомогою хуку, який виконується перед викликом методу `Flight::start()`.

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
		// Налаштуйте свої дозволені хости тут.
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

// index.php або де б ви не мали ваші маршрути
$CorsUtil = new CorsUtil();

// Це потрібно виконати перед тим, як запуститься start.
Flight::before('start', [ $CorsUtil, 'setupCors' ]);
```

## Обробка помилок
Сховати чутливі деталі помилок у продакшені, щоб уникнути витоку інформації зловмисникам.

```php
// У вашому bootstrap.php або index.php

// у flightphp/skeleton це в app/config/config.php
$environment = ENVIRONMENT;
if ($environment === 'production') {
    ini_set('display_errors', 0); // Вимкнути відображення помилок
    ini_set('log_errors', 1);     // Логувати помилки натомість
    ini_set('error_log', '/path/to/error.log');
}

// У ваших маршрутах або контролерах
// Використовуйте Flight::halt() для контрольованих відповідей на помилки
Flight::halt(403, 'Доступ заборонено');
```

## Санітаризація вводу
Ніколи не довіряйте вводу користувачів. Санітаризуйте його перед обробкою, щоб запобігти проникненню шкідливих даних.

```php

// Припустимо запит $_POST з $_POST['input'] та $_POST['email']

// Санітаризація строкового вводу
$clean_input = filter_var(Flight::request()->data->input, FILTER_SANITIZE_STRING);
// Санітаризація електронної пошти
$clean_email = filter_var(Flight::request()->data->email, FILTER_SANITIZE_EMAIL);
```

## Хешування паролів
Зберігайте паролі у безпеці та перевіряйте їх безпечно, використовуючи вбудовані функції PHP.

```php
$password = Flight::request()->data->password;
// Хешуйте пароль при зберіганні (наприклад, під час реєстрації)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Перевірте пароль (наприклад, під час входу)
if (password_verify($password, $stored_hash)) {
    // Пароль співпадає
}
```

## Обмеження швидкості
Захистіть від атак типу brute force, обмежуючи темп запитів за допомогою кешу.

```php
// Припустимо, що у вас встановлено та зареєстровано flightphp/cache
// Використовуючи flightphp/cache в проміжному програмному забезпеченні
Flight::before('start', function() {
    $cache = Flight::cache();
    $ip = Flight::request()->ip;
    $key = "rate_limit_{$ip}";
    $attempts = (int) $cache->retrieve($key);
    
    if ($attempts >= 10) {
        Flight::halt(429, 'Забагато запитів');
    }
    
    $cache->set($key, $attempts + 1, 60); // Скинути через 60 секунд
});
```

## Висновок

Безпека є важливою справою, і важливо переконатися, що ваші веб-додатки безпечні. Flight надає ряд функцій, щоб допомогти вам 
захистити ваші веб-додатки, але важливо завжди залишатися пильним і переконатися, що ви робите все можливе, щоб захистити дані своїх користувачів. 
Завжди вважайте найгірше і ніколи не довіряйте вводу ваших користувачів. Завжди екранізуйте вихід і використовуйте підготовлені запити, щоб запобігти SQL 
ін'єкції. Завжди використовуйте проміжне програмне забезпечення, щоб захистити свої маршрути від атак CSRF та CORS. Якщо ви виконаєте всі ці дії, ви будете на 
шляху до створення безпечних веб-додатків.