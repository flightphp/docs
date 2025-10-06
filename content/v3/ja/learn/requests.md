# リクエスト

## 概要

Flight は HTTP リクエストを単一のオブジェクトにカプセル化し、以下の方法でアクセスできます：

```php
$request = Flight::request();
```

## 理解

HTTP リクエストは、HTTP ライフサイクルの理解に不可欠なコア要素の一つです。ユーザーがウェブブラウザや HTTP クライアントでアクションを実行すると、ヘッダー、本文、URL などをあなたのプロジェクトに送信します。これらのヘッダー（ブラウザの言語、扱える圧縮の種類、ユーザーエージェントなど）をキャプチャし、Flight アプリケーションに送信された本文と URL をキャプチャできます。これらのリクエストは、アプリが次に何をするかを理解するために不可欠です。

## 基本的な使用方法

PHP には `$_GET`、`$_POST`、`$_REQUEST`、`$_SERVER`、`$_FILES`、`$_COOKIE` などのスーパーグローバルがあります。Flight はこれらを便利な [Collections](/learn/collections) に抽象化します。`query`、`data`、`cookies`、`files` プロパティを配列またはオブジェクトとしてアクセスできます。

> **注意:** プロジェクトでこれらのスーパーグローバルを使用することは**強く**推奨されません。`request()` オブジェクト経由で参照してください。

> **注意:** `$_ENV` の抽象化は利用できません。

### `$_GET`

`$_GET` 配列は `query` プロパティ経由でアクセスできます：

```php
// GET /search?keyword=something
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	// または
	$keyword = Flight::request()->query->keyword;
	echo "You are searching for: $keyword";
	// $keyword でデータベースをクエリしたり、その他のことを行います
});
```

### `$_POST`

`$_POST` 配列は `data` プロパティ経由でアクセスできます：

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	// または
	$name = Flight::request()->data->name;
	$email = Flight::request()->data->email;
	echo "You submitted: $name, $email";
	// $name と $email でデータベースに保存したり、その他のことを行います
});
```

### `$_COOKIE`

`$_COOKIE` 配列は `cookies` プロパティ経由でアクセスできます：

```php
Flight::route('GET /login', function(){
	$savedLogin = Flight::request()->cookies['myLoginCookie'];
	// または
	$savedLogin = Flight::request()->cookies->myLoginCookie;
	// 本当に保存されているかチェックし、保存されている場合は自動的にログインします
	if($savedLogin) {
		Flight::redirect('/dashboard');
		return;
	}
});
```

新しいクッキー値の設定に関するヘルプについては、[overclokk/cookie](/awesome-plugins/php-cookie) を参照してください。

### `$_SERVER`

`$_SERVER` 配列は `getVar()` メソッド経由でアクセスするためのショートカットがあります：

```php

$host = Flight::request()->getVar('HTTP_HOST');
```

### `$_FILES`

アップロードされたファイルは `files` プロパティ経由でアクセスできます：

```php
// $_FILES プロパティへの生アクセス。推奨アプローチは以下を参照
$uploadedFile = Flight::request()->files['myFile']; 
// または
$uploadedFile = Flight::request()->files->myFile;
```

詳細については [Uploaded File Handler](/learn/uploaded-file) を参照してください。

#### ファイルアップロードの処理

_v3.12.0_

フレームワークを使用してヘルパーメソッドでファイルアップロードを処理できます。基本的に、リクエストからファイルデータを引き出し、新しい場所に移動するだけです。

```php
Flight::route('POST /upload', function(){
	// 入力フィールドが <input type="file" name="myFile"> のような場合
	$uploadedFileData = Flight::request()->getUploadedFiles();
	$uploadedFile = $uploadedFileData['myFile'];
	$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
});
```

複数のファイルがアップロードされた場合、それらをループで処理できます：

```php
Flight::route('POST /upload', function(){
	// 入力フィールドが <input type="file" name="myFiles[]"> のような場合
	$uploadedFiles = Flight::request()->getUploadedFiles()['myFiles'];
	foreach ($uploadedFiles as $uploadedFile) {
		$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
	}
});
```

> **セキュリティ注意:** ユーザー入力の検証とサニタイズを常に実行してください。特にファイルアップロードの場合、許可する拡張子の種類を検証し、ファイルの「マジックバイト」を検証して、ユーザーが主張するファイルの種類であることを確認してください。このヘルプには [記事](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [と](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [ライブラリ](https://github.com/RikudouSage/MimeTypeDetector) が利用可能です。

### リクエスト本文

POST/PUT リクエストなどの生の HTTP リクエスト本文を取得するには、以下のようにします：

```php
Flight::route('POST /users/xml', function(){
	$xmlBody = Flight::request()->getBody();
	// 送信された XML で何かを行います。
});
```

### JSON 本文

コンテンツタイプ `application/json` のリクエストを受け取り、例として `{"id": 123}` のデータの場合、`data` プロパティから利用可能です：

```php
$id = Flight::request()->data->id;
```

### リクエストヘッダー

`getHeader()` または `getHeaders()` メソッドを使用してリクエストヘッダーにアクセスできます：

```php

