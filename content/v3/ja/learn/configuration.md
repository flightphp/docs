# 構成

## 概要

Flight は、アプリケーションのニーズに合わせてフレームワークのさまざまな側面を構成するためのシンプルな方法を提供します。一部はデフォルトで設定されていますが、必要に応じてこれらを上書きできます。また、アプリケーション全体で使用するための独自の変数を設定することもできます。

## 理解

Flight の特定の動作をカスタマイズするには、`set` メソッドを使用して構成値を設定できます。

```php
Flight::set('flight.log_errors', true);
```

`app/config/config.php` ファイルでは、利用可能なすべてのデフォルト構成変数を確認できます。

## 基本的な使用方法

### Flight 構成オプション

以下のリストは、利用可能なすべての構成設定です：

- **flight.base_url** `?string` - Flight がサブディレクトリで動作している場合、リクエストのベース URL を上書きします。（デフォルト: null）
- **flight.case_sensitive** `bool` - URL の大文字小文字を区別したマッチング。（デフォルト: false）
- **flight.handle_errors** `bool` - Flight がすべてのエラーを内部で処理することを許可します。（デフォルト: true）
  - Flight がデフォルトの PHP 動作の代わりにエラーを処理する場合は、これを true に設定する必要があります。
  - [Tracy](/awesome-plugins/tracy) をインストールしている場合、Tracy がエラーを処理できるようにこれを false に設定します。
  - [APM](/awesome-plugins/apm) プラグインをインストールしている場合、APM がエラーをログに記録できるようにこれを true に設定します。
- **flight.log_errors** `bool` - ウェブサーバーのエラーログファイルにエラーをログします。（デフォルト: false）
  - [Tracy](/awesome-plugins/tracy) をインストールしている場合、Tracy はこの構成ではなく Tracy の構成に基づいてエラーをログします。
- **flight.views.path** `string` - ビュー テンプレート ファイルを含むディレクトリ。（デフォルト: ./views）
- **flight.views.extension** `string` - ビュー テンプレート ファイルの拡張子。（デフォルト: .php）
- **flight.content_length** `bool` - `Content-Length` ヘッダーを設定します。（デフォルト: true）
  - [Tracy](/awesome-plugins/tracy) を使用している場合、Tracy が適切にレンダリングできるようにこれを false に設定する必要があります。
- **flight.v2.output_buffering** `bool` - レガシー出力バッファリングを使用します。[v3 への移行](migrating-to-v3) を参照してください。（デフォルト: false）

### ローダー構成

ローダーには追加の構成設定があります。これにより、クラス名に `_` を含むクラスを自動ロードできます。

```php
// アンダースコア付きのクラスロードを有効にする
// デフォルトは true
Loader::$v2ClassLoading = false;
```

### 変数

Flight では、アプリケーションのどこでも使用できるように変数を保存できます。

```php
// 変数を保存
Flight::set('id', 123);

// アプリケーションの他の場所で
$id = Flight::get('id');
```

変数が設定されているかどうかを確認するには：

```php
if (Flight::has('id')) {
  // 何かを実行
}
```

変数をクリアするには：

```php
// id 変数をクリア
Flight::clear('id');

// すべての変数をクリア
Flight::clear();
```

> **注意:** 変数を設定できるからといって、設定すべきとは限りません。この機能は控えめに使用してください。理由は、ここに保存されたものはグローバル変数になるためです。グローバル変数は、アプリケーションのどこからでも変更可能であるため、バグの追跡が難しくなるため悪いのです。また、これにより [ユニットテスト](/guides/unit-testing) などのことが複雑になります。

### エラーと例外

すべてのエラーと例外は Flight によってキャッチされ、`flight.handle_errors` が true に設定されている場合、`error` メソッドに渡されます。

デフォルトの動作は、一般的な `HTTP 500 Internal Server Error` 応答をいくつかのエラー情報とともに送信することです。

この動作を独自のニーズに合わせて [上書き](/learn/extending) できます：

```php
Flight::map('error', function (Throwable $error) {
  // エラーを処理
  echo $error->getTraceAsString();
});
```

デフォルトでは、エラーはウェブサーバーにログされません。これを有効にするには、構成を変更します：

```php
Flight::set('flight.log_errors', true);
```

#### 404 Not Found

URL が見つからない場合、Flight は `notFound` メソッドを呼び出します。デフォルトの動作は、シンプルなメッセージ付きの `HTTP 404 Not Found` 応答を送信することです。

この動作を独自のニーズに合わせて [上書き](/learn/extending) できます：

```php
Flight::map('notFound', function () {
  // 見つからない場合の処理
});
```

## 関連項目
- [Flight の拡張](/learn/extending) - Flight のコア機能を拡張およびカスタマイズする方法。
- [ユニットテスト](/guides/unit-testing) - Flight アプリケーションのユニットテストの書き方。
- [Tracy](/awesome-plugins/tracy) - 高度なエラー処理とデバッグのためのプラグイン。
- [Tracy 拡張](/awesome-plugins/tracy_extensions) - Tracy を Flight に統合するための拡張。
- [APM](/awesome-plugins/apm) - アプリケーションのパフォーマンス監視とエラートラッキングのためのプラグイン。

## トラブルシューティング
- 構成のすべての値を確認する問題がある場合、`var_dump(Flight::get());` を実行できます。

## 変更履歴
- v3.5.0 - レガシー出力バッファリング動作をサポートするための `flight.v2.output_buffering` 構成を追加。
- v2.0 - コア構成を追加。