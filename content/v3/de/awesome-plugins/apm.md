# FlightPHP APM-Dokumentation

Willkommen bei FlightPHP APM – Ihrem persönlichen Leistungs-Coach für Ihre App! Diese Anleitung ist Ihre Wegbeschreibung, um Application Performance Monitoring (APM) mit FlightPHP einzurichten, zu verwenden und zu meistern. Ob Sie langsame Anfragen jagen oder sich einfach über Latenz-Diagramme freuen möchten, wir haben Sie abgedeckt. Lassen Sie uns Ihre App schneller machen, Ihre Benutzer glücklicher und Ihre Debugging-Sitzungen einfacher!

## Warum APM wichtig ist

Stellen Sie sich vor: Ihre App ist ein volles Restaurant. Ohne eine Möglichkeit, zu verfolgen, wie lange Bestellungen dauern oder wo die Küche stecken bleibt, raten Sie nur, warum die Kunden verärgert abreisen. APM ist Ihr Sous-Chef – es beobachtet jeden Schritt, von eingehenden Anfragen bis zu Datenbankabfragen, und markiert alles, was Sie verlangsamt. Langsame Seiten verlieren Benutzer (Studien sagen, 53 % verlassen eine Seite, wenn sie mehr als 3 Sekunden zum Laden braucht!), und APM hilft Ihnen, diese Probleme *bevor* sie schmerzen, zu erkennen. Es ist proaktiver Frieden – weniger „Warum ist das kaputt?“-Momente, mehr „Schau, wie glatt das läuft!“-Erfolge.

## Installation

Starten Sie mit Composer:

```bash
composer require flightphp/apm
```

Sie benötigen:
- **PHP 7.4+**: Halten wir kompatibel mit LTS-Linux-Distros, während moderne PHP unterstützt wird.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: Das leichtgewichtige Framework, das wir verbessern.

## Erste Schritte

Hier ist Ihre schrittweise Anleitung zu APM-Großartigkeit:

### 1. Registrieren Sie das APM

Fügen Sie dies in Ihre `index.php` oder eine `services.php`-Datei ein, um mit der Verfolgung zu beginnen:

```php
use flight\apm\logger\LoggerFactory;
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json');
$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);
```

**Was passiert hier?**
- `LoggerFactory::create()` greift auf Ihre Konfiguration zu (mehr dazu bald) und richtet einen Logger ein – SQLite per Voreinstellung.
- `Apm` ist der Star – es hört auf Flight-Ereignisse (Anfragen, Routen, Fehler usw.) und sammelt Metriken.
- `bindEventsToFlightInstance($app)` verbindet alles mit Ihrer Flight-App.

