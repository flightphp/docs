# Безпека

## Огляд

Безпека є великою справою, коли йдеться про веб-додатки. Ви хочете переконатися, що ваш додаток безпечний і що дані ваших користувачів 
безпечні. Flight надає низку функцій, які допоможуть вам захистити ваші веб-додатки.

## Розуміння

Існує низка поширених загроз безпеки, про які ви повинні знати під час створення веб-додатків. Деякі з найпоширеніших загроз
включають:
- Cross Site Request Forgery (CSRF)
- Cross Site Scripting (XSS)
- SQL Injection
- Cross Origin Resource Sharing (CORS)

[Templates](/learn/templates) допомагають з XSS, екрануючи вихідні дані за замовчуванням, тому вам не потрібно пам'ятати про це. [Sessions](/awesome-plugins/session) можуть допомогти з CSRF, зберігаючи токен CSRF у сесії користувача, як описано нижче. Використання підготовлених запитів з PDO може допомогти запобігти атакам SQL injection (або використання зручних методів у класі [PdoWrapper](/learn/pdo-wrapper)). CORS можна обробити за допомогою простого хука перед викликом `Flight::start()`.

Усі ці методи працюють разом, щоб допомогти зберегти безпеку ваших веб-додатків. Безпека завжди повинна бути на передньому плані вашого розуму, щоб вивчати та розуміти найкращі практики безпеки.

## Основне використання

### Заголовки

HTTP-заголовки є одним з найпростіших способів захисту ваших веб-додатків. Ви можете використовувати заголовки, щоб запобігти clickjacking, XSS та іншим атакам. 
Існує кілька способів додати ці заголовки до вашого додатку.

Два чудові веб-сайти для перевірки безпеки ваших заголовків — [securityheaders.com](https://securityheaders.com/) та 
[observatory.mozilla.org](https://observatory.mozilla.org/). Після налаштування коду нижче ви можете легко перевірити, чи працюють ваші заголовки, за допомогою цих двох сайтів.

#### Додавання вручну

Ви можете вручну додати ці заголовки, використовуючи метод `header` об'єкта `Flight\Response`.
```php
// Встановлення заголовка X-Frame-Options для запобігання clickjacking
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Встановлення заголовка Content-Security-Policy для запобігання XSS
// Примітка: цей заголовок може бути дуже складним, тому ви захочете
//  звернутися до прикладів в інтернеті для вашого додатку
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Встановлення заголовка X-XSS-Protection для запобігання XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Встановлення заголовка X-Content-Type-Options для запобігання MIME sniffing
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Встановлення заголовка Referrer-Policy для контролю кількості інформації про реферер, що надсилається
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Встановлення заголовка Strict-Transport-Security для примусового використання HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Встановлення заголовка Permissions-Policy для контролю того, які функції та API можуть бути використані
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Ці заголовки можна додати на вершині ваших файлів `routes.php` або `index.php`.

#### Додавання як фільтр

Ви також можете додати їх у фільтр/хук, як у наведеному нижче прикладі: 

```php
// Додавання заголовків у фільтр
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

#### Додавання як middleware

Ви також можете додати їх як клас middleware, що надає найбільшу гнучкість для того, до яких маршрутів це застосовувати. Загалом, ці заголовки повинні застосовуватися до всіх HTML- та API-відповідей.

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

// index.php або де ви маєте свої маршрути
// FYI, ця порожня група діє як глобальний middleware для
// всіх маршрутів. Звичайно, ви можете зробити те саме і просто додати
// це тільки до конкретних маршрутів.
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// більше маршрутів
}, [ SecurityHeadersMiddleware::class ]);
```

### Cross Site Request Forgery (CSRF)

Cross Site Request Forgery (CSRF) — це тип атаки, коли шкідливий веб-сайт може змусити браузер користувача надіслати запит на ваш веб-сайт. 
Це може бути використано для виконання дій на вашому веб-сайті без відома користувача. Flight не надає вбудований механізм захисту від CSRF, 
але ви можете легко реалізувати свій власний, використовуючи middleware.

#### Налаштування

Спочатку вам потрібно згенерувати токен CSRF і зберегти його в сесії користувача. Потім ви можете використовувати цей токен у ваших формах і перевіряти його, коли 
форма надсилається. Ми використаємо плагін [flightphp/session](/awesome-plugins/session) для керування сесіями.

```php
// Генерація токена CSRF і збереження його в сесії користувача
// (припускаючи, що ви створили об'єкт сесії та прикріпили його до Flight)
// див. документацію сесії для отримання додаткової інформації
Flight::register('session', flight\Session::class);

