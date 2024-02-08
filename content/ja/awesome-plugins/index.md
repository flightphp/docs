# 素晴らしいプラグイン

Flightは非常に拡張性が高いです。Flightアプリケーションに機能を追加するために使用できるプラグインがいくつかあります。一部はFlightPHPチームによって公式にサポートされており、他のものはマイクロ/ライトライブラリで、開始するのに役立ちます。

## キャッシュ

キャッシュはアプリケーションのスピードを向上させる素晴らしい方法です。Flightと一緒に使用できるいくつかのキャッシングライブラリがあります。

- [Wruczek/PHP-File-Cache](/awesome-plugins/php-file-cache) - 軽量でシンプルなPHPファイル内キャッシュクラス

## デバッグ

ローカル環境で開発しているときにはデバッグが重要です。デバッグエクスペリエンスを向上させるいくつかのプラグインがあります。

- [tracy/tracy](/awesome-plugins/tracy) - Flightと一緒に使用できる完全機能のエラーハンドラです。アプリケーションのデバッグに役立ついくつかのパネルがあります。拡張や独自のパネルの追加も非常に簡単です。
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - [Tracy](/awesome-plugins/tracy)エラーハンドラと使用され、Flightプロジェクトのデバッグを手助けするいくつかの追加パネルを追加するプラグインです。

## データベース

データベースはほとんどのアプリケーションの中核です。これによってデータの格納と取得が行われます。一部のデータベースライブラリは単にクエリを書くラッパーであり、一部は完全なORMです。

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - コアの一部である公式のFlight PDOラッパー。クエリの記述と実行のプロセスを簡素化するための簡単なラッパーです。ORMではありません。
- [flightphp/active-record](/awesome-plugins/active-record) - 公式のFlight ActiveRecord ORM/Mapper。データの取得と保存を簡単に行うための素晴らしいライブラリ。

## セッション

APIにはあまり役立ちませんが、Webアプリケーションの構築には、セッションが状態の維持やログイン情報の管理に非常に重要です。

- [Ghostff/Session](/awesome-plugins/session) - PHPセッションマネージャー（ノンブロッキング、フラッシュ、セグメント、セッション暗号化）。オプションでセッションデータの暗号化/復号化にPHP open_sslを使用します。

## テンプレーティング

UIを持つ任意のWebアプリケーションの中核になるのがテンプレートです。Flightと一緒に使用できるいくつかのテンプレートエンジンがあります。

- [flightphp/core View](/learn#views) - コアの一部である非常に基本的なテンプレートエンジンです。プロジェクトに数ページ以上ある場合はお勧めしません。
- [latte/latte](/awesome-plugins/latte) - Latteは非常に使いやすく、TwigやSmartyよりもPHP構文に近いフル機能のテンプレートエンジンです。フィルターや関数を簡単に拡張して追加することも非常に簡単です。

## コントリビューション

共有したいプラグインがありますか？リストに追加するプルリクエストを送信してください！