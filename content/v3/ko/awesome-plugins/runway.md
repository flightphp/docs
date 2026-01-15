# Runway

Runway는 Flight 애플리케이션을 관리하는 데 도움이 되는 CLI 애플리케이션입니다. 컨트롤러를 생성하고, 모든 경로를 표시하며, 그 외 더 많은 기능을 제공합니다. 이는 우수한 [adhocore/php-cli](https://github.com/adhocore/php-cli) 라이브러리를 기반으로 합니다.

코드 보기: [여기](https://github.com/flightphp/runway)를 클릭하세요.

## Installation

composer를 사용하여 설치하세요.

```bash
composer require flightphp/runway
```

## Basic Configuration

Runway를 처음 실행할 때, `app/config/config.php`의 `'runway'` 키를 통해 `runway` 구성을 찾으려고 시도합니다.

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

> **NOTE** - **v1.2.0**부터 `.runway-config.json`은 더 이상 사용되지 않습니다. 구성을 `app/config/config.php`로 마이그레이션하세요. `php runway config:migrate` 명령으로 쉽게 할 수 있습니다.

### Project Root Detection

Runway는 하위 디렉토리에서 실행하더라도 프로젝트 루트를 감지할 수 있을 만큼 똑똑합니다. `composer.json`, `.git`, 또는 `app/config/config.php`와 같은 지표를 찾아 프로젝트 루트를 결정합니다. 따라서 프로젝트의 어디서나 Runway 명령을 실행할 수 있습니다!

## Usage

Runway에는 Flight 애플리케이션을 관리하는 데 사용할 수 있는 여러 명령이 있습니다. Runway를 사용하는 두 가지 쉬운 방법이 있습니다.

1. 스켈레톤 프로젝트를 사용 중이라면, 프로젝트 루트에서 `php runway [command]`를 실행할 수 있습니다.
1. composer를 통해 설치된 패키지로 Runway를 사용 중이라면, 프로젝트 루트에서 `vendor/bin/runway [command]`를 실행할 수 있습니다.

### Command List

`php runway` 명령을 실행하여 모든 사용 가능한 명령 목록을 볼 수 있습니다.

```bash
php runway
```

### Command Help

어떤 명령에 대해서든 `--help` 플래그를 전달하여 명령 사용 방법에 대한 더 많은 정보를 얻을 수 있습니다.

```bash
php runway routes --help
```

다음은 몇 가지 예시입니다:

### Generate a Controller

`runway.app_root`의 구성에 따라, `app/controllers/` 디렉토리에 컨트롤러를 생성합니다.

```bash
php runway make:controller MyController
```

### Generate an Active Record Model

먼저 [Active Record](/awesome-plugins/active-record) 플러그인을 설치했는지 확인하세요. `runway.app_root`의 구성에 따라, `app/records/` 디렉토리에 레코드를 생성합니다.

```bash
php runway make:record users
```

예를 들어 `users` 테이블이 `id`, `name`, `email`, `created_at`, `updated_at` 스키마를 가진 경우, `app/records/UserRecord.php` 파일에 다음과 유사한 파일이 생성됩니다:

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

### Display All Routes

이것은 현재 Flight에 등록된 모든 경로를 표시합니다.

```bash
php runway routes
```

특정 경로만 보려면, 경로를 필터링하는 플래그를 전달할 수 있습니다.

```bash
# GET 경로만 표시
php runway routes --get

# POST 경로만 표시
php runway routes --post

# 등.
```

## Adding Custom Commands to Runway

Flight용 패키지를 만들거나 프로젝트에 사용자 지정 명령을 추가하려면, 프로젝트/패키지에 `src/commands/`, `flight/commands/`, `app/commands/`, 또는 `commands/` 디렉토리를 생성하여 할 수 있습니다. 추가 사용자 지정이 필요하면 아래 구성 섹션을 참조하세요.

명령을 생성하려면 `AbstractBaseCommand` 클래스를 확장하고, 최소한 `__construct` 메서드와 `execute` 메서드를 구현하세요.

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

Flight 애플리케이션에 사용자 지정 명령을 구축하는 방법에 대한 자세한 정보는 [adhocore/php-cli Documentation](https://github.com/adhocore/php-cli)을 참조하세요!

## Configuration Management

`v1.2.0`부터 구성이 `app/config/config.php`로 이동되었으므로, 구성을 관리하는 몇 가지 도우미 명령이 있습니다.

### Migrate Old Config

이전 `.runway-config.json` 파일이 있다면, 다음 명령으로 `app/config/config.php`로 쉽게 마이그레이션할 수 있습니다:

```bash
php runway config:migrate
```

### Set Configuration Value

`config:set` 명령을 사용하여 구성 값을 설정할 수 있습니다. 파일을 열지 않고 구성 값을 업데이트하려는 경우 유용합니다.

```bash
php runway config:set app_root "app/"
```

### Get Configuration Value

`config:get` 명령을 사용하여 구성 값을 가져올 수 있습니다.

```bash
php runway config:get app_root
```

## All Runway Configurations

Runway 구성을 사용자 지정해야 한다면, `app/config/config.php`에 이러한 값을 설정할 수 있습니다. 아래는 설정할 수 있는 몇 가지 추가 구성입니다:

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

### Accessing Configuration

구성 값을 효과적으로 액세스해야 한다면, `__construct` 메서드나 `app()` 메서드를 통해 액세스할 수 있습니다. 또한 `app/config/services.php` 파일이 있는 경우, 해당 서비스도 명령에서 사용할 수 있습니다.

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

## AI Helper Wrappers

Runway에는 AI가 명령을 생성하기 쉽게 하는 몇 가지 도우미 래퍼가 있습니다. Symfony Console과 유사하게 `addOption`과 `addArgument`를 사용할 수 있습니다. AI 도구를 사용하여 명령을 생성하는 경우 유용합니다.

```php
public function __construct(array $config)
{
    parent::__construct('make:example', 'Create an example for the documentation', $config);
    
    // The mode argument is nullable and defaults to completely optional
    $this->addOption('name', 'The name of the example', null);
}
```