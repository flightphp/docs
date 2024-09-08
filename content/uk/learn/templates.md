# HTML Views and Templates

Flight надає деякі базові функціональні можливості для шаблонізації за замовчуванням. 

Якщо вам потрібні більш складні потреби в шаблонізації, подивіться приклади Smarty і Latte в розділі [Custom Views](#custom-views).

## Дефолтний движок перегляду

Щоб відобразити шаблон перегляду, викликайте метод `render` з іменем 
файлу шаблону та необов'язковими даними шаблону:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Дані шаблону, які ви передаєте, автоматично вводяться в шаблон і можуть 
бути посиланнями, як локальні змінні. Файли шаблонів - це просто файли PHP. Якщо 
вміст файлу шаблону `hello.php` є:

```php
Hello, <?= $name ?>!
```

Вихідні дані будуть:

```
Hello, Bob!
```

Ви також можете вручну встановити змінні перегляду, використовуючи метод set:

```php
Flight::view()->set('name', 'Bob');
```

Змінна `name` тепер доступна для всіх ваших переглядів. Тому ви просто можете зробити:

```php
Flight::render('hello');
```

Зверніть увагу, що при вказуванні імені шаблону в методі render, ви можете 
опустити розширення `.php`.

За замовчуванням Flight буде шукати директорію `views` для файлів шаблонів. Ви можете 
встановити альтернативний шлях для своїх шаблонів, задавши наступну конфігурацію:

```php
Flight::set('flight.views.path', '/path/to/views');
```

### Макети

Відомо, що веб-сайти мають єдиний шаблон макету з змінним 
вмістом. Щоб відобразити вміст, який буде використовуватися в макеті, ви можете передати необов'язковий 
параметр до методу `render`.

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

Ваш перегляд буде мати збережені змінні з іменами `headerContent` і `bodyContent`.
Ви можете відобразити свій макет, виконавши:

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

## Користувацькі движки перегляду

Flight дозволяє замінити дефолтний движок перегляду, просто зареєструвавши свій 
власний клас перегляду. 

### Smarty

Ось як ви можете використовувати движок шаблонів [Smarty](http://www.smarty.net/)
для своїх переглядів:

```php
// Завантажити бібліотеку Smarty
require './Smarty/libs/Smarty.class.php';

// Зареєструвати Smarty як клас перегляду
// Також передайте функцію зворотного виклику для налаштування Smarty при завантаженні
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// Призначити дані шаблону
Flight::view()->assign('name', 'Bob');

// Відобразити шаблон
Flight::view()->display('hello.tpl');
```

Для повноти, вам також слід переоприділити дефолтний метод render Flight:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Latte

Ось як ви можете використовувати движок шаблонів [Latte](https://latte.nette.org/)
для своїх переглядів:

```php

// Зареєструвати Latte як клас перегляду
// Також передайте функцію зворотного виклику для налаштування Latte при завантаженні
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // Тут Latte буде кешувати ваші шаблони для підвищення швидкості
	// Одна з цікавих особливостей Latte полягає в тому, що він автоматично оновлює ваш
	// кеш, коли ви вносите зміни до своїх шаблонів!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Повідомте Latte, де буде коренева директорія для ваших переглядів.
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// І підсумуйте це, щоб ви могли правильно використовувати Flight::render()
Flight::map('render', function(string $template, array $data): void {
  // Це як $latte_engine->render($template, $data);
  echo Flight::view()->render($template, $data);
});
```