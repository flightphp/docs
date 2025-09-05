# FlightPHP APM ドキュメント

FlightPHP APM にようこそ—あなたのアプリのパーソナルパフォーマンスコーチです！ このガイドは、Application Performance Monitoring (APM) を FlightPHP で設定し、使用し、習得するためのロードマップです。 遅いリクエストを追いかける場合でも、レイテンシチャートに熱中したい場合でも、私たちがカバーします。 アプリをより速くし、ユーザーをより幸せにし、デバッグセッションを簡単にするために進みましょう！

![FlightPHP APM](/images/apm.png)

## APM の重要性

想像してみてください：あなたのアプリは忙しいレストランです。 オーダーにどれだけ時間がかかるかを追跡する方法がない、またはキッチンがどこで詰まっているのかわからない場合、顧客が不機嫌になって去る理由を推測するだけです。 APM はあなたの副料理長のようなものです—着信リクエストからデータベースクエリまですべてのステップを監視し、遅延を引き起こすものをフラグします。 遅いページはユーザーを失います（研究によると、サイトの読み込みに 3 秒以上かかると 53% が離脱します！）、そして APM はそれらの問題を *事前に* キャッチします。 これは積極的な安心—「なぜこれは壊れているの？」という瞬間を少なくし、「これがどれほどスムーズに動くか！」という勝利を増やします。

## インストール

Composer で始めましょう：

```bash
composer require flightphp/apm
```

必要なもの：
- **PHP 7.4+**: LTS Linux ディストリビューションとの互換性を保ちつつ、現代的な PHP をサポートします。
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: 私たちがブーストする軽量フレームワークです。

## サポートされているデータベース

FlightPHP APM は、現在、以下のデータベースをメトリクスの保存にサポートしています：

- **SQLite3**: シンプルでファイルベースのもの、または小規模アプリのローカル開発に最適。 ほとんどのセットアップでデフォルトのオプションです。
- **MySQL/MariaDB**: 大規模プロジェクトやプロダクション環境で堅牢でスケーラブルなストレージが必要な場合に理想的です。

構成ステップ（以下を参照）でデータベースの種類を選択できます。 PHP 環境に必要な拡張（例: `pdo_sqlite` または `pdo_mysql`）をインストールされていることを確認してください。

## 始め方

APM の素晴らしさをステップバイステップで：

### 1. APM を登録する

`index.php` または `services.php` ファイルにこれを追加して追跡を開始します：

```php
use flight\apm\logger\LoggerFactory;
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json');
$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);

// もしデータベース接続を追加する場合
// これは Tracy Extensions からの PdoWrapper または PdoQueryCapture でなければなりません
$pdo = new PdoWrapper('mysql:host=localhost;dbname=example', 'user', 'pass', null, true); // <-- True を指定して APM での追跡を有効にします。
$Apm->addPdoConnection($pdo);
```

**ここで何が起こっているのか？**
- `LoggerFactory::create()` はあなたの構成を入手し（すぐに詳しく）、ロガーをセットアップします—デフォルトで SQLite。
- `Apm` はスター—it は Flight のイベント（リクエスト、ルート、エラーなど）を監視し、メトリクスを収集します。
- `bindEventsToFlightInstance($app)` はすべてを Flight アプリに結びつけます。

**プロチップ: サンプリング**
アプリが忙しい場合、*すべての* リクエストをログに残すとオーバーロードになる可能性があります。 サンプル率（0.0 から 1.0）を使用します：

```php
$Apm = new Apm($ApmLogger, 0.1); // 10% のリクエストをログに残します
```

これでパフォーマンスを維持しつつ、しっかりしたデータを取得できます。

### 2. それを構成する

`.runway-config.json` を作成するためにこれを実行します：

```bash
php vendor/bin/runway apm:init
```

**これは何をするのか？**
- 生のメトリクスのソースと処理されたデータの宛先を尋ねるウィザードを起動します。
- デフォルトは SQLite—例: `sqlite:/tmp/apm_metrics.sqlite` がソース、もう一つが宛先。
- 以下のような構成ができます：
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

> このプロセスは、このセットアップの移行を実行するかどうかを尋ねます。 初めて設定する場合、答えは yes です。

**なぜ 2 つの場所が必要なのか？**
生のメトリクスは急速に積み上がります（フィルタリングされていないログを考えて）。 ワーカーがそれを構造化された宛先でダッシュボード用に処理します。 すべてを整頓します！

