---
# FlightPHP APM ドキュメント

FlightPHP APM へようこそ—あなたのアプリのパーソナルパフォーマンスコーチ！このガイドは、FlightPHP でのアプリケーションパフォーマンスモニタリング (APM) の設定、使用、および習得のためのロードマップです。遅いリクエストを追いかけたい場合でも、単にレイテンシチャートに夢中になりたい場合でも、ご安心ください。あなたのアプリを速くし、ユーザーを幸せにし、デバッグセッションを簡単にしましょう！

## なぜ APM が重要か

これを想像してみてください：あなたのアプリは忙しいレストランです。注文がどれくらい時間がかかるか、キッチンがどこで詰まっているかを追跡する方法がなければ、顧客がイライラしている理由を推測することになります。APM はあなたの副料理長です—受信リクエストからデータベースクエリまで、すべてのステップを監視し、あなたを遅くしているものをフラグ付けします。遅いページはユーザーを失います（調査によれば、サイトの読み込みに 3 秒以上かかると 53% のユーザーが離脱します！）、そして APM はそれらの問題を痛む前にキャッチします。この仕組みは、あらかじめ安心感をもたらします—「なぜこれが壊れているのか？」という瞬間を減らし、「これがどれだけスムーズに動くか！」という勝利を増やします。

## インストール

Composer で始めましょう：

```bash
composer require flightphp/apm
```

必要なもの：
- **PHP 7.4+**: 最新の PHP をサポートしつつ、LTS Linux ディストリビューションとの互換性を保ちます。
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: 軽量フレームワークです。

## 始め方

APM の素晴らしさへのステップバイステップはこちら：

### 1. APM を登録する

これを `index.php` または `services.php` に入れてトラッキングを開始します：

```php
use flight\apm\logger\LoggerFactory;
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json');
$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);
```

**ここで何が起こっているのですか？**
- `LoggerFactory::create()` はあなたの設定を取得し（それについては後で詳しく）、ロガーを設定します—デフォルトは SQLite です。
- `Apm` は主役で、Flight のイベント（リクエスト、ルート、エラーなど）をリッスンし、メトリクスを収集します。
- `bindEventsToFlightInstance($app)` はそれをあなたの Flight アプリに結び付けます。

**プロのヒント：サンプリング**
アプリが忙しい場合、*すべての*リクエストをロギングすることは負荷をかけるかもしれません。サンプルレート（0.0 から 1.0）を使用します：

```php
$Apm = new Apm($ApmLogger, 0.1); // 10% のリクエストをログ
```

これにより、パフォーマンスをスナッピーに保ちながら、しっかりとしたデータを得ることができます。

### 2. 設定を行う

これを実行して `.runway-config.json` を作成します：

```bash
php vendor/bin/runway apm:init
```

**これは何をしますか？**
- 生のメトリクスがどこから来るか（ソース）と、処理されたデータがどこに行くか（宛先）を尋ねるウィザードが起動します。
- デフォルトは SQLite です—例えば、ソースには `sqlite:/tmp/apm_metrics.sqlite`、宛先には別のものを使用します。
- 結果、次のような設定が生成されます：
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

**なぜ 2 つの場所？**
生のメトリクスはすぐに蓄積されます（フィルタリングされていないログを考えてください）。ワーカーはそれらをダッシュボードのための構造化された宛先に処理します。すっきりと保つためです！

### 3. ワーカーでメトリクスを処理する

ワーカーは生のメトリクスをダッシュボード用のデータに変換します。これを一度実行します：

```bash
php vendor/bin/runway apm:worker
```

**これは何をしていますか？**
- あなたのソース（例： `apm_metrics.sqlite`）から読み取ります。
- あなたの宛先に最大 100 メトリクス（デフォルトのバッチサイズ）を処理します。
- 処理が完了するか、メトリクスが残っていない場合に停止します。

**常に実行する**
ライブアプリの場合、継続的な処理が望ましいです。こちらがあなたの選択肢です：

- **デーモンモード**：
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  常に実行し、到着するメトリクスを処理します。開発や小規模セットアップに最適です。

- **クロンタブ**：
  あなたのクロンタブにこれを追加します（`crontab -e`）：
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  毎分実行されます—本番用に最適です。

- **Tmux/Screen**：
  デタッチ可能なセッションを開始します：
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B、次に D でデタッチ；`tmux attach -t apm-worker` で再接続
  ```
  ログアウトしても実行され続けます。

- **カスタム調整**：
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: 一度に 50 メトリクスを処理します。
  - `--max_messages 1000`: 1000 メトリクス処理後に停止します。
  - `--timeout 300`: 5 分後に終了します。

**なぜ気にする必要がありますか？**
ワーカーがなければ、あなたのダッシュボードは空っぽです。生のログとアクション可能なインサイトの橋渡しです。

### 4. ダッシュボードを起動する

アプリの重要データを確認します：

```bash
php vendor/bin/runway apm:dashboard
```

**これは何ですか？**
- `http://localhost:8001/apm/dashboard` で PHP サーバーを起動します。
- リクエストログ、遅いルート、エラーレートなどを表示します。

**カスタマイズする**：
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: どの IP からもアクセス可能（リモートビューイングに便利）。
- `--port 8080`: 8001 が使用中であれば別のポートを使用します。
- `--php-path`: PHP が PATH にない場合は指し示してください。

ブラウザで URL にアクセスし、探検してください！

#### 生産モード

本番では、ダッシュボードを実行するためにいくつかの技術を試す必要があるかもしれません。ファイアウォールやその他のセキュリティ対策が存在する可能性があります。こちらがいくつかのオプションです：

