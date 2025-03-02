# ランウェイ

ランウェイはCLIアプリケーションで、Flightアプリケーションの管理を支援します。コントローラを生成したり、すべてのルートを表示したりすることができます。優れた[adhocore/php-cli](https://github.com/adhocore/php-cli)ライブラリに基づいています。

[こちらをクリック](https://github.com/flightphp/runway)して、コードを表示してください。

## インストール

Composerを使用してインストールしてください。

```bash
composer require flightphp/runway
```

## 基本設定

ランウェイを実行する最初の回は、セットアッププロセスを進め、プロジェクトのルートに`.runway.json`構成ファイルを作成します。このファイルには、ランウェイが正しく動作するために必要ないくつかの構成が含まれています。

## 使用法

ランウェイには、Flightアプリケーションを管理するために使用できる複数のコマンドがあります。ランウェイを使用する方法は2つあります。

1. スケルトンプロジェクトを使用している場合、プロジェクトのルートから `php runway [command]` を実行できます。
1. Composerを介してインストールされたパッケージとしてRunwayを使用している場合、プロジェクトのルートから `vendor/bin/runway [command]` を実行できます。

任意のコマンドに対して、`--help`フラグを渡すと、そのコマンドの使用方法に関するより詳細な情報を取得できます。

```bash
php runway routes --help
```

以下はいくつかの例です。

### コントローラを生成する

`.runway.json`ファイルの構成に基づいて、デフォルトの場所は `app/controllers/` ディレクトリにコントローラを生成します。

```bash
php runway make:controller MyController
```

### アクティブレコードモデルを生成する

`.runway.json`ファイルの構成に基づいて、デフォルトの場所は `app/records/` ディレクトリにコントローラを生成します。

```bash
php runway make:record users
```

たとえば、次のスキーマを持つ `users` テーブルがある場合：`id`、`name`、`email`、`created_at`、`updated_at`、`app/records/UserRecord.php` ファイルに類似したファイルが作成されます：

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * ユーザーテーブルのアクティブレコードクラス。
 * @link https://docs.flightphp.com/awesome-plugins/active-record
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $created_at
 * @property string $updated_at
 * // 関係を定義した場合、ここに関係を追加できます
 * @property CompanyRecord $company 関係の例
 */
class UserRecord extends \flight\ActiveRecord
{
    /**
     * @var array $relations モデルの関係を設定します
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

### すべてのルートを表示する

登録されているすべてのFlightのルートを表示します。

```bash
php runway routes
```

特定のルートのみを表示したい場合、フラグを渡してルートをフィルタリングできます。

```bash
# GETルートのみを表示
php runway routes --get

# POSTルートのみを表示
php runway routes --post

# など
```

## ランウェイのカスタマイズ

Flight向けのパッケージを作成しているか、プロジェクトに独自のカスタムコマンドを追加したい場合は、プロジェクト/パッケージ向けに `src/commands/`、`flight/commands/`、`app/commands/`、または `commands/` ディレクトリを作成してください。

コマンドを作成するには、`AbstractBaseCommand`クラスを拡張し、`__construct`メソッドと`execute`メソッドを最低限実装します。

```php
<?php

declare(strict_types=1);

namespace flight\commands;

class ExampleCommand extends AbstractBaseCommand
{
	/**
     * コンストラクタ
     *
     * @param array<string,mixed> $config .runway-config.jsonからのJSON構成
     */
    public function __construct(array $config)
    {
        parent::__construct('make:example', 'ドキュメントの例を作成', $config);
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

		$io->info('例を作成します...');

		// ここで何かを実行

		$io->ok('例が作成されました！');
	}
}
```

独自のカスタムコマンドをFlightアプリケーションに組み込む方法については、[adhocore/php-cliドキュメント](https://github.com/adhocore/php-cli)を参照してください！