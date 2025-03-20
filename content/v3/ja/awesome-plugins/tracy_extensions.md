Tracy Flight パネル拡張
=====

これは、Flightをよりリッチに使用するための一連の拡張です。

- Flight - すべてのFlight変数を分析します。
- Database - ページで実行されたすべてのクエリを分析します（データベース接続を正しく開始した場合）。
- Request - すべての `$_SERVER` 変数を分析し、すべてのグローバルペイロード（`$_GET`、`$_POST`、`$_FILES`）を調べます。
- Session - セッションがアクティブな場合、すべての `$_SESSION` 変数を分析します。

これがパネルです。

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

それぞれのパネルは、アプリケーションに関する非常に役立つ情報を表示します！

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

コードは[こちら](https://github.com/flightphp/tracy-extensions)から確認できます。

インストール
-------
`composer require flightphp/tracy-extensions --dev`を実行すると、準備完了です！

設定
-------
これを始めるために必要な設定はほとんどありません。使用する前にTracyデバッガを初期化する必要があります。[https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// ブートストラップコード
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// 環境を指定する必要がある場合があります Debugger::enable(Debugger::DEVELOPMENT)

// アプリでデータベース接続を使用する場合、 
// 開発中のみ使用する必要がある必須PDOラッパーがあります（本番環境では使用しないでください！）
// 通常のPDO接続と同じパラメータを持っています
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// または、これをFlightフレームワークに接続する場合
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// クエリを行うたびに、その時間、クエリ、およびパラメータがキャプチャされます

// これは点をつなぎます
if(Debugger::$showBar === true) {
	// これはfalseである必要があります。でないとTracyは実際にレンダリングできません :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// さらなるコード

Flight::start();
```

## 追加の設定

### セッションデータ
カスタムセッションハンドラ（例えば、ghostff/session）を持っている場合、Tracyに任意のセッションデータの配列を渡すことができます。これを`TracyExtensionLoader`コンストラクタの二番目のパラメータの`session_data`キーで渡します。

```php

use Ghostff\Session\Session;
// または flight\Session を使用します。

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// これはfalseである必要があります。でないとTracyは実際にレンダリングできません :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// ルートやその他のこと...

Flight::start();
```

### Latte

プロジェクトにLatteがインストールされている場合、Latteパネルを使用してテンプレートを分析できます。 `TracyExtensionLoader`コンストラクタの二番目のパラメータに`latte`キーを使用してLatteインスタンスを渡すことができます。

```php

use Latte\Engine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', Engine::class, [], function($latte) {
	$latte->setTempDirectory(__DIR__ . '/temp');

	// ここでLatteパネルをTracyに追加します
	$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
});

if(Debugger::$showBar === true) {
	// これはfalseである必要があります。でないとTracyは実際にレンダリングできません :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}
```