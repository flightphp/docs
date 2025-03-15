# FlightPHP APM Dokumentation

Willkommen in FlightPHP APM—der persönliche Leistungstrainer deiner App! Dieser Leitfaden ist deine Roadmap zum Einrichten, Verwenden und Meistern des Application Performance Monitoring (APM) mit FlightPHP. Egal, ob du langsame Anfragen aufspürst oder einfach nur die Latenzdiagramme bewundern möchtest, wir haben dich abgedeckt. Lass uns deine App schneller, deine Benutzer glücklicher und deine Debugging-Sitzungen zum Kinderspiel machen!

## Warum APM wichtig ist

Stell dir Folgendes vor: Deine App ist ein geschäftiges Restaurant. Ohne eine Möglichkeit, die Dauer von Bestellungen zu verfolgen oder wo die Küche ins Stocken gerät, rätst du, warum Kunden verärgert gehen. APM ist dein Sous-Chef—er beobachtet jeden Schritt, von eingehenden Anfragen bis zu Datenbankabfragen, und markiert alles, was dich verlangsamt. Langsame Seiten verlieren Benutzer (Studien zeigen, dass 53% abspringen, wenn eine Seite über 3 Sekunden zum Laden benötigt!), und APM hilft dir, diese Probleme *bevor* sie schmerzhaft werden. Es ist proaktive Gelassenheit—weniger „warum ist das kaputt?“ Momente, mehr „schau, wie reibungslos das läuft!“ Gewinne.

## Installation

Starte mit Composer:

```bash
composer require flightphp/apm
```

Du benötigst:
- **PHP 7.4+**: Hält uns kompatibel mit LTS Linux-Distributionen und unterstützt modernes PHP.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: Das leichte Framework, das wir verstärken.

## Erste Schritte

Hier ist deine Schritt-für-Schritt-Anleitung zur APM-Tollheit:

### 1. Registriere das APM

Füge dies in deine `index.php` oder eine `services.php` Datei ein, um das Tracking zu starten:

```php
use flight\apm\logger\LoggerFactory;
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json');
$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);
```

**Was passiert hier?**
- `LoggerFactory::create()` erfasst deine Konfiguration (mehr dazu bald) und richtet einen Logger ein—SQLite standardmäßig.
- `Apm` ist der Star—er hört auf die Events von Flight (Anfragen, Routen, Fehler usw.) und sammelt Kennzahlen.
- `bindEventsToFlightInstance($app)` verknüpft alles mit deiner Flight-App.

