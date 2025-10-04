# Requests

## Overview

Flight kapselt die HTTP-Anfrage in ein einzelnes Objekt, das wie folgt zugänglich ist:

```php
$request = Flight::request();
```

## Understanding

HTTP-Anfragen sind einer der Kernaspekte, die es zu verstehen gilt, um den HTTP-Lebenszyklus zu verstehen. Ein Benutzer führt eine Aktion in einem Webbrowser oder einem HTTP-Client aus, und sie senden eine Reihe von Headern, Body, URL usw. an Ihr Projekt. Sie können diese Header (die Sprache des Browsers, welche Art von Komprimierung sie handhaben können, den User Agent usw.) erfassen und den Body und die URL, die an Ihre Flight-Anwendung gesendet werden, erfassen. Diese Anfragen sind essenziell, damit Ihre App versteht, was als Nächstes zu tun ist.

## Basic Usage

PHP hat mehrere Super-Globalen, einschließlich `$_GET`, `$_POST`, `$_REQUEST`, `$_SERVER`, `$_FILES` und `$_COOKIE`. Flight abstrahiert diese in praktische [Collections](/learn/collections). Sie können die Eigenschaften `query`, `data`, `cookies` und `files` als Arrays oder Objekte zugreifen.

> **Hinweis:** Es wird **STRONGLICH** davon abgeraten, diese Super-Globalen in Ihrem Projekt zu verwenden, und sie sollten über das `request()`-Objekt referenziert werden.

> **Hinweis:** Es gibt keine Abstraktion für `$_ENV`.

### `$_GET`

Sie können das `$_GET`-Array über die `query`-Eigenschaft zugreifen:

```php
// GET /search?keyword=something
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	// or
	$keyword = Flight::request()->query->keyword;
	echo "You are searching for: $keyword";
	// query a database or something else with the $keyword
});
```

### `$_POST`

Sie können das `$_POST`-Array über die `data`-Eigenschaft zugreifen:

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	// or
	$name = Flight::request()->data->name;
	$email = Flight::request()->data->email;
	echo "You submitted: $name, $email";
	// save to a database or something else with the $name and $email
});
```

### `$_COOKIE`

Sie können das `$_COOKIE`-Array über die `cookies`-Eigenschaft zugreifen:

```php
Flight::route('GET /login', function(){
	$savedLogin = Flight::request()->cookies['myLoginCookie'];
	// or
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

Es gibt einen Shortcut, um das `$_SERVER`-Array über die `getVar()`-Methode zuzugreifen:

```php

$host = Flight::request()->getVar('HTTP_HOST');
```

### `$_FILES`

Sie können hochgeladene Dateien über die `files`-Eigenschaft zugreifen:

```php
// raw access to $_FILES property. See below for recommended approach
$uploadedFile = Flight::request()->files['myFile']; 
// or
$uploadedFile = Flight::request()->files->myFile;
```

Siehe [Uploaded File Handler](/learn/uploaded-file) für mehr Infos.

#### Processing File Uploads

_v3.12.0_

Sie können Datei-Uploads mit dem Framework und einigen Hilfsmethoden verarbeiten. Es läuft im Wesentlichen darauf hinaus, die Dateidaten aus der Anfrage zu ziehen und sie an einen neuen Speicherort zu verschieben.

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

> **Sicherheitshinweis:** Validieren und sanitieren Sie immer Benutzereingaben, insbesondere bei Datei-Uploads. Validieren Sie immer den Typ der Erweiterungen, die Sie hochladen lassen, aber Sie sollten auch die "Magic Bytes" der Datei validieren, um sicherzustellen, dass es tatsächlich der Dateityp ist, den der Benutzer angibt. Es gibt [Artikel](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [und](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [Bibliotheken](https://github.com/RikudouSage/MimeTypeDetector), die dabei helfen.

### Request Body

Um den rohen HTTP-Anfragetext zu erhalten, z. B. bei POST/PUT-Anfragen, können Sie Folgendes tun:

```php
Flight::route('POST /users/xml', function(){
	$xmlBody = Flight::request()->getBody();
	// do something with the XML that was sent.
});
```

### JSON Body

Wenn Sie eine Anfrage mit dem Inhaltstyp `application/json` und den Beispieldaten `{"id": 123}` erhalten, ist sie über die `data`-Eigenschaft verfügbar:

```php
$id = Flight::request()->data->id;
```

### Request Headers

Sie können Anfrage-Header mit der `getHeader()`- oder `getHeaders()`-Methode zugreifen:

```php

// Maybe you need Authorization header
$host = Flight::request()->getHeader('Authorization');
// or
$host = Flight::request()->header('Authorization');

// If you need to grab all headers
$headers = Flight::request()->getHeaders();
// or
$headers = Flight::request()->headers();
```

### Request Method

Sie können die Anfragemethode mit der `method`-Eigenschaft oder der `getMethod()`-Methode zugreifen:

```php
$method = Flight::request()->method; // actually populated by getMethod()
$method = Flight::request()->getMethod();
```

**Hinweis:** Die `getMethod()`-Methode zieht zunächst die Methode aus `$_SERVER['REQUEST_METHOD']`, dann kann sie von `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']` überschrieben werden, falls vorhanden, oder `$_REQUEST['_method']`, falls vorhanden.

## Request Object Properties

Das Anfrage-Objekt stellt die folgenden Eigenschaften bereit:

- **body** - Der rohe HTTP-Anfragetext
- **url** - Die angeforderte URL
- **base** - Das übergeordnete Unterverzeichnis der URL
- **method** - Die Anfragemethode (GET, POST, PUT, DELETE)
- **referrer** - Die Referrer-URL
- **ip** - IP-Adresse des Clients
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
- **proxy_ip** - Proxy-IP-Adresse des Clients. Scannt das `$_SERVER`-Array nach `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED` in dieser Reihenfolge.
- **host** - Der Anfragen-Hostname
- **servername** - Der SERVER_NAME aus `$_SERVER`

## Helper Methods

Es gibt ein paar Hilfsmethoden, um Teile einer URL zusammenzusetzen oder mit bestimmten Headern umzugehen.

### Full URL

Sie können die vollständige Anfrage-URL mit der `getFullUrl()`-Methode zugreifen:

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```
### Base URL

Sie können die Basis-URL mit der `getBaseUrl()`-Methode zugreifen:

```php
// http://example.com/path/to/something/cool?query=yes+thanks
$url = Flight::request()->getBaseUrl();
// https://example.com
// Notice, no trailing slash.
```

## Query Parsing

Sie können eine URL an die `parseQuery()`-Methode übergeben, um den Query-String in ein assoziatives Array zu parsen:

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```

## Negotiate Content Accept Types

_v3.17.2_

Sie können die `negotiateContentType()`-Methode verwenden, um den besten Inhaltstyp zu bestimmen, mit dem geantwortet werden soll, basierend auf dem `Accept`-Header, der vom Client gesendet wird.

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

## See Also
- [Routing](/learn/routing) - See how to map routes to controllers and render views.
- [Responses](/learn/responses) - How to customize HTTP responses.
- [Why a Framework?](/learn/why-frameworks) - How requests fit into the big picture.
- [Collections](/learn/collections) - Working with collections of data.
- [Uploaded File Handler](/learn/uploaded-file) - Handling file uploads.

## Troubleshooting
- `request()->ip` and `request()->proxy_ip` can be different if your webserver is behind a proxy, load balancer, etc. 

## Changelog
- v3.17.2 - Added negotiateContentType()
- v3.12.0 - Added ability to handle file uploads through the request object.
- v1.0 - Initial release.