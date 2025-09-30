# Anfragen

## Überblick

Flight kapselt die HTTP-Anfrage in ein einzelnes Objekt, das auf folgende Weise zugänglich ist:

```php
$request = Flight::request();
```

## Verständnis

HTTP-Anfragen sind einer der Kernaspekte, die man über den HTTP-Lebenszyklus verstehen sollte. Ein Benutzer führt eine Aktion in einem Webbrowser oder einem HTTP-Client aus, und sie senden eine Reihe von Headern, Body, URL usw. an Ihr Projekt. Sie können diese Header (die Sprache des Browsers, welche Art von Kompression sie handhaben können, den User Agent usw.) erfassen und den Body und die URL, die an Ihre Flight-Anwendung gesendet werden, erfassen. Diese Anfragen sind essenziell, damit Ihre App versteht, was als Nächstes zu tun ist.

## Grundlegende Verwendung

PHP hat mehrere Super-Global-Variablen, einschließlich `$_GET`, `$_POST`, `$_REQUEST`, `$_SERVER`, `$_FILES` und `$_COOKIE`. Flight abstrahiert diese in praktische [Collections](/learn/collections). Sie können die Eigenschaften `query`, `data`, `cookies` und `files` als Arrays oder Objekte zugreifen.

> **Hinweis:** Es wird **STRONGLICH** davon abgeraten, diese Super-Global-Variablen in Ihrem Projekt zu verwenden, und sie sollten über das `request()`-Objekt referenziert werden.

> **Hinweis:** Es gibt keine Abstraktion für `$_ENV` verfügbar.

### `$_GET`

Sie können das `$_GET`-Array über die `query`-Eigenschaft zugreifen:

```php
// GET /search?keyword=something
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	// oder
	$keyword = Flight::request()->query->keyword;
	echo "Sie suchen nach: $keyword";
	// fragen Sie eine Datenbank oder etwas Ähnliches mit dem $keyword ab
});
```

### `$_POST`

