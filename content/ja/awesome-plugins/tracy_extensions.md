Tracy Flight Panel Extensions
=====

これは、Flight を使う際に利便性を高めるための拡張機能のセットです。

- Flight - すべての Flight 変数を分析します。
- Database - ページで実行されたすべてのクエリを分析します（データベース接続を正しく初期化した場合）
- Request - すべての `$_SERVER` 変数を分析し、すべてのグローバルペイロード（`$_GET`、`$_POST`、`$_FILES`）を調べます。
- Session - セッションがアクティブな場合、すべての `$_SESSION` 変数を分析します。

これがパネルです

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

そして、各パネルはアプリケーションに関する非常に役立つ情報を表示します！

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

インストール
-------
`composer require flightphp/tracy-extensions --dev` を実行して、準備は完了です！

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
// 開発環境のみで使用する必要がある必須の PDO ラッパーがあります
// 通常の PDO 接続と同じパラメータを持ちます
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// またはこのFlight フレームワークにアタッチする場合
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// クエリを実行するたびに時間、クエリ、およびパラメータをキャプチャします

// これがつながりです
if(Debugger::$showBar === true) {
	// これは false でなければならず、そうでないと Tracy が実際にレンダリングできません :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// さらにコード

Flight::start();
```

## 追加の設定

### セッションデータ
カスタムセッションハンドラー（例: ghostff/session）を使用している場合、任意のセッションデータの配列を Tracy に渡すことができ、自動的に出力されます。`TracyExtensionLoader` コンストラクタの第2パラメータで `session_data` キーを使用して渡します。

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// これは false でなければならず、そうでないと Tracy が実際にレンダリングできません :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// ルートおよびその他の処理...

Flight::start();
```

### Latte

プロジェクトに Latte がインストールされている場合、テンプレートを分析するために Latte パネルを使用できます。Latte インスタンスを `TracyExtensionLoader` コンストラクタの第2パラメータで `latte` キーを使用して渡すことができます。

```php

use Latte\Engine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', Engine::class, [], function($latte) {
	$latte->setTempDirectory(__DIR__ . '/temp');

	// ここで Latte パネルを Tracy に追加します
	$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
});

if(Debugger::$showBar === true) {
	// これは false でなければならず、そうでないと Tracy が実際にレンダリングできません :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}
