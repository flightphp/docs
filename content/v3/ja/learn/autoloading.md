# オートローディング

## 概要

オートローディングは、PHPの概念で、クラスをロードするためのディレクトリまたはディレクトリを指定します。これにより、`require` や `include` を使用してクラスをロードするよりもはるかに利点があります。また、Composerパッケージを使用するための要件でもあります。

## 理解

デフォルトでは、`Flight` クラスはComposerのおかげで自動的にオートロードされます。ただし、自分のクラスをオートロードしたい場合は、`Flight::path()` メソッドを使用してクラスをロードするディレクトリを指定できます。

オートローダーを使用すると、コードを大幅に簡素化できます。ファイルの先頭に多数の `include` や `require` 文を記述して、そのファイルで使用されるすべてのクラスをキャプチャする代わりに、クラスを動的に呼び出すだけで自動的にインクルードされます。

## 基本的な使用方法

以下のディレクトリツリーがあると仮定します：

```text
# 例のパス
/home/user/project/my-flight-project/
├── app
│   ├── cache
│   ├── config
│   ├── controllers - このプロジェクトのコントローラーを含む
│   ├── translations
│   ├── UTILS - このアプリケーション専用のクラスを含む（後述の例のために意図的にすべて大文字）
│   └── views
└── public
    └── css
	└── js
	└── index.php
```

このドキュメントサイトと同じファイル構造であることに気づいたかもしれません。

各ディレクトリをこのように指定できます：

```php

/**
 * public/index.php
 */

// オートローダーにパスを追加
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');


/**
 * app/controllers/MyController.php
 */

// 名前空間は必要ありません

// オートロードされるクラスは、Pascal Case（各単語の先頭を大文字にし、スペースなし）で記述することを推奨します
class MyController {

	public function index() {
		// 何かを実行
	}
}
```

## 名前空間

名前空間を使用する場合、これは非常に簡単に実装できます。アプリケーションのルートディレクトリ（ドキュメントルートや `public/` フォルダではなく）を指定するために、`Flight::path()` メソッドを使用するべきです。

```php

/**
 * public/index.php
 */

// オートローダーにパスを追加
Flight::path(__DIR__.'/../');
```

これがコントローラーの例です。以下の例を見てください。ただし、重要な情報のためにコメントに注目してください。

```php
/**
 * app/controllers/MyController.php
 */

// 名前空間は必須です
// 名前空間はディレクトリ構造と同じです
// 名前空間はディレクトリ構造と同じケースに従う必要があります
// 名前空間とディレクトリにはアンダースコアを含めないでください（Loader::setV2ClassLoading(false) を設定しない限り）
// 名前空間はディレクトリ構造とケースに一致する必要があります
namespace app\controllers;

// オートロードされるクラスは、Pascal Case（各単語の先頭を大文字にし、スペースなし）で記述することを推奨します
// 3.7.2以降、Loader::setV2ClassLoading(false); を実行することで、クラス名に Pascal_Snake_Case を使用できます
class MyController {

	public function index() {
		// 何かを実行
	}
}
```

utils ディレクトリのクラスをオートロードしたい場合、基本的に同じことを行います：

```php

/**
 * app/UTILS/ArrayHelperUtil.php
 */

// 名前空間はディレクトリ構造とケースに一致する必要があります（上記のファイルツリーのように UTILS ディレクトリはすべて大文字です）
namespace app\UTILS;

class ArrayHelperUtil {

	public function changeArrayCase(array $array) {
		// 何かを実行
	}
}
```

## クラス名のアンダースコア

3.7.2以降、`Loader::setV2ClassLoading(false);` を実行することで、クラス名に Pascal_Snake_Case を使用できます。 
これにより、クラス名にアンダースコアを使用できます。 
これは推奨されませんが、必要とする人向けに利用可能です。

```php
use flight\core\Loader;

/**
 * public/index.php
 */

// オートローダーにパスを追加
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');
Loader::setV2ClassLoading(false);

/**
 * app/controllers/My_Controller.php
 */

// 名前空間は必要ありません

class My_Controller {

	public function index() {
		// 何かを実行
	}
}
```

## 関連項目
- [Routing](/learn/routing) - ルートをコントローラーにマッピングし、ビューをレンダリングする方法。
- [Why a Framework?](/learn/why-frameworks) - Flight のようなフレームワークを使用する利点の理解。

## トラブルシューティング
- 名前空間付きのクラスが見つからない理由がわからない場合、プロジェクトのルートディレクトリに対して `Flight::path()` を使用することを忘れないでください。`app/` や `src/` ディレクトリや同等ではありません。

### クラスが見つからない（オートローディングが機能しない）

これが発生しない理由はいくつか考えられます。以下にいくつかの例を示しますが、[autoloading](/learn/autoloading) セクションも確認してください。

#### ファイル名の誤り
最も一般的なのは、クラス名がファイル名と一致しないことです。

クラス名が `MyClass` の場合、ファイル名は `MyClass.php` であるべきです。クラス名が `MyClass` でファイル名が `myclass.php` の場合、オートローダーはそれを見つけられません。

#### 名前空間の誤り
名前空間を使用している場合、名前空間はディレクトリ構造に一致する必要があります。

```php
// ...code...

// MyController が app/controllers ディレクトリにあり、名前空間が付けられている場合
// これは機能しません。
Flight::route('/hello', 'MyController->hello');

// これらのオプションのいずれかを選択する必要があります
Flight::route('/hello', 'app\controllers\MyController->hello');
// または、上部に use 文がある場合

use app\controllers\MyController;

Flight::route('/hello', [ MyController::class, 'hello' ]);
// また、次のように記述できます
Flight::route('/hello', MyController::class.'->hello');
// また...
Flight::route('/hello', [ 'app\controllers\MyController', 'hello' ]);
```

#### `path()` が定義されていない

スケルトンアプリでは、これは `config.php` ファイル内に定義されていますが、クラスが見つかるように、`path()` メソッドが定義されている（おそらくディレクトリのルートに対して）ことを確認する必要があります。それを使用する前に。

```php
// オートローダーにパスを追加
Flight::path(__DIR__.'/../');
```

## 変更履歴
- v3.7.2 - `Loader::setV2ClassLoading(false);` を実行することで、クラス名に Pascal_Snake_Case を使用できます
- v2.0 - オートロード機能が追加されました。