- **リバースプロキシを使用する**： Nginx または Apache を設定してリクエストをダッシュボードに転送します。
- **SSH トンネル**： サーバーに SSH できる場合、`ssh -L 8080:localhost:8001 youruser@yourserver` を使用してダッシュボードをローカルマシンにトンネルします。
- **VPN**： サーバーが VPN の背後にある場合、それに接続してダッシュボードに直接アクセスします。
- **ファイアウォールを設定する**： あなたの IP かサーバーのネットワークのためにポート 8001 を開放します。（または設定したポート）。
- **Apache/Nginx を設定する**： アプリケーションの前にウェブサーバーがある場合、それをドメインまたはサブドメインに設定できます。この場合、ドキュメントルートを `/path/to/your/project/vendor/flightphp/apm/dashboard` に設定します。

#### 別のダッシュボードが必要ですか？

ご自身のダッシュボードを構築したい場合は、vendor/flightphp/apm/src/apm/presenter ディレクトリを参照して、自分のダッシュボードのデータを提示する方法のアイデアを得てください！

## ダッシュボードの特徴

ダッシュボードはあなたの APM HQ です—ここで見ることができます：

- **リクエストログ**： タイムスタンプ、URL、レスポンスコード、合計時間とともにすべてのリクエスト。クリックして「詳細」を表示すれば、ミドルウェア、クエリ、エラーを確認できます。
- **最も遅いリクエスト**： 時間を浪費しているトップ 5 のリクエスト（例： “/api/heavy” で 2.5s）。
- **最も遅いルート**： 平均時間別のトップ 5 ルート—パターンを見つけるのに便利です。
- **エラーレート**： 失敗しているリクエストの割合（例： 2.3% の 500s）。
- **レイテンシパーセンタイル**： 95 番目（p95）および 99 番目（p99）レスポンスタイム—最悪のシナリオを把握しましょう。
- **レスポンスコードチャート**： 時間の経過に伴う 200s、404s、500s を視覚化します。
- **長いクエリ/ミドルウェア**： 最も遅いデータベース呼び出しとミドルウェアレイヤーのトップ 5。
- **キャッシュヒット/ミス**： キャッシュがどれだけ良い仕事をしているか。

**追加情報**：
- 「最終時間」、「最終日」または「最終週」でフィルタリングします。
- 夜更かしセッション用にダークモードを切り替えます。

**例**：
`/users` へのリクエストは次のように表示されるかもしれません：
- 合計時間：150ms
- ミドルウェア： `AuthMiddleware->handle`（50ms）
- クエリ： `SELECT * FROM users`（80ms）
- キャッシュ： `user_list` でヒット（5ms）

## カスタムイベントの追加

API 呼び出しや支払いプロセスのように、何でもトラッキングします：

```php
use flight\apm\CustomEvent;

$app->eventDispatcher()->emit('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**どこで表示されますか？**
ダッシュボードのリクエスト詳細の「カスタムイベント」に表示されます—きれいな JSON 形式で展開可能です。

**使用例**：
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->emit('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
これで、その API があなたのアプリを引きずり下ろしているかどうかを見ることができます！

## データベースモニタリング

PDO クエリを次のようにトラッキングします：

```php
use flight\database\PdoWrapper;

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite');
$Apm->addPdoConnection($pdo);
```

**得られるもの**：
- クエリテキスト（例： `SELECT * FROM users WHERE id = ?`）
- 実行時間（例： 0.015s）
- 行数（例： 42）

**ご注意**：
- **オプション**： DB トラッキングが必要ない場合はスキップできます。
- **PdoWrapper のみ**： コア PDO はまだフックされていません—続報をお待ちください！
- **パフォーマンス警告**： DB に負荷がかかるサイトでのクエリをすべてロギングすると、処理が遅くなる可能性があります。サンプリング（`$Apm = new Apm($ApmLogger, 0.1)`）を使用して負担を軽減してください。

**例の出力**：
- クエリ： `SELECT name FROM products WHERE price > 100`
- 時間： 0.023s
- 行： 15

## ワーカーオプション

ワーカーを好みに調整します：

- `--timeout 300`: 5 分後に停止—テストに適しています。
- `--max_messages 500`: 500 メトリクスで上限—範囲を制限します。
- `--batch_size 200`: 一度に 200 件処理—速度とメモリのバランスを取ります。
- `--daemon`: 非停止で実行—ライブモニタリングに最適です。

**例**：
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
1 時間実行し、1 回に 100 メトリクスを処理します。

## トラブルシューティング

詰まったら？以下を試してください：

- **ダッシュボードデータがない場合は？**
  - ワーカーは実行中ですか？ `ps aux | grep apm:worker` を確認します。
  - 設定パスが一致していますか？ `.runway-config.json` の DSN が実際のファイルを指していることを確認します。
  - 保留中のメトリクスを処理するために `php vendor/bin/runway apm:worker` を手動で実行します。

- **ワーカーエラー？**
  - SQLite ファイルを覗いてみてください（例： `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`）。
  - スタックトレースのために PHP ログをチェックします。

- **ダッシュボードが起動しない場合は？**
  - ポート 8001 が使用中ですか？ `--port 8080` を使用します。
  - PHP が見つかりませんか？ `--php-path /usr/bin/php` を使用します。
  - ファイアウォールがブロックしていますか？ ポートを開放するか、`--host localhost` を使用します。

- **遅すぎる？**
  - サンプリングレートを下げます： `$Apm = new Apm($ApmLogger, 0.05)`（5%）。
  - バッチサイズを減少させます： `--batch_size 20`。