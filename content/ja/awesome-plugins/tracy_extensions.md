Tracy Flight Panel Extensions
=====

これはFlightを使いやすくするための拡張機能セットです。

- Flight - すべてのFlight変数を分析します。
- Database - ページで実行されたすべてのクエリを分析します（データベース接続を正しく初期化する場合）
- Request - すべての`$_SERVER`変数を分析し、すべてのグローバルペイロード（`$_GET`、`$_POST`、`$_FILES`）を調べます。
- Session - セッションがアクティブな場合はすべての`$_SESSION`変数を分析します。

これがパネルです

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

そして各パネルにはアプリケーションに関する非常に役立つ情報が表示されます！

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

インストール
-------
`composer require flightphp/tracy-extensions --dev` を実行して、準備は完了です！

構成
-------
これを開始するために行う必要がある構成は非常に少ないです。Tracyデバッガを使用する前にこれを初期化する必要があります [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// ブートストラップコード
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Debugger::enable(Debugger::DEVELOPMENT) で環境を指定する必要があるかもしれません

// アプリケーションでデータベース接続を使用する場合、
// 開発環境でのみ使用する必要があるPDOラッパーがあります（本番環境では使用しないでください！）
// 通常のPDO接続と同じパラメータが必要です
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// またはFlightフレームワークにこれを添付する場合
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// クエリを実行すると、時間、クエリ、およびパラメータがキャプチャされます

// これが関連付けられます
if(Debugger::$showBar === true) {
	new TracyExtensionLoader(Flight::app());
}

// その他のコード

Flight::start();
```  