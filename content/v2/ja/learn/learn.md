# 学ぶ

このページはFlightの学習ガイドです。フレームワークの基本とその使用方法をカバーしています。

## <a name="routing"></a> ルーティング

Flightのルーティングは、URLパターンとコールバック関数を照合することで行われます。

``` php
Flight::route('/', function(){
    echo 'こんにちは、世界！';
});
```

コールバックは呼び出し可能な任意のオブジェクトにすることができます。したがって、通常の関数を使用することもできます：

``` php
function hello(){
    echo 'こんにちは、世界！';
}

Flight::route('/', 'hello');
```

またはクラスメソッドを使用することもできます：

``` php
class Greeting {
    public static function hello() {
        echo 'こんにちは、世界！';
    }
}

Flight::route('/', array('Greeting','hello'));
```

またはオブジェクトメソッドを使用することもできます：

``` php
class Greeting
{
    public function __construct() {
        $this->name = 'ジョン・ドー';
    }

    public function hello() {
        echo "こんにちは、{$this->name}!";
    }
}

$greeting = new Greeting();

Flight::route('/', array($greeting, 'hello'));
```

ルートは定義された順序で照合されます。リクエストにマッチした最初のルートが呼び出されます。

### メソッドルーティング

デフォルトでは、ルートパターンはすべてのリクエストメソッドに対して照合されます。特定のメソッドに応じて応答するには、URLの前に識別子を置きます。

``` php
Flight::route('GET /', function(){
    echo 'GETリクエストを受け取りました。';
});

Flight::route('POST /', function(){
    echo 'POSTリクエストを受け取りました。';
});
```

複数のメソッドを単一のコールバックにマッピングするには、`|`区切り文字を使用できます：

``` php
Flight::route('GET|POST /', function(){
    echo 'GETまたはPOSTリクエストのいずれかを受け取りました。';
});
```

### 正規表現

ルートに正規表現を使用することができます：

``` php
Flight::route('/user/[0-9]+', function(){
    // これは/user/1234にマッチします
});
```

### 名前付きパラメータ

ルートで名前付きパラメータを指定でき、それをコールバック関数に渡すことができます。

``` php
Flight::route('/@name/@id', function($name, $id){
    echo "こんにちは、$name ($id)!";
});
```

`:`区切り文字を使用して名前付きパラメータに正規表現を含めることもできます：

``` php
Flight::route('/@name/@id:[0-9]{3}', function($name, $id){
    // これは/bob/123にマッチします
    // しかし/bob/12345にはマッチしません
});
```

### オプションのパラメータ

セグメントを括弧で囲むことによって、照合に対してオプションの名前付きパラメータを指定できます。

``` php
Flight::route('/blog(/@year(/@month(/@day)))', function($year, $month, $day){
    // これは以下のURLにマッチします：
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
});
```

マッチしなかったオプションのパラメータはNULLとして渡されます。

### ワイルドカード

マッチングは個々のURLセグメントに対してのみ行われます。複数のセグメントをマッチさせたい場合は、`*`ワイルドカードを使用できます。

``` php
Flight::route('/blog/*', function(){
    // これは/blog/2000/02/01にマッチします
});
```

すべてのリクエストを単一のコールバックにルーティングするには、次のようにします：

``` php
Flight::route('*', function(){
    // 何かを行う
});
```

### 継続

コールバック関数から`true`を返すことで、次のマッチングルートに処理を渡すことができます。

``` php
Flight::route('/user/@name', function($name){
    // いくつかの条件を確認します
    if ($name != "ボブ") {
        // 次のルートに続行します
        return true;
    }
});

Flight::route('/user/*', function(){
    // これは呼ばれます
});
```

### ルート情報

マッチングルート情報を調べたい場合は、ルートメソッドの第三引数に`true`を指定して、コールバックにルートオブジェクトを渡すように要求できます。ルートオブジェクトは常にコールバック関数に渡される最後のパラメータです。

``` php
Flight::route('/', function($route){
    // 照合されたHTTPメソッドの配列
    $route->methods;

    // 名前付きパラメータの配列
    $route->params;

    // マッチング正規表現
    $route->regex;

    // URLパターンで使用された任意の'*'の内容を含みます
    $route->splat;
}, true);
```
### ルートグルーピング

