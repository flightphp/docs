# 設定

## 概要

Flightは、アプリケーションのニーズに合わせてフレームワークのさまざまな側面を設定するためのシンプルな方法を提供します。一部はデフォルトで設定されていますが、必要に応じて上書きできます。また、アプリケーション全体で使用する独自の変数を設定することもできます。

明確で階層化された設定（ファイルのデフォルト値＋環境のシークレット）は、[AIコーディングツール](/learn/ai)にも役立ちます。エージェントは、リテラルを1箇所、シークレットを1箇所で学ぶことができ、コントローラ内で`$_ENV`を読み取ることを独自に発明する必要がありません。

## 理解

`set`メソッドを使用して設定値を設定することで、Flightの特定の動作をカスタマイズできます。

```php
Flight::set('flight.log_errors', true);
```

構造化されたアプリケーション（[スケルトン](https://github.com/flightphp/skeleton)を含む）では、通常、`app/config/config.php`からプロジェクト設定を読み込み、関連するキーをEngineに適用します（例: `flight.base_url`、`flight.views.path`）。また、いたるところでグローバルを読み取る代わりに、小さな設定オブジェクトをコントローラに注入することもできます。これはテストや、`AGENTS.md`に従うエージェントにとってより親しみやすい方法です。

## 基本的な使い方

### Flightの設定オプション

利用可能な設定項目は次のとおりです。

- **flight.base_url** `?string` - Flightがサブディレクトリで実行されている場合、リクエストのベースURLを上書きします。（デフォルト: null）
- **flight.case_sensitive** `bool` - URLの大文字小文字を区別してマッチングします。（デフォルト: false）
- **flight.handle_errors** `bool` - Flightがすべてのエラーを内部で処理できるようにします。（デフォルト: true）
  - FlightにデフォルトのPHPの動作の代わりにエラーを処理させたい場合は、これをtrueにする必要があります。
  - [Tracy](/awesome-plugins/tracy)をインストールしている場合は、Tracyがエラーを処理できるようにこれをfalseに設定することをお勧めします。
  - [APM](/awesome-plugins/apm)プラグインをインストールしている場合は、APMがエラーをログに記録できるようにこれをtrueに設定することをお勧めします。
- **flight.log_errors** `bool` - エラーをWebサーバーのエラーログファイルに記録します。（デフォルト: false）
  - [Tracy](/awesome-plugins/tracy)をインストールしている場合、Tracyはこの設定ではなく、Tracyの設定に基づいてエラーをログに記録します。
- **flight.debug** `bool` - エラー発生時に詳細なエラー情報（例外メッセージ、コード、スタックトレース）をブラウザに出力します。（デフォルト: false）
  - **本番環境では決して有効にしないでください。** 内部アプリケーションの詳細が漏洩します。ローカル開発またはステージング環境でのみ使用してください。
  - `false`の場合、代わりに汎用の`500 Internal Server Error`が表示されます。`flight.log_errors`と組み合わせて、サーバー側でエラーを記録してください。
- **flight.allow_method_override** `bool` - `X-HTTP-Method-Override`リクエストヘッダーまたはPOSTボディの`_method`フィールドを介してHTTPメソッドを上書きできるようにします。（デフォルト: true）
  - HTMLフォームベースのメソッドスプーフィングを必要としないアプリケーションでは、**これを`false`に設定することをお勧めします**。これにより、標準のPOSTフォームを介した`DELETE`や`PUT`リクエストの偽造を防ぐことができます。
  - 詳細は[セキュリティ](/learn/security#flight-configuration-hardening)を参照してください。
- **flight.views.path** `string` - ビューテンプレートファイルを含むディレクトリ。（デフォルト: ./views）
- **flight.views.extension** `string` - ビューテンプレートファイルの拡張子。（デフォルト: `.php`。公式スケルトンではTwig使用時に`.twig`に設定されます）
- **flight.views.restrict_to_path** `bool` - `true`の場合、Flightネイティブの`View`は`flight.views.path`内に解決されるテンプレートファイルのみを受け入れます。（デフォルト: `false`）。ネイティブビューを使用するアプリでは**これをオンにしてください**。[セキュリティ](/learn/security#flightviewsrestrict_to_path)を参照してください。
- **flight.content_length** `bool` - `Content-Length`ヘッダーを設定します。（デフォルト: true）
  - [Tracy](/awesome-plugins/tracy)を使用している場合、Tracyが正しく表示されるようにこれをfalseに設定する必要があります。
- **flight.v2.output_buffering** `bool` - レガシー出力バッファリングを使用します。[v3への移行](migrating-to-v3)を参照してください。（デフォルト: false）

### ローダー設定

ローダーにはもう1つの設定項目があります。これにより、クラス名に`_`を含むクラスをオートロードできます。

```php
// アンダースコアを使用したクラスロードを有効にする
// デフォルトはtrue
Loader::$v2ClassLoading = false;
```

[オートローディング](/learn/autoloading)は、**フォルダーの大文字小文字**が名前空間と一致していることにも依存することに注意してください。特にスケルトンの`App\` + `app/Controller/`レイアウトでは重要です。

### プロジェクト設定と`.env`（スケルトンパターン）

Flightコアは`.env`ファイルを必要としません。多くのアプリはPHP設定配列のみを使用します。公式スケルトンは、Runwayが**リテラル**設定を安全に書き換えられるようにしながら、シークレットをgitから除外できるよう設定を階層化しています。

1. **`.env` / 実際の環境** — シークレットとデプロイの上書き（gitignore対象）。
2. **`app/config/config.php`** — リテラルなPHP配列のデフォルト値（`config_sample.php`からコピー）。このファイル内では`$_ENV[...]`式を使用**しない**ことをお勧めします。`runway config:set`などのツールが静的値として書き換え、シークレットをファイルに焼き付ける可能性があります。
3. **ブートストラップ時にマージ** — マップされたキーではenvが優先されます。アプリコードは設定オブジェクトまたは`$app->get()`を読み取り、コントローラ内で`$_ENV`を使用しません。

`config_sample.php` / `config.php`の例（簡略化）：

```php
<?php
// リテラルのみ — シークレットはスケルトンワークフローでは.envに属します
return [
	'app' => [
		'env' => 'development',
		'debug' => true,
		'base_url' => '/',
		'timezone' => 'UTC',
	],
	'database' => [
		'driver' => 'sqlite', // または mysql、無効にする場合は ''
		'host' => 'localhost',
		'dbname' => '',
		'user' => '',
		'password' => '',
		'file_path' => __DIR__ . '/../../database.sqlite',
	],
	// ...
];
```

```bash
# .env.example → .env（スケルトン）
APP_ENV=development
APP_DEBUG=true
FLIGHT_BASE_URL=/
DB_DRIVER=sqlite
# DB_PASSWORD=...
```

この分割は、[AIフレンドリーなプロジェクト](/learn/ai)にとって意図的です。手順書には「デフォルトは`config.php`、シークレットは`.env`、Config / Engineを注入し、コントローラ内でenvアクセスを発明しない」と書くことができます。既存のアプリは`.env`を完全に無視して、単一の設定ファイルを維持することもできます。

### 変数

Flightでは、アプリケーションのどこでも使用できるように変数を保存できます。

```php
// 変数を保存
Flight::set('id', 123);

// アプリケーションの別の場所で
$id = Flight::get('id');
```

変数が設定されているかどうかを確認するには、次のようにします。

```php
if (Flight::has('id')) {
  // 何かをする
}
```

変数をクリアするには、次のようにします。

```php
// id変数をクリア
Flight::clear('id');

// すべての変数をクリア
Flight::clear();
```

> **注:** 変数を設定できるからといって、それを使うべきとは限りません。この機能は控えめに使用してください。ここに保存されたものはすべてグローバル変数になるためです。グローバル変数は、アプリケーションのどこからでも変更できるため、バグの追跡が難しくなります。さらに、[ユニットテスト](/guides/unit-testing)などの複雑化を招く可能性があります。コントローラが必要とするサービスや設定には、コンストラクタ注入（スケルトン + Diceセットアップのように）を優先してください。

### エラーと例外

すべてのエラーと例外は、`flight.handle_errors`がtrueに設定されている場合、Flightによってキャッチされ、`error`メソッドに渡されます。

デフォルトの動作は、いくつかのエラー情報を含む汎用の`HTTP 500 Internal Server Error`レスポンスを送信することです。

この動作は、必要に応じて[上書き](/learn/extending)できます。

```php
Flight::map('error', function (Throwable $error) {
  // エラーを処理
  echo $error->getTraceAsString();
});
```

デフォルトでは、エラーはWebサーバーに記録されません。設定を変更することで有効にできます。

```php
Flight::set('flight.log_errors', true);
```

#### 404 Not Found

URLが見つからない場合、Flightは`notFound`メソッドを呼び出します。デフォルトの動作は、シンプルなメッセージとともに`HTTP 404 Not Found`レスポンスを送信することです。

この動作は、必要に応じて[上書き](/learn/extending)できます。

```php
Flight::map('notFound', function () {
  // 見つからない場合の処理
});
```

## 関連情報
- [インストール](/install) - スケルトン設定、`.env`、ブートストラップレイアウト。
- [オートローディング](/learn/autoloading) - 名前空間とフォルダーの大文字小文字。
- [Flightの拡張](/learn/extending) - Flightのコア機能を拡張およびカスタマイズする方法。
- [ユニットテスト](/guides/unit-testing) - Flightアプリケーション用のユニットテストを作成する方法。
- [AIと開発者体験](/learn/ai) - `AGENTS.md`と一貫したプロジェクト指示。
- [Tracy](/awesome-plugins/tracy) - 高度なエラー処理とデバッグのためのプラグイン。
- [Tracy拡張機能](/awesome-plugins/tracy_extensions) - TracyをFlightと統合するための拡張機能。
- [APM](/awesome-plugins/apm) - アプリケーションパフォーマンス監視とエラートラッキングのためのプラグイン。
- [セキュリティ](/learn/security) - 強化フラグとシークレットの取り扱い。

## トラブルシューティング
- 設定のすべての値を確認するのに問題がある場合は、`var_dump(Flight::get());`を実行できます。
- Runwayまたはデプロイツールが`config.php`を書き換えた場合は、シークレットがコミットされていないことを確認してください。スケルトンパターンを使用する場合は、シークレットを`.env`または実際の環境に保持してください。

## 変更履歴
- ドキュメント - `flight.views.restrict_to_path`をビューパス設定の隣に記載。
- ドキュメント - スケルトンスタイルの設定 / `.env`の階層化と、新しいプロジェクト向けのTwigビュー拡張子のデフォルトを記載。
- v3.18.1 - `flight.debug`および`flight.allow_method_override`設定オプションを追加。
- v3.5.0 - レガシー出力バッファリング動作をサポートするため、`flight.v2.output_buffering`の設定を追加。
- v2.0 - コア設定を追加。