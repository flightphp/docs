# Взлетная полоса

Взлетная полоса — это приложение CLI, которое помогает управлять приложениями Flight. Он может генерировать контроллеры, отображать все маршруты и многое другое. Он основан на отличной библиотеке [adhocore/php-cli](https://github.com/adhocore/php-cli).

[Нажмите здесь](https://github.com/flightphp/runway), чтобы просмотреть код.

## Установка

Установите через composer.

```bash
composer require flightphp/runway
```

## Основная настройка

Первый раз, когда вы запускаете Взлетную полосу, она проведет вас через процесс настройки и создаст файл конфигурации `.runway.json` в корне вашего проекта. Этот файл будет содержать несколько необходимых конфигураций для работы Взлетной полосы должным образом.

## Использование

У Взлетной полосы есть несколько команд, которые вы можете использовать для управления вашим приложением Flight. Есть два простых способа использования Взлетной полосы.

1. Если вы используете каркас проекта, вы можете запустить `php runway [команда]` из корня вашего проекта.
1. Если вы используете Взлетную полосу как пакет, установленный через composer, вы можете запустить `vendor/bin/runway [команда]` из корня вашего проекта.

Для любой команды вы можете передать флаг `--help`, чтобы получить больше информации о том, как использовать команду.

```bash
php runway routes --help
```

Вот несколько примеров:

### Создание контроллера

На основе конфигурации в вашем файле `.runway.json` по умолчанию контроллер будет создан для вас в каталоге `app/controllers/`.

```bash
php runway make:controller MyController
```

### Создание модели Active Record

На основе конфигурации в вашем файле `.runway.json` по умолчанию модель будет создана для вас в каталоге `app/records/`.

```bash
php runway make:record users
```

Если у вас, например, есть таблица `users` с такой схемой: `id`, `name`, `email`, `created_at`, `updated_at`, будет создан файл, подобный следующему, в файле `app/records/UserRecord.php`:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * Класс Active Record для таблицы пользователей.
 * @link https://docs.flightphp.com/awesome-plugins/active-record
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $created_at
 * @property string $updated_at
 * // здесь вы также можете добавить отношения после их определения в массиве $relations
 * @property CompanyRecord $company Пример отношения
 */
class UserRecord extends \flight\ActiveRecord
{
    /**
     * @var array $relations Установите отношения для модели
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

Если вы хотите просмотреть только определенные маршруты, вы можете передать флаг для фильтрации маршрутов.

```bash
# Отобразить только GET маршруты
php runway routes --get

# Отобразить только POST маршруты
php runway routes --post

# и т.д.
```

## Настройка Взлетной полосы

Если вы создаете пакет для Flight или хотите добавить свои собственные команды в свой проект, вы можете сделать это, создав каталог `src/commands/`, `flight/commands/`, `app/commands/` или `commands/` для вашего проекта/пакета.

Для создания команды просто расширьте класс `AbstractBaseCommand` и реализуйте, как минимум, метод `__construct` и метод `execute`.

```php
<?php

declare(strict_types=1);

namespace flight\commands;

class ExampleCommand extends AbstractBaseCommand
{
	/**
     * Конструктор
     *
     * @param array<string,mixed> $config JSON конфигурация из .runway-config.json
     */
    public function __construct(array $config)
    {
        parent::__construct('make:example', 'Создать пример для документации', $config);
        $this->argument('<funny-gif>', 'Имя смешной гифки');
    }

	/**
     * Выполняет функцию
     *
     * @return void
     */
    public function execute(string $controller)
    {
        $io = $this->app()->io();

		$io->info('Создание примера...');

		// Сделайте здесь что-то

		$io->ok('Пример создан!');
	}
}
```

Смотрите [Документация adhocore/php-cli](https://github.com/adhocore/php-cli) для получения дополнительной информации о том, как создавать свои собственные команды в вашем приложении Flight!