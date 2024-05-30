# 滑走路

滑走路は、Flightアプリケーションを管理するのに役立つCLIアプリケーションです。コントローラを生成したり、すべてのルートを表示したりすることができます。これは、優れた[adhocore/php-cli](https://github.com/adhocore/php-cli)ライブラリに基づいています。

## インストール

Composerでインストールします。

```bash
composer require flightphp/runway
```

## 基本設定

滑走路を初めて実行すると、セットアッププロセスを実行してプロジェクトのルートに`.runway.json`構成ファイルを作成します。このファイルには、滑走路が正常に機能するために必要ないくつかの構成が含まれます。

## 使用法

滑走路には、Flightアプリケーションを管理するためのいくつかのコマンドがあります。滑走路の使用方法は2つあります。

1. スケルトンプロジェクトを使用している場合、プロジェクトのルートから`php runway [command]`を実行できます。
1. Composerを介してインストールされたパッケージとしてRunwayを使用している場合、プロジェクトのルートから`vendor/bin/runway [command]`を実行できます。

どのコマンドに対しても、`--help`フラグを渡すとコマンドの使用方法に関する詳細情報を取得できます。

```bash
php runway routes --help
```

以下はいくつかの例です：

### コントローラを生成

`.runway.json`ファイルの構成に基づいて、デフォルトの場所は`app/controllers/`ディレクトリにコントローラを生成します。

```bash
php runway make:controller MyController
```

### アクティブレコードモデルを生成

`.runway.json`ファイルの構成に基づいて、デフォルトの場所は`app/records/`ディレクトリにコントローラを生成します。

```bash
php runway make:record users
```

例えば`users`テーブルが`id`、`name`、`email`、`created_at`、`updated_at`のスキーマを持っている場合、`app/records/UserRecord.php`ファイルに次のようなファイルが作成されます：

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * usersテーブルのアクティブレコードクラス。
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
     * @var array $relations モデルの関係を設定
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
     */
    protected array $relations = [];

    /**
     * コンストラクタ
     * @param mixed $databaseConnection データベースへの接続
     */
    public function __construct($databaseConnection)
    {
        parent::__construct($databaseConnection, 'users');
    }
}
```

### すべてのルートを表示

現在Flightに登録されているすべてのルートを表示します。

```bash
php runway routes
```

特定のルートのみを表示したい場合は、ルートをフィルタリングするフラグを渡すことができます。

```bash
# GETルートのみを表示
php runway routes --get

# POSTルートのみを表示
php runway routes --post

# その他
```

## Runwayのカスタマイズ

Flight用のパッケージを作成するか、プロジェクトに独自のカスタムコマンドを追加したい場合は、プロジェクト/パッケージ用に`src/commands/`、`flight/commands/`、`app/commands/`、または`commands/`ディレクトリを作成します。

コマンドを作成するには、`AbstractBaseCommand`クラスを拡張し、最小限`__construct`メソッドと`execute`メソッドを実装します。

```php
<?php

declare(strict_types=1);

namespace flight\commands;

class ExampleCommand extends AbstractBaseCommand
{
	/**
     * 構築
     *
     * @param array<string,mixed> $config .runway-config.jsonからのJSON構成
     */
    public function __construct(array $config)
    {
        parent::__construct('make:example', 'ドキュメント用の例を作成', $config);
        $this->argument('<funny-gif>', '面白いGIFの名前');
    }

	/**
     * 関数を実行
     *
     * @return void
     */
    public function execute(string $controller)
    {
        $io = $this->app()->io();

		$io->info('例を作成中...');

		// ここで何かを行う

		$io->ok('例が作成されました！');
	}
}
```

Flightアプリケーションに独自のカスタムコマンドを組み込む方法の詳細については、[adhocore/php-cliのドキュメンテーション](https://github.com/adhocore/php-cli)を参照してください！