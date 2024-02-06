# 素晴らしいプラグイン

Flightは非常に拡張性があります。Flightアプリケーションに機能を追加するために使用できるプラグインがいくつかあります。一部はFlightPHPチームによって公式にサポートされており、他にはスタートするのに役立つマイクロ/ライトライブラリがあります。

## キャッシュ

キャッシュはアプリケーションを高速化する素晴らしい方法です。Flightと一緒に使用できる複数のキャッシュライブラリがあります。

- [Wruczek/PHP-File-Cache](/awesome-plugins/php-file-cache) - 軽量でシンプルかつスタンドアロンのPHPインファイルキャッシュクラス

## デバッグ

ローカル環境で開発しているときにデバッグは重要です。いくつかのプラグインがデバッグ体験を向上させることができます。

- [tracy/tracy](/awesome-plugins/tracy) - これはFlightと一緒に使用できるフル機能のエラーハンドラです。アプリケーションのデバッグに役立つ多くのパネルがあります。また、簡単に拡張して独自のパネルを追加できます。
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - [Tracy](/awesome-plugins/tracy)エラーハンドラと共に使用され、このプラグインはFlightプロジェクトのデバッグを支援するためのいくつかの追加パネルを追加します。

## データベース

データベースはほとんどのアプリケーションの中核です。これはデータの格納と取得方法です。一部のデータベースライブラリはクエリを書くラッパーであり、一部は完全なORMです。

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Flightの公式PDOラッパーで、コアの一部です。これはクエリの記述と実行を簡素化するためのシンプルなラッパーです。ORMではありません。
- [flightphp/active-record](/awesome-plugins/active-record) - 公式Flight ActiveRecord ORM/Mapper。データベースでデータを簡単に取得および格納するための優れた小さなライブラリです。

## セッション

セッションはAPIにはあまり役立ちませんが、Webアプリケーションを構築する場合、状態とログイン情報を維持するために重要です。

- [Ghostff/Session](/awesome-plugins/session) - PHPセッションマネージャ（非同期、フラッシュ、セグメント、セッション暗号化）。オプションでセッションデータの暗号化/復号化にPHP open_sslを使用します。

## テンプレート

テンプレートはUIを備えたすべてのWebアプリケーションの中核です。Flightと一緒に使用できる多くのテンプレートエンジンがあります。

- [flightphp/core View](/learn#views) - これはコアの一部である非常に基本的なテンプレートエンジンです。プロジェクトに複数のページがある場合は使用しないことをお勧めします。
- [latte/latte](/awesome-plugins/latte) - Latteは非常に使いやすく、TwigやSmartyよりもPHP構文に近いフル機能のテンプレートエンジンです。また、独自のフィルターと関数を拡張して追加することが非常に簡単です。

## 貢献

共有したいプラグインはありますか？それをリストに追加するためのプルリクエストを送信してください！