# 設定

`set`メソッドを使用して、Flightの特定の振る舞いをカスタマイズすることができます。

```php
Flight::set('flight.log_errors', true);
```

## 利用可能な設定

以下は利用可能な設定のリストです:

- **flight.base_url** `?string` - リクエストのベースURLをオーバーライドします。(デフォルト: null)
- **flight.case_sensitive** `bool` - URLの大文字小文字を区別するかどうか。(デフォルト: false)
- **flight.handle_errors** `bool` - Flightにすべてのエラーを内部で処理させるかどうか。(デフォルト: true)
- **flight.log_errors** `bool` - エラーをWebサーバーのエラーログファイルに記録します。(デフォルト: false)
- **flight.views.path** `string` - ビューテンプレートファイルが含まれるディレクトリです。(デフォルト: ./views)
- **flight.views.extension** `string` - ビューテンプレートファイルの拡張子です。(デフォルト: .php)
- **flight.content_length** `bool` - `Content-Length`ヘッダーを設定します。(デフォルト: true)
- **flight.v2.output_buffering** `bool` - 旧バージョンの出力バッファリングを使用します。[v3への移行](migrating-to-v3)を参照してください。(デフォルト: false)

## 変数

Flightを使用して変数を保存し、アプリケーションのどこからでも使用できます。

```php
// 変数を保存する
Flight::set('id', 123);

// アプリケーションの別の場所で
$id = Flight::get('id');
```

変数が設定されているかどうかを確認するには、次のようにします:

```php
if (Flight::has('id')) {
  // 何かを実行する
}
```

次のようにして変数をクリアできます:

```php
// id変数をクリアする
Flight::clear('id');

// すべての変数をクリアする
Flight::clear();
```

Flightは設定目的で変数も使用します。

```php
Flight::set('flight.log_errors', true);
```

## エラー処理

### エラーと例外

すべてのエラーと例外はFlightによってキャッチされ、`error`メソッドに渡されます。
デフォルトの動作は、一般的な`HTTP 500 Internal Server Error`応答といくつかのエラー情報を送信することです。

これを独自のニーズに合わせて上書きすることができます:

```php
Flight::map('error', function (Throwable $error) {
  // エラーを処理する
  echo $error->getTraceAsString();
});
```

デフォルトではエラーはWebサーバーに記録されません。これを有効にするには、設定を変更してください:

```php
Flight::set('flight.log_errors', true);
```

### 見つからない場合

URLが見つからない場合、Flightは`notFound`メソッドを呼び出します。デフォルトの動作は、シンプルなメッセージ付きの`HTTP 404 Not Found`応答を送信することです。

これを独自のニーズに合わせて上書きすることができます:

```php
Flight::map('notFound', function () {
  // 見つからない場合の処理
});
```