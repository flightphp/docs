# 素晴らしいプラグイン

Flight は非常に拡張性が高いです。Flight アプリケーションに機能を追加するために使用できるプラグインが多数あります。一部は Flight チームによって公式にサポートされており、その他は導入の助けとなるマイクロ/軽量ライブラリです。

## AI ツール

Flight は AI を活用したプラグインでさらにクールにできます。

- [Flight MCP](/awesome-plugins/mcp) - MCP（Model Control Protocol）を Flight と統合するためのプラグインで、シームレスな AI 搭載機能を実現します。主にドキュメントページに焦点を当てており、Flight プロジェクトに関する最新情報を提供することでトークンコストを抑えるのに役立ちます。
- [stribus/mcp-flightphp-server-skeleton](https://github.com/stribus/mcp-flightphp-server-skeleton) - HTTP と stdio を備えた FlightPHP MCP サーバースケルトンに加え、ツール、プロンプト、リソースの自動検出機能を提供します。

## API ドキュメント

API ドキュメントはあらゆる API にとって極めて重要です。開発者が API とどのようにやり取りし、何が返されるかを理解する助けになります。Flight プロジェクトの API ドキュメントを生成するのに役立つツールがいくつかあります。

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Daniel Schreiber による、OpenAPI 仕様を FlightPHP で使用し、API ファーストのアプローチで API を構築する方法に関するブログ記事です。
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI は、Flight プロジェクトの API ドキュメントを生成するのに役立つ優れたツールです。非常に使いやすく、ニーズに合わせてカスタマイズできます。これは Swagger ドキュメントの生成を支援する PHP ライブラリです。

## アプリケーション パフォーマンス モニタリング（APM）

アプリケーション パフォーマンス モニタリング（APM）は、あらゆるアプリケーションにとって極めて重要です。アプリケーションのパフォーマンスやボトルネックを把握するのに役立ちます。Flight で使用できる APM ツールは多数あります。
- <span class="badge bg-primary">公式</span> [flightphp/apm](/awesome-plugins/apm) - Flight APM は、Flight アプリケーションを監視するために使用できるシンプルな APM ライブラリです。アプリケーションのパフォーマンスを監視し、ボトルネックを特定するのに役立ちます。

## 非同期

Flight はすでに高速なフレームワークですが、ターボエンジンを載せるとすべてがより楽しく（そしてやりがいのあるものに）なります！

- [flightphp/async](/awesome-plugins/async) - 公式 Flight Async ライブラリ。このライブラリは、アプリケーションに非同期処理を追加するシンプルな方法です。内部で Swoole/Openswoole を使用し、タスクを非同期で実行するためのシンプルで効果的な方法を提供します。

## 認可/権限

認可と権限は、誰が何にアクセスできるかを制御する必要があるあらゆるアプリケーションにとって極めて重要です。

- <span class="badge bg-primary">公式</span> [flightphp/permissions](/awesome-plugins/permissions) - 公式 Flight Permissions ライブラリ。このライブラリは、アプリケーションにユーザーレベルおよびアプリケーションレベルの権限を追加するシンプルな方法です。

## 認証

認証は、ユーザー ID を検証し、API エンドポイントを保護する必要があるアプリケーションにとって不可欠です。

- [firebase/php-jwt](/awesome-plugins/jwt) - PHP 用の JSON Web Token (JWT) ライブラリ。Flight アプリケーションでトークンベース認証を実装するためのシンプルで安全な方法です。ステートレスな API 認証、ミドルウェアによるルート保護、OAuth スタイルの認可フローの実装に最適です。

## キャッシュ

キャッシュはアプリケーションを高速化する優れた方法です。Flight で使用できるキャッシュライブラリは多数あります。

- <span class="badge bg-primary">公式</span> [flightphp/cache](/awesome-plugins/php-file-cache) - 軽量でシンプルなスタンドアロンの PHP ファイル内キャッシュクラス

## CLI

CLI アプリケーションは、アプリケーションとやり取りする優れた方法です。コントローラの生成、すべてのルートの表示などに使用できます。

- <span class="badge bg-primary">公式</span> [flightphp/runway](/awesome-plugins/runway) - Runway は、Flight アプリケーションの管理を支援する CLI アプリケーションです。

## Cookie

Cookie は、クライアント側に少量のデータを保存する優れた方法です。ユーザー設定、アプリケーション設定などを保存するために使用できます。

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie は、Cookie を管理するためのシンプルで効果的な方法を提供する PHP ライブラリです。

## デバッグ

ローカル環境で開発しているとき、デバッグは極めて重要です。デバッグ体験を向上させるプラグインがいくつかあります。

- [tracy/tracy](/awesome-plugins/tracy) - Flight で使用できるフル機能のエラーハンドラです。アプリケーションのデバッグに役立つ多数のパネルを備えています。独自のパネルを拡張して追加するのも非常に簡単です。
- <span class="badge bg-primary">公式</span> [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - [Tracy](/awesome-plugins/tracy) エラーハンドラと併用して、Flight プロジェクト専用のデバッグを支援するいくつかの追加パネルを追加するプラグインです。

## データベース

データベースはほとんどのアプリケーションの中核です。データを保存および取得する方法です。データベースライブラリには、クエリを書くための単なるラッパーもあれば、本格的な ORM もあります。

- <span class="badge bg-primary">公式</span> [flightphp/core SimplePdo](/learn/simple-pdo) - コアの一部である公式 Flight PDO ヘルパー。`insert()`、`update()`、`delete()`、`transaction()` などの便利なヘルパーメソッドを備えたモダンなラッパーで、データベース操作を簡素化します。すべての結果は Collections として返され、配列/オブジェクトの柔軟なアクセスを可能にします。ORM ではなく、PDO を扱うためのより良い方法です。
- <span class="badge bg-warning">非推奨</span> [flightphp/core PdoWrapper](/learn/pdo-wrapper) - コアの一部である公式 Flight PDO Wrapper（v3.18.0 以降非推奨）。代わりに SimplePdo を使用してください。
- <span class="badge bg-primary">公式</span> [flightphp/active-record](/awesome-plugins/active-record) - 公式 Flight ActiveRecord ORM/Mapper。データベース内のデータを簡単に取得および保存するための優れた小さなライブラリです。
- [byjg/php-migration](/awesome-plugins/migrations) - プロジェクトのすべてのデータベース変更を追跡するためのプラグイン。
- [knifelemon/easy-query](/awesome-plugins/easy-query) - プリペアドステートメント用の SQL とパラメータを生成する、軽量で流暢な SQL クエリビルダー。[SimplePdo](/learn/simple-pdo) と相性が抜群です。

## 暗号化

暗号化は、機密データを保存するあらゆるアプリケーションにとって極めて重要です。データの暗号化と復号化はそれほど難しくありませんが、暗号化キーを適切に保存するのは [難しい](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key) [ことが](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [あります](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.)。最も重要なのは、暗号化キーを公開ディレクトリに保存したり、コードリポジトリにコミットしたりしないことです。

- [defuse/php-encryption](/awesome-plugins/php-encryption) - データの暗号化と復号化に使用できるライブラリです。セットアップはかなり簡単で、すぐにデータの暗号化と復号化を始められます。

## メール

メール送信は、ほとんどの Web アプリケーションにとって中核的なニーズです - ウェルカムメッセージ、パスワードリセット、通知など。これらのライブラリは、配信性を堅牢に保ちながら、面倒なく実現します。

- [ryanstubbs/flightmail](/awesome-plugins/flightmail) - FlightMail は Symfony Mailer を流暢な Flight フレンドリー API でラップします。シンプルな DSN 文字列を介して SMTP または主要プロバイダー経由で送信し、メッセージごとに異なるプロバイダーをルーティングし、Twig または Latte テンプレートで本文をレンダリングします。これは Flight の非公式プラグインであり、Flight チームによって保守されていません。

## ジョブキュー

ジョブキューは、タスクを非同期で処理するのに非常に役立ちます。メール送信、画像処理、またはリアルタイムで行う必要のないあらゆる処理に使用できます。

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue は、ジョブを非同期で処理するために使用できるライブラリです。beanstalkd、MySQL/MariaDB、SQLite、PostgreSQL で使用できます。

## セッション

セッションは API にはあまり役立ちませんが、Web アプリケーションを構築する場合、状態とログイン情報を維持するために極めて重要になることがあります。

- <span class="badge bg-primary">公式</span> [flightphp/session](/awesome-plugins/session) - 公式 Flight Session ライブラリ。セッションデータを保存および取得するために使用できるシンプルなセッションライブラリです。PHP の組み込みセッション処理を使用します。
- [Ghostff/Session](/awesome-plugins/ghost-session) - PHP Session Manager（ノンブロッキング、フラッシュ、セグメント、セッション暗号化）。セッションデータのオプションの暗号化/復号化に PHP open_ssl を使用します。

## テンプレート

テンプレートは、UI を備えたあらゆる Web アプリケーションの中核です。Flight で使用できるテンプレートエンジンは多数あります。

- <span class="badge bg-warning">非推奨</span> [flightphp/core View](/learn#views) - これはコアの一部である非常に基本的なテンプレートエンジンです。プロジェクトに数ページ以上ある場合は使用をおすすめしません。
- [latte/latte](/awesome-plugins/latte) - Latte は、非常に使いやすく、Twig や Smarty よりも PHP 構文に近いと感じられるフル機能のテンプレートエンジンです。独自のフィルタや関数を拡張して追加するのも非常に簡単です。
- [twig/twig](/awesome-plugins/twig) - Twig は、柔軟で高速かつ安全なテンプレートエンジンです（Symfony で使用されているものと同じです）。AI ツールや多くの PHP 開発者によく知られており、デフォルトで出力を自動エスケープし、拡張機能の巨大なエコシステムを持っています。
- [knifelemon/comment-template](/awesome-plugins/comment-template) - CommentTemplate は、アセットコンパイル、テンプレート継承、変数処理を備えた強力な PHP テンプレートエンジンです。自動 CSS/JS 圧縮、キャッシュ、Base64 エンコード、およびオプションの Flight PHP フレームワーク統合を特徴としています。

## WordPress 統合

WordPress プロジェクトで Flight を使いたいですか？ それに便利なプラグインがあります！

- [n0nag0n/wordpress-integration-for-flight-framework](/awesome-plugins/n0nag0n_wordpress) - この WordPress プラグインを使うと、WordPress と並行して Flight を実行できます。Flight フレームワークを使用して、カスタム API、マイクロサービス、さらには完全なアプリを WordPress サイトに追加するのに最適です。両方の良いとこ取りをしたい場合に非常に便利です！

## コントリビュート

共有したいプラグインがありますか？ リストに追加するにはプルリクエストを送信してください！