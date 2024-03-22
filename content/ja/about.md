# フライトとは何ですか？

フライトはPHP向けの高速でシンプルで拡張性のあるフレームワークです。非常に汎用性があり、あらゆる種類のWebアプリケーションの構築に使用できます。シンプリシティを念頭に置いて構築され、理解しやすく使用しやすいように記述されています。

フライトは、PHPに新しく取り組み、Webアプリケーションの構築方法を学びたい初心者向けの優れたフレームワークです。また、Webアプリケーションについてより多くの制御を求める経験豊富な開発者にとっても優れたフレームワークです。RESTful API、シンプルなWebアプリケーション、または複雑なWebアプリケーションを簡単に構築できるように設計されています。

## クイックスタート

```php
<?php

// if installed with composer
require 'vendor/autoload.php';
// or if installed manually by zip file
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'hello world!';
});

Flight::route('/json', function() {
  Flight::json(['hello' => 'world']);
});

Flight::start();
```

<div class="video-container">
	<iframe width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
</div>

十分シンプルですよね？[ドキュメントでフライトについて詳しく学んでください！](learn)

### スケルトン/ボイラープレートアプリ

フライトフレームワークを使って始めるのに役立つ例のアプリがあります。始め方については、[flightphp/skeleton](https://github.com/flightphp/skeleton)にアクセスしてください！また、Flightでできることのいくつかのインスピレーションに関する資料がある[examples](examples)ページもご覧いただけます。

# コミュニティ

私たちはMatrix上にいます！私たちとチャットするには、[#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org)にアクセスしてください。

# 貢献

Flightに貢献する方法は2つあります：

1. コアフレームワークに貢献することができます。[コアリポジトリ](https://github.com/flightphp/core)を訪れてください。
1. ドキュメントに貢献することができます。このドキュメントのウェブサイトは[Github](https://github.com/flightphp/docs)にホストされています。エラーを見つけた場合やより良い内容にする場合は、修正してプルリクエストを送信してください！私たちは常に最新情報を提供しようとしていますが、更新と言語翻訳は歓迎されています。

# 必要条件

Flightを利用するには、PHP 7.4以上が必要です。

**注意:** PHP 7.4がサポートされているのは、執筆時点（2024年）でいくつかのLTS Linuxディストリビューションのデフォルトバージョンであるためです。PHP >8に移行することは、これらのユーザーに多くの問題を引き起こす可能性があります。フレームワークはまた、PHP >8をサポートしています。

# ライセンス

Flightは[MIT](https://github.com/flightphp/core/blob/master/LICENSE)ライセンスのもとでリリースされています。