**Pro-Tipp: Sampling**
Wenn deine App beschäftigt ist, könnte das Protokollieren *jeder* Anfrage die Dinge überlasten. Verwende eine Stichprobenrate (0.0 bis 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Protokolliert 10% der Anfragen
```

Dies hält die Leistung schnell, während es dir dennoch solide Daten gibt.

### 2. Konfiguriere es

Führe dies aus, um deine `.runway-config.json` zu erstellen:

```bash
php vendor/bin/runway apm:init
```

**Was macht das?**
- Startet einen Assistenten, der fragt, woher die Rohdaten kommen (Quelle) und wohin die verarbeiteten Daten gehen (Ziel).
- Standard ist SQLite—z.B. `sqlite:/tmp/apm_metrics.sqlite` für die Quelle, ein anderes für das Ziel.
- Du wirst mit einer Konfiguration wie dieser enden:
  ```json
  {
    "apm": {
      "source_type": "sqlite",
      "source_db_dsn": "sqlite:/tmp/apm_metrics.sqlite",
      "storage_type": "sqlite",
      "dest_db_dsn": "sqlite:/tmp/apm_metrics_processed.sqlite"
    }
  }
  ```

**Warum zwei Standorte?**
Rohdaten häufen sich schnell an (denk an ungefilterte Protokolle). Der Worker verarbeitet sie in ein strukturiertes Ziel für das Dashboard. Hält die Dinge ordentlich!

### 3. Verarbeite Kennzahlen mit dem Worker

Der Worker verwandelt Rohdaten in dashboard-bereite Daten. Führe ihn einmal aus:

```bash
php vendor/bin/runway apm:worker
```

**Was macht er?**
- Liest von deiner Quelle (z.B. `apm_metrics.sqlite`).
- Verarbeitet bis zu 100 Kennzahlen (standardmäßige Batchgröße) in dein Ziel.
- Stoppt, wenn er fertig ist oder wenn keine Kennzahlen mehr vorhanden sind.

**Lass ihn laufen**
Für Live-Apps möchtest du kontinuierliche Verarbeitung. Hier sind deine Optionen:

- **Daemon-Modus**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Läuft für immer, verarbeitet Kennzahlen, sobald sie eintreffen. Großartig für Entwicklungs- oder kleine Setups.

- **Crontab**:
  Füge dies zu deiner Crontab hinzu (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Läuft jede Minute—perfekt für die Produktion.

- **Tmux/Screen**:
  Starte eine abtrennbare Sitzung:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, dann D um zu trennen; `tmux attach -t apm-worker` um wieder zu verbinden
  ```
  Hält es am Laufen, auch wenn du dich abmeldest.

- **Benutzerdefinierte Anpassungen**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Verarbeite 50 Kennzahlen gleichzeitig.
  - `--max_messages 1000`: Stoppe nach 1000 Kennzahlen.
  - `--timeout 300`: Beende nach 5 Minuten.

**Warum sich die Mühe machen?**
Ohne den Worker ist dein Dashboard leer. Es ist die Brücke zwischen Rohprotokollen und umsetzbaren Erkenntnissen.

### 4. Starte das Dashboard

Sieh die Vitalwerte deiner App:

```bash
php vendor/bin/runway apm:dashboard
```

**Was ist das?**
- Startet einen PHP-Server bei `http://localhost:8001/apm/dashboard`.
- Zeigt Anfragenprotokolle, langsame Routen, Fehlerquoten und mehr an.

**Passe es an**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Von jeder IP erreichbar (praktisch für die Fernansicht).
- `--port 8080`: Verwende einen anderen Port, falls 8001 belegt ist.
- `--php-path`: Verweise auf PHP, wenn es nicht in deinem PATH ist.

Rufe die URL in deinem Browser auf und erkunde!

#### Produktionsmodus

Für die Produktion musst du vielleicht ein paar Techniken ausprobieren, um das Dashboard zum Laufen zu bringen, da wahrscheinlich Firewalls und andere Sicherheitsmaßnahmen vorhanden sind. Hier sind einige Optionen:

- **Verwende einen Reverse Proxy**: Richte Nginx oder Apache ein, um Anfragen an das Dashboard weiterzuleiten.
- **SSH-Tunnel**: Wenn du auf den Server SSH haben kannst, benutze `ssh -L 8080:localhost:8001 youruser@yourserver`, um das Dashboard zu deinem lokalen Rechner zu tunneln.
- **VPN**: Wenn dein Server hinter einem VPN ist, verbinde dich damit und greife direkt auf das Dashboard zu.
- **Firewall konfigurieren**: Öffne den Port 8001 für deine IP oder das Netzwerk des Servers. (oder welchen Port du auch immer eingestellt hast).
- **Apache/Nginx konfigurieren**: Wenn du einen Webserver vor deiner Anwendung hast, kannst du ihn auf eine Domain oder Subdomain konfigurieren. Wenn du dies tust, legst du das Dokumentenstammverzeichnis auf `/path/to/your/project/vendor/flightphp/apm/dashboard`.

#### Möchtest du ein anderes Dashboard?

Du kannst auch dein eigenes Dashboard erstellen, wenn du möchtest! Schau dir das Verzeichnis vendor/flightphp/apm/src/apm/presenter an, um Ideen zu sammeln, wie du die Daten für dein eigenes Dashboard präsentieren kannst!

## Dashboard-Funktionen

Das Dashboard ist dein APM-HQ—hier ist, was du sehen wirst:

- **Anfrageprotokoll**: Jede Anfrage mit Timestamp, URL, Antwort-Code und Gesamtzeit. Klicke auf „Details“ für Middleware, Abfragen und Fehler.
- **Langsamste Anfragen**: Die Top 5 Anfragen, die Zeit beanspruchen (z.B. „/api/heavy“ mit 2,5s).
- **Langsamste Routen**: Die Top 5 Routen nach durchschnittlicher Zeit—großartig zum Erkennen von Mustern.
- **Fehlerquote**: Prozentsatz der fehlgeschlagenen Anfragen (z.B. 2,3% 500er).
- **Latenz-Perzentile**: 95. (p95) und 99. (p99) Antwortzeiten—kenne deine schlimmsten Szenarien.
- **Antwortcode-Diagramm**: Visualisiere 200er, 404er, 500er über die Zeit.
- **Lange Abfragen/Middleware**: Die Top 5 langsamen Datenbankaufrufe und Middleware-Schichten.
- **Cache-Hit/Fehl**: Wie oft dein Cache den Tag rettet.

**Extras**:
- Filter nach „Letzte Stunde“, „Letzter Tag“ oder „Letzte Woche“.
- Schalte den Dunkelmodus für späte Nächte ein.

**Beispiel**:
Eine Anfrage an `/users` könnte folgendes anzeigen:
- Gesamtzeit: 150ms
- Middleware: `AuthMiddleware->handle` (50ms)
- Abfrage: `SELECT * FROM users` (80ms)
- Cache: Treffer bei `user_list` (5ms)

## Hinzufügen benutzerdefinierter Ereignisse

Verfolge alles—wie einen API-Aufruf oder einen Zahlungsprozess:

```php
use flight\apm\CustomEvent;

$app->eventDispatcher()->emit('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**Wo erscheint das?**
Im Dashboard unter den Anfragedetails unter „Benutzerdefinierte Ereignisse“—erweiterbar mit ansprechender JSON-Formatierung.

**Anwendungsfall**:
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->emit('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
Jetzt siehst du, ob diese API deine App ausbremst!

## Datenbanküberwachung

Verfolge PDO-Abfragen wie folgt:

```php
use flight\database\PdoWrapper;

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite');
$Apm->addPdoConnection($pdo);
```

**Was du bekommst**:
- Abfragetext (z.B. `SELECT * FROM users WHERE id = ?`)
- Ausführungszeit (z.B. 0.015s)
- Zeilenzahl (z.B. 42)

**Hinweis**:
- **Optional**: Überspringe dies, wenn du kein DB-Tracking benötigst.
- **Nur PdoWrapper**: Core PDO ist noch nicht verbunden—bleib dran!
- **Leistungswarnung**: Das Protokollieren jeder Abfrage auf einer DB-intensiven Seite kann die Dinge verlangsamen. Verwende Sampling (`$Apm = new Apm($ApmLogger, 0.1)`), um die Last zu verringern.

**Beispielausgabe**:
- Abfrage: `SELECT name FROM products WHERE price > 100`
- Zeit: 0.023s
- Zeilen: 15

## Worker-Optionen

Stimme den Worker nach deinen Wünschen ab:

- `--timeout 300`: Stoppt nach 5 Minuten—gut für Tests.
- `--max_messages 500`: Begrenzt auf 500 Kennzahlen—hält es endlich.
- `--batch_size 200`: Verarbeitet 200 auf einmal—balanciert Geschwindigkeit und Speicher.
- `--daemon`: Läuft nonstop—ideal für die Live-Überwachung.

**Beispiel**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Läuft eine Stunde, verarbeitet 100 Kennzahlen gleichzeitig.

## Fehlersuche

Steck fest? Probiere diese:

- **Keine Dashboard-Daten?**
  - Läuft der Worker? Überprüfe `ps aux | grep apm:worker`.
  - Stimmt der Konfigurationspfad? Verifiziere, dass die `.runway-config.json` DSNs auf echte Dateien zeigen.
  - Führe `php vendor/bin/runway apm:worker` manuell aus, um ausstehende Kennzahlen zu verarbeiten.

- **Worker-Fehler?**
  - Schau dir deine SQLite-Dateien an (z.B. `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Überprüfe die PHP-Protokolle auf Stack-Traces.

- **Dashboard lässt sich nicht starten?**
  - Port 8001 in Gebrauch? Verwende `--port 8080`.
  - PHP nicht gefunden? Verwende `--php-path /usr/bin/php`.
  - Firewall blockiert? Öffne den Port oder verwende `--host localhost`.

- **Zu langsam?**
  - Senke die Stichprobenrate: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Reduziere die Batch-Größe: `--batch_size 20`.