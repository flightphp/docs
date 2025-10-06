# 素晴らしいプラグイン

Flight は驚くほど拡張性が高いです。Flight アプリケーションに機能を追加するために使用できる多くのプラグインがあります。一部は Flight チームによって公式にサポートされており、他のものはスタートするためのマイクロ/ライトライブラリです。

## API ドキュメント

API ドキュメントはどんな API にとっても重要です。開発者が API とどのようにやり取りするかを理解し、何を期待するかを知るのに役立ちます。Flight プロジェクトの API ドキュメントを生成するのに役立ついくつかのツールがあります。

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Daniel Schreiber によるブログ記事で、OpenAPI Spec を FlightPHP と使用して API ファーストアプローチで API を構築する方法について説明しています。
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI は、Flight プロジェクトの API ドキュメントを生成するのに役立つ優れたツールです。非常に使いやすく、ニーズに合わせてカスタマイズ可能です。これは Swagger ドキュメントを生成するための PHP ライブラリです。

## アプリケーション パフォーマンス モニタリング (APM)

アプリケーション パフォーマンス モニタリング (APM) はどんなアプリケーションにとっても重要です。アプリケーションのパフォーマンスを理解し、ボトルネックの場所を特定するのに役立ちます。Flight で使用できる APM ツールがいくつかあります。
- <span class="badge bg-primary">official</span> [flightphp/apm](/awesome-plugins/apm) - Flight APM は、Flight アプリケーションを監視するためのシンプルな APM ライブラリです。アプリケーションのパフォーマンスを監視し、ボトルネックを特定するのに使用できます。

## 非同期

Flight はすでに高速なフレームワークですが、それにターボエンジンを追加するとすべてがより楽しく（そして挑戦的）になります！

- [flightphp/async](/awesome-plugins/async) - 公式の Flight Async ライブラリです。このライブラリは、アプリケーションに非同期処理を追加するシンプルな方法です。Swoole/Openswoole を内部で使用して、タスクを非同期で実行するシンプルで効果的な方法を提供します。

## 認可/権限

認可と権限は、誰が何にアクセスできるかを制御する必要があるどんなアプリケーションにとっても重要です。

- <span class="badge bg-primary">official</span> [flightphp/permissions](/awesome-plugins/permissions) - 公式の Flight Permissions ライブラリです。このライブラリは、アプリケーションにユーザーおよびアプリケーション レベルの権限を追加するシンプルな方法です。

## キャッシュ

キャッシュはアプリケーションを高速化する優れた方法です。Flight で使用できるキャッシュ ライブラリがいくつかあります。

- <span class="badge bg-primary">official</span> [flightphp/cache](/awesome-plugins/php-file-cache) - 軽量でシンプルなスタンドアローンファイル内キャッシュ PHP クラス

## CLI

CLI アプリケーションはアプリケーションとやり取りする優れた方法です。コントローラーの生成、すべてのルートの表示などを使用できます。

- <span class="badge bg-primary">official</span> [flightphp/runway](/awesome-plugins/runway) - Runway は Flight アプリケーションを管理するのに役立つ CLI アプリケーションです。

## クッキー

クッキーはクライアント側に少量のデータを保存する優れた方法です。ユーザーの好み、アプリケーション設定などを保存するために使用できます。

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie は、クッキーを管理するシンプルで効果的な PHP ライブラリです。

## デバッグ

デバッグはローカル環境で開発する際に重要です。デバッグ体験を向上させるいくつかのプラグインがあります。

- [tracy/tracy](/awesome-plugins/tracy) - これは Flight で使用できる完全機能のエラーハンドラーです。アプリケーションをデバッグするのに役立ついくつかのパネルがあります。また、拡張して独自のパネルを追加するのが非常に簡単です。
- <span class="badge bg-primary">official</span> [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - [Tracy](/awesome-plugins/tracy) エラーハンドラーと使用され、このプラグインは Flight プロジェクト専用のデバッグを支援するための追加パネルをいくつか追加します。

## データベース

データベースはほとんどのアプリケーションのコアです。これでデータを保存および取得します。一部のデータベース ライブラリはクエリを書くための単なるラッパーであり、一部はフル機能の ORM です。

- <span class="badge bg-primary">official</span> [flightphp/core PdoWrapper](/learn/pdo-wrapper) - コアの一部である公式の Flight PDO Wrapper です。これはクエリの記述と実行を簡素化するためのシンプルなラッパーです。ORM ではありません。
- <span class="badge bg-primary">official</span> [flightphp/active-record](/awesome-plugins/active-record) - 公式の Flight ActiveRecord ORM/Mapper です。データベースからデータを簡単に取得および保存するための優れた小さなライブラリです。
- [byjg/php-migration](/awesome-plugins/migrations) - プロジェクトのすべてのデータベース変更を追跡するためのプラグインです。

## 暗号化

暗号化は機密データを保存するどんなアプリケーションにとっても重要です。データの暗号化と復号化はそれほど難しくありませんが、暗号化キーの適切な保存 [は](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [です](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [難しい](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key)。最も重要なのは、暗号化キーをパブリックディレクトリに保存したり、コードリポジトリにコミットしたりしないことです。

- [defuse/php-encryption](/awesome-plugins/php-encryption) - これはデータを暗号化および復号化するためのライブラリです。データの暗号化と復号化を開始するのがかなりシンプルです。

## ジョブキュー

ジョブキューはタスクを非同期で処理するのに非常に役立ちます。これはメールの送信、画像の処理、またはリアルタイムで必要ない何でもです。

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue は、ジョブを非同期で処理するためのライブラリです。beanstalkd、MySQL/MariaDB、SQLite、PostgreSQL で使用できます。

## セッション

セッションは API にはあまり役立ちませんが、Web アプリケーションを構築する際には、状態とログイン情報を維持するために重要です。

- <span class="badge bg-primary">official</span> [flightphp/session](/awesome-plugins/session) - 公式の Flight Session ライブラリです。これはセッションデータを保存および取得するためのシンプルなセッション ライブラリです。PHP の組み込みセッション処理を使用します。
- [Ghostff/Session](/awesome-plugins/ghost-session) - PHP Session Manager (非ブロッキング、フラッシュ、セグメント、セッション暗号化)。セッションデータのオプションの暗号化/復号化に PHP open_ssl を使用します。

## テンプレート

テンプレートは UI を持つどんな Web アプリケーションにとってもコアです。Flight で使用できるテンプレートエンジンがいくつかあります。

- <span class="badge bg-warning">deprecated</span> [flightphp/core View](/learn#views) - コアの一部である非常に基本的なテンプレートエンジンです。プロジェクトに数ページ以上ある場合は使用を推奨しません。
- [latte/latte](/awesome-plugins/latte) - Latte は、Twig や Smarty よりも PHP 構文に近い、非常に使いやすいフル機能のテンプレートエンジンです。また、拡張して独自のフィルターと関数を追加するのが非常に簡単です。

## WordPress 統合

WordPress プロジェクトで Flight を使用したいですか？そのための便利なプラグインがあります！

- [n0nag0n/wordpress-integration-for-flight-framework](/awesome-plugins/n0nag0n_wordpress) - この WordPress プラグインにより、WordPress と並行して Flight を実行できます。カスタム API、マイクロサービス、または WordPress サイトに Flight フレームワークを使用してフルアプリを追加するのに最適です。両方の世界の最高を望む場合に超便利です！

## 貢献

共有したいプラグインがありますか？リストに追加するためのプルリクエストを送信してください！