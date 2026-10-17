# FlightPHP APM-Dokumentation

Willkommen bei FlightPHP APM – dein persönlicher Performance-Coach für deine App! Dieser Leitfaden ist deine Roadmap zum Einrichten, Verwenden und Meistern von Application Performance Monitoring (APM) mit FlightPHP. Ob du langsame Anfragen aufspürst oder dich einfach an Latenzdiagrammen erfreuen willst – wir haben dich abgedeckt. Machen wir deine App schneller, deine Nutzer glücklicher und deine Debugging-Sessions zum Kinderspiel!

Sieh dir eine [Demo](https://flightphp-docs-apm.sky-9.com/apm/dashboard) des Dashboards für die Flight-Dokumentationsseite an.

![FlightPHP APM](/images/apm.png)

## Warum APM wichtig ist

Stell dir vor: Deine App ist ein gut besuchtes Restaurant. Ohne eine Möglichkeit zu verfolgen, wie lange Bestellungen dauern oder wo die Küche ins Stocken gerät, rätselst du, warum Kunden genervt gehen. APM ist dein Souschef – es beobachtet jeden Schritt, von eingehenden Anfragen bis zu Datenbankabfragen, und markiert alles, was dich ausbremst. Langsame Seiten verlieren Nutzer (Studien besagen, dass 53 % abspringen, wenn eine Website länger als 3 Sekunden zum Laden braucht!), und APM hilft dir, diese Probleme zu erkennen, *bevor* sie wehtun. Es ist proaktive Beruhigung – weniger „Warum ist das kaputt?“-Momente, mehr „Sieh mal, wie geschmeidig das läuft!“-Erfolge.

## Installation

Beginne mit Composer:

```bash
composer require flightphp/apm
```

Du benötigst:
- **PHP 7.4+**: Hält uns mit LTS-Linux-Distributionen kompatibel und unterstützt modernes PHP.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: Das schlanke Framework, das wir beschleunigen.

## Unterstützte Datenbanken

FlightPHP APM unterstützt derzeit die folgenden Datenbanken zum Speichern von Metriken:

- **SQLite3**: Einfach, dateibasiert und ideal für lokale Entwicklung oder kleine Apps. Standardoption in den meisten Setups.
- **MySQL/MariaDB**: Ideal für größere Projekte oder Produktionsumgebungen, in denen du robusten, skalierbaren Speicher benötigst.

Du kannst deinen Datenbanktyp während des Konfigurationsschritts auswählen (siehe unten). Stelle sicher, dass deine PHP-Umgebung die erforderlichen Erweiterungen installiert hat (z. B. `pdo_sqlite` oder `pdo_mysql`).

## Erste Schritte

Hier ist deine Schritt-für-Schritt-Anleitung zum APM-Erfolg:

### 1. APM registrieren

Füge dies in deine `index.php` oder eine `services.php`-Datei ein, um mit der Verfolgung zu beginnen:

```php
use flight\apm\logger\LoggerFactory;
use flight\database\SimplePdo;
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json');
$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);

// Wenn du eine Datenbankverbindung hinzufügst
// Bevorzuge SimplePdo (oder PdoQueryCapture aus den Tracy Extensions in der Entwicklung).
// Aktiviere die APM-Abfrageverfolgung über das Options-Array (5. Argument).
$pdo = new SimplePdo('mysql:host=localhost;dbname=example', 'user', 'pass', null, [
	'trackApmQueries' => true, // erforderlich, um Abfragen für das APM zu erfassen
]);
$Apm->addPdoConnection($pdo);
```

**Was passiert hier?**
- `LoggerFactory::create()` holt deine Konfiguration (mehr dazu gleich) und richtet einen Logger ein – standardmäßig SQLite.
- `Apm` ist der Star – es hört auf Flight-Events (Anfragen, Routen, Fehler usw.) und sammelt Metriken.
- `bindEventsToFlightInstance($app)` bindet alles an deine Flight-App.

**Pro-Tipp: Sampling**
Wenn deine App stark ausgelastet ist, kann das Protokollieren *jeder* Anfrage die Dinge überlasten. Verwende eine Sampling-Rate (0.0 bis 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Protokolliert 10 % der Anfragen
```

Das hält die Performance flott und liefert trotzdem solide Daten.

### 2. Konfiguriere es

Führe dies aus, um deine `.runway-config.json` zu erstellen:

```bash
php vendor/bin/runway apm:init
```

**Was macht das?**
- Startet einen Assistenten, der fragt, woher Rohmetriken kommen (Quelle) und wohin verarbeitete Daten gehen (Ziel).
- Standard ist SQLite – z. B. `sqlite:/tmp/apm_metrics.sqlite` für die Quelle und ein weiterer Pfad für das Ziel.
- Du erhältst dann eine Konfiguration wie:
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

> Dieser Prozess fragt auch, ob du die Migrationen für dieses Setup ausführen möchtest. Wenn du dies zum ersten Mal einrichtest, lautet die Antwort ja.

**Warum zwei Orte?**
Rohmetriken sammeln sich schnell an (denk an ungefilterte Logs). Der Worker verarbeitet sie in ein strukturiertes Ziel für das Dashboard. Das hält die Dinge ordentlich!

### 3. Metriken mit dem Worker verarbeiten

Der Worker verwandelt Rohmetriken in dashboardfertige Daten. Führe ihn einmal aus:

```bash
php vendor/bin/runway apm:worker
```

**Was macht er?**
- Liest aus deiner Quelle (z. B. `apm_metrics.sqlite`).
- Verarbeitet bis zu 100 Metriken (Standard-Batchgröße) in dein Ziel.
- Stoppt, wenn er fertig ist oder keine Metriken mehr übrig sind.

**Dauerhaft laufen lassen**
Für Live-Apps möchtest du eine kontinuierliche Verarbeitung. Hier sind deine Optionen:

- **Daemon-Modus**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Läuft endlos und verarbeitet Metriken, sobald sie eintreffen. Ideal für Entwicklung oder kleine Setups.

- **Crontab**:
  Füge dies zu deiner Crontab hinzu (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Wird jede Minute ausgelöst – perfekt für die Produktion.

- **Tmux/Screen**:
  Starte eine abkoppelbare Sitzung:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, dann D zum Abkoppeln; `tmux attach -t apm-worker` zum erneuten Verbinden
  ```
  Läuft weiter, auch wenn du dich abmeldest.

- **Individuelle Anpassungen**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Verarbeitet 50 Metriken auf einmal.
  - `--max_messages 1000`: Stoppt nach 1000 Metriken.
  - `--timeout 300`: Beendet nach 5 Minuten.

**Warum das Ganze?**
Ohne den Worker bleibt dein Dashboard leer. Er ist die Brücke zwischen Rohlogs und umsetzbaren Erkenntnissen.

### 4. Dashboard starten

Sieh dir die Vitalwerte deiner App an:

```bash
php vendor/bin/runway apm:dashboard
```

**Was ist das?**
- Startet einen PHP-Server unter `http://localhost:8001/apm/dashboard`.
- Zeigt Anfrageprotokolle, langsame Routen, Fehlerraten und mehr.

**Anpassen**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Von jeder IP erreichbar (praktisch für Remote-Zugriff).
- `--port 8080`: Verwende einen anderen Port, wenn 8001 belegt ist.
- `--php-path`: Verweist auf PHP, wenn es nicht in deinem PATH liegt.

Rufe die URL in deinem Browser auf und erkunde sie!

#### Produktionsmodus

Für die Produktion musst du möglicherweise ein paar Techniken ausprobieren, um das Dashboard zum Laufen zu bringen, da wahrscheinlich Firewalls und andere Sicherheitsmaßnahmen vorhanden sind. Hier sind ein paar Optionen:

- **Reverse Proxy verwenden**: Richte Nginx oder Apache ein, um Anfragen an das Dashboard weiterzuleiten.
- **SSH-Tunnel**: Wenn du dich per SSH auf dem Server anmelden kannst, verwende `ssh -L 8080:localhost:8001
youruser@yourserver`, um das Dashboard auf deinen lokalen Rechner zu tunneln.
- **VPN**: Wenn dein Server hinter einem VPN steht, verbinde dich damit und greife direkt auf das Dashboard zu.
- **Firewall konfigurieren**: Öffne Port 8001 für deine IP oder das Netzwerk des Servers (oder den Port, den du festgelegt hast).
- **Apache/Nginx konfigurieren**: Wenn du einen Webserver vor deiner Anwendung hast, kannst du ihn auf eine Domain oder Subdomain konfigurieren. Richte diesen Host auf `dashboard/index.php` aus (der integrierte Server verwendet sie als Router). Statische Dateien liegen daneben in `dashboard/css` und `dashboard/js`.
- **Basic-Auth**: Lege `apm.dashboard_auth.user` und `apm.dashboard_auth.password` in der Runway-Konfiguration fest. Wenn einer der Werte leer ist, bleibt das Dashboard offen. Dies ist ein einzelnes gemeinsam genutztes Passwort, keine Anwendungsbenutzerkonten.

#### Ein anderes Dashboard gewünscht?

Du kannst dein eigenes Dashboard bauen, wenn du möchtest! Schau dir das Verzeichnis vendor/flightphp/apm/src/apm/presenter an, um Ideen zu erhalten, wie du die Daten für dein eigenes Dashboard präsentieren kannst!

## Dashboard-Funktionen

Das Dashboard öffnet mit den Zahlen, dann den Diagrammen, dann dem Anfrageprotokoll.

- **Statistikzeile**: Anfragenanzahl, Fehlerrate, p95, p99, durchschnittliche Latenz, Durchsatz pro Minute und Cache-Trefferrate.
- **Worker-Rückstand**: Wie viele Rohmetriken noch in `apm_metrics_log` liegen. Ein hoher Wert bedeutet, dass der Worker hinterherhängt. Ein leeres Dashboard mit einem Rückstand von 0 bedeutet, dass nichts gesammelt wurde.
- **Latenzdiagramm**: Durchschnittliche Latenz und Anfragenanzahl für dieselben Buckets. Die letzte Stunde verwendet 5-Minuten-Buckets, der letzte Tag verwendet 15-Minuten-Buckets und die letzte Woche verwendet 6-Stunden-Buckets. Ein benutzerdefinierter Zeitraum wählt einen Bucket, der etwa 40 Punkte beibehält. Zeiten werden in UTC gespeichert und in der von dir gewählten Zeitzone dargestellt.
- **Statusdiagramm**: Gestapelte `2xx`-, `3xx`-, `4xx`- und `5xx`-Anzahlen. Klicke auf eine Serie, um das Protokoll auf diese Klasse zu filtern.
- **Langsame Routen, Middleware, Abfragen und Anfragen**: Jeweils fünf. Abfragen werden gruppiert, sodass `WHERE id = 1` und `WHERE id = 2` als eine Anweisung zählen. Klicke auf eine Route, eine langsame Anfrage oder eine Fehlergruppe, um das Protokoll zu filtern.
- **Benutzerdefinierte Events**: Jeder benutzerdefinierte Event-Typ, den deine App auslöst, mit seiner Anzahl, wann er zuletzt gesehen wurde und einer kleinen Trendlinie für das ausgewählte Fenster. Nach Anzahl sortiert. Es berücksichtigt den Zeitraum, „Bots ausblenden“ und die anderen Filter. Klicke auf einen Typ, um das Protokoll darauf zu filtern.
- **Anfrageprotokoll**: Methode, URL, Status, Zeit, Speicher, Host und Bot-Kennzeichen. Details werden geladen, wenn du die Zeile öffnest, und enthalten einen Timing-Balken, Views, Query-Parameter, den Fehler-Trace und benutzerdefinierte Events.
- **Bots ausblenden**: Lässt Bot-Traffic aus den Aggregaten und dem Protokoll heraus. Suchcrawler und LLM-Crawler (GPTBot, ClaudeBot, PerplexityBot, SearxNG und andere) werden als Bots markiert.
- **Benutzerdefinierter Zeitraum**: Wähle zusätzlich zur letzten Stunde, zum letzten Tag oder zur letzten Woche ein Von/Bis-Fenster.
- **Filter**: URL, Status, Request-ID, Route, IP, Host, Session, User-Agent, benutzerdefinierter Event-Typ, Event-Daten und Fehlermeldung. Der Query-String behält sie über einen Refresh hinweg bei.

**Beispiel**:
Eine Anfrage an `/users` könnte Folgendes anzeigen:
- Gesamtzeit: 150ms
- Middleware: `AuthMiddleware->handle` (50ms)
- Abfrage: `SELECT * FROM users` (80ms)
- View-Renderzeit
- Cache: Treffer bei `user_list` (5ms)

**Beispiel**:
Eine Anfrage an `/users` könnte Folgendes anzeigen:
- Gesamtzeit: 150ms
- Middleware: `AuthMiddleware->handle` (50ms)
- Abfrage: `SELECT * FROM users` (80ms)
- Cache: Treffer bei `user_list` (5ms)

## Benutzerdefinierte Events hinzufügen

Verfolge alles – z. B. einen API-Aufruf oder einen Zahlungsvorgang:

```php
use flight\apm\CustomEvent;

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**Wo erscheint es?**
In den Anfragedetails des Dashboards unter „Benutzerdefinierte Events“ – aufklappbar mit hübsch formatiertem JSON. Das Panel **Benutzerdefinierte Events** zählt außerdem jeden Typ für dich, sodass ein schnelles `docs.markdown_served`-Event alles ist, was du brauchst, um die Frage „Wie oft passiert das?“ zu beantworten.

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
Jetzt siehst du, ob diese API deine App ausbremst!

## Datenbanküberwachung

Verfolge PDO-Abfragen so:

```php
use flight\database\SimplePdo;

$pdo = new SimplePdo('sqlite:/path/to/db.sqlite', null, null, null, [
	'trackApmQueries' => true, // erforderlich, um Abfragen für das APM zu erfassen
]);
$Apm->addPdoConnection($pdo);
```

**Was du bekommst**:
- Abfragetext (z. B. `SELECT * FROM users WHERE id = ?`)
- Ausführungszeit (z. B. 0.015s)
- Zeilenzahl (z. B. 42)

**Hinweis**:
- **Optional**: Überspringe dies, wenn du keine DB-Verfolgung benötigst.
- **SimplePdo (bevorzugt)**: Verwende `SimplePdo` mit `trackApmQueries => true`. Das veraltete `PdoWrapper` funktioniert weiterhin (5. Konstruktorargument `true`). Rohes Core-PDO ist noch nicht angehakt – bleib dran!
- **Performance-Warnung**: Das Protokollieren jeder Abfrage auf einer DB-intensiven Website kann die Dinge verlangsamen. Verwende Sampling (`$Apm = new Apm($ApmLogger, 0.1)`), um die Last zu verringern.

**Beispielausgabe**:
- Abfrage: `SELECT name FROM products WHERE price > 100`
- Zeit: 0.023s
- Zeilen: 15

## Worker-Optionen

Stimme den Worker nach deinen Wünschen ab:

- `--timeout 300`: Stoppt nach 5 Minuten – gut zum Testen.
- `--max_messages 500`: Begrenzt auf 500 Metriken – hält es endlich.
- `--batch_size 200`: Verarbeitet 200 auf einmal – balanciert Geschwindigkeit und Speicher.
- `--daemon`: Läuft ohne Unterbrechung – ideal für Live-Monitoring.

**Beispiel**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Läuft eine Stunde lang und verarbeitet jeweils 100 Metriken.

## Request-ID in der App

Jede Anfrage hat eine eindeutige Request-ID zur Verfolgung. Du kannst diese ID in deiner App verwenden, um Logs und Metriken zu korrelieren. Zum Beispiel kannst du die Request-ID zu einer Fehlerseite hinzufügen:

```php
Flight::map('error', function($message) {
	// Request-ID aus dem Response-Header X-Flight-Request-Id abrufen
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// Zusätzlich könntest du sie aus der Flight-Variable abrufen
	// Diese Methode funktioniert in Swoole oder anderen asynchronen Plattformen nicht gut.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## Upgrade

Wenn du auf eine neuere Version des APM aktualisierst, kann es sein, dass Datenbankmigrationen ausgeführt werden müssen. Du kannst dies mit dem folgenden Befehl tun:

```bash
php vendor/bin/runway apm:migrate
```
Dadurch werden alle Migrationen ausgeführt, die benötigt werden, um das Datenbankschema auf die neueste Version zu aktualisieren.

**Hinweis:** Wenn deine APM-Datenbank groß ist, können diese Migrationen einige Zeit dauern. Führe sie in einer ruhigen Phase aus. Der Worker kann weiterlaufen. Indexmigrationen schreiben gesammelte Zeilen nicht neu.

### Upgrade über 0.5.2 hinaus (Dashboard und Indizes)

0.5.2 hat nur die Bot-Erkennung erweitert. Die unten genannten Indexdateien werden im Release nach 0.5.2 ausgeliefert.

Sichere zuerst die Ziel-Datenbank. Kopiere die SQLite-Datei oder erstelle mit `mysqldump` einen Dump des MySQL/MariaDB-Schemas.

```bash
composer update flightphp/apm
php vendor/bin/runway apm:migrate
```

Das wendet an:

- SQLite: `0006-dest-indexes.sql` (die `request_dt`-Indizes wurden entfernt, als `0005` `apm_requests` neu aufgebaut hat)
- MySQL/MariaDB: `0002-dest-indexes.sql` (`request_dt` und die anderen Dashboard-Filter)

`apm:migrate` zeichnet den Dateinamen auf und überspringt ihn beim nächsten Mal. Es ist sicher, es zweimal auszuführen. `apm:purge` vergleicht `request_dt` mit einem UTC-Cutoff, was der Art entspricht, wie Zeilen gespeichert werden. Der erste Purge nach diesem Upgrade kann ein anderes Fenster behalten, wenn die Serverzeitzone vorher nicht UTC war.

`X-Flight-Request-Id` stimmt mit dem `request_token` überein, das für diese Anfrage gespeichert wurde.

### Upgrade von 0.4.3 -> 0.5.0

Wenn du von 0.4.3 auf 0.5.0 aktualisierst, musst du den folgenden Befehl ausführen:

```bash
php vendor/bin/runway apm:config-migrate
```

Dadurch wird deine Konfiguration vom alten Format mit der Datei `.runway-config.json` auf das neue Format migriert, das die Schlüssel/Werte in der Datei `config.php` speichert.

## Alte Daten bereinigen

Um deine Datenbank ordentlich zu halten, kannst du alte Daten bereinigen. Dies ist besonders nützlich, wenn du eine stark ausgelastete App betreibst und die Datenbankgröße überschaubar halten möchtest.
Du kannst dies mit dem folgenden Befehl tun:

```bash
php vendor/bin/runway apm:purge
```
Dadurch werden alle Daten entfernt, die älter als 30 Tage sind, aus der Datenbank. Du kannst die Anzahl der Tage anpassen, indem du einen anderen Wert an die Option `--days` übergibst:

```bash
php vendor/bin/runway apm:purge --days 7
```
Dadurch werden alle Daten entfernt, die älter als 7 Tage sind, aus der Datenbank.

## Fehlerbehebung

Hängst du fest? Probiere Folgendes:

- **Keine Dashboard-Daten?**
  - Läuft der Worker? Prüfe `ps aux | grep apm:worker`.
  - Stimmen die Konfigurationspfade? Überprüfe, ob die DSNs in `.runway-config.json` auf echte Dateien verweisen.
  - Führe `php vendor/bin/runway apm:worker` manuell aus, um ausstehende Metriken zu verarbeiten.

- **Worker-Fehler?**
  - Wirf einen Blick in deine SQLite-Dateien (z. B. `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Prüfe die PHP-Logs auf Stack-Traces.

- **Dashboard startet nicht?**
  - Port 8001 belegt? Verwende `--port 8080`.
  - PHP nicht gefunden? Verwende `--php-path /usr/bin/php`.
  - Firewall blockiert? Öffne den Port oder verwende `--host localhost`.

- **Zu langsam?**
  - Senke die Sampling-Rate: `$Apm = new Apm($ApmLogger, 0.05)` (5 %).
  - Reduziere die Batchgröße: `--batch_size 20`.

- **Exceptions/Fehler werden nicht verfolgt?**
  - Wenn [Tracy](https://tracy.nette.org/) für dein Projekt aktiviert ist, überschreibt es die Fehlerbehandlung von Flight. Du musst Tracy deaktivieren und dann sicherstellen, dass `Flight::set('flight.handle_errors', true);` gesetzt ist.

- **Datenbankabfragen werden nicht verfolgt?**
  - Bevorzuge `SimplePdo` mit `['trackApmQueries' => true]` als 5. Konstruktorargument (Options-Array).
  - Wenn du weiterhin das veraltete `PdoWrapper` verwendest, übergib `true` als 5. Argument.
  - Rufe `$Apm->addPdoConnection($pdo)` auf, nachdem du die Verbindung erstellt hast.