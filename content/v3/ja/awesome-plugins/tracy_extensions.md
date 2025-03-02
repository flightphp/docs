Tracy Flight Panel Extensions
=====

これはFlightを使いやすくするための拡張機能セットです。

- Flight - すべてのFlight変数を分析します。
- Database - ページで実行されたすべてのクエリを分析します（データベース接続を正しく初期化した場合）
- Request - `$_SERVER`変数を分析し、すべてのグローバルペイロード（`$_GET`、`$_POST`、`$_FILES`）を調べます。
- Session - セッションがアクティブな場合、すべての`$_SESSION`変数を分析します。

これはパネルです

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

それぞれのパネルはアプリケーションについて非常に役立つ情報を表示します！

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

[ここをクリック](https://github.com/flightphp/tracy-extensions)してコードを表示します。

インストール
-------
`composer require flightphp/tracy-extensions --dev` を実行して、準備が整います！

設定
-------
これを開始するために行う必要がある設定は非常に少ないです。これを使用する前に Tracy デバッガを初期化する必要があります [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// ブートストラップコード
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Debugger::enable(Debugger::DEVELOPMENT) で環境を指定する必要があるかもしれません

// アプリでデータベース接続を使用する場合、
//（本番環境ではなく開発環境でのみ使用）必要なPDOラッパーがあります
// 通常のPDO接続と同じパラメーターを持っています
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// またはFlightフレームワークにこれをアタッチする場合
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// クエリを実行するたびに、時間、クエリ、およびパラメーターがキャプチャされます

// これが全体を結びつけます
if(Debugger::$showBar === true) {
	// これは false にする必要があります、さもないとTracy が実際にレンダリングできません :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// もっとコード

Flight::start();
```

## 追加の設定

### セッションデータ
カスタムセッションハンドラー（例えばghostff/sessionなど）を持っている場合、
任意のセッションデータ配列をTracyに渡し、自動的に出力します。
`TracyExtensionLoader` コンストラクターの第二パラメーターで `session_data` キーで渡します。

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// これは false にする必要があります、さもないとTracy が実際にレンダリングできません :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// ルートやその他のもの...

Flight::start();
```

### Latte

プロジェクトにLatteがインストールされている場合、
テンプレートを分析するためのLatteパネルを使用できます。
`TracyExtensionLoader` コンストラクターの第二パラメーターで `latte` キーでLatteインスタンスを渡すことができます。

```php

use Latte\Engine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', Engine::class, [], function($latte) {
	$latte->setTempDirectory(__DIR__ . '/temp');

	// これでLatte PanelをTracyに追加します
	$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
});

if(Debugger::$showBar === true) {
	// これは false にする必要があります、さもないとTracy が実際にレンダリングできません :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}