// Вам потрібно генерувати лише один токен на сесію (щоб це працювало 
// у кількох вкладках і запитах для одного користувача)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

##### Використання стандартного шаблону PHP Flight

```html
<!-- Використання токена CSRF у вашій формі -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- інші поля форми -->
</form>
```

##### Використання Latte

Ви також можете встановити власну функцію для виведення токена CSRF у ваших шаблонах Latte.

```php

Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// інші конфігурації...

	// Встановлення власної функції для виведення токена CSRF
	$latte->addFunction('csrf', function() {
		$csrfToken = Flight::session()->get('csrf_token');
		return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
	});

	$latte->render($finalPath, $data, $block);
});
```

І тепер у ваших шаблонах Latte ви можете використовувати функцію `csrf()` для виведення токена CSRF.

```html
<form method="post">
	{csrf()}
	<!-- інші поля форми -->
</form>
```

#### Перевірка токена CSRF

Ви можете перевірити токен CSRF за допомогою кількох методів.

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

// index.php або де ви маєте свої маршрути
use app\middlewares\CsrfMiddleware;

Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// більше маршрутів
}, [ CsrfMiddleware::class ]);
```

##### Фільтри подій

```php
// Цей middleware перевіряє, чи є запит POST-запитом, і якщо так, то перевіряє, чи валідний токен CSRF
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// захоплення токена CSRF з значень форми
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Invalid CSRF token');
			// або для JSON-відповіді
			Flight::jsonHalt(['error' => 'Invalid CSRF token'], 403);
		}
	}
});
```

### Cross Site Scripting (XSS)

Cross Site Scripting (XSS) — це тип атаки, коли шкідливий ввід форми може ін'єктувати код у ваш веб-сайт. Більшість таких можливостей виникає 
з значень форм, які заповнюватимуть ваші кінцеві користувачі. Ви **ніколи** не повинні довіряти виводу від ваших користувачів! Завжди припускайте, що всі вони є 
найкращими хакерами у світі. Вони можуть ін'єктувати шкідливий JavaScript або HTML у вашу сторінку. Цей код може бути використаний для крадіжки інформації від ваших 
користувачів або виконання дій на вашому веб-сайті. Використовуючи клас view Flight або інший шаблонізатор, як [Latte](/awesome-plugins/latte), ви можете легко екранувати вихід, щоб запобігти атакам XSS.

```php
// Припустимо, користувач розумний і намагається використати це як своє ім'я
$name = '<script>alert("XSS")</script>';

// Це екранує вихід
Flight::view()->set('name', $name);
// Це виведе: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Якщо ви використовуєте щось на кшталт Latte, зареєстроване як ваш клас view, воно також автоматично екранує це.
Flight::view()->render('template', ['name' => $name]);
```

### SQL Injection

SQL Injection — це тип атаки, коли шкідливий користувач може ін'єктувати SQL-код у вашу базу даних. Це може бути використано для крадіжки інформації 
з вашої бази даних або виконання дій у вашій базі даних. Знову ж таки, ви **ніколи** не повинні довіряти вводу від ваших користувачів! Завжди припускайте, що вони 
намагаються нашкодити. Використання підготовлених запитів у ваших об'єктах `PDO` запобіжить SQL injection.

```php
// Припускаючи, що ви зареєстрували Flight::db() як ваш об'єкт PDO
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// Якщо ви використовуєте клас PdoWrapper, це можна легко зробити в одному рядку
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Ви можете зробити те саме з об'єктом PDO з плейсхолдерами ?
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);
```

#### Невпевнений приклад

Нижче наведено, чому ми використовуємо підготовлені запити SQL для захисту від невинних прикладів, як нижче:

```php
// кінцевий користувач заповнює веб-форму.
// для значення форми хакер вводить щось на кшталт цього:
$username = "' OR 1=1; -- ";

$sql = "SELECT * FROM users WHERE username = '$username' LIMIT 5";
$users = Flight::db()->fetchAll($sql);
// Після побудови запиту це виглядає так
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5

// Це виглядає дивно, але це валідний запит, який працюватиме. Насправді,
// це дуже поширена атака SQL injection, яка поверне всіх користувачів.

var_dump($users); // це виведе всіх користувачів у базі даних, не тільки того з одним ім'ям користувача
```

### CORS

Cross-Origin Resource Sharing (CORS) — це механізм, який дозволяє багатьом ресурсам (наприклад, шрифтам, JavaScript тощо) на веб-сторінці запитуватися 
з іншого домену поза доменом, з якого походить ресурс. Flight не має вбудованої функціональності, 
але це можна легко обробити за допомогою хука, який запускається перед викликом методу `Flight::start()`.

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
		// налаштуйте дозволені хости тут.
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

// index.php або де ви маєте свої маршрути
$CorsUtil = new CorsUtil();

// Це потрібно запустити перед запуском start.
Flight::before('start', [ $CorsUtil, 'setupCors' ]);
```