### 3. ワーカーでメトリクスを処理する

ワーカーは生のメトリクスをダッシュボード対応のデータに変換します。 1 度実行します：

```bash
php vendor/bin/runway apm:worker
```

**これは何をしているのか？**
- ソース（例: `apm_metrics.sqlite`）から読み込みます。
- 最大 100 メトリクス（デフォルトのバッチサイズ）を宛先に処理します。
- 完了するか、メトリクスが残っていない場合に停止します。

**それを継続的に実行する**
ライブアプリの場合、継続的な処理を望むでしょう。 オプションは：

- **デーモンモード**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  常に実行し、到着したメトリクスを処理します。 開発や小規模セットアップに最適。

- **Crontab**:
  あなたの crontab にこれを追加（`crontab -e`）：
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  毎分実行—プロダクションに最適。

- **Tmux/Screen**:
  分離可能なセッションを開始：
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, then D で分離; `tmux attach -t apm-worker` で再接続
  ```
  ログアウトしても実行を継続。

- **カスタム調整**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: 50 メトリクスを一度に処理。
  - `--max_messages 1000`: 1000 メトリクス後に停止。
  - `--timeout 300`: 5 分後に終了。

**なぜそれが必要なのか？**
ワーカーがないと、ダッシュボードは空です。 これは生のログと実用的な洞察の間の架け橋です。

### 4. ダッシュボードを起動する

アプリのバイタルサインを表示：

```bash
php vendor/bin/runway apm:dashboard
```

**これは何？**
- `http://localhost:8001/apm/dashboard` で PHP サーバーを起動。
- リクエストログ、遅いルート、エラーレートなどを表示。

**それをカスタマイズ**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: 任意の IP からアクセス可能（リモート表示に便利）。
- `--port 8080`: 8001 が使用中の場合、異なるポートを使用。
- `--php-path`: PATH にない場合、PHP を指す。

ブラウザで URL を開いて探検！

#### プロダクションモード

プロダクションでは、ファイアウォールや他のセキュリティ対策があるため、ダッシュボードを実行するためにいくつかの手法を試す必要があるかもしれません。 オプションは：

- **リバースプロキシを使用**: Nginx または Apache を設定してリクエストをダッシュボードに転送。
- **SSH トンネル**: サーバーに SSH でアクセスできる場合、`ssh -L 8080:localhost:8001 youruser@yourserver` を使用してダッシュボードをローカルマシンにトンネル。
- **VPN**: サーバーが VPN の背後にあり、接続して直接ダッシュボードにアクセス。
- **ファイアウォールを構成**: ポート 8001 をあなたの IP またはサーバーのネットワーク用に開く（または設定したポート）。
- **Apache/Nginx を構成**: アプリケーションの前にウェブサーバーがある場合、ドメインまたはサブドメインに構成。 これを行う場合、文書ルートを `/path/to/your/project/vendor/flightphp/apm/dashboard` に設定。

#### 異なるダッシュボードを望む？

独自のダッシュボードを作成できます！ vendor/flightphp/apm/src/apm/presenter ディレクトリを調べて、データを表示するためのアイデアを得てください！

## ダッシュボードの機能

ダッシュボードはあなたの APM HQ—以下が見えます：

- **リクエストログ**: タイムスタンプ、URL、レスポンスコード、合計時間を持つすべてのリクエスト。 「詳細」をクリックしてミドルウェア、クエリ、エラーを表示。
- **最も遅いリクエスト**: 時間を消費するトップ 5 リクエスト（例: 「/api/heavy」 at 2.5s）。
- **最も遅いルート**: 平均時間によるトップ 5 ルート—パターンを特定するのに最適。
- **エラーレート**: 失敗するリクエストの割合（例: 2.3% 500s）。
- **レイテンシパーセンタイル**: 95th (p95) と 99th (p99) レスポンス時間—最悪のシナリオを知る。
- **レスポンスコードチャート**: 時間経過による 200s、404s、500s の視覚化。
- **長いクエリ/ミドルウェア**: トップ 5 の遅いデータベース呼び出しとミドルウェアレイヤー。
- **キャッシュヒット/ミス**: キャッシュが活躍する頻度。

**エクストラ**:
- 「直近 1 時間」「直近 1 日」「直近 1 週間」でフィルタリング。
- 深夜セッション用のダークモードを切り替え。

**例**:
`/users` へのリクエストは次のように表示：
- 合計時間: 150ms
- ミドルウェア: `AuthMiddleware->handle` (50ms)
- クエリ: `SELECT * FROM users` (80ms)
- キャッシュ: `user_list` のヒット (5ms)

