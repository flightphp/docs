# Flightとは？

Flightは、PHP用の高速でシンプル、拡張可能なフレームワークです。非常に多用途で、あらゆる種類のウェブアプリケーションを構築するために使用できます。シンプルさを念頭に置いて構築されており、理解しやすく、使いやすい形式で記述されています。

Flightは、PHPを学び始めたばかりの初心者にとって素晴らしいフレームワークであり、ウェブアプリケーションの構築を学びたい方に最適です。また、ウェブアプリケーションに対してより多くの制御を求める経験豊富な開発者にも素晴らしいフレームワークです。RESTful API、シンプルなウェブアプリケーション、または複雑なウェブアプリケーションを簡単に構築できるように設計されています。

## クイックスタート

まず、Composerでインストールします。

```bash
composer require flightphp/core
```

または、リポジトリのzipを[こちら](https://github.com/flightphp/core)からダウンロードできます。その後、以下のような基本的な `index.php` ファイルを持つことになります。

```php
<?php

// composerでインストールした場合
require 'vendor/autoload.php';
// zipファイルで手動インストールした場合
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'hello world!';
});

Flight::route('/json', function() {
  Flight::json(['hello' => 'world']);
});

Flight::start();
```

これで完了です！基本的なFlightアプリケーションが出来上がりました。 `php -S localhost:8000` を使用してこのファイルを実行し、ブラウザで `http://localhost:8000` を訪れて出力を確認できます。

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube動画プレーヤー" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 text-center mt-5 pt-5">
      <span class="fligth-title-video">十分シンプルですよね？</span>
      <br>
      <a href="https://docs.flightphp.com/learn">ドキュメントでFlightについてもっと学びましょう！</a>

    </div>
  </div>
</div>

## 速いですか？

はい！Flightは速いです。利用可能な最も高速なPHPフレームワークの一つです。すべてのベンチマークは[TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks)で確認できます。

以下は、他の人気のPHPフレームワークとのベンチマークです。

| フレームワーク | プレーン・テキストのリクエスト/sec | JSONリクエスト/sec |
| --------- | ------------ | ------------ |
| Flight      | 190,421    | 182,491 |
| Yii         | 145,749    | 131,434 |
| Fat-Free    | 139,238	   | 133,952 |
| Slim        | 89,588     | 87,348  |
| Phalcon     | 95,911     | 87,675  |
| Symfony     | 65,053     | 63,237  |
| Lumen	      | 40,572     | 39,700  |
| Laravel     | 26,657     | 26,901  |
| CodeIgniter | 20,628     | 19,901  |

## スケルトン/ボイラープレートアプリ

Flightフレームワークを使い始めるためのサンプルアプリがあります。 [flightphp/skeleton](https://github.com/flightphp/skeleton)にアクセスして、使い始める方法を確認してください！また、Flightでできることのインスピレーションを得られる[例](examples)のページも訪れてみてください。

# コミュニティ

私たちはMatrixチャットで活動しています。

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

そしてDiscordでも。

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# 貢献

Flightに貢献する方法は2つあります：

1. [コアリポジトリ](https://github.com/flightphp/core)を訪問して、コアフレームワークに貢献できます。
2. ドキュメントに貢献できます。このドキュメントサイトは、[Github](https://github.com/flightphp/docs)にホストされています。エラーに気付いたり、より良い内容を充実させたい場合は、遠慮なく修正してプルリクエストを送信してください！私たちは物事を把握しようとしていますが、更新や言語翻訳を歓迎します。

# 要件

FlightはPHP 7.4以上を必要とします。

**注意：** PHP 7.4は、現在の執筆時点（2024年）でいくつかのLTS Linuxディストリビューションのデフォルトバージョンであるため、サポートされています。PHP >8への移行を強要すると、そのユーザーには多くの困難を引き起こすことになります。このフレームワークは、PHP >8もサポートしています。

# ライセンス

Flightは[MIT](https://github.com/flightphp/core/blob/master/LICENSE)ライセンスの下でリリースされています。