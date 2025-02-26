# Flightとは？

Flightは、PHP用の高速でシンプル、拡張性のあるフレームワークです。非常に多用途で、あらゆる種類のウェブアプリケーションの構築に使用できます。シンプルさを念頭に置いて設計されており、理解しやすく使いやすい形で書かれています。

Flightは、PHPに不慣れでウェブアプリケーションの構築方法を学びたい初心者向けの素晴らしいフレームワークです。また、ウェブアプリケーションに対してより多くのコントロールを求める経験豊富な開発者にとっても優れたフレームワークです。RESTful API、シンプルなウェブアプリケーション、または複雑なウェブアプリケーションを簡単に構築できるように設計されています。

## クイックスタート

```php
<?php

// composerでインストールした場合
require 'vendor/autoload.php';
// または手動でzipファイルでインストールした場合
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
      <span class="fligth-title-video">十分シンプルですよね？</span>
      <br>
      <a href="https://docs.flightphp.com/learn">ドキュメントでFlightについてもっと学んでください！</a>

    </div>
  </div>
</div>

### スケルトン/ボイラープレートアプリ

Flightフレームワークを使い始めるのに役立つサンプルアプリがあります。[flightphp/skeleton](https://github.com/flightphp/skeleton)に移動して、始め方の指示を確認してください！また、Flightでできることのインスピレーションを得るために[examples](examples)ページも訪れてみてください。

# コミュニティ

私たちはMatrixでチャットをしています。[#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org)で参加してください。

# 貢献

Flightに貢献する方法は2つあります：

1. [core repository](https://github.com/flightphp/core)にアクセスして、コアフレームワークに貢献できます。
1. ドキュメントに貢献できます。このドキュメントウェブサイトは[Github](https://github.com/flightphp/docs)にホストされています。エラーに気付いたり、何かを改善したいと思ったら、自由に修正してプルリクエストを提出してください！私たちは事柄を把握しようとしていますが、更新や翻訳は歓迎です。

# 要件

FlightはPHP 7.4以上を必要とします。

**注:** PHP 7.4は、執筆時（2024年）で一部のLTS Linuxディストリビューションのデフォルトバージョンであるため、サポートされています。PHP 8以上への移行を強制すると、そのユーザーにとって多くの問題を引き起こすことになります。このフレームワークはPHP 8以上もサポートしています。

# ライセンス

Flightは、[MIT](https://github.com/flightphp/core/blob/master/LICENSE)ライセンスの下でリリースされています。