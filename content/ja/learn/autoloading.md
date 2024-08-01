# オートローディング

オートローディングは、PHPにおいてクラスを読み込むディレクトリを指定する概念です。これは、`require`や`include`を使用してクラスをロードするよりも有益です。Composerパッケージを使用する際にも必要です。

デフォルトでは、`Flight`クラスはComposerのおかげで自動的にオートロードされます。ただし、独自のクラスをオートロードする場合は、`Flight::path()`メソッドを使用してクラスを読み込むディレクトリを指定できます。

## 基本例

以下のようなディレクトリツリーを持つとします：

```text
# 例えばのパス
/home/user/project/my-flight-project/
├── app
│   ├── cache
│   ├── config
│   ├── controllers - このプロジェクトのコントローラーが含まれる
│   ├── translations
│   ├── UTILS - このアプリケーション専用のクラスが含まれる（これは後の例のためにわざと全てキャピタライズされています）
│   └── views
└── public
    └── css
	└── js
	└── index.php
```

このドキュメンテーションサイトと同じファイル構造であることに気づかれたかもしれません。

次のように各ディレクトリを指定できます：

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

// すべてのオートロードされるクラスはパスカルケース（各単語を大文字にして、スペースなし）であることが推奨されます
// ローダー::setV2ClassLoading(false);を実行することで、クラス名にパスカル_スネーク_ケースを使用できます（バージョン3.7.2以降）
class MyController {

	public function index() {
		// 何かを実行
	}
}
```

## 名前空間

名前空間がある場合、これを実装するのは実際には非常に簡単です。`Flight::path()`メソッドを使用して、アプリケーションのルートディレクトリ（ドキュメントルートや `public/` フォルダではない）を指定する必要があります。

```php

/**
 * public/index.php
 */

// オートローダーにパスを追加
Flight::path(__DIR__.'/../');
```

これがあなたのコントローラーの見た目です。以下の例を見てくださいが、重要な情報はコメントに注目してください。

```php
/**
 * app/controllers/MyController.php
 */

// 名前空間は必須です
// 名前空間はディレクトリ構造と同じです
// 名前空間はディレクトリ構造と同じケースを使用する必要があります
// 名前空間とディレクトリにはアンダースコアを含めることはできません（Loader::setV2ClassLoading(false)が設定されていない限り）
namespace app\controllers;

// すべてのオートロードされるクラスはパスカルケース（各単語を大文字にして、スペースなし）であることが推奨されます
// ローダー::setV2ClassLoading(false);を実行することで、クラス名にパスカル_スネーク_ケースを使用できます（バージョン3.7.2以降）
class MyController {

	public function index() {
		// 何かを実行
	}
}
```

それと、utilsディレクトリ内のクラスをオートロードしたい場合は、基本的に同じことを行います：

```php

/**
 * app/UTILS/ArrayHelperUtil.php
 */

// 名前空間はディレクトリ構造とケースと一致する必要があります（UTILSディレクトリがファイルツリー内で全てキャピタライズされていることに注意）
namespace app\UTILS;

class ArrayHelperUtil {

	public function changeArrayCase(array $array) {
		// 何かを実行
	}
}
```

## クラス名にアンダースコアが含まれる場合

バージョン3.7.2以降、`Loader::setV2ClassLoading(false);`を実行することで、クラス名にパスカル_スネーク_ケースを使用できます。これにより、クラス名にアンダースコアを使用できます。これは推奨されませんが、必要な方には利用可能です。

```php

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