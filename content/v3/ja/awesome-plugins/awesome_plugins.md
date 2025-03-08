# 素晴らしいプラグイン

Flightは信じられないほど拡張性があります。Flightアプリケーションの機能を追加するために使用できるプラグインがいくつかあります。中にはFlightチームによって公式にサポートされているものもあり、他には始めるためのマイクロ/ライトライブラリもあります。

## APIドキュメンテーション

APIドキュメンテーションは、あらゆるAPIにとって重要です。開発者がAPIとどのように対話し、何を期待できるかを理解するのに役立ちます。FlightプロジェクトのAPIドキュメンテーションを生成するためのツールがいくつか用意されています。

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - FlightPHPを使用してAPIファーストアプローチでAPIを構築する方法についてDaniel Schreiberが書いたブログ投稿。
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UIは、FlightプロジェクトのAPIドキュメンテーションを生成するのに役立つ素晴らしいツールです。非常に使いやすく、ニーズに合わせてカスタマイズできます。これはSwaggerドキュメンテーションを生成するためのPHPライブラリです。

## 認証/認可

認証と認可は、誰が何にアクセスできるかを管理するために必要なアプリケーションにとって重要です。

- <span class="badge bg-primary">official</span> [flightphp/permissions](/awesome-plugins/permissions) - 公式Flight権限ライブラリ。このライブラリは、アプリケーションにユーザーとアプリケーションレベルの権限を追加するための簡単な方法です。

## キャッシング

キャッシングはアプリケーションを高速化するための優れた方法です。Flightと一緒に使用できるキャッシングライブラリがいくつかあります。

- <span class="badge bg-primary">official</span> [flightphp/cache](/awesome-plugins/php-file-cache) - 軽量でシンプル、独立したPHPインファイルキャッシングクラス

## CLI

CLIアプリケーションは、アプリケーションと対話するための素晴らしい方法です。コントローラを生成したり、すべてのルートを表示したりするために使用できます。

- <span class="badge bg-primary">official</span> [flightphp/runway](/awesome-plugins/runway) - Runwayは、Flightアプリケーションを管理するのに役立つCLIアプリケーションです。

## クッキー

クッキーはクライアント側に小さなデータを保存するための優れた方法です。ユーザーの設定やアプリケーションの設定などを保存するために使用できます。

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookieは、クッキーを管理するためのシンプルで効果的な方法を提供するPHPライブラリです。

## デバッグ

デバッグは、ローカル環境で開発しているときに重要です。デバッグ体験を向上させるためのプラグインがいくつかあります。

- [tracy/tracy](/awesome-plugins/tracy) - これはFlightと一緒に使用できるフル機能のエラーハンドラーです。アプリケーションのデバッグに役立ついくつかのパネルがあります。また、非常に簡単に拡張して独自のパネルを追加できます。
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - [Tracy](/awesome-plugins/tracy)エラーハンドラーと一緒に使用されるこのプラグインは、Flightプロジェクトのデバッグを助けるためのいくつかの追加パネルを提供します。

## データベース

データベースはほとんどのアプリケーションの中心です。これはデータを保存し、取得する方法です。いくつかのデータベースライブラリは、クエリを書くためのラッパーに過ぎないものもあれば、完全なORMであるものもあります。

- <span class="badge bg-primary">official</span> [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - コアの一部である公式Flight PDOラッパー。これは、クエリを書いて実行するプロセスを簡素化するためのシンプルなラッパーです。ORMではありません。
- <span class="badge bg-primary">official</span> [flightphp/active-record](/awesome-plugins/active-record) - 公式Flight ActiveRecord ORM/マッパー。データベース内のデータを簡単に取得して保存するための素晴らしい小さなライブラリです。
- [byjg/php-migration](/awesome-plugins/migrations) - プロジェクトのすべてのデータベース変更を追跡するためのプラグインです。

## 暗号化

暗号化は、機密データを保存するアプリケーションにとって重要です。データを暗号化および復号化することはそれほど難しくありませんが、暗号化キーを適切に保存することは[難しいことがあります](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.)。最も重要なのは、暗号化キーを公開ディレクトリに保存したり、コードリポジトリにコミットしたりしないことです。

- [defuse/php-encryption](/awesome-plugins/php-encryption) - これはデータを暗号化および復号化するために使用できるライブラリです。データの暗号化と復号化を開始するのは比較的簡単です。

## ジョブキュー

ジョブキューは、非同期にタスクを処理するのに非常に便利です。これには、メールの送信、画像の処理、リアルタイムで行う必要がないその他の作業が含まれます。

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queueは、ジョブを非同期に処理するために使用できるライブラリです。beanstalkd、MySQL/MariaDB、SQLite、PostgreSQLで使用できます。

## セッション

セッションはAPIにはあまり便利ではありませんが、Webアプリケーションを構築するためには、状態とログイン情報を維持するために重要です。

- <span class="badge bg-primary">official</span> [flightphp/session](/awesome-plugins/session) - 公式Flightセッションライブラリ。これはセッションデータを保存および取得するために使用できるシンプルなセッションライブラリです。PHPの組み込みのセッションハンドリングを使用しています。
- [Ghostff/Session](/awesome-plugins/ghost-session) - PHPセッションマネージャー（非ブロッキング、フラッシュ、セグメント、セッション暗号化）。セッションデータのオプションの暗号化/復号化にPHP open_sslを使用します。

## テンプレーティング

テンプレーティングは、UIを持つWebアプリケーションの中心です。Flightと一緒に使用できるテンプレーティングエンジンがいくつかあります。

- <span class="badge bg-warning">deprecated</span> [flightphp/core View](/learn#views) - これはコアの一部である非常に基本的なテンプレーティングエンジンです。プロジェクトにページが数ページ以上ある場合は使用することをお勧めしません。
- [latte/latte](/awesome-plugins/latte) - Latteは、非常に使いやすく、TwigやSmartyよりもPHPの構文に近い完全な機能を持つテンプレーティングエンジンです。また、拡張や独自のフィルターや関数の追加も非常に簡単です。

## 貢献

共有したいプラグインがありますか？リストに追加するためにプルリクエストを送信してください！