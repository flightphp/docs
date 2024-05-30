# オートローディング

オートローディングとは、PHPにおいて特定のディレクトリまたは複数のディレクトリからクラスをロードする概念です。これは、クラスをロードする際に `require` や `include` を使用するよりも優れています。Composerパッケージを使用する際にも必要とされます。

デフォルトでは、`Flight` クラスはComposerのおかげで自動的にオートロードされます。ただし、独自のクラスをオートロードしたい場合は、`Flight::path` メソッドを使用してクラスをロードするディレクトリを指定することができます。

## 基本的な例

以下のようなディレクトリツリーがあるとします：

```text
# 例のパス
/home/user/project/my-flight-project/
├── app
│   ├── cache
│   ├── config
│   ├── controllers - このプロジェクトのコントローラを含む
│   ├── translations
│   ├── UTILS - このアプリケーション専用のクラスが含まれています (これは後での例のために意図的にすべて大文字で指定されています)
│   └── views
└── public
    └── css
	└── js
	└── index.php
```

このドキュメントサイトと同じファイル構造だとお気づきかもしれません。

以下のようにそれぞれのディレクトリを指定することができます：

```php

/**
 * public/index.php
 */

// オートローダーへのパスを追加
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');


/**
 * app/controllers/MyController.php
 */

// 名前空間が必要ありません

// すべてのオートロードされるクラスはパスカルケースであることが推奨されます (各単語が大文字で、スペースはなし)
// 3.7.2 以降、お使いのクラス名に対して Pascal_Snake_Case を使用するには Loader::setV2ClassLoading(false); を実行できます
class MyController {

	public function index() {
		// 何かを行う
	}
}
```

## 名前空間

もし名前空間を持っている場合、実装が非常に簡単になります。アプリケーションのルートディレクトリ (ドキュメントルートや `public/` フォルダではない) を指定するために `Flight::path()` メソッドを使用するべきです。

```php

/**
 * public/index.php
 */

// オートローダーへのパスを追加
Flight::path(__DIR__.'/../');
```

これはあなたのコントローラがどのように見えるかです。以下の例を見てくださいが、重要な情報を知るためにコメントに注意してください。

```php
/**
 * app/controllers/MyController.php
 */

// 名前空間は必須です
// 名前空間はディレクトリ構造と同じです
// 名前空間はディレクトリ構造と同じケースを守る必要があります
// 名前空間とディレクトリにはアンダースコアを含めることはできません (Loader::setV2ClassLoading(false) が設定されていない限り)
namespace app\controllers;

// すべてのオートロードされるクラスはパスカルケースであることが推奨されます (各単語が大文字で、スペースはなし)
// 3.7.2 以降、お使いのクラス名に対して Pascal_Snake_Case を使用するには Loader::setV2ClassLoading(false); を実行できます
class MyController {

	public function index() {
		// 何かを行う
	}
}
```

もし utils ディレクトリ内のクラスをオートロードしたい場合、基本的に同じことを行います：

```php

/**
 * app/UTILS/ArrayHelperUtil.php
 */

// 名前空間はディレクトリ構造とケースと一致する必要があります (ファイルツリーにある UTILS ディレクトリのように大文字であることに注意してください)
namespace app\UTILS;

class ArrayHelperUtil {

	public function changeArrayCase(array $array) {
		// 何かを行う
	}
}
```

## クラス名の中のアンダースコア

3.7.2 以降、`Loader::setV2ClassLoading(false);` を実行することで、クラス名にアンダースコアを使用することができます。これは推奨されませんが、必要な方には利用可能です。

```php

/**
 * public/index.php
 */

// オートローダーへのパスを追加
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');
Loader::setV2ClassLoading(false);

/**
 * app/controllers/My_Controller.php
 */

// 名前空間は必要ありません

class My_Controller {

	public function index() {
		// 何かを行う
	}
}
```