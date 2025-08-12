# FlightPHP APM ドキュメント

FlightPHP APM へようこそ—あなたのアプリのパーソナルパフォーマンスコーチです！ このガイドは、Application Performance Monitoring (APM) を FlightPHP で設定し、使用し、マスターするためのロードマップです。 遅いリクエストを追いかけたり、レイテンシチャートに熱中したりするかどうか、私たちはカバーしています。 あなたのアプリをより速くし、ユーザーをより幸せにし、デバッグセッションを簡単にするために進みましょう！

![FlightPHP APM](/images/apm.png)

## APM の重要性

想像してみてください：あなたのアプリは忙しいレストランです。 注文にかかる時間を追跡する方法がない、またはキッチンが遅れている場所を特定できない場合、顧客が不満げに去る理由を推測するしかありません。 APM はあなたの副料理長のようなものです—着信リクエストからデータベースクエリまですべてのステップを監視し、遅延を引き起こすものをフラグします。 遅いページはユーザーを失います（研究によると、サイトの読み込みに 3 秒以上かかると 53% が離脱する！）、そして APM はそれらの問題を*事前に*キャッチします。 これは積極的な安心感です—「なぜこれが壊れているの？」という瞬間を少なくし、「これがどれほどスムーズに動くか！」という勝利を増やします。

## インストール

Composer で始めてください：

```bash
composer require flightphp/apm
```

必要なもの：
- **PHP 7.4+**：LTS Linux ディストリビューションとの互換性を保ちつつ、現代の PHP をサポートします。
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**：私たちがブーストしている軽量フレームワークです。

## 始め方

APM の素晴らしさをステップバイステップで：

### 1. APM を登録する

トラッキングを開始するために、`index.php` または `services.php` ファイルにこれを追加してください：

```php
use flight\apm\logger\LoggerFactory;
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json');
$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);
```

**何が起こっているのか？**
- `LoggerFactory::create()` はあなたの設定を入手し（すぐに詳しく）、ロガーを設定します—デフォルトで SQLite です。
- `Apm` はスターです—Flight のイベント（リクエスト、ルート、エラーなど）を聞き、指標を収集します。
- `bindEventsToFlightInstance($app)` はすべてをあなたの Flight アプリに結びつけます。

**プロチップ: サンプリング**
アプリが忙しい場合、*すべての*リクエストをログに記録するとオーバーロードする可能性があります。 サンプルレート（0.0 から 1.0）を使用してください：

```php
$Apm = new Apm($ApmLogger, 0.1); // 10% のリクエストをログに記録
```

これにより、パフォーマンスを維持しつつ、しっかりしたデータを取得します。

### 2. 設定する

`.runway-config.json` を作成するためにこれを実行してください：

```bash
php vendor/bin/runway apm:init
```

**これは何をするのか？**
- ソース（生の指標の出所）とデスティネーション（処理されたデータの行き先）を尋ねるウィザードを起動します。
- デフォルトは SQLite です—例: `sqlite:/tmp/apm_metrics.sqlite` をソース、`もう一つ` をデスティネーションに。
- 結果として、以下のような設定が得られます：
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

**なぜ 2 つの場所が必要か？**
生の指標は急速に蓄積されます（未フィルタのログを想像してください）。 ワーカーがそれを構造化されたデスティネーションに処理し、ダッシュボード用にします。 整理を保つためです！

### 3. ワーカーで指標を処理する

ワーカーは生の指標をダッシュボード対応データに変換します。 1 回実行してください：

```bash
php vendor/bin/runway apm:worker
```

**これは何をしているのか？**
- ソース（例: `apm_metrics.sqlite`）から読み込みます。
- デフォルトのバッチサイズで最大 100 件の指標をデスティネーションに処理します。
- 完了するか、指標がなくなると停止します。

**実行を継続する**
ライブアプリの場合、継続的な処理が必要です。 オプションは以下の通りです：

- **デーモンモード**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  指標が来るたびに永遠に実行します。 開発や小規模セットアップに最適です。

- **Crontab**:
  Crontab に追加してください（`crontab -e`）：
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  毎分実行—プロダクションに最適です。

- **Tmux/Screen**:
  分離可能なセッションを起動：
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, 続けて D で分離; `tmux attach -t apm-worker` で再接続
  ```
  ログアウトしても実行を続けます。

- **カスタム調整**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`：一度に 50 件の指標を処理。
  - `--max_messages 1000`：1000 件の指標後に停止。
  - `--timeout 300`：5 分後に終了。

**なぜこれが必要か？**
ワーカーなしでは、ダッシュボードは空です。 これは生のログと実用的な洞察の橋渡しです。

### 4. ダッシュボードを起動する

アプリのバイタルサインを表示：

```bash
php vendor/bin/runway apm:dashboard
```

**これは何？**
- `http://localhost:8001/apm/dashboard` で PHP サーバーを起動します。
- リクエストログ、遅いルート、エラーレートなどを表示します。

