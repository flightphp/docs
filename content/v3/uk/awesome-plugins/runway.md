# Runway

Runway — це CLI-додаток, який допомагає керувати вашими додатками Flight. Він може генерувати контролери, відображати всі маршрути та інше. Він базується на чудовій бібліотеці [adhocore/php-cli](https://github.com/adhocore/php-cli).

Натисніть [тут](https://github.com/flightphp/runway), щоб переглянути код.

## Встановлення

Встановіть за допомогою composer.

```bash
composer require flightphp/runway
```

## Базова конфігурація

Вперше запускаючи Runway, він проведе вас через процес налаштування та створить файл конфігурації `.runway.json` у корені вашого проекту. Цей файл міститиме деякі необхідні конфігурації для правильної роботи Runway.

## Використання

Runway має низку команд, які ви можете використовувати для керування вашим додатком Flight. Є два простих способи використовувати Runway.

1. Якщо ви використовуєте скелетний проект, ви можете запустити `php runway [command]` з кореня вашого проекту.
1. Якщо ви використовуєте Runway як пакет, встановлений через composer, ви можете запустити `vendor/bin/runway [command]` з кореня вашого проекту.

Для будь-якої команди ви можете передати прапорець `--help`, щоб отримати більше інформації про те, як використовувати команду.

```bash
php runway routes --help
```

Ось кілька прикладів:

### Генерація контролера

На основі конфігурації у вашому файлі `.runway.json`, за замовчуванням буде згенеровано контролер у директорії `app/controllers/`.

```bash
php runway make:controller MyController
```

### Генерація моделі Active Record

На основі конфігурації у вашому файлі `.runway.json`, за замовчуванням буде згенеровано контролер у директорії `app/records/`.

```bash
php runway make:record users
```

Наприклад, якщо у вас є таблиця `users` з такою схемою: `id`, `name`, `email`, `created_at`, `updated_at`, буде створено файл, подібний до наступного, у файлі `app/records/UserRecord.php`:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * ActiveRecord class for the users table.
 * @link https://docs.flightphp.com/awesome-plugins/active-record
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $created_at
 * @property string $updated_at
 * // you could also add relationships here once you define them in the $relations array
 * @property CompanyRecord $company Example of a relationship
 */
class UserRecord extends \flight\ActiveRecord
{
    /**
     * @var array $relations Set the relationships for the model
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
     */
    protected array $relations = [];

    /**
     * Constructor
     * @param mixed $databaseConnection The connection to the database
     */
    public function __construct($databaseConnection)
    {
        parent::__construct($databaseConnection, 'users');
    }
}
```

### Відображення всіх маршрутів

Це відобразить всі маршрути, які наразі зареєстровані у Flight.

```bash
php runway routes
```

Якщо ви хочете переглянути лише конкретні маршрути, ви можете передати прапорець для фільтрації маршрутів.

```bash
# Display only GET routes
php runway routes --get

# Display only POST routes
php runway routes --post

# etc.
```

## Налаштування Runway

Якщо ви створюєте пакет для Flight або хочете додати власні власні команди до вашого проекту, ви можете зробити це, створивши директорію `src/commands/`, `flight/commands/`, `app/commands/` або `commands/` для вашого проекту/пакету. Якщо вам потрібне подальше налаштування, дивіться розділ нижче про Конфігурацію.

Щоб створити команду, просто розширте клас `AbstractBaseCommand` та реалізуйте щонайменше метод `__construct` та метод `execute`.

```php
<?php

declare(strict_types=1);

namespace flight\commands;

class ExampleCommand extends AbstractBaseCommand
{
	/**
     * Construct
     *
     * @param array<string,mixed> $config JSON config from .runway-config.json
     */
    public function __construct(array $config)
    {
        parent::__construct('make:example', 'Create an example for the documentation', $config);
        $this->argument('<funny-gif>', 'The name of the funny gif');
    }

	/**
     * Executes the function
     *
     * @return void
     */
    public function execute()
    {
        $io = $this->app()->io();

		$io->info('Creating example...');

		// Do something here

		$io->ok('Example created!');
	}
}
```

Дивіться [Документацію adhocore/php-cli](https://github.com/adhocore/php-cli) для отримання додаткової інформації про те, як створювати власні власні команди для вашого додатка Flight!

### Конфігурація

Якщо вам потрібно налаштувати конфігурацію для Runway, ви можете створити файл `.runway-config.json` у корені вашого проекту. Нижче наведено деякі додаткові конфігурації, які ви можете встановити:

```js
{

	// This is where your application directory is located
	"app_root": "app/",

	// This is the directory where your root index file is located
	"index_root": "public/",

	// These are the paths to the roots of other projects
	"root_paths": [
		"/home/user/different-project",
		"/var/www/another-project"
	],

	// Base paths most likely don't need to be configured, but it's here if you want it
	"base_paths": {
		"/includes/libs/vendor", // if you have a really unique path for your vendor directory or something
	},

	// Final paths are locations within a project to search for the command files
	"final_paths": {
		"src/diff-path/commands",
		"app/module/admin/commands",
	},

	// If you want to just add the full path, go right ahead (absolute or relative to project root)
	"paths": [
		"/home/user/different-project/src/diff-path/commands",
		"/var/www/another-project/app/module/admin/commands",
		"app/my-unique-commands"
	]
}
```