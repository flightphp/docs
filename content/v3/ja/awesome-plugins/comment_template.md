# CommentTemplate

[CommentTemplate](https://github.com/KnifeLemon/CommentTemplate) は、強力な PHP テンプレートエンジンで、アセットのコンパイル、テンプレートの継承、変数の処理を備えています。組み込みの CSS/JS 最小化とキャッシュにより、シンプルで柔軟なテンプレート管理を提供します。

## 機能

- **テンプレートの継承**: レイアウトを使用し、他のテンプレートを含める
- **アセットのコンパイル**: CSS/JS の自動最小化とキャッシュ
- **変数の処理**: フィルターとコマンド付きのテンプレート変数
- **Base64 エンコーディング**: アセットをデータ URI としてインライン化
- **Flight Framework 統合**: Flight PHP フレームワークとのオプションの統合

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
    // テンプレートファイルが保存される場所
    $engine->setTemplatesPath(__DIR__ . '/views');
    
    // パブリックアセットが提供される場所
    $engine->setPublicPath(__DIR__ . '/public');
    
    // コンパイルされたアセットが保存される場所
    $engine->setAssetPath('assets');
    
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
    __DIR__ . '/public',    // publicPath - アセットが提供される場所
    __DIR__ . '/views',     // skinPath - テンプレートファイルが保存される場所  
    'assets',               // assetPath - コンパイルされたアセットが保存される場所
    '.php'                  // fileExtension - テンプレートファイルの拡張子
]);

$app->map('render', function(string $template, array $data) use ($app): void {
    echo $app->view()->render($template, $data);
});
```

## テンプレートディレクティブ

### レイアウトの継承

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

CommentTemplate はコンパイル中に CSS と JavaScript ファイル内のアセットディレクティブも処理します：

**CSS の例:**
```css
/* CSS ファイル内 */
/* フォントファイル */
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

**JavaScript の例:**
```javascript
/* JS ファイル内 */
const fontUrl = '<!--@asset(fonts/custom.woff2)-->';
const imageData = '<!--@base64(images/icon.png)-->';
```

#### Base64 エンコーディング
```html
<!--@base64(images/logo.png)-->       <!-- データ URI としてインライン化 -->
```
**例:**
```html
<!-- 小さな画像をデータ URI としてインライン化して高速読み込み -->
<img src="<!--@base64(images/logo.png)-->" alt="Logo">
<div style="background-image: url('<!--@base64(icons/star.svg)-->');">
    背景としての小さなアイコン
</div>
```

#### アセットのコピー
```html
<!--@asset(images/photo.jpg)-->       <!-- 単一のアセットをパブリックディレクトリにコピー -->
<!--@assetDir(assets)-->              <!-- ディレクトリ全体をパブリックディレクトリにコピー -->
```
**例:**
```html
<!-- 静的アセットをコピーして参照 -->
<img src="<!--@asset(images/hero-banner.jpg)-->" alt="Hero Banner">
<a href="<!--@asset(documents/brochure.pdf)-->" download>パンフレットダウンロード</a>

<!-- ディレクトリ全体（フォント、アイコンなど）をコピー -->
<!--@assetDir(assets/fonts)-->
<!--@assetDir(assets/icons)-->
```

### テンプレートのインクルード
```html
<!--@import(components/header)-->     <!-- 他のテンプレートを含める -->
```
**例:**
```html
<!-- 再利用可能なコンポーネントを含める -->
<!--@import(components/header)-->

<main>
    <h1>ウェブサイトへようこそ</h1>
    <!--@import(components/sidebar)-->
    
    <div class="content">
        <p>メインコンテンツはここ...</p>
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