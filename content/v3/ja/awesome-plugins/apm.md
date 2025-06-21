# FlightPHP APM ドキュメント

FlightPHP APM にようこそ—あなたのアプリのパーソナルなパフォーマンスコーチです！ このガイドは、Application Performance Monitoring (APM) を FlightPHP とともに設定し、使用し、マスターするためのロードマップです。 遅いリクエストを追跡したり、レイテンシのチャートに熱中したりするかどうか、私たちがカバーしています。 アプリをより速くし、ユーザーをより幸せにし、デバッグセッションを簡単に行うようにしましょう！

## APM の重要性

想像してみてください：あなたのアプリは忙しいレストランです。 注文にどれくらい時間がかかるかを追跡する方法がないと、キッチンがどこで遅れているのかを推測し、顧客が不機嫌になって去る理由を当てずっぽうに考えています。 APM はあなたの副料理長のようなものです—着信リクエストからデータベースクエリまで、すべてのステップを監視し、遅延を引き起こすものをフラグします。 遅いページはユーザーを失います（研究によると、サイトの読み込みに 3 秒以上かかると 53% が離脱する！）、APM はそれらの問題を *事前に* キャッチします。 これは積極的な安心感です—「なぜこれが壊れているの？」という瞬間を少なくし、「これがどれほどスムーズに動くか！」という勝利を増やします。

## インストール

Composer で始めましょう：

```bash
composer require flightphp/apm
```

必要なもの：
- **PHP 7.4+**：LTS Linux ディストリビューションとの互換性を保ちつつ、現代の PHP をサポートします。
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**：ブーストする軽量フレームワークです。

## 始め方

APM の素晴らしさをステップバイステップで紹介します：

### 1. APM を登録する

トラッキングを開始するために、`index.php` または `services.php` ファイルにこれを追加します：

```php
use flight\apm\logger\LoggerFactory;
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json');
$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);
```

**何が起こっているのか？**
- `LoggerFactory::create()` はあなたの設定を入手し（すぐに詳しく説明します）、ロガーを設定します—デフォルトで SQLite です。
- `Apm` はスターで、Flight のイベント（リクエスト、ルート、エラーなど）に耳を傾け、メトリクスを収集します。
- `bindEventsToFlightInstance($app)` はすべてを Flight アプリに結びつけます。

**プロチップ: サンプリング**
アプリが忙しい場合、*すべての* リクエストをログするとオーバーロードする可能性があります。 サンプル率（0.0 から 1.0）を使用します：

```php
$Apm = new Apm($ApmLogger, 0.1); // リクエストの 10% をログ
```

これでパフォーマンスを維持しつつ、しっかりしたデータが得られます。

### 2. 設定する

`.runway-config.json` を作成するためにこれを実行します：

```bash
php vendor/bin/runway apm:init
```

**これは何をするのか？**
- ウィザードを起動し、生のメトリクスのソースと処理されたデータの宛先を尋ねます。
- デフォルトは SQLite—例: `sqlite:/tmp/apm_metrics.sqlite` をソースに、もう一つを宛先に。
- 結果として次のような設定が得られます：
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

> このプロセスは、このセットアップのマイグレーションを実行するかどうかも尋ねます。 初めて設定する場合、答えは yes です。

**なぜ二つの場所が必要なのか？**
生のメトリクスは急速に蓄積します（フィルタリングされていないログを考えてください）。 ワーカーがそれを構造化された宛先で処理し、ダッシュボード用にします。 整理された状態を保ちます！

### 3. ワーカーでメトリクスを処理する

ワーカーは生のメトリクスをダッシュボード対応のデータに変換します。 一度実行します：

```bash
php vendor/bin/runway apm:worker
```

**これは何をしているのか？**
- ソース（例: `apm_metrics.sqlite`）から読み込みます。
- デフォルトのバッチサイズで最大 100 件のメトリクスを宛先で処理します。
- 完了するか、メトリクスがなくなると停止します。

**継続的に実行する**
ライブアプリの場合、継続的な処理が必要です。 オプションは次の通りです：

