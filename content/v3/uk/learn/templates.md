# HTML Views and Templates

Flight надає деяку базову функціональність шаблонізації за замовчуванням.

Flight дозволяє вам змінювати стандартний движок видів, просто зареєструвавши свій власний клас виду. Прокрутіть вниз, щоб побачити приклади використання Smarty, Latte, Blade та інше!

## Вбудований движок видів

Щоб відобразити шаблон виду, викличте метод `render` з ім'ям 
файлу шаблону та необов'язковими даними шаблону:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Дані шаблону, які ви передаєте, автоматично вводяться в шаблон і можуть
бути використані як локальна змінна. Шаблони - це просто PHP файли. Якщо
вміст файлу шаблону `hello.php` виглядає так:

```php
Hello, <?= $name ?>!
```

Вихідні дані будуть:

```
Hello, Bob!
```

Ви також можете вручну встановити змінні виду, використовуючи метод set:

```php
Flight::view()->set('name', 'Bob');
```

Змінна `name` тепер доступна у всіх ваших видах. Тож ви можете просто зробити:

```php
Flight::render('hello');
```

Зверніть увагу, що коли ви вказуєте ім'я шаблону в методі render, ви можете
опустити розширення `.php`.

За замовчуванням Flight шукатиме каталог `views` для файлів шаблонів. Ви можете
встановити альтернативний шлях для ваших шаблонів, встановивши наступну конфігурацію:

```php
Flight::set('flight.views.path', '/path/to/views');
```

### Макети

Зазвичай веб-сайти мають єдиний файл шаблону макета з мінливим
вмістом. Щоб відобразити вміст, який буде використовуватися в макеті, ви можете передати необов'язковий параметр у метод `render`.

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

Ваш вид тоді матиме збережені змінні з іменами `headerContent` і `bodyContent`.
Ви можете потім відобразити ваш макет, зробивши:

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

Вихідні дані будуть:
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

Ось як ви можете використовувати [Smarty](http://www.smarty.net/)
движок шаблонів для ваших видів:

```php
// Завантаження бібліотеки Smarty
require './Smarty/libs/Smarty.class.php';

// Реєстрація Smarty як класу виду
// Також передайте функцію зворотного виклику для налаштування Smarty при завантаженні
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// Призначення даних шаблону
Flight::view()->assign('name', 'Bob');

// Відображення шаблону
Flight::view()->display('hello.tpl');
```

Для повноти, вам також слід переопрацювати стандартний метод render Flight:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

## Latte

Ось як ви можете використовувати [Latte](https://latte.nette.org/)
движок шаблонів для ваших видів:

```php

// Реєстрація Latte як класу виду
// Також передайте функцію зворотного виклику для налаштування Latte при завантаженні
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // Тут Latte кешуватиме ваші шаблони для пришвидшення
	// Одна із зручностей Latte полягає в тому, що він автоматично оновлює ваш
	// кеш, коли ви вносите зміни в свої шаблони!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Скажіть Latte, де буде кореневий каталог для ваших видів.
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// І завершуйте так, щоб ви могли правильно використовувати Flight::render()
Flight::map('render', function(string $template, array $data): void {
  // Це наче $latte_engine->render($template, $data);
  echo Flight::view()->render($template, $data);
});
```

## Blade

Ось як ви можете використовувати [Blade](https://laravel.com/docs/8.x/blade) движок шаблонів для ваших видів:

По-перше, вам потрібно встановити бібліотеку BladeOne через Composer:

```bash
composer require eftec/bladeone
```

Потім ви можете налаштувати BladeOne як клас виду в Flight:

```php
<?php
// Завантаження бібліотеки BladeOne
use eftec\bladeone\BladeOne;

// Реєстрація BladeOne як класу виду
// Також передайте функцію зворотного виклику для налаштування BladeOne при завантаженні
Flight::register('view', BladeOne::class, [], function (BladeOne $blade) {
  $views = __DIR__ . '/../views';
  $cache = __DIR__ . '/../cache';

  $blade->setPath($views);
  $blade->setCompiledPath($cache);
});

// Призначення даних шаблону
Flight::view()->share('name', 'Bob');

// Відображення шаблону
echo Flight::view()->run('hello', []);
```

Для повноти, вам також слід переопрацювати стандартний метод render Flight:

```php
<?php
Flight::map('render', function(string $template, array $data): void {
  echo Flight::view()->run($template, $data);
});
```

У цьому прикладі файл шаблону hello.blade.php може виглядати так:

```php
<?php
Hello, {{ $name }}!
```

Вихідні дані будуть:

```
Hello, Bob!
```

Дотримуючись цих кроків, ви можете інтегрувати двигун шаблонів Blade з Flight і використовувати його для відображення ваших видів.