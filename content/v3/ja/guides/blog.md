# Flight PHPを使ったシンプルなブログの構築

このガイドでは、Flight PHPフレームワークを使用して基本的なブログを作成する方法を説明します。プロジェクトをセットアップし、ルートを定義し、JSONを使用して投稿を管理し、Latteテンプレーティングエンジンでレンダリングします。すべてがFlightのシンプルさと柔軟性を示しています。最後には、ホームページ、個別の投稿ページ、および作成フォームを持つ機能的なブログが完成します。

## 前提条件
- **PHP 7.4+**: システムにインストールされていること。
- **Composer**: 依存関係管理用。
- **テキストエディタ**: VS CodeやPHPStormなどの任意のエディタ。
- PHPとWeb開発の基本知識。

## ステップ1: プロジェクトのセットアップ

新しいプロジェクトディレクトリを作成し、Composerを介してFlightをインストールします。

1. **ディレクトリの作成**:
   ```bash
   mkdir flight-blog
   cd flight-blog
   ```

2. **Flightのインストール**:
   ```bash
   composer require flightphp/core
   ```

3. **パブリックディレクトリの作成**:
   Flightは単一のエントリーポイント（`index.php`）を使用します。それ用に`public/`フォルダを作成します:
   ```bash
   mkdir public
   ```

4. **基本的な`index.php`**:
   シンプルな「Hello World」ルートを持つ`public/index.php`を作成します:
   ```php
   <?php
   require '../vendor/autoload.php';

   Flight::route('/', function () {
       echo 'こんにちは、Flight！';
   });

   Flight::start();
   ```

5. **組み込みサーバーの起動**:
   PHPの開発サーバーを使用してセットアップをテストします:
   ```bash
   php -S localhost:8000 -t public/
   ```
   `http://localhost:8000`にアクセスして「こんにちは、Flight！」を見ることができます。

## ステップ2: プロジェクト構造の整理

クリーンなセットアップのために、プロジェクトを以下のように構成します:

```text
flight-blog/
├── app/
│   ├── config/
│   └── views/
├── data/
├── public/
│   └── index.php
├── vendor/
└── composer.json
```

- `app/config/`: 設定ファイル（例：イベント、ルート）。
- `app/views/`: ページをレンダリングするためのテンプレート。
- `data/`: ブログ投稿を保存するためのJSONファイル。
- `public/`: `index.php`を含むWebルート。

## ステップ3: Latteのインストールと設定

Latteは、Flightとよく統合される軽量なテンプレーティングエンジンです。

1. **Latteのインストール**:
   ```bash
   composer require latte/latte
   ```

2. **FlightでのLatteの設定**:
   `public/index.php`を更新してLatteをビューエンジンとして登録します:
   ```php
   <?php
   require '../vendor/autoload.php';

   use Latte\Engine;

   Flight::register('view', Engine::class, [], function ($latte) {
       $latte->setTempDirectory(__DIR__ . '/../cache/');
       $latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../app/views/'));
   });

   Flight::route('/', function () {
       Flight::view()->render('home.latte', ['title' => '私のブログ']);
   });

   Flight::start();
   ```

3. **レイアウトテンプレートを作成する:
`app/views/layout.latte`で**:
```html
<!DOCTYPE html>
<html>
<head>
    <title>{$title}</title>
</head>
<body>
    <header>
        <h1>私のブログ</h1>
        <nav>
            <a href="/">ホーム</a> | 
            <a href="/create">投稿を作成</a>
        </nav>
    </header>
    <main>
        {block content}{/block}
    </main>
    <footer>
        <p>&copy; {date('Y')} Flightブログ</p>
    </footer>
</body>
</html>
```

4. **ホームテンプレートを作成**:
   `app/views/home.latte`で:
   ```html
  {extends 'layout.latte'}

	{block content}
		<h2>{$title}</h2>
		<ul>
		{foreach $posts as $post}
			<li><a href="/post/{$post['slug']}">{$post['title']}</a></li>
		{/foreach}
		</ul>
	{/block}
   ```
   サーバーを再起動して、`http://localhost:8000`にアクセスしてレンダリングされたページを確認してください。

5. **データファイルを作成**:

   簡単のためにデータベースのシミュレーションとしてJSONファイルを使用します。

   `data/posts.json`で:
   ```json
   [
       {
           "slug": "first-post",
           "title": "私の最初の投稿",
           "content": "これはFlight PHPを使用した私の初めてのブログ投稿です！"
       }
   ]
   ```

## ステップ4: ルートの定義

ルートを構成ファイルに分けることで、整理を良くしましょう。

