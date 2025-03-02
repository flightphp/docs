# すごいプラグイン

Flightは非常に拡張可能です。あなたのFlightアプリケーションに機能を追加するために使用できるプラグインがいくつかあります。いくつかはFlightチームによって正式にサポートされており、その他は始めるのを助けるためのマイクロ/ライトライブラリです。

## APIドキュメンテーション

APIドキュメンテーションは、どのAPIにとっても重要です。それは開発者があなたのAPIとどのように対話するか、そして何を期待するかを理解するのを助けます。あなたのFlightプロジェクトのためにAPIドキュメンテーションを生成するのを助けるためにいくつかのツールが利用可能です。

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Daniel Schreiberによる、FlightPHPとOpenAPI Specを使用してAPIを構築する方法についてのブログ投稿。
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UIは、あなたのFlightプロジェクトのためにAPIドキュメンテーションを生成するのを助けるための素晴らしいツールです。使いやすく、あなたのニーズに合わせてカスタマイズできます。これはSwaggerドキュメントを生成するためのPHPライブラリです。

## 認証/認可

認証および認可は、誰が何にアクセスできるかの制御が必要なアプリケーションにとって重要です。

- [flightphp/permissions](/awesome-plugins/permissions) - 公式Flight Permissionsライブラリ。このライブラリは、あなたのアプリケーションにユーザーとアプリケーションレベルの権限を追加するためのシンプルな方法です。

## キャッシング

キャッシングは、アプリケーションの速度を向上させる素晴らしい方法です。Flightと一緒に使用できるいくつかのキャッシングライブラリがあります。

- [flightphp/cache](/awesome-plugins/php-file-cache) - 軽量でシンプルでスタンドアロンのPHPインファイルキャッシングクラス

## CLI

CLIアプリケーションは、あなたのアプリケーションと対話するための素晴らしい方法です。これらを使用して、コントローラーを生成したり、すべてのルートを表示したりできます。

- [flightphp/runway](/awesome-plugins/runway) - Runwayは、あなたのFlightアプリケーションを管理するのを助けるCLIアプリケーションです。

## クッキー

クッキーは、クライアント側に小さなデータビットを保存するための素晴らしい方法です。ユーザーの好み、アプリケーション設定などを保存するために使用できます。

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookieは、クッキーを管理するためのシンプルで効果的な方法を提供するPHPライブラリです。

## デバッグ

デバッグは、ローカル環境で開発しているときに重要です。あなたのデバッグ体験を向上させるいくつかのプラグインがあります。

- [tracy/tracy](/awesome-plugins/tracy) - これはFlightと一緒に使用できる完全なエラーハンドラーです。あなたのアプリケーションをデバッグするのを助けるいくつかのパネルがあります。また、自分のパネルを拡張して追加するのも非常に簡単です。
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - [Tracy](/awesome-plugins/tracy)エラーハンドラーと一緒に使用されるこのプラグインは、Flightプロジェクトのデバッグを助けるいくつかの追加パネルを追加します。

## データベース

データベースはほとんどのアプリケーションの中核です。これはデータを保存し、取得する方法です。いくつかのデータベースライブラリは単にクエリを書くためのラッパーであり、一部は本格的なORMです。

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - コアの一部である公式Flight PDO Wrapper。これは、クエリを書いて実行するプロセスを簡素化するためのシンプルなラッパーです。ORMではありません。
- [flightphp/active-record](/awesome-plugins/active-record) - 公式Flight ActiveRecord ORM/マッパー。データベースにデータを簡単に取得して保存するための素晴らしい小さなライブラリです。
- [byjg/php-migration](/awesome-plugins/migrations) - プロジェクトのすべてのデータベース変更を追跡するためのプラグインです。

## 暗号化

暗号化は、機密データを保存するアプリケーションにとって重要です。データを暗号化および復号化するのはあまり難しくありませんが、暗号化キーを適切に保存することは[困難](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [で](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [ある](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key)。最も重要なことは、暗号化キーを公開ディレクトリに保存したり、コードリポジトリにコミットしたりしないことです。

- [defuse/php-encryption](/awesome-plugins/php-encryption) - これはデータを暗号化および復号化するために使用できるライブラリです。暗号化および復号化を開始するのは非常に簡単です。

## ジョブキュー

ジョブキューは、非同期的にタスクを処理するのに非常に便利です。これはメール送信、画像処理、またはリアルタイムで行う必要がない何かを含むことができます。

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queueは、ジョブを非同期に処理するために使用できるライブラリです。beanstalkd、MySQL/MariaDB、SQLite、PostgreSQLと一緒に使用できます。

## セッション

セッションはAPIにはあまり役立ちませんが、Webアプリケーションを構築するためには、状態とログイン情報を維持するために重要です。

- [Ghostff/Session](/awesome-plugins/session) - PHPセッションマネージャー（ノンブロッキング、フラッシュ、セグメント、セッション暗号化）。オプションのセッションデータの暗号化/復号化にはPHP open_sslを使用します。

## テンプレーティング

テンプレーティングは、UIを持つWebアプリケーションの中核です。Flightと一緒に使用できるテンプレーティングエンジンがいくつかあります。

- [flightphp/core View](/learn#views) - これはコアの一部である非常に基本的なテンプレーティングエンジンです。プロジェクトに数ページ以上ある場合は使用しないことが推奨されます。
- [latte/latte](/awesome-plugins/latte) - Latteは、非常に使いやすく、TwigやSmartyよりもPHPの構文に近い完全な機能を持つテンプレーティングエンジンです。また、非常に簡単に拡張し、自分のフィルターや関数を追加できます。

## 貢献

共有したいプラグインがありますか？それをリストに追加するためにプルリクエストを送信してください！