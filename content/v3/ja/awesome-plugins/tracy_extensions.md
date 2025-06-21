Tracy Flight パネル拡張
=====

これは、Flight をより豊かに扱うための拡張機能のセットです。

- Flight - すべての Flight 変数を分析します。
- Database - ページで実行されたすべてのクエリを分析します（データベース接続を正しく開始した場合）。
- Request - すべての `$_SERVER` 変数を分析し、グローバルペイロード（`$_GET`、`$_POST`、`$_FILES`）を調べます。
- Session - セッションが有効な場合、すべての `$_SESSION` 変数を分析します。

これはパネルです

![Flight バー](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

各パネルは、アプリケーションに関する非常に役立つ情報を表示します！

![Flight データ](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight データベース](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight リクエスト](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

[こちら](https://github.com/flightphp/tracy-extensions) をクリックしてコードを表示します。

インストール
-------
`composer require flightphp/tracy-extensions --dev` を実行すると、すぐに開始できます！

設定
-------
これを開始するには、ほとんど設定は必要ありません。この拡張機能を使用する前に、Tracy デバッガーを開始する必要があります [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// ブートストラップコード
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// 環境を指定する必要がある場合、Debugger::enable(Debugger::DEVELOPMENT) を使用します

// アプリでデータベース接続を使用する場合、
// 開発環境でのみ使用する（本番環境では使用しないでください！）
// 通常のPDO接続と同じパラメータです
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// Flight フレームワークにこれを付与する場合
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// クエリを実行すると、時間、クエリ、パラメータをキャプチャします

// これでつながります
if(Debugger::$showBar === true) {
	// これをfalseにしないと、Tracy が正しく表示されません
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// 追加のコード

Flight::start();
```

## 追加の設定

### セッションデータ
カスタムのセッションハンドラ（例: ghostff/session）を使用している場合、Tracy にセッションデータの配列を渡すと、自動的に出力されます。`TracyExtensionLoader` コンストラクタの2番目のパラメータで `session_data` キーを使って渡します。

```php

use Ghostff\Session\Session;
// または flight\Session を使用します;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// これをfalseにしないと、Tracy が正しく表示されません
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// ルートや他のもの...

Flight::start();
```

### Latte

プロジェクトに Latte をインストールしている場合、Latte パネルを使用してテンプレートを分析できます。`TracyExtensionLoader` コンストラクタの2番目のパラメータで `latte` キーで Latte インスタンスを渡します。

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
	// これをfalseにしないと、Tracy が正しく表示されません
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}
```