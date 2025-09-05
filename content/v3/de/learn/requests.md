# Anfragen

Flight kapselt die HTTP-Anfrage in ein einzelnes Objekt, das wie folgt zugänglich ist:

```php
$request = Flight::request();
```

## Typische Anwendungsfälle

Wenn du in einer Web-Anwendung mit einer Anfrage arbeitest, möchtest du normalerweise einen Header abrufen, oder einen `$_GET`- oder `$_POST`-Parameter, oder vielleicht sogar den rohen Anfragekörper. Flight bietet eine einfache Schnittstelle, um all diese Dinge zu erledigen.

Hier ein Beispiel, um einen Query-String-Parameter zu erhalten:

```php
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	echo "You are searching for: $keyword";
	// eine Datenbank abfragen oder etwas anderes mit dem $keyword tun
});
```

Hier ein Beispiel für ein Formular mit einer POST-Methode:

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	echo "You submitted: $name, $email";
	// in einer Datenbank speichern oder etwas anderes mit dem $name und $email tun
});
```

## Eigenschaften des Anfrageobjekts

Das Anfrageobjekt bietet die folgenden Eigenschaften:

- **body** - Der rohe HTTP-Anfragekörper
- **url** - Die angefragte URL
- **base** - Das übergeordnete Unterverzeichnis der URL
- **method** - Die Anfragemethode (GET, POST, PUT, DELETE)
- **referrer** - Die Referrer-URL
- **ip** - Die IP-Adresse des Clients
- **ajax** - Ob es sich um eine AJAX-Anfrage handelt
- **scheme** - Das Server-Protokoll (http, https)
- **user_agent** - Browser-Informationen
- **type** - Der Inhaltstyp
- **length** - Die Inhaltslänge
- **query** - Query-String-Parameter
- **data** - Post-Daten oder JSON-Daten
- **cookies** - Cookie-Daten
- **files** - Hochgeladene Dateien
- **secure** - Ob die Verbindung sicher ist
- **accept** - HTTP-Accept-Parameter
- **proxy_ip** - Proxy-IP-Adresse des Clients. Durchsucht das `$_SERVER`-Array nach `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED` in dieser Reihenfolge.
- **host** - Der Anfrage-Hostname
- **servername** - Der SERVER_NAME aus `$_SERVER`

Du kannst die Eigenschaften `query`, `data`, `cookies` und `files` als Arrays oder Objekte verwenden.

Um also einen Query-String-Parameter zu erhalten, kannst du tun:

```php
$id = Flight::request()->query['id'];
```

Oder du kannst tun:

```php
$id = Flight::request()->query->id;
```

## Roher Anfragekörper

Um den rohen HTTP-Anfragekörper zu erhalten, zum Beispiel bei PUT-Anfragen, kannst du tun:

```php
$body = Flight::request()->getBody();
```

## JSON-Eingabe

Wenn du eine Anfrage mit dem Typ `application/json` und den Daten `{"id": 123}` sendest, ist sie über die `data`-Eigenschaft verfügbar:

```php
$id = Flight::request()->data->id;
```

## `$_GET`

Du kannst das `$_GET`-Array über die `query`-Eigenschaft zugreifen:

```php
$id = Flight::request()->query['id'];
```

## `$_POST`

Du kannst das `$_POST`-Array über die `data`-Eigenschaft zugreifen:

```php
$id = Flight::request()->data['id'];
```

## `$_COOKIE`

Du kannst das `$_COOKIE`-Array über die `cookies`-Eigenschaft zugreifen:

```php
$myCookieValue = Flight::request()->cookies['myCookieName'];
```

## `$_SERVER`

Es gibt einen Shortcut, um auf das `$_SERVER`-Array über die `getVar()`-Methode zuzugreifen:

```php
$host = Flight::request()->getVar('HTTP_HOST');
```

## Auf Hochgeladene Dateien über `$_FILES` zugreifen

Du kannst auf hochgeladene Dateien über die `files`-Eigenschaft zugreifen:

```php
$uploadedFile = Flight::request()->files['myFile'];
```

## Verarbeitung von Dateiuploads (v3.12.0)

Du kannst Dateiuploads mit dem Framework mit einigen Hilfsmethoden verarbeiten. Es kommt im Wesentlichen darauf an, die Dateidaten aus der Anfrage zu ziehen und sie an einen neuen Ort zu verschieben.

```php
Flight::route('POST /upload', function(){
	// Wenn du ein Eingabefeld wie <input type="file" name="myFile"> hattest
	$uploadedFileData = Flight::request()->getUploadedFiles();
	$uploadedFile = $uploadedFileData['myFile'];
	$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
});
```

Wenn du mehrere Dateien hochgeladen hast, kannst du durch sie iterieren:

```php
Flight::route('POST /upload', function(){
	// Wenn du ein Eingabefeld wie <input type="file" name="myFiles[]"> hattest
	$uploadedFiles = Flight::request()->getUploadedFiles()['myFiles'];
	foreach ($uploadedFiles as $uploadedFile) {
		$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
	}
});
```

> **Sicherheitshinweis:** Überprüfe und bereinige immer die Benutzereingaben, besonders bei Dateiuploads. Überprüfe immer die Dateiendungen, die du erlaubst, aber du solltest auch die „Magic Bytes“ der Datei überprüfen, um sicherzustellen, dass es sich tatsächlich um die Art von Datei handelt, die der Benutzer angibt. Es gibt [articles](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [and](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [libraries](https://github.com/RikudouSage/MimeTypeDetector), die dabei helfen.

## Anfrage-Header

Du kannst Anfrage-Header mit der `getHeader()`- oder `getHeaders()`-Methode zugreifen:

```php
// Vielleicht brauchst du den Authorization-Header
$host = Flight::request()->getHeader('Authorization');
// oder
$host = Flight::request()->header('Authorization');

// Wenn du alle Header abrufen möchtest
$headers = Flight::request()->getHeaders();
// oder
$headers = Flight::request()->headers();
```

## Anfragekörper

Du kannst den rohen Anfragekörper mit der `getBody()`-Methode zugreifen:

```php
$body = Flight::request()->getBody();
```

## Anfragemethode

Du kannst die Anfragemethode mit der `method`-Eigenschaft oder der `getMethod()`-Methode zugreifen:

```php
$method = Flight::request()->method; // ruft tatsächlich getMethod() auf
$method = Flight::request()->getMethod();
```

**Hinweis:** Die `getMethod()`-Methode holt zuerst die Methode aus `$_SERVER['REQUEST_METHOD']`, dann kann sie durch `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']` überschrieben werden, falls vorhanden, oder durch `$_REQUEST['_method']`, falls vorhanden.

## Anfrage-URLs

Es gibt ein paar Hilfsmethoden, um Teile einer URL zusammenzusetzen, zu deiner Bequemlichkeit.

### Vollständige URL

Du kannst die vollständige Anfrage-URL mit der `getFullUrl()`-Methode zugreifen:

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```

### Basis-URL

Du kannst die Basis-URL mit der `getBaseUrl()`-Methode zugreifen:

```php
$url = Flight::request()->getBaseUrl();
// Beachte, kein abschließender Slash.
// https://example.com
```

## Query-Parsing

Du kannst eine URL an die `parseQuery()`-Methode übergeben, um den Query-String in ein assoziatives Array zu parsen:

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```