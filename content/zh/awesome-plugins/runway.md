# 飛道

飛道是一個CLI應用程序，可以幫助您管理您的Flight應用程序。它可以生成控制器，顯示所有路由等。它基於優秀的[adhocore/php-cli](https://github.com/adhocore/php-cli)庫。

## 安裝

使用composer安裝。

```bash
composer require flightphp/runway
```

## 基本配置

第一次運行飛道時，它將引導您完成一個設置過程並在項目的根目錄中創建一個`.runway.json`配置文件。該文件將包含一些Runway正常工作所需的必要配置。

## 用法

飛道有多個命令可用於管理您的Flight應用程序。有兩種簡單的方式可以使用飛道。

1. 如果您使用骨架項目，您可以從項目的根目錄運行`php runway [command]`。
1. 如果您將Runway作為通過composer安裝的包使用，您可以從項目的根目錄運行`vendor/bin/runway [command]`。

對於任何命令，您都可以插入`--help`標誌以獲取有關如何使用該命令的更多信息。

```bash
php runway routes --help
```

這裡有一些示例：

### 生成控制器

根據您的`.runway.json`文件中的配置，默認位置將為您在`app/controllers/`目錄中生成一個控制器。

```bash
php runway make:controller MyController
```

### 生成活動記錄模型

根據您的`.runway.json`文件中的配置，默認位置將為您在`app/records/`目錄中生成一個控制器。

```bash
php runway make:record users
```

例如，如果您有具有以下模式的`users`表：`id`、`name`、`email`、`created_at`、`updated_at`，則將在`app/records/UserRecord.php`文件中創建類似以下的文件：

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * 用於用戶表的ActiveRecord類。
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
     * @var array $relations 設置模型的關係
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
     */
    protected array $relations = [];

    /**
     * 構造函數
     * @param mixed $databaseConnection 對數據庫的連接
     */
    public function __construct($databaseConnection)
    {
        parent::__construct($databaseConnection, 'users');
    }
}
```

### 顯示所有路由

這將顯示當前註冊到Flight的所有路由。

```bash
php runway routes
```

如果您只想查看特定路由，您可以插入標誌以過濾路由。

```bash
# 顯示僅GET路由
php runway routes --get

# 顯示僅POST路由
php runway routes --post

# 等等
```

## 自定義飛道

如果您要為Flight創建包，或者想在您的項目中添加自己的自定義命令，您可以通過為您的項目/包創建一個`src/commands/`、`flight/commands/`、`app/commands/`或`commands/`目錄來實現。

要創建一個命令，您只需擴展`AbstractBaseCommand`類，並最少實現一個`__construct`方法和一個`execute`方法。

```php
<?php

declare(strict_types=1);

namespace flight\commands;

class ExampleCommand extends AbstractBaseCommand
{
	/**
     * 構造函數
     *
     * @param array<string,mixed> $config 從.runway-config.json獲取的JSON配置
     */
    public function __construct(array $config)
    {
        parent::__construct('make:example', '為文檔創建一個示例', $config);
        $this->argument('<funny-gif>', '有趣gif的名稱');
    }

	/**
     * 執行該函數
     *
     * @return void
     */
    public function execute(string $controller)
    {
        $io = $this->app()->io();

		$io->info('正在創建示例...');

		// 在這裡執行某些操作

		$io->ok('示例已創建！');
	}
}
```

查看[adhocore/php-cli文檔](https://github.com/adhocore/php-cli)了解如何將自定義命令集成到您的Flight應用程序中的更多信息！