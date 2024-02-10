
# ルートミドルウェア

Flightはルートおよびグループルートミドルウェアをサポートしています。ミドルウェアは、ルートのコールバックの前（または後）に実行される関数です。これは、API認証チェックをコードに追加したり、ユーザーがルートにアクセスする権限を持っているかを検証したりする素晴らしい方法です。

## 基本ミドルウェア

以下は基本的な例です：

```php
// 匿名関数のみを指定すると、ルートのコールバックの前に実行されます。
// 下記のクラスを除くと「後」のミドルウェア関数はありません
Flight::route('/path', function() { echo 'ここにいます！'; })->addMiddleware(function() {
	echo '最初にミドルウェア！';
});

Flight::start();

// これにより、「最初にミドルウェア！ここにいます！」と出力されます。
```

ミドルウェアに関して、使用する前に把握しておくべき非常に重要な注意点がいくつかあります：
- ミドルウェア関数はルートに追加された順に実行されます。実行は[このようにSlim Frameworkが処理している方法](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work)に似ています。
   - ビフォーは追加された順に実行され、アフターは逆の順序で実行されます。
- ミドルウェア関数がfalseを返すと、すべての実行が停止され、403 Forbiddenエラーが投げられます。これをより優雅に処理するには、`Flight::redirect()`などで処理することがおそらく望ましいでしょう。
- ルートからパラメータが必要な場合、それらは単一配列でミドルウェア関数に渡されます（`function($params) { ... }`または`public function before($params) {}`）。これの理由は、パラメータをグループに構造化し、そのうちのいくつかのグループではパラメータが実際に異なる順序で表示されることがあり、これによりミドルウェア関数が誤ったパラメータを参照して壊れる可能性があるためです。この方法で、位置ではなく名前でアクセスできます。

## ミドルウェアクラス

ミドルウェアはクラスとしても登録できます。"後"の機能性が必要な場合、クラスを使用する必要があります。

```php
class MyMiddleware {
	public function before($params) {
		echo '最初にミドルウェア！';
	}

	public function after($params) {
		echo '最後にミドルウェア！';
	}
}

$MyMiddleware = new MyMiddleware();
Flight::route('/path', function() { echo 'ここにいます！'; })->addMiddleware($MyMiddleware); // または ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// これにより、「最初にミドルウェア！ここにいます！最後にミドルウェア！」と表示されます。
```

## ミドルウェアのグループ化

ルートグループを追加し、そのグループ内のすべてのルートに同じミドルウェアを付け加えることができます。これは、ヘッダーのAPIキーをチェックするためなど、いくつかのルートをAuthミドルウェアでグループ化する必要がある場合に便利です。

```php

// グループメソッドの最後に追加されました
Flight::group('/api', function() {

	// この「空」に見えるルートは実際に/apiに一致します
	Flight::route('', function() { echo 'api'; }, false, 'api');
    Flight::route('/users', function() { echo 'users'; }, false, 'users');
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```

すべてのルートにグローバルミドルウェアを適用したい場合は、"空"のグループを追加できます：

```php

// グループメソッドの最後に追加されました
Flight::group('', function() {
	Flight::route('/users', function() { echo 'users'; }, false, 'users');
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```