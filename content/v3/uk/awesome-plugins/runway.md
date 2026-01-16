# Runway

Runway — це додаток командного рядка (CLI), який допомагає керувати вашими додатками Flight. Він може генерувати контролери, відображати всі маршрути та багато іншого. Він базується на чудовій бібліотеці [adhocore/php-cli](https://github.com/adhocore/php-cli).

Натисніть [тут](https://github.com/flightphp/runway), щоб переглянути код.

## Встановлення

Встановіть за допомогою composer.

```bash
composer require flightphp/runway
```

## Базова Конфігурація

Під час першого запуску Runway він спробує знайти конфігурацію `runway` у `app/config/config.php` через ключ `'runway'`.

```php
<?php
// app/config/config.php
return [
    'runway' => [
        'app_root' => 'app/',
		'public_root' => 'public/',
    ],
];
```

> **ПРИМІТКА** - Починаючи з **v1.2.0**, `.runway-config.json` є застарілим. Будь ласка, мігруйте вашу конфігурацію до `app/config/config.php`. Ви можете зробити це легко за допомогою команди `php runway config:migrate`.



### Виявлення Кореня Проекту

Runway достатньо розумний, щоб виявити корінь вашого проекту, навіть якщо ви запускаєте його з підкаталогу. Він шукає індикатори, такі як `composer.json`, `.git` або `app/config/config.php`, щоб визначити, де знаходиться корінь проекту. Це означає, що ви можете запускати команди Runway з будь-якого місця у вашому проекті! 

## Використання

Runway має низку команд, які ви можете використовувати для керування вашим додатком Flight. Є два простих способи використовувати Runway.

1. Якщо ви використовуєте скелетний проект, ви можете запустити `php runway [command]` з кореня вашого проекту.
1. Якщо ви використовуєте Runway як пакет, встановлений через composer, ви можете запустити `vendor/bin/runway [command]` з кореня вашого проекту.

### Список Команд

Ви можете переглянути список усіх доступних команд, запустивши команду `php runway`.

```bash
php runway
```

### Довідка по Командах

Для будь-якої команди ви можете передати прапорець `--help`, щоб отримати більше інформації про те, як використовувати команду.

```bash
php runway routes --help
```

Ось кілька прикладів:

### Генерація Контролера

На основі конфігурації в `runway.app_root`, локація згенерує контролер для вас у директорії `app/controllers/`.

```bash
php runway make:controller MyController
```

### Генерація Моделі Active Record

Спочатку переконайтеся, що ви встановили плагін [Active Record](/awesome-plugins/active-record). На основі конфігурації в `runway.app_root`, локація згенерує запис для вас у директорії `app/records/`.

```bash
php runway make:record users
```

Наприклад, якщо у вас є таблиця `users` з такою схемою: `id`, `name`, `email`, `created_at`, `updated_at`, файл, подібний до наступного, буде створено у файлі `app/records/UserRecord.php`:

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

### Відображення Всіх Маршрутів

Це відобразить усі маршрути, які наразі зареєстровані у Flight.

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

## Додавання Власних Команд до Runway

Якщо ви створюєте пакет для Flight або хочете додати власні власні команди до вашого проекту, ви можете зробити це, створивши директорію `src/commands/`, `flight/commands/`, `app/commands/` або `commands/` для вашого проекту/пакету. Якщо вам потрібна подальша кастомізація, дивіться розділ нижче про Конфігурацію.

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
     * @param array<string,mixed> $config Config from app/config/config.php
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

Дивіться [adhocore/php-cli Documentation](https://github.com/adhocore/php-cli) для отримання додаткової інформації про те, як створювати власні власні команди для вашого додатка Flight!

## Керування Конфігурацією

Оскільки конфігурація переміщена до `app/config/config.php` починаючи з `v1.2.0`, є кілька допоміжних команд для керування конфігурацією.

### Міграція Старої Конфігурації

Якщо у вас є старий файл `.runway-config.json`, ви можете легко мігрувати його до `app/config/config.php` за допомогою наступної команди:

```bash
php runway config:migrate
```

### Встановлення Значення Конфігурації

Ви можете встановити значення конфігурації за допомогою команди `config:set`. Це корисно, якщо ви хочете оновити значення конфігурації без відкриття файлу.

```bash
php runway config:set app_root "app/"
```

### Отримання Значення Конфігурації

Ви можете отримати значення конфігурації за допомогою команди `config:get`.

```bash
php runway config:get app_root
```

## Усі Конфігурації Runway

Якщо вам потрібно кастомізувати конфігурацію для Runway, ви можете встановити ці значення у `app/config/config.php`. Нижче наведено деякі додаткові конфігурації, які ви можете встановити:

```php
<?php
// app/config/config.php
return [
    // ... other config values ...

    'runway' => [
        // This is where your application directory is located
        'app_root' => 'app/',

        // This is the directory where your root index file is located
        'index_root' => 'public/',

        // These are the paths to the roots of other projects
        'root_paths' => [
            '/home/user/different-project',
            '/var/www/another-project'
        ],

        // Base paths most likely don't need to be configured, but it's here if you want it
        'base_paths' => [
            '/includes/libs/vendor', // if you have a really unique path for your vendor directory or something
        ],

        // Final paths are locations within a project to search for the command files
        'final_paths' => [
            'src/diff-path/commands',
            'app/module/admin/commands',
        ],

        // If you want to just add the full path, go right ahead (absolute or relative to project root)
        'paths' => [
            '/home/user/different-project/src/diff-path/commands',
            '/var/www/another-project/app/module/admin/commands',
            'app/my-unique-commands'
        ]
    ]
];
```

### Доступ до Конфігурації

Якщо вам потрібно ефективно отримати доступ до значень конфігурації, ви можете отримати доступ до них через метод `__construct` або метод `app()`. Також важливо зазначити, що якщо у вас є файл `app/config/services.php`, ці сервіси також будуть доступні для вашої команди.

```php
public function execute()
{
    $io = $this->app()->io();
    
    // Access configuration
    $app_root = $this->config['runway']['app_root'];
    
    // Access services like maybe a database connection
    $database = $this->config['database']
    
    // ...
}
```

## Обгортки Допоміжника ШІ

Runway має деякі обгортки помічників, які полегшують для ШІ генерацію команд. Ви можете використовувати `addOption` та `addArgument` у спосіб, подібний до Symfony Console. Це корисно, якщо ви використовуєте інструменти ШІ для генерації ваших команд.

```php
public function __construct(array $config)
{
    parent::__construct('make:example', 'Create an example for the documentation', $config);
    
    // The mode argument is nullable and defaults to completely optional
    $this->addOption('name', 'The name of the example', null);
}
```