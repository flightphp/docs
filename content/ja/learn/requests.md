# リクエスト

Flight は、HTTP リクエストを単一のオブジェクトにカプセル化し、次のようにアクセスできます：

```php
$request = Flight::request();
```

リクエストオブジェクトは、次のプロパティを提供します：

- **body** - 生の HTTP リクエストボディ
- **url** - リクエストされている URL
- **base** - URL の親ディレクトリ
- **method** - リクエストメソッド (GET、POST、PUT、DELETE)
- **referrer** - リファラ URL
- **ip** - クライアントの IP アドレス
- **ajax** - リクエストが AJAX リクエストであるかどうか
- **scheme** - サーバープロトコル (http、https)
- **user_agent** - ブラウザ情報
- **type** - コンテンツタイプ
- **length** - コンテンツ長
- **query** - クエリ文字列パラメータ
- **data** - POST データまたは JSON データ
- **cookies** - クッキーデータ
- **files** - アップロードされたファイル
- **secure** - 接続がセキュアかどうか
- **accept** - HTTP accept パラメータ
- **proxy_ip** - クライアントのプロキシ IP アドレス
- **host** - リクエストホスト名

`query`、`data`、`cookies`、`files` プロパティには、配列やオブジェクトとしてアクセスできます。

そのため、クエリ文字列パラメータを取得するには、次のようにします：

```php
$id = Flight::request()->query['id'];
```

または、次のようにもできます：

```php
$id = Flight::request()->query->id;
```

## RAW リクエストボディ

例えば PUT リクエストを処理する場合など、生の HTTP リクエストボディを取得するには、次のようにします：

```php
$body = Flight::request()->getBody();
```

## JSON 入力

タイプが `application/json` でデータが `{"id": 123}` としてリクエストを送信した場合、それは `data` プロパティから利用できます：

```php
$id = Flight::request()->data->id;
```

## `$_SERVER` へのアクセス

`getVar()` メソッドを介して `$_SERVER` 配列にアクセスするショートカットが利用可能です：

```php
$host = Flight::request()->getVar['HTTP_HOST'];
```

## リクエストヘッダへのアクセス

`getHeader()` または `getHeaders()` メソッドを使用してリクエストヘッダにアクセスできます：

```php
// たとえば、Authorization ヘッダが必要な場合
$host = Flight::request()->getHeader('Authorization');

// すべてのヘッダを取得する必要がある場合
$headers = Flight::request()->getHeaders();
```