1. **`routes.php`を作成**:
   `app/config/routes.php`で:
   ```php
   <?php
   Flight::route('/', function () {
       Flight::view()->render('home.latte', ['title' => '私のブログ']);
   });

   Flight::route('/post/@slug', function ($slug) {
       Flight::view()->render('post.latte', ['title' => '投稿: ' . $slug, 'slug' => $slug]);
   });

   Flight::route('GET /create', function () {
       Flight::view()->render('create.latte', ['title' => '投稿を作成']);
   });
   ```

2. **`index.php`を更新**:
   ルートファイルを含める:
   ```php
   <?php
   require '../vendor/autoload.php';

   use Latte\Engine;

   Flight::register('view', Engine::class, [], function ($latte) {
       $latte->setTempDirectory(__DIR__ . '/../cache/');
       $latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../app/views/'));
   });

   require '../app/config/routes.php';

   Flight::start();
   ```

## ステップ5: ブログ投稿の保存と取得

投稿を読み込み、保存するメソッドを追加します。

1. **投稿メソッドを追加**:
   `index.php`で、投稿を読み込むメソッドを追加します:
   ```php
   Flight::map('posts', function () {
       $file = __DIR__ . '/../data/posts.json';
       return json_decode(file_get_contents($file), true);
   });
   ```

2. **ルートの更新**:
   `app/config/routes.php`を修正し、投稿を使用するようにします:
   ```php
   <?php
   Flight::route('/', function () {
       $posts = Flight::posts();
       Flight::view()->render('home.latte', [
           'title' => '私のブログ',
           'posts' => $posts
       ]);
   });

   Flight::route('/post/@slug', function ($slug) {
       $posts = Flight::posts();
       $post = array_filter($posts, fn($p) => $p['slug'] === $slug);
       $post = reset($post) ?: null;
       if (!$post) {
           Flight::notFound();
           return;
       }
       Flight::view()->render('post.latte', [
           'title' => $post['title'],
           'post' => $post
       ]);
   });

   Flight::route('GET /create', function () {
       Flight::view()->render('create.latte', ['title' => '投稿を作成']);
   });
   ```

## ステップ6: テンプレートの作成

投稿を表示するためにテンプレートを更新します。

1. **投稿ページ（`app/views/post.latte`）**:
   ```html
   {extends 'layout.latte'}

	{block content}
		<h2>{$post['title']}</h2>
		<div class="post-content">
			<p>{$post['content']}</p>
		</div>
	{/block}
   ```

## ステップ7: 投稿作成の追加

新しい投稿を追加するためのフォーム送信を処理します。

1. **フォーム（`app/views/create.latte`）**:
   ```html
   {extends 'layout.latte'}

	{block content}
		<h2>{$title}</h2>
		<form method="POST" action="/create">
			<div class="form-group">
				<label for="title">タイトル:</label>
				<input type="text" name="title" id="title" required>
			</div>
			<div class="form-group">
				<label for="content">コンテンツ:</label>
				<textarea name="content" id="content" required></textarea>
			</div>
			<button type="submit">投稿を保存</button>
		</form>
	{/block}
   ```

2. **POSTルートを追加**:
   `app/config/routes.php`で:
   ```php
   Flight::route('POST /create', function () {
       $request = Flight::request();
       $title = $request->data['title'];
       $content = $request->data['content'];
       $slug = strtolower(str_replace(' ', '-', $title));

       $posts = Flight::posts();
       $posts[] = ['slug' => $slug, 'title' => $title, 'content' => $content];
       file_put_contents(__DIR__ . '/../../data/posts.json', json_encode($posts, JSON_PRETTY_PRINT));

       Flight::redirect('/');
   });
   ```

3. **テストする**:
   - `http://localhost:8000/create`を訪問します。
   - 新しい投稿（例：「第二の投稿」とその内容）を送信します。
   - ホームページでそれがリストされているのを確認します。

## ステップ8: エラーハンドリングの強化

より良い404エクスペリエンスのために`notFound`メソッドをオーバーライドします。

`index.php`で:
```php
Flight::map('notFound', function () {
    Flight::view()->render('404.latte', ['title' => 'ページが見つかりません']);
});
```

`app/views/404.latte`を作成します:
```html
{extends 'layout.latte'}

{block content}
    <h2>404 - {$title}</h2>
    <p>申し訳ありませんが、そのページは存在しません！</p>
{/block}
```

## 次のステップ
- **スタイリングの追加**: より良い見た目のためにテンプレートにCSSを使用します。
- **データベース**: `posts.json`をSQLiteなどのデータベースに置き換えます。
- **バリデーション**: 重複スラッグや空の入力のチェックを追加します。
- **ミドルウェア**: 投稿作成のための認証を実装します。

## 結論

Flight PHPを使ってシンプルなブログを構築しました！ このガイドでは、ルーティング、Latteによるテンプレーティング、およびフォーム送信の処理などのコア機能を示しました。すべてを軽量に保ちながら実施しています。さらにブログを進化させるためにFlightのドキュメントを探求してください！