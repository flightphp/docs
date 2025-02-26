# 素晴らしいプラグイン

Flightは非常に拡張可能です。Flightアプリケーションに機能を追加するために使用できるプラグインがいくつかあります。いくつかはFlightチームによって公式にサポートされており、他はスタートアップをサポートするためのマイクロ/ライトライブラリです。

## APIドキュメンテーション

APIドキュメンテーションは、すべてのAPIにとって重要です。開発者がAPIとどのように対話し、どのような返答を期待するかを理解するのに役立ちます。FlightプロジェクトのAPIドキュメンテーションを生成するためのいくつかのツールがあります。

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - FlightPHPを使用してAPIドキュメンテーションを生成する方法についてダニエル・シュライバーが執筆したブログ投稿。
- [Swagger UI](https://github.com/zircote/swagger-php) - Swagger UIは、FlightプロジェクトのAPIドキュメンテーションを生成するのに役立つ素晴らしいツールです。非常に使いやすく、ニーズに合わせてカスタマイズできます。これはSwaggerドキュメンテーションを生成するためのPHPライブラリです。

## 認証/認可

認証と認可は、誰が何にアクセスできるかを制御する必要があるすべてのアプリケーションにとって重要です。

- [flightphp/permissions](/awesome-plugins/permissions) - 公式Flightパーミッションライブラリ。このライブラリは、ユーザーおよびアプリケーションレベルの権限をアプリケーションに追加するための簡単な方法です。

## キャッシュ

キャッシュは、アプリケーションを高速化するための優れた方法です。Flightと一緒に使用できるキャッシングライブラリがいくつかあります。

- [flightphp/cache](/awesome-plugins/php-file-cache) - 軽量でシンプルなスタンドアロンPHPインファイルキャッシングクラス

## CLI

CLIアプリケーションは、アプリケーションと対話するための優れた方法です。コントローラを生成したり、すべてのルートを表示したり、その他のことに使用できます。

- [flightphp/runway](/awesome-plugins/runway) - Runwayは、Flightアプリケーションを管理するのに役立つCLIアプリケーションです。

## クッキー

クッキーは、クライアント側に小さなデータを保存するための優れた方法です。ユーザーの好みやアプリケーションの設定などを保存するために使用できます。

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookieは、クッキーを管理するためのシンプルで効果的な方法を提供するPHPライブラリです。

## デバッグ

デバッグは、ローカル環境で開発しているときに非常に重要です。デバッグ体験を向上させるために使用できるプラグインがいくつかあります。

- [tracy/tracy](/awesome-plugins/tracy) - これはFlightと一緒に使用できるフル機能のエラーハンドラーです。アプリケーションのデバッグを支援するためのいくつかのパネルがあります。自分のパネルを追加するのも非常に簡単です。
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - [Tracy](/awesome-plugins/tracy)エラーハンドラーと一緒に使用され、このプラグインはFlightプロジェクトのデバッグに特に役立ついくつかの追加パネルを追加します。

## データベース

データベースは、ほとんどのアプリケーションの中心です。これがデータを保存および取得する方法です。いくつかのデータベースライブラリは、クエリを書くための単なるラッパーであり、いくつかは完全に機能するORMです。

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - コアの一部である公式Flight PDOラッパー。これは、クエリを書いてそれを実行するプロセスを簡素化するのに役立つ単純なラッパーです。ORMではありません。
- [flightphp/active-record](/awesome-plugins/active-record) - 公式Flight ActiveRecord ORM/マッパー。データベース内のデータを簡単に取得および保存するための素晴らしい小さなライブラリです。
- [byjg/php-migration](/awesome-plugins/migrations) - プロジェクトのすべてのデータベース変更を追跡するためのプラグインです。

## 暗号化

暗号化は、機密データを保存するアプリケーションにとって重要です。データを暗号化および復号化することはそれほど難しくありませんが、暗号化キーを適切に保存することは[困難](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key)です。最も重要なのは、暗号化キーを公共のディレクトリに保存したり、コードリポジトリにコミットしたりしないことです。

- [defuse/php-encryption](/awesome-plugins/php-encryption) - これはデータを暗号化および復号化するために使用できるライブラリです。データの暗号化および復号化を開始するのは非常に簡単です。

## セッション

セッションはAPIにはあまり役立ちませんが、Webアプリケーションを構築するためには、セッションは状態を維持し、ログイン情報を保持するために重要です。

- [Ghostff/Session](/awesome-plugins/session) - PHPセッションマネージャー（ノンブロッキング、フラッシュ、セグメント、セッション暗号化）。PHP open_sslを使用して、セッションデータのオプションの暗号化/復号化を行います。

## テンプレーティング

テンプレーティングは、UIのあるWebアプリケーションの核です。Flightと一緒に使用できるテンプレートエンジンがいくつかあります。

- [flightphp/core View](/learn#views) - これはコアの一部である非常に基本的なテンプレートエンジンです。プロジェクト内にページが数ページ以上ある場合は使用しないことをお勧めします。
- [latte/latte](/awesome-plugins/latte) - Latteは、非常に使いやすく、TwigやSmartyよりもPHP構文に近い完全な機能を持つテンプレートエンジンです。独自のフィルターや関数を追加するのも非常に簡単です。

## 貢献

共有したいプラグインがありますか？リストに追加するためにプルリクエストを送信してください！