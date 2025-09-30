# オートローディング

## 概要

オートローディングは、PHPの概念で、クラスをロードするためのディレクトリまたはディレクトリを指定します。これは、`require` や `include` を使用してクラスをロードするよりもはるかに有益です。また、Composerパッケージを使用するための要件でもあります。

## 理解

デフォルトでは、`Flight` のクラスは Composer のおかげで自動的にオートロードされます。ただし、自分のクラスをオートロードしたい場合は、`Flight::path()` メソッドを使用してクラスをロードするためのディレクトリを指定できます。

オートローダーを使用することで、コードを大幅に簡素化できます。ファイルの先頭に多数の `include` や `require` 文を記述して、そのファイルで使用されるすべてのクラスをキャプチャする代わりに、クラスを動的に呼び出すだけで自動的にインクルードされます。

## 基本的な使用方法

以下のディレクトリツリーがあると仮定しましょう：

```text
# 例のパス
/home/user/project/my-flight-project/
├── app
│   ├── cache
│   ├── config
│   ├── controllers - このプロジェクトのコントローラーを含む
│   ├── translations
│   ├── UTILS - このアプリケーション専用のクラスを含む（後で例を示すために意図的に大文字）
│   └── views
└── public
    └── css
	└── js
	└── index.php
```

このドキュメントサイトと同じファイル構造であることに気づいたかもしれません。

各ディレクトリをロードするための指定は以下のようになります：

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

// 名前空間は不要

// オートロードされるすべてのクラスは Pascal Case を推奨（各単語の先頭を大文字、スペースなし）
class MyController {

	public function index() {
		// 何かを行う
	}
}
```

## 名前空間

名前空間を使用する場合、これは非常に簡単に実装できます。アプリケーションのルートディレクトリ（ドキュメントルートや `public/` フォルダではない）を指定するために `Flight::path()` メソッドを使用するべきです。

```php

/**
 * public/index.php
 */

// オートローダーにパスを追加
Flight::path(__DIR__.'/../');
```

これがコントローラーの例です。以下の例を見て、重要な情報のためにコメントに注意してください。

```php
/**
 * app/controllers/MyController.php
 */

// 名前空間は必須
// 名前空間はディレクトリ構造と同じ
// 名前空間はディレクトリ構造と同じケースに従う
// 名前空間とディレクトリにはアンダースコアを含められない（Loader::setV2ClassLoading(false) を設定しない限り）
namespace app\controllers;

// オートロードされるすべてのクラスは Pascal Case を推奨（各単語の先頭を大文字、スペースなし）
// 3.7.2 以降、Loader::setV2ClassLoading(false); を実行することでクラス名に Pascal_Snake_Case を使用可能
class MyController {

	public function index() {
		// 何かを行う
	}
}
```

utils ディレクトリのクラスをオートロードしたい場合、基本的に同じことを行います：

```php

/**
 * app/UTILS/ArrayHelperUtil.php
 */

// 名前空間はディレクトリ構造とケースに一致する必要がある（上記のファイルツリーのように UTILS ディレクトリはすべて大文字）
namespace app\UTILS;

class ArrayHelperUtil {

	public function changeArrayCase(array $array) {
		// 何かを行う
	}
}
```

## クラス名のアンダースコア

3.7.2 以降、`Loader::setV2ClassLoading(false);` を実行することで、クラス名に Pascal_Snake_Case を使用できます。
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

// 名前空間は不要

class My_Controller {

	public function index() {
		// 何かを行う
	}
}
```

## 関連項目
- [ルーティング](/learn/routing) - ルートをコントローラーにマッピングし、ビューをレンダリングする方法。
- [なぜフレームワークか？](/learn/why-frameworks) - Flight のようなフレームワークを使用する利点の理解。

## トラブルシューティング
- 名前空間付きのクラスが見つからない理由がわからない場合、プロジェクトのルートディレクトリに `Flight::path()` を使用することを忘れずに。`app/` や `src/` ディレクトリや同等ではありません。

### クラスが見つからない（オートローディングが動作しない）

これが発生しない理由はいくつか考えられます。以下に例を示しますが、[オートローディング](/learn/autoloading) セクションも確認してください。

#### ファイル名が不正
最も一般的なのは、クラス名がファイル名と一致しないことです。

`MyClass` という名前のクラスがある場合、ファイルは `MyClass.php` と名付けるべきです。`MyClass` というクラスがあり、ファイルが `myclass.php` の場合、オートローダーはそれを見つけられません。

#### 名前空間が不正
名前空間を使用している場合、名前空間はディレクトリ構造に一致するべきです。

```php
// ...code...

// MyController が app/controllers ディレクトリにあり、名前空間付きの場合
// これは動作しません。
Flight::route('/hello', 'MyController->hello');

// これらのオプションのいずれかを選択する必要があります
Flight::route('/hello', 'app\controllers\MyController->hello');
// または上部に use 文がある場合

use app\controllers\MyController;

Flight::route('/hello', [ MyController::class, 'hello' ]);
// また、次のように書けます
Flight::route('/hello', MyController::class.'->hello');
// また...
Flight::route('/hello', [ 'app\controllers\MyController', 'hello' ]);
```

#### `path()` が定義されていない

スケルトンアプリでは、これは `config.php` ファイル内で定義されていますが、クラスが見つかるためには、`path()`
メソッドが定義されていることを確認する必要があります（おそらくディレクトリのルートに）。使用しようとする前に。

```php
// オートローダーにパスを追加
Flight::path(__DIR__.'/../');
```

## 変更履歴
- v3.7.2 - `Loader::setV2ClassLoading(false);` を実行することでクラス名に Pascal_Snake_Case を使用可能
- v2.0 - オートロード機能が追加