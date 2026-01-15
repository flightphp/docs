# Runway

Runway は、Flight アプリケーションを管理するのに役立つ CLI アプリケーションです。コントローラーを生成したり、全ルートを表示したり、その他多くの機能を提供します。これは優れた [adhocore/php-cli](https://github.com/adhocore/php-cli) ライブラリを基にしています。

コードを見るには [こちら](https://github.com/flightphp/runway) をクリックしてください。

## インストール

Composer を使用してインストールします。

```bash
composer require flightphp/runway
```

## 基本設定

Runway を初めて実行すると、`app/config/config.php` 内の `'runway'` キーから `runway` 設定を探そうとします。

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

> **注意** - **v1.2.0** 以降、`.runway-config.json` は非推奨です。設定を `app/config/config.php` に移行してください。これを簡単に実行するには、`php runway config:migrate` コマンドを使用できます。

### プロジェクトルートの検出

Runway は、プロジェクトのルートを検出するのに十分賢いです。サブディレクトリから実行した場合でも、`composer.json`、`.git`、または `app/config/config.php` などの指標を探してプロジェクトルートを決定します。これにより、プロジェクト内のどこからでも Runway コマンドを実行できます！

## 使用方法

Runway には、Flight アプリケーションを管理するためのいくつかのコマンドがあります。Runway を使用する簡単な方法は 2 つあります。

1. スケルトンプロジェクトを使用している場合、プロジェクトのルートから `php runway [command]` を実行できます。
1. Composer を介してインストールしたパッケージとして Runway を使用している場合、プロジェクトのルートから `vendor/bin/runway [command]` を実行できます。

### コマンドリスト

`php runway` コマンドを実行することで、利用可能なすべてのコマンドのリストを表示できます。

```bash
php runway
```

### コマンドヘルプ

任意のコマンドに対して、`--help` フラグを渡すことでコマンドの使用方法に関する詳細情報を取得できます。

```bash
php runway routes --help
```

以下にいくつかの例を示します。

### コントローラーの生成

`runway.app_root` の設定に基づいて、場所が `app/controllers/` ディレクトリにコントローラーを生成します。

```bash
php runway make:controller MyController
```

### Active Record モデルの生成

まず、[Active Record](/awesome-plugins/active-record) プラグインをインストールしたことを確認してください。`runway.app_root` の設定に基づいて、場所が `app/records/` ディレクトリにレコードを生成します。

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

### 全ルートの表示

これにより、現在 Flight に登録されているすべてのルートが表示されます。

```bash
php runway routes
```

特定のルートのみを表示したい場合、フラグを渡してルートをフィルタリングできます。

```bash
# GET ルートのみを表示
php runway routes --get

# POST ルートのみを表示
php runway routes --post

# など
```

## Runway にカスタムコマンドを追加

Flight のパッケージを作成している場合、またはプロジェクトに独自のカスタムコマンドを追加したい場合、`src/commands/`、`flight/commands/`、`app/commands/`、または `commands/` ディレクトリを作成することで実現できます。さらにカスタマイズが必要な場合、以下の設定セクションを参照してください。

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
     * @param array<string,mixed> $config app/config/config.php からの設定
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

		// ここで何かを実行

		$io->ok('例が作成されました！');
	}
}
```

Flight アプリケーションに独自のカスタムコマンドを構築する方法の詳細については、[adhocore/php-cli ドキュメント](https://github.com/adhocore/php-cli) を参照してください！

## 設定管理

v1.2.0 以降、設定が `app/config/config.php` に移行されたため、設定を管理するためのヘルパーコマンドがいくつかあります。

### 古い設定の移行

古い `.runway-config.json` ファイルがある場合、次のコマンドで簡単に `app/config/config.php` に移行できます：

```bash
php runway config:migrate
```

### 設定値の設定

`config:set` コマンドを使用して設定値を設定できます。ファイルを開かずに設定値を更新したい場合に便利です。

```bash
php runway config:set app_root "app/"
```

### 設定値の取得

`config:get` コマンドを使用して設定値を取得できます。

```bash
php runway config:get app_root
```

## すべての Runway 設定

Runway の設定をカスタマイズする必要がある場合、`app/config/config.php` でこれらの値を設定できます。以下に設定できる追加の設定を示します：

```php
<?php
// app/config/config.php
return [
    // ... 他の設定値 ...

    'runway' => [
        // アプリケーション ディレクトリが配置されている場所
        'app_root' => 'app/',

        // ルートインデックスファイルが配置されているディレクトリ
        'index_root' => 'public/',

        // 他のプロジェクトのルートへのパス
        'root_paths' => [
            '/home/user/different-project',
            '/var/www/another-project'
        ],

        // ベースパスは通常設定する必要はありませんが、必要に応じてここにあります
        'base_paths' => [
            '/includes/libs/vendor', // vendor ディレクトリに非常に独自のパスがある場合など
        ],

        // 最終パスは、プロジェクト内のコマンドファイルを検索する場所です
        'final_paths' => [
            'src/diff-path/commands',
            'app/module/admin/commands',
        ],

        // フルパスを追加したい場合、問題ありません（プロジェクトルートからの絶対または相対パス）
        'paths' => [
            '/home/user/different-project/src/diff-path/commands',
            '/var/www/another-project/app/module/admin/commands',
            'app/my-unique-commands'
        ]
    ]
];
```

### 設定へのアクセス

設定値に効果的にアクセスする必要がある場合、`__construct` メソッドまたは `app()` メソッド経由でアクセスできます。また、`app/config/services.php` ファイルがある場合、そのサービスもコマンドで利用可能であることに注意してください。

```php
public function execute()
{
    $io = $this->app()->io();
    
    // 設定にアクセス
    $app_root = $this->config['runway']['app_root'];
    
    // データベース接続などのサービスにアクセス
    $database = $this->config['database']
    
    // ...
}
```

## AI ヘルパー ラッパー

Runway には、AI がコマンドを生成しやすくするためのヘルパー ラッパーがいくつかあります。Symfony Console に似た方法で `addOption` および `addArgument` を使用できます。AI ツールを使用してコマンドを生成する場合に役立ちます。

```php
public function __construct(array $config)
{
    parent::__construct('make:example', 'ドキュメント用の例を作成', $config);
    
    // name オプションは null 可能で、完全にオプションのデフォルトです
    $this->addOption('name', '例の名前', null);
}
```