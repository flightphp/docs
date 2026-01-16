# Runway

Runway 是一个 CLI 应用程序，帮助您管理您的 Flight 应用程序。它可以生成控制器、显示所有路由等。它基于优秀的 [adhocore/php-cli](https://github.com/adhocore/php-cli) 库。

点击 [这里](https://github.com/flightphp/runway) 查看代码。

## 安装

使用 Composer 安装。

```bash
composer require flightphp/runway
```

## 基本配置

首次运行 Runway 时，它将尝试在 `app/config/config.php` 中通过 `'runway'` 键查找 `runway` 配置。

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

> **注意** - 从 **v1.2.0** 开始，`.runway-config.json` 已弃用。请将您的配置迁移到 `app/config/config.php`。您可以使用 `php runway config:migrate` 命令轻松完成此操作。

### 项目根目录检测

Runway 足够智能，即使您从子目录运行它，也能检测到项目根目录。它会查找像 `composer.json`、`.git` 或 `app/config/config.php` 这样的指示符来确定项目根目录在哪里。这意味着您可以在项目中的任何位置运行 Runway 命令！

## 使用

Runway 有许多命令可用于管理您的 Flight 应用程序。有两种简单的方式使用 Runway。

1. 如果您使用的是骨架项目，您可以从项目根目录运行 `php runway [command]`。
1. 如果您通过 Composer 安装 Runway 作为包，您可以从项目根目录运行 `vendor/bin/runway [command]`。

### 命令列表

您可以通过运行 `php runway` 命令查看所有可用命令的列表。

```bash
php runway
```

### 命令帮助

对于任何命令，您可以传入 `--help` 标志以获取有关如何使用该命令的更多信息。

```bash
php runway routes --help
```

这里有一些示例：

### 生成控制器

基于 `runway.app_root` 中的配置，位置将在 `app/controllers/` 目录中为您生成控制器。

```bash
php runway make:controller MyController
```

### 生成 Active Record 模型

首先确保您已安装 [Active Record](/awesome-plugins/active-record) 插件。基于 `runway.app_root` 中的配置，位置将在 `app/records/` 目录中为您生成记录。

```bash
php runway make:record users
```

例如，如果您有 `users` 表，架构如下：`id`、`name`、`email`、`created_at`、`updated_at`，则将在 `app/records/UserRecord.php` 文件中创建一个类似以下的文件：

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

### 显示所有路由

这将显示当前注册到 Flight 的所有路由。

```bash
php runway routes
```

如果您只想查看特定路由，可以传入标志来过滤路由。

```bash
# 只显示 GET 路由
php runway routes --get

# 只显示 POST 路由
php runway routes --post

# 等。
```

## 将自定义命令添加到 Runway

如果您正在为 Flight 创建包，或者想将自己的自定义命令添加到项目中，您可以通过为您的项目/包创建 `src/commands/`、`flight/commands/`、`app/commands/` 或 `commands/` 目录来实现。如果需要进一步自定义，请参阅下面的配置部分。

要创建命令，您只需扩展 `AbstractBaseCommand` 类，并至少实现 `__construct` 方法和 `execute` 方法。

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

请参阅 [adhocore/php-cli 文档](https://github.com/adhocore/php-cli) 以获取有关如何为您的 Flight 应用程序构建自定义命令的更多信息！

## 配置管理

由于从 `v1.2.0` 开始配置已移动到 `app/config/config.php`，因此有一些辅助命令来管理配置。

### 迁移旧配置

如果您有旧的 `.runway-config.json` 文件，您可以使用以下命令轻松将其迁移到 `app/config/config.php`：

```bash
php runway config:migrate
```

### 设置配置值

您可以使用 `config:set` 命令设置配置值。这在您想更新配置值而无需打开文件时很有用。

```bash
php runway config:set app_root "app/"
```

### 获取配置值

您可以使用 `config:get` 命令获取配置值。

```bash
php runway config:get app_root
```

## 所有 Runway 配置

如果您需要自定义 Runway 的配置，您可以在 `app/config/config.php` 中设置这些值。下面是一些您可以设置的附加配置：

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

### 访问配置

如果您需要有效地访问配置值，您可以通过 `__construct` 方法或 `app()` 方法访问它们。同样重要的是要注意，如果您有 `app/config/services.php` 文件，这些服务也将可供您的命令使用。

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

## AI 助手包装器

Runway 有一些辅助包装器，使 AI 更容易生成命令。您可以使用 `addOption` 和 `addArgument`，方式类似于 Symfony Console。这在使用 AI 工具生成命令时很有帮助。

```php
public function __construct(array $config)
{
    parent::__construct('make:example', 'Create an example for the documentation', $config);
    
    // The mode argument is nullable and defaults to completely optional
    $this->addOption('name', 'The name of the example', null);
}
```