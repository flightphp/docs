Tracy フライトパネル拡張機能
=====

これは、Flight の操作をより豊かにするための拡張機能セットです。

- フライト - すべての Flight 変数を分析します。
- データベース - ページで実行されたすべてのクエリを分析します（データベース接続を正しく初期化した場合）
- リクエスト - すべての `$_SERVER` 変数を分析し、すべてのグローバルペイロード（`$_GET`、`$_POST`、`$_FILES`）を調べます
- セッション - セッションが有効な場合、すべての `$_SESSION` 変数を分析します。

これがパネルです

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

そして、各パネルはアプリケーションに関する非常に役立つ情報を表示します！

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

インストール
-------
`composer require flightphp/tracy-extensions --dev` を実行してください！

設定
-------
これを開始するにはほとんど設定する必要はありません。これを使用する前に Tracy デバッガを初期化する必要があります [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// ブートストラップコード
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Debugger::enable(Debugger::DEVELOPMENT) で環境を指定する必要がある場合があります

// アプリでデータベース接続を使用する場合、
// 開発環境のみで使用する必要がある必須の PDO ラッパーがあります
// これは通常の PDO 接続と同じパラメータを持っています
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// または Flight フレームワークにこれをアタッチする場合
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// クエリを実行するたびに、時間、クエリ、パラメータをキャプチャします

// これが全体をつなげるものです
if(Debugger::$showBar === true) {
	// これは false である必要があります、さもないと Tracy は実際にレンダリングできません :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// もっとコード

Flight::start();
```

## 追加の設定

### セッションデータ
カスタムセッションハンドラ（ghostff/session など）を使用している場合、Tracy にセッションデータの配列を渡すことができ、自動的に出力されます。`TracyExtensionLoader` コンストラクタの2番目のパラメータ内の `session_data` キーで渡します。

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// これは false である必要があります、さもないと Tracy は実際にレンダリングできません :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// ルートやその他の処理...

Flight::start();
```

### Latte

プロジェクトに Latte がインストールされている場合、Latte パネルを使用してテンプレートを分析できます。`TracyExtensionLoader` コンストラクタの2番目のパラメータで、`latte` キーで Latte インスタンスを渡すことができます。

```php

use Latte\Engine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', Engine::class, [], function($latte) {
	$latte->setTempDirectory(__DIR__ . '/temp');

	// ここに Latte パネルを Tracy に追加します
	$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
});

if(Debugger::$showBar === true) {
	// これは false である必要があります、さもないと Tracy は実際にレンダリングできません :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}