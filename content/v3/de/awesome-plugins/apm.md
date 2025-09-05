# FlightPHP APM-Dokumentation

Willkommen bei FlightPHP APM – dein persönlicher Leistungstrainer für deine App! Diese Anleitung ist deine Straßenkarte, um Application Performance Monitoring (APM) mit FlightPHP einzurichten, zu verwenden und zu meistern. Ob du langsame Anfragen aufspürst oder dich einfach über Latenzdiagramme freust, wir haben dich abgedeckt. Lass uns deine App schneller machen, deine Nutzer glücklicher und deine Debugging-Sitzungen unkompliziert!

![FlightPHP APM](/images/apm.png)

## Warum APM wichtig ist

Stell dir vor: Deine App ist ein volles Restaurant. Ohne eine Möglichkeit, zu verfolgen, wie lange Bestellungen dauern oder wo die Küche hängen bleibt, rätst du nur, warum die Kunden verärgert abreisen. APM ist dein Sous-Chef – es beobachtet jeden Schritt, von eingehenden Anfragen bis zu Datenbankabfragen, und markiert alles, was dich bremst. Langsame Seiten verlieren Nutzer (Studien sagen, 53 % verlassen eine Seite, wenn sie mehr als 3 Sekunden zum Laden braucht!), und APM hilft dir, diese Probleme *bevor* sie schmerzen, zu erkennen. Es ist proaktiver Seelenfrieden – weniger „Warum ist das kaputt?“-Momente, mehr „Schau, wie glatt das läuft!“-Erfolge.

## Installation

Komm mit Composer los:

```bash
composer require flightphp/apm
```

Du brauchst:
- **PHP 7.4+**: Halten wir kompatibel mit LTS-Linux-Distros und unterstützen moderne PHP.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: Das leichtgewichtige Framework, das wir verbessern.

## Unterstützte Datenbanken

FlightPHP APM unterstützt derzeit die folgenden Datenbanken zur Speicherung von Metriken:

- **SQLite3**: Einfach, dateibasierend und großartig für lokale Entwicklung oder kleine Apps. Standardoption in den meisten Einrichtungen.
- **MySQL/MariaDB**: Ideal für größere Projekte oder Produktionsumgebungen, in denen du robusten, skalierbaren Speicherplatz brauchst.

Du kannst deinen Datenbanktyp während des Konfigurationsschritts wählen (siehe unten). Stelle sicher, dass deine PHP-Umgebung die notwendigen Erweiterungen installiert hat (z. B. `pdo_sqlite` oder `pdo_mysql`).

## Erste Schritte

Hier ist dein schrittweiser Leitfaden zu APM-Großartigkeit:

### 1. APM registrieren

Füge das in deine `index.php` oder eine `services.php`-Datei ein, um mit der Verfolgung zu beginnen:

```php
use flight\apm\logger\LoggerFactory;
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json'); // Erstellt deinen Konfig und richtet einen Logger ein – SQLite per Standard
$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);

// Wenn du eine Datenbankverbindung hinzufügst
// Muss PdoWrapper oder PdoQueryCapture von Tracy-Erweiterungen sein
$pdo = new PdoWrapper('mysql:host=localhost;dbname=example', 'user', 'pass', null, true); // <-- True erforderlich, um die Verfolgung in APM zu aktivieren.
$Apm->addPdoConnection($pdo);
```

**Was passiert hier?**
- `LoggerFactory::create()` greift auf deinen Konfig zu (mehr dazu bald) und richtet einen Logger ein – SQLite per Standard.
- `Apm` ist der Star – es hört auf Flight-Ereignisse (Anfragen, Routen, Fehler usw.) und sammelt Metriken.
- `bindEventsToFlightInstance($app)` verbindet alles mit deiner Flight-App.

