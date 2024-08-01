# リクエスト

Flight は HTTP リクエストを 1 つのオブジェクトにカプセル化し、次のようにしてアクセスできます:

```php
$request = Flight::request();
```

## 典型的な使用例

Web アプリケーションでリクエストを操作している場合、通常はヘッダー、`$_GET`、`$_POST` パラメータ、または生のリクエスト ボディを取り出したいと考えるでしょう。Flight はこれらすべてを行うための簡単なインターフェースを提供します。

クエリ文字列パラメータを取得する例:

```php
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	echo "検索中: $keyword";
	// $keyword を使ってデータベースなどをクエリする
});
```

おそらく POST メソッドを使用するフォームの例:

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	echo "以下を提出しました: $name, $email";
	// $name と $email を使ってデータベースなどに保存
});
```

## リクエスト オブジェクトのプロパティ

リクエスト オブジェクトは次のプロパティを提供します:

- **body** - 生の HTTP リクエスト ボディ
- **url** - リクエストされている URL
- **base** - URL の親ディレクトリ
- **method** - リクエストメソッド (GET、POST、PUT、DELETE)
- **referrer** - リファラ URL
- **ip** - クライアントの IP アドレス
- **ajax** - リクエストが AJAX リクエストかどうか
- **scheme** - サーバー プロトコル (http、https)
- **user_agent** - ブラウザ情報
- **type** - コンテンツの種類
- **length** - コンテンツの長さ
- **query** - クエリ文字列パラメータ
- **data** - POST データまたは JSON データ
- **cookies** - クッキー データ
- **files** - アップロードされたファイル
- **secure** - 接続が安全かどうか
- **accept** - HTTP Accept パラメータ
- **proxy_ip** - クライアントのプロキシ IP アドレス。`$_SERVER` 配列を `HTTP_CLIENT_IP`、`HTTP_X_FORWARDED_FOR`、`HTTP_X_FORWARDED`、`HTTP_X_CLUSTER_CLIENT_IP`、`HTTP_FORWARDED_FOR`、`HTTP_FORWARDED` の順にスキャンします。
- **host** - リクエストホスト名

`query`、`data`、`cookies`、および `files` プロパティには配列またはオブジェクトとしてアクセスできます。

したがって、クエリ文字列パラメータを取得するには、次のようにします:

```php
$id = Flight::request()->query['id'];
```

または次のようにします:

```php
$id = Flight::request()->query->id;
```

## 生の HTTP リクエスト ボディ

PUT リクエストなどを処理する場合など、生の HTTP リクエスト ボディを取得するには、次のようにします:

```php
$body = Flight::request()->getBody();
```

## JSON 入力

`application/json` タイプとデータ `{"id": 123}` を使用してリクエストを送信すると、そのデータは `data` プロパティから利用できます:

```php
$id = Flight::request()->data->id;
```

## `$_GET`

`query` プロパティを使って `$_GET` 配列にアクセスできます:

```php
$id = Flight::request()->query['id'];
```

## `$_POST`

`data` プロパティを使って `$_POST` 配列にアクセスできます:

```php
$id = Flight::request()->data['id'];
```

## `$_COOKIE`

`cookies` プロパティを使って `$_COOKIE` 配列にアクセスできます:

```php
$myCookieValue = Flight::request()->cookies['myCookieName'];
```

## `$_SERVER`

`getVar()` メソッドを通じて `$_SERVER` 配列にアクセスするショートカットがあります:

```php

$host = Flight::request()->getVar['HTTP_HOST'];
```

## `$_FILES` 経由のアップロードされたファイル

`files` プロパティを使ってアップロードされたファイルにアクセスできます:

```php
$uploadedFile = Flight::request()->files['myFile'];
```

## リクエスト ヘッダー

`getHeader()` メソッドや `getHeaders()` メソッドを使用してリクエストヘッダーにアクセスできます:

```php

// たとえば Authorization ヘッダーが必要な場合
$host = Flight::request()->getHeader('Authorization');
// または
$host = Flight::request()->header('Authorization');

// すべてのヘッダーを取得する場合
$headers = Flight::request()->getHeaders();
// または
$headers = Flight::request()->headers();
```

## リクエスト ボディ

`getBody()` メソッドを使用して生のリクエストボディにアクセスできます:

```php
$body = Flight::request()->getBody();
```

## リクエストメソッド

`method` プロパティまたは `getMethod()` メソッドを使用してリクエストメソッドにアクセスできます:

```php
$method = Flight::request()->method; // 実際には getMethod() を呼び出します
$method = Flight::request()->getMethod();
```

**注意:** `getMethod()` メソッドはまず `$_SERVER['REQUEST_METHOD']` からメソッドを取得し、存在する場合は `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']` によって上書きされ、存在する場合は `$_REQUEST['_method']` によって上書きされます。

## リクエスト URL

URL の構成要素を手軽に取得するためのいくつかのヘルパー メソッドがあります。

### フル URL

`getFullUrl()` メソッドを使用してフルリクエスト URL にアクセスできます:

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```
### ベース URL

`getBaseUrl()` メソッドを使用してベース URL にアクセスできます:

```php
$url = Flight::request()->getBaseUrl();
// 末尾にスラッシュがないことに注意してください。
// https://example.com
```

## クエリの解析

`parseQuery()` メソッドに URL を渡すことで、クエリ文字列を連想配列に解析できます:

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```