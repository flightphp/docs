# すばらしいプラグイン

Flightは非常に拡張可能です。Flightアプリケーションに機能を追加するために使用できるプラグインがいくつかあります。いくつかはFlightチームによって公式にサポートされており、他には開始を手助けするためのミクロ/ライトライブラリがあります。

## キャッシュ

キャッシュはアプリケーションの高速化に役立つ方法です。Flightと使用できるキャッシュライブラリがいくつかあります。

- [Wruczek/PHP-File-Cache](/awesome-plugins/php-file-cache) - 軽量でシンプルなPHPファイル内キャッシュクラス

## デバッグ

開発を行うローカル環境ではデバッグが重要です。いくつかのプラグインを使用するとデバッグ体験を向上させることができます。

- [tracy/tracy](/awesome-plugins/tracy) - Flightと使用できるフル機能のエラーハンドラ。アプリケーションのデバッグに役立ついくつかのパネルがあります。また、容易に拡張して独自のパネルを追加できます。
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - [Tracy](/awesome-plugins/tracy) エラーハンドラと共に使用し、Flightプロジェクトのデバッグを支援する追加パネルが含まれています。

## データベース

データベースはほとんどのアプリケーションの中心です。これによりデータの保存と取得が可能になります。一部のデータベースライブラリはクエリの記述や実行を簡素化するラッパーであり、一部は完全なORMです。

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Flightの公式PDOラッパーで、コアの一部です。クエリの記述と実行のプロセスを簡単にするためのシンプルなラッパーです。ORMではありません。
- [flightphp/active-record](/awesome-plugins/active-record) - Flightの公式ActiveRecord ORM/Mapper。データの簡単な取得と保存に適した優れたライブラリ。

## セッション

APIにはあまり役立たないが、Webアプリケーションの構築にはセッションが状態とログイン情報の維持に重要です。

- [Ghostff/Session](/awesome-plugins/session) - PHPセッションマネージャー（非同期、フラッシュ、セグメント、セッション暗号化）。セッションデータの暗号化/復号化のためにPHP open_sslを使用します。

## テンプレーティング

テンプレートはUIを持つWebアプリケーションにとって重要です。Flightと使用できるいくつかのテンプレートエンジンがあります。

- [flightphp/core View](/learn#views) - コアの一部である非常に基本的なテンプレートエンジンです。プロジェクト内に複数のページがある場合は推奨されません。
- [latte/latte](/awesome-plugins/latte) - PHP構文に近い感覚で非常に使いやすい完全機能のテンプレートエンジンです。TwigやSmartyよりも簡単に拡張して独自のフィルターや関数を追加できます。

## 貢献

共有したいプラグインがありますか？リストに追加するためにプルリクエストを送信してください！