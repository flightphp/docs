# 跑道

跑道是一个CLI应用程序，可帮助您管理您的Flight应用程序。它可以生成控制器，显示所有路由等。它基于优秀的 [adhocore/php-cli](https://github.com/adhocore/php-cli) 库。

点击[这里](https://github.com/flightphp/runway) 查看代码。

## 安装

使用 composer 安装。

```bash
composer require flightphp/runway
```

## 基本配置

第一次运行跑道时，它将引导您完成设置过程并在项目根目录中创建一个 `.runway.json` 配置文件。此文件将包含一些Runway正常工作所需的配置。

## 用法

跑道有许多命令可用于管理您的Flight应用程序。有两种简单的方法可以使用跑道。

1. 如果您使用的是骨架项目，可以从项目的根目录运行 `php runway [command]`。
1. 如果您是通过composer安装的包来使用跑道，您可以从项目的根目录运行 `vendor/bin/runway [command]`。

对于任何命令，您都可以传入 `--help` 标志以获取有关如何使用命令的更多信息。

```bash
php runway routes --help
```

以下是一些示例：

### 生成控制器

根据您的 `.runway.json` 文件中的配置，默认位置将为您在 `app/controllers/` 目录中生成一个控制器。

```bash
php runway make:controller MyController
```

### 生成活动记录模型

根据您的 `.runway.json` 文件中的配置，默认位置将为您在 `app/records/` 目录中生成一个控制器。

```bash
php runway make:record users
```

例如，如果您有以下架构的 `users` 表：`id`，`name`，`email`，`created_at`，`updated_at`，则类似以下内容的文件将在 `app/records/UserRecord.php` 文件中创建：

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * users 表的ActiveRecord类。
 * @link https://docs.flightphp.com/awesome-plugins/active-record
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $created_at
 * @property string $updated_at
 * // 一旦在 $relations 数组中定义了关系，您也可以在此处添加关系
 * @property CompanyRecord $company 关系示例
 */
class UserRecord extends \flight\ActiveRecord
{
    /**
     * @var array $relations 为该模型设置关系
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

这将显示当前在Flight中注册的所有路由。

```bash
php runway routes
```

如果您只想查看特定路由，您可以传入一个标志以过滤路由。

```bash
# 仅显示 GET 路由
php runway routes --get

# 仅显示 POST 路由
php runway routes --post

# 等等
```

## 自定义跑道

如果您要为Flight创建一个包，或者想要将自定义命令添加到您的项目中，您可以通过为您的项目/包创建一个 `src/commands/`, `flight/commands/`, `app/commands/`, 或 `commands/` 目录来实现此目的。

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
     * @param array<string,mixed> $config 来自 .runway-config.json 的JSON配置
     */
    public function __construct(array $config)
    {
        parent::__construct('make:example', '为文档创建示例', $config);
        $this->argument('<funny-gif>', '滑稽gif的名称');
    }

	/**
     * 执行函数
     *
     * @return void
     */
    public function execute(string $controller)
    {
        $io = $this->app()->io();

		$io->info('创建示例...');

		// 在这里执行操作

		$io->ok('示例已创建！');
	}
}
```

有关如何将自定义命令构建到您的Flight应用程序中的更多信息，请参阅 [adhocore/php-cli 文档](https://github.com/adhocore/php-cli)。