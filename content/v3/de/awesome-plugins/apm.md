# FlightPHP APM Dokumentation

Willkommen bei FlightPHP APM – dem persönlichen Performance-Coach für Ihre App! Dieser Leitfaden ist Ihre Roadmap zur Einrichtung, Nutzung und Beherrschung der Application Performance Monitoring (APM) mit FlightPHP. Ob Sie langsame Anfragen aufspüren oder einfach nur Latency-Diagramme analysieren möchten, wir haben Sie abgedeckt. Lassen Sie uns Ihre App schneller machen, Ihre Nutzer glücklicher und Ihre Debugging-Sitzungen zu einem Kinderspiel!

Sehen Sie sich eine [Demo](https://flightphp-docs-apm.sky-9.com/apm/dashboard) des Dashboards für die Flight Docs Site an.

![FlightPHP APM](/images/apm.png)

## Warum APM wichtig ist

Stellen Sie sich vor: Ihre App ist ein volles Restaurant. Ohne eine Möglichkeit, zu verfolgen, wie lange Bestellungen dauern oder wo die Küche stockt, raten Sie, warum Kunden unzufrieden gehen. APM ist Ihr Sous-Chef – es beobachtet jeden Schritt, von eingehenden Anfragen bis zu Datenbankabfragen, und markiert alles, was Sie verlangsamt. Langsame Seiten verlieren Nutzer (Studien sagen, 53 % verlassen die Seite, wenn sie mehr als 3 Sekunden zum Laden braucht!), und APM hilft Ihnen, diese Probleme *bevor* sie schmerzen zu erkennen. Es ist proaktive Seelenruhe – weniger „Warum ist das kaputt?“-Momente, mehr „Schau, wie reibungslos das läuft!“-Erfolge.

## Installation

Beginnen Sie mit Composer:

```bash
composer require flightphp/apm
```

Sie benötigen:
- **PHP 7.4+**: Hält uns kompatibel mit LTS Linux-Distributionen, während es modernes PHP unterstützt.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: Das leichte Framework, das wir boosten.

## Unterstützte Datenbanken

FlightPHP APM unterstützt derzeit die folgenden Datenbanken zur Speicherung von Metriken:

- **SQLite3**: Einfach, dateibasiert und ideal für lokale Entwicklung oder kleine Apps. Standardoption in den meisten Setups.
- **MySQL/MariaDB**: Ideal für größere Projekte oder Produktionsumgebungen, in denen Sie robuste, skalierbare Speicherung benötigen.

Sie können Ihren Datenbanktyp während des Konfigurationsschritts wählen (siehe unten). Stellen Sie sicher, dass Ihre PHP-Umgebung die notwendigen Erweiterungen installiert hat (z. B. `pdo_sqlite` oder `pdo_mysql`).

## Erste Schritte

Hier ist Ihr Schritt-für-Schritt zu APM-Großartigkeit:

### 1. Registrieren Sie die APM

Fügen Sie das in Ihre `index.php` oder eine `services.php`-Datei ein, um mit dem Tracking zu beginnen:

```php
use flight\apm\logger\LoggerFactory;
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json');
$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);

// Wenn Sie eine Datenbankverbindung hinzufügen
// Muss PdoWrapper oder PdoQueryCapture aus Tracy Extensions sein
$pdo = new PdoWrapper('mysql:host=localhost;dbname=example', 'user', 'pass', null, true); // <-- True erforderlich, um Tracking in der APM zu aktivieren.
$Apm->addPdoConnection($pdo);
```

**Was passiert hier?**
- `LoggerFactory::create()` greift auf Ihre Konfiguration zu (mehr dazu bald) und richtet einen Logger ein – standardmäßig SQLite.
- `Apm` ist der Star – es hört auf Flights Events (Anfragen, Routen, Fehler usw.) und sammelt Metriken.
- `bindEventsToFlightInstance($app)` verbindet alles mit Ihrer Flight-App.

**Pro-Tipp: Sampling**
Wenn Ihre App beschäftigt ist, könnte das Loggen *jeder* Anfrage die Dinge überlasten. Verwenden Sie eine Sample-Rate (0.0 bis 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Protokolliert 10 % der Anfragen
```

Das hält die Performance knackig, während es Ihnen dennoch solide Daten liefert.

### 2. Konfigurieren Sie es

Führen Sie das aus, um Ihre `.runway-config.json` zu erstellen:

```bash
php vendor/bin/runway apm:init
```

**Was macht das?**
- Startet einen Wizard, der fragt, wo rohe Metriken herkommen (Quelle) und wo verarbeitete Daten hingehen (Ziel).
- Standard ist SQLite – z. B. `sqlite:/tmp/apm_metrics.sqlite` für die Quelle, eine andere für das Ziel.
- Sie erhalten eine Konfiguration wie:
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

> Dieser Prozess fragt auch, ob Sie die Migrationen für dieses Setup ausführen möchten. Wenn Sie das zum ersten Mal einrichten, lautet die Antwort ja.

**Warum zwei Orte?**
Rohe Metriken häufen sich schnell an (denken Sie an ungefilterte Logs). Der Worker verarbeitet sie in ein strukturiertes Ziel für das Dashboard. Hält alles ordentlich!

### 3. Metriken mit dem Worker verarbeiten

Der Worker verwandelt rohe Metriken in dashboard-bereite Daten. Führen Sie ihn einmal aus:

```bash
php vendor/bin/runway apm:worker
```

**Was macht er?**
- Liest aus Ihrer Quelle (z. B. `apm_metrics.sqlite`).
- Verarbeitet bis zu 100 Metriken (Standard-Batch-Größe) in Ihr Ziel.
- Stoppt, wenn fertig oder keine Metriken mehr da sind.

**Am Laufen halten**
Für Live-Apps möchten Sie kontinuierliche Verarbeitung. Hier sind Ihre Optionen:

- **Daemon-Modus**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Läuft ewig und verarbeitet Metriken, sobald sie kommen. Gut für Dev oder kleine Setups.

- **Crontab**:
  Fügen Sie das zu Ihrer Crontab hinzu (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Feuert jede Minute – perfekt für Produktion.

- **Tmux/Screen**:
  Starten Sie eine abtrennbare Sitzung:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, dann D zum Abtrennen; `tmux attach -t apm-worker` zum Wiederverbinden
  ```
  Hält es am Laufen, auch wenn Sie ausloggen.

- **Benutzerdefinierte Anpassungen**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Verarbeitet 50 Metriken auf einmal.
  - `--max_messages 1000`: Stoppt nach 1000 Metriken.
  - `--timeout 300`: Beendet nach 5 Minuten.

**Warum die Mühe?**
Ohne den Worker ist Ihr Dashboard leer. Es ist die Brücke zwischen rohen Logs und handlungsrelevanten Erkenntnissen.

### 4. Dashboard starten

Sehen Sie die Vitalwerte Ihrer App:

```bash
php vendor/bin/runway apm:dashboard
```

**Was ist das?**
- Startet einen PHP-Server unter `http://localhost:8001/apm/dashboard`.
- Zeigt Anfragen-Logs, langsame Routen, Fehlerquoten und mehr.

**Anpassen**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Erreichbar von jeder IP (praktisch für Fernzugriff).
- `--port 8080`: Verwenden Sie einen anderen Port, wenn 8001 belegt ist.
- `--php-path`: Zeigen Sie auf PHP, wenn es nicht in Ihrem PATH ist.

Öffnen Sie die URL in Ihrem Browser und erkunden Sie!

#### Produktionsmodus

Für die Produktion müssen Sie möglicherweise einige Techniken ausprobieren, um das Dashboard zum Laufen zu bringen, da wahrscheinlich Firewalls und andere Sicherheitsmaßnahmen im Spiel sind. Hier sind ein paar Optionen:

- **Reverse Proxy verwenden**: Richten Sie Nginx oder Apache ein, um Anfragen an das Dashboard weiterzuleiten.
- **SSH-Tunnel**: Wenn Sie per SSH auf den Server zugreifen können, verwenden Sie `ssh -L 8080:localhost:8001 youruser@yourserver`, um das Dashboard zu Ihrem lokalen Rechner zu tunneln.
- **VPN**: Wenn Ihr Server hinter einem VPN ist, verbinden Sie sich damit und greifen Sie direkt auf das Dashboard zu.
- **Firewall konfigurieren**: Öffnen Sie Port 8001 für Ihre IP oder das Netzwerk des Servers. (Oder welchen Port Sie auch eingestellt haben).
- **Apache/Nginx konfigurieren**: Wenn Sie einen Webserver vor Ihrer Anwendung haben, können Sie ihn für eine Domain oder Subdomain konfigurieren. Wenn Sie das tun, setzen Sie das Document Root auf `/path/to/your/project/vendor/flightphp/apm/dashboard`.

#### Wollen Sie ein anderes Dashboard?

Sie können Ihr eigenes Dashboard bauen, wenn Sie möchten! Schauen Sie in das Verzeichnis `vendor/flightphp/apm/src/apm/presenter` für Ideen, wie Sie die Daten für Ihr eigenes Dashboard präsentieren können!

## Dashboard-Funktionen

Das Dashboard ist Ihr APM-Hauptquartier – hier ist, was Sie sehen werden:

- **Anfragen-Log**: Jede Anfrage mit Zeitstempel, URL, Response-Code und Gesamtzeit. Klicken Sie auf „Details“ für Middleware, Abfragen und Fehler.
- **Langsamste Anfragen**: Top 5 Anfragen, die Zeit fressen (z. B. „/api/heavy“ bei 2,5 s).
- **Langsamste Routen**: Top 5 Routen nach durchschnittlicher Zeit – super zum Erkennen von Mustern.
- **Fehlerquote**: Prozentsatz fehlgeschlagener Anfragen (z. B. 2,3 % 500er).
- **Latenz-Percentile**: 95. (p95) und 99. (p99) Response-Zeiten – kennen Sie Ihre Worst-Case-Szenarien.
- **Response-Code-Diagramm**: Visualisieren Sie 200er, 404er, 500er über die Zeit.
- **Lange Abfragen/Middleware**: Top 5 langsame Datenbankaufrufe und Middleware-Schichten.
- **Cache-Treffer/Verfehlung**: Wie oft Ihr Cache den Tag rettet.

**Extras**:
- Filtern nach „Letzte Stunde“, „Letzter Tag“ oder „Letzte Woche“.
- Umschalten auf Dark Mode für nächtliche Sessions.

**Beispiel**:
Eine Anfrage an `/users` könnte zeigen:
- Gesamtzeit: 150 ms
- Middleware: `AuthMiddleware->handle` (50 ms)
- Abfrage: `SELECT * FROM users` (80 ms)
- Cache: Treffer bei `user_list` (5 ms)

## Hinzufügen benutzerdefinierter Events

Verfolgen Sie alles – wie einen API-Aufruf oder Zahlungsprozess:

```php
use flight\apm\CustomEvent;

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**Wo erscheint es?**
In den Anfragen-Details des Dashboards unter „Custom Events“ – erweiterbar mit hübscher JSON-Formatierung.

**Anwendungsfall**:
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
Jetzt sehen Sie, ob diese API Ihre App herunterzieht!

## Datenbank-Monitoring

Verfolgen Sie PDO-Abfragen so:

```php
use flight\database\PdoWrapper;

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite', null, null, null, true); // <-- True erforderlich, um Tracking in der APM zu aktivieren.
$Apm->addPdoConnection($pdo);
```

**Was Sie bekommen**:
- Abfragetext (z. B. `SELECT * FROM users WHERE id = ?`)
- Ausführungszeit (z. B. 0,015 s)
- Zeilenanzahl (z. B. 42)

**Achtung**:
- **Optional**: Überspringen Sie das, wenn Sie kein DB-Tracking brauchen.
- **Nur PdoWrapper**: Core PDO ist noch nicht integriert – bleiben Sie dran!
- **Performance-Warnung**: Das Loggen jeder Abfrage auf einer DB-lastigen Site kann Dinge verlangsamen. Verwenden Sie Sampling (`$Apm = new Apm($ApmLogger, 0.1)`), um die Last zu reduzieren.

**Beispiel-Ausgabe**:
- Abfrage: `SELECT name FROM products WHERE price > 100`
- Zeit: 0,023 s
- Zeilen: 15

## Worker-Optionen

Passen Sie den Worker nach Ihrem Geschmack an:

- `--timeout 300`: Stoppt nach 5 Minuten – gut für Tests.
- `--max_messages 500`: Begrenzt auf 500 Metriken – hält es endlich.
- `--batch_size 200`: Verarbeitet 200 auf einmal – balanciert Geschwindigkeit und Speicher.
- `--daemon`: Läuft non-stop – ideal für Live-Monitoring.

**Beispiel**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Läuft eine Stunde, verarbeitet 100 Metriken auf einmal.

## Request ID in der App

Jede Anfrage hat eine eindeutige Request ID für das Tracking. Sie können diese ID in Ihrer App verwenden, um Logs und Metriken zu korrelieren. Zum Beispiel können Sie die Request ID auf einer Fehlerseite hinzufügen:

```php
Flight::map('error', function($message) {
	// Holen Sie die Request ID aus dem Response-Header X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// Zusätzlich könnten Sie sie aus der Flight-Variable holen
	// Diese Methode funktioniert nicht gut in Swoole oder anderen asynchronen Plattformen.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Fehler: $message (Request ID: $requestId)";
});
```

## Upgrade

Wenn Sie auf eine neuere Version der APM upgraden, besteht die Möglichkeit, dass Datenbank-Migrationen ausgeführt werden müssen. Sie können das tun, indem Sie den folgenden Befehl ausführen:

```bash
php vendor/bin/runway apm:migrate
```
Das führt alle benötigten Migrationen aus, um das Datenbankschema auf die neueste Version zu aktualisieren.

**Hinweis:** Wenn Ihre APM-Datenbank groß ist, können diese Migrationen einige Zeit in Anspruch nehmen. Sie möchten diesen Befehl vielleicht während der Nebenzeiten ausführen.

## Alte Daten bereinigen

Um Ihre Datenbank ordentlich zu halten, können Sie alte Daten bereinigen. Das ist besonders nützlich, wenn Sie eine beschäftigte App betreiben und die Datenbankgröße handhabbar halten möchten.
Sie können das tun, indem Sie den folgenden Befehl ausführen:

```bash
php vendor/bin/runway apm:purge
```
Das entfernt alle Daten, die älter als 30 Tage sind, aus der Datenbank. Sie können die Anzahl der Tage anpassen, indem Sie einen anderen Wert an die `--days`-Option übergeben:

```bash
php vendor/bin/runway apm:purge --days 7
```
Das entfernt alle Daten, die älter als 7 Tage sind, aus der Datenbank.

## Fehlerbehebung

Feststecken? Probieren Sie diese aus:

- **Kein Dashboard-Daten?**
  - Läuft der Worker? Überprüfen Sie `ps aux | grep apm:worker`.
  - Stimmen die Konfigurationspfade? Überprüfen Sie, ob die DSNs in `.runway-config.json` auf echte Dateien zeigen.
  - Führen Sie `php vendor/bin/runway apm:worker` manuell aus, um ausstehende Metriken zu verarbeiten.

- **Worker-Fehler?**
  - Schauen Sie in Ihre SQLite-Dateien (z. B. `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Überprüfen Sie PHP-Logs auf Stack-Traces.

- **Dashboard startet nicht?**
  - Port 8001 belegt? Verwenden Sie `--port 8080`.
  - PHP nicht gefunden? Verwenden Sie `--php-path /usr/bin/php`.
  - Firewall blockiert? Öffnen Sie den Port oder verwenden Sie `--host localhost`.

- **Zu langsam?**
  - Senken Sie die Sample-Rate: `$Apm = new Apm($ApmLogger, 0.05)` (5 %).
  - Reduzieren Sie die Batch-Größe: `--batch_size 20`.

- **Keine Ausnahmen/Fehler getrackt?**
  - Wenn Sie [Tracy](https://tracy.nette.org/) für Ihr Projekt aktiviert haben, überschreibt es die Fehlerbehandlung von Flight. Sie müssen Tracy deaktivieren und sicherstellen, dass `Flight::set('flight.handle_errors', true);` gesetzt ist.

- **Datenbankabfragen nicht getrackt?**
  - Stellen Sie sicher, dass Sie `PdoWrapper` für Ihre Datenbankverbindungen verwenden.
  - Vergewissern Sie sich, dass Sie das letzte Argument im Konstruktor auf `true` setzen.