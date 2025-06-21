# 素晴らしいプラグイン

Flight は非常に拡張性が高いです。Flight アプリケーションに機能を追加するためのいくつかのプラグインがあります。一部は Flight チームによって公式にサポートされており、他のものは Flight を始めるためのマイクロ/ライトライブラリです。

## API ドキュメンテーション

API ドキュメンテーションは、どの API にとっても重要です。開発者が API とどのように相互作用するかを理解し、何を期待するかを知るのに役立ちます。Flight プロジェクトの API ドキュメンテーションを生成するためのいくつかのツールがあります。

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Daniel Schreiber によって書かれたブログ投稿で、OpenAPI 仕様を FlightPHP と一緒に使用して、API ファーストアプローチで API を構築する方法について説明しています。
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI は、Flight プロジェクトの API ドキュメンテーションを生成するのに最適なツールです。非常に使いやすく、ニーズに合わせてカスタマイズ可能です。これは Swagger ドキュメンテーションを生成するための PHP ライブラリです。

## アプリケーションのパフォーマンス監視 (APM)

アプリケーションのパフォーマンス監視 (APM) は、どのアプリケーションにとっても重要です。アプリケーションがどのように動作しているかを理解し、ボトルネックがどこにあるかを知るのに役立ちます。Flight と一緒に使用できるいくつかの APM ツールがあります。
- <span class="badge bg-info">beta</span>[flightphp/apm](/awesome-plugins/apm) - Flight APM は、Flight アプリケーションを監視するためのシンプルな APM ライブラリです。アプリケーションのパフォーマンスを監視し、ボトルネックを特定するのに使用できます。

## 認証/認可

認証と認可は、誰が何にアクセスできるかを制御する必要があるアプリケーションにとって重要です。

- <span class="badge bg-primary">official</span> [flightphp/permissions](/awesome-plugins/permissions) - 公式の Flight Permissions ライブラリです。このライブラリは、アプリケーションにユーザーとアプリケーションレベルの権限を簡単に追加するためのシンプルな方法です。

## キャッシング

キャッシングは、アプリケーションの速度を向上させる優れた方法です。Flight と一緒に使用できるいくつかのキャッシングライブラリがあります。

- <span class="badge bg-primary">official</span> [flightphp/cache](/awesome-plugins/php-file-cache) - 軽量でシンプルなスタンドアロンの PHP インファイルキャッシングクラス

## CLI

CLI アプリケーションは、アプリケーションと相互作用するための優れた方法です。コントローラーを生成したり、すべてのルートを表示したり、その他さまざまな用途に使用できます。

- <span class="badge bg-primary">official</span> [flightphp/runway](/awesome-plugins/runway) - Runway は、Flight アプリケーションを管理するための CLI アプリケーションです。

## クッキー

クッキーは、クライアント側に小さなデータを保存するための優れた方法です。ユーザーの好み、アプリケーションの設定などを保存するのに使用できます。

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie は、クッキーを管理するためのシンプルで効果的な PHP ライブラリを提供します。

## デバッグ

デバッグは、ローカル環境で開発する際に重要です。デバッグ体験を向上させるためのいくつかのプラグインがあります。

- [tracy/tracy](/awesome-plugins/tracy) - これは Flight と一緒に使用できる完全な機能を持つエラーハンドラーです。さまざまなパネルがあり、アプリケーションのデバッグに役立ちます。また、拡張して独自のパネルを追加することも非常に簡単です。
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - [Tracy](/awesome-plugins/tracy) エラーハンドラーと一緒に使用され、Flight プロジェクトのデバッグに特化した追加のパネルを追加します。

## データベース

データベースは、ほとんどのアプリケーションの基盤です。これを使ってデータを保存し、取得します。一部はクエリを書くためのシンプルなラッパーで、一部はフル機能の ORM です。

- <span class="badge bg-primary">official</span> [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - 公式の Flight PDO Wrapper で、core の一部です。これはクエリを書くプロセスを簡素化するためのシンプルなラッパーです。ORM ではありません。
- <span class="badge bg-primary">official</span> [flightphp/active-record](/awesome-plugins/active-record) - 公式の Flight ActiveRecord ORM/Mapper です。データベースにデータを簡単に取得し、保存するための優れた小さなライブラリです。
- [byjg/php-migration](/awesome-plugins/migrations) - プロジェクトのすべてのデータベース変更を追跡するためのプラグインです。

## 暗号化

暗号化は、機密データを保存するアプリケーションにとって重要です。データを暗号化し、復号化するのはそれほど難しくありませんが、暗号化キーを適切に保存する[こと](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [は](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [難しい](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key)場合があります。一番重要なのは、暗号化キーを公開ディレクトリに保存したり、コードリポジトリにコミットしたりしないことです。

- [defuse/php-encryption](/awesome-plugins/php-encryption) - これはデータを暗号化し、復号化するためのライブラリです。セットアップしてデータを暗号化し、復号化するのは比較的簡単です。

## ジョブキュー

ジョブキューは、タスクを非同期で処理するための非常に役立つものです。これには、メールの送信、画像の処理、リアルタイムで処理する必要がないものは何でも使用できます。

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue は、ジョブを非同期で処理するためのライブラリです。beanstalkd、MySQL/MariaDB、SQLite、PostgreSQL と一緒に使用できます。

## セッション

セッションは API にはあまり役立ちませんが、Web アプリケーションを構築する際には、状態の維持やログイン情報の管理に重要です。

- <span class="badge bg-primary">official</span> [flightphp/session](/awesome-plugins/session) - 公式の Flight Session ライブラリです。これはセッションデータを保存し、取得するためのシンプルなセッションライブラリで、PHP の組み込みセッション処理を使用します。
- [Ghostff/Session](/awesome-plugins/ghost-session) - PHP Session Manager (非ブロッキング、フラッシュ、セグメント、セッション暗号化)。オプションでセッションデータを暗号化/復号化するための PHP open_ssl を使用します。

## テンプレート

テンプレートは、UI を持つ Web アプリケーションの基盤です。Flight と一緒に使用できるいくつかのテンプレートエンジンがあります。

- <span class="badge bg-warning">deprecated</span> [flightphp/core View](/learn#views) - これは core の一部で非常に基本的なテンプレートエンジンです。プロジェクトに数ページ以上ある場合、推奨されません。
- [latte/latte](/awesome-plugins/latte) - Latte は、使いやすく、Twig や Smarty よりも PHP 構文に近い、完全な機能を持つテンプレートエンジンです。また、フィルターや関数を拡張して追加することも非常に簡単です。

## 貢献

共有したいプラグインがありますか？リストに追加するためのプルリクエストを提出してください！