# FlightPHP APM ドキュメント

FlightPHP APM へようこそ—あなたのアプリのパーソナルパフォーマンスコーチです！このガイドは、FlightPHP を使用した Application Performance Monitoring (APM) のセットアップ、使用、マスターのためのロードマップです。遅いリクエストを追跡したり、レイテンシーチャートに没頭したりするかどうかにかかわらず、私たちがカバーします。あなたのアプリを速くし、ユーザーを幸せにし、デバッグセッションを楽にしましょう！

Flight Docs サイトのダッシュボードの [デモ](https://flightphp-docs-apm.sky-9.com/apm/dashboard) をご覧ください。

![FlightPHP APM](/images/apm.png)

## APM が重要な理由

こんな状況を想像してください：あなたのアプリは忙しいレストランです。注文にかかる時間を追跡したり、キッチンがどこで滞っているかを把握する手段がなければ、なぜ顧客が不機嫌になって去るのかを推測するだけです。APM はあなたの副シェフです—着信リクエストからデータベースクエリまですべてのステップを監視し、遅延を引き起こすものをフラグ付けします。遅いページはユーザーを失います（研究によると、サイトの読み込みに3秒以上かかると53%がバウンス！）、APM はそれらの問題を *事前に* キャッチするのに役立ちます。それは積極的な安心感—「なぜこれが壊れているのか？」という瞬間を減らし、「これがどれだけスムーズに動作するか見て！」という勝利を増やします。

## インストール

Composer で開始してください：

```bash
composer require flightphp/apm
```

必要なもの：
- **PHP 7.4+**：LTS Linux ディストリビューションとの互換性を保ちつつ、現代の PHP をサポートします。
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**：私たちが強化する軽量フレームワークです。

## サポートされるデータベース

FlightPHP APM は現在、以下のデータベースをメトリクスの保存にサポートしています：

- **SQLite3**：シンプルでファイルベース、ローカル開発や小規模アプリに最適。ほとんどのセットアップでデフォルトオプションです。
- **MySQL/MariaDB**：大規模プロジェクトや本番環境で堅牢でスケーラブルなストレージが必要な場合に理想的です。

構成ステップ（以下参照）でデータベースタイプを選択できます。PHP 環境に必要な拡張機能がインストールされていることを確認してください（例：`pdo_sqlite` または `pdo_mysql`）。

## 開始方法

APM の素晴らしさへのステップバイステップ：

### 1. APM を登録する

追跡を開始するために、これを `index.php` または `services.php` ファイルに追加してください：

```php
use flight\apm\logger\LoggerFactory;
use flight\database\PdoWrapper;
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json');
$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);

// データベース接続を追加する場合
// Tracy Extensions からの PdoWrapper または PdoQueryCapture でなければなりません
$pdo = new PdoWrapper('mysql:host=localhost;dbname=example', 'user', 'pass', null, true); // <-- APM での追跡を有効にするために True が必要です。
$Apm->addPdoConnection($pdo);
```

**ここで何が起こっているか？**
- `LoggerFactory::create()` は構成を取得（まもなく詳述）し、ロガーをセットアップします—デフォルトで SQLite。
- `Apm` はスターです—Flight のイベント（リクエスト、ルート、エラーなど）を監視し、メトリクスを収集します。
- `bindEventsToFlightInstance($app)` はこれをすべてあなたの Flight アプリに結びつけます。

**プロチップ：サンプリング**
アプリが忙しい場合、*すべての* リクエストをログにすると過負荷になる可能性があります。サンプルレート（0.0 から 1.0）を使用してください：

```php
$Apm = new Apm($ApmLogger, 0.1); // リクエストの 10% をログにします
```

これにより、パフォーマンスを維持しつつ、堅実なデータを取得できます。

### 2. 構成する

`.runway-config.json` を作成するためにこれを実行してください：

```bash
php vendor/bin/runway apm:init
```

**これは何をするか？**
- 生メトリクスのソース（source）と処理済みデータの宛先（destination）を尋ねるウィザードを起動します。
- デフォルトは SQLite—例：ソース用に `sqlite:/tmp/apm_metrics.sqlite`、宛先用に別のもの。
- 以下のような構成が得られます：
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

> このプロセスは、このセットアップのマイグレーションを実行するかどうかも尋ねます。初めてセットアップする場合、答えは yes です。

**なぜ2つの場所か？**
生メトリクスは急速に蓄積されます（フィルタリングされていないログを想像してください）。ワーカーがこれを構造化された宛先へ処理し、ダッシュボード用にします。すべてを整理します！

### 3. ワーカーでメトリクスを処理する

ワーカーは生メトリクスをダッシュボード対応データに変換します。一度実行してください：

```bash
php vendor/bin/runway apm:worker
```

**何をしているか？**
- ソースから読み込みます（例：`apm_metrics.sqlite`）。
- 最大 100 メトリクス（デフォルトバッチサイズ）を宛先へ処理します。
- 完了するか、メトリクスがなくなると停止します。

**継続実行する**
ライブアプリでは、継続的な処理を望みます。オプションはこちら：

- **デーモンモード**：
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  ずっと実行し、メトリクスを処理します。開発や小規模セットアップに最適です。

- **Crontab**：
  crontab に追加（`crontab -e`）：
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  毎分実行—本番に最適です。

- **Tmux/Screen**：
  分離可能なセッションを開始：
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, then D で分離；`tmux attach -t apm-worker` で再接続
  ```
  ログアウトしても実行を続けます。

- **カスタム調整**：
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`：一度に 50 メトリクスを処理。
  - `--max_messages 1000`：1000 メトリクス後に停止。
  - `--timeout 300`：5分後に終了。

**なぜ面倒を見るか？**
ワーカーがないと、ダッシュボードは空です。それは生ログと実用的な洞察の橋です。

### 4. ダッシュボードを起動する

アプリのバイタルサインを表示：

```bash
php vendor/bin/runway apm:dashboard
```

**これは何？**
- `http://localhost:8001/apm/dashboard` で PHP サーバーを起動。
- リクエストログ、遅いルート、エラーレートなどを表示。

**カスタマイズ**：
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`：任意の IP からアクセス可能（リモート閲覧に便利）。
- `--port 8080`：8001 が使用中の場合に異なるポートを使用。
- `--php-path`：PATH にない場合に PHP を指定。

ブラウザで URL にアクセスして探索してください！

#### 本番モード

本番では、ファイアウォールや他のセキュリティ対策があるため、ダッシュボードを起動するためにいくつかのテクニックを試す必要があります。オプションはこちら：

- **リバースプロキシを使用**：Nginx または Apache をセットアップして、ダッシュボードへのリクエストを転送。
- **SSH トンネル**：サーバーに SSH で接続できる場合、`ssh -L 8080:localhost:8001 youruser@yourserver` を使用してダッシュボードをローカルマシンにトンネル。
- **VPN**：サーバーが VPN の背後にある場合、接続してダッシュボードに直接アクセス。
- **ファイアウォール構成**：ポート 8001 をあなたの IP またはサーバーネットワーク用に開放（または設定したポート）。
- **Apache/Nginx 構成**：アプリケーションの前にウェブサーバーがある場合、ドメインまたはサブドメインに構成可能。これを行う場合、ドキュメントルートを `/path/to/your/project/vendor/flightphp/apm/dashboard` に設定。

#### 別のダッシュボードが欲しい？

独自のダッシュボードを構築できます！vendor/flightphp/apm/src/apm/presenter ディレクトリを参照して、独自のダッシュボードでデータを提示する方法のアイデアを得てください！

## ダッシュボードの機能

ダッシュボードは APM の本部—ここで見られるもの：

- **リクエストログ**：タイムスタンプ、URL、レスポンスコード、総時間付きのすべてのリクエスト。「詳細」をクリックしてミドルウェア、クエリ、エラーを表示。
- **最も遅いリクエスト**：時間を消費するトップ 5 リクエスト（例：「/api/heavy」 2.5s）。
- **最も遅いルート**：平均時間によるトップ 5 ルート—パターンを特定するのに最適。
- **エラーレート**：失敗するリクエストのパーセンテージ（例：2.3% の 500s）。
- **レイテンシーパーセンタイル**：95th (p95) および 99th (p99) レスポンス時間—最悪ケースを知る。
- **レスポンスコードチャート**：時間経過による 200s、404s、500s を可視化。
- **長いクエリ/ミドルウェア**：トップ 5 の遅いデータベースコールとミドルウェアレイヤー。
- **キャッシュヒット/ミス**：キャッシュがどれだけ役立つか。

**エクストラ**：
- 「最終1時間」、「最終1日」、「最終1週間」でフィルタ。
- 深夜セッション用のダークモードを切り替え。

**例**：
`/users` へのリクエストは以下を表示する可能性：
- 総時間：150ms
- ミドルウェア：`AuthMiddleware->handle` (50ms)
- クエリ：`SELECT * FROM users` (80ms)
- キャッシュ：`user_list` でヒット (5ms)

## カスタムイベントの追加

API コールや支払いプロセスなど何でも追跡：

```php
use flight\apm\CustomEvent;

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**どこに表示されるか？**
ダッシュボードのリクエスト詳細の「カスタムイベント」下—プリティ JSON フォーマットで展開可能。

**ユースケース**：
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
これで、その API がアプリを遅くしているかどうかがわかります！

## データベース監視

PDO クエリを以下のように追跡：

```php
use flight\database\PdoWrapper;

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite', null, null, null, true); // <-- APM での追跡を有効にするために True が必要です。
$Apm->addPdoConnection($pdo);
```

**得られるもの**：
- クエリテキスト（例：`SELECT * FROM users WHERE id = ?`）
- 実行時間（例：0.015s）
- 行数（例：42）

**注意**：
- **オプション**：DB 追跡が必要ない場合スキップ。
- **PdoWrapper のみ**：コア PDO はまだフックされていません—続報をお待ちください！
- **パフォーマンス警告**：DB 重いサイトですべてのクエリをログにすると遅くなる可能性。サンプリング（`$Apm = new Apm($ApmLogger, 0.1)`）を使用して負荷を軽減。

**例出力**：
- クエリ：`SELECT name FROM products WHERE price > 100`
- 時間：0.023s
- 行：15

## ワーカーのオプション

ワーカーを好みに調整：

- `--timeout 300`：5分後に停止—テストに適。
- `--max_messages 500`：500 メトリクスで上限—有限に保つ。
- `--batch_size 200`：一度に 200 を処理—速度とメモリのバランス。
- `--daemon`：非停止実行—ライブ監視に理想。

**例**：
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
1時間実行し、一度に 100 メトリクスを処理。

## アプリ内のリクエスト ID

各リクエストには追跡用のユニークなリクエスト ID があります。この ID をアプリで使用してログとメトリクスを相関できます。例えば、エラーページにリクエスト ID を追加：

```php
Flight::map('error', function($message) {
	// レスポンスヘッダー X-Flight-Request-Id からリクエスト ID を取得
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// また Flight 変数から取得可能
	// Swoole や他の非同期プラットフォームではこの方法はうまく動作しません。
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## アップグレード

APM の新しいバージョンにアップグレードする場合、データベースマイグレーションを実行する必要がある可能性があります。以下のコマンドを実行してください：

```bash
php vendor/bin/runway apm:migrate
```
これにより、データベーススキーマを最新バージョンに更新するための必要なマイグレーションが実行されます。

**注意：** APM データベースがサイズが大きい場合、これらのマイグレーションは実行に時間がかかる可能性があります。オフピーク時にこのコマンドを実行することを検討してください。

### 0.4.3 から 0.5.0 へのアップグレード

0.4.3 から 0.5.0 にアップグレードする場合、以下のコマンドを実行する必要があります：

```bash
php vendor/bin/runway apm:config-migrate
```

これにより、古い形式の `.runway-config.json` ファイルを使用した構成を、新しい形式の `config.php` ファイルにキー/値を保存する形式にマイグレーションします。

## 古いデータの削除

データベースを整理するために、古いデータを削除できます。これは、忙しいアプリを実行していてデータベースサイズを管理したい場合に特に有用です。
以下のコマンドを実行してください：

```bash
php vendor/bin/runway apm:purge
```
これにより、データベースから 30 日以上前のすべてのデータが削除されます。`--days` オプションに異なる値を渡すことで、日数を調整できます：

```bash
php vendor/bin/runway apm:purge --days 7
```
これにより、データベースから 7 日以上前のすべてのデータが削除されます。

## トラブルシューティング

困ったら、これを試してください：

- **ダッシュボードにデータがない？**
  - ワーカーが実行中か？`ps aux | grep apm:worker` をチェック。
  - 構成パスが一致するか？`.runway-config.json` の DSN が実際のファイル指向を確認。
  - `php vendor/bin/runway apm:worker` を手動で実行して保留中のメトリクスを処理。

- **ワーカーエラー？**
  - SQLite ファイルを確認（例：`sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`）。
  - PHP ログでスタックトレースを確認。

- **ダッシュボードが起動しない？**
  - ポート 8001 が使用中？`--port 8080` を使用。
  - PHP が見つからない？`--php-path /usr/bin/php` を使用。
  - ファイアウォールがブロック？ポートを開放するか `--host localhost` を使用。

- **遅すぎる？**
  - サンプルレートを下げる：`$Apm = new Apm($ApmLogger, 0.05)` (5%)。
  - バッチサイズを減らす：`--batch_size 20`。

- **例外/エラーが追跡されない？**
  - プロジェクトで [Tracy](https://tracy.nette.org/) が有効の場合、Flight のエラーハンドリングをオーバーライドします。Tracy を無効にし、`Flight::set('flight.handle_errors', true);` が設定されていることを確認してください。

- **データベースクエリが追跡されない？**
  - データベース接続に `PdoWrapper` を使用していることを確認。
  - コンストラクタの最後の引数が `true` であることを確認。