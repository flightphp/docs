# Tracy Flight Panel Extensions
=====

これは、Flight をより豊かに作業するための拡張機能のセットです。

- Flight - すべての Flight 変数を分析します。
- Database - ページで実行されたすべてのクエリを分析します（データベース接続を正しく開始した場合）
- Request - すべての `$_SERVER` 変数を分析し、すべてのグローバルペイロード（`$_GET`、`$_POST`、`$_FILES`）を調べます
- Session - セッションがアクティブな場合、すべての `$_SESSION` 変数を分析します。

これは Panel です

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

そして、各パネルはアプリケーションに関する非常に役立つ情報を表示します！

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

コードを表示するには [here](https://github.com/flightphp/tracy-extensions) をクリックしてください。

Installation
-------
`composer require flightphp/tracy-extensions --dev` を実行するだけで、すぐに始められます！

Configuration
-------
これを始めるために必要な設定はほとんどありません。この機能を使用する前に Tracy デバッガーを開始する必要があります [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// bootstrap code
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// 環境を指定する必要がある場合があります Debugger::enable(Debugger::DEVELOPMENT)

// アプリでデータベース接続を使用する場合、
// 開発でのみ使用する（本番環境では使用しないでください！）必須の PDO ラッパーがあります
// 通常の PDO 接続と同じパラメータです
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// または Flight フレームワークにアタッチする場合
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// これでクエリを実行するたびに、時間、クエリ、パラメータをキャプチャします

// これでつながります
if(Debugger::$showBar === true) {
	// これが false でないと Tracy がレンダリングできません :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// more code

Flight::start();
```

## Additional Configuration

### Session Data
カスタムセッションハンドラー（例: ghostff/session）をお持ちの場合、Tracy にセッションデータの配列を渡すことができ、自動的に出力されます。`TracyExtensionLoader` コンストラクタの 2 番目のパラメータの `session_data` キーで渡します。

```php

use Ghostff\Session\Session;
// または flight\Session を使用;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// これが false でないと Tracy がレンダリングできません :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// routes and other things...

Flight::start();
```

### Latte

_このセクションには PHP 8.1+ が必要です。_

プロジェクトに Latte がインストールされている場合、Tracy は Latte とネイティブに統合されており、テンプレートを分析できます。Latte インスタンスに拡張を登録するだけです。

```php

require 'vendor/autoload.php';

$app = Flight::app();

$app->map('render', function($template, $data, $block = null) {
	$latte = new Latte\Engine;

	// other configurations...

	// Tracy Debug Bar が有効な場合のみ拡張を追加
	if(Debugger::$showBar === true) {
		// ここで Latte Panel を Tracy に追加
		$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
	}

	$latte->render($template, $data, $block);
});
```