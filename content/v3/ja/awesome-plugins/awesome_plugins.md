# 素晴らしいプラグイン

Flight は非常に拡張性が高く、Flight アプリケーションに機能を追加するためのさまざまなプラグインを使用できます。一部は Flight チームによって公式にサポートされており、他のものは Flight を始めるためのマイクロ/ライトライブラリです。

## API ドキュメント

API ドキュメントは、どの API にとっても重要です。開発者が API とどのように相互作用するかを理解し、返されるものを期待できるようにします。Flight プロジェクトの API ドキュメントを生成するためのいくつかのツールがあります。

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Daniel Schreiber によって書かれたブログ投稿で、OpenAPI 仕様を FlightPHP と一緒に使用して、API ファーストアプローチで API を構築する方法について説明しています。
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI は、Flight プロジェクトの API ドキュメントを生成するための優れたツールです。非常に使いやすく、ニーズに合わせてカスタマイズできます。これは Swagger ドキュメントを生成するための PHP ライブラリです。

## アプリケーションのパフォーマンス監視 (APM)

アプリケーションのパフォーマンス監視 (APM) は、どのアプリケーションにとっても重要です。アプリケーションのパフォーマンスを理解し、ボトルネックがどこにあるかを把握するのに役立ちます。Flight と一緒に使用できるいくつかの APM ツールがあります。
- <span class="badge bg-info">beta</span>[flightphp/apm](/awesome-plugins/apm) - Flight APM は、Flight アプリケーションを監視するためのシンプルな APM ライブラリです。アプリケーションのパフォーマンスを監視し、ボトルネックを特定するのに使用できます。

## 認証/認可

認証と認可は、誰が何にアクセスできるかを制御する必要があるアプリケーションにとって重要です。

- <span class="badge bg-primary">official</span> [flightphp/permissions](/awesome-plugins/permissions) - 公式 Flight Permissions ライブラリです。このライブラリは、ユーザーとアプリケーションのレベルの権限を簡単に追加するためのシンプルな方法です。

## キャッシング

キャッシングは、アプリケーションの速度を向上させる優れた方法です。Flight と一緒に使用できるさまざまなキャッシングライブラリがあります。

- <span class="badge bg-primary">official</span> [flightphp/cache](/awesome-plugins/php-file-cache) - 軽量でシンプルでスタンドアロンの PHP インファイルキャッシングクラス

## CLI

CLI アプリケーションは、アプリケーションと相互作用するための優れた方法です。コントローラーを生成したり、すべてのルートを表示したり、その他さまざまな用途に使用できます。

- <span class="badge bg-primary">official</span> [flightphp/runway](/awesome-plugins/runway) - Runway は、Flight アプリケーションを管理するための CLI アプリケーションです。

## クッキー

クッキーは、クライアント側に小さなデータを保存するための優れた方法です。ユーザーの好み、アプリケーションの設定などを保存するのに使用できます。

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie は、クッキーを管理するためのシンプルで効果的な PHP ライブラリを提供します。

## デバッグ

デバッグは、ローカル環境で開発する際に重要です。デバッグ体験を向上させるためのいくつかのプラグインがあります。

- [tracy/tracy](/awesome-plugins/tracy) - これは Flight と一緒に使用できるフル機能のエラーハンドラーです。さまざまなパネルがあり、アプリケーションのデバッグに役立ちます。また、拡張して独自のパネルを追加することも非常に簡単です。
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - [Tracy](/awesome-plugins/tracy) エラーハンドラーと一緒に使用され、Flight プロジェクトのデバッグに特化した追加のパネルを追加するプラグインです。

## データベース

データベースは、ほとんどのアプリケーションの基盤です。これを使ってデータを保存し、取得します。一部のデータベースライブラリはクエリを書くための単純なラッパーですが、他のものはフル機能の ORM です。

- <span class="badge bg-primary">official</span> [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - 公式 Flight PDO Wrapper で、コアの一部です。これはクエリを書くプロセスを簡素化するためのシンプルなラッパーです。ORM ではありません。
- <span class="badge bg-primary">official</span> [flightphp/active-record](/awesome-plugins/active-record) - 公式 Flight ActiveRecord ORM/Mapper です。データベースにデータを簡単に取得し、保存するための優れた小さなライブラリです。
- [byjg/php-migration](/awesome-plugins/migrations) - プロジェクトのすべてのデータベース変更を追跡するためのプラグインです。

## 暗号化

暗号化は、機密データを保存するアプリケーションにとって重要です。データを暗号化し、復号化するのはそれほど難しくありませんが、暗号化キーを適切に保存 [can](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [be](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [difficult](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key) [です](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key)。最も重要なのは、暗号化キーを公開ディレクトリに保存したり、コードリポジトリにコミットしたりしないことです。

- [defuse/php-encryption](/awesome-plugins/php-encryption) - これはデータを暗号化し、復号化するためのライブラリです。実行してデータを暗号化し、復号化するのに比較的簡単です。

## ジョブキュー

ジョブキューは、非同期にタスクを処理するのに非常に役立ちます。これはメールの送信、画像の処理、リアルタイムで処理する必要がないものを含みます。

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue は、ジョブを非同期に処理するためのライブラリです。beanstalkd、MySQL/MariaDB、SQLite、PostgreSQL と一緒に使用できます。

## セッション

セッションは API ではあまり役立ちませんが、Web アプリケーションを構築する場合は、状態とログイン情報を維持するために重要です。

- <span class="badge bg-primary">official</span> [flightphp/session](/awesome-plugins/session) - 公式 Flight Session ライブラリです。これはセッションデータを保存し、取得するためのシンプルなセッションライブラリです。PHP の組み込みセッション処理を使用します。
- [Ghostff/Session](/awesome-plugins/ghost-session) - PHP Session Manager (非ブロッキング、フラッシュ、セグメント、セッション暗号化)。オプションでセッションデータの暗号化/復号化に PHP open_ssl を使用します。

## テンプレート

テンプレートは、UI を持つ Web アプリケーションの基盤です。Flight と一緒に使用できるさまざまなテンプレートエンジンがあります。

- <span class="badge bg-warning">deprecated</span> [flightphp/core View](/learn#views) - これはコアの一部である非常に基本的なテンプレートエンジンです。プロジェクトに数ページ以上ある場合、推奨されません。
- [latte/latte](/awesome-plugins/latte) - Latte は、使いやすく、Twig や Smarty よりも PHP 構文に近い、フル機能のテンプレートエンジンです。また、フィルターや関数を拡張して追加することも非常に簡単です。

## WordPress 統合

WordPress プロジェクトで Flight を使用したいですか？ それ用の便利なプラグインがあります！

- [n0nag0n/wordpress-integration-for-flight-framework](/awesome-plugins/n0nag0n_wordpress) - この WordPress プラグインは、WordPress と並行して Flight を実行できます。Flight フレームワークを使用して、WordPress サイトにカスタム API、マイクロサービス、またはフルアプリを追加するのに最適です。両方の世界のベストを組み合わせたい場合に非常に便利です！

## コントリビュート

共有したいプラグインがありますか？ リストに追加するためのプルリクエストを送信してください！