# 設定

Flightの特定の振る舞いをカスタマイズするには、`set`メソッドを使用して設定値を設定することができます。

```php
Flight::set('flight.log_errors', true);
```

## 利用可能な設定

以下は利用可能なすべての設定のリストです:

- **flight.base_url** - リクエストのベースURLをオーバーライドします。(デフォルト: null)
- **flight.case_sensitive** - URLの大文字小文字を区別します。(デフォルト: false)
- **flight.handle_errors** - Flightにすべてのエラーを内部で処理させるかどうかを許可します。(デフォルト: true)
- **flight.log_errors** - エラーをWebサーバーのエラーログファイルに記録します。(デフォルト: false)
- **flight.views.path** - ビューテンプレートファイルが格納されているディレクトリ。(デフォルト: ./views)
- **flight.views.extension** - ビューテンプレートファイルの拡張子。(デフォルト: .php)

## 変数

Flightを使用して変数を保存し、アプリケーションのどこでも使用できるようにすることができます。

```php
// 変数を保存
Flight::set('id', 123);

// アプリケーションの他の場所では
$id = Flight::get('id');
```
変数が設定されているかどうかを確認するには、次のようにします:

```php
if (Flight::has('id')) {
  // 何かを実行
}
```

変数をクリアするには、次のようにします:

```php
// id変数をクリア
Flight::clear('id');

// すべての変数をクリア
Flight::clear();
```

Flightは設定目的のためにも変数を使用します。

```php
Flight::set('flight.log_errors', true);
```

## エラーハンドリング

### エラーと例外

すべてのエラーと例外はFlightによってキャッチされ、`error`メソッドに渡されます。
デフォルトの動作は、いくつかのエラー情報を含む一般的な`HTTP 500 Internal Server Error`応答を送信することです。

独自のニーズに合わせてこの動作をオーバーライドすることができます:

```php
Flight::map('error', function (Throwable $error) {
  // エラーを処理
  echo $error->getTraceAsString();
});
```

デフォルトでは、エラーはWebサーバーにログされません。これを有効にすることができます:
```php
Flight::set('flight.log_errors', true);
```

### 見つからない場合

URLが見つからない場合、Flightは`notFound`メソッドを呼び出します。デフォルトの動作は、単純なメッセージと共に`HTTP 404 Not Found`応答を送信することです。

独自のニーズに合わせてこの動作をオーバーライドすることができます:

```php
Flight::map('notFound', function () {
  // 見つからない場合の処理
});
```