関連するルートを一つにグループ化したいときがあるかもしれません（例えば`/api/v1`など）。これを`group`メソッドを使用して行うことができます：

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// /api/v1/usersにマッチします
  });

  Flight::route('/posts', function () {
	// /api/v1/postsにマッチします
  });
});
```

さらにグループのグループをネストすることもできます：

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get()は変数を取得しますが、ルートを設定しません！以下のオブジェクトコンテキストを参照してください
	Flight::route('GET /users', function () {
	  // GET /api/v1/usersにマッチします
	});

	Flight::post('/posts', function () {
	  // POST /api/v1/postsにマッチします
	});

	Flight::put('/posts/1', function () {
	  // PUT /api/v1/postsにマッチします
	});
  });
  Flight::group('/v2', function () {

	// Flight::get()は変数を取得しますが、ルートを設定しません！以下のオブジェクトコンテキストを参照してください
	Flight::route('GET /users', function () {
	  // GET /api/v2/usersにマッチします
	});
  });
});
```

#### オブジェクトコンテキストでのグルーピング

次の方法で`Engine`オブジェクトを使用したルートグルーピングを行うこともできます：

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {
  $router->get('/users', function () {
	// GET /api/v1/usersにマッチします
  });

  $router->post('/posts', function () {
	// POST /api/v1/postsにマッチします
  });
});
```

### ルートエイリアシング

ルートにエイリアスを割り当てることができるため、URLを後で動的に生成できます（例えば、テンプレートなど）。

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');

// 後でコード内のどこかで
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5'を返します
```

これは、URLが変更される場合に特に便利です。上記の例では、ユーザーが`/admin/users/@id`に移動したと仮定します。
エイリアスが設定されていれば、エイリアスを参照する場所を変更する必要はありません。エイリアスは今や`/admin/users/5`を返します。

ルートエイリアスはグループ内でも機能します：

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});

// 後でコード内のどこかで
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5'を返します
```

## <a name="extending"></a> 拡張

Flightは拡張可能なフレームワークとして設計されています。フレームワークには一連のデフォルトメソッドとコンポーネントが付属していますが、独自のメソッドをマッピングしたり、独自のクラスを登録したり、既存のクラスやメソッドを上書きしたりすることができます。

### メソッドのマッピング

独自のカスタムメソッドをマッピングするには、`map`関数を使用します：

``` php
// メソッドをマップする
Flight::map('hello', function($name){
    echo "こんにちは $name!";
});

// カスタムメソッドを呼び出す
Flight::hello('ボブ');
```

### クラスの登録

独自のクラスを登録するには、`register`関数を使用します：

``` php
// クラスを登録する
Flight::register('user', 'User');

// クラスのインスタンスを取得する
$user = Flight::user();
```

registerメソッドは、クラスコンストラクタにパラメータを渡すことも可能です。これにより、カスタムクラスを読み込むときにプリインストールされます。コンストラクタパラメータは、追加の配列を渡すことによって定義できます。
以下は、データベース接続を読み込む例です：

``` php
// コンストラクタパラメータ付きのクラスを登録する
Flight::register('db', 'PDO', array('mysql:host=localhost;dbname=test','user','pass'));

