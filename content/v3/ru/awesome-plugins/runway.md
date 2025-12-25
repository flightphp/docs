# Runway

Runway — это CLI-приложение, которое помогает управлять вашими приложениями Flight. Оно может генерировать контроллеры, отображать все маршруты и многое другое. Оно основано на отличной библиотеке [adhocore/php-cli](https://github.com/adhocore/php-cli).

Нажмите [здесь](https://github.com/flightphp/runway), чтобы просмотреть код.

## Установка

Установите с помощью composer.

```bash
composer require flightphp/runway
```

## Базовая конфигурация

В первый раз, когда вы запустите Runway, он проведёт вас через процесс настройки и создаст файл конфигурации `.runway.json` в корне вашего проекта. Этот файл будет содержать некоторые необходимые конфигурации для правильной работы Runway.

## Использование

Runway имеет ряд команд, которые вы можете использовать для управления вашим приложением Flight. Есть два простых способа использовать Runway.

1. Если вы используете скелетный проект, вы можете запустить `php runway [command]` из корня вашего проекта.
1. Если вы используете Runway как пакет, установленный через composer, вы можете запустить `vendor/bin/runway [command]` из корня вашего проекта.

Для любой команды вы можете передать флаг `--help`, чтобы получить больше информации о том, как использовать команду.

```bash
php runway routes --help
```

Вот несколько примеров:

### Генерация контроллера

На основе конфигурации в вашем файле `.runway.json` по умолчанию будет сгенерирован контроллер в директории `app/controllers/`.

```bash
php runway make:controller MyController
```

### Генерация модели Active Record

На основе конфигурации в вашем файле `.runway.json` по умолчанию будет сгенерирована модель в директории `app/records/`.

```bash
php runway make:record users
```

Например, если у вас есть таблица `users` со следующей схемой: `id`, `name`, `email`, `created_at`, `updated_at`, то в файле `app/records/UserRecord.php` будет создан файл, похожий на следующий:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * Класс ActiveRecord для таблицы users.
 * @link https://docs.flightphp.com/awesome-plugins/active-record
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $created_at
 * @property string $updated_at
 * // вы также можете добавить отношения здесь, как только определите их в массиве $relations
 * @property CompanyRecord $company Пример отношения
 */
class UserRecord extends \flight\ActiveRecord
{
    /**
     * @var array $relations Установка отношений для модели
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
     */
    protected array $relations = [];

    /**
     * Конструктор
     * @param mixed $databaseConnection Соединение с базой данных
     */
    public function __construct($databaseConnection)
    {
        parent::__construct($databaseConnection, 'users');
    }
}
```

### Отображение всех маршрутов

Это отобразит все маршруты, которые в настоящее время зарегистрированы в Flight.

```bash
php runway routes
```

Если вы хотите просмотреть только конкретные маршруты, вы можете передать флаг для фильтрации маршрутов.

```bash
# Отобразить только GET-маршруты
php runway routes --get

# Отобразить только POST-маршруты
php runway routes --post

# и т.д.
```

## Настройка Runway

Если вы создаёте пакет для Flight или хотите добавить свои собственные пользовательские команды в проект, вы можете сделать это, создав директорию `src/commands/`, `flight/commands/`, `app/commands/` или `commands/` для вашего проекта/пакета. Если вам нужна дальнейшая настройка, см. раздел ниже о Конфигурации.

Чтобы создать команду, просто расширьте класс `AbstractBaseCommand` и реализуйте как минимум метод `__construct` и метод `execute`.

```php
<?php

declare(strict_types=1);

namespace flight\commands;

class ExampleCommand extends AbstractBaseCommand
{
	/**
     * Конструктор
     *
     * @param array<string,mixed> $config JSON-конфигурация из .runway-config.json
     */
    public function __construct(array $config)
    {
        parent::__construct('make:example', 'Создать пример для документации', $config);
        $this->argument('<funny-gif>', 'Имя забавного GIF');
    }

	/**
     * Выполняет функцию
     *
     * @return void
     */
    public function execute()
    {
        $io = $this->app()->io();

		$io->info('Создание примера...');

		// Сделайте что-то здесь

		$io->ok('Пример создан!');
	}
}
```

См. [Документацию adhocore/php-cli](https://github.com/adhocore/php-cli) для получения дополнительной информации о том, как создавать свои собственные пользовательские команды для вашего приложения Flight!

### Конфигурация

Если вам нужно настроить конфигурацию для Runway, вы можете создать файл `.runway-config.json` в корне вашего проекта. Ниже приведены некоторые дополнительные конфигурации, которые вы можете установить:

```js
{

	// Здесь находится директория вашего приложения
	"app_root": "app/",

	// Это директория, где находится ваш корневой индексный файл
	"index_root": "public/",

	// Это пути к корням других проектов
	"root_paths": [
		"/home/user/different-project",
		"/var/www/another-project"
	],

	// Базовые пути, скорее всего, не нужно настраивать, но они здесь, если вы хотите
	"base_paths": {
		"/includes/libs/vendor", // если у вас есть действительно уникальный путь для директории vendor или чего-то подобного
	},

	// Финальные пути — это расположения внутри проекта для поиска файлов команд
	"final_paths": {
		"src/diff-path/commands",
		"app/module/admin/commands",
	},

	// Если вы хотите просто добавить полный путь, вперед (абсолютный или относительный к корню проекта)
	"paths": [
		"/home/user/different-project/src/diff-path/commands",
		"/var/www/another-project/app/module/admin/commands",
		"app/my-unique-commands"
	]
}
```