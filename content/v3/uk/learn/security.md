# Безпека

## Огляд

Безпека є дуже важливою для веб-додатків. Ви повинні переконатися, що ваш додаток є захищеним, а дані ваших користувачів у безпеці. 
Flight надає ряд функцій, які допоможуть вам захистити ваші веб-додатки.

Офіційний [скелетон](https://github.com/flightphp/skeleton) також містить спеціальний файл **`SECURITY.md`** та middleware для заголовків безпеки, щоб [AI інструменти для кодування](/learn/ai) (та люди) мали одне продумане місце для секретів, заголовків і правил XSS/SQL — окремо від загального стилю кодування у `AGENTS.md`.

## Розуміння

Існує ряд поширених загроз безпеці, про які ви повинні знати під час створення веб-додатків. Деякі з найпоширеніших загроз 
включають:
- Міжсайтовий підроблений запит (CSRF)
- Міжсайтовий скриптинг (XSS)
- SQL-ін'єкції
- Спільне використання ресурсів між джерелами (CORS)

[Шаблони](/learn/templates) допомагають із XSS, екрануючи вивід за замовчуванням (Twig і Latte це роблять; використовуйте цю перевагу). [Сесії](/awesome-plugins/session) можуть допомогти з CSRF, зберігаючи CSRF-токен у сесії користувача, як описано нижче. Використання підготовлених запитів (prepared statements) з PDO — або помічників із [SimplePdo](/learn/simple-pdo) — допомагає запобігти SQL-ін'єкціям. CORS можна обробити за допомогою простого хука перед викликом `Flight::start()`.

Усі ці методи працюють разом, щоб допомогти зберегти ваші веб-додатки в безпеці. Завжди тримайте в пріоритеті вивчення та розуміння найкращих практик безпеки. Не просіть AI-асистента "вимкнути CSP" або послабити заголовки лише для того, щоб сторінка завантажилась, не розуміючи компромісів.

## Базове використання

### Заголовки (Headers)

HTTP-заголовки — це один із найпростіших способів захистити ваші веб-додатки. Ви можете використовувати заголовки для запобігання клікджекінгу, XSS та інших атак. 
Існує кілька способів додати ці заголовки до вашого додатку.

Два чудові веб-сайти для перевірки безпеки ваших заголовків: [securityheaders.com](https://securityheaders.com/) та 
[observatory.mozilla.org](https://observatory.mozilla.org/). Після налаштування наведеного нижче коду ви зможете легко перевірити, чи працюють ваші заголовки, за допомогою цих двох веб-сайтів.

Скелетон включає `App\Middleware\SecurityHeadersMiddleware` (CSP з nonce для кожного запиту, frame options, HSTS тощо). Віддавайте перевагу розширенню цього класу обдумано, а не повному вимкненню заголовків.

#### Додати вручну

Ви можете вручну додати ці заголовки за допомогою методу `header` на об'єкті `Flight\Response`.
```php
// Встановлює заголовок X-Frame-Options для запобігання клікджекінгу
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Встановлює заголовок Content-Security-Policy для запобігання XSS
// Примітка: цей заголовок може бути дуже складним, тому вам слід
//  переглянути приклади в інтернеті для вашого додатку
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Встановлює заголовок X-XSS-Protection для запобігання XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Встановлює заголовок X-Content-Type-Options для запобігання MIME-сніфінгу
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Встановлює заголовок Referrer-Policy для контролю над тим, скільки інформації про реферера надсилається
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Встановлює заголовок Strict-Transport-Security для примусового використання HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Встановлює заголовок Permissions-Policy для контролю над тим, які функції та API можна використовувати
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Їх можна додати на початку ваших файлів `routes.php` або `index.php`.

#### Додати як фільтр (Filter)

Ви також можете додати їх у фільтр/хук, наприклад, так:

```php
// Додає заголовки у фільтрі
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

Ви також можете додати їх як клас middleware, що забезпечує найбільшу гнучкість щодо того, до яких маршрутів це застосовувати. Загалом ці заголовки слід застосовувати до всіх HTML та API відповідей.

Шлях і простір імен у стилі скелетона (**регістр папки відповідає `App\Middleware`**):

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
		// Віддавайте перевагу CSP nonce з bootstrap, якщо у вас є інлайн-скрипти (скелетон встановлює csp_nonce)
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

// app/config/routes.php — група з порожнім рядком = глобальний middleware для всіх маршрутів
use App\Middleware\SecurityHeadersMiddleware;
use flight\net\Router;

$router->group('', function (Router $router) {
	$router->get('/users', [ \App\Controller\UserController::class, 'getUsers' ]);
	// інші маршрути
}, [SecurityHeadersMiddleware::class]);
```

Старі проєкти можуть все ще використовувати `app/middlewares` та `app\middlewares`; це працює, якщо папки збігаються. Нові скелетон-додатки використовують **`app/Middleware/`** та **`App\Middleware`**. Дивіться [Автозавантаження](/learn/autoloading).

### Міжсайтовий підроблений запит (CSRF)

Міжсайтовий підроблений запит (CSRF) — це тип атаки, коли шкідливий веб-сайт може змусити браузер користувача надіслати запит на ваш веб-сайт. 
Це може бути використано для виконання дій на вашому веб-сайті без відома користувача. Flight не надає вбудованого механізму захисту від CSRF, 
але ви можете легко реалізувати власний за допомогою middleware.

#### Налаштування

Спочатку вам потрібно згенерувати CSRF-токен і зберегти його в сесії користувача. Потім ви можете використовувати цей токен у ваших формах і перевіряти його, коли 
форма надсилається. Ми використаємо плагін [flightphp/session](/awesome-plugins/session) для керування сесіями.

```php
// Генерує CSRF-токен і зберігає його в сесії користувача
// (припускаючи, що ви створили об'єкт сесії та приєднали його до Flight)
// дивіться документацію по сесіях для отримання додаткової інформації
Flight::register('session', flight\Session::class);

// Вам потрібно згенерувати лише один токен на сесію (щоб він працював 
// у декількох вкладках і запитах для одного користувача)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

##### Використання стандартного PHP шаблону Flight

```html
<!-- Використовуйте CSRF-токен у вашій формі -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- інші поля форми -->
</form>
```

##### Використання Twig (за замовчуванням у скелетоні)

Зареєструйте функцію Twig або передавайте токен у кожне подання форми. Мінімальний приклад із глобальною змінною та полем форми:

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

Ви також можете встановити спеціальну функцію для виведення CSRF-токену у ваших шаблонах Latte.

```php

Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// інші конфігурації...

	// Встановлює спеціальну функцію для виведення CSRF-токену
	$latte->addFunction('csrf', function() {
		$csrfToken = Flight::session()->get('csrf_token');
		return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
	});

	$latte->render($finalPath, $data, $block);
});
```

Тепер у ваших шаблонах Latte ви можете використовувати функцію `csrf()` для виведення CSRF-токену.

```html
<form method="post">
	{csrf()}
	<!-- інші поля форми -->
</form>
```

#### Перевірка CSRF-токену

Ви можете перевірити CSRF-токен кількома способами.

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
				$this->app->halt(403, 'Недійсний CSRF-токен');
			}
		}
	}
}