- **デーモンモード**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  ずっと実行し、メトリクスが来たら処理します。 開発や小規模セットアップに最適です。

- **Crontab**:
  Crontab に追加します（`crontab -e`）：
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  毎分実行—プロダクションに最適です。

- **Tmux/Screen**:
  分離可能なセッションを起動：
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, then D で分離; `tmux attach -t apm-worker` で再接続
  ```
  ログアウトしても実行を続けます。

- **カスタム調整**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: 一度に 50 件のメトリクスを処理。
  - `--max_messages 1000`: 1000 件のメトリクス後に停止。
  - `--timeout 300`: 5 分後に終了。

**なぜこれが必要なのか？**
ワーカーなしではダッシュボードは空です。 これは生のログと実用的な洞察の橋渡しです。

### 4. ダッシュボードを起動する

アプリのバイタルサインを表示：

```bash
php vendor/bin/runway apm:dashboard
```

**これは何？**
- `http://localhost:8001/apm/dashboard` で PHP サーバーを起動。
- リクエストログ、遅いルート、エラー率などを表示。

**カスタマイズ**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: 任意の IP からアクセス可能（リモート閲覧に便利）。
- `--port 8080`: 8001 が使用中なら別のポートを使用。
- `--php-path`: PATH にない場合、PHP を指定。

ブラウザで URL を開いて探検しましょう！

#### プロダクションモード

プロダクションでは、ファイアウォールや他のセキュリティ対策があるため、ダッシュボードを実行するためにいくつかの手法を試す必要があるかもしれません。 オプションは次の通りです：

- **リバースプロキシの使用**: Nginx または Apache を設定してリクエストをダッシュボードに転送。
- **SSH トンネル**: サーバーに SSH でアクセスできる場合、`ssh -L 8080:localhost:8001 youruser@yourserver` を使用してダッシュボードをローカルマシンにトンネル。
- **VPN**: サーバーが VPN の背後にあり、接続して直接ダッシュボードにアクセス。
- **ファイアウォールの設定**: ポート 8001 をあなたの IP またはサーバーのネットワークで開く（または設定したポート）。
- **Apache/Nginx の設定**: アプリケーションの前にウェブサーバーがある場合、ドメインまたはサブドメインに設定。 これを行う場合、文書ルートを `/path/to/your/project/vendor/flightphp/apm/dashboard` に設定。

#### 違うダッシュボードが欲しい？

独自のダッシュボードを作成できます！ データの表示方法のアイデアのために `vendor/flightphp/apm/src/apm/presenter` ディレクトリを見てください！

## ダッシュボードの機能

ダッシュボードはあなたの APM 本部です—ここに表示されるものを紹介します：

- **リクエストログ**: タイムスタンプ、URL、レスポンスコード、合計時間を持つすべてのリクエスト。 「詳細」をクリックしてミドルウェア、クエリ、エラーを表示。
- **最も遅いリクエスト**: 時間を消費するトップ 5 のリクエスト（例: 「/api/heavy」 at 2.5s）。
- **最も遅いルート**: 平均時間によるトップ 5 のルート—パターンの特定に最適。
- **エラー率**: 失敗するリクエストの割合（例: 2.3% 500s）。
- **レイテンシパーセンタイル**: 95th (p95) と 99th (p99) レスポンス時間—最悪ケースを知る。
- **レスポンスコードチャート**: 時間経過による 200s、404s、500s の視覚化。
- **長いクエリ/ミドルウェア**: トップ 5 の遅いデータベース呼び出しとミドルウェア層。
- **キャッシュヒット/ミス**: キャッシュがどれほど活躍するかを示す。

**追加機能**:
- 「直近 1 時間」「直近 1 日」「直近 1 週間」でフィルタ。
- 深夜セッションのためにダークモードを切り替え。

**例**:
`/users` へのリクエストは次のように表示される可能性があります：
- 合計時間: 150ms
- ミドルウェア: `AuthMiddleware->handle` (50ms)
- クエリ: `SELECT * FROM users` (80ms)
- キャッシュ: `user_list` のヒット (5ms)

