# Anfragen

Flight kapselt die HTTP-Anfrage in ein einzelnes Objekt, auf das über Folgendes zugegriffen werden kann:

```php
$request = Flight::request();
```

Das Anfrageobjekt bietet die folgenden Eigenschaften:

- **body** - Der rohe HTTP-Anforderungstext
- **url** - Die angeforderte URL
- **base** - Das übergeordnete Unterverzeichnis der URL
- **method** - Die Anfragemethode (GET, POST, PUT, DELETE)
- **referrer** - Die Referrer-URL
- **ip** - IP-Adresse des Clients
- **ajax** - Ob die Anfrage eine AJAX-Anfrage ist
- **scheme** - Das Serverprotokoll (http, https)
- **user_agent** - Browserinformationen
- **type** - Der Inhaltstyp
- **length** - Die Inhaltslänge
- **query** - Abfragezeichenfolgenparameter
- **data** - Post-Daten oder JSON-Daten
- **cookies** - Cookie-Daten
- **files** - Hochgeladene Dateien
- **secure** - Ob die Verbindung sicher ist
- **accept** - HTTP-Akzept-Parameter
- **proxy_ip** - Proxy-IP-Adresse des Clients
- **host** - Der Anforderungshostname

Sie können auf die Eigenschaften `query`, `data`, `cookies` und `files` als Arrays oder Objekte zugreifen.

Um beispielweise einen Abfragezeichenfolgenparameter zu erhalten, können Sie Folgendes tun:

```php
$id = Flight::request()->query['id'];
```

Oder Sie können Folgendes tun:

```php
$id = Flight::request()->query->id;
```

## Roher Anforderungstext

Um den rohen HTTP-Anforderungstext zu erhalten, beispielsweise beim Umgang mit PUT-Anforderungen, können Sie Folgendes tun:

```php
$body = Flight::request()->getBody();
```

## JSON-Eingabe

Wenn Sie eine Anforderung mit dem Typ `application/json` und den Daten `{"id": 123}` senden, stehen sie über die `data`-Eigenschaft zur Verfügung:

```php
$id = Flight::request()->data->id;
```

## Zugriff auf `$_SERVER`

Es gibt eine Abkürzung zum Zugriff auf das `$_SERVER`-Array über die Methode `getVar()`:

```php

$host = Flight::request()->getVar['HTTP_HOST'];
```

## Zugriff auf Anforderungsheader

Sie können auf Anforderungsheader über die Methoden `getHeader()` oder `getHeaders()` zugreifen:

```php

// Möglicherweise benötigen Sie den Autorisierungsheader
$host = Flight::request()->getHeader('Authorization');

// Wenn Sie alle Header abrufen müssen
$headers = Flight::request()->getHeaders();
```