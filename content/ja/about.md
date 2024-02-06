# Flightとは何ですか？

FlightはPHPのための高速でシンプルで拡張可能なフレームワークです。非常に多様であり、あらゆる種類のWebアプリケーションの構築に使用できます。シンプルさを念頭に置いて構築され、理解しやすく使用しやすい方法で書かれています。

Flightは、PHPに新しくてWebアプリケーションの構築方法を学びたい初心者にとって優れたフレームワークです。また、迅速かつ簡単にWebアプリケーションを構築したい経験豊富な開発者にとっても優れたフレームワークです。簡単にRESTful API、シンプルなWebアプリケーション、または複雑なWebアプリケーションを構築できるように設計されています。

```php
<?php

// composerでインストールされている場合
require 'vendor/autoload.php';
// またはzipファイルで手動でインストールされている場合
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'hello world!';
});

Flight::start();
```

それほど単純ですよね？ [Flightについて詳しく学ぶ！](learn)

## クイックスタート
Flight Frameworkで始めるための例のアプリケーションがあります。 [flightphp/skeleton](https://github.com/flightphp/skeleton) に移動して始め方の手順を確認してください！また、[examples](examples) ページに移動してFlightでできるいくつかのことのインスピレーションを得ることもできます。

# コミュニティ

Matrixにいます！[#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org) でチャットしてください。

# 貢献

Flightに貢献する方法は2つあります：

1. [core repository](https://github.com/flightphp/core) を訪れることで、コアフレームワークに貢献することができます。
1. ドキュメントに貢献することができます。このドキュメントのウェブサイトは[GitHub](https://github.com/flightphp/docs) にホスティングされています。エラーを発見したり、より良い内容にするための提案があれば、修正してプルリクエストを送信してください！私たちは最新の情報を提供しようとしていますが、更新と言語翻訳は歓迎します。

# 必要条件

FlightにはPHP 7.4以上が必要です。

**注意:** PHP 7.4がサポートされている理由は、執筆時（2024年）に一部のLTS Linuxディストリビューションでデフォルトのバージョンであるためです。PHP >8への移行はこれらのユーザーに多くの頭痛を引き起こすでしょう。このフレームワークはまたPHP >8をサポートしています。

# ライセンス

Flightは[MIT](https://github.com/flightphp/core/blob/master/LICENSE) ライセンスの下でリリースされています。