# 素晴らしいプラグイン

Flight は非常に拡張性が高いです。Flight アプリケーションに機能を追加するために使用できるプラグインがいくつかあります。一部は Flight チームによって公式にサポートされており、他のものは開始するのに役立つマイクロ/ライトライブラリです。

## API ドキュメント

API ドキュメントは、API のために不可欠です。開発者が API とどのようにやり取りするかを理解し、何を期待するかを知るのに役立ちます。Flight プロジェクトの API ドキュメントを生成するのに役立つツールがいくつかあります。

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Daniel Schreiber によって書かれたブログ投稿で、OpenAPI Spec を FlightPHP と使用して API ファーストアプローチで API を構築する方法を説明しています。
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI は、Flight プロジェクトの API ドキュメントを生成するのに役立つ優れたツールです。非常に使いやすく、ニーズに合わせてカスタマイズ可能です。これは Swagger ドキュメントを生成するための PHP ライブラリです。

## アプリケーション パフォーマンス監視 (APM)

アプリケーション パフォーマンス監視 (APM) は、アプリケーションのために不可欠です。アプリケーションのパフォーマンスを理解し、ボトルネックがどこにあるかを知るのに役立ちます。Flight と使用できる APM ツールがいくつかあります。
- <span class="badge bg-primary">official</span> [flightphp/apm](/awesome-plugins/apm) - Flight APM は、Flight アプリケーションを監視するために使用できるシンプルな APM ライブラリです。アプリケーションのパフォーマンスを監視し、ボトルネックを特定するのに役立ちます。

## 認可/権限

認可と権限は、誰が何にアクセスできるかを制御するためのコントロールが必要なアプリケーションのために不可欠です。

- <span class="badge bg-primary">official</span> [flightphp/permissions](/awesome-plugins/permissions) - 公式の Flight Permissions ライブラリです。このライブラリは、アプリケーションにユーザーおよびアプリケーション レベルの権限を追加するシンプルな方法です。 

## キャッシュ

キャッシュはアプリケーションを高速化する優れた方法です。Flight と使用できるキャッシュ ライブラリがいくつかあります。

- <span class="badge bg-primary">official</span> [flightphp/cache](/awesome-plugins/php-file-cache) - 軽量でシンプルなスタンドアロン PHP イン-File キャッシュ クラス

## CLI

CLI アプリケーションは、アプリケーションとやり取りする優れた方法です。コントローラーを生成したり、全ルートを表示したり、その他多くのことを行うために使用できます。

- <span class="badge bg-primary">official</span> [flightphp/runway](/awesome-plugins/runway) - Runway は、Flight アプリケーションを管理するのに役立つ CLI アプリケーションです。

## クッキー

クッキーは、クライアント側に少量のデータを保存する優れた方法です。ユーザー設定、アプリケーション設定などを保存するために使用できます。

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie は、クッキーを管理するためのシンプルで効果的な PHP ライブラリを提供します。

## デバッグ

デバッグは、ローカル環境で開発する際に不可欠です。デバッグ体験を向上させるプラグインがいくつかあります。

- [tracy/tracy](/awesome-plugins/tracy) - これは Flight と使用できる完全な機能のエラーハンドラーです。アプリケーションをデバッグするのに役立つパネルがいくつかあります。また、拡張して独自のパネルを追加するのが非常に簡単です。
- <span class="badge bg-primary">official</span> [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - [Tracy](/awesome-plugins/tracy) エラーハンドラーと使用され、このプラグインは Flight プロジェクト専用のデバッグを支援するための追加パネルをいくつか追加します。

## データベース

データベースはほとんどのアプリケーションのコアです。これによりデータを保存および取得します。一部のデータベース ライブラリはクエリを書くための単なるラッパーで、一部は完全な ORM です。

- <span class="badge bg-primary">official</span> [flightphp/core PdoWrapper](/learn/pdo-wrapper) - コアの一部である公式の Flight PDO Wrapper です。これはクエリを書くおよび実行するプロセスを簡素化するためのシンプルなラッパーです。ORM ではありません。
- <span class="badge bg-primary">official</span> [flightphp/active-record](/awesome-plugins/active-record) - 公式の Flight ActiveRecord ORM/マッパーです。データベースからデータを簡単に取得および保存するための優れた小さなライブラリです。
- [byjg/php-migration](/awesome-plugins/migrations) - プロジェクトのすべてのデータベース変更を追跡するためのプラグインです。

## 暗号化

暗号化は、機密データを保存するアプリケーションのために不可欠です。データを暗号化および復号化するのはそれほど難しくありませんが、暗号化キーを適切に保存するのは [can](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [be](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [difficult](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key) です。最も重要なのは、暗号化キーを公開ディレクトリに保存したり、コード リポジトリにコミットしたりしないことです。

- [defuse/php-encryption](/awesome-plugins/php-encryption) - これはデータを暗号化および復号化するために使用できるライブラリです。データを暗号化および復号化するのを開始するのはかなりシンプルです。

## ジョブキュー

ジョブキューは、タスクを非同期に処理するのに非常に役立ちます。これはメールの送信、画像の処理、またはリアルタイムで実行する必要のないあらゆるものです。

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue は、ジョブを非同期に処理するために使用できるライブラリです。beanstalkd、MySQL/MariaDB、SQLite、PostgreSQL と使用できます。

## セッション

セッションは API にはあまり有用ではありませんが、Web アプリケーションを構築する際には、状態とログイン情報を維持するために不可欠です。

- <span class="badge bg-primary">official</span> [flightphp/session](/awesome-plugins/session) - 公式の Flight Session ライブラリです。これはセッションデータを保存および取得するために使用できるシンプルなセッション ライブラリです。PHP の組み込みセッション ハンドリングを使用します。
- [Ghostff/Session](/awesome-plugins/ghost-session) - PHP Session Manager (非ブロッキング、フラッシュ、セグメント、セッション暗号化)。セッションデータのオプションの暗号化/復号化に PHP open_ssl を使用します。

## テンプレート

テンプレートは UI を備えた Web アプリケーションのコアです。Flight と使用できるテンプレート エンジンがいくつかあります。

- <span class="badge bg-warning">deprecated</span> [flightphp/core View](/learn#views) - コアの一部である非常に基本的なテンプレート エンジンです。プロジェクトに数ページ以上ある場合は使用を推奨しません。
- [latte/latte](/awesome-plugins/latte) - Latte は、Twig や Smarty よりも PHP 構文に近い、非常に使いやすい完全な機能のテンプレート エンジンです。また、拡張して独自のフィルターと関数を追加するのが非常に簡単です。

## WordPress 統合

WordPress プロジェクトで Flight を使用したいですか？ そのための便利なプラグインがあります！

- [n0nag0n/wordpress-integration-for-flight-framework](/awesome-plugins/n0nag0n_wordpress) - この WordPress プラグインにより、WordPress と並行して Flight を実行できます。カスタム API、マイクロサービス、または Flight フレームワークを使用して WordPress サイトにフルアプリを追加するのに最適です。両方の世界の最高を望む場合に超有用です！

## 貢献

共有したいプラグインがありますか？ リストに追加するためのプルリクエストを送信してください！