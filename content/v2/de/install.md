# Installation

### 1. Laden Sie die Dateien herunter.

Wenn Sie [Composer](https://getcomposer.org) verwenden, können Sie den folgenden
Befehl ausführen:

```bash
composer require flightphp/core
```

ODER Sie können sie [herunterladen](https://github.com/flightphp/core/archive/master.zip)
und direkt in Ihr Webverzeichnis entpacken.

### 2. Konfigurieren Sie Ihren Webserver.

Für *Apache* bearbeiten Sie Ihre `.htaccess`-Datei mit Folgendem:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **Hinweis**: Wenn Sie Flight in einem Unterverzeichnis verwenden müssen, fügen Sie die Zeile
> `RewriteBase /subdir/` direkt nach `RewriteEngine On` hinzu.
> **Hinweis**: Wenn Sie alle Serverdateien schützen möchten, wie z.B. eine db oder env-Datei.
> Fügen Sie dies in Ihre `.htaccess`-Datei ein:

```apache
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

Für *Nginx* fügen Sie Folgendes zu Ihrer Serverdeklaration hinzu:

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```

### 3. Erstellen Sie Ihre `index.php`-Datei.

Zuerst binden Sie das Framework ein.

```php
require 'flight/Flight.php';
```

Wenn Sie Composer verwenden, führen Sie stattdessen den Autoloader aus.

```php
require 'vendor/autoload.php';
```

Definieren Sie dann eine Route und weisen Sie eine Funktion zu, um die Anfrage zu bearbeiten.

```php
Flight::route('/', function () {
  echo 'Hallo Welt!';
});
```

Schließlich starten Sie das Framework.

```php
Flight::start();
```