### Обробка помилок
Приховуйте чутливі деталі помилок у продакшені, щоб уникнути витоку інформації до атакуючих. У продакшені записуйте помилки замість їх відображення з `display_errors` встановленим на `0`.

```php
// У вашому bootstrap.php або index.php

// додайте це до вашого app/config/config.php
$environment = ENVIRONMENT;
if ($environment === 'production') {
    ini_set('display_errors', 0); // Вимкнення відображення помилок
    ini_set('log_errors', 1);     // Запис помилок замість
    ini_set('error_log', '/path/to/error.log');
}

// У ваших маршрутах або контролерах
// Використовуйте Flight::halt() для контрольованих відповідей на помилки
Flight::halt(403, 'Access denied');
```

### Санітізація вводу
Ніколи не довіряйте вводу користувача. Санітізуйте його за допомогою [filter_var](https://www.php.net/manual/en/function.filter-var.php) перед обробкою, щоб запобігти проникненню шкідливих даних.

```php

// Припустимо POST-запит з $_POST['input'] та $_POST['email']

// Санітізація рядкового вводу
$clean_input = filter_var(Flight::request()->data->input, FILTER_SANITIZE_STRING);
// Санітізація email
$clean_email = filter_var(Flight::request()->data->email, FILTER_SANITIZE_EMAIL);
```

### Хешування паролів
Зберігайте паролі безпечно та перевіряйте їх надійно за допомогою вбудованих функцій PHP, таких як [password_hash](https://www.php.net/manual/en/function.password-hash.php) та [password_verify](https://www.php.net/manual/en/function.password-verify.php). Паролі ніколи не повинні зберігатися у чистому тексті, ні зашифрованими оборотними методами. Хешування забезпечує, що навіть якщо ваша база даних скомпрометована, фактичні паролі залишаються захищеними.

```php
$password = Flight::request()->data->password;
// Хешування пароля під час зберігання (наприклад, під час реєстрації)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Перевірка пароля (наприклад, під час входу)
if (password_verify($password, $stored_hash)) {
    // Пароль збігається
}
```

### Обмеження швидкості
Захищайтеся від атак brute force або атак відмови в обслуговуванні, обмежуючи швидкість запитів за допомогою кешу.

```php
// Припускаючи, що у вас встановлено та зареєстровано flightphp/cache
// Використання flightphp/cache у фільтрі
Flight::before('start', function() {
    $cache = Flight::cache();
    $ip = Flight::request()->ip;
    $key = "rate_limit_{$ip}";
    $attempts = (int) $cache->retrieve($key);
    
    if ($attempts >= 10) {
        Flight::halt(429, 'Too many requests');
    }
    
    $cache->set($key, $attempts + 1, 60); // Скидання через 60 секунд
});
```

## Дивіться також
- [Sessions](/awesome-plugins/session) - Як керувати сесіями користувачів безпечно.
- [Templates](/learn/templates) - Використання шаблонів для автоматичного екранування виводу та запобігання XSS.
- [PDO Wrapper](/learn/pdo-wrapper) - Спрощені взаємодії з базою даних за допомогою підготовлених запитів.
- [Middleware](/learn/middleware) - Як використовувати middleware для спрощення процесу додавання заголовків безпеки.
- [Responses](/learn/responses) - Як налаштовувати HTTP-відповіді з безпечними заголовками.
- [Requests](/learn/requests) - Як обробляти та санітізувати ввід користувача.
- [filter_var](https://www.php.net/manual/en/function.filter-var.php) - Функція PHP для санітізації вводу.
- [password_hash](https://www.php.net/manual/en/function.password-hash.php) - Функція PHP для безпечного хешування паролів.
- [password_verify](https://www.php.net/manual/en/function.password-verify.php) - Функція PHP для перевірки захешованих паролів.

## Вирішення проблем
- Зверніться до розділу "Дивіться також" вище для інформації щодо вирішення проблем, пов'язаних з компонентами Framework Flight.

## Журнал змін
- v3.1.0 - Додано розділи про CORS, Обробку помилок, Санітізацію вводу, Хешування паролів та Обмеження швидкості.
- v2.0 - Додано екранування для стандартних переглядів для запобігання XSS.