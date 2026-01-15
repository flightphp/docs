# 素晴らしいプラグイン

Flight は非常に拡張性が高いです。Flight アプリケーションに機能を追加するために使用できるプラグインがいくつかあります。一部は Flight チームによって公式にサポートされており、他のものは開始に役立つマイクロ/ライトライブラリです。

## API ドキュメント

API ドキュメントはあらゆる API にとって重要です。開発者が API とどのようにやり取りするかを理解し、返されるものを期待するのに役立ちます。Flight プロジェクトの API ドキュメントを生成するのに役立つツールがいくつかあります。

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Daniel Schreiber によるブログ投稿で、OpenAPI Spec を FlightPHP と使用して API ファーストアプローチで API を構築する方法について説明しています。
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI は、Flight プロジェクトの API ドキュメントを生成するのに役立つ素晴らしいツールです。非常に使いやすく、ニーズに合わせてカスタマイズできます。これは Swagger ドキュメントを生成するための PHP ライブラリです。

## アプリケーション パフォーマンス モニタリング (APM)

アプリケーション パフォーマンス モニタリング (APM) はあらゆるアプリケーションにとって重要です。アプリケーションのパフォーマンスを理解し、ボトルネックがどこにあるかを特定するのに役立ちます。Flight で使用できる APM ツールがいくつかあります。
- <span class="badge bg-primary">official</span> [flightphp/apm](/awesome-plugins/apm) - Flight APM は、Flight アプリケーションを監視するために使用できるシンプルな APM ライブラリです。アプリケーションのパフォーマンスを監視し、ボトルネックを特定するのに役立ちます。

## Async

Flight はすでに高速なフレームワークですが、ターボエンジンを追加するとすべてがより楽しく（そして挑戦的）になります！

- [flightphp/async](/awesome-plugins/async) - 公式の Flight Async ライブラリです。このライブラリは、アプリケーションに非同期処理を追加するシンプルな方法です。バックエンドで Swoole/Openswoole を使用して、タスクを非同期で実行するシンプルで効果的な方法を提供します。

## 承認/権限

承認と権限は、誰が何にアクセスできるかを制御する必要があるあらゆるアプリケーションにとって重要です。

- <span class="badge bg-primary">official</span> [flightphp/permissions](/awesome-plugins/permissions) - 公式の Flight Permissions ライブラリです。このライブラリは、アプリケーションにユーザーおよびアプリケーション レベルの権限を追加するシンプルな方法です。

## 認証

認証は、ユーザー ID を検証し、API エンドポイントを保護する必要があるアプリケーションにとって不可欠です。

- [firebase/php-jwt](/awesome-plugins/jwt) - PHP 用の JSON Web Token (JWT) ライブラリです。Flight アプリケーションでトークンベースの認証を実装するシンプルで安全な方法です。ステートレス API 認証、ミドルウェアによるルートの保護、OAuth スタイルの承認フローの実装に最適です。

## キャッシュ

キャッシュはアプリケーションを高速化する素晴らしい方法です。Flight で使用できるキャッシュ ライブラリがいくつかあります。

- <span class="badge bg-primary">official</span> [flightphp/cache](/awesome-plugins/php-file-cache) - 軽量でシンプルなスタンドアロンの PHP イン ファイル キャッシュ クラス

## CLI

CLI アプリケーションは、アプリケーションとやり取りする素晴らしい方法です。コントローラーを生成したり、すべてのルートを表示したり、その他多くの機能に使用できます。

- <span class="badge bg-primary">official</span> [flightphp/runway](/awesome-plugins/runway) - Runway は、Flight アプリケーションを管理するのに役立つ CLI アプリケーションです。

## Cookies

クッキーはクライアント側に少量のデータを保存する素晴らしい方法です。ユーザー設定、アプリケーション設定などを保存するために使用できます。

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie は、クッキーを管理するシンプルで効果的な方法を提供する PHP ライブラリです。

## デバッグ

ローカル環境で開発する際のデバッグは重要です。デバッグ体験を向上させるプラグインがいくつかあります。

