# フライトとは何ですか？

フライトはPHP向けの高速でシンプルで拡張可能なフレームワークです。非常に汎用性があり、どんな種類のWebアプリケーションでも構築に使えます。シンプルさを重視して構築され、理解しやすく使いやすい方法で書かれています。

フライトは、PHPに新しい人やWebアプリケーションの構築方法を学びたい人にとって最適な初心者向けフレームワークです。経験豊富な開発者が自分のWebアプリケーションをよりコントロールしたい場合にも優れたフレームワークです。RESTful API、シンプルなWebアプリケーション、または複雑なWebアプリケーションを簡単に構築できるように設計されています。

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

Flight::start();
```

簡単ですね？ [ドキュメントでフライトについて詳しく学ぶ！](learn)

### スケルトン/ボイラープレートアプリ

フライトフレームワークを使い始めるのに役立つ例のアプリがあります。[flightphp/skeleton](https://github.com/flightphp/skeleton)に移動して、始め方の手順を確認してください！また、[examples](examples)ページを訪れて、フライトでできるいくつかのアイデアについてのインスピレーションを得ることもできます。

# コミュニティ

私たちはMatrix上にいます！[チャットで話す](https://matrix.to/#/#flight-php-framework:matrix.org)。

# 貢献

フライトに貢献する方法は2つあります：

1. [コアリポジトリ](https://github.com/flightphp/core)を訪れることで、コアフレームワークに貢献できます。
1. ドキュメントに貢献することもできます。このドキュメントのウェブサイトは[GitHub](https://github.com/flightphp/docs)でホストされています。エラーを見つけた場合やより良い内容を追記したい場合は、修正してプルリクエストを送信してください！私たちは最新情報を追いかけようとしていますが、アップデートや言語の翻訳は歓迎します。

# 必要条件

フライトはPHP 7.4以上が必要です。

**注意:** PHP 7.4は、執筆時点（2024年）で一部のLTS Linuxディストリビューションのデフォルトバージョンであるためサポートされています。PHP >8への移行はこれらのユーザーにとって多くの問題を引き起こす可能性があるためです。フレームワークはまた、PHP >8もサポートしています。

# ライセンス

フライトは[MIT](https://github.com/flightphp/core/blob/master/LICENSE)ライセンスのもとでリリースされています。