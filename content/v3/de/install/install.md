# Installation

## Dateien herunterladen

Stellen Sie sicher, dass PHP auf Ihrem System installiert ist. Wenn nicht, klicken Sie [hier](#installing-php), um Anweisungen zur Installation für Ihr System zu erhalten.

Wenn Sie [Composer](https://getcomposer.org) verwenden, können Sie folgenden Befehl ausführen:

```bash
composer require flightphp/core
```

ODER Sie können die Dateien [hier herunterladen](https://github.com/flightphp/core/archive/master.zip) und direkt in Ihr Webverzeichnis extrahieren.

## Konfigurieren Sie Ihren Webserver

### Eingebauter PHP-Entwicklungsserver

Dies ist bei weitem der einfachste Weg, um loszulegen. Sie können den integrierten Server verwenden, um Ihre Anwendung auszuführen und sogar SQLite für eine Datenbank zu verwenden (solange sqlite3 auf Ihrem System installiert ist) und praktisch nichts benötigen! Führen Sie nach der Installation von PHP einfach den folgenden Befehl aus:

```bash
php -S localhost:8000
```

Öffnen Sie dann Ihren Browser und gehen Sie zu `http://localhost:8000`.

Wenn Sie das Dokumentenverzeichnis Ihres Projekts in ein anderes Verzeichnis ändern möchten (Beispiel: Ihr Projekt ist `~/myproject`, aber Ihr Dokumentenstamm ist `~/myproject/public/`), können Sie nach dem Wechsel in das Verzeichnis `~/myproject` den folgenden Befehl ausführen:

```bash
php -S localhost:8000 -t public/
```

Öffnen Sie dann Ihren Browser und gehen Sie zu `http://localhost:8000`.

### Apache

Stellen Sie sicher, dass Apache bereits auf Ihrem System installiert ist. Wenn nicht, suchen Sie bei Google nach Anweisungen zur Installation von Apache auf Ihrem System.

Für Apache bearbeiten Sie Ihre `.htaccess`-Datei wie folgt:

```apacheconf
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **Hinweis**: Wenn Sie Flight in einem Unterverzeichnis verwenden müssen, fügen Sie die Zeile `RewriteBase /subdir/` direkt nach `RewriteEngine On` hinzu.

> **Hinweis**: Wenn Sie alle Serverdateien schützen möchten, z. B. eine Datenbank- oder Umgebungsdatei. Fügen Sie dies in Ihre `.htaccess`-Datei ein:

```apacheconf
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

### Nginx

Stellen Sie sicher, dass Nginx bereits auf Ihrem System installiert ist. Wenn nicht, suchen Sie bei Google nach Anweisungen zur Installation von Nginx auf Ihrem System.

Fügen Sie für Nginx Folgendes zur Serverdeklaration hinzu:

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```

## Erstellen Sie Ihre `index.php`-Datei

```php
<?php

// Wenn Sie Composer verwenden, erfordern Sie den Autoloader.
require 'vendor/autoload.php';
// Wenn Sie Composer nicht verwenden, laden Sie das Framework direkt
// require 'flight/Flight.php';

// Definieren Sie dann eine Route und weisen Sie eine Funktion zur Behandlung der Anfrage zu.
Flight::route('/', function () {
  echo 'Hallo Welt!';
});

// Starten Sie das Framework schließlich.
Flight::start();
```

## PHP installieren

Wenn Sie bereits über `php` auf Ihrem System verfügen, überspringen Sie diese Anweisungen und wechseln Sie zum [Download-Abschnitt](#download-the-files)

Sicher! Hier sind die Anweisungen zur Installation von PHP auf macOS, Windows 10/11, Ubuntu und Rocky Linux. Ich werde auch Details darüber enthalten, wie verschiedene Versionen von PHP installiert werden.

### **macOS**

#### **PHP mit Homebrew installieren**

1. **Homebrew installieren** (wenn nicht bereits installiert):
   - Öffnen Sie das Terminal und führen Sie aus:
     ```bash
     /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
     ```

2. **PHP installieren**:
   - Installieren Sie die neueste Version:
     ```bash
     brew install php
     ```
   - Um eine bestimmte Version zu installieren, z. B. PHP 8.1:
     ```bash
     brew tap shivammathur/php
     brew install shivammathur/php/php@8.1
     ```

3. **Zwischen PHP-Versionen wechseln**:
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
   - Besuchen Sie [PHP für Windows](https://windows.php.net/download/) und laden Sie die neueste oder eine bestimmte Version (z. B. 7.4, 8.0) als zip-Datei ohne Thread-Sicherheit herunter.

2. **PHP extrahieren**:
   - Extrahieren Sie die heruntergeladene Zip-Datei nach `C:\php`.

3. **PHP zum System-PATH hinzufügen**:
   - Gehen Sie zu **Systemeigenschaften** > **Umgebungsvariablen**.
   - Unter **Systemvariablen** suchen Sie **Path** und klicken Sie auf **Bearbeiten**.
   - Fügen Sie den Pfad `C:\php` (oder wo immer Sie PHP extrahiert haben) hinzu.
   - Klicken Sie auf **OK**, um alle Fenster zu schließen.

4. **PHP konfigurieren**:
   - Kopieren Sie `php.ini-development` nach `php.ini`.
   - Bearbeiten Sie `php.ini`, um PHP nach Bedarf zu konfigurieren (z. B. `extension_dir` setzen, Erweiterungen aktivieren).

5. **PHP-Installation überprüfen**:
   - Öffnen Sie die Eingabeaufforderung und führen Sie aus:
     ```cmd
     php -v
     ```

#### **Mehrere PHP-Versionen installieren**

1. **Wiederholen Sie die obigen Schritte** für jede Version und platzieren Sie sie jeweils in einem separaten Verzeichnis (z. B. `C:\php7`, `C:\php8`).

2. Zwischen den Versionen wechseln, indem Sie die System-PATH-Variable anpassen, um auf das gewünschte Versionsverzeichnis zu verweisen.

### **Ubuntu (20.04, 22.04, usw.)**

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
   - Um eine bestimmte Version zu installieren, z. B. PHP 8.1:
     ```bash
     sudo apt install php8.1
     ```

3. **Zusätzliche Module installieren** (optional):
   - Zum Beispiel, um die MySQL-Unterstützung zu installieren:
     ```bash
     sudo apt install php8.1-mysql
     ```

4. **Zwischen PHP-Versionen wechseln**:
   - Verwenden Sie `update-alternatives`:
     ```bash
     sudo update-alternatives --set php /usr/bin/php8.1
     ```

5. **Installierte Version überprüfen**:
   - Führen Sie aus:
     ```bash
     php -v
     ```

### **Rocky Linux**

#### **PHP mit yum/dnf installieren**

1. **EPEL-Repository aktivieren**:
   - Öffnen Sie das Terminal und führen Sie aus:
     ```bash
     sudo dnf install epel-release
     ```

2. **Remi-Repository installieren**:
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
   - Um eine bestimmte Version zu installieren, z. B. PHP 7.4:
     ```bash
     sudo dnf module install php:remi-7.4
     ```

4. **Zwischen PHP-Versionen wechseln**:
   - Verwenden Sie den Befehl `dnf module`:
     ```bash
     sudo dnf module reset php
     sudo dnf module enable php:remi-8.0
     sudo dnf install php
     ```

5. **Überprüfen Sie die installierte Version**:
   - Führen Sie aus:
     ```bash
     php -v
     ```

### **Allgemeine Hinweise**

- Für Entwicklungsumgebungen ist es wichtig, die PHP-Einstellungen gemäß den Anforderungen Ihres Projekts zu konfigurieren. 
- Wenn Sie PHP-Versionen wechseln, stellen Sie sicher, dass alle relevanten PHP-Erweiterungen für die spezifische Version installiert sind, die Sie verwenden möchten.
- Starten Sie Ihren Webserver (Apache, Nginx usw.) nach dem Wechsel der PHP-Versionen oder dem Aktualisieren von Konfigurationen neu, um Änderungen zu übernehmen.