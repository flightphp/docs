# 素晴らしいプラグイン

Flight は非常に拡張性が高いです。Flight アプリケーションに機能を追加するために使用できるプラグインがいくつかあります。一部は Flight Team によって公式にサポートされており、他のものは開始するためのマイクロ/ライトライブラリです。

## API ドキュメント

API ドキュメントは任意の API にとって重要です。開発者が API とどのようにやり取りするかを理解し、返されるものを期待するのに役立ちます。Flight プロジェクトの API ドキュメントを生成するのに役立つツールがいくつかあります。

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Daniel Schreiber によるブログ投稿で、OpenAPI Spec を FlightPHP と使用して API ファーストアプローチで API を構築する方法について説明しています。
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI は、Flight プロジェクトの API ドキュメントを生成するのに役立つ優れたツールです。非常に使いやすく、ニーズに合わせてカスタマイズできます。これは Swagger ドキュメントを生成するための PHP ライブラリです。

## アプリケーション パフォーマンス監視 (APM)

アプリケーション パフォーマンス監視 (APM) は任意のアプリケーションにとって重要です。アプリケーションのパフォーマンスを理解し、ボトルネックがどこにあるかを特定するのに役立ちます。Flight で使用できる APM ツールがいくつかあります。
- <span class="badge bg-primary">official</span> [flightphp/apm](/awesome-plugins/apm) - Flight APM は、Flight アプリケーションを監視するために使用できるシンプルな APM ライブラリです。アプリケーションのパフォーマンスを監視し、ボトルネックを特定するのに役立ちます。

## Async

Flight はすでに高速なフレームワークですが、ターボエンジンを追加するとすべてがより楽しく（そして挑戦的）になります！

- [flightphp/async](/awesome-plugins/async) - 公式の Flight Async ライブラリです。このライブラリは、アプリケーションに非同期処理を追加するシンプルな方法です。Swoole/Openswoole を内部で使用して、タスクを非同期で実行するシンプルで効果的な方法を提供します。

## 認可/権限

認可と権限は、誰が何にアクセスできるかを制御する必要がある任意のアプリケーションにとって重要です。

- <span class="badge bg-primary">official</span> [flightphp/permissions](/awesome-plugins/permissions) - 公式の Flight Permissions ライブラリです。このライブラリは、アプリケーションにユーザーおよびアプリケーション レベルの権限を追加するシンプルな方法です。

## 認証

認証は、ユーザー ID を検証し、API エンドポイントを保護する必要があるアプリケーションに不可欠です。

- [firebase/php-jwt](/awesome-plugins/jwt) - PHP 用の JSON Web Token (JWT) ライブラリです。Flight アプリケーションでトークンベースの認証を実装するためのシンプルで安全な方法です。ステートレス API 認証、ミドルウェアによるルートの保護、OAuth スタイルの認可フローの実装に最適です。

## キャッシュ

キャッシュはアプリケーションを高速化する優れた方法です。Flight で使用できるキャッシュ ライブラリがいくつかあります。

- <span class="badge bg-primary">official</span> [flightphp/cache](/awesome-plugins/php-file-cache) - 軽量でシンプルなスタンドアロンの PHP イン-File キャッシュ クラス

## CLI

CLI アプリケーションは、アプリケーションとやり取りする優れた方法です。コントローラーを生成したり、すべてのルートを表示したり、その他多くの機能に使用できます。

- <span class="badge bg-primary">official</span> [flightphp/runway](/awesome-plugins/runway) - Runway は、Flight アプリケーションを管理するのに役立つ CLI アプリケーションです。

## Cookies

クッキーは、クライアント側に少量のデータを保存する優れた方法です。ユーザー設定、アプリケーション設定などを保存するために使用できます。

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie は、クッキーを管理するためのシンプルで効果的な PHP ライブラリです。

## デバッグ

ローカル環境で開発する際のデバッグは重要です。デバッグ体験を向上させるプラグインがいくつかあります。