## カスタムイベントの追加

API 呼び出しや支払いプロセスなど、任意のものを追跡：

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
これでその API がアプリを引き下げるかどうかを確認できます！

## データベース監視

PDO クエリをこのように追跡：

```php
use flight\database\PdoWrapper;

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite', null, null, null, true); // <-- True を指定して APM での追跡を有効にします。
$Apm->addPdoConnection($pdo);
```

**何を得るのか**:
- クエリテキスト（例: `SELECT * FROM users WHERE id = ?`）
- 実行時間（例: 0.015s）
- 行数（例: 42）

**注意**:
- **オプション**: DB 追跡が必要ない場合、スキップ。
- **PdoWrapper のみ**: コア PDO はまだフックされていません—待機中！
- **パフォーマンス警告**: DB が重いサイトですべてのクエリをログに残すと遅くなる可能性があります。 サンプリング（`$Apm = new Apm($ApmLogger, 0.1)`）を使用して負荷を軽減。

**例の出力**:
- クエリ: `SELECT name FROM products WHERE price > 100`
- 時間: 0.023s
- 行: 15

## ワーカーオプション

ワーカーを好みに調整：

- `--timeout 300`: 5 分後に停止—テストに良い。
- `--max_messages 500`: 500 メトリクスでキャップ。
- `--batch_size 200`: 200 を一度に処理—速度とメモリのバランス。
- `--daemon`: 止まらず実行—ライブ監視に理想的。

**例**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
1 時間実行し、100 メトリクスを一度に処理。

## アプリ内のリクエスト ID

各リクエストに一意のリクエスト ID が付与され、ログとメトリクスの相関に使用できます。 例: エラーページにリクエスト ID を追加：

```php
Flight::map('error', function($message) {
	// レスポンスヘッダー X-Flight-Request-Id からリクエスト ID を取得
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// また、Flight 変数から取得することもできます
	// この方法は swoole や他の非同期プラットフォームではうまく動作しない可能性があります。
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## アップグレード

APM の新しいバージョンにアップグレードする場合、データベースの移行を実行する必要がある可能性があります。 以下のコマンドで実行：

```bash
php vendor/bin/runway apm:migrate
```
これはデータベーススキーマを最新バージョンに更新するための必要な移行を実行します。

**注記:** APM データベースが大きい場合、これらの移行には時間がかかる可能性があります。 オフピーク時に実行することを検討してください。

## 古いデータの消去

データベースを整頓するために、古いデータを消去できます。 これは忙しいアプリを実行していて、データベースのサイズを管理したい場合に特に便利です。
以下のコマンドで実行：

```bash
php vendor/bin/runway apm:purge
```
これはデータベースから 30 日より古いすべてのデータを削除します。 `--days` オプションで日数を調整：

```bash
php vendor/bin/runway apm:purge --days 7
```
これは 7 日より古いすべてのデータを削除します。

## トラブルシューティング

困った？ これを試してください：

- **ダッシュボードにデータがない？**
  - ワーカーが実行中か？ `ps aux | grep apm:worker` で確認。
  - 構成パスが一致するか？ `.runway-config.json` の DSN が実際のファイルを示しているか確認。
  - `php vendor/bin/runway apm:worker` を手動で実行して保留中のメトリクスを処理。

- **ワーカーエラー？**
  - SQLite ファイルを覗く（例: `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`）。
  - PHP ログでスタックトレースを確認。

- **ダッシュボードが起動しない？**
  - ポート 8001 が使用中か？ `--port 8080` を使用。
  - PHP が見つからない？ `--php-path /usr/bin/php` を使用。
  - ファイアウォールがブロック？ ポートを開くか `--host localhost` を使用。

- **遅すぎる？**
  - サンプル率を下げる: `$Apm = new Apm($ApmLogger, 0.05)` (5%)。
  - バッチサイズを減らす: `--batch_size 20`。

- **例外/エラーを追跡していない？**
  - [Tracy](https://tracy.nette.org/) がプロジェクトで有効になっている場合、Flight のエラー処理をオーバーライドします。 Tracy を無効にし、`Flight::set('flight.handle_errors', true);` を設定。

- **データベースクエリを追跡していない？**
  - `PdoWrapper` をデータベース接続で使用していることを確認。
  - コンストラクタの最後の引数を `true` にしていることを確認。