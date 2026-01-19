# Flight PHP Framework

Flight は、迅速に作業を進めたい開発者向けに構築された、速く、シンプルで拡張可能な PHP フレームワークです。面倒なことは一切なし。クラシックなウェブアプリ、超高速 API、または最新の AI 駆動ツールの実験など、どのような用途でも、Flight の小さなフットプリントとストレートな設計がぴったりです。Flight はスリムを志向していますが、エンタープライズアーキテクチャの要件にも対応可能です。

## Flight を選ぶ理由は？

- **初心者向け:** Flight は新しい PHP 開発者の優れたスタート地点です。その明確な構造とシンプルな構文により、ボイラープレートに迷子になることなくウェブ開発を学べます。
- **プロに愛される:** 経験豊富な開発者は、Flight の柔軟性と制御性を愛しています。小さなプロトタイプからフル機能のアプリまでスケールアップでき、フレームワークの切り替えは不要です。
- **後方互換性:** あなたの時間を大切にします。Flight v3 は v2 の拡張版で、ほぼすべての API を維持しています。私たちは進化を信じ、革命を起こしません。主要バージョンのリリースごとに「世界を壊す」ようなことはありません。
- **ゼロ依存:** Flight のコアは完全に依存関係フリーです。ポリフィルなし、外部パッケージなし、PSR インターフェースさえありません。これにより、攻撃ベクターが少なくなり、フットプリントが小さく、上流依存からの予期せぬ破壊的変更もありません。オプションのプラグインに依存関係が含まれる場合もありますが、コアは常にスリムでセキュアです。
- **AI 指向:** Flight の最小限のオーバーヘッドとクリーンなアーキテクチャは、AI ツールや API の統合に理想的です。スマートチャットボット、AI 駆動ダッシュボードの構築、または単なる実験など、Flight は邪魔にならず、重要なことに集中できます。[skeleton app](https://github.com/flightphp/skeleton) には、主要な AI コーディングアシスタント向けのプリビルド指示ファイルが最初から含まれています！ [Flight での AI の使用について詳しく学ぶ](/learn/ai)

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

高速なベアボーンインストールを行うには、Composer でインストールしてください：

```bash
composer require flightphp/core
```

または、リポジトリの zip を [ここ](https://github.com/flightphp/core) からダウンロードできます。その後、以下の基本的な `index.php` ファイルを作成します：

```php
<?php

// composer でインストールした場合
require 'vendor/autoload.php';
// または zip ファイルで手動インストールした場合
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

これだけです！ 基本的な Flight アプリケーションが完成しました。このファイルを `php -S localhost:8000` で実行し、ブラウザで `http://localhost:8000` にアクセスすると出力が表示されます。

## Skeleton/Boilerplate App

Flight でプロジェクトを始めるための例アプリがあります。構造化されたレイアウト、基本設定がすべて揃い、Composer スクリプトもすぐに扱えます！ [flightphp/skeleton](https://github.com/flightphp/skeleton) をチェックしてすぐに使えるプロジェクトを取得するか、[examples](examples) ページでインスピレーションを得てください。AI の統合方法を知りたいですか？ [AI 駆動の例を探求](/learn/ai)。

## Installing the Skeleton App

簡単です！

```bash
# 新しいプロジェクトを作成
composer create-project flightphp/skeleton my-project/
# 新しいプロジェクトディレクトリに入る
cd my-project/
# すぐにローカル開発サーバーを起動
composer start
```

これでプロジェクト構造が作成され、必要なファイルがセットアップされ、すぐに始められます！

## High Performance

Flight は市販の PHP フレームワークの中でも最速クラスの一つです。その軽量コアによりオーバーヘッドが少なく速度が向上し、伝統的なアプリと現代の AI 駆動プロジェクトの両方に最適です。すべてのベンチマークは [TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks) で確認できます。

他の人気の PHP フレームワークとのベンチマークを以下に示します。

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

AI の扱い方が気になる？ [発見する](/learn/ai) Flight がお気に入りのコーディング LLM との作業をどれほど簡単にしますか！

## Stability and Backwards Compatibility

あなたの時間を大切にします。これまで、2年ごとに完全に自分たちを再発明するフレームワークを見てきましたが、それにより開発者のコードが壊れ、高価な移行作業が発生します。Flight は違います。Flight v3 は v2 の拡張として設計されており、知って愛する API が取り除かれていません。実際、ほとんどの v2 プロジェクトは v3 で変更なしで動作します。

Flight を安定した状態に保つことにコミットしており、フレームワークの修正ではなくアプリの構築に集中できます。

# Community

Matrix Chat で参加中です

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

そして Discord

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# Contributing

Flight に貢献する方法は 2 つあります：

1. [core repository](https://github.com/flightphp/core) を訪れてコアフレームワークに貢献。
2. ドキュメントを改善するお手伝い！ このドキュメントウェブサイトは [Github](https://github.com/flightphp/docs) でホストされています。エラーを見つけたり、改善したいことがあれば、プルリクエストを送信してください。更新や新しいアイデア、特に AI と新技術に関するものを歓迎します！

# Requirements

Flight は PHP 7.4 以上が必要です。

**Note:** PHP 7.4 はサポートされています。なぜなら、執筆時点（2024 年）で一部の LTS Linux ディストリビューションのデフォルトバージョンが PHP 7.4 だからです。PHP >8 への強制移行は、そうしたユーザーにとって大きな負担となります。フレームワークは PHP >8 もサポートします。

# License

Flight は [MIT](https://github.com/flightphp/core/blob/master/LICENSE) ライセンスでリリースされています。