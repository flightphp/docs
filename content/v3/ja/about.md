# Flight PHP Framework

Flight は、PHP 向けの高速でシンプルで拡張可能なフレームワークです。迅速に作業を進めたく、面倒な手間をかけずに済ませたい開発者のために構築されています。クラシックなウェブアプリを構築する場合でも、超高速 API を構築する場合でも、最新の AI 駆動ツールを試す場合でも、Flight の低フットプリントとストレートフォワードな設計が完璧にフィットします。Flight はスリムであることを目的としていますが、エンタープライズアーキテクチャの要件にも対応可能です。

## Flight を選ぶ理由は？

- **初心者向け:** Flight は新しい PHP 開発者のための優れた出発点です。その明確な構造とシンプルな構文により、ボイラープレートに迷子になることなくウェブ開発を学べます。
- **プロに愛される:** 経験豊富な開発者は、Flight の柔軟性とコントロールを愛しています。小さなプロトタイプからフル機能のアプリまでスケールアップでき、フレームワークを切り替える必要はありません。
- **後方互換性:** あなたの時間を大切にします。Flight v3 は v2 の拡張として設計されており、ほぼすべての API を維持しています。私たちは革命ではなく進化を信じています。主要バージョンのリリースごとに「世界を壊す」ようなことはもうありません。
- **ゼロ依存:** Flight のコアは完全に依存関係がありません。ポリフィルも外部パッケージも、PSR インターフェースさえもありません。これにより、攻撃ベクターが少なくなり、フットプリントが小さく、上流の依存関係からの予期せぬ破壊的変更がありません。オプションのプラグインには依存関係が含まれる可能性がありますが、コアは常にスリムでセキュアです。
- **AI 指向:** Flight の最小オーバーヘッドとクリーンなアーキテクチャは、AI ツールと API の統合に理想的です。スマートチャットボット、AI 駆動ダッシュボードを構築する場合でも、単に実験する場合でも、Flight は邪魔をせず、重要なことに集中できます。[skeleton app](https://github.com/flightphp/skeleton) には、主要な AI コーディングアシスタント向けの事前構築された指示ファイルが最初から含まれています！ [Flight での AI の使用について詳しく学ぶ](/learn/ai)

## Video Overview

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 fs-5 text-center mt-5 pt-5">
      <span class="flight-title-video">シンプルでしょ？</span>
      <br>
      <a href="https://docs.flightphp.com/learn">ドキュメントで Flight について詳しく学ぶ</a>！
    </div>
  </div>
</div>

## Quick Start

高速で最小限のインストールを行うには、Composer でインストールします：

```bash
composer require flightphp/core
```

または、リポジトリの zip を [ここ](https://github.com/flightphp/core) からダウンロードできます。その後、以下のようないっそん 基本的な `index.php` ファイルを作成します：

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
  Flight::json([
	'hello' => 'world'
  ]);
});

Flight::start();
```

これだけです！ 基本的な Flight アプリケーションができました。このファイルを `php -S localhost:8000` で実行し、ブラウザで `http://localhost:8000` にアクセスすると出力が表示されます。

## Skeleton/Boilerplate App

Flight でプロジェクトを開始するための例アプリがあります。構造化されたレイアウト、基本的な設定がすべて整っており、Composer スクリプトもすぐに扱えます！ すぐに使えるプロジェクトとして [flightphp/skeleton](https://github.com/flightphp/skeleton) を確認するか、インスピレーションを得るために [examples](examples) ページを訪れてください。AI がどのようにフィットするかを知りたいですか？ [AI 駆動の例を探求](/learn/ai)。

## Installing the Skeleton App

簡単です！

```bash
# Create the new project
composer create-project flightphp/skeleton my-project/
# Enter your new project directory
cd my-project/
# Bring up the local dev-server to get started right away!
composer start
```

これにより、プロジェクト構造が作成され、必要なファイルが設定され、すぐに開始できます！

## High Performance

Flight は市販の PHP フレームワークの中で最も高速なもののひとつです。その軽量コアにより、オーバーヘッドが少なく速度が向上し、伝統的なアプリと現代の AI 駆動プロジェクトの両方に最適です。すべてのベンチマークを [TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks) で確認できます。

以下に、他の人気 PHP フレームワークとのベンチマークを示します。

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


## Flight and AI

AI をどのように扱うか気になりますか？ [発見](/learn/ai) して、Flight がお気に入りのコーディング LLM との作業を簡単にする方法を！

## Stability and Backwards Compatibility

あなたの時間を大切にします。私たちは、数年ごとに完全に自分たちを再発明し、開発者に壊れたコードと高価な移行を残すフレームワークをすべて見てきました。Flight は異なります。Flight v3 は v2 の拡張として設計されており、あなたが知って愛する API が剥ぎ取られていません。実際、ほとんどの v2 プロジェクトは v3 で変更なしに動作します。

Flight を安定した状態に保つことにコミットしており、フレームワークの修正ではなくアプリの構築に集中できます。

# Community

Matrix Chat に参加しています

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

そして Discord

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# Contributing

Flight に貢献する方法は 2 つあります：

1. [core repository](https://github.com/flightphp/core) を訪れてコアフレームワークに貢献する。
2. ドキュメントを改善するお手伝いをする！ このドキュメントウェブサイトは [Github](https://github.com/flightphp/docs) でホストされています。エラーが見つかったり、何かを改善したい場合、プルリクエストを送信してください。更新と新しいアイデアを歓迎します。特に AI と新しい技術周りで！

# Requirements

Flight は PHP 7.4 以上を必要とします。

**Note:** PHP 7.4 は、執筆時点（2024 年）で一部の LTS Linux ディストリビューションのデフォルトバージョンであるためサポートされています。PHP >8 への強制移行はそれらのユーザーにとって大きな負担となります。フレームワークは PHP >8 もサポートします。

# License

Flight は [MIT](https://github.com/flightphp/core/blob/master/LICENSE) ライセンスの下でリリースされています。