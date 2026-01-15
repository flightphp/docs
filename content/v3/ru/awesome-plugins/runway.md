# Runway

Runway — это CLI-приложение, которое помогает управлять вашими приложениями Flight. Оно может генерировать контроллеры, отображать все маршруты и многое другое. Оно основано на отличной библиотеке [adhocore/php-cli](https://github.com/adhocore/php-cli).

Нажмите [здесь](https://github.com/flightphp/runway), чтобы просмотреть код.

## Установка

Установите с помощью composer.

```bash
composer require flightphp/runway
```

## Базовая конфигурация

Впервые запустив Runway, оно попытается найти конфигурацию `runway` в `app/config/config.php` через ключ `'runway'`.

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

> **ПРИМЕЧАНИЕ** - Начиная с **v1.2.0**, `.runway-config.json` устарел. Пожалуйста, мигрируйте вашу конфигурацию в `app/config/config.php`. Вы можете сделать это легко с помощью команды `php runway config:migrate`.

### Обнаружение корня проекта

Runway достаточно умён, чтобы обнаружить корень вашего проекта, даже если вы запускаете его из поддиректории. Оно ищет индикаторы, такие как `composer.json`, `.git` или `app/config/config.php`, чтобы определить, где находится корень проекта. Это значит, что вы можете запускать команды Runway откуда угодно в вашем проекте! 

## Использование

Runway имеет ряд команд, которые вы можете использовать для управления вашим приложением Flight. Есть два простых способа использовать Runway.

1. Если вы используете скелетный проект, вы можете запустить `php runway [command]` из корня вашего проекта.
1. Если вы используете Runway как пакет, установленный через composer, вы можете запустить `vendor/bin/runway [command]` из корня вашего проекта.

### Список команд

Вы можете просмотреть список всех доступных команд, запустив команду `php runway`.

```bash
php runway
```

### Справка по командам

Для любой команды вы можете передать флаг `--help`, чтобы получить больше информации о том, как использовать команду.

```bash
php runway routes --help
```

Вот несколько примеров:

### Генерация контроллера

На основе конфигурации в `runway.app_root`, место будет генерировать контроллер для вас в директории `app/controllers/`.

```bash
php runway make:controller MyController
```

### Генерация модели Active Record

Сначала убедитесь, что вы установили плагин [Active Record](/awesome-plugins/active-record). На основе конфигурации в `runway.app_root`, место будет генерировать запись для вас в директории `app/records/`.

```bash
php runway make:record users
```

Если, например, у вас есть таблица `users` со следующей схемой: `id`, `name`, `email`, `created_at`, `updated_at`, файл, похожий на следующий, будет создан в файле `app/records/UserRecord.php`:

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

### Отображение всех маршрутов

Это отобразит все маршруты, которые в настоящее время зарегистрированы в Flight.

```bash
php runway routes
```

Если вы хотите просмотреть только конкретные маршруты, вы можете передать флаг для фильтрации маршрутов.

```bash
# Display only GET routes
php runway routes --get

# Display only POST routes
php runway routes --post

# etc.
```

## Добавление пользовательских команд в Runway

Если вы создаёте пакет для Flight или хотите добавить свои собственные пользовательские команды в ваш проект, вы можете сделать это, создав директорию `src/commands/`, `flight/commands/`, `app/commands/` или `commands/` для вашего проекта/пакета. Если вам нужна дальнейшая настройка, см. раздел ниже о Конфигурации.

Чтобы создать команду, вы просто расширяете класс `AbstractBaseCommand` и реализуете как минимум метод `__construct` и метод `execute`.

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

См. [Документацию adhocore/php-cli](https://github.com/adhocore/php-cli) для получения дополнительной информации о том, как создавать свои собственные пользовательские команды в вашем приложении Flight!

## Управление конфигурацией

Поскольку конфигурация перемещена в `app/config/config.php` начиная с `v1.2.0`, есть несколько вспомогательных команд для управления конфигурацией.

### Миграция старой конфигурации

Если у вас есть старый файл `.runway-config.json`, вы можете легко мигрировать его в `app/config/config.php` с помощью следующей команды:

```bash
php runway config:migrate
```

### Установка значения конфигурации

Вы можете установить значение конфигурации с помощью команды `config:set`. Это полезно, если вы хотите обновить значение конфигурации без открытия файла.

```bash
php runway config:set app_root "app/"
```

### Получение значения конфигурации

Вы можете получить значение конфигурации с помощью команды `config:get`.

```bash
php runway config:get app_root
```

## Все конфигурации Runway

Если вам нужно настроить конфигурацию для Runway, вы можете установить эти значения в `app/config/config.php`. Ниже приведены некоторые дополнительные конфигурации, которые вы можете установить:

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

### Доступ к конфигурации

Если вам нужно эффективно получить доступ к значениям конфигурации, вы можете получить к ним доступ через метод `__construct` или метод `app()`. Также важно отметить, что если у вас есть файл `app/config/services.php`, эти сервисы также будут доступны для вашей команды.

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

## Обёртки помощников ИИ

Runway имеет некоторые обёртки помощников, которые облегчают генерацию команд ИИ. Вы можете использовать `addOption` и `addArgument` таким образом, который похож на Symfony Console. Это полезно, если вы используете инструменты ИИ для генерации ваших команд.

```php
public function __construct(array $config)
{
    parent::__construct('make:example', 'Create an example for the documentation', $config);
    
    // The mode argument is nullable and defaults to completely optional
    $this->addOption('name', 'The name of the example', null);
}
```