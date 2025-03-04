# トラブルシューティング

このページでは、Flightを使用している際に遭遇するかもしれない一般的な問題のトラブルシューティングを支援します。

## 一般的な問題

### 404 Not Found または予期しないルートの動作

404 Not Found エラーが表示される場合（しかし、それが実際に存在していることを誓って、タイプミスではないと主張する場合）、実際にはこれは、単にそれをエコーするのではなく、ルートエンドポイントで値を返すことが問題である可能性があります。これは意図的に行われている理由ですが、開発者の一部には忍び込む可能性があります。

```php

Flight::route('/hello', function(){
	// これが 404 Not Found エラーの原因となる可能性があります
	return 'Hello World';
});

// おそらく望む動作
Flight::route('/hello', function(){
	echo 'Hello World';
});

```

これは、ルーターに組み込まれている特別なメカニズムのために行われます。このメカニズムは、戻り出力を単一の「次のルートに移動する」として処理します。この動作は[Routing](/learn/routing#passing)セクションで文書化されています。

### クラスが見つかりません（オートローディングが機能していない）

これにはいくつかの理由が考えられます。以下にいくつかの例を示しますが、[autoloading](/learn/autoloading)セクションも確認してください。

#### ファイル名が間違っています
最も一般的なのは、クラス名がファイル名と一致していないことです。

クラス名が `MyClass` の場合、ファイル名は `MyClass.php` とする必要があります。クラス名が `MyClass` でファイル名が `myclass.php` の場合、オートローダーはそれを見つけることができません。

#### 名前空間が正しくありません
名前空間を使用している場合、名前空間はディレクトリ構造と一致している必要があります。

```php
// コード

// もし MyController が app/controllers ディレクトリにあり、名前空間が付いている場合
// この方法は機能しません。
Flight::route('/hello', 'MyController->hello');

// 以下のオプションのいずれかを選択する必要があります
Flight::route('/hello', 'app\controllers\MyController->hello');
// または先頭に use 文がある場合

use app\controllers\MyController;

Flight::route('/hello', [ MyController::class, 'hello' ]);
// また、以下のように記述することもできます
Flight::route('/hello', MyController::class.'->hello');
// また...
Flight::route('/hello', [ 'app\controllers\MyController', 'hello' ]);
```

#### `path()` が定義されていません

スケルトンアプリでは、これは `config.php` ファイル内で定義されていますが、クラスを見つけるためには、使用する前に `path()` メソッドが定義されていることを確認する必要があります（おそらくディレクトリのルートに）。

```php

// オートローダーにパスを追加
Flight::path(__DIR__.'/../');

```