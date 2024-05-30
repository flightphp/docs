# すばらしいプラグイン

Flightは非常に拡張性があります。Flightアプリケーションに機能を追加するために使用できるプラグインがいくつかあります。一部はFlightチームによって公式にサポートされており、他にはマイクロ/ライトなライブラリがあります。

## キャッシュ

キャッシュはアプリケーションの高速化に役立ちます。Flightと使用できる多くのキャッシュライブラリがあります。

- [Wruczek/PHP-File-Cache](/awesome-plugins/php-file-cache) - 軽量でシンプルなスタンドアロンのPHPファイルキャッシングクラス

## CLI

CLIアプリケーションはアプリケーションと対話する素晴らしい方法です。これらを使用してコントローラを生成したり、すべてのルートを表示したりできます。

- [flightphp/runway](/awesome-plugins/runway) - RunwayはFlightアプリケーションの管理を支援するCLIアプリケーションです。

## Cookies

Cookieはクライアント側に小さなデータを保存する素晴らしい方法です。ユーザーの設定、アプリケーションの設定などを保存するために使用できます。

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookieは、クッキーを管理するためのシンプルで効果的なPHPライブラリです。

## デバッグ

ローカル環境で開発しているときにデバッグは重要です。いくつかのプラグインがデバッグ体験を向上させることができます。

- [tracy/tracy](/awesome-plugins/tracy) - Flightと使用できるフル機能のエラーハンドラです。アプリケーションのデバッグに役立ついくつかのパネルがあります。拡張や独自のパネルの追加も非常に簡単です。
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - [Tracy](/awesome-plugins/tracy)エラーハンドラと共に使用され、Flightプロジェクト専用のデバッグを支援するいくつかの追加パネルが追加されます。

## データベース

データベースはほとんどのアプリケーションの中心です。これはデータの保存と取得方法です。一部のデータベースライブラリはクエリを書くためのラッパーであり、一部は完全なORMです。

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Flightの公式PDOラッパーで、コアの一部です。これはクエリの書き方と実行を簡素化するためのシンプルなラッパーです。ORMではありません。
- [flightphp/active-record](/awesome-plugins/active-record) - 公式のFlight ActiveRecord ORM/Mapper。データの簡単な取得と保存のための素晴らしいライブラリです。

## 暗号化

機密データを保存するためには暗号化が重要です。データの暗号化と復号化はそれほど難しくありませんが、暗号化キーの適切な保存は難しいことがあります。暗号化キーを公開ディレクトリに保存したり、コードリポジトリにコミットしたりしないことが最も重要です。

- [defuse/php-encryption](/awesome-plugins/php-encryption) - データの暗号化と復号化に使用できるライブラリです。暗号化と復号化を開始するのはかなり簡単です。

## セッション

セッションはAPIにはあまり役立ちませんが、Webアプリケーションを構築する際には、状態を維持しログイン情報を管理するために重要です。

- [Ghostff/Session](/awesome-plugins/session) - PHPセッションマネージャ（ノンブロッキング、フラッシュ、セグメント、セッション暗号化）。セッションデータのオプションの暗号化/復号化にはPHP open_sslを使用します。

## テンプレート

テンプレートはUIを持つすべてのWebアプリケーションの基礎です。Flightと使用できるいくつかのテンプレーティングエンジンがあります。

- [flightphp/core View](/learn#views) - これはFlightのコアの一部である非常に基本的なテンプレートエンジンです。プロジェクトに数ページ以上ある場合は使用しないことをお勧めします。
- [latte/latte](/awesome-plugins/latte) - Latteは非常に使いやすく、TwigやSmartyよりもPHPの構文に近い非常に充実したテンプレートエンジンです。拡張や独自のフィルタや関数の追加も非常に簡単です。

## 貢献

共有したいプラグインがありますか？リストに追加するためのプルリクエストを送信してください！