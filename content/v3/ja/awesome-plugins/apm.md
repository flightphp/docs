# FlightPHP APM ドキュメント

FlightPHP APM へようこそ。あなたのアプリのパーソナルパフォーマンスコーチです！このガイドは、FlightPHP でアプリケーション・パフォーマンス監視（APM）をセットアップし、使いこなし、マスターするためのロードマップです。遅いリクエストを探している場合でも、レイテンシーチャートに夢中になりたいだけの場合でも、しっかりサポートします。アプリをより速く、ユーザーをより幸せに、デバッグ作業を楽々にしましょう！

Flight Docsサイトのダッシュボードの[デモ](https://flightphp-docs-apm.sky-9.com/apm/dashboard)をご覧ください。

![FlightPHP APM](/images/apm.png)

## APMが重要な理由

想像してみてください。あなたのアプリは忙しいレストランです。注文にどれだけ時間がかかっているかや、キッチンがどこで滞っているかを追跡する手段がなければ、なぜ客が不機嫌になって帰ってしまうのか推測するしかありません。APMはあなたのスーシェフ（副料理長）です。受信リクエストからデータベースクエリまで、すべてのステップを監視し、遅延の原因となるものを検出します。ページが遅いとユーザーは離れます（サイトの読み込みに3秒以上かかると53%が離脱するという調査もあります）。APMはそうした問題が痛手になる*前に*発見するのに役立ちます。これは先手を打つ安心感です。「なぜ壊れているの？」という瞬間が減り、「なんてスムーズに動くんだ！」という成功が増えます。

## インストール

Composerで始めましょう：

```bash
composer require flightphp/apm
```

必要なもの：
- **PHP 7.4+**：最新のPHPをサポートしつつ、LTS Linuxディストリビューションとの互換性を維持します。
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**：私たちが強化する軽量フレームワークです。

## 対応データベース

FlightPHP APMは現在、メトリクスを保存するために以下のデータベースをサポートしています：

- **SQLite3**：シンプルでファイルベース、ローカル開発や小規模アプリに最適です。ほとんどのセットアップでのデフォルトオプションです。
- **MySQL/MariaDB**：堅牢でスケーラブルなストレージが必要な大規模プロジェクトや本番環境に最適です。

設定手順（下記参照）でデータベースの種類を選択できます。PHP環境に必要な拡張機能（例：`pdo_sqlite` や `pdo_mysql`）がインストールされていることを確認してください。

## はじめに

APMを素晴らしく使うためのステップバイステップガイドです：

### 1. APMを登録する

追跡を開始するには、これを`index.php`または`services.php`ファイルに追加してください：

```php
use flight\apm\logger\LoggerFactory;
use flight\database\SimplePdo;
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json');
$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);

// データベース接続を追加する場合
// 開発時は SimplePdo（またはTracy ExtensionsのPdoQueryCapture）を推奨します。
// オプション配列（第5引数）でAPMクエリ追跡を有効にします。
$pdo = new SimplePdo('mysql:host=localhost;dbname=example', 'user', 'pass', null, [
	'trackApmQueries' => true, // APMでクエリを捕捉するために必須
]);
$Apm->addPdoConnection($pdo);
```

**ここで何が起きているのか？**
- `LoggerFactory::create()`は設定を取得し（詳細は後ほど）、ロガーをセットアップします。デフォルトはSQLiteです。
- `Apm`が主役です。Flightのイベント（リクエスト、ルート、エラーなど）をリッスンし、メトリクスを収集します。
- `bindEventsToFlightInstance($app)`はそれをすべてFlightアプリに結び付けます。

**プロのヒント：サンプリング**

アプリが忙しい場合、*すべての*リクエストをログに記録すると過負荷になる可能性があります。サンプルレート（0.0〜1.0）を使用してください：

```php
$Apm = new Apm($ApmLogger, 0.1); // リクエストの10%をログに記録
```

これにより、確かなデータを得ながら、パフォーマンスを軽快に保てます。

### 2. 設定する

`.runway-config.json`を作成するには、次を実行します：

```bash
php vendor/bin/runway apm:init
```

**これは何をするのか？**
- 生のメトリクスの取得元（ソース）と処理済みデータの送信先（デスティネーション）を尋ねるウィザードを起動します。
- デフォルトはSQLiteです。たとえば、ソースは`sqlite:/tmp/apm_metrics.sqlite`、デスティネーションは別のSQLiteファイルです。
- 次のような設定が生成されます：

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

> このプロセスでは、このセットアップのマイグレーションを実行するかどうかも尋ねられます。初めてセットアップする場合は「はい」と答えてください。

**なぜ2つの場所が必要なのか？**

生のメトリクスは急速に蓄積されます（フィルタリングされていないログを想像してください）。ワーカーがそれらを処理して、ダッシュボード用の構造化されたデスティネーションに変換します。これで整理が保たれます！

### 3. ワーカーでメトリクスを処理する

ワーカーは生のメトリクスをダッシュボードで利用可能なデータに変換します。一度実行します：

```bash
php vendor/bin/runway apm:worker
```

**これは何をするのか？**
- ソース（例：`apm_metrics.sqlite`）から読み取ります。
- 最大100件のメトリクス（デフォルトのバッチサイズ）をデスティネーションに処理します。
- 処理が完了するか、メトリクスが残っていない場合に停止します。

**実行を継続する**

ライブアプリでは、継続的な処理が必要です。選択肢は以下の通りです：

- **デーモンモード**：
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  永久に実行され、メトリクスが来るたびに処理します。開発環境や小規模セットアップに最適です。

- **crontab**：
  これをcrontab（`crontab -e`）に追加します：
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  毎分実行されます。本番環境に最適です。

- **tmux/screen**：
  デタッチ可能なセッションを開始します：
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B、次にDでデタッチ、`tmux attach -t apm-worker`で再接続
  ```
  ログアウトしても実行が継続されます。

- **カスタム調整**：
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`：一度に50件のメトリクスを処理します。
  - `--max_messages 1000`：1000件のメトリクスを処理したら停止します。
  - `--timeout 300`：5分後に終了します。

**なぜこれをするのか？**

ワーカーがないと、ダッシュボードは空のままです。生のログと実用的な洞察をつなぐ橋です。

### 4. ダッシュボードを起動する

アプリの重要指標を確認します：

```bash
php vendor/bin/runway apm:dashboard
```

**これは何か？**
- `http://localhost:8001/apm/dashboard` でPHPサーバーを起動します。
- リクエストログ、低速ルート、エラーレートなどを表示します。

**カスタマイズ**：
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`：任意のIPからアクセス可能（リモート閲覧に便利）。
- `--port 8080`：8001が使用中の場合は別のポートを使用。
- `--php-path`：PHPがPATHにない場合は、PHPの場所を指定します。

ブラウザでURLにアクセスして探索してください！

#### 本番モード

本番環境では、ファイアウォールやその他のセキュリティ対策がおそらくあるため、ダッシュボードを稼働させるためにいくつかのテクニックを試す必要があるかもしれません。いくつかのオプションを紹介します：

- **リバースプロキシを使用する**：NginxやApacheを設定して、ダッシュボードにリクエストを転送します。
- **SSHトンネル**：サーバーにSSH接続できる場合は、`ssh -L 8080:localhost:8001
youruser@yourserver` を使用してダッシュボードをローカルマシンにトンネリングします。
- **VPN**：サーバーがVPNの背後にある場合は、VPNに接続してダッシュボードに直接アクセスします。
- **ファイアウォールを設定する**：ご自身のIPまたはサーバーのネットワークに対してポート8001を開きます（設定したポートがあればそのポート）。
- **Apache/Nginxを設定する**：アプリケーションの前にWebサーバーがある場合は、ドメインまたはサブドメインに設定できます。そのホストを`dashboard/index.php`に向けます（組み込みサーバーはこれをルーターとして使用します）。静的ファイルは同じディレクトリの`dashboard/css`と`dashboard/js`にあります。
- **ベーシック認証**：runway設定で`apm.dashboard_auth.user`と`apm.dashboard_auth.password`を設定します。どちらかの値が空の場合、ダッシュボードはオープンに保たれます。これはアプリケーションのユーザーアカウントではなく、単一の共有パスワードです。

#### 別のダッシュボードが必要ですか？

必要であれば、独自のダッシュボードを構築できます！独自のダッシュボード用にデータを提示する方法のアイデアについては、vendor/flightphp/apm/src/apm/presenterディレクトリを参照してください！

## ダッシュボードの機能

ダッシュボードはまず数値、次にチャート、最後にリクエストログを表示します。

- **統計行**：リクエスト数、エラーレート、p95、p99、平均レイテンシー、毎分のスループット、キャッシュヒット率。
- **ワーカーバックログ**：`apm_metrics_log`にまだ残っている生のメトリクスの数。数が多い場合はワーカーが遅れています。バックログが0でダッシュボードが空の場合は、何も収集されていないことを意味します。
- **レイテンシーチャート**：同じバケットの平均レイテンシーとリクエスト数。過去1時間は5分バケット、過去1日は15分バケット、過去1週間は6時間バケットを使用します。カスタム範囲では約40ポイントを保つバケットが選択されます。時刻はUTCで保存され、選択したタイムゾーンで描画されます。
- **ステータスチャート**：`2xx`、`3xx`、`4xx`、`5xx`の数を積み上げて表示します。系列をクリックすると、そのクラスにログをフィルタリングできます。
- **低速ルート、ミドルウェア、クエリ、リクエスト**：それぞれ5件表示されます。クエリはグループ化され、`WHERE id = 1`と`WHERE id = 2`は1つのステートメントとしてカウントされます。ルート、低速リクエスト、エラーグループをクリックすると、ログをフィルタリングできます。
- **カスタムイベント**：アプリが発生させるすべてのカスタムイベントタイプについて、その数、最後に確認された時刻、選択した期間の小さなトレンドラインを表示します。数で並べ替えられます。時間範囲、「ボットを隠す」、その他のフィルターが適用されます。タイプをクリックすると、そのタイプにログをフィルタリングできます。
- **リクエストログ**：メソッド、URL、ステータス、時間、メモリ、ホスト、ボットフラグ。行を開くと詳細が読み込まれ、タイミングバー、ビュー、クエリパラメータ、エラートレース、カスタムイベントが含まれます。
- **ボットを隠す**：集計とログからボットトラフィックを除外します。検索クローラーとLLMクローラー（GPTBot、ClaudeBot、PerplexityBot、SearxNGなど）はボットとしてマークされます。
- **カスタム範囲**：過去1時間、1日、1週間に加えて、開始/終了の期間を選択できます。
- **フィルター**：URL、ステータス、リクエストID、ルート、IP、ホスト、セッション、ユーザーエージェント、カスタムイベントタイプ、イベントデータ、エラーメッセージ。クエリ文字列は、リフレッシュ後もこれらを維持します。

**例**：`/users`へのリクエストでは次のように表示される場合があります：
- 合計時間：150ms
- ミドルウェア：`AuthMiddleware->handle`（50ms）
- クエリ：`SELECT * FROM users`（80ms）
- ビューレンダリング時間
- キャッシュ：`user_list`にヒット（5ms）

**例**：`/users`へのリクエストでは次のように表示される場合があります：
- 合計時間：150ms
- ミドルウェア：`AuthMiddleware->handle`（50ms）
- クエリ：`SELECT * FROM users`（80ms）
- キャッシュ：`user_list`にヒット（5ms）

## カスタムイベントの追加

API呼び出しや支払い処理など、あらゆるものを追跡します：

```php
use flight\apm\CustomEvent;

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**どこに表示されるのか？**

ダッシュボードのリクエスト詳細の「Custom Events」の下に表示され、きれいなJSON形式で展開できます。**Custom events**パネルでは各タイプもカウントされるので、簡単な`docs.markdown_served`イベントだけで「これはどのくらいの頻度で発生するのか？」という質問に答えられます。

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

これで、そのAPIがアプリを遅くしているかどうかが分かります！

## データベースモニタリング

PDOクエリを次のように追跡します：

```php
use flight\database\SimplePdo;

$pdo = new SimplePdo('sqlite:/path/to/db.sqlite', null, null, null, [
	'trackApmQueries' => true, // APMでクエリを捕捉するために必須
]);
$Apm->addPdoConnection($pdo);
```

**得られるもの**：
- クエリテキスト（例：`SELECT * FROM users WHERE id = ?`）
- 実行時間（例：0.015秒）
- 行数（例：42）

**注意点**：
- **任意**：DB追