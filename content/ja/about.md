# フライトとは何ですか？

フライトは、PHP向けの高速でシンプルで拡張性のあるフレームワークです。非常に汎用性が高く、あらゆる種類のWebアプリケーション構築に使用できます。単純さを念頭に置いて構築され、理解しやすく使いやすい方法で記述されています。

フライトは、PHPに新しい人やWebアプリケーション構築の方法を学びたい人にとって素晴らしい初心者向けフレームワークです。また、Webアプリケーションに対してより多くの制御を望む経験豊富な開発者にとっても優れたフレームワークです。RESTful API、シンプルなWebアプリケーション、または複雑なWebアプリケーションを簡単に構築できるように設計されています。

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

簡単ですね？[ドキュメントでフライトについて詳しく学ぶ！](learn)

### スケルトン/ボイラープレートアプリ

フライトフレームワークを使って始めるのに役立つ例のアプリケーションがあります。[flightphp/skeleton](https://github.com/flightphp/skeleton) に移動して開始方法に関する指示を参照してください！また、[examples](examples) ページを訪れると、フライトで行えるいくつかのアイデアを参考にできます。

# コミュニティ

Matrix Chatでの参加をお待ちしています。[#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org) でチャットしましょう。

# 貢献

フライトに貢献する方法は2つあります：

1. [コアリポジトリ](https://github.com/flightphp/core) を訪れてコアフレームワークに貢献できます。
1. ドキュメントに貢献することもできます。このドキュメントのウェブサイトは[GitHub](https://github.com/flightphp/docs) でホストされています。エラーを見つけたり、よりよい説明ができる場合は、修正してプルリクエストを送信してください！私たちは最新情報を提供しようと努めていますが、更新と言語翻訳は歓迎します。

# 必要条件

フライトはPHP 7.4以上が必要です。

**注意：** PHP 7.4がサポートされている理由は、執筆時点（2024年）で一部のLTS Linuxディストリビューションでデフォルトバージョンとして採用されているためです。PHP >8への移行を強制すると、これらのユーザーに多くの問題が生じる可能性があります。また、このフレームワークはPHP >8もサポートしています。

# ライセンス

フライトは[MIT](https://github.com/flightphp/core/blob/master/LICENSE) ライセンスの下でリリースされています。