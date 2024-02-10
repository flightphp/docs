# Anfragen

Flight kapselt die HTTP-Anfrage in ein einzelnes Objekt, auf das zugegriffen werden kann, indem Folgendes getan wird:

```php
$request = Flight::request();
```

Das Anfrageobjekt stellt die folgenden Eigenschaften bereit:

- **body** - Der Roh-HTTP-Anfragekörper
- **url** - Die angeforderte URL
- **base** - Das übergeordnete Unterverzeichnis der URL
- **method** - Die Anfragemethode (GET, POST, PUT, DELETE)
- **referrer** - Die Referrer-URL
- **ip** - IP-Adresse des Clients
- **ajax** - Ob die Anfrage eine AJAX-Anfrage ist
- **scheme** - Das Serverprotokoll (http, https)
- **user_agent** - Browserinformationen
- **type** - Der Inhalts Typ
- **length** - Die Inhaltslänge
- **query** - Abfragezeichenfolgenparameter
- **data** - Post-Daten oder JSON-Daten
- **cookies** - Cookie-Daten
- **files** - Hochgeladene Dateien
- **secure** - Ob die Verbindung sicher ist
- **accept** - HTTP-Akzept-Parameter
- **proxy_ip** - Proxy-IP-Adresse des Clients
- **host** - Der Anforderungs-Hostname

Sie können auf die Eigenschaften `query`, `data`, `cookies` und `files` als Arrays oder Objekte zugreifen.

Um beispielsweise einen Abfragezeichenfolgenparameter zu erhalten, können Sie Folgendes tun:

```php
$id = Flight::request()->query['id'];
```

Oder Sie können Folgendes tun:

```php
$id = Flight::request()->query->id;
```

## ROHER Anfragekörper

Um den Roh-HTTP-Anfragekörper zu erhalten, beispielsweise beim Umgang mit PUT-Anfragen, können Sie Folgendes tun:

```php
$body = Flight::request()->getBody();
```

## JSON-Eingabe

Wenn Sie eine Anfrage mit dem Typ `application/json` und den Daten `{"id": 123}` senden, sind diese über die `data`-Eigenschaft verfügbar:

```php
$id = Flight::request()->data->id;
```

## Zugriff auf `$_SERVER`

Es gibt eine Abkürzung zum Zugriff auf das Array `$_SERVER` über die Methode `getVar()`:

```php

$host = Flight::request()->getVar['HTTP_HOST'];
```

## Zugriff auf Anfrageheader

Sie können auf Anfrageheader mit der Methode `getHeader()` oder `getHeaders()` zugreifen:

```php

// Möglicherweise benötigen Sie den Autorisierungsheader
$host = Flight::request()->getHeader('Authorization');

// Wenn Sie alle Header abrufen müssen
$headers = Flight::request()->getHeaders();
```