- [tracy/tracy](/awesome-plugins/tracy) - Flight で使用できるフル機能のエラー ハンドラーです。アプリケーションをデバッグするのに役立つパネルがいくつかあります。また、拡張して独自のパネルを追加することも非常に簡単です。
- <span class="badge bg-primary">official</span> [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - [Tracy](/awesome-plugins/tracy) エラー ハンドラーと使用され、このプラグインは Flight プロジェクト特有のデバッグを支援するための追加パネルをいくつか追加します。

## データベース

データベースはほとんどのアプリケーションのコアです。これによりデータを保存および取得します。一部のデータベース ライブラリはクエリを書くための単なるラッパーであり、他のものはフル機能の ORM です。

- <span class="badge bg-primary">official</span> [flightphp/core PdoWrapper](/learn/pdo-wrapper) - コアの一部である公式の Flight PDO Wrapper です。クエリを書くプロセスを簡素化するためのシンプルなラッパーです。ORM ではありません。
- <span class="badge bg-primary">official</span> [flightphp/active-record](/awesome-plugins/active-record) - 公式の Flight ActiveRecord ORM/Mapper です。データベースからデータを簡単に取得および保存するための優れたライブラリです。
- [byjg/php-migration](/awesome-plugins/migrations) - プロジェクトのすべてのデータベース変更を追跡するためのプラグインです。

## 暗号化

暗号化は、機密データを保存する任意のアプリケーションにとって重要です。データを暗号化および復号化するのはそれほど難しくありませんが、暗号化キーを適切に保存することは [可能](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [です](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [が](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key)、難しい場合があります。最も重要なことは、暗号化キーを公開ディレクトリに保存したり、コード リポジトリにコミットしたりしないことです。

- [defuse/php-encryption](/awesome-plugins/php-encryption) - データを暗号化および復号化するために使用できるライブラリです。データを暗号化および復号化するのに開始するのはかなりシンプルです。

## ジョブ キュー

ジョブ キューは、タスクを非同期で処理するのに非常に役立ちます。これはメールの送信、画像の処理、またはリアルタイムで実行する必要のない任意のものです。

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue は、ジョブを非同期で処理するために使用できるライブラリです。beanstalkd、MySQL/MariaDB、SQLite、PostgreSQL と使用できます。

## セッション

セッションは API にはあまり有用ではありませんが、Web アプリケーションを構築する際には、状態とログイン情報を維持するために重要です。

- <span class="badge bg-primary">official</span> [flightphp/session](/awesome-plugins/session) - 公式の Flight Session ライブラリです。セッションデータを保存および取得するために使用できるシンプルなセッション ライブラリです。PHP の組み込みセッション処理を使用します。
- [Ghostff/Session](/awesome-plugins/ghost-session) - PHP Session Manager (非ブロッキング、フラッシュ、セグメント、セッション暗号化)。セッションデータのオプションの暗号化/復号化に PHP open_ssl を使用します。

## テンプレート

テンプレートは UI を備えた任意の Web アプリケーションのコアです。Flight で使用できるテンプレート エンジンがいくつかあります。

- <span class="badge bg-warning">deprecated</span> [flightphp/core View](/learn#views) - コアの一部である非常に基本的なテンプレート エンジンです。プロジェクトに数ページ以上ある場合は使用しないことを推奨します。
- [latte/latte](/awesome-plugins/latte) - Latte は、Twig や Smarty よりも PHP 構文に近いフル機能のテンプレート エンジンで、非常に使いやすいです。また、拡張して独自のフィルターと関数を追加することも非常に簡単です。
- [knifelemon/comment-template](/awesome-plugins/comment-template) - CommentTemplate は、アセット コンパイル、テンプレート継承、変数処理を備えた強力な PHP テンプレート エンジンです。自動 CSS/JS 最小化、キャッシュ、Base64 エンコーディング、およびオプションの Flight PHP フレームワーク統合を備えています。

## WordPress 統合

WordPress プロジェクトで Flight を使用したいですか？そのための便利なプラグインがあります！

- [n0nag0n/wordpress-integration-for-flight-framework](/awesome-plugins/n0nag0n_wordpress) - この WordPress プラグインにより、WordPress と並行して Flight を実行できます。カスタム API、マイクロサービス、または WordPress サイトに Flight フレームワークを使用してフルアプリを追加するのに最適です。両方の世界の最高のものを求める場合に非常に便利です！

## 貢献

共有したいプラグインがありますか？リストに追加するためのプルリクエストを送信してください！