**カスタマイズ**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`：どの IP からもアクセス可能（リモート表示に便利）。
- `--port 8080`：8001 が使用中なら別のポートを使用。
- `--php-path`：PATH にない場合、PHP を指定。

ブラウザで URL を開いて探検してください！

#### プロダクションモード

プロダクションでは、ファイアウォールや他のセキュリティ対策のため、ダッシュボードを起動するためにいくつかの手法を試す必要があるかもしれません。 オプションは以下の通りです：

- **リバースプロキシの使用**：Nginx や Apache を設定してリクエストをダッシュボードに転送。
- **SSH トンネル**：サーバーに SSH で接続できる場合、`ssh -L 8080:localhost:8001 youruser@yourserver` を使用してダッシュボードをローカルマシンにトンネル。
- **VPN**：サーバーが VPN の背後にいる場合、接続してダッシュボードに直接アクセス。
- **ファイアウォールの設定**：ポート 8001 をあなたの IP またはサーバーのネットワークに対して開く（または設定したポート）。
- **Apache/Nginx の設定**：アプリケーションの前にウェブサーバーがある場合、ドメインまたはサブドメインに設定。 これを行う場合、ドキュメントルートを `/path/to/your/project/vendor/flightphp/apm/dashboard` に設定。

#### 違うダッシュボードが欲しい？

独自のダッシュボードを作成できます！ データの提示方法のアイデアのために `vendor/flightphp/apm/src/apm/presenter` ディレクトリを見てください！

## ダッシュボードの機能

ダッシュボードはあなたの APM 本部です—ここで何が見られるか：

- **リクエストログ**：タイムスタンプ、URL、レスポンスコード、合計時間を伴うすべてのリクエスト。 「詳細」をクリックしてミドルウェア、クエリ、エラーを表示。
- **最も遅いリクエスト**：時間を消費するトップ 5 リクエスト（例: 「/api/heavy」 で 2.5 秒）。
- **最も遅いルート**：平均時間によるトップ 5 ルート—パターンの特定に最適。
- **エラーレート**：失敗するリクエストの割合（例: 500 の 2.3%）。
- **レイテンシパーセンタイル**：95 番目 (p95) と 99 番目 (p99) のレスポンスタイム—最悪ケースを知る。
- **レスポンスコードチャート**：時間経過による 200、404、500 を視覚化。
- **長いクエリ/ミドルウェア**：トップ 5 の遅いデータベース呼び出しとミドルウェアレイヤー。
- **キャッシュヒット/ミス**：キャッシュが活躍する頻度。

**追加機能**:
- 「直近 1 時間」「直近 1 日」「直近 1 週間」でフィルタ。
- 深夜セッション用にダークモードを切り替え。

**例**:
`/users` へのリクエストは次のように表示される可能性があります：
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

**どこに表示されるか？**
ダッシュボードのリクエスト詳細の下の「カスタムイベント」に—JSON 形式で展開可能。

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
これで、その API がアプリを遅くしているかどうかがわかります！

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

**注意**:
- **オプション**：DB 追跡が必要ない場合、スキップ。
- **PdoWrapper のみ**：コア PDO はまだフックされていません—待機中！
- **パフォーマンス警告**：DB が重いサイトですべてのクエリをログに記録すると遅くなる可能性があります。 サンプリング（`$Apm = new Apm($ApmLogger, 0.1)`）を使用して負荷を軽減。

**例の出力**:
- クエリ: `SELECT name FROM products WHERE price > 100`
- 時間: 0.023s
- 行: 15

## ワーカーオプション

ワーカーを好みに調整：

- `--timeout 300`：5 分後に停止—テストに良い。
- `--max_messages 500`：500 件の指標でキャップ。
- `--batch_size 200`：一度に 200 件を処理—速度とメモリのバランス。
- `--daemon`：非停止で実行—ライブ監視に理想的。

**例**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
1 時間実行し、一度に 100 件の指標を処理。

## アプリ内のリクエスト ID

各リクエストには一意のリクエスト ID があり、追跡に使用できます。 例えば、エラーページにリクエスト ID を追加：

```php
Flight::map('error', function($message) {
	// レスポンスヘッダー X-Flight-Request-Id からリクエスト ID を取得する
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// また、Flight 変数から取得することも可能
	// これは Swoole や他の非同期プラットフォームではうまく動作しない。
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## アップグレード

APM の新しいバージョンにアップグレードする場合、データベースのマイグレーションが必要になる可能性があります。 以下のコマンドで実行：

```bash
php vendor/bin/runway apm:migrate
```
これはデータベーススキーマを最新バージョンに更新します。

**注意:** APM データベースが大きい場合、これらのマイグレーションには時間がかかる可能性があります。 オフピーク時に実行することを検討してください。

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
  - 設定パスが一致するか？ `.runway-config.json` の DSN が実際のファイルを示しているか確認。
  - `php vendor/bin/runway apm:worker` を手動で実行して保留中の指標を処理。

- **ワーカーエラー？**
  - SQLite ファイルを覗いてみてください（例: `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`）。
  - PHP ログでスタックトレースを確認。

- **ダッシュボードが起動しない？**
  - ポート 8001 が使用中か？ `--port 8080` を使用。
  - PHP が見つからない？ `--php-path /usr/bin/php` を使用。
  - ファイアウォールがブロック？ ポートを開くか `--host localhost` を使用。

- **遅すぎる？**
  - サンプルレートを下げる: `$Apm = new Apm($ApmLogger, 0.05)` (5%)。
  - バッチサイズを減らす: `--batch_size 20`。