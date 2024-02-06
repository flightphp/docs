# リクエスト

FlightはHTTPリクエストを単一のオブジェクトにカプセル化し、次のようにアクセスできます：

```php
$request = Flight::request();
```

リクエストオブジェクトは次のプロパティを提供します：

- **url** - リクエストされているURL
- **base** - URLの親ディレクトリ
- **method** - リクエストメソッド（GET、POST、PUT、DELETE）
- **referrer** - リファラURL
- **ip** - クライアントのIPアドレス
- **ajax** - リクエストがAjaxリクエストかどうか
- **scheme** - サーバのプロトコル（http、https）
- **user_agent** - ブラウザの情報
- **type** - コンテンツタイプ
- **length** - コンテンツ長
- **query** - クエリ文字列パラメータ
- **data** - POSTデータまたはJSONデータ
- **cookies** - Cookieデータ
- **files** - アップロードされたファイル
- **secure** - 接続が安全であるかどうか
- **accept** - HTTP Acceptパラメータ
- **proxy_ip** - クライアントのプロキシIPアドレス
- **host** - リクエストホスト名

`query`、`data`、`cookies`、`files`のプロパティには、それぞれ配列またはオブジェクトとしてアクセスできます。

したがって、クエリ文字列パラメータを取得するには、次のようにします：

```php
$id = Flight::request()->query['id'];
```

もしくは、次のようにします：

```php
$id = Flight::request()->query->id;
```

## RAWリクエストボディ

PUTリクエストを処理する際など、生のHTTPリクエストボディを取得するには次のようにします：

```php
$body = Flight::request()->getBody();
```

## JSON入力

`application/json`タイプとデータ`{"id": 123}`を含むリクエストを送信すると、それは`data`プロパティから利用可能になります：

```php
$id = Flight::request()->data->id;
```