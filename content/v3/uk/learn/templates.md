# HTML-представлення та шаблони

## Огляд

Flight за замовчуванням надає базову функціональність HTML-шаблонізації. Шаблонізація — це дуже ефективний спосіб відокремити логіку застосунку від рівня представлення. Спеціалізований рушій (Twig, Latte тощо) також надає [інструментам ШІ для кодування](/learn/ai) знайомий, обмежений синтаксис, тому вони рідше вбудовують бізнес-логіку у ваш HTML.

## Розуміння

Коли ви створюєте застосунок, вам, найімовірніше, знадобиться HTML, який потрібно повертати кінцевому користувачу. PHP сам по собі є мовою шаблонів, але _дуже_ легко вплести бізнес-логіку, як-от виклики бази даних, API-виклики тощо, у ваш HTML-файл, що робить тестування та розділення коду дуже складним процесом. Передаючи дані в шаблон і дозволяючи шаблону самостійно відображатися, ви можете набагато легше розділити та модульно тестувати код. Ви нам подякуєте, якщо використовуватимете шаблони!

## Базове використання

Flight дозволяє замінити стандартний рушій представлень, просто зіставивши `render` (або зареєструвавши клас представлення). Прокрутіть униз, щоб дізнатися про Twig, Latte, Smarty, Blade та інші.

