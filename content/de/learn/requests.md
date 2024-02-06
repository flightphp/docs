# Anfragen

Flight kapselt die HTTP-Anfrage in ein einziges Objekt, auf das zugegriffen werden kann, indem:

```php
$request = Flight::request();
```

Das Anfrageobjekt bietet die folgenden Eigenschaften:

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

Sie können auf die Eigenschaften `query`, `data`, `cookies` und `files`
als Arrays oder Objekte zugreifen.

Um beispielsweise einen Abfragezeichenfolgenparameter zu erhalten, können Sie Folgendes tun:

```php
$id = Flight::request()->query['id'];
```

Oder Sie können Folgendes tun:

```php
$id = Flight::request()->query->id;
```

## RAW Request-Body

Um den RAW-HTTP-Anforderungskörper zu erhalten, beispielsweise bei der Bearbeitung von PUT-Anfragen,
können Sie Folgendes tun:

```php
$body = Flight::request()->getBody();
```

## JSON-Eingabe

Wenn Sie eine Anfrage mit dem Typ `application/json` und den Daten `{"id": 123}` senden,
ist es über die `data`-Eigenschaft verfügbar:

```php
$id = Flight::request()->data->id;
```