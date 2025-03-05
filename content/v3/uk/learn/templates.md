# HTML Views та Шаблони

Flight надає деяку базову функціональність шаблонізації за замовчуванням.

Flight дозволяє вам замінити базовий механізм перегляду просто зареєструвавши свій
клас представлення. Прокрутіть вниз, щоб побачити приклади використання Smarty, Latte, Blade та інших!

## Вбудований механізм перегляду

Щоб відобразити шаблон представлення, викликайте метод `render` з ім'ям 
файлу шаблону та необов'язковими даними шаблону:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Дані шаблону, які ви передаєте, автоматично інжектуються у шаблон і можуть
бути доступні як локальна змінна. Шаблони — це просто PHP файли. Якщо 
вміст файлу шаблону `hello.php` є:

```php
Hello, <?= $name ?>!
```

Вихід буде:

```text
Hello, Bob!
```

Ви також можете вручну встановити змінні представлення, використовуючи метод set:

```php
Flight::view()->set('name', 'Bob');
```

Змінна `name` тепер доступна у всіх ваших представленнях. Тому ви можете просто зробити:

```php
Flight::render('hello');
```

Зверніть увагу, що при вказуванні імені шаблону в методі render ви можете
не вказувати розширення `.php`.

За замовчуванням Flight буде шукати каталог `views` для файлів шаблонів. Ви можете
вказати альтернативний шлях для ваших шаблонів, задавши наступну конфігурацію:

```php
Flight::set('flight.views.path', '/path/to/views');
```

### Макети

Зазвичай веб-сайти мають один шаблон макета з мінливим
вмістом. Щоб відобразити вміст, який слід використовувати в макеті, ви можете передати необов'язковий
параметр до методу `render`.

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

Ваше представлення тепер матиме збережені змінні, звані `headerContent` та `bodyContent`.
Ви можете відобразити свій макет, зробивши:

```php
Flight::render('layout', ['title' => 'Home Page']);
```

Якщо файли шаблонів виглядають ось так:

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

Вихід буде:
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

## Smarty

Ось як ви можете використовувати шаблонний механізм [Smarty](http://www.smarty.net/)
для своїх представлень:

```php
// Завантажте бібліотеку Smarty
require './Smarty/libs/Smarty.class.php';

// Зареєструйте Smarty як клас представлення
// Також передайте функцію зворотного виклику для налаштування Smarty при завантаженні
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// Призначте дані шаблону
Flight::view()->assign('name', 'Bob');

// Відобразіть шаблон
Flight::view()->display('hello.tpl');
```

Для повноти ви також повинні переозначити стандартний метод render Flight:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

## Latte

Ось як ви можете використовувати шаблонний механізм [Latte](https://latte.nette.org/)
для своїх представлень:

```php
// Зареєструйте Latte як клас представлення
// Також передайте функцію зворотного виклику для налаштування Latte при завантаженні
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // Тут Latte буде кешувати ваші шаблони, щоб прискорити процес
	// Однією з цікавих функцій Latte є те, що він автоматично оновлює ваш
	// кеш, коли ви вносите зміни до ваших шаблонів!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Скажіть Latte, де буде коренева директорія для ваших представлень.
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// І завершіть так, щоб ви могли правильно використовувати Flight::render()
Flight::map('render', function(string $template, array $data): void {
  // Це як $latte_engine->render($template, $data);
  echo Flight::view()->render($template, $data);
});
```

## Blade

Ось як ви можете використовувати шаблонний механізм [Blade](https://laravel.com/docs/8.x/blade) для своїх представлень:

Спочатку вам потрібно встановити бібліотеку BladeOne за допомогою Composer:

```bash
composer require eftec/bladeone
```

Тоді ви можете налаштувати BladeOne як клас представлення в Flight:

```php
<?php
// Завантажте бібліотеку BladeOne
use eftec\bladeone\BladeOne;

// Зареєструйте BladeOne як клас представлення
// Також передайте функцію зворотного виклику для налаштування BladeOne при завантаженні
Flight::register('view', BladeOne::class, [], function (BladeOne $blade) {
  $views = __DIR__ . '/../views';
  $cache = __DIR__ . '/../cache';

  $blade->setPath($views);
  $blade->setCompiledPath($cache);
});

// Призначте дані шаблону
Flight::view()->share('name', 'Bob');

// Відобразіть шаблон
echo Flight::view()->run('hello', []);
```

Для повноти ви також повинні переозначити стандартний метод render Flight:

```php
<?php
Flight::map('render', function(string $template, array $data): void {
  echo Flight::view()->run($template, $data);
});
```

У цьому прикладі файл шаблону hello.blade.php може виглядати ось так:

```php
<?php
Hello, {{ $name }}!
```

Вихід буде:

```
Hello, Bob!
```

Дотримуючись цих кроків, ви можете інтегрувати механізм шаблонів Blade з Flight і використовувати його для візуалізації ваших представлень.