// Authorization ヘッダーが必要な場合
$host = Flight::request()->getHeader('Authorization');
// または
$host = Flight::request()->header('Authorization');

// すべてのヘッダーを取得する場合
$headers = Flight::request()->getHeaders();
// または
$headers = Flight::request()->headers();
```

### リクエストメソッド

`method` プロパティまたは `getMethod()` メソッドを使用してリクエストメソッドにアクセスできます：

```php
$method = Flight::request()->method; // 実際には getMethod() で設定されます
$method = Flight::request()->getMethod();
```

**注意:** `getMethod()` メソッドは最初に `$_SERVER['REQUEST_METHOD']` からメソッドを引き出し、存在する場合に `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']` または `$_REQUEST['_method']` で上書きできます。

## リクエストオブジェクトのプロパティ

リクエストオブジェクトは以下のプロパティを提供します：

- **body** - 生の HTTP リクエスト本文
- **url** - リクエストされている URL
- **base** - URL の親サブディレクトリ
- **method** - リクエストメソッド (GET, POST, PUT, DELETE)
- **referrer** - リファラー URL
- **ip** - クライアントの IP アドレス
- **ajax** - リクエストが AJAX リクエストかどうか
- **scheme** - サーバープロトコル (http, https)
- **user_agent** - ブラウザ情報
- **type** - コンテンツタイプ
- **length** - コンテンツ長
- **query** - クエリ文字列パラメータ
- **data** - 投稿データまたは JSON データ
- **cookies** - クッキーデータ
- **files** - アップロードされたファイル
- **secure** - 接続がセキュアかどうか
- **accept** - HTTP accept パラメータ
- **proxy_ip** - クライアントのプロキシ IP アドレス。`$_SERVER` 配列を `HTTP_CLIENT_IP`、`HTTP_X_FORWARDED_FOR`、`HTTP_X_FORWARDED`、`HTTP_X_CLUSTER_CLIENT_IP`、`HTTP_FORWARDED_FOR`、`HTTP_FORWARDED` の順でスキャンします。
- **host** - リクエストホスト名
- **servername** - `$_SERVER` からの SERVER_NAME

## ヘルパーメソッド

URL の一部を組み合わせたり、特定のヘッダーを扱うためのいくつかのヘルパーメソッドがあります。

### フル URL

`getFullUrl()` メソッドを使用してフルリクエスト URL にアクセスできます：

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```

### ベース URL

`getBaseUrl()` メソッドを使用してベース URL にアクセスできます：

```php
// http://example.com/path/to/something/cool?query=yes+thanks
$url = Flight::request()->getBaseUrl();
// https://example.com
// 注意: 末尾のスラッシュはありません。
```

## クエリ解析

`parseQuery()` メソッドに URL を渡すと、クエリ文字列を連想配列に解析できます：

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```

## コンテンツ Accept タイプのネゴシエーション

_v3.17.2_

`negotiateContentType()` メソッドを使用して、クライアントが送信した `Accept` ヘッダーに基づいて、最適なコンテンツタイプを決定できます。

```php

// 例: Accept ヘッダー: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8
// 以下でサポートするものを定義します。
$availableTypes = ['application/json', 'application/xml'];
$typeToServe = Flight::request()->negotiateContentType($availableTypes);
if ($typeToServe === 'application/json') {
	// JSON レスポンスを送信
} elseif ($typeToServe === 'application/xml') {
	// XML レスポンスを送信
} else {
	// デフォルトで何か他のものを設定するか、エラーをスロー
}
```

> **注意:** `Accept` ヘッダーに利用可能なタイプが見つからない場合、メソッドは `null` を返します。`Accept` ヘッダーが定義されていない場合、メソッドは `$availableTypes` 配列の最初のタイプを返します。

## 関連項目
- [Routing](/learn/routing) - ルートをコントローラーにマッピングし、ビューをレンダリングする方法を参照。
- [Responses](/learn/responses) - HTTP レスポンスをカスタマイズする方法。
- [Why a Framework?](/learn/why-frameworks) - リクエストが全体像にどのように適合するかを理解。
- [Collections](/learn/collections) - データのコレクションを扱う。
- [Uploaded File Handler](/learn/uploaded-file) - ファイルアップロードの処理。

## トラブルシューティング
- `request()->ip` と `request()->proxy_ip` は、ウェブサーバーがプロキシ、ロードバランサーなどの背後にある場合に異なる可能性があります。

## 変更履歴
- v3.17.2 - negotiateContentType() を追加
- v3.12.0 - リクエストオブジェクト経由でファイルアップロードを扱う機能を追加。
- v1.0 - 初回リリース。