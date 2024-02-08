# リクエスト

FlightはHTTPリクエストを1つのオブジェクトにカプセル化し、次のようにアクセスできます：

```php
$request = Flight::request();
```

リクエストオブジェクトは次のプロパティを提供します：

- **body** - 生のHTTPリクエストボディ
- **url** - 要求されているURL
- **base** - URLの親サブディレクトリ
- **method** - リクエストメソッド（GET、POST、PUT、DELETE）
- **referrer** - リファラURL
- **ip** - クライアントのIPアドレス
- **ajax** - リクエストがAJAXリクエストかどうか
- **scheme** - サーバープロトコル（http、https）
- **user_agent** - ブラウザの情報
- **type** - コンテンツタイプ
- **length** - コンテンツの長さ
- **query** - クエリ文字列パラメータ
- **data** - POSTデータまたはJSONデータ
- **cookies** - クッキーデータ
- **files** - アップロードされたファイル
- **secure** - 接続がセキュアかどうか
- **accept** - HTTPアクセプトパラメータ
- **proxy_ip** - クライアントのプロキシIPアドレス
- **host** - リクエストホスト名

`query`、 `data`、 `cookies`、および `files` プロパティには、配列またはオブジェクトとしてアクセスできます。

したがって、クエリ文字列パラメータを取得するには、次のようにします：

```php
$id = Flight::request()->query['id'];
```

または、次のようにすることもできます：

```php
$id = Flight::request()->query->id;
```

## RAWリクエストボディ

例えばPUTリクエストを扱う場合など、生のHTTPリクエストボディを取得するには、次のようにします：

```php
$body = Flight::request()->getBody();
```

## JSON入力

タイプが `application/json` でデータが `{"id": 123}` を含むリクエストを送信した場合、それは `data` プロパティから利用できるようになります：

```php
$id = Flight::request()->data->id;
```

## `$_SERVER`へのアクセス

`getVar()` メソッドを介して `$_SERVER` 配列にアクセスするショートカットが利用可能です：

```php

$host = Flight::request()->getVar['HTTP_HOST'];
```

## リクエストヘッダーのアクセス

`getHeader()` または `getHeaders()` メソッドを使用してリクエストヘッダーにアクセスできます：

```php

// Authorizationヘッダーが必要な場合
$host = Flight::request()->getHeader('Authorization');

// すべてのヘッダーを取得する必要がある場合
$headers = Flight::request()->getHeaders();
```