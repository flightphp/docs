# Anfragen

Flight encapsuliert die HTTP-Anfrage in ein einziges Objekt, das über folgenden Zugriff erhalten werden kann:

```php
$request = Flight::request();
```

## Typische Anwendungsfälle

Wenn Sie mit einer Anfrage in einer Webanwendung arbeiten, möchten Sie in der Regel einen Header, einen `$_GET`- oder `$_POST`-Parameter oder sogar den Rohanforderungstext extrahieren. Flight bietet eine einfache Schnittstelle, um all diese Dinge zu erledigen.

Hier ist ein Beispiel zum Abrufen eines Abfragezeichenfolgenparameters:

```php
Flight::route('/suche', function(){
	$keyword = Flight::request()->query['keyword'];
	echo "Sie suchen nach: $keyword";
	// Eine Datenbank abfragen oder etwas anderes mit dem $keyword machen
});
```

Hier ist ein Beispiel für ein Formular mit einer POST-Methode:

```php
Flight::route('POST /senden', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	echo "Sie haben übermittelt: $name, $email";
	// In einer Datenbank speichern oder etwas anderes mit dem $name und $email machen
});
```

## Eigenschaften des Anfrageobjekts

Das Anfrageobjekt bietet die folgenden Eigenschaften an:

- **body** - Der rohe HTTP-Anfragekörper
- **url** - Die angeforderte URL
- **base** - Das übergeordnete Unterverzeichnis der URL
- **method** - Der Anfragemethode (GET, POST, PUT, DELETE)
- **referrer** - Die Referrer-URL
- **ip** - IP-Adresse des Clients
- **ajax** - Ob die Anfrage eine AJAX-Anfrage ist
- **scheme** - Das Serverprotokoll (http, https)
- **user_agent** - Browserinformationen
- **type** - Der Inhaltstyp
- **length** - Die Inhaltslänge
- **query** - Abfragezeichenfolgenparameter
- **data** - Postdaten oder JSON-Daten
- **cookies** - Cookiedaten
- **files** - Hochgeladene Dateien
- **secure** - Ob die Verbindung sicher ist
- **accept** - HTTP-Akzeptparameter
- **proxy_ip** - Proxy-IP-Adresse des Clients. Sucht im `$_SERVER`-Array nach `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED` in dieser Reihenfolge.
- **host** - Der Name des angeforderten Hosts

Sie können auf die `query`, `data`, `cookies` und `files` Eigenschaften als Arrays oder Objekte zugreifen.

Um also einen Abfragezeichenfolgenparameter zu erhalten, können Sie folgendes tun:

```php
$id = Flight::request()->query['id'];
```

Oder Sie können dies tun:

```php
$id = Flight::request()->query->id;
```

## Rohanforderungstext

Um den rohen HTTP-Anfragekörper zu erhalten, z. B. bei der Verarbeitung von PUT-Anfragen, können Sie Folgendes tun:

```php
$body = Flight::request()->getBody();
```

## JSON-Eingabe

Wenn Sie eine Anfrage mit dem Typ `application/json` und den Daten `{"id": 123}` senden, werden diese über die `data`-Eigenschaft verfügbar sein:

```php
$id = Flight::request()->data->id;
```

## `$_GET`

Sie können auf das `$_GET`-Array über die `query`-Eigenschaft zugreifen:

```php
$id = Flight::request()->query['id'];
```

## `$_POST`

Sie können auf das `$_POST`-Array über die `data`-Eigenschaft zugreifen:

```php
$id = Flight::request()->data['id'];
```

## `$_COOKIE`

Sie können auf das `$_COOKIE`-Array über die `cookies`-Eigenschaft zugreifen:

```php
$meinCookieWert = Flight::request()->cookies['meinCookieName'];
```

## `$_SERVER`

Es steht eine Abkürzung zum Zugriff auf das `$_SERVER`-Array über die Methode `getVar()` zur Verfügung:

```php

$host = Flight::request()->getVar['HTTP_HOST'];
```

## Hochgeladene Dateien über `$_FILES`

Sie können auf hochgeladene Dateien über die `files`-Eigenschaft zugreifen:

```php
$hochgeladeneDatei = Flight::request()->files['meineDatei'];
```

## Anforderungshauptzeilen

Sie können auf Anforderungshauptzeilen mit der Methode `getHeader()` oder `getHeaders()` zugreifen:

```php

// Möglicherweise benötigen Sie das Autorisierungshauptzeichen
$host = Flight::request()->getHeader('Authorization');
// oder
$host = Flight::request()->header('Authorization');

// Wenn Sie alle Header abrufen müssen
$headers = Flight::request()->getHeaders();
// oder
$headers = Flight::request()->headers();
```

## Anforderungstext

Sie können auf den rohen Anforderungstext mit der Methode `getBody()` zugreifen:

```php
$body = Flight::request()->getBody();
```

## Anforderungsmethode

Sie können auf die Anforderungsmethode mit der `method`-Eigenschaft oder der Methode `getMethod()` zugreifen:

```php
$method = Flight::request()->method; // ruft tatsächlich getMethod() auf
$method = Flight::request()->getMethod();
```

**Hinweis:** Die Methode `getMethod()` zieht die Methode zuerst aus `$_SERVER['REQUEST_METHOD']`, dann kann sie von `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']` überschrieben werden, wenn es existiert, oder von `$_REQUEST['_method']`, wenn es existiert.

## Anforderungs-URLs

Es gibt ein paar Hilfsmethoden, um Teile einer URL für Ihre Bequemlichkeit zusammensetzen.

### Volle URL

Sie können die vollständige Anforderungs-URL mit der Methode `getFullUrl()` abrufen:

```php
$url = Flight::request()->getFullUrl();
// https://beispiel.com/ein/pfad?foo=balken
```
### Basis-URL

Die Basis-URL können Sie mit der Methode `getBaseUrl()` abrufen:

```php
$url = Flight::request()->getBaseUrl();
// Beachten Sie, kein abschließender Schrägstrich.
// https://beispiel.com
```

## Abfrageanalyse

Sie können einer URL die `parseQuery()`-Methode übergeben, um die Abfragezeichenfolge in ein assoziatives Array zu analysieren:

```php
$query = Flight::request()->parseQuery('https://beispiel.com/ein/pfad?foo=balken');
// ['foo' => 'balken']
```  