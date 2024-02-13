# オートローディング

オートローディングは、PHPの概念で、クラスを読み込むためにディレクトリを指定するものです。これは`require`や`include`を使用してクラスを読み込むよりも有益です。Composerパッケージを使用する際にも必須です。

デフォルトでは`Flight`クラスはComposerのおかげで自動的にオートロードされます。ただし、独自のクラスをオートロードしたい場合は、`Flight::path`メソッドを使用してクラスを読み込むディレクトリを指定できます。

## 基本的な例

以下のようなディレクトリツリーがあるとします：

```text
# 例
/home/user/project/my-flight-project/
├── app
│   ├── cache
│   ├── config
│   ├── controllers - このプロジェクト用のコントローラーが含まれています
│   ├── translations
│   ├── UTILS - このアプリケーション専用のクラスが含まれています（後で例としてわざと全て大文字になっています）
│   └── views
└── public
    └── css
    └── js
    └── index.php
```

これはこのドキュメントサイトと同じファイル構造であることに気づいたかもしれません。

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

// オートロードされるすべてのクラスは、各単語の先頭を大文字にしてパスカルケースにすることが推奨されます（単語間にスペースはありません）
// クラス名にアンダースコアを含めることはできないという要件もあります
class MyController {

	public function index() {
		// 何かをする
	}
}
```

## 名前空間

名前空間がある場合、これを実装するのは実際には非常に簡単です。アプリケーションのルートディレクトリ（ドキュメントルートや`public/`フォルダではない）を指定するために`Flight::path()`メソッドを使用する必要があります。

```php

/**
 * public/index.php
 */

// オートローダーにパスを追加
Flight::path(__DIR__.'/../');
```

これがコントローラーの見た目です。以下の例を見てくださいが、重要な情報にはコメントを注目してください。

```php
/**
 * app/controllers/MyController.php
 */

// 名前空間が必要です
// 名前空間はディレクトリ構造と同じです
// 名前空間はディレクトリ構造と同じパターンである必要があります
// 名前空間とディレクトリにアンダースコアを含めることはできません
namespace app\controllers;

// オートロードされるすべてのクラスは、各単語の先頭を大文字にしてパスカルケースにすることが推奨されます（単語間にスペースはありません）
// クラス名にアンダースコアを含めることはできないという要件もあります
class MyController {

	public function index() {
		// 何かをする
	}
}
```

そして、あなたがutilsディレクトリにあるクラスをオートロードしたい場合、基本的に同じことを行います：

```php

/**
 * app/UTILS/ArrayHelperUtil.php
 */

// 名前空間はディレクトリ構造とケースと一致している必要があります（ファイルツリーでUTILSディレクトリが全部大文字になっていることに注意してください）
namespace app\UTILS;

class ArrayHelperUtil {

	public function changeArrayCase(array $array) {
		// 何かをする
	}
}
```