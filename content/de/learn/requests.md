# Anfragen

Flight kapselt die HTTP-Anfrage in ein einzelnes Objekt, das aufgerufen werden kann durch:

```php
$request = Flight::request();
```

## Typische Anwendungsfälle

Wenn Sie mit einer Anfrage in einer Webanwendung arbeiten, möchten Sie in der Regel einen Header, oder ein `$_GET` oder `$_POST` Parameter extrahieren, oder vielleicht sogar den rohen Anfrageinhalt. Flight bietet eine einfache Schnittstelle, um all diese Dinge zu tun.

Hier ist ein Beispiel, wie man einen Abfragezeichenfolgenparameter erhält:

```php
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	echo "Sie suchen nach: $keyword";
	// Abfrage einer Datenbank oder etwas anderem mit dem $keyword
});
```

Hier ist ein Beispiel für ein Formular mit einer POST-Methode:

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	echo "Sie haben eingereicht: $name, $email";
	// in eine Datenbank oder etwas anderes mit $name und $email speichern
});
```

## Eigenschaften des Anfrageobjekts

Das Anfrageobjekt bietet die folgenden Eigenschaften:

- **body** - Der rohe HTTP-Anfrageinhalt
- **url** - Die angeforderte URL
- **base** - Das übergeordnete Unterverzeichnis der URL
- **method** - Die Anfrage-Methode (GET, POST, PUT, DELETE)
- **referrer** - Die verweisende URL
- **ip** - IP-Adresse des Clients
- **ajax** - Ob die Anfrage eine AJAX-Anfrage ist
- **scheme** - Das Serverprotokoll (http, https)
- **user_agent** - Browserinformationen
- **type** - Der Inhaltstyp
- **length** - Die Inhaltslänge
- **query** - Abfragezeichenfolgeparameter
- **data** - Postdaten oder JSON-Daten
- **cookies** - Cookie-Daten
- **files** - Hochgeladene Dateien
- **secure** - Ob die Verbindung sicher ist
- **accept** - HTTP-Annahmeparameter
- **proxy_ip** - Proxy-IP-Adresse des Clients. Durchsucht das `$_SERVER`-Array nach `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED` in dieser Reihenfolge.
- **host** - Der Hostname der Anfrage

Sie können auf die Eigenschaften `query`, `data`, `cookies` und `files` als Arrays oder Objekte zugreifen.

Um einen Abfragezeichenfolgenparameter zu erhalten, können Sie tun:

```php
$id = Flight::request()->query['id'];
```

Oder Sie können tun:

```php
$id = Flight::request()->query->id;
```

## RAW Anfrageinhalt

Um den rohen HTTP-Anfrageinhalt zu erhalten, beispielsweise bei PUT-Anfragen, können Sie tun:

```php
$body = Flight::request()->getBody();
```

## JSON-Eingabe

Wenn Sie eine Anfrage mit dem Typ `application/json` und den Daten `{"id": 123}` senden, ist es über die `data`-Eigenschaft verfügbar:

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
$myCookieValue = Flight::request()->cookies['myCookieName'];
```

## `$_SERVER`

Es gibt eine Abkürzung, um auf das `$_SERVER`-Array über die `getVar()`-Methode zuzugreifen:

```php

$host = Flight::request()->getVar['HTTP_HOST'];
```

## Hochgeladene Dateien über `$_FILES` zugreifen

Sie können auf hochgeladene Dateien über die `files`-Eigenschaft zugreifen:

```php
$uploadedFile = Flight::request()->files['myFile'];
```

## Verarbeitung von Datei-Uploads

Sie können Datei-Uploads mithilfe des Frameworks mit einigen Hilfsmethoden verarbeiten. Es reduziert sich im Grunde darauf, die Dateidaten aus der Anfrage zu extrahieren und sie an einen neuen Speicherort zu verschieben.

```php
Flight::route('POST /upload', function(){
	// Wenn Sie ein Eingabefeld wie <input type="file" name="myFile"> hatten
	$uploadedFileData = Flight::request()->getUploadedFiles();
	$uploadedFile = $uploadedFileData['myFile'];
	$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
});
```

Wenn Sie mehrere Dateien hochgeladen haben, können Sie sie durchlaufen:

```php
Flight::route('POST /upload', function(){
	// Wenn Sie ein Eingabefeld wie <input type="file" name="myFiles[]"> hatten
	$uploadedFiles = Flight::request()->getUploadedFiles()['myFiles'];
	foreach ($uploadedFiles as $uploadedFile) {
		$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
	}
});
```

> **Sicherheitsnotiz:** Validieren und sanitieren Sie immer die Benutzereingaben, insbesondere bei Datei-Uploads. Überprüfen Sie immer die Art der Dateierweiterungen, die Sie zulassen möchten, aber Sie sollten auch die "magischen Bytes" der Datei validieren, um sicherzustellen, dass es sich tatsächlich um den Dateityp handelt, den der Benutzer angibt. Es gibt [Artikel](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [und](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [Bibliotheken](https://github.com/RikudouSage/MimeTypeDetector), die Ihnen dabei helfen können.

## Anfrage-Header

Sie können auf Anfrage-Header mithilfe der `getHeader()`- oder `getHeaders()`-Methode zugreifen:

```php

// Vielleicht benötigen Sie den Authorization-Header
$host = Flight::request()->getHeader('Authorization');
// oder
$host = Flight::request()->header('Authorization');

// Wenn Sie alle Header abrufen müssen
$headers = Flight::request()->getHeaders();
// oder
$headers = Flight::request()->headers();
```

## Anfrageinhalt

Sie können auf den rohen Anfrageinhalt über die `getBody()`-Methode zugreifen:

```php
$body = Flight::request()->getBody();
```

## Anfrage-Methode

Sie können auf die Anfrage-Methode über die `method`-Eigenschaft oder die `getMethod()`-Methode zugreifen:

```php
$method = Flight::request()->method; // ruft tatsächlich getMethod() auf
$method = Flight::request()->getMethod();
```

**Hinweis:** Die `getMethod()`-Methode ruft zuerst die Methode aus `$_SERVER['REQUEST_METHOD']` ab, dann kann sie durch `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']` überschrieben werden, wenn sie vorhanden ist, oder durch `$_REQUEST['_method']`, wenn sie vorhanden ist.

## Anfrage-URLs

Es gibt eine Reihe von Hilfsmethoden, um Teile einer URL zu Ihrem Vorteil zusammenzufügen.

### Vollständige URL

Sie können die vollständige Anforderungs-URL über die `getFullUrl()`-Methode abrufen:

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```
### Basis-URL

Sie können die Basis-URL über die `getBaseUrl()`-Methode abrufen:

```php
$url = Flight::request()->getBaseUrl();
// Hinweis, kein abschließender Schrägstrich.
// https://example.com
```

## Abfrageanalyse

Sie können eine URL an die `parseQuery()`-Methode übergeben, um die Abfragezeichenfolge in ein assoziatives Array zu analysieren:

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```