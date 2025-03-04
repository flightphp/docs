# フレームワークのメソッド

Flightは使いやすく理解しやすいように設計されています。以下はフレームワークの完全なメソッドセットです。
コアメソッドには、通常の静的メソッドであるコアメソッドと、フィルタリングやオーバーライドが可能なマップされたメソッドである拡張メソッドが含まれています。

## コアメソッド

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // カスタムフレームワークメソッドを作成します。
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // クラスをフレームワークメソッドに登録します。
Flight::before(string $name, callable $callback) // フレームワークメソッドの前にフィルタを追加します。
Flight::after(string $name, callable $callback) // フレームワークメソッドの後にフィルタを追加します。
Flight::path(string $path) // クラスの自動読み込み用のパスを追加します。
Flight::get(string $key) // 変数を取得します。
Flight::set(string $key, mixed $value) // 変数を設定します。
Flight::has(string $key) // 変数が設定されているかどうかを確認します。
Flight::clear(array|string $key = []) // 変数をクリアします。
Flight::init() // フレームワークをデフォルト設定に初期化します。
Flight::app() // アプリケーションオブジェクトのインスタンスを取得します。
```

## 拡張メソッド

```php
Flight::start() // フレームワークを開始します。
Flight::stop() // フレームワークを停止してレスポンスを送信します。
Flight::halt(int $code = 200, string $message = '') // オプションのステータスコードとメッセージでフレームワークを停止します。
Flight::route(string $pattern, callable $callback, bool $pass_route = false) // URLパターンをコールバックにマップします。
Flight::group(string $pattern, callable $callback) // URLのグループを作成します。パターンは文字列でなければなりません。
Flight::redirect(string $url, int $code) // 別のURLにリダイレクトします。
Flight::render(string $file, array $data, ?string $key = null) // テンプレートファイルをレンダリングします。
Flight::error(Throwable $error) // HTTP 500レスポンスを送信します。
Flight::notFound() // HTTP 404レスポンスを送信します。
Flight::etag(string $id, string $type = 'string') // ETag HTTPキャッシュを実行します。
Flight::lastModified(int $time) // 最終変更日HTTPキャッシュを実行します。
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // JSONレスポンスを送信します。
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // JSONPレスポンスを送信します。
```

`map`と`register`で追加された任意のカスタムメソッドもフィルタリングできます。