// クラスのインスタンスを取得する
// これは定義されたパラメータでオブジェクトを作成します
//
//     new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();
```

追加のコールバックパラメータを渡すと、クラス構築後にすぐに実行されます。これにより、新しいオブジェクトの設定手順を行うことができます。コールバック関数は、新しいオブジェクトのインスタンスを1つのパラメータとして受け取ります。

``` php
// コールバックは構築されたオブジェクトが渡されます
Flight::register('db', 'PDO', array('mysql:host=localhost;dbname=test','user','pass'),
  function($db){
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

デフォルトでは、クラスを読み込むたびに共有インスタンスが取得されます。クラスの新しいインスタンスを取得するには、単に`false`をパラメータとして渡すだけです。 

``` php
// クラスの共有インスタンス
$shared = Flight::db();

// クラスの新しいインスタンス
$new = Flight::db(false);
```

マッピングされたメソッドは、登録されたクラスより優先されることに注意してください。両方を同じ名前で宣言すると、マッピングされたメソッドのみが呼び出されます。

## <a name="overriding"></a> 上書き

Flightは、必要に応じてデフォルトの機能を上書きすることを許可しますが、何のコードも修正する必要はありません。

例えば、FlightがURLをルートにマッチさせられない場合、`notFound`メソッドが呼び出され、一般的な`HTTP 404`レスポンスが送信されます。この動作を上書きするには、`map`メソッドを使用します：

``` php
Flight::map('notFound', function(){
    // カスタム404ページを表示する
    include 'errors/404.html';
});
```

Flightはまた、フレームワークのコアコンポーネントを置き換えることもできます。
例えば、デフォルトのRouterクラスを独自のカスタムクラスと置き換えることができます：

``` php
// カスタムクラスを登録する
Flight::register('router', 'MyRouter');

// FlightがRouterインスタンスを読み込むときは、あなたのクラスが読み込まれます
$myrouter = Flight::router();
```

ただし、`map`や`register`のようなフレームワークメソッドは上書きすることができません。そうしようとするとエラーが発生します。

## <a name="filtering"></a> フィルタリング

Flightは、メソッドが呼び出される前後でそれらをフィルタリングすることを許可します。記憶する必要がある事前定義のフックはありません。デフォルトのフレームワークメソッドだけでなく、マッピングしたカスタムメソッドもフィルタリングできます。

フィルタ関数は次のようになります：

``` php
function(&$params, &$output) {
    // フィルタコード
}
```

渡された変数を使用して、入力パラメータや出力を操作できます。

メソッドの前にフィルタを実行することができます：

``` php
Flight::before('start', function(&$params, &$output){
    // 何かをする
});
```

メソッドの後にフィルタを実行することもできます：

``` php
Flight::after('start', function(&$params, &$output){
    // 何かをする
});
```

任意のメソッドに対して好きなだけフィルタを追加できます。それらは宣言された順序で呼び出されます。

フィルタリングプロセスの例を以下に示します：

``` php
// カスタムメソッドをマッピングする
Flight::map('hello', function($name){
    return "こんにちは、$name!";
});

// 前のフィルタを追加する
Flight::before('hello', function(&$params, &$output){
    // パラメータを操作する
    $params[0] = 'フレッド';
});

// 後のフィルタを追加する
Flight::after('hello', function(&$params, &$output){
    // 出力を操作する
    $output .= " 良い一日を!";
});

// カスタムメソッドを呼び出す
echo Flight::hello('ボブ');
```

これは表示されるはずです：

``` html
こんにちは フレッド! 良い一日を!
```

複数のフィルタを定義している場合は、フィルタ関数のいずれかで`false`を返すことでチェーンを切断できます：

``` php
Flight::before('start', function(&$params, &$output){
    echo '一つ';
});

Flight::before('start', function(&$params, &$output){
    echo '二つ';

    // これでチェーンが終了します
    return false;
});

// これは呼ばれません
Flight::before('start', function(&$params, &$output){
    echo '三つ';
});
```

`map`や`register`のようなコアメソッドは、直接呼び出されるためフィルタリングできないことに注意してください。

## <a name="variables"></a> 変数

Flightは変数を保存して、アプリケーションのどこでも使用できるようにします。

``` php
// 変数を保存する
Flight::set('id', 123);

// アプリケーションの他の場所で
$id = Flight::get('id');
```

変数が設定されているかどうかを確認するには、以下のようにします：

``` php
if (Flight::has('id')) {
     // 何かをする
}
```

変数をクリアするには、以下のようにします：

``` php
// id変数をクリアする
Flight::clear('id');

// すべての変数をクリアする
Flight::clear();
```

Flightはまた、構成目的のために変数を使用します。

``` php
Flight::set('flight.log_errors', true);
```

## <a name="views"></a> ビュー

Flightはデフォルトで基本的なテンプレート機能を提供します。ビュー テンプレートを表示するには、`render`メソッドを呼び出し、テンプレートファイルの名前とオプションのテンプレートデータを渡します。

``` php
Flight::render('hello.php', array('name' => 'ボブ'));
```

渡すテンプレートデータは自動的にテンプレートに注入され、ローカル変数のように参照できます。テンプレートファイルは単なるPHPファイルです。`hello.php`テンプレートファイルの内容が次のようになっている場合：

``` php
こんにちは、'<?php echo $name; ?>'!
```

出力は次のようになります：

``` html
こんにちは、ボブ!
```

`set`メソッドを使用して手動でビュー変数を設定することもできます：

``` php
Flight::view()->set('name', 'ボブ');
```

変数`name`はすべてのビューで利用可能になります。したがって、単に次のようにできます：

``` php
Flight::render('hello');
```

`render`メソッドでテンプレートの名前を指定する際は、`.php`拡張子を省略することができます。

デフォルトでは、Flightはテンプレートファイルのために`views`ディレクトリを探します。次の構成を設定することで、テンプレートのための代替パスを設定できます：

``` php
Flight::set('flight.views.path', '/path/to/views');
```

### レイアウト

ウェブサイトには、入れ替え可能なコンテンツを持つ単一のレイアウトテンプレートファイルが一般的です。レイアウトに使用されるコンテンツをレンダリングするには、`render`メソッドにオプションのパラメータを渡します。

``` php
Flight::render('header', array('heading' => 'こんにちは'), 'header_content');
Flight::render('body', array('body' => '世界'), 'body_content');
```

これにより、ビューには`header_content`および`body_content`という保存された変数が作成されます。次に、次のようにレイアウトをレンダリングできます：

``` php
Flight::render('layout', array('title' => 'ホームページ'));
```

テンプレートファイルが次のようになっている場合：

`header.php`:

``` php
<h1><?php echo $heading; ?></h1>
```

`body.php`:

``` php
<div><?php echo $body; ?></div>
```

`layout.php`:

``` php
<html>
<head>
<title><?php echo $title; ?></title>
</head>
<body>
<?php echo $header_content; ?>
<?php echo $body_content; ?>
</body>
</html>
```

出力は次のようになります：

``` html
<html>
<head>
<title>ホームページ</title>
</head>
<body>
<h1>こんにちは</h1>
<div>世界</div>
</body>
</html>
```

### カスタムビュー

Flightは、デフォルトのビューエンジンを登録することによって入れ替えることを許可します。以下は、[Smarty](http://www.smarty.net/)テンプレートエンジンをビューとして使用する方法です：

``` php
// Smartyライブラリを読み込む
require './Smarty/libs/Smarty.class.php';

// Smartyをビュークラスとして登録する
// Smartyをロード時に構成するためのコールバック関数も渡します
Flight::register('view', 'Smarty', array(), function($smarty){
    $smarty->template_dir = './templates/';
    $smarty->compile_dir = './templates_c/';
    $smarty->config_dir = './config/';
    $smarty->cache_dir = './cache/';
});

// テンプレートデータを割り当てる
Flight::view()->assign('name', 'ボブ');

// テンプレートを表示する
Flight::view()->display('hello.tpl');
```

完全性のために、Flightのデフォルトのレンダーメソッドを上書きすることもできます：

``` php
Flight::map('render', function($template, $data){
    Flight::view()->assign($data);
    Flight::view()->display($template);
});
```

## <a name="errorhandling"></a> エラーハンドリング

### エラーと例外

すべてのエラーと例外はFlightによってキャッチされ、`error`メソッドに渡されます。デフォルトの動作は、一般的な`HTTP 500 Internal Server Error`レスポンスを送信し、いくつかのエラー情報が表示されます。

独自のニーズに合わせてこの動作を上書きすることができます：

``` php
Flight::map('error', function(Exception $ex){
    // エラーを処理する
    echo $ex->getTraceAsString();
});
```

デフォルトでは、エラーはウェブサーバーにログ記録されません。これを有効にするには、構成を変更します：

``` php
Flight::set('flight.log_errors', true);
```

### 見つかりません

URLが見つからない場合、Flightは`notFound`メソッドを呼び出します。デフォルトの動作は、簡単なメッセージを含む`HTTP 404 Not Found`レスポンスを送信します。

独自のニーズに合わせてこの動作を上書きすることができます：

``` php
Flight::map('notFound', function(){
    // 見つからなかった場合の処理
});
```

## <a name="redirects"></a> リダイレクト

現在のリクエストをリダイレクトするには、`redirect`メソッドを使用し、新しいURLを渡します。

``` php
Flight::redirect('/new/location');
```

デフォルトでFlightはHTTP 303ステータスコードを送信します。オプションでカスタムコードを設定できます：

``` php
Flight::redirect('/new/location', 401);
```

## <a name="requests"></a> リクエスト

FlightはHTTPリクエストを単一のオブジェクトにカプセル化します。アクセスするには、次のようにします：

``` php
$request = Flight::request();
```

リクエストオブジェクトは、次のプロパティを提供します：

``` html
url - 要求されているURL
base - URLの親サブディレクトリ
method - リクエストメソッド（GET、POST、PUT、DELETE）
referrer - 参照元URL
ip - クライアントのIPアドレス
ajax - リクエストがAJAXリクエストであるかどうか
scheme - サーバープロトコル（http、https）
user_agent - ブラウザ情報
type - コンテンツタイプ
length - コンテンツ長
query - クエリ文字列パラメータ
data - ポストデータまたはJSONデータ
cookies - クッキー情報
files - アップロードされたファイル
secure - 接続が安全かどうか
accept - HTTP受け入れパラメータ
proxy_ip - クライアントのプロキシIPアドレス
```

`query`、`data`、`cookies`、`files`プロパティには、配列またはオブジェクトとしてアクセスできます。

したがって、クエリ文字列パラメータを取得するには、次のようにします：

``` php
$id = Flight::request()->query['id'];
```

または次のようにすることもできます：

``` php
$id = Flight::request()->query->id;
```

### 生のリクエストボディ

生のHTTPリクエストボディを取得するには、例えばPUTリクエストを扱うときなどは、次のようにします：

``` php
$body = Flight::request()->getBody();
```

### JSON入力

`application/json`タイプでリクエストを送信し、データが`{"id": 123}`の場合、それは`data`プロパティから使用できます：

``` php
$id = Flight::request()->data->id;
```

## <a name="stopping"></a> 停止

`halt`メソッドを呼び出すことで、任意の時点でフレームワークを停止できます：

``` php
Flight::halt();
```

オプションでHTTPステータスコードとメッセージを指定することもできます：

``` php
Flight::halt(200, 'すぐ戻ります...');
```

`halt`を呼び出すと、それまでのレスポンスコンテンツが破棄されます。フレームワークを停止し、現在のレスポンスを出力したい場合は、`stop`メソッドを使用します：

``` php
Flight::stop();
```

## <a name="httpcaching"></a> HTTPキャッシング

FlightはHTTPレベルのキャッシングをサポートしています。キャッシング条件が満たされると、FlightはHTTP `304 Not Modified`レスポンスを返します。次回クライアントが同じリソースを要求すると、ローカルにキャッシュされたバージョンの使用を促されます。

### 最終更新日時

`lastModified`メソッドを使用してUNIXタイムスタンプを渡すことで、ページが最後に変更された日付と時刻を設定できます。クライアントは、最終更新値が変更されるまでキャッシュを使用し続けます。

``` php
Flight::route('/news', function(){
    Flight::lastModified(1234567890);
    echo 'このコンテンツはキャッシュされます。';
});
```

### ETag

`ETag`キャッシングは`Last-Modified`に似ていますが、リソースに対して任意のIDを指定できます：

``` php
Flight::route('/news', function(){
    Flight::etag('my-unique-id');
    echo 'このコンテンツはキャッシュされます。';
});
```

`lastModified`または`etag`を呼び出すと、キャッシュ値が設定され、チェックも行われます。リクエスト間でキャッシュ値が同じ場合、Flightは即座に`HTTP 304`レスポンスを送信し、処理を止めます。

## <a name="json"></a> JSON

FlightはJSONおよびJSONPレスポンスの送信をサポートします。JSONレスポンスを送信するには、JSONエンコードするデータを渡します：

``` php
Flight::json(array('id' => 123));
```

JSONPリクエストの場合、コールバック関数を定義するために使用するクエリパラメータ名をオプションで渡すことができます：

``` php
Flight::jsonp(array('id' => 123), 'q');
```

したがって、`?q=my_func`を使用してGETリクエストを行うと、出力は次のようになります：

``` json
my_func({"id":123});
```

クエリパラメータ名を渡さなかった場合、デフォルトは`jsonp`となります。

## <a name="configuration"></a> 設定

`set`メソッドを通じて設定値を設定することによって、Flightの特定の動作をカスタマイズできます。

``` php
Flight::set('flight.log_errors', true);
```

以下は、すべての利用可能な設定設定のリストです：

``` html 
flight.base_url - リクエストのベースURLを上書きします。（デフォルト：null）
flight.case_sensitive - URLの大文字と小文字を区別します。（デフォルト：false）
flight.handle_errors - Flightがすべてのエラーを内部で処理できるようにします。（デフォルト：true）
flight.log_errors - エラーをウェブサーバのエラーログファイルに記録します。（デフォルト：false）
flight.views.path - ビュー テンプレートファイルを含むディレクトリ。（デフォルト：./views）
flight.views.extension - ビュー テンプレートファイルの拡張子。（デフォルト：.php）
```

## <a name="frameworkmethods"></a> フレームワークメソッド

Flightは使いやすく、理解しやすいように設計されています。以下は、フレームワークの完全なメソッドセットです。これは通常の静的メソッドであるコアメソッドと、フィルタリングまたは上書き可能なマッピングされたメソッドである拡張可能なメソッドから構成されています。

### コアメソッド

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // カスタムフレームワークメソッドを作成します。
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // フレームワークメソッドにクラスを登録します。
Flight::before(string $name, callable $callback) // フレームワークメソッドの前にフィルタを追加します。
Flight::after(string $name, callable $callback) // フレームワークメソッドの後にフィルタを追加します。
Flight::path(string $path) // 自動読み込み用のパスを追加します。
Flight::get(string $key) // 変数を取得します。
Flight::set(string $key, mixed $value) // 変数を設定します。
Flight::has(string $key) // 変数が設定されているか確認します。
Flight::clear(array|string $key = []) // 変数をクリアします。
Flight::init() // フレームワークをデフォルト設定に初期化します。
Flight::app() // アプリケーションオブジェクトインスタンスを取得します。
```

### 拡張可能なメソッド

```php
Flight::start() // フレームワークを開始します。
Flight::stop() // フレームワークを停止し、レスポンスを送信します。
Flight::halt(int $code = 200, string $message = '') // オプションのステータスコードとメッセージを指定してフレームワークを停止します。
Flight::route(string $pattern, callable $callback, bool $pass_route = false) // URLパターンをコールバックにマッピングします。
Flight::group(string $pattern, callable $callback) // URLのグルーピングを作成します。パターンは文字列である必要があります。
Flight::redirect(string $url, int $code) // 別のURLにリダイレクトします。
Flight::render(string $file, array $data, ?string $key = null) // テンプレートファイルをレンダリングします。
Flight::error(Throwable $error) // HTTP 500レスポンスを送信します。
Flight::notFound() // HTTP 404レスポンスを送信します。
Flight::etag(string $id, string $type = 'string') // ETag HTTPキャッシングを行います。
Flight::lastModified(int $time) // 最終更新日時HTTPキャッシングを行います。
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // JSONレスポンスを送信します。
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // JSONPレスポンスを送信します。
```

`map`および`register`で追加したカスタムメソッドにもフィルタを適用できます。

## <a name="frameworkinstance"></a> フレームワークインスタンス

Flightをグローバルな静的クラスとして実行する代わりに、オブジェクトインスタンスとして実行することもできます。

``` php
require 'flight/autoload.php';

use flight\Engine;

$app = new Engine();

$app->route('/', function(){
    echo 'こんにちは、世界！';
});

$app->start();
```

このようにして、静的メソッドではなく、Engineオブジェクトの同じ名前のインスタンスメソッドを呼び出すことができます。
