# Uploaded File Handler

## Übersicht

Die `UploadedFile`-Klasse in Flight erleichtert es, Datei-Uploads in Ihrer Anwendung sicher und einfach zu handhaben. Sie umschließt die Details des PHP-Datei-Upload-Prozesses und bietet Ihnen eine einfache, objektorientierte Möglichkeit, auf Dateiinformationen zuzugreifen und hochgeladene Dateien zu verschieben.

## Verständnis

Wenn ein Benutzer eine Datei über ein Formular hochlädt, speichert PHP Informationen über die Datei in der `$_FILES`-Superglobal. In Flight interagieren Sie selten direkt mit `$_FILES`. Stattdessen stellt das `Request`-Objekt von Flight (erreichbar über `Flight::request()`) eine Methode `getUploadedFiles()` bereit, die ein Array von `UploadedFile`-Objekten zurückgibt, was den Datei-Handling viel bequemer und robuster macht.

Die `UploadedFile`-Klasse bietet Methoden zum:
- Abrufen des ursprünglichen Dateinamens, MIME-Typs, der Größe und des temporären Speicherorts
- Überprüfen auf Upload-Fehler
- Verschieben der hochgeladenen Datei an einen permanenten Speicherort

Diese Klasse hilft Ihnen, gängige Fallstricke bei Datei-Uploads zu vermeiden, wie z. B. das Handhaben von Fehlern oder das sichere Verschieben von Dateien.

## Grundlegende Verwendung

### Zugriff auf hochgeladene Dateien aus einer Anfrage

Der empfohlene Weg, um auf hochgeladene Dateien zuzugreifen, ist über das Request-Objekt:

```php
Flight::route('POST /upload', function() {
    // Für ein Formularfeld namens <input type="file" name="myFile">
    $uploadedFiles = Flight::request()->getUploadedFiles();
    $file = $uploadedFiles['myFile'];

    // Nun können Sie die UploadedFile-Methoden verwenden
    if ($file->getError() === UPLOAD_ERR_OK) {
        $file->moveTo('/path/to/uploads/' . $file->getClientFilename());
        echo "Datei erfolgreich hochgeladen!";
    } else {
        echo "Upload fehlgeschlagen: " . $file->getError();
    }
});
```

### Handhabung mehrerer Datei-Uploads

Wenn Ihr Formular `name="myFiles[]"` für mehrere Uploads verwendet, erhalten Sie ein Array von `UploadedFile`-Objekten:

```php
Flight::route('POST /upload', function() {
    // Für ein Formularfeld namens <input type="file" name="myFiles[]">
    $uploadedFiles = Flight::request()->getUploadedFiles();
    foreach ($uploadedFiles['myFiles'] as $file) {
        if ($file->getError() === UPLOAD_ERR_OK) {
            $file->moveTo('/path/to/uploads/' . $file->getClientFilename());
            echo "Hochgeladen: " . $file->getClientFilename() . "<br>";
        } else {
            echo "Upload fehlgeschlagen: " . $file->getClientFilename() . "<br>";
        }
    }
});
```

### Manuelles Erstellen einer UploadedFile-Instanz

Normalerweise erstellen Sie keine `UploadedFile` manuell, aber Sie können es tun, wenn es benötigt wird:

```php
use flight\net\UploadedFile;

$file = new UploadedFile(
  $_FILES['myfile']['name'],
  $_FILES['myfile']['type'],
  $_FILES['myfile']['size'],
  $_FILES['myfile']['tmp_name'],
  $_FILES['myfile']['error']
);
```

### Zugriff auf Dateiinformationen

Sie können leicht Details über die hochgeladene Datei abrufen:

```php
echo $file->getClientFilename();   // Ursprünglicher Dateiname vom Computer des Benutzers
echo $file->getClientMediaType();  // MIME-Typ (z. B. image/png)
echo $file->getSize();             // Dateigröße in Bytes
echo $file->getTempName();         // Temporärer Dateipfad auf dem Server
echo $file->getError();            // Upload-Fehlercode (0 bedeutet kein Fehler)
```

### Verschieben der hochgeladenen Datei

Nach der Validierung der Datei verschieben Sie sie an einen permanenten Speicherort:

```php
try {
  $file->moveTo('/path/to/uploads/' . $file->getClientFilename());
  echo "Datei erfolgreich hochgeladen!";
} catch (Exception $e) {
  echo "Upload fehlgeschlagen: " . $e->getMessage();
}
```

Die `moveTo()`-Methode wirft eine Exception, wenn etwas schiefgeht (wie ein Upload-Fehler oder ein Berechtigungsproblem).

### Handhabung von Upload-Fehlern

Wenn es während des Uploads ein Problem gab, können Sie eine lesbare Fehlermeldung abrufen:

```php
if ($file->getError() !== UPLOAD_ERR_OK) {
  // Sie können den Fehlercode verwenden oder die Exception von moveTo() abfangen
  echo "Es gab einen Fehler beim Hochladen der Datei.";
}
```

## Siehe auch

- [Requests](/learn/requests) - Erfahren Sie, wie Sie auf hochgeladene Dateien aus HTTP-Anfragen zugreifen und sehen Sie weitere Beispiele für Datei-Uploads.
- [Configuration](/learn/configuration) - Wie Sie Upload-Limits und Verzeichnisse in PHP konfigurieren.
- [Extending](/learn/extending) - Wie Sie die Kernklassen von Flight anpassen oder erweitern.

## Fehlerbehebung

- Überprüfen Sie immer `$file->getError()`, bevor Sie die Datei verschieben.
- Stellen Sie sicher, dass Ihr Upload-Verzeichnis vom Webserver beschreibbar ist.
- Wenn `moveTo()` fehlschlägt, überprüfen Sie die Exception-Nachricht auf Details.
- Die PHP-Einstellungen `upload_max_filesize` und `post_max_size` können Datei-Uploads einschränken.
- Für mehrere Datei-Uploads iterieren Sie immer durch das Array der `UploadedFile`-Objekte.

## Changelog

- v3.12.0 - `UploadedFile`-Klasse zum Request-Objekt hinzugefügt für einfacheres Datei-Handling.