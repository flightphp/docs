# 飞行跑道

飞行跑道是一个 CLI 应用程序，可以帮助您管理您的 Flight 应用程序。它可以生成控制器，显示所有路由等。它基于优秀的 [adhocore/php-cli](https://github.com/adhocore/php-cli) 库。

## 安装

使用 composer 安装。

```bash
composer require flightphp/runway
```

## 基本配置

第一次运行 Runway 时，它将引导您完成设置流程，并在项目根目录中创建一个 `.runway.json` 配置文件。该文件将包含一些使 Runway 正常工作所必需的配置信息。

## 用法

Runway 有许多命令可用于管理您的 Flight 应用程序。有两种简单的方法可以使用 Runway。

1. 如果您是在骨架项目中使用，可以从项目的根目录运行 `php runway [command]`。
1. 如果您是通过 composer 安装的 Runway 包，则可以从项目的根目录运行 `vendor/bin/runway [command]`。

对于任何命令，您可以传入 `--help` 标志以获取有关如何使用该命令的更多信息。

```bash
php runway routes --help
```

这里有一些示例：

### 生成控制器

根据您的 `.runway.json` 文件中的配置，将在 `app/controllers/` 目录为您生成一个控制器。

```bash
php runway make:controller MyController
```

### 生成活动记录模型

根据您的 `.runway.json` 文件中的配置，默认位置将在 `app/records/` 目录为您生成一个控制器。

```bash
php runway make:record users
```

例如，如果您有 `users` 表，其模式如下：`id`、`name`、`email`、`created_at`、`updated_at`，一个类似以下的文件将在 `app/records/UserRecord.php` 文件中创建：

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * 用户表的 Active Record 类。
 * @link https://docs.flightphp.com/awesome-plugins/active-record
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $created_at
 * @property string $updated_at
 * // 一旦您在 $relations 数组中定义关系，还可以在这里添加关系
 * @property CompanyRecord $company 关系示例
 */
class UserRecord extends \flight\ActiveRecord
{
    /**
     * @var array $relations 为模型设置关系
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
     */
    protected array $relations = [];

    /**
     * 构造函数
     * @param mixed $databaseConnection 数据库连接
     */
    public function __construct($databaseConnection)
    {
        parent::__construct($databaseConnection, 'users');
    }
}
```

### 显示所有路由

这将显示所有当前注册到 Flight 的路由。

```bash
php runway routes
```

如果您只想查看特定路由，可以传入一个标志来过滤路由。

```bash
# 仅显示 GET 路由
php runway routes --get

# 仅显示 POST 路由
php runway routes --post

# 等等。
```

## 自定义 Runway

如果您要么为 Flight 创建一个包，要么想要将自定义命令添加到您的项目中，可以通过创建一个 `src/commands/`、`flight/commands/`、`app/commands/` 或 `commands/` 目录来实现。

要创建一个命令，您只需扩展 `AbstractBaseCommand` 类，并至少实现一个 `__construct` 方法和一个 `execute` 方法。

```php
<?php

declare(strict_types=1);

namespace flight\commands;

class ExampleCommand extends AbstractBaseCommand
{
	/**
     * 构造函数
     *
     * @param array<string,mixed> $config 来自 .runway-config.json 的 JSON 配置
     */
    public function __construct(array $config)
    {
        parent::__construct('make:example', '为文档创建示例', $config);
        $this->argument('<funny-gif>', '有趣 GIF 的名称');
    }

	/**
     * 执行函数
     *
     * @return void
     */
    public function execute(string $controller)
    {
        $io = $this->app()->io();

		$io->info('正在创建示例...');

		// 在这里做一些事情

		$io->ok('示例已创建！');
	}
}
```

查看 [adhocore/php-cli 文档](https://github.com/adhocore/php-cli) 了解如何将自定义命令构建到您的 Flight 应用程序中的更多信息！