> **Скелет за замовчуванням:** Офіційний [flightphp/skeleton](https://github.com/flightphp/skeleton) використовує **лише Twig** у каталозі `app/views/` (`*.twig`). Контролери викликають `$this->app->render('welcome', $data)` (розширення необов'язкове). Це вибір застосунку для нових проєктів, а не вимога ядра Flight. Latte та інші рушії залишаються повністю підтримуваними.

### Twig

<span class="badge bg-info">скелет за замовчуванням</span>

[Twig](https://twig.symfony.com/) — це гнучкий, швидкий і безпечний рушій шаблонів, який використовується в Symfony та багатьох інших PHP-проєктах. Інструменти ШІ для кодування, як правило, особливо добре знають Twig, а він за замовчуванням автоматично екранує виведення, що допомагає захиститися від XSS.

#### Встановлення

```bash
composer require twig/twig
```

(Вже включено, коли ви виконуєте `composer create-project flightphp/skeleton`.)

#### Базова конфігурація

Перевизначте метод `render`, щоб використовувати Twig замість стандартного PHP-рендерера:

```php
// перевизначаємо метод render, щоб використовувати Twig замість стандартного PHP-рендерера
Flight::map('render', function(string $template, array $data): void {
	$loader = new \Twig\Loader\FilesystemLoader(Flight::get('flight.views.path'));
	$twig = new \Twig\Environment($loader, [
		// Де Twig зберігає свої скомпільовані шаблони
		'cache' => __DIR__ . '/../cache/twig',
		'auto_reload' => true,
	]);

	// Дозволяємо "welcome" або "welcome.twig"
	if (substr($template, -5) !== '.twig') {
		$template .= '.twig';
	}

	echo $twig->render($template, $data);
});
```

У скелеті це налаштування розташоване у `app/config/services.php` (спільне середовище Twig, шлях кешу, глобальні змінні, як-от `base_url` / CSP nonce). Віддавайте перевагу впровадженню `Engine` і виклику `$app->render()` із контролерів, щоб код залишався [зручним для ШІ та тестування](/learn/ai).

#### Використання Twig у Flight

Тепер, коли ви можете рендерити за допомогою Twig, можна зробити, наприклад, так:

```html
{# app/views/home.twig #}
<html>
  <head>
	<title>{% if title %}{{ title }} - {% endif %}My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Hello, {{ name }}!</h1>
  </body>
</html>
```

```php
// routes.php
Flight::route('/@name', function ($name) {
	Flight::render('home.twig', [
		'title' => 'Home Page',
		'name' => $name
	]);
});
```

Коли ви відвідаєте `/Bob` у своєму браузері, результат буде таким:

```html
<html>
  <head>
	<title>Home Page - My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Hello, Bob!</h1>
  </body>
</html>
```

#### Подальше читання

Повніший приклад використання Twig із макетами наведено в розділі [чудові плагіни](/awesome-plugins/twig) цієї документації. Щодо метрик часу рендерингу на панелі Tracy дивіться [панель Twig у Tracy Extensions](/awesome-plugins/tracy-extensions#twig-panel-optional).

Ви можете дізнатися більше про повні можливості Twig, прочитавши [офіційну документацію](https://twig.symfony.com/doc/3.x/).

### Latte

<span class="badge bg-secondary">чудова альтернатива</span>

[Latte](https://latte.nette.org/) — це повнофункціональний рушій із синтаксисом, подібним до PHP. Він все ще є чудовим вибором для застосунків Flight; скелет просто стандартизує Twig як єдиний стандарт за замовчуванням (особливо корисно, коли інструменти ШІ генерують шаблони).

#### Встановлення

```bash
composer require latte/latte
```

#### Базова конфігурація

Основна ідея полягає в тому, щоб перевизначити метод `render` для використання Latte замість стандартного PHP-рендерера.

```php
// перевизначаємо метод render, щоб використовувати Latte замість стандартного PHP-рендерера
Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Де саме Latte зберігає свій кеш
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	$latte->render($finalPath, $data, $block);
});
```

#### Використання Latte у Flight

Тепер, коли ви можете рендерити за допомогою Latte, можна зробити, наприклад, так:

```html
<!-- app/views/home.latte -->
<html>
  <head>
	<title>{$title ? $title . ' - '}My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Hello, {$name}!</h1>
  </body>
</html>
```

```php
// routes.php
Flight::route('/@name', function ($name) {
	Flight::render('home.latte', [
		'title' => 'Home Page',
		'name' => $name
	]);
});
```

Коли ви відвідаєте `/Bob` у своєму браузері, результат буде таким:

```html
<html>
  <head>
	<title>Home Page - My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Hello, Bob!</h1>
  </body>
</html>
```

#### Подальше читання

Складніший приклад використання Latte з макетами наведено в розділі [чудові плагіни](/awesome-plugins/latte) цієї документації.

Ви можете дізнатися більше про повні можливості Latte, зокрема переклад і мовні можливості, прочитавши [офіційну документацію](https://latte.nette.org/en/).

### Вбудований рушій представлень

<span class="badge bg-warning">застаріло</span>

> **Примітка:** Це досі є функціональністю за замовчуванням і технічно працює.

Щоб відобразити шаблон представлення, викличте метод `render` із назвою файлу шаблону та необов'язковими даними шаблону:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Дані шаблону, які ви передаєте, автоматично впроваджуються в шаблон, і на них можна посилатися як на локальну змінну. Файли шаблонів — це просто PHP-файли. Якщо вміст файлу шаблону `hello.php` такий:

```php
Hello, <?= $name ?>!
```

Результат буде таким:

```text
Hello, Bob!
```

Ви також можете вручну встановити змінні представлення за допомогою методу `set`:

```php
Flight::view()->set('name', 'Bob');
```

Тепер змінна `name` доступна у всіх ваших представленнях. Тож можна просто викликати:

```php
Flight::render('hello');
```

Зверніть увагу, що під час вказання назви шаблону в методі render можна опускати розширення `.php`.

За замовчуванням Flight шукає файли шаблонів у каталозі `views`. Ви можете встановити альтернативний шлях для ваших шаблонів, задавши таку конфігурацію:

```php
Flight::set('flight.views.path', '/path/to/views');
```

За замовчуванням вбудований `View` у Flight також приймає абсолютний шлях до шаблону або назву, яка виходить за межі цього каталогу. Для більшості застосунків варто це обмежити:

```php
Flight::set('flight.views.restrict_to_path', true);
```

Це тримає `render()`, `fetch()` і `exists()` у межах `flight.views.path`. За замовчуванням цю опцію вимкнено для зворотної сумісності. Див. [Безпека](/learn/security#flightviewsrestrict_to_path).

#### Макети

Для вебсайтів зазвичай використовують один файл шаблону макета зі змінним вмістом. Щоб відрендерити вміст для використання в макеті, ви можете передати необов'язковий параметр у метод `render`.

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

Ваше представлення тоді матиме збережені змінні `headerContent` і `bodyContent`. Потім ви можете відрендерити макет так:

```php
Flight::render('layout', ['title' => 'Home Page']);
```

Якщо файли шаблонів виглядають так:

`header.php`:

```php
<h1><?= $heading ?></h1>
```

`body.php`:

```php
<div><?= $body ?></div>
```

`layout.php`:

```php
<html>
  <head>
    <title><?= $title ?></title>
  </head>
  <body>
    <?= $headerContent ?>
    <?= $bodyContent ?>
  </body>
</html>
```

Результат буде таким:

```html
<html>
  <head>
    <title>Home Page</title>
  </head>
  <body>
    <h1>Hello</h1>
    <div>World</div>
  </body>
</html>
```

### Smarty

Ось як ви можете використовувати рушій шаблонів [Smarty](http://www.smarty.net/) для своїх представлень:

```php
// Підключаємо бібліотеку Smarty
require './Smarty/libs/Smarty.class.php';

// Реєструємо Smarty як клас представлення
// Також передаємо функцію зворотного виклику для налаштування Smarty під час завантаження
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// Призначаємо дані шаблону
Flight::view()->assign('name', 'Bob');

// Відображаємо шаблон
Flight::view()->display('hello.tpl');
```

Для повноти слід також перевизначити стандартний метод render у Flight:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Blade

Ось як ви можете використовувати рушій шаблонів [Blade](https://laravel.com/docs/8.x/blade) для своїх представлень:

Спочатку потрібно встановити бібліотеку BladeOne через Composer:

```bash
composer require eftec/bladeone
```

Потім ви можете налаштувати BladeOne як клас представлення у Flight:

```php
<?php
// Підключаємо бібліотеку BladeOne
use eftec\bladeone\BladeOne;

// Реєструємо BladeOne як клас представлення
// Також передаємо функцію зворотного виклику для налаштування BladeOne під час завантаження
Flight::register('view', BladeOne::class, [], function (BladeOne $blade) {
  $views = __DIR__ . '/../views';
  $cache = __DIR__ . '/../cache';

  $blade->setPath($views);
  $blade->setCompiledPath($cache);
});

// Призначаємо дані шаблону
Flight::view()->share('name', 'Bob');

// Відображаємо шаблон
echo Flight::view()->run('hello', []);
```

Для повноти слід також перевизначити стандартний метод render у Flight:

```php
<?php
Flight::map('render', function(string $template, array $data): void {
  echo Flight::view()->run($template, $data);
});
```

У цьому прикладі файл шаблону `hello.blade.php` може виглядати так:

```php
<?php
Hello, {{ $name }}!
```

Результат буде таким:

```text
Hello, Bob!
```

## Дивіться також

- [Встановлення](/install) — Розташування скелета (`app/views/*.twig`) для нових проєктів.
- [Розширення](/learn/extending) — Як перевизначити метод `render` для використання іншого рушія шаблонів.
- [Маршрутизація](/learn/routing) — Як зіставляти маршрути з контролерами та рендерити представлення.
- [Відповіді](/learn/responses) — Як налаштовувати HTTP-відповіді.
- [Безпека](/learn