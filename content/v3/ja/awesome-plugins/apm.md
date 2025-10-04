# FlightPHP APM ドキュメント

FlightPHP APM へようこそ—アプリのパーソナルパフォーマンスコーチです！このガイドは、FlightPHP を使用したアプリケーション パフォーマンス モニタリング (APM) の設定、使用、マスターするためのロードマップです。遅いリクエストを追跡したり、レイテンシ チャートに没頭したりするかどうかにかかわらず、私たちがカバーします。アプリを速くし、ユーザーを幸せにし、デバッグ セッションを楽にしましょう！

Flight Docs サイトのダッシュボードの [デモ](https://flightphp-docs-apm.sky-9.com/apm/dashboard) をご覧ください。

![FlightPHP APM](/images/apm.png)

## APM が重要な理由

これを想像してください：アプリが忙しいレストランです。注文にかかる時間を追跡したり、キッチンがどこで詰まっているかを追跡する方法がないと、顧客が不機嫌になって去る理由を推測するだけです。APM はあなたの副シェフです—受信リクエストからデータベース クエリまで、すべてのステップを監視し、遅延を引き起こすものをフラグ付けします。遅いページはユーザーを失います（研究によると、サイトの読み込みに 3 秒以上かかると 53% がバウンス！）、APM はそれらの問題を *事前に* キャッチするのに役立ちます。それは積極的な安心感です—「これが壊れているのはなぜ？」という瞬間が少なくなり、「これがどれだけスムーズに動作するか見て！」という勝利が増えます。

## インストール

Composer で開始してください：

```bash
composer require flightphp/apm
```

必要なもの：
- **PHP 7.4+**：LTS Linux ディストリビューションとの互換性を保ちつつ、モダンな PHP をサポートします。
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**：私たちが強化する軽量フレームワークです。

## サポートされるデータベース

FlightPHP APM は、現在、指標を保存するための以下のデータベースをサポートしています：

- **SQLite3**：シンプルでファイルベースで、ローカル開発や小規模アプリに最適です。ほとんどのセットアップでデフォルト オプションです。
- **MySQL/MariaDB**：大規模プロジェクトや本番環境で堅牢でスケーラブルなストレージが必要な場合に理想的です。

構成ステップ（以下を参照）でデータベース タイプを選択できます。PHP 環境に必要な拡張機能がインストールされていることを確認してください（例：`pdo_sqlite` または `pdo_mysql`）。

## 開始方法

APM の素晴らしさへのステップバイステップです：

### 1. APM を登録する

`index.php` または `services.php` ファイルにこれを追加して追跡を開始してください：

```php
use flight\apm\logger\LoggerFactory;
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json');
$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);

// データベース接続を追加する場合
// Tracy Extensions からの PdoWrapper または PdoQueryCapture である必要があります
$pdo = new PdoWrapper('mysql:host=localhost;dbname=example', 'user', 'pass', null, true); // <-- APM で追跡を有効にするために True が必要です。
$Apm->addPdoConnection($pdo);
```

**ここで何が起こっていますか？**
- `LoggerFactory::create()` は構成を取得（まもなく詳述）し、ロガーをセットアップします—デフォルトで SQLite です。
- `Apm` はスターです—Flight のイベント（リクエスト、ルート、エラーなど）を監視し、指標を収集します。
- `bindEventsToFlightInstance($app)` はこれをすべて Flight アプリに結びつけます。

**プロ ティップ: サンプリング**
アプリが忙しい場合、*すべての* リクエストをログにするとオーバーロードする可能性があります。サンプル レート（0.0 から 1.0）を使用してください：

```php
$Apm = new Apm($ApmLogger, 0.1); // リクエストの 10% をログにします
```

これにより、パフォーマンスを維持しつつ、堅実なデータを取得できます。

### 2. 構成する

`.runway-config.json` を作成するためにこれを実行してください：

```bash
php vendor/bin/runway apm:init
```

**これは何をしますか？**
- 生の指標のソース（source）と処理されたデータの宛先（destination）を尋ねるウィザードを起動します。
- デフォルトは SQLite—例：ソース用に `sqlite:/tmp/apm_metrics.sqlite`、宛先用に別のもの。
- 次のような構成が得られます：
  ```json
  {
    "apm": {
      "source_type": "sqlite",
      "source_db_dsn": "sqlite:/tmp/apm_metrics.sqlite",
      "storage_type": "sqlite",
      "dest_db_dsn": "sqlite:/tmp/apm_metrics_processed.sqlite"
    }
  }
  ```

> このプロセスは、このセットアップのマイグレーションを実行するかどうかも尋ねます。初めて設定する場合、答えは yes です。

**なぜ 2 つの場所ですか？**
生の指標は急速に蓄積されます（フィルタリングされていないログを想像してください）。ワーカーがこれを構造化された宛先のダッシュボードに処理します。整理を保ちます！

### 3. ワーカーで指標を処理する

ワーカーは生の指標をダッシュボード対応データに変換します。一度実行してください：

```bash
php vendor/bin/runway apm:worker
```

**何をしていますか？**
- ソースから読み込みます（例：`apm_metrics.sqlite`）。
- 最大 100 指標（デフォルト バッチ サイズ）を宛先に処理します。
- 完了するか、指標が残っていない場合に停止します。

**継続実行する**
ライブ アプリの場合、継続的な処理を望みます。オプションはこちらです：

- **デーモン モード**：
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  指標が来るたびに永遠に実行します。開発や小規模セットアップに最適です。

- **Crontab**：
  crontab に追加してください（`crontab -e`）：
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  毎分実行—本番に最適です。

- **Tmux/Screen**：
  分離可能なセッションを開始：
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, 続いて D で分離；`tmux attach -t apm-worker` で再接続
  ```
  ログアウトしても実行を続けます。

- **カスタム調整**：
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`：一度に 50 指標を処理。
  - `--max_messages 1000`：1000 指標後に停止。
  - `--timeout 300`：5 分後に終了。

**なぜ面倒を見るのですか？**
ワーカーがないと、ダッシュボードは空です。生のログと実用的な洞察の橋渡しです。

### 4. ダッシュボードを起動する

アプリのバイタル サインを表示：

```bash
php vendor/bin/runway apm:dashboard
```

**これは何ですか？**
- `http://localhost:8001/apm/dashboard` で PHP サーバーを起動します。
- リクエスト ログ、遅いルート、エラー レートなどを表示します。

**カスタマイズ**：
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`：任意の IP からアクセス可能（リモート表示に便利）。
- `--port 8080`：8001 が使用されている場合に別のポートを使用。
- `--php-path`：PATH にない場合に PHP を指定。

ブラウザで URL にアクセスして探索してください！

#### 本番モード

本番では、ファイアウォールや他のセキュリティ対策があるため、ダッシュボードを実行するためにいくつかのテクニックを試す必要があるかもしれません。オプションはこちらです：

- **リバース プロキシを使用**：Nginx または Apache をセットアップして、ダッシュボードへのリクエストを転送します。
- **SSH トンネル**：サーバーに SSH でアクセスできる場合、`ssh -L 8080:localhost:8001 youruser@yourserver` を使用して、ダッシュボードをローカル マシンにトンネルします。
- **VPN**：サーバーが VPN の背後にある場合、それに接続してダッシュボードに直接アクセスします。
- **ファイアウォール構成**：ポート 8001 を自分の IP またはサーバーのネットワーク用に開放します。（または設定したポート）。
- **Apache/Nginx 構成**：アプリケーションの前にウェブ サーバーがある場合、ドメインまたはサブドメインに構成できます。これを行う場合、ドキュメント ルートを `/path/to/your/project/vendor/flightphp/apm/dashboard` に設定します。

#### 別のダッシュボードが欲しいですか？

自分のダッシュボードを構築できます！vendor/flightphp/apm/src/apm/presenter ディレクトリを参照して、自分のダッシュボードでデータを提示する方法のアイデアを得てください！

## ダッシュボードの機能

ダッシュボードは APM の本部です—ここで見えるものは：

- **リクエスト ログ**：タイムスタンプ、URL、レスポンス コード、合計時間付きのすべてのリクエスト。「詳細」をクリックしてミドルウェア、クエリ、エラーを表示。
- **最も遅いリクエスト**：時間を消費する上位 5 つのリクエスト（例：`/api/heavy` が 2.5s）。
- **最も遅いルート**：平均時間による上位 5 つのルート—パターンを特定するのに最適。
- **エラー レート**：失敗するリクエストのパーセンテージ（例：2.3% の 500s）。
- **レイテンシ パーセンタイル**：95 番目 (p95) と 99 番目 (p99) のレスポンス時間—最悪の場合のシナリオを知る。
- **レスポンス コード チャート**：時間経過による 200s、404s、500s を可視化。
- **長いクエリ/ミドルウェア**：上位 5 つの遅いデータベース コールとミドルウェア レイヤー。
- **キャッシュ ヒット/ミス**：キャッシュがどれだけ役立つか。

**追加機能**：
- 「最終 1 時間」、「最終 1 日」、「最終 1 週間」でフィルタリング。
- 深夜のセッション用にダーク モードを切り替え。

**例**：
`/users` へのリクエストは以下を表示するかもしれません：
- 合計時間: 150ms
- ミドルウェア: `AuthMiddleware->handle` (50ms)
- クエリ: `SELECT * FROM users` (80ms)
- キャッシュ: `user_list` でヒット (5ms)

## カスタム イベントの追加

API コールや支払いプロセスなど、何でも追跡：

```php
use flight\apm\CustomEvent;

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**どこに表示されますか？**
ダッシュボードのリクエスト詳細の「カスタム イベント」下—プリティ JSON 形式で展開可能。

**使用例**：
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
これで、その API がアプリを遅くしているかどうかを確認できます！

## データベース モニタリング

PDO クエリをこのように追跡：

```php
use flight\database\PdoWrapper;

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite', null, null, null, true); // <-- APM で追跡を有効にするために True が必要です。
$Apm->addPdoConnection($pdo);
```

**何が得られますか？**
- クエリ テキスト（例：`SELECT * FROM users WHERE id = ?`）
- 実行時間（例：0.015s）
- 行数（例：42）

**注意**：
- **オプション**：DB 追跡が必要ない場合スキップ。
- **PdoWrapper のみ**：コア PDO はまだフックされていません—続報をお待ちください！
- **パフォーマンス警告**：DB 重いサイトですべてのクエリをログにすると遅くなる可能性があります。サンプリング（`$Apm = new Apm($ApmLogger, 0.1)`）を使用して負荷を軽減。

**出力例**：
- クエリ: `SELECT name FROM products WHERE price > 100`
- 時間: 0.023s
- 行: 15

## ワーカーのオプション

ワーカーを好みに調整：

- `--timeout 300`：5 分後に停止—テストに適しています。
- `--max_messages 500`：500 指標で上限—有限に保ちます。
- `--batch_size 200`：一度に 200 を処理—速度とメモリのバランス。
- `--daemon`：非停止で実行—ライブ モニタリングに理想的。

**例**：
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
1 時間実行、一度に 100 指標を処理。

## アプリ内のリクエスト ID

各リクエストには追跡のためのユニークなリクエスト ID があります。この ID をアプリで使用してログと指標を相関させることができます。例えば、エラー ページにリクエスト ID を追加できます：

```php
Flight::map('error', function($message) {
	// レスポンス ヘッダー X-Flight-Request-Id からリクエスト ID を取得
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// また、Flight 変数から取得することもできます
	// この方法は swoole や他の非同期プラットフォームではうまく動作しません。
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## アップグレード

APM の新しいバージョンにアップグレードする場合、データベース マイグレーションを実行する必要がある可能性があります。以下のコマンドを実行してこれを行います：

```bash
php vendor/bin/runway apm:migrate
```
これにより、データベース スキーマを最新バージョンに更新するために必要なすべてのマイグレーションが実行されます。

**注意：** APM データベースのサイズが大きい場合、これらのマイグレーションは実行に時間がかかる可能性があります。オフピーク時間にこのコマンドを実行することを検討してください。

## 古いデータの消去

データベースを整理するために、古いデータを消去できます。これは、忙しいアプリを実行していてデータベースのサイズを管理しやすくしたい場合に特に有用です。
以下のコマンドを実行してこれを行います：

```bash
php vendor/bin/runway apm:purge
```
これにより、データベースから 30 日より古いすべてのデータが削除されます。`--days` オプションに異なる値を渡すことで、日数を調整できます：

```bash
php vendor/bin/runway apm:purge --days 7
```
これにより、データベースから 7 日より古いすべてのデータが削除されます。

## トラブルシューティング

困っていますか？これを試してください：

- **ダッシュボードにデータがない？**
  - ワーカーが実行中ですか？`ps aux | grep apm:worker` をチェック。
  - 構成パスが一致しますか？`.runway-config.json` の DSN が実際のファイルにポイントしていることを確認。
  - 保留中の指標を処理するために `php vendor/bin/runway apm:worker` を手動で実行。

- **ワーカーのエラー？**
  - SQLite ファイルを確認（例：`sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`）。
  - PHP ログでスタック トレースを確認。

- **ダッシュボードが開始されない？**
  - ポート 8001 が使用中？`--port 8080` を使用。
  - PHP が見つからない？`--php-path /usr/bin/php` を使用。
  - ファイアウォールがブロック？ポートを開放するか、`--host localhost` を使用。

- **遅すぎる？**
  - サンプル レートを下げる：`$Apm = new Apm($ApmLogger, 0.05)` (5%)。
  - バッチ サイズを減らす：`--batch_size 20`。

- **例外/エラーが追跡されない？**
  - プロジェクトで [Tracy](https://tracy.nette.org/) が有効の場合、Flight のエラー処理をオーバーライドします。Tracy を無効にして、`Flight::set('flight.handle_errors', true);` が設定されていることを確認してください。

- **データベース クエリが追跡されない？**
  - データベース接続に `PdoWrapper` を使用していることを確認。
  - コンストラクタの最後の引数を `true` にしていることを確認。