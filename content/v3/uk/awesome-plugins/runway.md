# Паска

Паска — це CLI-додаток, який допомагає керувати вашими додатками Flight. Він може генерувати контролери, відображати всі маршрути та багато іншого. Він базується на чудовій бібліотеці [adhocore/php-cli](https://github.com/adhocore/php-cli).

Натисніть [тут](https://github.com/flightphp/runway), щоб переглянути код.

## Встановлення

Встановіть за допомогою composer.

```bash
composer require flightphp/runway
```

## Базова Конфігурація

Вперше, коли ви запустите Паска, він спробує знайти конфігурацію `runway` у `app/config/config.php` через ключ `'runway'`.

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

Паска достатньо розумний, щоб виявити корінь вашого проекту, навіть якщо ви запускаєте його з підкаталогу. Він шукає індикатори, такі як `composer.json`, `.git` або `app/config/config.php`, щоб визначити, де знаходиться корінь проекту. Це означає, що ви можете запускати команди Паска з будь-якого місця у вашому проекті! 

## Використання

Паска має низку команд, які ви можете використовувати для керування вашим додатком Flight. Є два простих способи використовувати Паска.

1. Якщо ви використовуєте скелетний проект, ви можете запускати `php runway [command]` з кореня вашого проекту.
1. Якщо ви використовуєте Паска як пакет, встановлений через composer, ви можете запускати `vendor/bin/runway [command]` з кореня вашого проекту.

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

На основі конфігурації в `runway.app_root`, локація згенерує контролер для вас у каталозі `app/controllers/`.

```bash
php runway make:controller MyController
```

### Генерація Моделі Active Record

Спочатку переконайтеся, що ви встановили плагін [Active Record](/awesome-plugins/active-record). На основі конфігурації в `runway.app_root`, локація згенерує запис для вас у каталозі `app/records/`.

```bash
php runway make:record users
```

Наприклад, якщо у вас є таблиця `users` з такою схемою: `id`, `name`, `email`, `created_at`, `updated_at`, файл, подібний до наступного, буде створено в файлі `app/records/UserRecord.php`:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * Клас ActiveRecord для таблиці users.
 * @link https://docs.flightphp.com/awesome-plugins/active-record
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $created_at
 * @property string $updated_at
 * // ви також можете додати відносини тут, як тільки визначите їх у масиві $relations
 * @property CompanyRecord $company Приклад відношення
 */
class UserRecord extends \flight\ActiveRecord
{
    /**
     * @var array $relations Встановлює відносини для моделі
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
     */
    protected array $relations = [];

    /**
     * Конструктор
     * @param mixed $databaseConnection Підключення до бази даних
     */
    public function __construct($databaseConnection)
    {
        parent::__construct($databaseConnection, 'users');
    }
}
```

### Відображення Всіх Маршрутів

Це відобразить усі маршрути, які наразі зареєстровані в Flight.

```bash
php runway routes
```

Якщо ви хочете переглянути лише конкретні маршрути, ви можете передати прапорець для фільтрації маршрутів.

```bash
# Відобразити лише GET маршрути
php runway routes --get

# Відобразити лише POST маршрути
php runway routes --post

# тощо.
```

## Додавання Власних Команд до Паска

Якщо ви створюєте пакет для Flight або хочете додати власні власні команди до вашого проекту, ви можете зробити це, створивши каталог `src/commands/`, `flight/commands/`, `app/commands/` або `commands/` для вашого проекту/пакету. Якщо вам потрібна подальша кастомізація, дивіться розділ нижче про Конфігурацію.

Щоб створити команду, ви просто розширюєте клас `AbstractBaseCommand` та реалізуєте щонайменше метод `__construct` та метод `execute`.

```php
<?php

declare(strict_types=1);

namespace flight\commands;

class ExampleCommand extends AbstractBaseCommand
{
	/**
     * Конструктор
     *
     * @param array<string,mixed> $config Конфігурація з app/config/config.php
     */
    public function __construct(array $config)
    {
        parent::__construct('make:example', 'Створити приклад для документації', $config);
        $this->argument('<funny-gif>', 'Назва смішного GIF');
    }

	/**
     * Виконує функцію
     *
     * @return void
     */
    public function execute()
    {
        $io = $this->app()->io();

		$io->info('Створення прикладу...');

		// Зробіть щось тут

		$io->ok('Приклад створено!');
	}
}
```

Дивіться [Документацію adhocore/php-cli](https://github.com/adhocore/php-cli) для отримання додаткової інформації про те, як створювати власні власні команди для вашого додатка Flight!

## Керування Конфігурацією

Оскільки конфігурація перемістилася до `app/config/config.php` починаючи з `v1.2.0`, є кілька допоміжних команд для керування конфігурацією.

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

## Усі Конфігурації Паска

Якщо вам потрібно кастомізувати конфігурацію для Паска, ви можете встановити ці значення в `app/config/config.php`. Нижче наведено деякі додаткові конфігурації, які ви можете встановити:

```php
<?php
// app/config/config.php
return [
    // ... інші значення конфігурації ...

    'runway' => [
        // Тут розташований каталог вашого додатка
        'app_root' => 'app/',

        // Це каталог, де розташований ваш кореневий індексний файл
        'index_root' => 'public/',

        // Це шляхи до коренів інших проектів
        'root_paths' => [
            '/home/user/different-project',
            '/var/www/another-project'
        ],

        // Базові шляхи, ймовірно, не потрібно налаштовувати, але вони тут, якщо ви хочете
        'base_paths' => [
            '/includes/libs/vendor', // якщо у вас є дійсно унікальний шлях для каталогу vendor або щось подібне
        ],

        // Фінальні шляхи — це локації всередині проекту для пошуку файлів команд
        'final_paths' => [
            'src/diff-path/commands',
            'app/module/admin/commands',
        ],

        // Якщо ви хочете просто додати повний шлях, робіть це (абсолютний або відносний до кореня проекту)
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
    
    // Доступ до конфігурації
    $app_root = $this->config['runway']['app_root'];
    
    // Доступ до сервісів, наприклад, підключення до бази даних
    $database = $this->config['database']
    
    // ...
}
```

## Обгортки Допоміжника ШІ

Паска має деякі обгортки помічників, які полегшують для ШІ генерацію команд. Ви можете використовувати `addOption` та `addArgument` у спосіб, подібний до Symfony Console. Це корисно, якщо ви використовуєте інструменти ШІ для генерації ваших команд.

```php
public function __construct(array $config)
{
    parent::__construct('make:example', 'Створити приклад для документації', $config);
    
    // Аргумент mode є nullable і за замовчуванням повністю необов'язковим
    $this->addOption('name', 'Назва прикладу', null);
}
```