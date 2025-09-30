# Flight について学ぶ

Flight は、PHP 向けの高速でシンプルで拡張可能なフレームワークです。非常に汎用性が高く、あらゆる種類のウェブアプリケーションを構築するために使用できます。
シンプルさを念頭に置いて構築されており、理解しやすく使用しやすい方法で記述されています。

> **注意:** `Flight::` を静的変数として使用する例と、`$app->` Engine オブジェクトを使用する例の両方を見ることになります。これらは互換性があり、どちらも使用可能です。コントローラー/ミドルウェア内の `$app` および `$this->app` が Flight チームのおすすめのアプローチです。

## コアコンポーネント

### [Routing](/learn/routing)

ウェブアプリケーションのルートを管理する方法を学びます。これにはルートのグループ化、ルートパラメータ、ミドルウェアが含まれます。

### [Middleware](/learn/middleware)

アプリケーションのリクエストとレスポンスをフィルタリングするためにミドルウェアを使用する方法を学びます。

### [Autoloading](/learn/autoloading)

アプリケーション内で独自のクラスをオートロードする方法を学びます。

### [Requests](/learn/requests)

アプリケーションでリクエストとレスポンスを処理する方法を学びます。

### [Responses](/learn/responses)

ユーザーにレスポンスを送信する方法を学びます。

### [HTML Templates](/learn/templates)

ビルトインのビューエンジンを使用して HTML テンプレートをレンダリングする方法を学びます。

### [Security](/learn/security)

アプリケーションを一般的なセキュリティ脅威から保護する方法を学びます。

### [Configuration](/learn/configuration)

アプリケーション向けにフレームワークを構成する方法を学びます。

### [Event Manager](/learn/events)

イベントシステムを使用してアプリケーションにカスタムイベントを追加する方法を学びます。

### [Extending Flight](/learn/extending)

独自のメソッドとクラスを追加してフレームワークを拡張する方法を学びます。

### [Method Hooks and Filtering](/learn/filtering)

メソッドと内部フレームワークメソッドにイベントフックを追加する方法を学びます。

### [Dependency Injection Container (DIC)](/learn/dependency-injection-container)

依存性注入コンテナ (DIC) を使用してアプリケーションの依存関係を管理する方法を学びます。

## ユーティリティクラス

### [Collections](/learn/collections)

コレクションはデータを保持し、配列またはオブジェクトとしてアクセスしやすくするために使用されます。

### [JSON Wrapper](/learn/json)

JSON のエンコードとデコードを一貫させるためのシンプルな関数がいくつかあります。

### [PDO Wrapper](/learn/pdo-wrapper)

PDO は時に必要以上に頭痛の種になることがあります。このシンプルなラッパークラスにより、データベースとのやり取りが大幅に簡単になります。

### [Uploaded File Handler](/learn/uploaded-file)

アップロードされたファイルを管理し、パーマネントな場所に移動するのを支援するシンプルなクラスです。

## 重要な概念

### [なぜフレームワークか？](/learn/why-frameworks)

フレームワークを使用する理由についての短い記事です。フレームワークを使用する前に、その利点を理解しておくのが良い考えです。

さらに、[@lubiana](https://git.php.fail/lubiana) によって作成された優れたチュートリアルがあります。Flight について特に詳細に触れていませんが、
このガイドはフレームワークを取り巻く主要な概念とその利点について理解するのに役立ちます。
チュートリアルは [ここ](https://git.php.fail/lubiana/no-framework-tutorial/src/branch/master/README.md) から見つかります。

### [Flight を他のフレームワークと比較](/learn/flight-vs-another-framework)

Laravel、Slim、Fat-Free、または Symfony などの他のフレームワークから Flight に移行する場合、このページは両者の違いを理解するのに役立ちます。

## その他のトピック

### [ユニットテスト](/learn/unit-testing)

Flight のコードを堅牢にするためのユニットテストの方法を学ぶためのガイドに従ってください。

### [AI & Developer Experience](/learn/ai)

Flight が AI ツールと現代の開発者ワークフローと連携して、より速くスマートにコーディングするのにどのように役立つかを学びます。

### [v2 から v3 への移行](/learn/migrating-to-v3)

後方互換性は大部分維持されていますが、v2 から v3 への移行時に知っておくべきいくつかの変更点があります。