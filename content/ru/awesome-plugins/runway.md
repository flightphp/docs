# Взлетная полоса

Взлетная полоса - это приложение CLI, которое помогает управлять вашими приложениями Flight. Он может генерировать контроллеры, отображать все маршруты и многое другое. Он основан на отличной библиотеке [adhocore/php-cli](https://github.com/adhocore/php-cli).

## Установка

Установите с помощью composer.

```bash
composer require flightphp/runway
```

## Базовая конфигурация

Первый раз, когда вы запускаете Взлетную полосу, она проведет вас через процесс настройки и создаст файл конфигурации `.runway.json` в корне вашего проекта. Этот файл будет содержать необходимые конфигурации для работы Взлетной полосы правильно.

## Использование

У Взлетной полосы есть несколько команд, которые вы можете использовать для управления вашим приложением Flight. Существуют два простых способа использования Взлетной полосы.

1. Если вы используете каркасный проект, вы можете запустить `php runway [команда]` из корня вашего проекта.
1. Если вы используете Взлетную полосу в качестве пакета, установленного через composer, вы можете запустить `vendor/bin/runway [команда]` из корня вашего проекта.

Для любой команды вы можете передать флаг `--help`, чтобы получить больше информации о том, как использовать команду.

```bash
php runway routes --help
```

Вот несколько примеров:

### Создание контроллера

На основе конфигурации в вашем файле `.runway.json` контроллер будет сгенерирован для вас в каталоге `app/controllers/`.

```bash
php runway make:controller MyController
```

### Создание модели Active Record

На основе конфигурации в вашем файле `.runway.json` модель Active Record будет сгенерирована для вас в каталоге `app/records/`.

```bash
php runway make:record users
```

Если, например, у вас есть таблица `users` со следующей схемой: `id`, `name`, `email`, `created_at`, `updated_at`, будет создан файл, подобный следующему, в файле `app/records/UserRecord.php`:

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
     * @param mixed $databaseConnection Подключение к базе данных
     */
    public function __construct($databaseConnection)
    {
        parent::__construct($databaseConnection, 'users');
    }
}
```

### Отобразить все маршруты

Это отобразит все маршруты, зарегистрированные в данный момент в Flight.

```bash
php runway routes
```

Если вы хотите просмотреть только определенные маршруты, вы можете передать флаг для фильтрации маршрутов.

```bash
# Показать только GET маршруты
php runway routes --get

# Показать только POST маршруты
php runway routes --post

# и т.д.
```

## Настройка Взлетной полосы

Если вы создаете пакет для Flight или хотите добавить свои собственные пользовательские команды в свой проект, вы можете сделать это, создав каталог `src/commands/`, `flight/commands/`, `app/commands/` или `commands/` для вашего проекта/пакета.

Чтобы создать команду, просто расширьте класс `AbstractBaseCommand` и как минимум реализуйте метод `__construct` и метод `execute`.

```php
<?php

declare(strict_types=1);

namespace flight\commands;

class ExampleCommand extends AbstractBaseCommand
{
	/**
     * Создать
     *
     * @param array<string,mixed> $config JSON конфиг из .runway-config.json
     */
    public function __construct(array $config)
    {
        parent::__construct('make:example', 'Создать пример для документации', $config);
        $this->argument('<funny-gif>', 'Имя смешного gif');
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

		// Выполнить действия здесь

		$io->ok('Пример создан!');
	}
}
```

Смотрите [Документацию adhocore/php-cli](https://github.com/adhocore/php-cli) для получения дополнительной информации о том, как добавить свои собственные пользовательские команды в ваше приложение Flight!