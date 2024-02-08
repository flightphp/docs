# 設定

`set` メソッドを使用して、Flight の特定の動作をカスタマイズできます。

```php
Flight::set('flight.log_errors', true);
```

## 利用可能な設定

以下は利用可能なすべての設定のリストです：

- **flight.base_url** - リクエストのベース URL を上書きします。 (デフォルト: null)
- **flight.case_sensitive** - URL の大文字と小文字を区別します。 (デフォルト: false)
- **flight.handle_errors** - すべてのエラーを Flight が内部で処理できるようにします。 (デフォルト: true)
- **flight.log_errors** - エラーをウェブサーバーのエラーログファイルに記録します。 (デフォルト: false)
- **flight.views.path** - ビューテンプレートファイルが含まれるディレクトリ。 (デフォルト: ./views)
- **flight.views.extension** - ビューテンプレートファイルの拡張子。 (デフォルト: .php)

## 変数

Flight では、変数を保存してアプリケーション内のどこでも使用できるようにします。

```php
// 変数を保存する
Flight::set('id', 123);

// アプリケーション内の別の場所で
$id = Flight::get('id');
```

変数が設定されているかどうかを確認するには、次のようにします：

```php
if (Flight::has('id')) {
  // 何かを行う
}
```

次のようにして変数をクリアできます：

```php
// id 変数をクリアする
Flight::clear('id');

// すべての変数をクリアする
Flight::clear();
```

Flight は構成目的で変数も使用します。

```php
Flight::set('flight.log_errors', true);
```

## エラー処理

### エラーと例外

Flight によってすべてのエラーと例外がキャッチされ、`error` メソッドに渡されます。デフォルトの動作は、一般的な `HTTP 500 Internal Server Error` 応答といくつかのエラー情報を送信することです。

必要に応じてこの動作を上書きできます：

```php
Flight::map('error', function (Throwable $error) {
  // エラーを処理する
  echo $error->getTraceAsString();
});
```

デフォルトではエラーはウェブサーバーにログを記録しません。これを有効にするには、設定を変更してください：

```php
Flight::set('flight.log_errors', true);
```

### 見つからない

URL が見つからない場合、Flight は `notFound` メソッドを呼び出します。デフォルトの動作は、簡単なメッセージとともに `HTTP 404 Not Found` 応答を送信することです。

必要に応じてこの動作を上書きできます：

```php
Flight::map('notFound', function () {
  // 見つからない場合の処理
});
```  