**Pro-Tipp: Sampling**
Wenn deine App beschäftigt ist, könnte das Protokollieren *jeder* Anfrage zu viel werden. Verwende eine Sample-Rate (0,0 bis 1,0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Protokolliert 10 % der Anfragen
```

Das hält die Leistung flott, während du solide Daten erhältst.

### 2. Konfiguriere es

Führe das aus, um deine `.runway-config.json` zu erstellen:

```bash
php vendor/bin/runway apm:init
```

**Was macht das?**
- Startet einen Assistenten, der fragt, woher die unbearbeiteten Metriken kommen (Quelle) und wohin die verarbeiteten Daten gehen (Ziel).
- Standard ist SQLite – z. B. `sqlite:/tmp/apm_metrics.sqlite` für die Quelle, ein weiteres für das Ziel.
- Du erhältst eine Konfig wie:
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

> Dieser Prozess fragt auch, ob du die Migrationen für diese Einrichtung ausführen möchtest. Wenn du das zum ersten Mal einrichtest, ist die Antwort ja.

**Warum zwei Orte?**
Unbearbeitete Metriken sammeln sich schnell (denke an unfiltrierte Protokolle). Der Worker verarbeitet sie in ein strukturiertes Ziel für das Dashboard. Das hält alles ordentlich!

### 3. Metriken mit dem Worker verarbeiten

Der Worker verwandelt unbearbeitete Metriken in Dashboard-fähige Daten. Führe ihn einmal aus:

```bash
php vendor/bin/runway apm:worker
```

**Was macht er?**
- Liest von deiner Quelle (z. B. `apm_metrics.sqlite`).
- Verarbeitet bis zu 100 Metriken (Standard-Pakgroße) in dein Ziel.
- Stoppt, wenn fertig oder keine Metriken mehr übrig sind.

**Lass es laufen**
Für Live-Apps möchtest du kontinuierliche Verarbeitung. Hier sind deine Optionen:

- **Daemon-Modus**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Läuft ewig und verarbeitet Metriken, sobald sie eintreffen. Toll für Entwicklung oder kleine Einrichtungen.

- **Crontab**:
  Füge das zu deiner Crontab hinzu (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Führt jede Minute aus – perfekt für Produktion.

- **Tmux/Screen**:
  Starte eine ablösbare Sitzung:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Strg+B, dann D, um zu lösen; `tmux attach -t apm-worker`, um wieder anzuschließen
  ```
  Hält es am Laufen, auch wenn du dich abmeldest.

- **Benutzerdefinierte Anpassungen**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Verarbeitet 50 Metriken auf einmal.
  - `--max_messages 1000`: Stoppt nach 1000 Metriken.
  - `--timeout 300`: Beendet nach 5 Minuten.

**Warum die Mühe?**
Ohne den Worker ist dein Dashboard leer. Es ist die Brücke zwischen unbearbeiteten Protokollen und handlungsrelevanten Erkenntnissen.

### 4. Das Dashboard starten

Sieh die Vitalwerte deiner App:

```bash
php vendor/bin/runway apm:dashboard
```

**Was ist das?**
- Startet einen PHP-Server unter `http://localhost:8001/apm/dashboard`.
- Zeigt Anfrageprotokolle, langsame Routen, Fehlerquoten und mehr.

**Passe es an**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Von jeder IP zugänglich (praktisch für Fernansicht).
- `--port 8080`: Verwende einen anderen Port, wenn 8001 belegt ist.
- `--php-path`: Zeige auf PHP, wenn es nicht in deinem Pfad ist.

Öffne die URL in deinem Browser und erkunde!

#### Produktionsmodus

In der Produktion musst du möglicherweise einige Techniken ausprobieren, um das Dashboard zu starten, da es wahrscheinlich Firewalls und andere Sicherheitsmaßnahmen gibt. Hier sind ein paar Optionen:

- **Umgekehrtes Proxy**: Richte Nginx oder Apache ein, um Anfragen an das Dashboard weiterzuleiten.
- **SSH-Tunnel**: Wenn du per SSH auf den Server zugreifen kannst, verwende `ssh -L 8080:localhost:8001 youruser@yourserver`, um das Dashboard auf deinen lokalen Rechner zu tunneln.
- **VPN**: Wenn dein Server hinter einem VPN liegt, verbinde dich damit und greife direkt auf das Dashboard zu.
- **Firewall konfigurieren**: Öffne Port 8001 für deine IP oder das Netzwerk des Servers. (oder welchen Port du eingestellt hast).
- **Apache/Nginx konfigurieren**: Wenn du einen Webserver vor deiner Anwendung hast, kannst du ihn für eine Domain oder Subdomain konfigurieren. Wenn du das tust, setze das Dokumentenroot auf `/path/to/your/project/vendor/flightphp/apm/dashboard`.

#### Willst du ein anderes Dashboard?

Du kannst dein eigenes Dashboard erstellen, wenn du möchtest! Schau in das Verzeichnis `vendor/flightphp/apm/src/apm/presenter` für Ideen, wie du die Daten für dein eigenes Dashboard darstellen kannst!

## Dashboard-Funktionen

Das Dashboard ist dein APM-Hauptquartier – hier ist, was du siehst:

- **Anfrageprotokoll**: Jede Anfrage mit Zeitstempel, URL, Antwortcode und Gesamtzeit. Klicke auf „Details“, um Middleware, Abfragen und Fehler zu sehen.
- **Langsamste Anfragen**: Die Top 5 Anfragen, die Zeit verbrauchen (z. B. „/api/heavy“ bei 2,5 s).
- **Langsamste Routen**: Die Top 5 Routen nach durchschnittlicher Zeit – großartig zum Erkennen von Mustern.
- **Fehlerquote**: Prozentsatz der fehlgeschlagenen Anfragen (z. B. 2,3 % 500er).
- **Latenz-Percentile**: 95. (p95) und 99. (p99) Antwortzeiten – kenne deine schlimmsten Szenarien.
- **Antwortcode-Diagramm**: Visualisiere 200er, 404er, 500er im Laufe der Zeit.
- **Lange Abfragen/Middleware**: Die Top 5 langsamen Datenbankaufrufe und Middleware-Schichten.
- **Cache-Treffer/Fehlgeschlagene**: Wie oft dein Cache hilft.

**Zusätze**:
- Filtere nach „Letzte Stunde“, „Letzter Tag“ oder „Letzte Woche“.
- Schalte den Dunkelmodus für nächtliche Sitzungen ein.

**Beispiel**:
Eine Anfrage zu `/users` könnte zeigen:
- Gesamtzeit: 150 ms
- Middleware: `AuthMiddleware->handle` (50 ms)
- Abfrage: `SELECT * FROM users` (80 ms)
- Cache: Treffer auf `user_list` (5 ms)

## Hinzufügen benutzerdefinierter Ereignisse

Verfolge alles – wie einen API-Aufruf oder einen Zahlungsprozess:

```php
use flight\apm\CustomEvent;

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**Wo zeigt es auf?**
In den Anfragedetails des Dashboards unter „Benutzerdefinierte Ereignisse“ – erweiterbar mit hübscher JSON-Formatierung.

**Anwendungsfalld**:
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
Jetzt siehst du, ob diese API deine App bremst!

## Datenbanküberwachung

Verfolge PDO-Abfragen so:

```php
use flight\database\PdoWrapper;

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite', null, null, null, true); // <-- True erforderlich, um die Verfolgung in APM zu aktivieren.
$Apm->addPdoConnection($pdo);
```

**Was du bekommst**:
- Abfragetext (z. B. `SELECT * FROM users WHERE id = ?`)
- Ausführungszeit (z. B. 0,015 s)
- Zeilenanzahl (z. B. 42)

**Achtung**:
- **Optional**: Überspringe das, wenn du keine DB-Verfolgung brauchst.
- **Nur PdoWrapper**: Kern-PDO ist noch nicht angebunden – bleib dran!
- **Leistungs-Warnung**: Das Protokollieren jeder Abfrage auf einer DB-intensiven Site kann Dinge verlangsamen. Verwende Sampling (`$Apm = new Apm($ApmLogger, 0.1)`), um die Last zu reduzieren.

**Beispiel-Ausgabe**:
- Abfrage: `SELECT name FROM products WHERE price > 100`
- Zeit: 0,023 s
- Zeilen: 15

## Worker-Optionen

Passe den Worker an deine Vorlieben an:

- `--timeout 300`: Stoppt nach 5 Minuten – gut für Tests.
- `--max_messages 500`: Begrenzt auf 500 Metriken – hält es begrenzt.
- `--batch_size 200`: Verarbeitet 200 auf einmal – balanciert Geschwindigkeit und Speicher.
- `--daemon`: Läuft ununterbrochen – ideal für Live-Überwachung.

**Beispiel**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Läuft eine Stunde lang und verarbeitet 100 Metriken auf einmal.

## Request-ID in der App

Jede Anfrage hat eine eindeutige Request-ID zur Verfolgung. Du kannst diese ID in deiner App verwenden, um Protokolle und Metriken zu korrelieren. Zum Beispiel kannst du die Request-ID auf einer Fehlerseite hinzufügen:

```php
Flight::map('error', function($message) {
	// Hole die Request-ID aus dem Response-Header X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// Zusätzlich könntest du sie aus der Flight-Variablen holen
	// Diese Methode funktioniert nicht gut in Swoole oder anderen asynchronen Plattformen.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## Upgrade

Wenn du auf eine neuere Version von APM aktualisierst, könnte es sein, dass Datenbank-Migrationen ausgeführt werden müssen. Du kannst das tun, indem du den folgenden Befehl ausführst:

```bash
php vendor/bin/runway apm:migrate
```
Das führt alle benötigten Migrationen aus, um das Datenbankschema auf die neueste Version zu aktualisieren.

**Hinweis:** Wenn deine APM-Datenbank groß ist, könnte diese Ausführung Zeit in Anspruch nehmen. Du möchtest den Befehl vielleicht in den Nebenzeiten ausführen.

## Bereinigen alter Daten

Um deine Datenbank aufgeräumt zu halten, kannst du alte Daten löschen. Das ist besonders nützlich, wenn du eine beschäftigte App betreibst und die Datenbankgröße handhabbar halten möchtest.
Du kannst das tun, indem du den folgenden Befehl ausführst:

```bash
php vendor/bin/runway apm:purge
```
Das entfernt alle Daten, die älter als 30 Tage sind, aus der Datenbank. Du kannst die Anzahl der Tage anpassen, indem du einen anderen Wert an die Option `--days` übergeben:

```bash
php vendor/bin/runway apm:purge --days 7
```
Das entfernt alle Daten, die älter als 7 Tage sind, aus der Datenbank.

## Fehlerbehebung

Festhängen? Probiere das:

- **Kein Dashboard-Daten?**
  - Läuft der Worker? Überprüfe `ps aux | grep apm:worker`.
  - Stimmen die Konfig-Pfade? Überprüfe, ob die DSNs in `.runway-config.json` auf echte Dateien zeigen.
  - Führe `php vendor/bin/runway apm:worker` manuell aus, um ausstehende Metriken zu verarbeiten.

- **Worker-Fehler?**
  - Schau in deine SQLite-Dateien (z. B. `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Überprüfe PHP-Protokolle auf Stack-Traces.

- **Dashboard startet nicht?**
  - Ist Port 8001 belegt? Verwende `--port 8080`.
  - PHP nicht gefunden? Verwende `--php-path /usr/bin/php`.
  - Firewall blockiert? Öffne den Port oder verwende `--host localhost`.

- **Zu langsam?**
  - Reduziere die Sample-Rate: `$Apm = new Apm($ApmLogger, 0.05)` (5 %).
  - Reduziere die Pakgroße: `--batch_size 20`.

- **Verfolgt keine Ausnahmen/Fehler?**
  - Wenn du [Tracy](https://tracy.nette.org/) für dein Projekt aktiviert hast, überschreibt es Flights Fehlerbehandlung. Du musst Tracy deaktivieren und sicherstellen, dass `Flight::set('flight.handle_errors', true);` gesetzt ist.

- **Verfolgt keine Datenbankabfragen?**
  - Stelle sicher, dass du `PdoWrapper` für deine Datenbankverbindungen verwendest.
  - Stelle sicher, dass du das letzte Argument im Konstruktor auf `true` setzt.