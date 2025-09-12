# Flightとは？

Flightは、PHPのための高速でシンプル、かつ拡張可能なフレームワークです。
Flightを使用すると、迅速かつ容易にRESTfulウェブアプリケーションを構築できます。

``` php
require 'flight/Flight.php';

// ルートを定義します
Flight::route('/', function(){
  echo 'hello world!';
});

// アプリケーションを開始します
Flight::start();
```

[詳細はこちら](learn)

# 要件

FlightはPHP 7.4以上が必要です。

# ライセンス

Flightは[MIT](https://github.com/mikecao/flight/blob/master/LICENSE)ライセンスの下で配布されています。

# コミュニティ

私たちはMatrixにいます！ [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org)でチャットしましょう。

# 貢献

このウェブサイトは[Github](https://github.com/mikecao/flightphp.com)にホストされています。
更新や言語翻訳を歓迎します。
