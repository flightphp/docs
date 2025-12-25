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

## パス設定

CommentTemplate は、相対パスと絶対パスの両方に対してインテリジェントなパス処理を提供します：

### パブリックパス

**パブリックパス** は、Web アプリケーションのルートディレクトリで、通常 `index.php` が存在する場所です。これは Web サーバーがファイルを配信するドキュメントルートです。

```php
// 例: index.php が /var/www/html/myapp/index.php にある場合
$template->setPublicPath('/var/www/html/myapp');  // ルートディレクトリ

// Windows の例: index.php が C:\xampp\htdocs\myapp\index.php にある場合
$template->setPublicPath('C:\\xampp\\htdocs\\myapp');
```

### テンプレートパス設定

テンプレートパスは、相対パスと絶対パスの両方をサポートします：

```php
$template = new Engine();
$template->setPublicPath('/var/www/html/myapp');  // ルートディレクトリ（index.php が存在する場所）

// 相対パス - パブリックパスと自動的に結合
$template->setSkinPath('views');           // → /var/www/html/myapp/views/
$template->setSkinPath('templates/pages'); // → /var/www/html/myapp/templates/pages/

// 絶対パス - そのまま使用（Unix/Linux）
$template->setSkinPath('/var/www/templates');      // → /var/www/templates/
$template->setSkinPath('/full/path/to/templates'); // → /full/path/to/templates/

// Windows 絶対パス
$template->setSkinPath('C:\\www\\templates');     // → C:\www\templates\
$template->setSkinPath('D:/projects/templates');  // → D:/projects/templates/

// UNC パス（Windows ネットワーク共有）
$template->setSkinPath('\\\\server\\share\\templates'); // → \\server\share\templates\
```

### アセットパス設定

アセットパスも相対パスと絶対パスの両方をサポートします：

```php
// 相対パス - パブリックパスと自動的に結合
$template->setAssetPath('assets');        // → /var/www/html/myapp/assets/
$template->setAssetPath('static/files');  // → /var/www/html/myapp/static/files/

// 絶対パス - そのまま使用（Unix/Linux）
$template->setAssetPath('/var/www/cdn');           // → /var/www/cdn/
$template->setAssetPath('/full/path/to/assets');   // → /full/path/to/assets/

// Windows 絶対パス
$template->setAssetPath('C:\\www\\static');       // → C:\www\static\
$template->setAssetPath('D:/projects/assets');    // → D:/projects/assets/

// UNC パス（Windows ネットワーク共有）
$template->setAssetPath('\\\\server\\share\\assets'); // → \\server\share\assets\
```

**スマートパス検出：**

- **相対パス**: 先頭に区切り文字（`/`、`\`）やドライブレターがない
- **Unix 絶対**: `/` で始まる（例: `/var/www/assets`）
- **Windows 絶対**: ドライブレターで始まる（例: `C:\www`、`D:/assets`）
- **UNC パス**: `\\` で始まる（例: `\\server\share`）

**仕組み：**

- すべてのパスはタイプ（相対 vs 絶対）に基づいて自動的に解決される
- 相対パスはパブリックパスと結合される
- `@css` と `@js` は最小化されたファイルを `{resolvedAssetPath}/css/` または `{resolvedAssetPath}/js/` に作成
- `@asset` は単一ファイルを `{resolvedAssetPath}/{relativePath}` にコピー
- `@assetDir` はディレクトリを `{resolvedAssetPath}/{relativePath}` にコピー
- スマートキャッシュ: ソースがデスティネーションより新しい場合のみファイルをコピー

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