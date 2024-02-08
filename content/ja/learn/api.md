# フレームワークAPIメソッド

Flightは使いやすく理解しやすいように設計されています。以下はフレームワーク用の完全なメソッドセットです。それには、通常の静的メソッドであるコアメソッドと、フィルタリングやオーバーライドできるマップされたメソッドである拡張可能なメソッドが含まれています。

## コアメソッド

これらのメソッドはフレームワークのコアであり、オーバーライドすることはできません。

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // カスタムフレームワークメソッドを作成します。
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // クラスをフレームワークメソッドに登録します。
Flight::unregister(string $name) // クラスをフレームワークメソッドから登録解除します。
Flight::before(string $name, callable $callback) // フレームワークメソッドの前にフィルターを追加します。
Flight::after(string $name, callable $callback) // フレームワークメソッドの後にフィルターを追加します。
Flight::path(string $path) // クラスの自動読み込み用のパスを追加します。
Flight::get(string $key) // 変数を取得します。
Flight::set(string $key, mixed $value) // 変数を設定します。
Flight::has(string $key) // 変数が設定されているかどうかをチェックします。
Flight::clear(array|string $key = []) // 変数をクリアします。
Flight::init() // フレームワークをデフォルトの設定に初期化します。
Flight::app() // アプリケーションオブジェクトインスタンスを取得します。
Flight::request() // リクエストオブジェクトインスタンスを取得します。
Flight::response() // レスポンスオブジェクトインスタンスを取得します。
Flight::router() // ルーターオブジェクトインスタンスを取得します。
Flight::view() // ビューオブジェクトインスタンスを取得します。
```

## 拡張可能なメソッド

```php
Flight::start() // フレームワークを開始します。
Flight::stop() // フレームワークを停止し、レスポンスを送信します。
Flight::halt(int $code = 200, string $message = '') // オプションのステータスコードとメッセージでフレームワークを停止します。
Flight::route(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // URLパターンをコールバックにマップします。
Flight::post(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // POSTリクエストURLパターンをコールバックにマップします。
Flight::put(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // PUTリクエストURLパターンをコールバックにマップします。
Flight::patch(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // PATCHリクエストURLパターンをコールバックにマップします。
Flight::delete(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // DELETEリクエストURLパターンをコールバックにマップします。
Flight::group(string $pattern, callable $callback) // URLのグループ化を作成します。パターンは文字列でなければなりません。
Flight::getUrl(string $name, array $params = []) // ルートエイリアスに基づいてURLを生成します。
Flight::redirect(string $url, int $code) // 別のURLにリダイレクトします。
Flight::render(string $file, array $data, ?string $key = null) // テンプレートファイルをレンダリングします。
Flight::error(Throwable $error) // HTTP 500の応答を送信します。
Flight::notFound() // HTTP 404の応答を送信します。
Flight::etag(string $id, string $type = 'string') // ETagHTTPキャッシュを実行します。
Flight::lastModified(int $time) // 最終変更日HTTPキャッシュを実行します。
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // JSON応答を送信します。
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // JSONP応答を送信します。
```

`map`と`register`で追加されたカスタムメソッドはフィルタリングもできます。