# Runway

Runway は、Flight アプリケーションを管理するのに役立つ CLI アプリケーションです。コントローラーを生成したり、すべてのルートを表示したり、その他さまざまな機能を提供します。これは優れた [adhocore/php-cli](https://github.com/adhocore/php-cli) ライブラリを基にしています。

コードを見るには [こちら](https://github.com/flightphp/runway) をクリックしてください。

## インストール

Composer でインストールします。

```bash
composer require flightphp/runway
```

## 基本設定

Runway を初めて実行すると、セットアッププロセスを実行し、プロジェクトのルートに `.runway.json` 設定ファイルを作成します。このファイルには、Runway が正常に動作するために必要な設定が含まれています。

## 使用方法

Runway には、Flight アプリケーションを管理するためのさまざまなコマンドがあります。Runway を使用する簡単な方法は 2 つあります。

1. スケルトンプロジェクトを使用している場合、プロジェクトのルートから `php runway [command]` を実行できます。
1. Composer 経由でインストールしたパッケージとして Runway を使用している場合、プロジェクトのルートから `vendor/bin/runway [command]` を実行できます。

任意のコマンドに対して、`--help` フラグを渡すことで、そのコマンドの使用方法についての詳細情報を取得できます。

```bash
php runway routes --help
```

以下にいくつかの例を示します：

### コントローラーの生成

`.runway.json` ファイルの設定に基づいて、デフォルトの場所に `app/controllers/` ディレクトリでコントローラーを生成します。

```bash
php runway make:controller MyController
```

### Active Record モデルの生成

`.runway.json` ファイルの設定に基づいて、デフォルトの場所に `app/records/` ディレクトリでコントローラーを生成します。

```bash
php runway make:record users
```

たとえば、`users` テーブルに以下のスキーマがある場合：`id`、`name`、`email`、`created_at`、`updated_at`、`app/records/UserRecord.php` ファイルに以下の類似したファイルが作成されます：

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * users テーブルの ActiveRecord クラス。
 * @link https://docs.flightphp.com/awesome-plugins/active-record
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $created_at
 * @property string $updated_at
 * // $relations 配列で定義したら、ここに関連付けを追加することもできます
 * @property CompanyRecord $company 関連付けの例
 */
class UserRecord extends \flight\ActiveRecord
{
    /**
     * @var array $relations モデルの関連付けを設定
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

### すべてのルートの表示

これにより、現在 Flight に登録されているすべてのルートが表示されます。

```bash
php runway routes
```

特定のルートのみを表示したい場合、ルートをフィルタリングするためのフラグを渡せます。

```bash
# GET ルートのみを表示
php runway routes --get

# POST ルートのみを表示
php runway routes --post

# など
```

## Runway のカスタマイズ

Flight 用にパッケージを作成する場合や、プロジェクトに独自のカスタムコマンドを追加したい場合、プロジェクト/パッケージ用に `src/commands/`、`flight/commands/`、`app/commands/`、または `commands/` ディレクトリを作成することで実現できます。さらにカスタマイズが必要な場合は、以下の設定セクションを参照してください。

コマンドを作成するには、`AbstractBaseCommand` クラスを拡張し、最低限 `__construct` メソッドと `execute` メソッドを実装するだけです。

```php
<?php

declare(strict_types=1);

namespace flight\commands;

class ExampleCommand extends AbstractBaseCommand
{
	/**
     * コンストラクタ
     *
     * @param array<string,mixed> $config .runway-config.json からの JSON 設定
     */
    public function __construct(array $config)
    {
        parent::__construct('make:example', 'ドキュメント用の例を作成', $config);
        $this->argument('<funny-gif>', '面白い GIF の名前');
    }

	/**
     * 関数を実行
     *
     * @return void
     */
    public function execute()
    {
        $io = $this->app()->io();

		$io->info('例を作成中...');

		// ここで何かをします

		$io->ok('例が作成されました！');
	}
}
```

Flight アプリケーションに独自のカスタムコマンドを構築する方法についての詳細は、[adhocore/php-cli ドキュメント](https://github.com/adhocore/php-cli) を参照してください！

### 設定

Runway の設定をカスタマイズする必要がある場合、プロジェクトのルートに `.runway-config.json` ファイルを作成できます。以下に設定できる追加の設定を示します：

```js
{

	// アプリケーション ディレクトリが配置されている場所
	"app_root": "app/",

	// ルート index ファイルが配置されているディレクトリ
	"index_root": "public/",

	// 他のプロジェクトのルートへのパス
	"root_paths": [
		"/home/user/different-project",
		"/var/www/another-project"
	],

	// ベース パスは通常設定する必要はありませんが、必要に応じて使用できます
	"base_paths": {
		"/includes/libs/vendor", // vendor ディレクトリなどのユニークなパスがある場合
	},

	// 最終パスは、コマンド ファイルを検索するプロジェクト内の場所
	"final_paths": {
		"src/diff-path/commands",
		"app/module/admin/commands",
	},

	// フルパスを追加したい場合、問題ありません（プロジェクト ルートからの絶対パスまたは相対パス）
	"paths": [
		"/home/user/different-project/src/diff-path/commands",
		"/var/www/another-project/app/module/admin/commands",
		"app/my-unique-commands"
	]
}
```