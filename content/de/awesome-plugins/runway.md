# Startbahn

Die Startbahn ist eine CLI-Anwendung, die Ihnen hilft, Ihre Flight-Anwendungen zu verwalten. Es kann Controller generieren, alle Routen anzeigen und mehr. Es basiert auf der ausgezeichneten [adhocore/php-cli](https://github.com/adhocore/php-cli) Bibliothek.

## Installation

Installieren Sie es mit Composer.

```bash
composer require flightphp/runway
```

## Grundkonfiguration

Wenn Sie die Startbahn zum ersten Mal ausführen, führt sie Sie durch einen Setup-Prozess und erstellt eine `.runway.json` Konfigurationsdatei im Stammverzeichnis Ihres Projekts. Diese Datei wird einige notwendige Konfigurationen enthalten, damit die Startbahn ordnungsgemäß funktioniert.

## Verwendung

Die Startbahn verfügt über eine Reihe von Befehlen, die Sie verwenden können, um Ihre Flight-Anwendung zu verwalten. Es gibt zwei einfache Möglichkeiten, die Startbahn zu verwenden.

1. Wenn Sie das Skelettprojekt verwenden, können Sie `php runway [Befehl]` aus dem Stammverzeichnis Ihres Projekts ausführen.
1. Wenn Sie die Startbahn als Paket installiert haben, können Sie `vendor/bin/runway [Befehl]` aus dem Stammverzeichnis Ihres Projekts ausführen.

Für jeden Befehl können Sie die `--help` Flagge übergeben, um weitere Informationen zur Verwendung des Befehls zu erhalten.

```bash
php runway routes --help
```

Hier sind ein paar Beispiele:

### Einen Controller generieren

Basierend auf der Konfiguration in Ihrer `.runway.json` Datei wird standardmäßig ein Controller für Sie im `app/controllers/` Verzeichnis generiert.

```bash
php runway make:controller MeinController
```

### Ein Active Record-Modell generieren

Basierend auf der Konfiguration in Ihrer `.runway.json` Datei wird standardmäßig ein Controller für Sie im `app/records/` Verzeichnis generiert.

```bash
php runway make:record benutzer
```

Wenn Sie beispielsweise die Tabelle `benutzer` mit dem folgenden Schema haben: `id`, `name`, `email`, `created_at`, `updated_at`, wird eine Datei ähnlich der folgenden in der `app/records/UserRecord.php` Datei erstellt:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * Aktivdatenklasse für die Benutzertabelle.
 * @link https://docs.flightphp.com/awesome-plugins/active-record
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $created_at
 * @property string $updated_at
 * // Hier können Sie auch Beziehungen hinzufügen, sobald Sie sie im $relations-Array definieren
 * @property CompanyRecord $company Beispiel für eine Beziehung
 */
class UserRecord extends \flight\ActiveRecord
{
    /**
     * @var array $relations Legen Sie die Beziehungen für das Modell fest
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
     */
    protected array $relations = [];

    /**
     * Konstruktor
     * @param mixed $databaseConnection Die Verbindung zur Datenbank
     */
    public function __construct($databaseConnection)
    {
        parent::__construct($databaseConnection, 'benutzer');
    }
}
```

### Alle Routen anzeigen

Dies zeigt alle Routen an, die derzeit bei Flight registriert sind.

```bash
php runway routes
```

Wenn Sie nur bestimmte Routen anzeigen möchten, können Sie eine Flagge übergeben, um die Routen zu filtern.

```bash
# Zeige nur GET-Routen an
php runway routes --get

# Zeige nur POST-Routen an
php runway routes --post

# usw.
```

## Anpassung der Startbahn

Wenn Sie ein Paket für Flight erstellen oder eigene benutzerdefinierte Befehle in Ihr Projekt aufnehmen möchten, können Sie dies tun, indem Sie ein `src/commands/`, `flight/commands/`, `app/commands/` oder `commands/` Verzeichnis für Ihr Projekt/Paket erstellen.

Um einen Befehl zu erstellen, erweitern Sie einfach die `AbstractBaseCommand`-Klasse und implementieren Sie mindestens eine `__construct`-Methode und eine `execute`-Methode.

```php
<?php

declare(strict_types=1);

namespace flight\commands;

class BeispielBefehl erstreckt sich von AbstractBaseCommand
{
	/**
     * Konstrukt
     *
     * @param array<string,mixed> $config JSON-Konfiguration von .runway-config.json
     */
    public function __construct(array $config)
    {
        parent::__construct('make:example', 'Erstellen eines Beispiels für die Dokumentation', $config);
        $this->argument('<funny-gif>', 'Der Name des lustigen Gifs');
    }

	/**
     * Führt die Funktion aus
     *
     * @return void
     */
    public function execute(string $controller)
    {
        $io = $this->app()->io();

		$io->info('Beispiel wird erstellt...');

		// Hier etwas tun

		$io->ok('Beispiel erstellt!');
	}
}
```

Sehen Sie die [adhocore/php-cli-Dokumentation](https://github.com/adhocore/php-cli) für weitere Informationen darüber, wie Sie Ihre eigenen benutzerdefinierten Befehle in Ihre Flight-Anwendung integrieren können!