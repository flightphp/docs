# Anfragen

Der Flug kapselt die HTTP-Anfrage in ein einziges Objekt, auf das zugegriffen werden kann, indem Folgendes gemacht wird:

```php
$request = Flight::request();
```

Das Anfragenobjekt stellt die folgenden Eigenschaften bereit:

- **url** - Die angeforderte URL
- **base** - Das übergeordnete Unterverzeichnis der URL
- **methode** - Die Anfragemethode (GET, POST, PUT, DELETE)
- **verweiser** - Die Verweis-URL
- **ip** - IP-Adresse des Clients
- **ajax** - Ob die Anfrage eine AJAX-Anfrage ist
- **schema** - Das Serverprotokoll (http, https)
- **benutzer_agent** - Browserinformationen
- **art** - Der Inhaltstyp
- **länge** - Die Inhaltslänge
- **abfrage** - Abfragezeichenfolgenparameter
- **daten** - Postdaten oder JSON-Daten
- **cookies** - Cookiedaten
- **dateien** - Hochgeladene Dateien
- **sicher** - Ob die Verbindung sicher ist
- **akzeptieren** - HTTP-Akzeptanzparameter
- **proxy_ip** - Proxy-IP-Adresse des Clients
- **host** - Der angeforderte Hostname

Sie können auf die Eigenschaften `query`, `data`, `cookies` und `files` als Arrays oder Objekte zugreifen.

Daher um einen Query-String-Parameter zu erhalten, können Sie Folgendes tun:

```php
$id = Flight::request()->query['id'];
```

Oder Sie können Folgendes tun:

```php
$id = Flight::request()->query->id;
```

## ROHER Anfragekörper

Um den rohen HTTP-Anfragekörper zu erhalten, z. B. beim Umgang mit PUT-Anfragen, können Sie Folgendes tun:

```php
$body = Flight::request()->getBody();
```

## JSON-Eingabe

Wenn Sie eine Anfrage mit dem Typ `application/json` und den Daten `{"id": 123}` senden, sind sie über die Eigenschaft `data` verfügbar:

```php
$id = Flight::request()->data->id;
```