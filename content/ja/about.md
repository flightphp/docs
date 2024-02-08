# Flightとは何ですか？

FlightはPHP用の高速でシンプルで拡張可能なフレームワークです。非常に汎用性があり、あらゆる種類のWebアプリケーションの構築に使用できます。シンプルさを念頭に置いて構築され、理解しやすく使用しやすい方法で記述されています。

Flightは、PHPに新しく取り組み、Webアプリケーションの構築方法を学びたい初心者にとって優れたフレームワークです。また、Webアプリケーションを迅速かつ簡単に構築したい経験豊富な開発者にとっても優れたフレームワークです。これには、RESTful API、シンプルなWebアプリケーション、または複雑なWebアプリケーションを簡単に構築するための機能が備わっています。

```php
<?php

// if installed with composer
require 'vendor/autoload.php';
// or if installed manually by zip file
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'hello world!';
});

Flight::start();
```

それなりにシンプルでしょ？ [Flightについてもっと学ぶ！](learn)

## クイックスタート
Flight Frameworkを使用して始めるのに役立つ例のアプリケーションがあります。始め方に関する手順については、[flightphp/skeleton](https://github.com/flightphp/skeleton)に移動してください！Flightでできることの一部についてのインスピレーションを得るには、[examples](examples) ページを訪問してもいいでしょう。

# コミュニティ

Matrix上にいます！[#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org)でチャットしてください。

# 貢献

Flightに貢献する方法は2つあります：

1. [コアリポジトリ](https://github.com/flightphp/core)を訪れて、コアフレームワークに貢献することができます。
1. ドキュメントに貢献することができます。このドキュメントのウェブサイトは[GitHub](https://github.com/flightphp/docs)上にホスティングされています。エラーを見つけたり、何かをより良くするために改善したい場合は、遠慮せず修正してプルリクエストを送信してください！私たちは最新情報を提供しようと努力していますが、アップデートや言語の翻訳は歓迎されています。

# 必要条件

FlightはPHP 7.4以上が必要です。

**注意:** PHP 7.4がサポートされている理由は、執筆時点（2024年）において、一部のLTS Linuxディストリビューションでデフォルトのバージョンとして利用されているためです。PHP >8への移行を強制すると、これらのユーザーに多くの問題が発生します。このフレームワークはPHP >8もサポートしています。

# ライセンス

Flightは[MIT](https://github.com/flightphp/core/blob/master/LICENSE) ライセンスの下でリリースされています。