# Installationsanweisungen

Es gibt einige grundlegende Voraussetzungen, bevor Sie Flight installieren können. Insbesondere benötigen Sie:

1. [Installieren Sie PHP auf Ihrem System](#installing-php)
2. [Installieren Sie Composer](https://getcomposer.org) für das beste Entwicklererlebnis.

## Basisinstallation

Wenn Sie [Composer](https://getcomposer.org) verwenden, können Sie den folgenden Befehl ausführen:

```bash
composer require flightphp/core
```

Dies platziert nur die Flight-Kern-Dateien auf Ihrem System. Sie müssen die Projektstruktur definieren, [Layout](/learn/templates), [Abhängigkeiten](/learn/dependency-injection-container), [Konfigurationen](/learn/configuration), [Autoloading](/learn/autoloading) usw. Diese Methode stellt sicher, dass keine anderen Abhängigkeiten außer Flight installiert werden.

Sie können die Dateien auch [direkt herunterladen](https://github.com/flightphp/core/archive/master.zip) und sie in Ihr Web-Verzeichnis extrahieren.

## Empfohlene Installation

Es wird dringend empfohlen, mit der [flightphp/skeleton](https://github.com/flightphp/skeleton)-App für alle neuen Projekte zu beginnen. Die Installation ist kinderleicht.

```bash
composer create-project flightphp/skeleton my-project/
```

Dies richtet Ihre Projektstruktur ein, konfiguriert Autoloading mit Namespaces, richtet eine Konfiguration ein und stellt andere Tools wie [Tracy](/awesome-plugins/tracy), [Tracy Extensions](/awesome-plugins/tracy-extensions) und [Runway](/awesome-plugins/runway) bereit.

## Konfigurieren Sie Ihren Webserver

### Eingebauter PHP-Entwicklungsserver

Dies ist bei weitem der einfachste Weg, um loszulegen. Sie können den eingebauten Server verwenden, um Ihre Anwendung auszuführen, und sogar SQLite für eine Datenbank verwenden (solange sqlite3 auf Ihrem System installiert ist) und fast nichts anderes benötigen! Führen Sie einfach den folgenden Befehl aus, sobald PHP installiert ist:

```bash
php -S localhost:8000
# oder mit der Skeleton-App
composer start
```

Öffnen Sie dann Ihren Browser und gehen Sie zu `http://localhost:8000`.

Wenn Sie das Dokumentenroot Ihres Projekts zu einem anderen Verzeichnis machen möchten (z. B. Ihr Projekt ist `~/myproject`, aber Ihr Dokumentenroot ist `~/myproject/public/`), können Sie den folgenden Befehl ausführen, sobald Sie sich im `~/myproject`-Verzeichnis befinden:

```bash
php -S localhost:8000 -t public/
# mit der Skeleton-App ist dies bereits konfiguriert
composer start
```

Öffnen Sie dann Ihren Browser und gehen Sie zu `http://localhost:8000`.

### Apache

Stellen Sie sicher, dass Apache bereits auf Ihrem System installiert ist. Falls nicht, googeln Sie, wie Sie Apache auf Ihrem System installieren.

Für Apache bearbeiten Sie Ihre `.htaccess`-Datei mit dem Folgenden:

```apacheconf
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **Hinweis**: Wenn Sie Flight in einem Unterverzeichnis verwenden müssen, fügen Sie die Zeile hinzu
> `RewriteBase /subdir/` direkt nach `RewriteEngine On`.

> **Hinweis**: Wenn Sie alle Serverdateien schützen möchten, wie eine db- oder env-Datei.
> Fügen Sie dies in Ihre `.htaccess`-Datei ein:

```apacheconf
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

### Nginx

Stellen Sie sicher, dass Nginx bereits auf Ihrem System installiert ist. Falls nicht, googeln Sie, wie Sie Nginx auf Ihrem System installieren.

Für Nginx fügen Sie das Folgende zu Ihrer Server-Deklaration hinzu:

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```

## Erstellen Sie Ihre `index.php`-Datei

Wenn Sie eine Basisinstallation durchführen, benötigen Sie etwas Code, um zu starten.

```php
<?php

// Wenn Sie Composer verwenden, laden Sie den Autoloader.
// if you're using Composer, require the autoloader.
require 'vendor/autoload.php';
// wenn Sie Composer nicht verwenden, laden Sie das Framework direkt
// if you're not using Composer, load the framework directly
// require 'flight/Flight.php';

// Definieren Sie dann eine Route und weisen Sie eine Funktion zu, um die Anfrage zu handhaben.
Flight::route('/', function () {
  echo 'hello world!';
});

// Starten Sie schließlich das Framework.
Flight::start();
```

Mit der Skeleton-App ist dies bereits konfiguriert und in Ihrer `app/config/routes.php`-Datei gehandhabt. Dienste werden in `app/config/services.php` konfiguriert.

## PHP installieren

Wenn Sie bereits `php` auf Ihrem System installiert haben, überspringen Sie diese Anweisungen und gehen Sie zum [Download-Bereich](#download-the-files).

### **macOS**

#### **PHP mit Homebrew installieren**

1. **Installieren Sie Homebrew** (falls noch nicht installiert):
   - Öffnen Sie das Terminal und führen Sie aus:
     ```bash
     /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
     ```

2. **Installieren Sie PHP**:
   - Installieren Sie die neueste Version:
     ```bash
     brew install php
     ```
   - Um eine spezifische Version zu installieren, z. B. PHP 8.1:
     ```bash
     brew tap shivammathur/php
     brew install shivammathur/php/php@8.1
     ```

3. **Wechseln Sie zwischen PHP-Versionen**:
   - Verknüpfen Sie die aktuelle Version und verknüpfen Sie die gewünschte Version:
     ```bash
     brew unlink php
     brew link --overwrite --force php@8.1
     ```
   - Überprüfen Sie die installierte Version:
     ```bash
     php -v
     ```

### **Windows 10/11**

#### **PHP manuell installieren**

1. **PHP herunterladen**:
   - Besuchen Sie [PHP for Windows](https://windows.php.net/download/) und laden Sie die neueste oder eine spezifische Version (z. B. 7.4, 8.0) als nicht-thread-sichere ZIP-Datei herunter.

2. **PHP extrahieren**:
   - Extrahieren Sie die heruntergeladene ZIP-Datei nach `C:\php`.

3. **PHP zum System-PATH hinzufügen**:
   - Gehen Sie zu **Systemeigenschaften** > **Umgebungsvariablen**.
   - Unter **Systemvariablen** finden Sie **Path** und klicken Sie auf **Bearbeiten**.
   - Fügen Sie den Pfad `C:\php` (oder wo Sie PHP extrahiert haben) hinzu.
   - Klicken Sie auf **OK**, um alle Fenster zu schließen.

4. **PHP konfigurieren**:
   - Kopieren Sie `php.ini-development` zu `php.ini`.
   - Bearbeiten Sie `php.ini`, um PHP wie benötigt zu konfigurieren (z. B. `extension_dir` einstellen, Erweiterungen aktivieren).

5. **PHP-Installation überprüfen**:
   - Öffnen Sie die Eingabeaufforderung und führen Sie aus:
     ```cmd
     php -v
     ```

#### **Mehrere Versionen von PHP installieren**

1. **Wiederholen Sie die obigen Schritte** für jede Version und platzieren Sie jede in einem separaten Verzeichnis (z. B. `C:\php7`, `C:\php8`).

2. **Wechseln Sie zwischen Versionen**, indem Sie die System-PATH-Variable anpassen, um auf das gewünschte Versionsverzeichnis zu verweisen.

### **Ubuntu (20.04, 22.04 usw.)**

#### **PHP mit apt installieren**

1. **Paketlisten aktualisieren**:
   - Öffnen Sie das Terminal und führen Sie aus:
     ```bash
     sudo apt update
     ```

2. **PHP installieren**:
   - Installieren Sie die neueste PHP-Version:
     ```bash
     sudo apt install php
     ```
   - Um eine spezifische Version zu installieren, z. B. PHP 8.1:
     ```bash
     sudo apt install php8.1
     ```

3. **Zusätzliche Module installieren** (optional):
   - Zum Beispiel, um MySQL-Unterstützung zu installieren:
     ```bash
     sudo apt install php8.1-mysql
     ```

4. **Wechseln Sie zwischen PHP-Versionen**:
   - Verwenden Sie `update-alternatives`:
     ```bash
     sudo update-alternatives --set php /usr/bin/php8.1
     ```

5. **Die installierte Version überprüfen**:
   - Führen Sie aus:
     ```bash
     php -v
     ```

### **Rocky Linux**

#### **PHP mit yum/dnf installieren**

1. **Aktivieren Sie das EPEL-Repository**:
   - Öffnen Sie das Terminal und führen Sie aus:
     ```bash
     sudo dnf install epel-release
     ```

2. **Installieren Sie das Remi-Repository**:
   - Führen Sie aus:
     ```bash
     sudo dnf install https://rpms.remirepo.net/enterprise/remi-release-8.rpm
     sudo dnf module reset php
     ```

3. **PHP installieren**:
   - Um die Standardversion zu installieren:
     ```bash
     sudo dnf install php
     ```
   - Um eine spezifische Version zu installieren, z. B. PHP 7.4:
     ```bash
     sudo dnf module install php:remi-7.4
     ```

4. **Wechseln Sie zwischen PHP-Versionen**:
   - Verwenden Sie den `dnf`-Modulbefehl:
     ```bash
     sudo dnf module reset php
     sudo dnf module enable php:remi-8.0
     sudo dnf install php
     ```

5. **Die installierte Version überprüfen**:
   - Führen Sie aus:
     ```bash
     php -v
     ```

### **Allgemeine Hinweise**

- Für Entwicklungsumgebungen ist es wichtig, PHP-Einstellungen gemäß den Anforderungen Ihres Projekts zu konfigurieren. 
- Beim Wechseln von PHP-Versionen stellen Sie sicher, dass alle relevanten PHP-Erweiterungen für die spezifische Version installiert sind, die Sie verwenden möchten.
- Starten Sie Ihren Webserver (Apache, Nginx usw.) neu, nachdem Sie PHP-Versionen gewechselt oder Konfigurationen aktualisiert haben, um die Änderungen anzuwenden.