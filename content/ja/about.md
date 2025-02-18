# Flightとは？

Flightは、PHPのための高速でシンプル、拡張可能なフレームワークです。非常に多用途で、あらゆる種類のWebアプリケーションを構築するために使用できます。シンプルさを念頭に置いて構築されており、理解しやすく使いやすいように書かれています。

Flightは、PHPに不慣れでWebアプリケーションの構築方法を学びたい初心者にとって素晴らしいフレームワークです。また、Webアプリケーションに対してより多くの制御を求める経験豊富な開発者にも最適なフレームワークです。RESTful API、シンプルなWebアプリケーション、あるいは複雑なWebアプリケーションを簡単に構築できるように設計されています。

## クイックスタート

```php
<?php

// composerでインストールされた場合
require 'vendor/autoload.php';
// zipファイルで手動インストールされた場合
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
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 text-center mt-5 pt-5">
      <span class="fligth-title-video">十分シンプルですよね？</span>
      <br>
      <a href="https://docs.flightphp.com/learn">ドキュメントでFlightについてもっと学んでください！</a>

    </div>
  </div>
</div>

### スケルトン/ボイラープレートアプリ

Flightフレームワークを使い始めるのに役立つサンプルアプリがあります。使い始めるための指示については[flightphp/skeleton](https://github.com/flightphp/skeleton)にアクセスしてください！また、Flightでできることに関するインスピレーションを得るために[examples](examples)ページを訪れることもできます。

# コミュニティ

Matrixで私たちとチャットしましょう [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org)。

# 貢献

Flightに貢献する方法は二つあります：

1. [コアリポジトリ](https://github.com/flightphp/core)を訪れて、コアフレームワークに貢献できます。 
1. ドキュメントに貢献できます。このドキュメントウェブサイトは[Github](https://github.com/flightphp/docs)でホストされています。エラーに気づいたり、より良い説明を加えたい場合は、自在に修正してプルリクエストを送信してください！私たちは常に最新情報を追っているように努めていますが、更新や言語翻訳は歓迎です。

# 要件

FlightはPHP 7.4以上を必要とします。

**注意：** 現在執筆中（2024）の時点で、PHP 7.4は一部のLTS Linuxディストリビューションのデフォルトバージョンであるため、PHP 7.4がサポートされています。PHP >8への移行を強制すると、そのユーザーにとって多くの問題が発生する可能性があります。このフレームワークはPHP >8もサポートしています。

# ライセンス

Flightは[MIT](https://github.com/flightphp/core/blob/master/LICENSE)ライセンスのもとでリリースされています。