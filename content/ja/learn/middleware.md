# ルートミドルウェア

Flightはルートとグループルートのミドルウェアをサポートしています。ミドルウェアは、ルートコールバックの前（または後）に実行される関数です。これは、コードにAPI認証チェックを追加したり、ユーザーがルートにアクセスする権限を持っているかを検証するための優れた方法です。

## 基本的なミドルウェア

基本的な例を以下に示します：

```php
// 匿名関数だけを供給する場合、ルートコールバックの前に実行されます。
// クラスを除いて "after" ミドルウェア関数はありません（以下参照）
Flight::route('/path', function() { echo '私がここにいます！'; })->addMiddleware(function() {
    echo '最初にミドルウェア！';
});

Flight::start();

// これにより "最初にミドルウェア！私がここにいます！" が出力されます。
```

ミドルウェアについて知っておくべきいくつかの重要な注意事項があります：
- ミドルウェア関数は、ルートに追加された順序で実行されます。実行は [Slim Framework がこの操作をどのように処理するか](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work) に似ています。
   - ビフォアは追加された順序で実行され、アフターは逆の順序で実行されます。
- ミドルウェア関数が false を返すと、すべての実行が停止され、403 Forbidden エラーがスローされます。これをより丁寧に扱いたい場合は、`Flight::redirect()` や類似の手法で処理する必要があります。
- ルートからパラメーターが必要な場合、これらは1つの配列にパスされます。(`function($params) { ... }` または `public function before($params) {}`)。これは、パラメーターをグループに構造化し、いくつかのグループでは、同じパラメーターを参照することによりミドルウェア関数を壊してしまう可能性があるためです。この方法で、位置ではなく名前でアクセスできます。
- ミドルウェアの名前だけを渡すと、[依存性注入コンテナ](dependency-injection-container) により自動的に実行され、必要なパラメーターでミドルウェアが実行されます。依存性注入コンテナが登録されていない場合、`__construct()` に `flight\Engine` インスタンスが渡されます。

## ミドルウェアクラス

ミドルウェアをクラスとして登録することもできます。"after" 機能が必要な場合は、**必ず**クラスを使用する必要があります。

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
Flight::route('/path', function() { echo '私がここにいます！'; })->addMiddleware($MyMiddleware); // または ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// これにより "最初にミドルウェア！私がここにいます！最後にミドルウェア！" が表示されます。
```

## ミドルウェアエラーの処理

認証ミドルウェアがあり、認証されていない場合にユーザーをログインページにリダイレクトしたいとします。使用可能なオプションがいくつかあります：

1. ミドルウェア関数から false を返すと、Flight は自動的に 403 Forbidden エラーを返しますが、カスタマイズは行われません。
1. ユーザーをログインページにリダイレクトするには `Flight::redirect()` を使用できます。
1. ミドルウェア内でカスタムエラーを作成し、ルートの実行を停止できます。

### 基本的な例

ここに単純な return false; の例があります：
```php
class MyMiddleware {
    public function before($params) {
        if (isset($_SESSION['user']) === false) {
            return false;
        }

        // true の場合、すべてが続行されます
    }
}
```

### リダイレクト例

ユーザーをログインページにリダイレクトする例は次の通りです：
```php
class MyMiddleware {
    public function before($params) {
        if (isset($_SESSION['user']) === false) {
            Flight::redirect('/login');
            exit;
        }
    }
}
```

### カスタムエラーの例

APIを構築しているため、JSONエラーをスローする必要があるとします。以下のように行うことができます：
```php
class MyMiddleware {
    public function before($params) {
        $authorization = Flight::request()->headers['Authorization'];
        if(empty($authorization)) {
            Flight::json(['error' => 'このページにアクセスするにはログインする必要があります。'], 403);
            exit;
            // または
            Flight::halt(403, json_encode(['error' => 'このページにアクセスするにはログインする必要があります。']);
        }
    }
}
```

## ミドルウェアのグループ化

ルートグループを追加し、そのグループ内のすべてのルートに同じミドルウェアが適用されます。これは、ヘッダー内のAPIキーをチェックするような場合にルートをグループ化する必要がある場合に便利です。

```php

// グループメソッドの最後に追加
Flight::group('/api', function() {

	// この "empty" のように見えるルートは実際には /api と一致します
	Flight::route('', function() { echo 'api'; }, false, 'api');
    Flight::route('/users', function() { echo 'users'; }, false, 'users');
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```

すべてのルートにグローバルミドルウェアを適用したい場合は、"empty" グループを追加できます：

```php

// グループメソッドの最後に追加
Flight::group('', function() {
	Flight::route('/users', function() { echo 'users'; }, false, 'users');
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```