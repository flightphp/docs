# Requests

## Übersicht

Flight kapselt die HTTP-Anfrage in ein einzelnes Objekt, das wie folgt zugänglich ist:

```php
$request = Flight::request();
```

## Verständnis

HTTP-Anfragen sind eines der Kernaspekte, die man über den HTTP-Lebenszyklus verstehen muss. Ein Benutzer führt eine Aktion in einem Webbrowser oder einem HTTP-Client aus, und sie senden eine Reihe von Headern, Body, URL usw. an Ihr Projekt. Sie können diese Header (die Sprache des Browsers, welche Art von Kompression sie handhaben können, den User-Agent usw.) erfassen und den Body sowie die URL, die an Ihre Flight-Anwendung gesendet wird, erfassen. Diese Anfragen sind essenziell, damit Ihre App versteht, was als Nächstes zu tun ist.

## Grundlegende Verwendung

PHP hat mehrere Super-Globalen, einschließlich `$_GET`, `$_POST`, `$_REQUEST`, `$_SERVER`, `$_FILES` und `$_COOKIE`. Flight abstrahiert diese in handliche [Collections](/learn/collections). Sie können die Eigenschaften `query`, `data`, `cookies` und `files` als Arrays oder Objekte zugreifen.

> **Hinweis:** Es wird **STRONGLICH** davon abgeraten, diese Super-Globalen in Ihrem Projekt zu verwenden, und sie sollten über das `request()`-Objekt referenziert werden.

> **Hinweis:** Es gibt keine Abstraktion für `$_ENV` verfügbar.

### `$_GET`

Sie können das `$_GET`-Array über die Eigenschaft `query` zugreifen:

```php
// GET /search?keyword=something
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	// oder
	$keyword = Flight::request()->query->keyword;
	echo "You are searching for: $keyword";
	// query a database or something else with the $keyword
});
```

### `$_POST`

Sie können das `$_POST`-Array über die Eigenschaft `data` zugreifen:

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	// oder
	$name = Flight::request()->data->name;
	$email = Flight::request()->data->email;
	echo "You submitted: $name, $email";
	// save to a database or something else with the $name and $email
});
```

### `$_COOKIE`

Sie können das `$_COOKIE`-Array über die Eigenschaft `cookies` zugreifen:

```php
Flight::route('GET /login', function(){
	$savedLogin = Flight::request()->cookies['myLoginCookie'];
	// oder
	$savedLogin = Flight::request()->cookies->myLoginCookie;
	// check if it's really saved or not and if it is auto log them in
	if($savedLogin) {
		Flight::redirect('/dashboard');
		return;
	}
});
```

Für Hilfe beim Setzen neuer Cookie-Werte siehe [overclokk/cookie](/awesome-plugins/php-cookie)

### `$_SERVER`

Es gibt einen Shortcut, um das `$_SERVER`-Array über die Methode `getVar()` zugreifen:

```php

$host = Flight::request()->getVar('HTTP_HOST');
```

### `$_FILES`

Sie können hochgeladene Dateien über die Eigenschaft `files` zugreifen:

```php
// raw access to $_FILES property. See below for recommended approach
$uploadedFile = Flight::request()->files['myFile']; 
// oder
$uploadedFile = Flight::request()->files->myFile;
```

Siehe [Uploaded File Handler](/learn/uploaded-file) für mehr Infos.

#### Verarbeiten von Datei-Uploads

_v3.12.0_

Sie können Datei-Uploads mit dem Framework mithilfe einiger Hilfsmethoden verarbeiten. Es kommt im Wesentlichen darauf an, die Dateidaten aus der Anfrage zu ziehen und sie an einen neuen Ort zu verschieben.

```php
Flight::route('POST /upload', function(){
	// If you had an input field like <input type="file" name="myFile">
	$uploadedFileData = Flight::request()->getUploadedFiles();
	$uploadedFile = $uploadedFileData['myFile'];
	$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
});
```

Wenn Sie mehrere Dateien hochgeladen haben, können Sie durch sie iterieren:

```php
Flight::route('POST /upload', function(){
	// If you had an input field like <input type="file" name="myFiles[]">
	$uploadedFiles = Flight::request()->getUploadedFiles()['myFiles'];
	foreach ($uploadedFiles as $uploadedFile) {
		$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
	}
});
```

> **Sicherheitshinweis:** Validieren und sanitieren Sie immer Benutzereingaben, insbesondere bei Datei-Uploads. Validieren Sie immer den Typ der Erweiterungen, die Sie zum Hochladen erlauben, aber Sie sollten auch die "Magic Bytes" der Datei validieren, um sicherzustellen, dass es tatsächlich der Typ der Datei ist, den der Benutzer angibt. Es gibt [Artikel](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [und](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [Bibliotheken](https://github.com/RikudouSage/MimeTypeDetector), die dabei helfen.

### Request Body

Um den rohen HTTP-Request-Body zu erhalten, z. B. bei POST/PUT-Anfragen, können Sie Folgendes tun:

```php
Flight::route('POST /users/xml', function(){
	$xmlBody = Flight::request()->getBody();
	// do something with the XML that was sent.
});
```

### JSON Body

Wenn Sie eine Anfrage mit dem Content-Type `application/json` und den Beispieldaten `{"id": 123}` erhalten, ist sie über die Eigenschaft `data` verfügbar:

```php
$id = Flight::request()->data->id;
```

### Request Headers

Sie können Request-Header mit der Methode `getHeader()` oder `getHeaders()` zugreifen:

```php

