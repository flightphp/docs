# CommentTemplate

[CommentTemplate](https://github.com/KnifeLemon/CommentTemplate) は、強力な PHP テンプレートエンジンで、アセットのコンパイル、テンプレートの継承、変数の処理を備えています。ビルトインの CSS/JS 最小化とキャッシュにより、シンプルで柔軟なテンプレート管理を提供します。

## 機能

- **テンプレートの継承**: レイアウトを使用し、他のテンプレートを含める
- **アセットのコンパイル**: 自動 CSS/JS 最小化とキャッシュ
- **変数の処理**: フィルターとコマンド付きのテンプレート変数
- **Base64 エンコーディング**: アセットをデータ URI としてインライン化
- **Flight Framework との統合**: Flight PHP フレームワークとのオプションの統合

## インストール

Composer を使用してインストールします。

```bash
composer require knifelemon/comment-template
```

## 基本設定

開始するための基本的な設定オプションがあります。これらについての詳細は [CommentTemplate Repo](https://github.com/KnifeLemon/CommentTemplate) を参照してください。

### 方法 1: コールバック関数を使用

```php
<?php
require_once 'vendor/autoload.php';

use KnifeLemon\CommentTemplate\Engine;

$app = Flight::app();

$app->register('view', Engine::class, [], function (Engine $engine) use ($app) {
    // ルートディレクトリ（index.php が存在する場所） - Web アプリケーションのドキュメントルート
    $engine->setPublicPath(__DIR__);
    
    // テンプレートファイルのディレクトリ - 相対パスと絶対パスの両方をサポート
    $engine->setSkinPath('views');             // パブリックパスに対する相対パス
    
    // コンパイルされたアセットの保存場所 - 相対パスと絶対パスの両方をサポート
    $engine->setAssetPath('assets');           // パブリックパスに対する相対パス
    
    // テンプレートファイルの拡張子
    $engine->setFileExtension('.php');
});

$app->map('render', function(string $template, array $data) use ($app): void {
    echo $app->view()->render($template, $data);
});
```

### 方法 2: コンストラクタパラメータを使用

```php
<?php
require_once 'vendor/autoload.php';

use KnifeLemon\CommentTemplate\Engine;

$app = Flight::app();

// __construct(string $publicPath = "", string $skinPath = "", string $assetPath = "", string $fileExtension = "")
$app->register('view', Engine::class, [
    __DIR__,                // publicPath - ルートディレクトリ（index.php が存在する場所）
    'views',                // skinPath - テンプレートパス（相対/絶対をサポート）
    'assets',               // assetPath - コンパイルされたアセットパス（相対/絶対をサポート）
    '.php'                  // fileExtension - テンプレートファイルの拡張子
]);

$app->map('render', function(string $template, array $data) use ($app): void {
    echo $app->view()->render($template, $data);
});
```

## Tracy デバッガー統合

CommentTemplateは開発ログとデバッグのために[Tracy Debugger](https://tracy.nette.org/)との統合を含んでいます。

![Comment Template Tracy](https://raw.githubusercontent.com/KnifeLemon/CommentTemplate/refs/heads/master/tracy.jpeg)

### インストール

```bash
composer require tracy/tracy
```

### 使用方法

```php
<?php
use KnifeLemon\CommentTemplate\Engine;
use Tracy\Debugger;

// Tracyを有効にする（出力前に呼び出す必要があります）
Debugger::enable(Debugger::DEVELOPMENT);
Flight::set('flight.content_length', false);

// テンプレートのオーバーライド
$app->register('view', Engine::class, [], function (Engine $builder) use ($app) {
    $builder->setPublicPath($app->get('flight.views.topPath'));
    $builder->setAssetPath($app->get('flight.views.assetPath'));
    $builder->setSkinPath($app->get('flight.views.path'));
    $builder->setFileExtension($app->get('flight.views.extension'));
});
$app->map('render', function(string $template, array $data) use ($app): void {
    echo $app->view()->render($template, $data);
});

$app->start();
```

### デバッグパネル機能

CommentTemplateは4つのタブを持つカスタムパネルをTracyのデバッグバーに追加します：

- **Overview**: 設定、パフォーマンス指標、カウント
- **Assets**: 圧縮率を含むCSS/JSコンパイルの詳細
- **Variables**: 適用されたフィルターを含む元の値と変換された値
- **Timeline**: すべてのテンプレート操作の時系列ビュー

### ログに記録される内容

- テンプレートレンダリング（開始/終了、期間、レイアウト、インポート）
- アセットコンパイル（CSS/JSファイル、サイズ、圧縮率）
- 変数処理（元の値/変換された値、フィルター）
- アセット操作（base64エンコーディング、ファイルコピー）
- パフォーマンス指標（期間、メモリ使用量）

**注意:** Tracyがインストールされていないか無効になっている場合、パフォーマンスへの影響はゼロです。

[Flight PHPでの完全な動作例](https://github.com/KnifeLemon/CommentTemplate/tree/master/examples/flightphp)を参照してください。

## テンプレートディレクティブ

### レイアウト継承

共通の構造を作成するためにレイアウトを使用します：

**layout/global_layout.php**:
```html
<!DOCTYPE html>
<html>
<head>
    <title>{$title}</title>
</head>
<body>
    <!--@contents-->
</body>
</html>
```

**view/page.php**:
```html
<!--@layout(layout/global_layout)-->
<h1>{$title}</h1>
<p>{$content}</p>
```

### アセット管理

#### CSS ファイル
```html
<!--@css(/css/styles.css)-->          <!-- 最小化されキャッシュされる -->
<!--@cssSingle(/css/critical.css)-->  <!-- 単一ファイル、最小化されない -->
```

#### JavaScript ファイル
CommentTemplate は異なる JavaScript 読み込み戦略をサポートします：

```html
<!--@js(/js/script.js)-->             <!-- 最小化され、ボトムで読み込まれる -->
<!--@jsAsync(/js/analytics.js)-->     <!-- 最小化され、ボトムで async で読み込まれる -->
<!--@jsDefer(/js/utils.js)-->         <!-- 最小化され、ボトムで defer で読み込まれる -->
<!--@jsTop(/js/critical.js)-->        <!-- 最小化され、head で読み込まれる -->
<!--@jsTopAsync(/js/tracking.js)-->   <!-- 最小化され、head で async で読み込まれる -->
<!--@jsTopDefer(/js/polyfill.js)-->   <!-- 最小化され、head で defer で読み込まれる -->
<!--@jsSingle(/js/widget.js)-->       <!-- 単一ファイル、最小化されない -->
<!--@jsSingleAsync(/js/ads.js)-->     <!-- 単一ファイル、最小化されない、async -->
<!--@jsSingleDefer(/js/social.js)-->  <!-- 単一ファイル、最小化されない、defer -->
```

#### CSS/JS ファイル内のアセットディレクティブ

CommentTemplate は、コンパイル中に CSS と JavaScript ファイル内のアセットディレクティブも処理します：

**CSS の例：**
```css
/* あなたの CSS ファイル内で */
@font-face {
    font-family: 'CustomFont';
    src: url('<!--@asset(fonts/custom.woff2)-->') format('woff2');
}

.background-image {
    background: url('<!--@asset(images/bg.jpg)-->');
}

.inline-icon {
    background: url('<!--@base64(icons/star.svg)-->');
}
```

**JavaScript の例：**
```javascript
/* あなたの JS ファイル内で */
const fontUrl = '<!--@asset(fonts/custom.woff2)-->';
const imageData = '<!--@base64(images/icon.png)-->';
```

#### Base64 エンコーディング
```html
<!--@base64(images/logo.png)-->       <!-- データ URI としてインライン -->
```
**例：**
```html
<!-- 小さな画像をデータ URI としてインライン化して高速読み込み -->
<img src="<!--@base64(images/logo.png)-->" alt="Logo">
<div style="background-image: url('<!--@base64(icons/star.svg)-->');">
    背景としての小さなアイコン
</div>
```

#### アセットのコピー
```html
<!--@asset(images/photo.jpg)-->       <!-- 単一アセットをパブリックディレクトリにコピー -->
<!--@assetDir(assets)-->              <!-- 全体のディレクトリをパブリックディレクトリにコピー -->
```
**例：**
```html
<!-- 静的アセットをコピーして参照 -->
<img src="<!--@asset(images/hero-banner.jpg)-->" alt="Hero Banner">
<a href="<!--@asset(documents/brochure.pdf)-->" download>ブロシュアをダウンロード</a>

<!-- 全体のディレクトリ（フォント、アイコンなど）をコピー -->
<!--@assetDir(assets/fonts)-->
<!--@assetDir(assets/icons)-->
```

### テンプレートのインクルード
```html
<!--@import(components/header)-->     <!-- 他のテンプレートを含める -->
```
**例：**
```html
<!-- 再利用可能なコンポーネントを含める -->
<!--@import(components/header)-->

<main>
    <h1>当社のウェブサイトへようこそ</h1>
    <!--@import(components/sidebar)-->
    
    <div class="content">
        <p>ここにメインコンテンツ...</p>
    </div>
</main>

<!--@import(components/footer)-->
```

### 変数の処理

#### 基本変数
```html
<h1>{$title}</h1>
<p>{$description}</p>
```

#### 変数フィルター
```html
{$title|upper}                       <!-- 大文字に変換 -->
{$content|lower}                     <!-- 小文字に変換 -->
{$html|striptag}                     <!-- HTML タグを除去 -->
{$text|escape}                       <!-- HTML をエスケープ -->
{$multiline|nl2br}                   <!-- 改行を <br> に変換 -->
{$html|br2nl}                        <!-- <br> タグを改行に変換 -->
{$description|trim}                  <!-- 空白をトリム -->
{$subject|title}                     <!-- タイトルケースに変換 -->
```

#### 変数コマンド
```html
{$title|default=Default Title}       <!-- デフォルト値を設定 -->
{$name|concat= (Admin)}              <!-- テキストを連結 -->
```

#### 変数コマンド
```html
{$content|striptag|trim|escape}      <!-- 複数のフィルターをチェーン -->
```

### コメント

テンプレートコメントは出力から完全に削除され、最終的な HTML に表示されません：

```html
{* これは1行のテンプレートコメントです *}

{* 
   これは複数行の 
   テンプレートコメントです 
   複数行にわたります
*}

<h1>{$title}</h1>
{* デバッグコメント: title 変数が動作するかを確認 *}
<p>{$content}</p>
```

**注意**: テンプレートコメント `{* ... *}` は HTML コメント `<!-- ... -->` と異なります。テンプレートコメントは処理中に削除され、ブラウザに到達しません。

## 例のプロジェクト構造

```
project/
├── source/
│   ├── layouts/
│   │   └── default.php
│   ├── components/
│   │   ├── header.php
│   │   └── footer.php
│   ├── css/
│   │   ├── bootstrap.min.css
│   │   └── custom.css
│   ├── js/
│   │   ├── app.js
│   │   └── bootstrap.min.js
│   └── homepage.php
├── public/
│   └── assets/           # 生成されたアセット
│       ├── css/
│       └── js/
└── vendor/
```