**Pro-Tipp: Sampling**
Wenn Ihre App beschäftigt ist, könnte das Protokollieren *jeder* Anfrage zu viel werden. Verwenden Sie eine Sample-Rate (0,0 bis 1,0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Protokolliert 10 % der Anfragen
```

Das hält die Leistung flott, während Sie immer noch solide Daten erhalten.

### 2. Konfigurieren Sie es

Führen Sie dies aus, um Ihre `.runway-config.json` zu erstellen:

```bash
php vendor/bin/runway apm:init
```

**Was macht das?**
- Startet einen Assistenten, der fragt, wo die unbearbeiteten Metriken herkommen (Quelle) und wohin die verarbeiteten Daten gehen (Ziel).
- Standard ist SQLite – z. B. `sqlite:/tmp/apm_metrics.sqlite` für die Quelle, eine andere für das Ziel.
- Am Ende erhalten Sie eine Konfiguration wie:
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

> Dieser Prozess fragt auch, ob Sie die Migrationen für diese Einrichtung ausführen möchten. Wenn Sie das zum ersten Mal einrichten, lautet die Antwort ja.

**Warum zwei Orte?**
Unbearbeitete Metriken sammeln sich schnell (denken Sie an unfiltrierte Protokolle). Der Worker verarbeitet sie in ein strukturiertes Ziel für das Dashboard. Das hält alles ordentlich!

### 3. Verarbeiten Sie Metriken mit dem Worker

Der Worker wandelt unbearbeitete Metriken in dashboard-fähige Daten um. Führen Sie ihn einmal aus:

```bash
php vendor/bin/runway apm:worker
```

**Was macht er?**
- Liest aus Ihrer Quelle (z. B. `apm_metrics.sqlite`).
- Verarbeitet bis zu 100 Metriken (Standard-Batch-Größe) in Ihr Ziel.
- Stoppt, wenn erledigt oder keine Metriken mehr übrig sind.

**Lassen Sie ihn laufen**
Für Live-Apps möchten Sie eine kontinuierliche Verarbeitung. Hier sind Ihre Optionen:

- **Daemon-Modus**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Läuft für immer und verarbeitet Metriken, sobald sie eintreffen. Toll für Entwicklung oder kleine Einrichtungen.

- **Crontab**:
  Fügen Sie dies zu Ihrer crontab hinzu (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Führt jede Minute aus – perfekt für die Produktion.

- **Tmux/Screen**:
  Starten Sie eine abtrennbare Sitzung:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Strg+B, dann D zum Abtrennen; `tmux attach -t apm-worker` zum Wiederverbinden
  ```
  Hält es am Laufen, auch wenn Sie sich abmelden.

- **Benutzerdefinierte Anpassungen**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Verarbeitet 50 Metriken auf einmal.
  - `--max_messages 1000`: Stoppt nach 1000 Metriken.
  - `--timeout 300`: Beendet nach 5 Minuten.

**Warum die Mühe?**
Ohne den Worker ist Ihr Dashboard leer. Es ist die Brücke zwischen unbearbeiteten Protokollen und handlungsrelevanten Erkenntnissen.

### 4. Starten Sie das Dashboard

Sehen Sie die Vitalwerte Ihrer App:

```bash
php vendor/bin/runway apm:dashboard
```

**Was ist das?**
- Startet einen PHP-Server unter `http://localhost:8001/apm/dashboard`.
- Zeigt Anfrageprotokolle, langsame Routen, Fehlerquoten und mehr.

**Passen Sie es an**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Von jeder IP zugänglich (praktisch für Fernansichten).
- `--port 8080`: Verwenden Sie einen anderen Port, wenn 8001 belegt ist.
- `--php-path`: Zeigen Sie auf PHP, wenn es nicht in Ihrem PATH ist.

Öffnen Sie die URL in Ihrem Browser und erkunden Sie!

#### Produktionsmodus

In der Produktion müssen Sie möglicherweise einige Techniken ausprobieren, um das Dashboard zum Laufen zu bringen, da wahrscheinlich Firewalls und andere Sicherheitsmaßnahmen vorhanden sind. Hier sind ein paar Optionen:

- **Verwenden Sie einen Reverse-Proxy**: Richten Sie Nginx oder Apache ein, um Anfragen an das Dashboard weiterzuleiten.
- **SSH-Tunnel**: Wenn Sie per SSH auf den Server zugreifen können, verwenden Sie `ssh -L 8080:localhost:8001 youruser@yourserver`, um das Dashboard auf Ihren lokalen Computer zu tunneln.
- **VPN**: Wenn Ihr Server hinter einem VPN liegt, verbinden Sie sich damit und greifen Sie direkt auf das Dashboard zu.
- **Firewall konfigurieren**: Öffnen Sie Port 8001 für Ihre IP oder das Netzwerk des Servers (oder welchen Port Sie festgelegt haben).
- **Apache/Nginx konfigurieren**: Wenn Sie einen Webserver vor Ihrer Anwendung haben, können Sie ihn für eine Domain oder Subdomain konfigurieren. Wenn Sie das tun, legen Sie das Document Root auf `/path/to/your/project/vendor/flightphp/apm/dashboard` fest.

#### Möchten Sie ein anderes Dashboard?

Sie können Ihr eigenes Dashboard erstellen, wenn Sie möchten! Schauen Sie in das Verzeichnis `vendor/flightphp/apm/src/apm/presenter` für Ideen, wie Sie die Daten für Ihr eigenes Dashboard darstellen können!

## Dashboard-Funktionen

Das Dashboard ist Ihr APM-Hauptquartier – hier ist, was Sie sehen werden:

- **Anfrageprotokoll**: Jede Anfrage mit Zeitstempel, URL, Antwortcode und Gesamtzeit. Klicken Sie auf „Details“, um Middleware, Abfragen und Fehler zu sehen.
- **Langsamste Anfragen**: Die Top 5 Anfragen, die Zeit verbrauchen (z. B. „/api/heavy“ bei 2,5 s).
- **Langsamste Routen**: Die Top 5 Routen nach durchschnittlicher Zeit – großartig zum Erkennen von Mustern.
- **Fehlerquote**: Prozentsatz der fehlgeschlagenen Anfragen (z. B. 2,3 % 500er).
- **Latenz-Percentile**: 95. (p95) und 99. (p99) Antwortzeiten – kennen Sie Ihre schlimmsten Szenarien.
- **Antwortcode-Diagramm**: Visualisieren Sie 200er, 404er, 500er im Laufe der Zeit.
- **Lange Abfragen/Middleware**: Top 5 langsame Datenbankaufrufe und Middleware-Schichten.
- **Cache-Treffer/Verfehlung**: Wie oft Ihr Cache hilft.

**Zusätze**:
- Filtern Sie nach „Letzte Stunde“, „Letzter Tag“ oder „Letzte Woche“.
- Schalten Sie den Dunkelmodus für nächtliche Sitzungen ein.

**Beispiel**:
Eine Anfrage zu `/users` könnte zeigen:
- Gesamtzeit: 150 ms
- Middleware: `AuthMiddleware->handle` (50 ms)
- Abfrage: `SELECT * FROM users` (80 ms)
- Cache: Treffer bei `user_list` (5 ms)

## Hinzufügen benutzerdefinierter Ereignisse

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
In den Anfragedetails des Dashboards unter „Benutzerdefinierte Ereignisse“ – erweiterbar mit hübscher JSON-Formatierung.

**Anwendungsfallsbeispiel**:
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
Jetzt sehen Sie, ob diese API Ihre App bremst!

## Datenbanküberwachung

Verfolgen Sie PDO-Abfragen so:

```php
use flight\database\PdoWrapper;

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite');
$Apm->addPdoConnection($pdo);
```

**Was Sie erhalten:**
- Abfragetext (z. B. `SELECT * FROM users WHERE id = ?`)
- Ausführungszeit (z. B. 0,015 s)
- Zeilenanzahl (z. B. 42)

**Hinweis:**
- **Optional**: Überspringen Sie das, wenn Sie keine DB-Verfolgung benötigen.
- **Nur PdoWrapper**: Kern-PDO ist noch nicht angebunden – bleiben Sie dran!
- **Leistungs-Warnung**: Das Protokollieren jeder Abfrage auf einer DB-intensiven Site kann Dinge verlangsamen. Verwenden Sie Sampling (`$Apm = new Apm($ApmLogger, 0.1)`), um die Last zu verringern.

**Beispielausgabe**:
- Abfrage: `SELECT name FROM products WHERE price > 100`
- Zeit: 0,023 s
- Zeilen: 15

## Worker-Optionen

Passen Sie den Worker an Ihre Vorlieben an:

- `--timeout 300`: Stoppt nach 5 Minuten – gut für Tests.
- `--max_messages 500`: Begrenzt auf 500 Metriken – hält es endlich.
- `--batch_size 200`: Verarbeitet 200 auf einmal – balanciert Geschwindigkeit und Speicher.
- `--daemon`: Läuft ununterbrochen – ideal für Live-Überwachung.

**Beispiel**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Läuft eine Stunde lang und verarbeitet 100 Metriken auf einmal.

## Anfrage-ID in der App

Jede Anfrage hat eine eindeutige Anfrage-ID zur Verfolgung. Sie können diese ID in Ihrer App verwenden, um Protokolle und Metriken zu korrelieren. Zum Beispiel können Sie die Anfrage-ID auf einer Fehlerseite hinzufügen:

```php
Flight::map('error', function($message) {
	// Holen Sie die Anfrage-ID aus dem Response-Header X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// Zusätzlich könnten Sie sie aus der Flight-Variablen holen
	// Diese Methode funktioniert nicht gut in Swoole oder anderen asynchronen Plattformen.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## Upgrade

Wenn Sie auf eine neuere Version des APM upgraden, besteht die Möglichkeit, dass Datenbank-Migrationen ausgeführt werden müssen. Führen Sie dazu den folgenden Befehl aus:

```bash
php vendor/bin/runway apm:migrate
```
Das führt alle benötigten Migrationen aus, um das Datenbankschema auf die neueste Version zu aktualisieren.

**Hinweis:** Wenn Ihre APM-Datenbank groß ist, kann diese Migration einige Zeit in Anspruch nehmen. Führen Sie diesen Befehl möglicherweise in Spitzenzeiten aus.

## Bereinigen alter Daten

Um Ihre Datenbank ordentlich zu halten, können Sie alte Daten bereinigen. Das ist besonders nützlich, wenn Sie eine beschäftigte App betreiben und die Datenbankgröße handhabbar halten möchten.
Führen Sie dazu den folgenden Befehl aus:

```bash
php vendor/bin/runway apm:purge
```
Das entfernt alle Daten, die älter als 30 Tage sind, aus der Datenbank. Sie können die Anzahl der Tage anpassen, indem Sie einen anderen Wert an die Option `--days` übergeben:

```bash
php vendor/bin/runway apm:purge --days 7
```
Das entfernt alle Daten, die älter als 7 Tage sind, aus der Datenbank.

## Fehlerbehebung

Feststecken? Probieren Sie das:

- **Kein Dashboard-Daten?**
  - Läuft der Worker? Überprüfen Sie `ps aux | grep apm:worker`.
  - Stimmen die Konfigurationspfade überein? Überprüfen Sie, ob die DSNs in `.runway-config.json` auf echte Dateien zeigen.
  - Führen Sie `php vendor/bin/runway apm:worker` manuell aus, um ausstehende Metriken zu verarbeiten.

- **Worker-Fehler?**
  - Schauen Sie in Ihre SQLite-Dateien (z. B. `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Überprüfen Sie PHP-Protokolle auf Stack-Traces.

- **Dashboard startet nicht?**
  - Ist Port 8001 belegt? Verwenden Sie `--port 8080`.
  - PHP nicht gefunden? Verwenden Sie `--php-path /usr/bin/php`.
  - Firewall blockiert? Öffnen Sie den Port oder verwenden Sie `--host localhost`.

- **Zu langsam?**
  - Verringern Sie die Sample-Rate: `$Apm = new Apm($ApmLogger, 0.05)` (5 %).
  - Reduzieren Sie die Batch-Größe: `--batch_size 20`.