// routes.php
use App\Middleware\CsrfMiddleware;

$router->group('', function ($router) {
	$router->get('/users', [ \App\Controller\UserController::class, 'getUsers' ]);
	// інші маршрути
}, [CsrfMiddleware::class]);
```

##### Подієві фільтри

```php
// Цей middleware перевіряє, чи є запит POST-запитом, і якщо так, перевіряє, чи є CSRF-токен дійсним
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// отримуємо csrf-токен із значень форми
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Недійсний CSRF-токен');
			// або для JSON-відповіді
			Flight::jsonHalt(['error' => 'Недійсний CSRF-токен'], 403);
		}
	}
});
```

### Міжсайтовий скриптинг (XSS)

Міжсайтовий скриптинг (XSS) — це тип атаки, коли шкідливе введення у формі може впровадити код у ваш веб-сайт. Більшість таких можливостей походять 
із значень форм, які заповнюють ваші кінцеві користувачі. Вам **ніколи** не слід довіряти виводу від ваших користувачів! Завжди вважайте, що всі вони — 
найкращі хакери у світі. Вони можуть впровадити шкідливий JavaScript або HTML на вашу сторінку. Цей код може бути використаний для крадіжки інформації у ваших 
користувачів або виконання дій на вашому веб-сайті. Використовуючи клас представлення Flight або шаблонізатор, як-от [Twig](/awesome-plugins/twig) або [Latte](/awesome-plugins/latte), ви можете легко екранувати вивід для запобігання XSS-атакам.

```php
// Припустимо, користувач розумний і намагається використати це як своє ім'я
$name = '<script>alert("XSS")</script>';

// Це екранує вивід
Flight::view()->set('name', $name);
// Це виведе: &lt;script&gt