## カスタムイベントの追加

API 呼び出しや支払いプロセスなどのものを追跡：

```php
use flight\apm\CustomEvent;

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**どこに表示されるのか？**
ダッシュボードのリクエスト詳細の下の「カスタムイベント」—展開可能できれいな JSON フォーマット。

**使用例**:
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
これでその API がアプリを遅らせているかどうかがわかります！

## データベース監視

PDO クエリをこのように追跡：

```php
use flight\database\PdoWrapper;

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite');
$Apm->addPdoConnection($pdo);
```

**得られるもの**:
- クエリテキスト（例: `SELECT * FROM users WHERE id = ?`）
- 実行時間（例: 0.015s）
- 行数（例: 42）

**注意点**:
- **オプション**: DB 追跡が必要ない場合はスキップ。
- **PdoWrapper のみ**: コア PDO はまだフックされていません—待機中！
- **パフォーマンス警告**: DB が重いサイトですべてのクエリをログすると遅くなる可能性があります。 サンプリング（`$Apm = new Apm($ApmLogger, 0.1)`）を使用して負荷を軽減。

**例の出力**:
- クエリ: `SELECT name FROM products WHERE price > 100`
- 時間: 0.023s
- 行: 15

## ワーカーのオプション

ワーカーを好みに合わせて調整：

- `--timeout 300`: 5 分後に停止—テストに良い。
- `--max_messages 500`: 500 件のメトリクスで上限。
- `--batch_size 200`: 一度に 200 件処理—速度とメモリのバランス。
- `--daemon`: 止まらず実行—ライブ監視に理想的。

**例**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
1 時間実行、一度に 100 件のメトリクスを処理。

## アプリ内のリクエスト ID

各リクエストには一意のリクエスト ID があり、追跡に使用できます。 ログとメトリクスを関連付けるために、エラーページにリクエスト ID を追加できます：

```php
Flight::map('error', function($message) {
	// Get the request ID from the response header X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// Additionally you could fetch it from the Flight variable
	// This method won't work well in swoole or other async platforms.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## アップグレード

APM の新しいバージョンにアップグレードする場合、データベースのマイグレーションが必要になる可能性があります。 次のコマンドを実行して：

```bash
php vendor/bin/runway apm:migrate
```
これはデータベーススキーマを最新バージョンに更新するための必要なマイグレーションを実行します。

**注意:** APM データベースが大きい場合、これらのマイグレーションには時間がかかる可能性があります。 オフピーク時に実行することを検討してください。

## 古いデータの消去

データベースを整理するために、古いデータを消去できます。 これは忙しいアプリを実行していて、データベースのサイズを管理したい場合に特に便利です。
次のコマンドを実行して：

```bash
php vendor/bin/runway apm:purge
```
これはデータベースから 30 日より古いすべてのデータを削除します。 `--days` オプションで日数を調整できます：

```bash
php vendor/bin/runway apm:purge --days 7
```
これはデータベースから 7 日より古いすべてのデータを削除します。

## トラブルシューティング

困った？ これを試してください：

- **ダッシュボードにデータがない？**
  - ワーカーが実行中ですか？ `ps aux | grep apm:worker` で確認。
  - 設定パスが一致しますか？ `.runway-config.json` の DSN が実際のファイルを示しているか確認。
  - `php vendor/bin/runway apm:worker` を手動で実行して保留中のメトリクスを処理。

- **ワーカーエラー？**
  - SQLite ファイルを覗いてみてください（例: `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`）。
  - PHP ログでスタックトレースを確認。

- **ダッシュボードが起動しない？**
  - ポート 8001 が使用中ですか？ `--port 8080` を使用。
  - PHP が見つからない？ `--php-path /usr/bin/php` を使用。
  - ファイアウォールがブロック？ ポートを開くか `--host localhost` を使用。

- **遅すぎる？**
  - サンプル率を下げる: `$Apm = new Apm($ApmLogger, 0.05)` (5%)。
  - バッチサイズを減らす: `--batch_size 20`。