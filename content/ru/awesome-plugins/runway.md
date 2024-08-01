# Взлетная полоса

Взлетная полоса - это приложение CLI, которое помогает управлять вашими приложениями Flight. Он может создавать контроллеры, отображать все маршруты и многое другое. Он основан на отличной библиотеке [adhocore/php-cli](https://github.com/adhocore/php-cli).

## Установка

Установите с помощью Composer.

```bash
composer require flightphp/runway
```

## Базовая конфигурация

Первый раз, когда вы запускаете Runway, он проведет вас через процесс установки и создаст файл конфигурации `.runway.json` в корне вашего проекта. В этом файле будут содержаться некоторые необходимые конфигурации для правильной работы Runway.

## Использование

У Runway есть несколько команд, которые вы можете использовать для управления вашим приложением Flight. Есть два простых способа использовать Runway.

1. Если вы используете эталонный проект, вы можете запустить `php runway [команда]` из корня вашего проекта.
1. Если вы используете Runway в качестве пакета, установленного с помощью Composer, вы можете запустить `vendor/bin/runway [команда]` из корня вашего проекта.

Для любой команды вы можете добавить флаг `--help`, чтобы получить более подробную информацию о том, как использовать команду.

```bash
php runway routes --help
```

Вот несколько примеров:

### Создание контроллера

На основе конфигурации в вашем файле `.runway.json` контроллер по умолчанию будет создан для вас в каталоге `app/controllers/`.

```bash
php runway make:controller MyController
```

### Создание модели активной записи

На основе конфигурации в вашем файле `.runway.json` модель по умолчанию будет создана для вас в каталоге `app/records/`.

```bash
php runway make:record users
```

Если, например, у вас есть таблица `users` со следующей схемой: `id`, `name`, `email`, `created_at`, `updated_at`, будет создан файл, подобный следующему, в файле `app/records/UserRecord.php`:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * Класс активной записи для таблицы пользователей.
 * @link https://docs.flightphp.com/awesome-plugins/active-record
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $created_at
 * @property string $updated_at
 * // здесь также можно добавить отношения, когда вы определите их в массиве $relations
 * @property CompanyRecord $company Пример отношения
 */
class UserRecord extends \flight\ActiveRecord
{
    /**
     * @var array $relations Устанавливает отношения для модели
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

Это отобразит все маршруты, которые в данный момент зарегистрированы в Flight.

```bash
php runway routes
```

Если вы хотите просмотреть только определенные маршруты, вы можете добавить флаг для фильтрации маршрутов.

```bash
# Показать только GET маршруты
php runway routes --get

# Показать только POST маршруты
php runway routes --post

# и так далее
```

## Настройка Runway

Если вы создаете пакет для Flight или хотите добавить свои собственные пользовательские команды в свой проект, вы можете сделать это, создав каталог `src/commands/`, `flight/commands/`, `app/commands/` или `commands/` для вашего проекта/пакета.

Для создания команды вам просто нужно расширить класс `AbstractBaseCommand` и реализовать как минимум метод `__construct` и метод `execute`.

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
        $this->argument('<funny-gif>', 'Название забавного gif');
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

Смотрите [Документацию adhocore/php-cli](https://github.com/adhocore/php-cli) для более подробной информации о том, как создать свои пользовательские команды в вашем приложении Flight!