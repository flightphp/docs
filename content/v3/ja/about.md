# Flight とは？

Flight は、速く、シンプルで、拡張可能な PHP フレームワークです。開発者が素早く作業を完了させたい場合に最適で、一切の面倒なことを避けられます。クラシックなウェブアプリ、驚異的に速い API、または最新の AI 駆動ツールの実験など、Flight の低負荷で直感的な設計は、さまざまな用途にぴったりです。

## Flight を選ぶ理由？

- **初心者向け:** Flight は、PHP の新しい開発者にとって素晴らしい出発点です。明確な構造とシンプルな構文により、余計なコードに惑わされずにウェブ開発を学べます。
- **プロの愛用:** 経験豊富な開発者は、Flight の柔軟性と制御性に魅了されます。小規模なプロトタイプから本格的なアプリまで、スケールアップ可能で、他のフレームワークに切り替える必要はありません。
- **AI 対応:** Flight の最小限のオーバーヘッドとクリーンなアーキテクチャは、AI ツールや API の統合に理想的です。スマートなチャットボット、AI 駆動のダッシュボード、または単なる実験など、Flight は邪魔をせずに本質に集中できます。 [AI を Flight で使用する方法について詳しく知る](/learn/ai)

## クイックスタート

まず、Composer でインストールします：

```bash
composer require flightphp/core
```

または、リポジトリの ZIP を [こちら](https://github.com/flightphp/core) からダウンロードできます。次に、基本的な `index.php` ファイルを作成します：

```php
<?php

// Composer でインストールした場合
require 'vendor/autoload.php';
// または ZIP ファイルで手動インストールした場合
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'hello world!';
});

Flight::route('/json', function() {
  Flight::json(['hello' => 'world']);
});

Flight::start();
```

以上です！これで基本的な Flight アプリケーションが完成します。このファイルを `php -S localhost:8000` で実行し、ブラウザで `http://localhost:8000` を訪れると、出力を確認できます。

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 text-center mt-5 pt-5">
      <span class="fligth-title-video">十分シンプルですね？</span>
      <br>
      <a href="https://docs.flightphp.com/learn">Flight のドキュメントでさらに学ぼう！</a>
      <br>
      <a href="/learn/ai" class="btn btn-primary mt-3">Flight が AI を簡単にする方法を発見</a>
    </div>
  </div>
</div>

## 速いですか？

もちろんです！ Flight は、PHP フレームワークの中でも最も速いもののひとつです。軽量なコアにより、オーバーヘッドが少なく、速度が向上します。これは、伝統的なアプリや現代の AI 駆動プロジェクトに最適です。ベンチマークは [TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks) で確認できます。

以下は、他の人気の PHP フレームワークとのベンチマークです。

| Framework | Plaintext Reqs/sec | JSON Reqs/sec |
| --------- | ------------ | ------------ |
| Flight      | 190,421    | 182,491 |
| Yii         | 145,749    | 131,434 |
| Fat-Free    | 139,238    | 133,952 |
| Slim        | 89,588     | 87,348  |
| Phalcon     | 95,911     | 87,675  |
| Symfony     | 65,053     | 63,237  |
| Lumen       | 40,572     | 39,700  |
| Laravel     | 26,657     | 26,901  |
| CodeIgniter | 20,628     | 19,901  |

## スケルトン/ボイラープレートアプリ

Flight の開始に役立つ例のアプリがあります。[flightphp/skeleton](https://github.com/flightphp/skeleton) を確認して、すぐに使えるプロジェクトを入手するか、[examples](examples) ページでインスピレーションを得てください。AI の統合に興味がある場合？ [AI 駆動の例を探す](/learn/ai)。

# コミュニティ

Matrix Chat で参加できます

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

そして Discord も

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# コントリビュート

Flight に貢献する方法は 2 つあります：

1. コアフレームワークに貢献する： [core repository](https://github.com/flightphp/core) を訪れてください。
2. ドキュメントを改善する！ このドキュメントウェブサイトは [Github](https://github.com/flightphp/docs) でホストされています。エラーを発見したり、改善したい場合、プルリクエストを送信してください。更新や新しいアイデア、特に AI と新技術に関するものを大歓迎です！

# 必要条件

Flight には PHP 7.4 以上が必要です。

**注意:** PHP 7.4 は、2024 年現在でいくつかの LTS Linux ディストリビューションのデフォルトバージョンであるため、サポートされています。PHP >8 への移行を強制すると、ユーザーに問題を引き起こす可能性があるためです。フレームワークは PHP >8 もサポートしています。

# ライセンス

Flight は [MIT](https://github.com/flightphp/core/blob/master/LICENSE) ライセンスで公開されています。