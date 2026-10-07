# 設定

## 概要

Flightは、アプリケーションのニーズに合わせてフレームワークのさまざまな側面を設定する簡単な方法を提供します。一部はデフォルトで設定されていますが、必要に応じて上書きできます。また、アプリケーション全体で使用する独自の変数を設定することもできます。

明確で階層化された設定（ファイルのデフォルト + 環境シークレット）は、[AIコーディングツール](/learn/ai) にも役立ちます。エージェントは、コントローラー内で `$_ENV` の読み取りを独自に作成する代わりに、リテラル用とシークレット用の場所をそれぞれ1か所で学習できます。

## 理解

`set` メソッドを使用して設定値を設定することで、Flightの特定の動作をカスタマイズできます。

```php
Flight::set('flight.log_errors', true);
```

構造化されたアプリ（[スケルトン](https://github.com/flightphp/skeleton) を含む）では、通常、`app/config/config.php` からプロジェクト設定を読み込み、関連するキーをEngineに適用します（例：`flight.base_url`、`flight.views.path`）。また、コントローラー内でグローバルをあちこちから読み取る代わりに、小さな設定オブジェクトをコントローラーに注入することもできます。これはテストや `AGENTS.md` に従うエージェントにとって、より親しみやすい方法です。

## 基本的な使い方

### Flight設定オプション

以下は、利用可能なすべての設定項目の一覧です。

- **flight.base_url** `?string` - Flightがサブディレクトリで実行されている場合に、リクエストのベースURLを上書きします。（デフォルト: null）
- **flight.case_sensitive** `bool` - URLに対して大文字小文字を区別したマッチングを行います。（デフォルト: false）
- **flight.handle_errors** `bool` - Flightがすべてのエラーを内部的に処理できるようにします。（デフォルト: true）
  - デフォルトのPHP動作ではなくFlightにエラーを処理させたい場合は、これをtrueに設定する必要があります。
  - [Tracy](/awesome-plugins/tracy) をインストールしている場合は、Tracyがエラーを処理できるように false に設定します。
  - [APM](/awesome-plugins/apm) プラグインをインストールしている場合は、APMがエラーをログに記録できるよう true に設定します。
- **flight.log_errors** `bool` - エラーをWebサーバーのエラーログファイルに記録します。（デフォルト: false）
  - [Tracy](/awesome-plugins/tracy) をインストールしている場合、Tracyはこの設定ではなくTracyの設定に基づいてエラーを記録します。
- **flight.debug** `bool` - エラー発生時に、詳細なエラー情報（例外メッセージ、コード、スタックトレース）をブラウザに出力します。（デフォルト: false）
  - **本番環境では絶対に有効にしないでください** — 内部アプリケーションの詳細が漏れます。ローカル開発またはステージング環境でのみ使用してください。
  - `false` の場合、代わりに一般的な `500 Internal Server Error` が表示されます。サーバー側でエラーを把握するには `flight.log_errors` と組み合わせて使用します。
- **flight.allow_method_override** `bool` - `X-HTTP-Method-Override` リクエストヘッダーまたはPOST本文の `_method` フィールドを介してHTTPメソッドを上書きできるようにします。（デフォルト: true）
  - HTMLフォームベースのメソッドスプーフィングが不要なアプリケーションでは、**これを `false` に設定することを推奨します**。これにより、標準のPOSTフォームを介した `DELETE` や `PUT` リクエストの偽造を防ぐことができます。
  - 詳細は [Security](/learn/security#flight-configuration-hardening) を参照してください。
- **flight.views.path** `string` - ビューテンプレートファイルを格納するディレクトリ。（デフォルト: ./views）
- **flight.views.extension** `string` - ビューテンプレートファイルの拡張子。（デフォルト: `.php`。公式スケルトンではTwigを使用する場合に `.twig` に設定されます）
- **flight.views.restrict_to_path** `bool` - `true` の場合、Flightのネイティブ `View` は `flight.views.path` 内に解決されるテンプレートファイルのみを受け付けます。（デフォルト: `false`）。ネイティブビューを使用するアプリでは**これをオンにしてください**。[Security](/learn/security#flightviewsrestrict_to_path) を参照してください。
- **flight.content_length** `bool` - `Content-Length` ヘッダーを設定します。（デフォルト: true）
  - [Tracy](/awesome-plugins/tracy) を使用している場合、Tracyが正しくレンダリングできるように false に設定する必要があります。
- **flight.v2.output_buffering** `bool` - レガシーな出力バッファリングを使用します。[v3への移行](migrating-to-v3) を参照してください。（デフォルト: false）

### ローダー設定

ローダーには、もう1つの設定項目があります。これにより、クラス名に `_` が含まれるクラスをオートロードできるようになります。

```php
// アンダースコア付きのクラスローディングを有効にする
// デフォルトはtrue
Loader::$v2ClassLoading = false;
```

オートローディングは、名前空間とフォルダー名の大文字小文字が一致していることにも依存することに注意してください。特に、スケルトンの `App\` と `app/Controller/` のレイアウトでは重要です。

### プロジェクト設定と `.env`（スケルトンパターン）

Flightのコアは `.env` ファイルを必要としません。多くのアプリはPHPの設定配列のみを使用します。公式スケルトンは設定を階層化することで、シークレットをgitの管理外に保ちつつ、Runwayが**リテラル**設定を安全に書き換えられるようにしています。

1. **`.env` / 実際の環境** — シークレットとデプロイ時の上書き値（gitignore対象）。
2. **`app/config/config.php`** — リテラルなPHP配列のデフォルト（`config_sample.php` からコピー）。このファイル内では `$_ENV[...]` 式を**使用しない**ことを推奨します。`runway config:set` のようなツールはこれを静的な値として書き換える可能性があり、シークレットをファイルに埋め込んでしまう可能性があります。
3. **ブートストラップ時にマージ** — マッピングされたキーでは環境変数が優先されます。アプリコードは設定オブジェクトまたは `$app->get()` を読み取り、コントローラー内で `$_ENV` を使用しません。

`config_sample.php` / `config.php` の例（簡略化）：

```php
<?php
// リテラルのみ — シークレットはスケルトンワークフローでは .env に属する
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

この分割は、AIフレンドリーなプロジェクトのために意図的に行われています。手順書には「デフォルトは `config.php`、シークレットは `.env`、Config / Engine を注入し、コントローラー内で環境変数アクセスを独自に作成しないこと」と書けます。既存のアプリは `.env` を完全に無視して、単一の設定ファイルを維持することもできます。

### 変数

Flightでは、アプリケーションのどこからでも使用できる変数を保存できます。

```php
// 変数を保存
Flight::set('id', 123);

// アプリケーションの別の場所で使用
$id = Flight::get('id');
```

変数が設定されているかどうかを確認するには、次のようにします：

```php
if (Flight::has('id')) {
  // 何かをする
}
```

変数をクリアするには、次のようにします：

```php
// id変数をクリア
Flight::clear('id');

// すべての変数をクリア
Flight::clear();
```

> **注:** 変数を設定できるからといって、それを使うべきとは限りません。この機能は控えめに使用してください。ここに保存されたものはすべてグローバル変数になるからです。グローバル変数は、アプリケーションのどこからでも変更できるため、バグの追跡が難しくなるため望ましくありません。さらに、[ユニットテスト](/guides/unit-testing) のようなものを複雑にする可能性があります。コントローラーが必要とするサービスや設定には、コンストラクター注入（スケルトン + Dice のセットアップのように）を優先してください。

### エラーと例外

すべてのエラーと例外はFlightによってキャッチされ、`flight.handle_errors` が true に設定されている場合、`error` メソッドに渡されます。

デフォルトの動作は、いくつかのエラー情報を含む一般的な `HTTP 500 Internal Server Error` レスポンスを送信することです。

独自のニーズに合わせてこの動作を上書きできます：

```php
Flight::map('error', function (Throwable $error) {
  // エラーを処理
  echo $error->getTraceAsString();
});
```

デフォルトでは、エラーはWebサーバーに記録されません。設定を変更することで有効にできます：

```php
Flight::set('flight.log_errors', true);
```

#### 404 Not Found

URLが見つからない場合、Flightは `notFound` メソッドを呼び出します。デフォルトの動作は、簡単なメッセージ付きの `HTTP 404 Not Found` レスポンスを送信することです。

独自のニーズに合わせてこの動作を上書きできます：

```php
Flight::map('notFound', function () {
  // 見つからない場合の処理
});
```

## 関連項目
- [インストール](/install) - スケルトン設定、`.env`、ブートストラップレイアウト。
- [オートローディング](/learn/autoloading) - 名前空間とフォルダーの大文字小文字。
- [Flightの拡張](/learn/extending) - Flightのコア機能を拡張およびカスタマイズする方法。
- [ユニットテスト](/guides/unit-testing) - Flightアプリケーション用のユニットテストの書き方。
- [AIと開発者体験](/learn/ai) - `AGENTS.md` と一貫したプロジェクト指示。
- [Tracy](/awesome-plugins/tracy) - 高度なエラー処理とデバッグのためのプラグイン。
- [Tracy Extensions](/awesome-plugins/tracy_extensions) - TracyをFlightと統合するための拡張機能。
- [APM](/awesome-plugins/apm) - アプリケーションパフォーマンス監視とエラー追跡のためのプラグイン。
- [Security](/learn/security) - 強化フラグとシークレットの取り扱い。

## トラブルシューティング
- 設定のすべての値を確認するのに問題がある場合は、`var_dump(Flight::get());` を実行できます。
- Runwayまたはデプロイツールが `config.php` を書き換えた場合は、シークレットがコミットされていないことを確認してください。スケルトンパターンを使用する場合は、シークレットを `.env` または実際の環境に保持してください。

## 変更履歴
- ドキュメント – ビューパス設定の横に `flight.views.restrict_to_path` を記載。
- ドキュメント – スケルトン形式の設定 / `.env` の階層化と、新規プロジェクト向けのTwigビュー拡張子のデフォルトを文書化。
- v3.18.1 - `flight.debug` と `flight.allow_method_override` 設定オプションを追加。
- v3.5.0 - レガシーな出力バッファリング動作をサポートする `flight.v2.output_buffering` 設定を追加。
- v2.0 - コア設定を追加。