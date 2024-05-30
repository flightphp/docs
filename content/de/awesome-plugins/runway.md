# Startbahn

Startbahn ist eine CLI-Anwendung, die Ihnen hilft, Ihre Flight-Anwendungen zu verwalten. Sie kann Controller generieren, alle Routen anzeigen und mehr. Basierend auf der ausgezeichneten [adhocore/php-cli](https://github.com/adhocore/php-cli) Bibliothek.

## Installation

Installieren Sie es mit Composer.

```bash
composer require flightphp/runway
```

## Grundlegende Konfiguration

Beim ersten Start von Startbahn wird es Sie durch einen Einrichtungsprozess führen und eine `.runway.json` Konfigurationsdatei im Stammverzeichnis Ihres Projekts erstellen. Diese Datei enthält einige notwendige Konfigurationen, damit Startbahn ordnungsgemäß funktioniert.

## Verwendung

Startbahn verfügt über mehrere Befehle, die Sie verwenden können, um Ihre Flight-Anwendung zu verwalten. Es gibt zwei einfache Möglichkeiten, Startbahn zu verwenden.

1. Wenn Sie das Grundgerüstprojekt verwenden, können Sie `php runway [Befehl]` im Stammverzeichnis Ihres Projekts ausführen.
1. Wenn Sie Startbahn als Paket installiert haben, können Sie `vendor/bin/runway [Befehl]` im Stammverzeichnis Ihres Projekts ausführen.

Für jeden Befehl können Sie die `--help`-Flagge angeben, um weitere Informationen zur Verwendung des Befehls zu erhalten.

```bash
php runway routes --help
```

Hier sind ein paar Beispiele:

### Einen Controller generieren

Basierend auf der Konfiguration in Ihrer `.runway.json` Datei wird standardmäßig ein Controller für Sie im Verzeichnis `app/controllers/` generiert.

```bash
php runway make:controller MeinController
```

### Ein Active Record Model generieren

Basierend auf der Konfiguration in Ihrer `.runway.json` Datei wird standardmäßig ein Active Record Model für Sie im Verzeichnis `app/records/` generiert.

```bash
php runway make:record benutzer
```

Wenn Sie beispielsweise die `benutzer` Tabelle mit dem folgenden Schema haben: `id`, `name`, `email`, `erstellt_am`, `aktualisiert_am`, wird eine Datei ähnlich der folgenden im `app/records/BenutzerRecord.php` erstellt:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * ActiveRecord-Klasse für die Benutzertabelle.
 * @link https://docs.flightphp.com/awesome-plugins/active-record
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $created_at
 * @property string $updated_at
 */
class BenutzerRecord extends \flight\ActiveRecord
{
    /**
     * @var array $relations Legt die Beziehungen für das Modell fest
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

Wenn Sie nur bestimmte Routen anzeigen möchten, können Sie eine Flagge angeben, um die Routen zu filtern.

```bash
# Zeige nur GET-Routen an
php runway routes --get

# Zeige nur POST-Routen an
php runway routes --post

# usw.
```

## Anpassen von Startbahn

Wenn Sie ein Paket für Flight erstellen oder Ihre eigenen benutzerdefinierten Befehle in Ihr Projekt aufnehmen möchten, können Sie ein `src/commands/`, `flight/commands/`, `app/commands/` oder `commands/` Verzeichnis für Ihr Projekt/Paket erstellen.

Um einen Befehl zu erstellen, erweitern Sie einfach die Klasse `AbstractBaseCommand` und implementieren Sie mindestens eine `__construct`-Methode und eine `execute`-Methode.

```php
<?php

declare(strict_types=1);

namespace flight\commands;

class BeispielBefehl extends AbstractBaseCommand
{
	/**
     * Konstruieren
     *
     * @param array<string,mixed> $config JSON-Konfiguration aus .runway-config.json
     */
    public function __construct(array $config)
    {
        parent::__construct('make:beispiel', 'Erstellt ein Beispiel für die Dokumentation', $config);
        $this->argument('<lustiges-gif>', 'Der Name des lustigen Gifs');
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

Siehe die [adhocore/php-cli Dokumentation](https://github.com/adhocore/php-cli) für weitere Informationen darüber, wie Sie Ihre eigenen benutzerdefinierten Befehle in Ihre Flight-Anwendung integrieren können!