Sie können das `$_POST`-Array über die `data`-Eigenschaft zugreifen:

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	// oder
	$name = Flight::request()->data->name;
	$email = Flight::request()->data->email;
	echo "Sie haben eingereicht: $name, $email";
	// speichern Sie in einer Datenbank oder etwas Ähnliches mit dem $name und $email
});
```

### `$_COOKIE`

Sie können das `$_COOKIE`-Array über die `cookies`-Eigenschaft zugreifen:

```php
Flight::route('GET /login', function(){
	$savedLogin = Flight::request()->cookies['myLoginCookie'];
	// oder
	$savedLogin = Flight::request()->cookies->myLoginCookie;
	// prüfen Sie, ob es wirklich gespeichert ist oder nicht, und wenn ja, loggen Sie sie automatisch ein
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
// roher Zugriff auf die $_FILES-Eigenschaft. Siehe unten für den empfohlenen Ansatz
$uploadedFile = Flight::request()->files['myFile']; 
// oder
$uploadedFile = Flight::request()->files->myFile;
```

Siehe [Uploaded File Handler](/learn/uploaded-file) für weitere Infos.

#### Verarbeiten von Datei-Uploads

_v3.12.0_

Sie können Datei-Uploads mit dem Framework und einigen Hilfsmethoden verarbeiten. Es kommt im Wesentlichen darauf an, die Dateidaten aus der Anfrage zu ziehen und sie an einen neuen Ort zu verschieben.

```php
Flight::route('POST /upload', function(){
	// Wenn Sie ein Eingabefeld wie <input type="file" name="myFile"> haben
	$uploadedFileData = Flight::request()->getUploadedFiles();
	$uploadedFile = $uploadedFileData['myFile'];
	$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
});
```

Wenn Sie mehrere Dateien hochgeladen haben, können Sie durch sie iterieren:

```php
Flight::route('POST /upload', function(){
	// Wenn Sie ein Eingabefeld wie <input type="file" name="myFiles[]"> haben
	$uploadedFiles = Flight::request()->getUploadedFiles()['myFiles'];
	foreach ($uploadedFiles as $uploadedFile) {
		$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
	}
});
```

> **Sicherheitshinweis:** Validieren und sanitieren Sie immer Benutzereingaben, insbesondere bei Datei-Uploads. Validieren Sie immer den Typ der Erweiterungen, die Sie zum Hochladen erlauben, aber Sie sollten auch die "Magic Bytes" der Datei validieren, um sicherzustellen, dass es tatsächlich der Dateityp ist, den der Benutzer angibt. Es gibt [Artikel](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [und](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [Bibliotheken](https://github.com/RikudouSage/MimeTypeDetector), die dabei helfen.

### Anfragen-Body

Um den rohen HTTP-Anfragen-Body zu erhalten, z. B. bei POST/PUT-Anfragen, können Sie Folgendes tun:

```php
Flight::route('POST /users/xml', function(){
	$xmlBody = Flight::request()->getBody();
	// tun Sie etwas mit dem gesendeten XML.
});
```

### JSON-Body

Wenn Sie eine Anfrage mit dem Content-Type `application/json` und den Beispieldaten `{"id": 123}` erhalten, ist sie über die `data`-Eigenschaft verfügbar:

```php
$id = Flight::request()->data->id;
```

### Anfragen-Header

Sie können Anfragen-Header mit der `getHeader()`- oder `getHeaders()`-Methode zugreifen:

```php

// Vielleicht brauchen Sie den Authorization-Header
$host = Flight::request()->getHeader('Authorization');
// oder
$host = Flight::request()->header('Authorization');

// Wenn Sie alle Header holen müssen
$headers = Flight::request()->getHeaders();
// oder
$headers = Flight::request()->headers();
```

### Anfragen-Methode

Sie können die Anfragen-Methode mit der `method`-Eigenschaft oder der `getMethod()`-Methode zugreifen:

```php
$method = Flight::request()->method; // tatsächlich von getMethod() befüllt
$method = Flight::request()->getMethod();
```

**Hinweis:** Die `getMethod()`-Methode holt zuerst die Methode aus `$_SERVER['REQUEST_METHOD']`, dann kann sie von `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']` überschrieben werden, wenn sie existiert, oder `$_REQUEST['_method']`, wenn sie existiert.

## Eigenschaften des Anfragen-Objekts

Das Anfragen-Objekt stellt die folgenden Eigenschaften zur Verfügung:

- **body** - Der rohe HTTP-Anfragen-Body
- **url** - Die angeforderte URL
- **base** - Das übergeordnete Unterverzeichnis der URL
- **method** - Die Anfragen-Methode (GET, POST, PUT, DELETE)
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
- **proxy_ip** - Proxy-IP-Adresse des Clients. Scant das `$_SERVER`-Array nach `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED` in dieser Reihenfolge.
- **host** - Der Anfragen-Hostname
- **servername** - Der SERVER_NAME aus `$_SERVER`

## Hilfsmethoden für URLs

Es gibt ein paar Hilfsmethoden, um Teile einer URL für Ihre Bequemlichkeit zusammenzusetzen.

### Volle URL

Sie können die volle Anfragen-URL mit der `getFullUrl()`-Methode zugreifen:

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```

### Basis-URL

Sie können die Basis-URL mit der `getBaseUrl()`-Methode zugreifen:

```php
// http://example.com/path/to/something/cool?query=yes+thanks
$url = Flight::request()->getBaseUrl();
// https://example.com
// Beachten Sie, kein abschließender Schrägstrich.
```

## Query-Parsing

Sie können eine URL an die `parseQuery()`-Methode übergeben, um den Query-String in ein assoziatives Array zu parsen:

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```

## Siehe auch
- [Routing](/learn/routing) - Sehen Sie, wie Routen zu Controllern zugeordnet und Views gerendert werden.
- [Responses](/learn/responses) - Wie man HTTP-Antworten anpasst.
- [Warum ein Framework?](/learn/why-frameworks) - Wie Anfragen in das große Ganze passen.
- [Collections](/learn/collections) - Arbeiten mit Sammlungen von Daten.
- [Uploaded File Handler](/learn/uploaded-file) - Handhaben von Datei-Uploads.

## Fehlerbehebung
- `request()->ip` und `request()->proxy_ip` können unterschiedlich sein, wenn Ihr Webserver hinter einem Proxy, Load Balancer usw. ist.

## Changelog
- v3.12.0 - Fähigkeit hinzugefügt, Datei-Uploads über das Anfragen-Objekt zu handhaben.
- v1.0 - Erste Veröffentlichung.