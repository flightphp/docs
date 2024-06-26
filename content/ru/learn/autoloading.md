# Автозагрузка

Автозагрузка - это концепция в PHP, где вы указываете каталог или каталоги для загрузки классов. Это намного более выгодно, чем использование `require` или `include` для загрузки классов. Это также требуется для использования пакетов Composer.

По умолчанию любой класс `Flight` автоматически загружается благодаря композитору. Однако, если вы хотите загружать собственные классы, вы можете использовать метод `Flight::path` для указания каталога загрузки классов.

## Базовый пример

Допустим, у нас есть древо каталогов следующего вида:

```text
# Пример пути
/home/user/project/my-flight-project/
├── app
│   ├── cache
│   ├── config
│   ├── controllers - содержит контроллеры для этого проекта
│   ├── translations
│   ├── UTILS - содержит классы только для этого приложения (это все большими буквами намеренно для примера позже)
│   └── views
└── public
    └── css
	└── js
	└── index.php
```

Вы, возможно, заметили, что это та же структура файлов, что и у этого сайта документации.

Вы можете указать каждый каталог для загрузки так:

```php

/**
 * public/index.php
 */

// Добавление пути к автозагрузчику
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');


/**
 * app/controllers/MyController.php
 */

// нет необходимости в пространствах имен

// Рекомендуется, чтобы все автозагружаемые классы были в Pascal Case (каждое слово с заглавной буквы, без пробелов)
// Начиная с версии 3.7.2, вы можете использовать Pascal_Snake_Case для названий ваших классов, запустив Loader::setV2ClassLoading(false);
class MyController {

	public function index() {
		// сделать что-то
	}
}
```

## Пространства имен

Если у вас есть пространства имен, на самом деле становится очень легко это реализовать. Вы должны использовать метод `Flight::path()` для указания корневого каталога (не корневого документа или папки `public/`) вашего приложения.

```php

/**
 * public/index.php
 */

// Добавление пути к автозагрузчику
Flight::path(__DIR__.'/../');
```

Теперь это может выглядеть ваш контроллер. Обратите внимание на пример ниже, но обратите внимание на комментарии для важной информации.

```php
/**
 * app/controllers/MyController.php
 */

// пространства имен обязательны
// пространства имен совпадают со структурой каталогов
// пространства имен должны следовать тому же регистру, что и структура каталога
// пространства имен и каталоги не могут содержать нижние подчеркивания (если не установить Loader::setV2ClassLoading(false))
namespace app\controllers;

// Рекомендуется, чтобы все автозагружаемые классы были в Pascal Case (каждое слово с заглавной буквы, без пробелов)
// Начиная с версии 3.7.2, вы можете использовать Pascal_Snake_Case для названий ваших классов, запустив Loader::setV2ClassLoading(false);
class MyController {

	public function index() {
		// сделать что-то
	}
}
```

И если вы хотите автоматически загрузить класс из вашего каталога utils, вы будете делать практически то же самое:

```php

/**
 * app/UTILS/ArrayHelperUtil.php
 */

// пространство имен должно соответствовать структуре каталогов и регистру (обратите внимание, что каталог UTILS все в заглавных буквах
//     как в дереве файлов выше)
namespace app\UTILS;

class ArrayHelperUtil {

	public function changeArrayCase(array $array) {
		// сделать что-то
	}
}
```

## Подчеркивания в названиях классов

Начиная с версии 3.7.2, вы можете использовать Pascal_Snake_Case для названий ваших классов, запустив `Loader::setV2ClassLoading(false);`. Это позволит вам использовать подчеркивания в именах ваших классов. Это не рекомендуется, но доступно для тех, кто в этом нуждается.

```php

/**
 * public/index.php
 */

// Добавление пути к автозагрузчику
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');
Loader::setV2ClassLoading(false);

/**
 * app/controllers/My_Controller.php
 */

// нет необходимости в пространствах имен

class My_Controller {

	public function index() {
		// сделать что-то
	}
}
```