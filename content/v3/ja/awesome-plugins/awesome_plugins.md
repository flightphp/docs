# 素晴らしいプラグイン

Flightは非常に拡張性があります。Flightアプリケーションに機能を追加するために使用できるプラグインがいくつかあります。中にはFlightチームが公式にサポートしているものもあり、その他は始めるためのマイクロ/ライトライブラリです。

## APIドキュメント

APIドキュメントは、すべてのAPIにとって重要です。開発者がAPIとどのように対話し、何を期待するかを理解するのに役立ちます。FlightプロジェクトのAPIドキュメントを生成するためのいくつかのツールが利用可能です。

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - FlightPHPを使ってAPIファーストアプローチでAPIを構築する方法について、Daniel Schreiberが執筆したブログ記事です。
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UIは、FlightプロジェクトのAPIドキュメントを生成するのに役立つ優れたツールです。非常に使いやすく、ニーズに合わせてカスタマイズできます。これはSwaggerドキュメントを生成するためのPHPライブラリです。

## アプリケーションパフォーマンスモニタリング (APM)

アプリケーションパフォーマンスモニタリング（APM）は、すべてのアプリケーションにとって重要です。アプリケーションがどのように機能しているか、ボトルネックがどこにあるかを理解するのに役立ちます。Flightで使用できるAPMツールがいくつかあります。
- <span class="badge bg-info">ベータ</span>[flightphp/apm](/awesome-plugins/apm) - Flight APMは、Flightアプリケーションを監視するために使用できるシンプルなAPMライブラリです。アプリケーションのパフォーマンスを監視し、ボトルネックを特定するのに役立ちます。

## 認証/認可

認証と認可は、誰が何にアクセスできるかのコントロールが必要なすべてのアプリケーションにとって重要です。

- <span class="badge bg-primary">公式</span> [flightphp/permissions](/awesome-plugins/permissions) - 公式Flight Permissionsライブラリ。このライブラリは、アプリケーションにユーザーおよびアプリケーションレベルの権限を追加するためのシンプルな方法です。

## キャッシング

キャッシングは、アプリケーションの速度を上げる素晴らしい方法です。Flightと一緒に使用できるキャッシングライブラリがいくつかあります。

- <span class="badge bg-primary">公式</span> [flightphp/cache](/awesome-plugins/php-file-cache) - 軽量でシンプル、かつスタンドアロンのPHPインファイルキャッシングクラス

## CLI

CLIアプリケーションは、アプリケーションと対話する素晴らしい方法です。コントローラーを生成したり、すべてのルートを表示したり、その他多くのことができます。

- <span class="badge bg-primary">公式</span> [flightphp/runway](/awesome-plugins/runway) - Runwayは、あなたのFlightアプリケーションを管理するのに役立つCLIアプリケーションです。

## クッキー

クッキーは、クライアント側に小さなデータを保存する素晴らしい方法です。ユーザーの設定やアプリケーションの設定などを保存するために使用できます。

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookieは、クッキーを管理するためのシンプルで効果的な方法を提供するPHPライブラリです。

## デバッグ

デバッグは、ローカル環境で開発しているときに重要です。デバッグ体験を向上させるために使用できるプラグインがいくつかあります。

- [tracy/tracy](/awesome-plugins/tracy) - これはFlightと一緒に使用できる特徴のあるエラーハンドラーです。アプリケーションのデバッグに役立つパネルがいくつかあります。また、独自のパネルを追加することも非常に簡単です。
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - [Tracy](/awesome-plugins/tracy)エラーハンドラーと一緒に使用されるこのプラグインは、Flightプロジェクトのデバッグのためのいくつかの追加パネルを追加します。

## データベース

データベースはほとんどのアプリケーションの核心です。データを保存し、取得するための方法です。いくつかのデータベースライブラリは、単にクエリを記述するためのラッパーであり、いくつかは完全なORMです。

- <span class="badge bg-primary">公式</span> [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - コアの一部である公式Flight PDOラッパー。クエリを記述し、実行するプロセスを簡素化するためのシンプルなラッパーです。これはORMではありません。
- <span class="badge bg-primary">公式</span> [flightphp/active-record](/awesome-plugins/active-record) - 公式Flight ActiveRecord ORM/Mapper。データベースでのデータの取得や保存を容易にする素晴らしい小さなライブラリです。
- [byjg/php-migration](/awesome-plugins/migrations) - プロジェクトのすべてのデータベース変更を追跡するためのプラグインです。

## 暗号化

暗号化は、敏感なデータを保存するアプリケーションにとって重要です。データを暗号化および復号化することはそれほど難しくありませんが、暗号化キーを適切に保存することは[難しい場合があります](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.)。最も重要なことは、暗号化キーを公開ディレクトリに保存したり、コードリポジトリにコミットしたりしないことです。

- [defuse/php-encryption](/awesome-plugins/php-encryption) - データを暗号化および復号化するために使用できるライブラリです。データの暗号化および復号化を始めるのは比較的簡単です。

## ジョブキュー

ジョブキューは、非同期にタスクを処理するのに役立ちます。これには、メールの送信、画像の処理、またはリアルタイムで行う必要のない任意の作業が含まれます。

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queueは、ジョブを非同期に処理するために使用できるライブラリです。beanstalkd、MySQL/MariaDB、SQLite、およびPostgreSQLで使用できます。

## セッション

セッションはAPIにはあまり役立ちませんが、ウェブアプリケーションを構築する際には、状態やログイン情報を維持するために重要です。

- <span class="badge bg-primary">公式</span> [flightphp/session](/awesome-plugins/session) - 公式Flightセッションライブラリ。セッションデータを保存および取得するために使用できるシンプルなセッションライブラリです。PHPの組み込みセッション処理を使用しています。
- [Ghostff/Session](/awesome-plugins/ghost-session) - PHPセッションマネージャー（ノンブロッキング、フラッシュ、セグメント、セッション暗号化）。オプションでセッションデータの暗号化/復号化用にPHP open_sslを使用します。

## テンプレーティング

テンプレーティングは、UIを持つウェブアプリケーションの中心です。Flightと共に使用できるテンプレーティングエンジンがいくつかあります。

- <span class="badge bg-warning">非推奨</span> [flightphp/core View](/learn#views) - これはコアの一部である非常に基本的なテンプレーティングエンジンです。プロジェクトに数ページ以上がある場合の使用は推奨されません。
- [latte/latte](/awesome-plugins/latte) - Latteは、非常に使いやすく、TwigやSmartyよりもPHP構文に近いフル機能のテンプレーティングエンジンです。また、自分のフィルターや関数を追加することも非常に簡単です。

## 貢献

共有したいプラグインがありますか？リストに追加するためにプルリクエストを送信してください！