- [tracy/tracy](/awesome-plugins/tracy) - Flight で使用できるフル機能のエラー ハンドラーです。アプリケーションのデバッグに役立つパネルがいくつかあります。また、拡張して独自のパネルを追加することも非常に簡単です。
- <span class="badge bg-primary">official</span> [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - [Tracy](/awesome-plugins/tracy) エラー ハンドラーと使用され、Flight プロジェクトのデバッグに特化した追加のパネルをいくつか追加します。

## データベース

データベースはほとんどのアプリケーションのコアです。これによりデータを保存および取得します。一部のデータベース ライブラリはクエリを書くための単なるラッパーで、一部はフル機能の ORM です。

- <span class="badge bg-primary">official</span> [flightphp/core SimplePdo](/learn/simple-pdo) - コアの一部である公式の Flight PDO ヘルパーです。これは、`insert()`、`update()`、`delete()`、`transaction()` などの便利なヘルパー メソッドを備えたモダンなラッパーで、データベース操作を簡素化します。すべての結果は柔軟な配列/オブジェクト アクセス用にコレクションとして返されます。ORM ではなく、PDO をより良く作業するための方法です。
- <span class="badge bg-warning">deprecated</span> [flightphp/core PdoWrapper](/learn/pdo-wrapper) - コアの一部である公式の Flight PDO ラッパー（v3.18.0 以降非推奨）。SimplePdo を使用してください。
- <span class="badge bg-primary">official</span> [flightphp/active-record](/awesome-plugins/active-record) - 公式の Flight ActiveRecord ORM/マッパーです。データベースからデータを簡単に取得および保存するための優れた小さなライブラリです。
- [byjg/php-migration](/awesome-plugins/migrations) - プロジェクトのすべてのデータベース変更を追跡するためのプラグインです。

## 暗号化

暗号化は機密データを保存するあらゆるアプリケーションにとって重要です。データを暗号化および復号化するのはそれほど難しくありませんが、暗号化キーを適切に保存することは [可能](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [です](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [が](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key)、[困難](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key) です。最も重要なことは、暗号化キーを公開ディレクトリに保存したり、コード リポジトリにコミットしたりしないことです。

- [defuse/php-encryption](/awesome-plugins/php-encryption) - データを暗号化および復号化するために使用できるライブラリです。データを暗号化および復号化するのに開始するのはかなりシンプルです。

## ジョブ キュー

ジョブ キューは、タスクを非同期で処理するのに非常に役立ちます。これは、メールの送信、画像の処理、またはリアルタイムで実行する必要のないあらゆるものです。

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue は、ジョブを非同期で処理するために使用できるライブラリです。beanstalkd、MySQL/MariaDB、SQLite、PostgreSQL と使用できます。

## セッション

セッションは API にはあまり役立ちませんが、Web アプリケーションを構築する際には、状態とログイン情報を維持するために重要です。

- <span class="badge bg-primary">official</span> [flightphp/session](/awesome-plugins/session) - 公式の Flight Session ライブラリです。これは、セッションデータを保存および取得するために使用できるシンプルなセッション ライブラリです。PHP の組み込みセッション ハンドリングを使用します。
- [Ghostff/Session](/awesome-plugins/ghost-session) - PHP Session Manager（非ブロッキング、フラッシュ、セグメント、セッション暗号化）。セッションデータのオプションの暗号化/復号化に PHP open_ssl を使用します。

## テンプレート

テンプレートは UI を備えたあらゆる Web アプリケーションのコアです。Flight で使用できるテンプレート エンジンがいくつかあります。

- <span class="badge bg-warning">deprecated</span> [flightphp/core View](/learn#views) - コアの一部である非常に基本的なテンプレート エンジンです。プロジェクトに数ページ以上ある場合は使用しないことを推奨します。
- [latte/latte](/awesome-plugins/latte) - Latte は、Twig や Smarty よりも PHP 構文に近いフル機能のテンプレート エンジンで、非常に使いやすいです。また、拡張して独自のフィルターや関数を追加することも非常に簡単です。
- [knifelemon/comment-template](/awesome-plugins/comment-template) - CommentTemplate は、アセット コンパイル、テンプレート継承、変数処理を備えた強力な PHP テンプレート エンジンです。自動 CSS/JS 最小化、キャッシュ、Base64 エンコーディング、およびオプションの Flight PHP フレームワーク統合を備えています。

## WordPress 統合

WordPress プロジェクトで Flight を使用したいですか？ そのための便利なプラグインがあります！

- [n0nag0n/wordpress-integration-for-flight-framework](/awesome-plugins/n0nag0n_wordpress) - この WordPress プラグインにより、WordPress と並行して Flight を実行できます。カスタム API、マイクロサービス、または Flight フレームワークを使用して WordPress サイトにフル アプリを追加するのに最適です。両方の世界の最高のものを求める場合に超便利です！

## 貢献

共有したいプラグインがありますか？ リストに追加するためのプル リクエストを送信してください！