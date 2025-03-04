# Flightとは何ですか？

Flightは、PHPのための高速でシンプル、かつ拡張可能なフレームワークです。非常に多用途で、あらゆる種類のウェブアプリケーションの構築に使用できます。シンプルさを念頭に置いて構築されており、理解しやすく使いやすい方法で記述されています。

Flightは、PHPに不慣れでウェブアプリケーションの構築を学びたい初心者にとって素晴らしいフレームワークです。また、ウェブアプリケーションに対してより多くのコントロールを望む経験豊富な開発者にも適したフレームワークです。RESTful API、シンプルなウェブアプリケーション、または複雑なウェブアプリケーションを容易に構築できるように設計されています。

## クイックスタート

```php
<?php

// composerでインストールした場合
require 'vendor/autoload.php';
// もしくは手動でzipファイルからインストールした場合
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'hello world!';
});

Flight::route('/json', function() {
  Flight::json(['hello' => 'world']);
});

Flight::start();
```

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTubeビデオプレーヤー" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 text-center mt-5 pt-5">
      <span class="fligth-title-video">シンプルでしょ？</span>
      <br>
      <a href="https://docs.flightphp.com/learn">ドキュメンテーションでFlightについてもっと学びましょう！</a>

    </div>
  </div>
</div>

### スケルトン/ボイラープレートアプリ

Flightフレームワークを使用して始めるためのサンプルアプリがあります。[flightphp/skeleton](https://github.com/flightphp/skeleton)にアクセスして、始め方の手順を確認してください！また、Flightでできることのインスピレーションを得るために[examples](examples)ページも訪れてみてください。

# コミュニティ

私たちはMatrixにいます。 [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org)で一緒にチャットしましょう。

# コントリビュート

Flightに貢献する方法は2つあります：

1. [core repository](https://github.com/flightphp/core)にアクセスして、コアフレームワークに貢献することができます。
1. ドキュメンテーションに貢献することもできます。このドキュメンテーションウェブサイトは[Github](https://github.com/flightphp/docs)にホストされています。エラーを見つけたり、何かをより良く肉付けしたい場合は、自由に修正してプルリクエストを送信してください！私たちはできるだけ最新の情報を維持しようとしていますが、更新や翻訳は大歓迎です。

# 要件

FlightはPHP 7.4以上を必要とします。

**注意:** PHP 7.4は、現在の執筆時点（2024年）で、いくつかのLTS Linuxディストリビューションのデフォルトバージョンであるためサポートされています。PHP >8への移行を強制すると、そのユーザーにとって多くの不満を引き起こすことになります。フレームワークはまた、PHP >8もサポートしています。

# ライセンス

Flightは[MIT](https://github.com/flightphp/core/blob/master/LICENSE)ライセンスの下でリリースされています。