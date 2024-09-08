# Розгін

Розгін - це CLI-додаток, який допомагає вам керувати вашими застосунками Flight. Він може генерувати контролери, відображати всі маршрути та багато іншого. Він базується на відмінній бібліотеці [adhocore/php-cli](https://github.com/adhocore/php-cli).

Натисніть [тут](https://github.com/flightphp/runway), щоб переглянути код.

## Встановлення

Встановіть за допомогою composer.

```bash
composer require flightphp/runway
```

## Основна конфігурація

Перший раз, коли ви запустите Розгін, він проведе вас через процес налаштування і створить файл конфігурації `.runway.json` у корені вашого проєкту. Цей файл міститиме необхідні конфігурації для коректної роботи Розгону.

## Використання

Розгін має кілька команд, які ви можете використовувати для керування вашим застосунком Flight. Є два простих способи використовувати Розгін.

1. Якщо ви використовуєте скелетний проєкт, ви можете запустити `php runway [command]` з кореня вашого проєкту.
1. Якщо ви використовуєте Розгін як пакет, встановлений через composer, ви можете запустити `vendor/bin/runway [command]` з кореня вашого проєкту.

Для будь-якої команди ви можете передати прапор `--help`, щоб отримати більше інформації про те, як використовувати команду.

```bash
php runway routes --help
```

Ось кілька прикладів:

### Генерація контролера

На основі конфігурації у вашому файлі `.runway.json`, за замовчуванням буде згенеровано контролер у каталозі `app/controllers/`.

```bash
php runway make:controller MyController
```

### Генерація моделі Active Record

На основі конфігурації у вашому файлі `.runway.json`, за замовчуванням буде згенеровано контролер у каталозі `app/records/`.

```bash
php runway make:record users
```

Якщо, наприклад, у вас є таблиця `users` з наступною схемою: `id`, `name`, `email`, `created_at`, `updated_at`, буде створено файл подібний до наступного у файлі `app/records/UserRecord.php`:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * Клас ActiveRecord для таблиці користувачів.
 * @link https://docs.flightphp.com/awesome-plugins/active-record
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $created_at
 * @property string $updated_at
 * // ви також можете додати зв'язки тут, як тільки визначите їх у масиві $relations
 * @property CompanyRecord $company Приклад зв'язку
 */
class UserRecord extends \flight\ActiveRecord
{
    /**
     * @var array $relations Встановити зв'язки для моделі
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
     */
    protected array $relations = [];

    /**
     * Конструктор
     * @param mixed $databaseConnection З'єднання з базою даних
     */
    public function __construct($databaseConnection)
    {
        parent::__construct($databaseConnection, 'users');
    }
}
```

### Відображення всіх маршрутів

Це відобразить всі маршрути, які наразі зареєстровані в Flight.

```bash
php runway routes
```

Якщо ви хочете переглянути лише конкретні маршрути, ви можете передати прапор для фільтрації маршрутів.

```bash
# Відображати лише маршрути GET
php runway routes --get

# Відображати лише маршрути POST
php runway routes --post

# тощо.
```

## Налаштування Розгону

Якщо ви або створюєте пакет для Flight, або хочете додати свої власні команди у свій проєкт, ви можете це зробити, створивши каталог `src/commands/`, `flight/commands/`, `app/commands/`, або `commands/` для вашого проєкту/пакету. 

Щоб створити команду, вам просто потрібно розширити клас `AbstractBaseCommand` і реалізувати, принаймні, методи `__construct` та `execute`.

```php
<?php

declare(strict_types=1);

namespace flight\commands;

class ExampleCommand extends AbstractBaseCommand
{
	/**
     * Конструктор
     *
     * @param array<string,mixed> $config JSON конфігурація з .runway-config.json
     */
    public function __construct(array $config)
    {
        parent::__construct('make:example', 'Створити приклад для документації', $config);
        $this->argument('<funny-gif>', 'Назва смішного гіфу');
    }

	/**
     * Виконує функцію
     *
     * @return void
     */
    public function execute(string $controller)
    {
        $io = $this->app()->io();

		$io->info('Створення прикладу...');

		// Зробіть щось тут

		$io->ok('Приклад створено!');
	}
}
```

Дивіться [adhocore/php-cli Документацію](https://github.com/adhocore/php-cli) для отримання додаткової інформації про те, як створити свої власні команди у вашому застосунку Flight!