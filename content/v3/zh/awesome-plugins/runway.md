# Runway

Runway 是一个 CLI 应用程序，帮助您管理 Flight 应用程序。它可以生成控制器、显示所有路由等。它基于优秀的 [adhocore/php-cli](https://github.com/adhocore/php-cli) 库。

点击 [这里](https://github.com/flightphp/runway) 查看代码。

## 安装

使用 Composer 安装。

```bash
composer require flightphp/runway
```

## 基本配置

首次运行 Runway 时，它会引导您完成设置过程，并在项目根目录创建一个 `.runway.json` 配置文件。此文件将包含 Runway 正常工作的必要配置。

## 使用

Runway 具有多个命令，可用于管理您的 Flight 应用程序。有两种简单的方式使用 Runway。

1. 如果您使用的是骨架项目，可以从项目根目录运行 `php runway [command]`。
1. 如果您通过 Composer 安装 Runway 作为包，可以从项目根目录运行 `vendor/bin/runway [command]`。

对于任何命令，您可以传入 `--help` 标志以获取有关如何使用该命令的更多信息。

```bash
php runway routes --help
```

以下是一些示例：

### 生成控制器

基于您的 `.runway.json` 文件中的配置，默认位置将在 `app/controllers/` 目录中为您生成控制器。

```bash
php runway make:controller MyController
```

### 生成 Active Record 模型

基于您的 `.runway.json` 文件中的配置，默认位置将在 `app/records/` 目录中为您生成模型。

```bash
php runway make:record users
```

例如，如果您有 `users` 表，具有以下架构：`id`、`name`、`email`、`created_at`、`updated_at`，则将在 `app/records/UserRecord.php` 文件中创建类似以下的文件：

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
# 显示仅 GET 路由
php runway routes --get

# 显示仅 POST 路由
php runway routes --post

# 等。
```

## 自定义 Runway

如果您正在为 Flight 创建包，或者想在项目中添加自己的自定义命令，可以通过为您的项目/包创建 `src/commands/`、`flight/commands/`、`app/commands/` 或 `commands/` 目录来实现。如果需要进一步自定义，请参阅下面的配置部分。

要创建命令，只需扩展 `AbstractBaseCommand` 类，并至少实现 `__construct` 方法和 `execute` 方法。

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

有关如何将自定义命令构建到 Flight 应用程序中的更多信息，请参阅 [adhocore/php-cli 文档](https://github.com/adhocore/php-cli)！

### 配置

如果需要自定义 Runway 的配置，可以在项目根目录创建一个 `.runway-config.json` 文件。以下是一些可以设置的附加配置：

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