# Flight PHP フレームワーク

Flight は、速く、シンプルで、拡張可能な PHP フレームワークです。開発者が素早く作業を完了させ、一切の面倒なことを避けたい場合に最適です。クラシックな Web アプリ、超高速 API、または最新の AI 駆動ツールの実験を行う場合、Flight の低負荷でシンプルな設計はぴったりです。Flight は軽量に設計されていますが、エンタープライズアーキテクチャの要件にも対応可能です。

## Flight を選ぶ理由？

- **初心者向け:** Flight は新しい PHP 開発者にとって素晴らしい出発点です。明確な構造とシンプルな構文により、余計なコードに迷うことなく Web 開発を学べます。
- **プロが愛用:** 経験豊富な開発者は、Flight の柔軟性と制御性を好みます。小さなプロトタイプからフル機能のアプリまでスケールアップでき、フレームワークを切り替える必要はありません。
- **AI 向け:** Flight の最小限のオーバーヘッドとクリーンなアーキテクチャは、AI ツールと API の統合に理想的です。スマートチャットボット、AI 駆動ダッシュボードの実装、または実験を行う場合、Flight は邪魔をせずに本質に集中できます。 [skeleton app](https://github.com/flightphp/skeleton) には、主要な AI コーディングアシスタント向けの事前構築済み指示ファイルが最初から含まれています！ [AI を使用した Flight の詳細について学ぶ](/learn/ai)

## ビデオ概要

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 fs-5 text-center mt-5 pt-5">
      <span class="flight-title-video">シンプルですよね？</span>
      <br>
      <a href="https://docs.flightphp.com/learn">詳細を学ぶ</a> ドキュメントで Flight について！
    </div>
  </div>
</div>

## クイックスタート

素早い最小限のインストールを行うには、Composer でインストールしてください：

```bash
composer require flightphp/core
```

または、リポジトリの ZIP を [こちら](https://github.com/flightphp/core) からダウンロードできます。その場合、基本的な `index.php` ファイルは次のようになります：

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
  Flight::json([
	'hello' => 'world'
  ]);
});

Flight::start();
```

これで完了です！ 基本的な Flight アプリケーションができました。今後、このファイルを `php -S localhost:8000` で実行し、ブラウザで `http://localhost:8000` を訪問して出力を見ることができます。

## スケルトン/ボイラープレートアプリ

Flight でプロジェクトを開始するための例のアプリがあります。構造化されたレイアウト、基本的な設定、Composer スクリプトが最初から設定されています！ [flightphp/skeleton](https://github.com/flightphp/skeleton) を確認して、すぐに使用可能なプロジェクトを取得するか、[examples](examples) ページでインスピレーションを得てください。AI の適合方法を知りたいですか？ [AI 駆動の例を探す](/learn/ai)。

## スケルトンアプリのインストール

簡単です！

```bash
# 新しいプロジェクトを作成
composer create-project flightphp/skeleton my-project/
# 新しいプロジェクトディレクトリに入る
cd my-project/
# ローカル開発サーバーを起動してすぐに開始！
composer start
```

これにより、プロジェクト構造が作成され、必要なファイルが設定され、準備完了です！

## 高パフォーマンス

Flight は、既存の PHP フレームワークの中で最も速いもののひとつです。その軽量なコアは、オーバーヘッドを減らし、速度を向上させ、伝統的なアプリや現代の AI 駆動プロジェクトに最適です。すべてのベンチマークは [TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks) で確認できます。

以下に、いくつかの人気の PHP フレームワークとのベンチマークを示します。

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

## Flight と AI

AI の扱いについて好奇心がありますか？ [発見する](/learn/ai) Flight が、お気に入りのコーディング LLM との作業を簡単にする方法を！

# コミュニティ

Matrix Chat で利用可能です

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

そして Discord

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# コントリビューション

Flight に貢献する方法は 2 つあります：

1. コアフレームワークに貢献するには、[core repository](https://github.com/flightphp/core) を訪問してください。
2. ドキュメントを改善する手伝い！ このドキュメントウェブサイトは [Github](https://github.com/flightphp/docs) でホストされています。エラーを発見したり、何かを改善したい場合、プルリクエストを提出してください。私たちは更新と新しいアイデアを歓迎します。特に AI と新しい技術に関するものを！

# 要件

Flight には PHP 7.4 以上が必要です。

**注記:** PHP 7.4 は、執筆時点 (2024 年) でいくつかの LTS Linux ディストリビューションのデフォルトバージョンであるため、サポートされています。PHP >8 への移行を強制すると、ユーザーに問題を引き起こす可能性があるためです。フレームワークは PHP >8 もサポートしています。

# ライセンス

Flight は [MIT](https://github.com/flightphp/core/blob/master/LICENSE) ライセンスでリリースされています。