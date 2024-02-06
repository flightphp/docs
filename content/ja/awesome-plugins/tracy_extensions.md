# Tracy Flight Panel Extensions
=====

これは、Flightを使いやすくするための拡張セットです。

- Flight - すべてのFlight変数を分析します。
- データベース - ページで実行されたすべてのクエリを分析します（データベース接続を正しく初期化した場合）
- リクエスト - すべての`$_SERVER`変数を分析し、すべてのグローバルペイロード（`$_GET`、`$_POST`、`$_FILES`）を検査します。
- セッション - セッションがアクティブな場合、すべての`$_SESSION`変数を分析します。

# これはパネルです

![Flight Bar（フライトバー）](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

そして、各パネルはアプリケーションについて非常に役立つ情報を表示します！

![Flight Data（フライトデータ）](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database（フライトデータベース）](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request（フライトリクエスト）](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

## インストール
-------
`composer require flightphp/tracy-extensions --dev` を実行して、設定が完了です！

## 設定
-------
始めるにはほとんど設定する必要はありません。[https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide) を使用する前にTracyデバッガを初期化する必要があります：

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// ブートストラップコード
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Debugger::enable(Debugger::DEVELOPMENT) で環境を指定する必要があるかもしれません

// アプリケーションでデータベース接続を使用する場合、
// 開発環境でのみ使用する必須のPDOラッパーがあります（本番での使用は避けてください）
// 通常のPDO接続と同じパラメータを持っています
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// またはFlightフレームワークにこれをアタッチする場合
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// クエリを実行するたびに、時間、クエリ、およびパラメータがキャプチャされます

// これで全体のイメージがつかめます
if(Debugger::$showBar === true) {
	new TracyExtensionLoader(Flight::app());
}

// もっとコード

Flight::start();
```