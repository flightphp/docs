# ランウェイ

ランウェイは、Flightアプリケーションを管理するのに役立つCLIアプリケーションです。コントローラを生成したり、すべてのルートを表示したりすることができます。このライブラリは優れた[adhocore/php-cli](https://github.com/adhocore/php-cli)ライブラリに基づいています。

## インストール

Composerでインストールしてください。

```bash
composer require flightphp/runway
```

## 基本的な設定

ランウェイを初めて実行すると、セットアッププロセスを実行し、プロジェクトのルートに`.runway.json`設定ファイルを作成します。このファイルには、ランウェイが正しく動作するために必要ないくつかの設定が含まれます。

## 使用法

ランウェイには、Flightアプリケーションを管理するためのいくつかのコマンドがあります。ランウェイの使用方法には簡単な2つの方法があります。

1. スケルトンプロジェクトを使用している場合、プロジェクトのルートから `php runway [command]` を実行できます。
1. Composer経由でインストールされたパッケージとしてRunwayを使用している場合、プロジェクトのルートから `vendor/bin/runway [command]` を実行できます。

どんなコマンドに対しても、`--help`フラグを渡すとコマンドの使用方法についての詳細情報を取得できます。

```bash
php runway routes --help
```

以下にいくつかの例を示します:

### コントローラの生成

`.runway.json`ファイルの設定に基づいて、デフォルトの場所にコントローラを `app/controllers/`ディレクトリに生成します。

```bash
php runway make:controller MyController
```

### Active Recordモデルの生成

`.runway.json`ファイルの設定に基づいて、デフォルトの場所にコントローラを `app/records/`ディレクトリに生成します。

```bash
php runway make:record users
```

たとえば、`users`テーブルが次のスキーマを持っている場合: `id`, `name`, `email`, `created_at`, `updated_at`、次のようなファイルが`app/records/UserRecord.php`に作成されます:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * ユーザーテーブル用のActiveRecordクラス.
 * @link https://docs.flightphp.com/awesome-plugins/active-record
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $created_at
 * @property string $updated_at
 * // リレーションシップが定義されたら、ここに関係を追加することもできます
 * @property CompanyRecord $company リレーションシップの例
 */
class UserRecord extends \flight\ActiveRecord
{
    /**
     * @var array $relations モデルの関係性を設定します
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

Flightに現在登録されているすべてのルートを表示します。

```bash
php runway routes
```

特定のルートのみ表示したい場合は、フラグを渡してルートをフィルタリングできます。

```bash
# GETリクエストのみ表示
php runway routes --get

# POSTリクエストのみ表示
php runway routes --post

# その他
```

## ランウェイのカスタマイズ

Flight向けのパッケージを作成するか、プロジェクトに独自のカスタムコマンドを追加したい場合は、プロジェクト/パッケージ用に `src/commands/`、 `flight/commands/`、 `app/commands/`、または `commands/` ディレクトリを作成してください。

コマンドを作成するには、 `AbstractBaseCommand`クラスを拡張し、`__construct`メソッドと最低限`execute`メソッドを実装します。

```php
<?php

declare(strict_types=1);

namespace flight\commands;

class ExampleCommand extends AbstractBaseCommand
{
	/**
     * コンストラクタ
     *
     * @param array<string,mixed> $config .runway-config.jsonからのJSON設定
     */
    public function __construct(array $config)
    {
        parent::__construct('make:example', 'ドキュメントの例を作成します', $config);
        $this->argument('<funny-gif>', '面白いGIFの名前');
    }

	/**
     * 関数の実行
     *
     * @return void
     */
    public function execute(string $controller)
    {
        $io = $this->app()->io();

		$io->info('例を作成中...');

		// ここで何かを実行

		$io->ok('例が作成されました！');
	}
}
```

独自のカスタムコマンドをFlightアプリケーションに組み込む方法については、[adhocore/php-cliドキュメント](https://github.com/adhocore/php-cli)を参照してください！