// Maybe you need Authorization header
$host = Flight::request()->getHeader('Authorization');
// oder
$host = Flight::request()->header('Authorization');

// If you need to grab all headers
$headers = Flight::request()->getHeaders();
// oder
$headers = Flight::request()->headers();
```

### Request Method

Sie können die Request-Methode über die Eigenschaft `method` oder die Methode `getMethod()` zugreifen:

```php
$method = Flight::request()->method; // actually populated by getMethod()
$method = Flight::request()->getMethod();
```

**Hinweis:** Die Methode `getMethod()` zieht zunächst die Methode aus `$_SERVER['REQUEST_METHOD']`, dann kann sie durch `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']` überschrieben werden, falls vorhanden, oder `$_REQUEST['_method']`, falls vorhanden.

## Eigenschaften des Request-Objekts

Das Request-Objekt stellt die folgenden Eigenschaften bereit:

- **body** - Der rohe HTTP-Request-Body
- **url** - Die angeforderte URL
- **base** - Das übergeordnete Unterverzeichnis der URL
- **method** - Die Request-Methode (GET, POST, PUT, DELETE)
- **referrer** - Die Referrer-URL
- **ip** - IP-Adresse des Clients
- **ajax** - Ob es sich um eine AJAX-Anfrage handelt
- **scheme** - Das Server-Protokoll (http, https)
- **user_agent** - Browser-Informationen
- **type** - Der Content-Type
- **length** - Die Content-Länge
- **query** - Query-String-Parameter
- **data** - Post-Daten oder JSON-Daten
- **cookies** - Cookie-Daten
- **files** - Hochgeladene Dateien
- **secure** - Ob die Verbindung sicher ist
- **accept** - HTTP-Accept-Parameter
- **proxy_ip** - Proxy-IP-Adresse des Clients. Scannt das `$_SERVER`-Array nach `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED` in dieser Reihenfolge.
- **host** - Der Request-Hostname
- **servername** - Der SERVER_NAME aus `$_SERVER`

## Hilfsmethoden

Es gibt ein paar Hilfsmethoden, um Teile einer URL zusammenzusetzen oder mit bestimmten Headern umzugehen.

### Volle URL

Sie können die volle Request-URL mit der Methode `getFullUrl()` zugreifen:

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```
### Basis-URL

Sie können die Basis-URL mit der Methode `getBaseUrl()` zugreifen:

```php
// http://example.com/path/to/something/cool?query=yes+thanks
$url = Flight::request()->getBaseUrl();
// https://example.com
// Notice, no trailing slash.
```

## Query-Parsing

Sie können eine URL an die Methode `parseQuery()` übergeben, um den Query-String in ein assoziatives Array zu parsen:

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```

## Verhandeln von Content-Accept-Types

_v3.17.2_

Sie können die Methode `negotiateContentType()` verwenden, um den besten Content-Type für die Antwort basierend auf dem vom Client gesendeten `Accept`-Header zu bestimmen.

```php

// Example Accept header: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8
// The below defines what you support.
$availableTypes = ['application/json', 'application/xml'];
$typeToServe = Flight::request()->negotiateContentType($availableTypes);
if ($typeToServe === 'application/json') {
	// Serve JSON response
} elseif ($typeToServe === 'application/xml') {
	// Serve XML response
} else {
	// Default to something else or throw an error
}
```

> **Hinweis:** Wenn keiner der verfügbaren Typen im `Accept`-Header gefunden wird, gibt die Methode `null` zurück. Wenn kein `Accept`-Header definiert ist, gibt die Methode den ersten Typ im `$availableTypes`-Array zurück.

## Siehe auch
- [Routing](/learn/routing) - So ordnen Sie Routen Controllern zu und rendern Views.
- [Responses](/learn/responses) - So passen Sie HTTP-Antworten an.
- [Warum ein Framework?](/learn/why-frameworks) - Wie Anfragen in das große Ganze passen.
- [Collections](/learn/collections) - Arbeiten mit Datensammlungen.
- [Uploaded File Handler](/learn/uploaded-file) - Behandeln von Datei-Uploads.

## Fehlerbehebung
- `request()->ip` und `request()->proxy_ip` können unterschiedlich sein, wenn Ihr Webserver hinter einem Proxy, Load Balancer usw. steht.

## Changelog
- v3.17.2 - Added negotiateContentType()
- v3.12.0 - Added ability to handle file uploads through the request object.
- v1.0 - Initial release.